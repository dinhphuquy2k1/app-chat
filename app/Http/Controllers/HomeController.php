<?php

namespace App\Http\Controllers;

use App\Enums\UserActive;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Conversation;
use App\Enums\ReponseStatus;
use App\Models\Commons\HttpResponse;
use DB;
use App\Models\Participant;
use App\Models\Message;
use App\Enums\MessageType;
use App\Enums\ReactType;
use App\Exceptions\DatabaseException;
use App\Models\Message_Recipient;
use Validator;
use Illuminate\Validation\Rule;
use App\Events\ChatEvent;
use App\Events\UserOffline;
use App\Events\UserOnline;
use App\Events\CreatedConversationEvent;
use Pusher\Pusher;
use Illuminate\Support\Facades\Broadcast;

class HomeController extends Controller
{
    /**
     * api send Message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'message' => 'required_without_all:image,parent_message_id|string',
                'image' => 'required_without_all:message,parent_message_id|image|mimes:jpeg,png,jpg,gif|max:2048',
                'parent_message_id' => 'required_without_all:message,image|exists:messages,id',
                'to_conversation_id' => 'required|exists:conversations,id',
            ],
            [
                'message.required_without_all' => 'Message là bắt buộc khi không có image',
                'image.required_without_all' => 'Image là bắt buộc khi không có message',
                'image.image' => 'Chỉ cho phép tải lên hình ảnh',
                'image.mimes' => 'Hình ảnh phải là một tệp có định dạng: jpeg, png, jpg, gif',
                'image.max' => 'Hình ảnh không được lớn hơn 2048 kb',
                'to_conversation_id.required' => 'Id cuộc trò chuyện không được để trống',
                'to_conversation_id.exists' => 'Id cuộc trò chuyện không tồn tại',
                'parent_message_id.required_without_all' => 'parent_message_id không được để trống nếu ko có image hoặc message',
                'parent_message_id.exists' => 'parent_message_id ko tồn tại'
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            return HttpResponse::error($errors, ReponseStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $request->get('user');
        $parent_message_id = $request->get('parent_message_id', null);
        $contentMessage = $request->get('message', null);
        $reactType = $request->get('react_type', ReactType::NONE);
        $image = $request->file('image');
        $conversation_id = $request->get('to_conversation_id');
        //danh sách id user có mặt trong cuộc họp
        $userIds = Conversation::find($conversation_id)->participantsUserIds($user->id);
        try {
            $this->doSaveMessage($user, $userIds, $parent_message_id, $contentMessage, $conversation_id, $image, $reactType);
        } catch (DatabaseException $ex) {
            \Log::debug($ex->getMessage());
            return HttpResponse::error($ex->getMessage(), $ex->getCode());
        }

        return HttpResponse::success('Gửi tin nhắn thành công');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return HttpResponse::success(['access_token' => auth()->refresh(), 'token_type' => 'bearer', 'expires_in' => auth()->factory()->getTTL() * 180]);
    }

    /**
     * Tạo cuộc trò chuyện mới
     */
    public function createConversation(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'userIds' => 'required|array',
                'userIds.*' => 'exists:users,id',
                'name' => ''
            ],
            [
                'userIds.required' => 'Mảng userIds không được để trống.',
                'userIds.array' => 'userIds phải là một mảng.',
                'userIds.*.exists' => 'Id user không tồn tại.',
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            return HttpResponse::error($errors, ReponseStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $request->get('user'); //user đăng nhập
        $name = $request->get('name');

        if ($user->user_type != UserRole::CUSER) {
            return HttpResponse::error(['error' => 'Bạn không có quyển tạo cuộc trò chuyện mới'], ReponseStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userIds = $request->get('userIds');
        $userIds[] = $user->id;
        //loại bỏ các phần tử trùng lặp
        $userIds = array_unique($userIds);
        $conversation = [];

        if (count($userIds) == 2) {
            //kiểm tra có cuộc hội thoại nào ko giữa 2 user
            $conversation = Conversation::forBetweenOnly($userIds);
        }

        try {
            DB::beginTransaction();
            if (!$conversation) {
                $conversation = Conversation::create([
                    'creator_id' => $user->id,
                    'name' => $name
                ]);

                foreach ($userIds as $user_id) {
                    Participant::create([
                        'conversation_id' => $conversation['id'],
                        'user_id' => $user_id
                    ]);
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return HttpResponse::error($th->getMessage(), $th->getCode());
        }

        broadcast(new CreatedConversationEvent($userIds, Conversation::with('users')->find($conversation['id'])->toArray()))->toOthers();
        return HttpResponse::success($conversation);
    }

    /**
     * Get the authenticated User.
     *
     * @return
     */
    public function userProfile()
    {
        $token = substr(request()->header('Authorization'), 7);
        return HttpResponse::success(['access_token' => $token, 'token_type' => 'bearer', 'expires_in' => auth()->factory()->getTTL() * 180]);
    }

    /**
     * save Message
     */
    public function doSaveMessage($user, $userIds, $parent_message_id, $contentMessage, $conversation_id, $image, $reactType)
    {
        try {
            DB::transaction(function () use ($user, $userIds, $parent_message_id, $contentMessage, $conversation_id, $image, $reactType) {
                $message_type = MessageType::TEXT;
                if ($image) {
                    $message_type = MessageType::IMAGE;
                    $fileName = $image->getClientOriginalName();
                    $image->storeAs("public/$conversation_id/", $fileName);

                    $message = Message::create([
                        'sender_id' => $user->id,
                        'conversation_id' => $conversation_id,
                        'message_type' => $message_type,
                        'message' => "storage/$conversation_id/$fileName",
                        'parent_message_id' => $parent_message_id
                    ]);

                    foreach ($userIds as $userId) {
                        Message_Recipient::create([
                            'recipient_id' => $userId,
                            'recipient_group_id' => $conversation_id,
                            'message_id' => $message->id,
                            'read_status' => MessageType::UNREAD,
                            'react_status' => $reactType,
                        ]);
                    }
                }

                if ($contentMessage && $reactType == ReactType::NONE) {
                    $message_type = MessageType::TEXT;
                    $message = Message::create([
                        'sender_id' => $user->id,
                        'conversation_id' => $conversation_id,
                        'message_type' => MessageType::TEXT,
                        'message' => $contentMessage,
                        'parent_message_id' => $parent_message_id
                    ]);

                    foreach ($userIds as $userId) {
                        Message_Recipient::create([
                            'recipient_id' => $userId,
                            'recipient_group_id' => $conversation_id,
                            'message_id' => $message->id,
                            'read_status' => MessageType::UNREAD,
                            'react_status' => $reactType,
                        ]);
                    }
                }

                Conversation::find($conversation_id)->touch();

                if ($parent_message_id) {
                    if ($reactType != ReactType::NONE) {
                        $message_type = $reactType != ReactType::NONE ? MessageType::EMOTION : MessageType::TEXT;
                        Message_Recipient::where('message_id', $parent_message_id)->where('recipient_id', $user->id)->update([
                            'react_status' => $reactType
                        ]);
                        $message = Message::find($parent_message_id);
                    }
                }

                broadcast(new ChatEvent($conversation_id, $message->messageWithParentMessage(), $user, $message_type))->toOthers();
            });
        } catch (\Throwable $th) {
            throw new DatabaseException($th->getMessage(), ReponseStatus::HTTP_INTERNAL_SERVER_ERROR);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * setUserActivityStatus
     *
     * @param  mixed $request
     * @param  mixed $status
     * @return void
     */
    public function setUserActivityStatus(Request $request, $status)
    {
        $user = $request->get('user');
        $user->is_active = $status;
        $user->save();
        switch ($status) {
            case UserActive::ACTIVE:
                event(new UserOnline($user));
                broadcast(new UserOnline($user))->toOthers();
                break;
            case UserActive::INACTIVE:
                event(new UserOffline($user));
                broadcast(new UserOffline($user))->toOthers();
                break;
        }
    }

    /**
     * lấy danh sách tin nhắn theo id cuộc trò chuyện
     *
     * @param  mixed $id
     * @return void
     */
    public function getMessageByConversationId($id)
    {
        try {
            $conversation = Conversation::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return HttpResponse::error("Không tìm thấy cuộc trò chuyện có id là $id", ReponseStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        //kiểm tra xem ng dùng có mặt trong cuộc trò chuyện này ko
        if (!$conversation->hasParticipant(request()->get('user')->id)) {
            return HttpResponse::error("Bạn không có quyền truy cập cuộc trò chuyện này", ReponseStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        //cập nhật trạng thái đọc tin nhắn
        $conversation->markAsRead(request()->get('user')->id);
        $pageIndex = request()->get('pageIndex', 1);
        $offset = request()->get('offset', 0);
        return HttpResponse::success($conversation->getMessage($pageIndex, $offset)->get()->toArray());
    }

    /**
     * Lấy danh sách cuộc trò chuyện kèm tin nhắn mới nhất của cuộc trò chuyện
     */
    public function getConversationWithNewMessage()
    {
        $user_id = request()->get('user')->id;
        $pageIndex = request()->get('pageIndex', 1);
        return HttpResponse::success(Conversation::forUserWithNewMessages($user_id, $pageIndex));
    }


    /**
     * Số lượng tin nhắn chưa đọc
     *
     * @return void
     */
    public function getUnReadMessageCount()
    {
        $user_id = request()->get('user')->id;
        return HttpResponse::success(['countUnRead' => Conversation::forUserUnreadMessagesCount($user_id)]);
    }


    /**
     * Load danh sách sinh viên cho doanh nghiệp
     *
     * @param  mixed $request
     * @return void
     */
    public function getUserByCUser(Request $request)
    {
        try {
            $data = User::getUserByCUser($request->get('user')->id);
        } catch (\Throwable $th) {
            throw $th;
        }
        return HttpResponse::success($data);
    }


}
