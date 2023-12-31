<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

     /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['sender_id', 'conversation_id', 'message_type', 'message', 'parent_message_id'];

    public function messageWithParentMessage(){

        $messageTable = 'messages';
        return $this->leftJoin("$messageTable as m1", function ($join) use($messageTable ){
            $join->on('m1.id', "$messageTable.parent_message_id")
                ->where("$messageTable.parent_message_id", '!=', null);
        })->where("$messageTable.id", '=', $this->id)
        ->with(['message_recipients' => function ($query){
            $query->where('react_status', '!=', 0);
            $query->with('user_react_message');
        }, 'userSender'])
        ->select("$messageTable.*", "m1.message as parent_message", "m1.message_type as parent_message_type")->with('userSender')->first()->toArray();
    }

    public function userSender(){
        return $this->hasOne(User::class, 'id', 'sender_id');
    }

    public function message_recipients(){
        return $this->hasMany(
            Message_Recipient::class,
            'message_id',
            'id',
        );
    }

    public function user_react_message(){
        return $this->hasOneThrough(User::class, Message_Recipient::class, 'recipient_group_id', 'id', 'id', 'recipient_id');
    }
}
