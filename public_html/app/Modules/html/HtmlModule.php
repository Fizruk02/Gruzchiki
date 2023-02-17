<?php
/**
 * @author Alexey Garkin wmstr2007@yandex.ru
 * @copyright Copyright (c) 2022 b2bot.ru
 */

namespace App\Modules\html;

use App\Models\Module;
use Illuminate\Support\Facades\View;

class HtmlModule extends Module {
    /**
     * @var string Уникальный ID идентифитатор модуля
     */
    public $id = 'html';

    /**
     * @var string Описание модуля
     */
    public $name = 'HTML текст';

    public function invoke() {
        $data = $this->section->data ? json_decode($this->section->data, true) : null;
        app('view')->addNamespace('html', __DIR__);
        return View::make('html::index', ['item' => $data, 'section' => $this->section, 'page' => $this->page])->render();
        //$data = json_decode($this->section->data);
        //return $data->code;
    }
}
