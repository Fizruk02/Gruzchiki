<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersBalance extends Model
{
    protected $table = 'orders_balance';

    public $timestamps = false;

    use HasFactory;

}
