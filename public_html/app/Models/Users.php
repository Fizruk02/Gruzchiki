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

class Users extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    const STATUS_ACTIVE = 1;
    const STATUS_WAIT   = 0;
    const STATUS_NEW    = -1;
    const STATUS_BLACK  = -10;
    const STATUS_BAN    = -20;

    const ROLE_SUPERADMIN   = 1;
    const ROLE_USER         = 2;
    const ROLE_ADMIN        = 3;
    const ROLE_KURATOR      = 4;
    const ROLE_DISPETCHER   = 5;

    public $table = 'users';

    protected $with = ['users_profiles', 'cabinet'];

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
     * Get the user that owns the cabinet.
     */
    public function cabinet()
    {
        return $this->hasOne(Cabinet::class, 'id', 'cabinet_id');
    }

    /**
     * Get the user that owns the cabinet.
     */
    public function users_profiles()
    {
        //return $this->belongsTo(UsersProfiles::class, 'id', 'users_id');
        return $this->hasOne(UsersProfiles::class, 'users_id', 'id');
        //return $this->hasOne(UsersProfiles::class, 'id', 'users_id');
    }

    /**
     * Get the user that owns the cabinet.
     */
    public function users()
    {
        return $this->hasOne(Users::class, 'id', 'id');
    }

    /**
     * Get the user that owns the cabinet.
     */
    public function cabinet_admin()
    {
        return $this->hasOne(Users::class, 'id', 'id');
    }

    /**
     * Get the user that owns the bot.
     */
    public function bot()
    {
        return $this->hasOne(Bot::class, 'id', 'bot_id');
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
