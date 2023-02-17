<?php

namespace App\Models;

use App\Constructor\Telegram;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        't_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Получить кабинет пользователя
     */
    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id', 'id');
    }

    /**
     * Получить профиль пользователя
     */
    public function users_profiles()
    {
        return $this->belongsTo(UsersProfiles::class, 'users_id', 'id');
    }

    /**
     * Получить бот пользователя
     */
    public function bot()
    {
        return $this->belongsTo(Bot::class, 'bot_id', 'id');
    }

    /**
     * Отправка сообщения в телеграмм
     */
    public function sendMessage($message, $bot_key = null)
    {
        $telegram = new Telegram($bot_key ? $bot_key : $this->bot->bot_key);
        $telegram->tgMess($message, $this->id_chat);
    }

}
