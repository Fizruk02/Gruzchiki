<?php

namespace App\Http\Livewire;

use App\Constructor\helpers\BTRouter;
use App\Http\controllers\RulesController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use LaravelViews\Views\DetailView;
use App\Models\User;
use App\Actions\ActivateUserAction;
use App\Actions\ListAction;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;
use Illuminate\Support\Facades\Route;

class RulesEdit extends EditView
{
    protected $modelClass = \App\Models\Cabinet::class;

    public function heading(\App\Models\Cabinet $model)
    {
        //$name = $model->id ? $model->user->name : '';
        return [
            //'', ''
            "Правила",
            "Редактор правил",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the edit data or the components
     */
    public function edit($model, $params)
    {
        $cUser = new RulesController();
        $cUser->cbInit();
        $cUser->return_url = $params['return_url'];
        config(['crudbooster.ADMIN_PATH' => '']);

        if(Request::method() == 'POST') return $cUser->postEditSave($model->id);
        return $cUser->getEdit($model->id)->render();
    }

    public function actions()
    {
        return [
            //new ActivateUserAction,
        ];
    }
}
