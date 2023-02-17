<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersProfiles extends Model
{
    use HasFactory;

    protected $table = 'users_profiles';

    public $timestamps = false;

    /**
     * Получить пользователя, владеющего данным телефоном.
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'users_id', 'id');
    }
}
