<?php

namespace App\Http\Livewire;

use App\Actions\DeleteEmployeeAction;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Request;

class EmployeeEdit extends EditView
{
    protected $modelClass = \App\Models\Employee::class;

    public function heading(\App\Models\Employee $model)
    {
        return [
            $model->name ? $model->name : 'Создание',
            "Профиль сотрудника",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the edit data or the components
     */
    public function edit($model, $params)
    {
        $cUser = new EmployeeController();
        $cUser->cbInit();
        $cUser->return_url = $params['return_url'];

        config(['crudbooster.ADMIN_PATH' => '']);
        //if(Request::method() == 'POST') dd($model);

        /* @TODO Сделать автоматическую проверку и на лайваре */
        if (BTBooster::getModulePath() != 'livewire') {
            if ($model->id) {
                if(Request::method() == 'POST') return $cUser->postEditSave($model->id);
                return $cUser->getEditModel($model)->render();
            } else {
                if(Request::method() == 'POST') return $cUser->postAddSave($model->id);
                return $cUser->getAdd($model->id)->render();
            }
        } else {
            //parent::edit($model, $params);
            //return $cUser->getEditModel($model)->render();
            //$action = new DeleteEmployeeAction();
            //$action->view = $this;
            //$action->is_redirect = true;
            //$action->handle($model, $this);
            return $this->executeAction('delete-employee-action', $model->id);
        }
    }

    public function actions()
    {
        return [
            //new DeleteEmployeeAction(),
        ];
    }
}
