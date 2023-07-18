<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Conversation;
class Message_Recipient extends Model
{
    use HasFactory;

     /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['recipient_id', 'recipient_group_id', 'message_id', 'read_status', 'react_status'];

    public function conversation(){
        return $this->belongsTo(Conversation::class, 'recipient_group_id', 'id');
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'recipient_id');
    }

    public function user_react_message(){
        return $this->hasOne(User::class, 'id', 'recipient_id');
    }
}
