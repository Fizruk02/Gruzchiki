<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    public $timestamps = true;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'bot_id', 'title', 'description'
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
    public function bot()
    {
        return $this->hasOne(Bot::class, 'id', 'bot_id');
    }
}
