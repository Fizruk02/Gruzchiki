<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebLayouts extends Model
{
    use HasFactory;

    /**
     * Получить секции страницы
     */
    public function sections()
    {
        return $this->hasMany(WebLayoutSections::class, 'layout_id')->orderBy('sort');
    }
}
