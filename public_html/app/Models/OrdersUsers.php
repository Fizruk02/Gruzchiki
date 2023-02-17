<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersUsers extends Model
{
    protected $table = 'orders_users';

    public $timestamps = false;

    use HasFactory;

    public function order()
    {
        return $this->hasOne(Orders::class, 'id', 'order_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
