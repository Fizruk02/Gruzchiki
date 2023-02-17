<?php

namespace App\Http\Livewire;

use App\Http\controllers\AdminCmsUsersController;
use LaravelViews\Views\DetailView;
use App\Models\User;
use App\Actions\ActivateUserAction;
use App\Actions\ListAction;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;
use Illuminate\Support\Facades\Route;

class UserEdit extends EditView
{
    protected $modelClass = \App\Models\User::class;

    public function heading(User $model)
    {
        return [
            "Администратор {$model->name}",
            "Профиль пользователя",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the edit data or the components
     */
    public function edit($model)
    {
        /*return [
            'Имя' => $model->name,
            'Email' => $model->email,
            'Статус' => $model->status ? UI::icon('check', 'success') : UI::icon('slash', 'danger'),
            'Создан' => $model->created_at ? $model->created_at->diffForHumans() : '-',
            'Обновлен' => $model->updated_at ? $model->updated_at->diffForHumans() : '-',
        ];*/

        $cUser = new AdminCmsUsersController();
        $cUser->cbInit();
        //dd($cUser->getProfile());
        /*Route::get('/', function (\Illuminate\Http\Request $request) {
            return '';
        })->name('GetIndex');*/
        //$r = Route::getRoutes()->getByName('GetIndex');
        //dd(Route::getRoutes());
        //dd($model);
        //$html = $cUser->getEdit($model->id)->render();
        $id = $model->id;
        $row = $model;
        $page_menu = '';
        $page_title = '';
        $command = '';
        $forms = $cUser->form;
        $button_save = 'Сохранить';
        $button_cancel = 'Отмена';

        $html = view('crudbooster::include.form',
            compact('id', 'row', 'page_menu', 'page_title', 'command',
                'forms', 'button_save')
        );
        //dump($html);
        //dd($html->render());
        return $html;
    }

    public function actions()
    {
        return [
            new ActivateUserAction,
        ];
    }
}
