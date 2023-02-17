<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $table = 'request';

    public $timestamps = true;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'cabinet_id',
        'number',
    ];

    public function request_values() {
        return $this->hasMany(RequestValues::class, 'request_id', 'id');
    }
}
