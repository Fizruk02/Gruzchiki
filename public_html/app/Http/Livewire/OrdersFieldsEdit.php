<?php

namespace App\Http\Livewire;

use App\Actions\DeleteEmployeeAction;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrdersController;
use App\Models\OrdersFields;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use LaravelViews\Facades\UI;

class OrdersFieldsEdit extends EditView
{
    protected $modelClass = \App\Models\Orders::class;
    protected $cabinet = null;
    protected $fields = null;

    public function heading(\App\Models\Orders $model)
    {
        return [
            $model->id ? 'Заказ #'.$model->number : 'Новый заказ',
            "Редактирование",
        ];
    }

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function getFields() {
        $cabinet = $this->getCabinet();
        if ($this->fields) return $this->fields;
        return $this->fields = OrdersFields::where('cabinet_id', $cabinet->id)->orderBy('sort')->get();
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the edit data or the components
     */
    public function edit($model, $params)
    {
        $cUser = new OrdersController();

        $cabinet = \App\Models\Cabinet::curCabinet();
        //$cabinet = \App\Models\Cabinet::where('users_id', 1000)->first();
        //dd($cabinet);
        //$bots =
        $fields = $this->getFields();

        $form = [];
        $form[] = [
            'label' => 'Заказ для бота',
            'name' => 'bot_id',
            'required' => true,
            'validation' => 'required|integer',
            'type' => 'select2',
            'datatable' => 'bot,name',
            'datatable_where' => 'cabinet_id = '.$cabinet->id,
            //'value' => $rv->value,
            'group_class' => 'w-full',
        ];
        $html = '<div class="w-full"><span class="text-danger mx-3" title="Это поле обязательное">*</span> Поля помеченные звездочкой обязательны для заполнения</div>
                 <div class="w-full"><div class="mx-1 float-left text-yellow-200" title="Виден всем">'.UI::icon('circle', 'default').'</div><div class="mx-1 float-left">Поля </div> отправляются вашим контактам</div>';
        $form[] = [
            'name' => 'html',
            'type' => 'block',
            'code' => '<li class="px-4 py-2 border-b border-gray-200 sm:flex sm:items-center form-group flex-wrap bg-blue-100 w-full">'.$html.'</li>',
            'group_class' => 'w-full bg-blue-100',
        ];
        foreach ($fields as $field) {
            $help = null;
            if ($field->is_first || $field->is_accept /*|| $field->is_2hours || $field->is_30minutes*/) {
                $helps = [];
                if ($field->is_first) $helps[] = 'всем';
                if ($field->is_accept) $helps[] = 'принявшим';
                //if ($field->is_2hours) $helps[] = 'за 2 часа';
                //if ($field->is_30minutes) $helps[] = 'за 30 минут';
                $help = 'Виден: ' . implode(', ', $helps);
            }

            $type = 'text';
            if($field->type == 'tasks') $type = 'textarea';
            else if ($field->type == 'work_day_at') $type = 'date';

            if ($model->id) {
                foreach ($model->orders_values as $rv) {
                    if ($rv->orders_fields_id == $field->id) {
                        $form[] = [
                            'label' => $field->is_label ? $field->name : null,
                            'name' => $field->type,
                            'required' => $field->is_require ? true : false,
                            'validation' => $field->is_require ? 'required|max:1000' : 'max:1000',
                            'type' => $type,
                            'value' => $rv->value,
                            //'help' => $help,
                            //'html' => $help ? true : false,
                            'group_class' => $field->class.($help ? ' bg-yellow-200' : ''),
                            'placeholder' => $field->placeholder,
                            'title' => $help,
                        ];
                        break;
                    }
                }
            } else {
                $value = null;
                if ($field->type == 'work_day_at') $value = date('d.m.Y');
                else if ($field->type == 'work_time_at') $value = date('H:i');

                $form[] = [
                    'label' => $field->is_label ? $field->name : null,
                    'name' => $field->type,
                    'required' => $field->is_require ? true : false,
                    'validation' => $field->is_require ? 'required' : '',
                    'type' => $type,
                    'value' => $value,
                    //'help' => $help,
                    //'html' => $help ? true : false,
                    'group_class' => $field->class.($help ? ' bg-yellow-200' : ''),
                    'placeholder' => $field->placeholder,
                    'title' => $help,
                ];
            }
        }

        $form[] = [
            'label' => 'Выручка',
            'name' => 'profit',
            'required' => true,
            'validation' => 'required|integer',
            'type' => 'text',
            'group_class' => 'sm:w-4/12',
            'value' => $model->id ? $model->balance->profit: 0,
        ];
        $form[] = [
            'label' => 'Затраты',
            'name' => 'expense',
            'required' => true,
            'validation' => 'required|integer',
            'type' => 'text',
            'group_class' => 'sm:w-4/12',
            'value' => $model->id ? $model->balance->expense: 0,
        ];
        $form[] = [
            'label' => 'Долг по заказу',
            'name' => 'debt',
            'required' => true,
            'validation' => 'required|integer',
            'type' => 'text',
            'group_class' => 'sm:w-4/12',
            'value' => $model->id ? $model->balance->debt: 0,
        ];
        $form[] = [
            'label' => 'Коментарии к заказу',
            'name' => 'comments',
            'required' => true,
            'validation' => '',
            'type' => 'textarea',
            'group_class' => 'w-full',
            'value' => $model->id ? $model->balance->comments: '',
        ];

        $cUser->fields = $form;
        $cUser->baseFields = $fields;
        $cUser->ul_class = 'flex flex-wrap';
        $cUser->cbInit();
        $cUser->return_url = $params['return_url'];

        if ($model->id) $cUser->custom_component = ['component' => 'orders-result', 'params' => ['model' => $model]];

        config(['crudbooster.ADMIN_PATH' => '']);

        if ($model->id) {
            if(Request::method() == 'POST') return $cUser->postEditSave($model->id);
            return $cUser->getEditModel($model)->render();
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
