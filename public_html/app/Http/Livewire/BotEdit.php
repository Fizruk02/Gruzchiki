<?php

namespace App\Http\Livewire;

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Request;

class BotEdit extends EditView
{
    protected $modelClass = \App\Models\Bot::class;

    public function heading(\App\Models\Bot $model)
    {
        return [
            $model->name ? $model->name : 'Создание',
            "Чат-бот",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the edit data or the components
     */
    public function edit($model, $params)
    {
        $cBot = new BotController();
        $cBot->cbInit();
        $cBot->return_url = $params['return_url'];

        config(['crudbooster.ADMIN_PATH' => '']);

        if ($model->id) {
            if(Request::method() == 'POST') return $cBot->postEditSave($model->id);
            return $cBot->getEditModel($model)->render();
        } else {
            if(Request::method() == 'POST') return $cBot->postAddSave($model->id);
            return $cBot->getAdd($model->id)->render();
        }
    }

    public function actions()
    {
        return [
            //new ActivateUserAction,
        ];
    }
}
