<?php

namespace App\Http\Livewire;

use App\Constructor\Facades\CRUI;
use LaravelViews\Views\DetailView;
use App\Models\Users;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;

class EmployeesDetailView extends DetailView
{
    //public $title = "Title";
    //public $subtitle = "Subtitle or description";

    protected $modelClass = \App\Models\Employee::class;

    public function heading(Users $model)
    {
        //dd($model);
        return [
            //'', ''
            "{$model->name}",
            "Профиль пользователя",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the detail data or the components
     */
    public function detail(Users $model)
    {
        return [
            'Фамилия' => $model->users_profiles->f,
            'Имя' => $model->users_profiles->i,
            'Отчество' => $model->users_profiles->o,
            'Телефон' => $model->phone,
            'Город' => $model->users_profiles->city,
            'Район проживания' => $model->users_profiles->district,
            'Email' => $model->email,
            //'Статус' => '<b><span class="text-'.$class.'">'.$status.'</span></b>',
            'Паспорт' => $model->users_profiles->passport,
            'Снилс' => $model->users_profiles->snils,
            'Комментарий' => $model->users_profiles->comment,

            'Родился' => date('d.m.Y', $model->users_profiles->birthday_at),
            'Специализация' => $model->users_profiles->special,
            'Гражданство РФ' => $model->users_profiles->is_rf ? UI::icon('check', 'success') : UI::icon('x', 'danger'), //UI::check($model->users_profiles, 'is_rf'),//$model->users_profiles->is_rf ? Check::'Есть' : 'Нет',
            'Трудоустроен' => $model->users_profiles->is_worker ? 'Да' : 'Нет',
            'Место работы' => $model->users_profiles->is_worker ? $model->users_profiles->work : '-',
            'Семейное положение' => $model->users_profiles->family ? 'Женат' : 'Холост',
            'Дети' => $model->users_profiles->children ? $model->users_profiles->children : '-',
            'В какие дни и время готов работать' => $model->users_profiles->times,
            'Опыт работы грузчиком, такелажником, в строительстве' => $model->users_profiles->experience ? $model->users_profiles->experience : '-',
            'Наличие судимости' => $model->users_profiles->is_criminal ? UI::icon('check', 'danger') : UI::icon('x', 'success'),// $model->users_profiles->is_criminal ? 'Есть' : 'Нет',
            'Наличие автомобиля' => $model->users_profiles->is_car ? UI::icon('check', 'success') : UI::icon('x', 'danger'),// ? 'Есть' : 'Нет',

            'Создан' => $model->created_at ? $model->created_at->diffForHumans() : '-',
            'Обновлен' => $model->updated_at ? $model->updated_at->diffForHumans() : '-',
        ];
    }

    public function actions()
    {
        return [
            //new EditUserAction,
            //new RedirectAction('admin-user-edit', 'Редактировать', 'edit'),
            //new RedirectAction('admin-users', 'Список пользователей', 'list'),
        ];
    }
}
