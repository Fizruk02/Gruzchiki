<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestFields extends Model
{
    protected $table = 'request_fields';

    public $timestamps = false;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'cabinet_id',
        'sort',
        'name',
        'is_list',
        'is_text',
        'is_number',
        'is_required',
    ];
}
