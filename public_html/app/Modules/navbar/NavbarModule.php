<?php
/**
 * @author Alexey Garkin wmstr2007@yandex.ru
 * @copyright Copyright (c) 2022 b2bot.ru
 */

namespace App\Modules\navbar;

use App\Models\Module;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class NavbarModule extends Module {
    /**
     * @var string Уникальный ID идентифитатор модуля
     */
    public $id = 'navbar';

    /**
     * @var string Описание модуля
     */
    public $name = 'Панель навигации';

    public function invoke() {
        $data = $this->section->data ? json_decode($this->section->data, true) : null;
        app('view')->addNamespace('navbar', __DIR__.'/templates');
        //$finder = new \Illuminate\View\FileViewFinder(app()['files'], [__DIR__.'/templates']);
        //View::setFinder($finder);
        return View::make('navbar::index', ['item' => $data])->render();
    }
}
