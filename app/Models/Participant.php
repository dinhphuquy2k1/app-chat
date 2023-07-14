<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Conversation;
class Participant extends Model
{
    use HasFactory;

    protected $table = 'participants';
    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['conversation_id', 'user_id', 'last_read'];
    /**
     * The users that belong to the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation', 'id');
    }

    /**
     * Participants relationship.
     *
     * @return
     *
     * @codeCoverageIgnore
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'conversation_id', 'id');
    }

}
