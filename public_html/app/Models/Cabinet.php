<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cabinet extends Model
{
    protected $table = 'cabinet';

    public $timestamps = false;

    public static $curCabinet = null;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'rules', 'land_title'
    ];

    /**
     * Get the user that owns the cabinet.
     */
    public function users()
    {
        return $this->belongsTo(Users::class, 'users_id', 'id');
        //return $this->hasOne(Users::class);
    }

    public function orders_fields() {
        return $this->hasMany(OrdersFields::class, 'cabinet_id', 'id');
    }

    static function curCabinet() {
        if (self::$curCabinet) return self::$curCabinet;

        $user = Auth::user();
        if (Auth::user()->id_cms_privileges == Users::ROLE_ADMIN) {
            self::$curCabinet = \App\Models\Cabinet::where('users_id', $user->id)->first();
        } else if ($user->cabinet_id) {
            self::$curCabinet = \App\Models\Cabinet::where('id', $user->cabinet_id)->first();
        }
        return self::$curCabinet;
    }
}
