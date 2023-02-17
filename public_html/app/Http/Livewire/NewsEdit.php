<?php

namespace App\Http\Livewire;

use App\Actions\DeleteEmployeeAction;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrdersController;
use App\Models\OrdersFields;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use LaravelViews\Facades\UI;

class NewsEdit extends EditView
{
    protected $modelClass = \App\Models\News::class;
    protected $cabinet = null;

    public function heading(\App\Models\News $model)
    {
        return [
            $model->name ? $model->name : 'Новая новость',
            "Редактирование",
        ];
    }

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the edit data or the components
     */
    public function edit($model, $params)
    {
        $cUser = new NewsController();
        $cabinet = $this->getCabinet();

        $form = [];
        $form[] = [
            'label' => 'Новость для бота',
            'name' => 'bot_id',
            'required' => true,
            'validation' => 'required|integer',
            'type' => 'select2',
            'datatable' => 'bot,name',
            'datatable_where' => 'cabinet_id = '.$cabinet->id,
            //'value' => $rv->value,
            'group_class' => 'w-full',
        ];
        $form[] = [
            'label' => 'Заголовок',
            'name' => 'title',
            'required' => true,
            'validation' => 'required',
            'type' => 'text',
            'group_class' => 'w-full',
            'placeholder' => 'Заголовок (не отправляется работникам или пользователям)'
        ];
        $form[] = [
            'label' => 'Заголовок',
            'name' => 'description',
            'required' => true,
            'validation' => 'required',
            'type' => 'textarea',
            'group_class' => 'w-full',
            'placeholder' => 'Содержание (ОТПРАВЛЯЕТСЯ работникам или пользователям)'
        ];
        $cUser->fields = $form;
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
            //new DeleteEmployeeAction(),
        ];
    }
}
