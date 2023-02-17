<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersValues extends Model
{
    protected $table = 'orders_values';

    public $timestamps = false;

    use HasFactory;
}
