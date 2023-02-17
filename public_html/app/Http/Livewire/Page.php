<?php

namespace App\Http\Livewire;

use App\Constructor\helpers\BTRouter;
use App\Http\controllers\AdminCmsUsersController;
use Illuminate\Support\Facades\Request;
use LaravelViews\Views\DetailView;
use App\Models\User;
use App\Actions\ActivateUserAction;
use App\Actions\ListAction;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;
use Illuminate\Support\Facades\Route;
use App\Models\WebPages;

class Page extends DetailView
{
    protected $modelClass = \App\Models\WebPages::class;

    public function heading(WebPages $model)
    {
        //$name = $model->id ? $model->user->name : '';
        return [
            //'', ''
            "",
            "",
        ];
    }

    public function detail(WebPages $model)
    {
        //return UI::component('components.my-custom-component', ['attribute' => 'value' ]);
        if (!isset($model->params['buttons'])) return '<div style="padding: 15px 15px; min-height: 500px;">Нет информации</div>';
        $content = '<div style="padding: 15px 15px; min-height: 500px;">';
        $ind = 0;
        foreach ($model->params['buttons'] as $key => $btn) {
            if ($ind != $model->params['cur'])
                $content .= '<a href="'.$btn['link'].'" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">'.$btn['name'].'</a> ';
            else
                $content .= '<a href="'.$btn['link'].'" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">'.$btn['name'].'</a> ';

            $ind++;
        }
        return $content.'<br><br>'.'Нет информации</div>';
    }

}
