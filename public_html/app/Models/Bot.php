<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $table = 'bot';

    public $timestamps = false;

    use HasFactory;
}
