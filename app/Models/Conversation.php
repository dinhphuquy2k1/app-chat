<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\ReponseStatus;
use App\Models\Commons\HttpResponse;
use App\Models\Message_Recipient;
use App\Enums\MessageType;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['creator_id', 'name'];

    public function users()
    {
        return $this
            ->belongsToMany(
                User::class,
                Participant::class,
                'conversation_id',
                'user_id',
                'id',
                'id'
            );
    }

    public function message_recipients()
    {
        return $this->hasMany(
            Message_Recipient::class,
            'recipient_group_id',
            'id'
        );
    }

    public function test()
    {
        return $this->hasManyThrough(Message_Recipient::class, Message::class, 'conversation_id', 'message_id', 'id', 'id');
    }

    public function lastestMessageRecipient()
    {
        return $this->hasOne(Message_Recipient::class, 'recipient_group_id')->latestOfMany();
    }

    public function messageRecipient()
    {
        return $this->hasOneThrough(Message_Recipient::class, Message::class, 'conversation_id', 'message_id', 'id', 'id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id', 'id')->latestOfMany();
    }

    public function userSender()
    {
        return $this->hasOneThrough(User::class, Message::class, 'conversation_id', 'id', 'id', 'sender_id');
    }

    public function userRecipient()
    {
        return $this->hasOneThrough(User::class, Message_Recipient::class, 'recipient_group_id', 'id', 'id', 'recipient_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'creator_id');
    }

    public function participant()
    {
        return $this->hasOne(Participant::class, 'conversation_id', 'id');
    }

    /**
     * Messages relationship.
     *
     * @return
     *
     * @codeCoverageIgnore
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'conversation_id', 'id');
    }

    /**
     * kiểm tra có cuộc hội thoại nào giữa doanh nghiệp và sinh viên ko
     *
     * @param Builder $query
     * @param array $participants
     *
     * @return
     */
    public function scopeForBetweenOnly(Builder $query, array $participants)
    {
        $result = $query->whereHas('participants', function (Builder $builder) use ($participants) {
            $builder->whereIn('user_id', $participants)
                ->groupBy('participants.conversation_id')
                ->select('participants.conversation_id')
                ->havingRaw('COUNT(participants.conversation_id) = ?', [count($participants)]);
        })->first();

        return $result ? $result->toArray() : [];
    }

    /**
     * Mark a thread as read for a user.
     *
     * @param mixed $userId
     *
     * @return void
     */
    public function markAsRead($userId)
    {
        try {
            $messageRecipient = $this->getMessageRecipientFromUser($userId);
            $messageRecipient->update(['read_status' => MessageType::READ]);
            $participant = $this->getParticipantFromUser($userId);
            $participant->last_read = new Carbon();
            $participant->save();
        } catch (ModelNotFoundException $e) { // @codeCoverageIgnore
            return HttpResponse::error("Không tìm thấy user có id là $userId", ReponseStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Checks to see if a user is a current participant of the thread.
     *
     * @param mixed $userId
     *
     * @return bool
     */
    public function hasParticipant($userId)
    {
        $participants = $this->participants()->where('user_id', '=', $userId);
        return $participants->count() > 0;
    }

    /**
     * Returns threads with new messages that the user is associated with.
     *
     * @param Builder $query
     * @param mixed $userId
     * @param int $pageIndex
     * @return
     */
    public function scopeForUserWithNewMessages(Builder $query, $userId, $pageIndex)
    {
        $recordsPerPage = 15;
        // $sql =  $this->whereHas(
        //     'latestMessage',
        //     function ($query) use ($userId) {
        //         $query->where('sender_id', $userId);
        //     },
        //     )
        //     ->orWhereHas('lastestMessageRecipient', function ($query) use ($userId) {
        //         $query->where('recipient_id', $userId);
        //     })
        //     ->with('users', function($query) use($userId){
        //         $query->where('users.id', '!=', $userId);
        //     })
        //     ->with('userRecipient', 'latestMessage', 'lastestMessageRecipient');
        //     dd($sql->toSql());
        // ->latest('updated_at')
        // ->skip(($pageIndex - 1) * $recordsPerPage)
        // ->take($recordsPerPage)
        // ->get()
        // ->map(function ($item) {
        //     $mergedData = array_merge($item->latestMessage->toArray(), $item->lastestMessageRecipient->toArray(), $item->userRecipient->toArray());
        //     $item->setAttribute('messages', $mergedData);
        //     unset($item->latestMessage, $item->lastestMessageRecipient, $item->userRecipient);
        //     return $item;
        // })
        // ->toArray();
        return $this->whereHas(
            'latestMessage',
            function ($query) use ($userId) {
                $query->where('sender_id', $userId);
            },
            )
            ->orWhereHas('participant', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['latestMessage', 'messageRecipient'])
            ->with('users', function ($query) use ($userId) {
                $query->where('users.id', '!=', $userId);
            })
            ->with('latestMessage.userSender')
            ->latest("updated_at")
            ->skip(($pageIndex - 1) * $recordsPerPage)
            ->take($recordsPerPage)
            ->get()
            ->map(function ($item) {
                $latestMessage = $item->latestMessage ? $item->latestMessage->toArray() : [];
                $messageRecipient = $item->messageRecipient ? $item->messageRecipient->toArray() : [];
                $mergedData = array_merge($latestMessage, $messageRecipient);
                $mergedData = $mergedData ? $mergedData : null;
                $item->setAttribute('messages', $mergedData);
                unset($item->latestMessage, $item->messageRecipient);
                return $item;
            })
            ->toArray();
    }

    /**
     * Returns array of unread message s in thread for given user.
     *
     * @param mixed $userId
     *
     * @return
     */
    public function userUnreadMessages($userId)
    {
        $messageRecipient = $this->getMessageRecipientFromUser($userId);
        dd($this->with('message_recipients')->get());
        $messages = $this->messages()->where('user_id', '!=', $userId)->get();

        try {
            $participant = $this->getParticipantFromUser($userId);
        } catch (ModelNotFoundException $e) {
            return collect();
        }

        if (!$participant->last_read) {
            return $messages;
        }

        return $messages->filter(function ($message) use ($participant) {
            return $message->updated_at->gt($participant->last_read);
        });
    }

    /**
     * Finds the participant record from a user id.
     *
     * @param mixed $userId
     *
     * @return mixed
     *
     * @throws ModelNotFoundException
     */
    public function getParticipantFromUser($userId)
    {
        return $this->participants()->where('user_id', $userId)->firstOrFail();
    }

    /**
     * Tìm kiếm các tin nhắn được gửi đến cho userid
     *
     * @param mixed $userId
     *
     * @return mixed
     *
     * @throws ModelNotFoundException
     */
    public function getMessageRecipientFromUser($userId)
    {
        return $this->message_recipients()->where('recipient_id', $userId);
    }

    /**
     * Returns count of unread messages in thread for given user.
     *
     * @param mixed $userId
     *
     * @return int
     */
    public function scopeForUserUnreadMessagesCount(Builder $query, $userId)
    {
        return $query->whereHas('message_recipients', function (Builder $builder) use ($userId) {
            $builder->where('recipient_id', '=', $userId)
                ->where('read_status', '=', MessageType::UNREAD);
        })->count();
    }

    /**
     * Returns an array of user ids that are associated with the thread.
     *
     * @param mixed $userId
     *
     * @return array
     */
    public function participantsUserIds($userId = null)
    {
        $users = $this->participants()->select('user_id')->get()->map(function ($participant) {
            return $participant->user_id;
        });

        return $users->toArray();
    }

    /**
     * Lấy danh sách tin nhắn trong cuộc trò chuyện
     *
     * @param mixed $userId
     *
     * @return mixed
     *
     * @throws ModelNotFoundException
     */
    public function getMessage($pageIndex = 1)
    {
        $recordsPerPage = 20;
        $conversationTable = 'conversations';
        $messageTable = 'messages';
        $messageRecipientTable = 'message__recipients';
        return $this->join("$messageTable as m", "$conversationTable.id", '=', "m.conversation_id")
            ->join($messageRecipientTable, "$messageRecipientTable.recipient_group_id", '=', "$conversationTable.id")
            ->leftJoin("$messageTable as m1", function ($join) {
                $join->on('m1.id', 'm.parent_message_id')
                    ->where('m.parent_message_id', '!=', null);
            })
            ->where("m.message_type", '!=', MessageType::EMOTION)
            ->where($this->getQualifiedKeyName(), '=', $this->id)
            ->whereRaw("$messageRecipientTable.message_id = m.id")
            ->select("m.*", "$messageRecipientTable.* ", "$conversationTable.*", "m1.message as parent_message", "m1.message_type as parent_message_type", "m.updated_at", "m.created_at")
            ->skip(($pageIndex - 1) * $recordsPerPage)
            ->take($recordsPerPage)
            ->latest("m.updated_at");
    }
}
