<?php

namespace App\Models;

use App\Http\Middleware\Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebPages extends Model
{
    use HasFactory;

    protected $with = ['roles', 'sections', 'layout'];

    public $params = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'photo',
        'slug',
        'date_pub',
        'sort',
        'parent',
        'activate',
        'ogdescr',
        'ogtitle',
        'layout',
        'content',
        'ctype',
    ];

    /**
     * Получить роли у кого есть доступ
     */
    public function roles()
    {
        return $this->hasMany(UsRolesAccessPages::class, 'page_id');
    }

    /**
     * Получить секции страницы
     */
    public function sections()
    {
        return $this->hasMany(WebSections::class, 'page_id')->orderBy('sort');
    }

    /**
     * Получить шаблон страницы
     */
    public function layout()
    {
        return $this->hasOne(WebLayouts::class, 'id', 'layout_id')
            ->where('activate', 1);
    }

    /**
     * Получить роли у кого есть доступ
     */
    public function isAccess($role_id)
    {
        if (empty($this->roles)) return true;

        $roles = [];
        foreach ($this->roles as $role)
            $roles[] = $role->role_id;

        return in_array($role_id, $roles);
    }

}
