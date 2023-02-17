<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersFields extends Model
{
    protected $table = 'orders_fields';

    public $timestamps = false;

    use HasFactory;

    protected $fillable = [
        'sort',
        'name',
        'type',
        'is_first',
        'is_accept',
        'is_2hours',
        'is_30minutes',
        'is_require',
        'is_visible',
        'class',
        'is_label',
        'placeholder'
    ];
}
