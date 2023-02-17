<?php

namespace App\Http\Livewire;

use App\Constructor\helpers\BTRouter;
use App\Http\Controllers\DispatcherController;
use Illuminate\Support\Facades\Request;
use LaravelViews\Views\DetailView;
use App\Models\User;
use App\Actions\ActivateUserAction;
use App\Actions\ListAction;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;
use Illuminate\Support\Facades\Route;

class DispatcherEdit extends EditView
{
    protected $modelClass = \App\Models\Dispatcher::class;

    public function heading(\App\Models\Dispatcher $model)
    {
        return [
            $model->name ? $model->name : 'Создание',
            "Профиль диспетчера",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the edit data or the components
     */
    public function edit($model, $params)
    {
        $cUser = new DispatcherController();
        $cUser->cbInit();
        $cUser->return_url = $params['return_url'];

        config(['crudbooster.ADMIN_PATH' => '']);

        if ($model->id) {
            if(Request::method() == 'POST') return $cUser->postEditSave($model->id);
            return $cUser->getEdit($model->id)->render();
        } else {
            if(Request::method() == 'POST') return $cUser->postAddSave($model->id);
            return $cUser->getAdd($model->id)->render();
        }
    }

    public function actions()
    {
        return [
            //new ActivateUserAction,
        ];
    }
}
