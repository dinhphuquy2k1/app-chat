<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Conversation;
use Tymon\JWTAuth\Contracts\JWTSubject;
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }    

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'participants', 'user_id', 'participant_id');
    }


    /**
     * Lấy danh sách user cho doanh nghiệp
     */
    public static function getUserByCUser($user_id){
        return User::where('user_type', '!=', UserRole::CUSER)->where('id', '!=', $user_id)->get()->toArray();
    }

    /**
     * Kiểm tra cuộc hội thoại giữa doanh nghiệp và user đã tồn tại chưa
     * @param $from user_id doanh nghiệp
     * @param $to id sinh viên
     */
    public function scopeForCheckConversation(Builder $query, $participants)
    {
        $query = User::whereHas('participants', function (Builder $builder) use ($participants) {
            $builder->whereIn('user_id', $participants)
                ->groupBy('participants.conversation_id')
                ->select('participants.conversation_id')
                ->havingRaw('COUNT(participants.conversation_id) = ?', [count($participants)]);
        });

        $sql = $query->toSql();
        dd($sql);
    //   return $query->rightJoin('conversations', 'conversations.creator_id', '=', 'users.id')
    //     ->rightJoin('participants', 'participants.user_id', '=', 'users.id')
    //     ->where('participants.user_id', '=', $from)
    //     ->where('participants.user_id', '=', $to)
    //     ->get()->toArray();
    }
}
