<?php
/**
 * @author Alexey Garkin wmstr2007@yandex.ru
 * @copyright Copyright (c) 2022 b2bot.ru
 */

namespace App\Modules\slidebar;

use App\Models\Module;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class SlidebarModule extends Module {
    /**
     * @var string Уникальный ID идентифитатор модуля
     */
    public $id = 'slidebar';

    /**
     * @var string Описание модуля
     */
    public $name = 'Панель навигации';

    public function invoke() {
        $data = $this->section->data ? json_decode($this->section->data, true) : null;
        app('view')->addNamespace('slidebar', __DIR__.'/templates');
        return View::make('slidebar::index', ['item' => $data])->render();
    }
}
