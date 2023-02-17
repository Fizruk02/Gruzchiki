<?php

namespace App\Http\Livewire;

use LaravelViews\Views\DetailView;
use App\Models\Users;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;

class UsersDetailView extends DetailView
{
    //public $title = "Title";
    //public $subtitle = "Subtitle or description";

    protected $modelClass = \App\Models\Users::class;

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

        $status = match ($model->status) {
            0 => 'Активен',
            -1 => 'Новый',
            -20 => 'Забанен',
            default => 'Черный список'
        };
        $class = match ($model->status) {
            0 => 'success',
            -1 => 'warning',
            -20 => 'danger',
            default => 'danger'
        };

        return [
            'Фамилия' => $model->users_profiles->f,
            'Имя' => $model->users_profiles->i,
            'Отчество' => $model->users_profiles->o,
            'Телефон' => $model->phone,
            'Город' => $model->users_profiles->city,
            'Район проживания' => $model->users_profiles->district,
            'Email' => $model->email,
            'Статус' => '<b><span class="text-'.$class.'">'.$status.'</span></b>',
            'Паспорт' => $model->users_profiles->passport,
            'Снилс' => $model->users_profiles->snils,
            'Комментарий' => $model->users_profiles->comment,

            'Родился' => date('d.m.Y', $model->users_profiles->birthday_at),
            'Специализация' => $model->users_profiles->special,
            'Гражданство РФ' => $model->users_profiles->is_rf ? 'Есть' : 'Нет',
            'Трудоустроен' => $model->users_profiles->is_worker ? 'Да' : 'Нет',
            'Место работы' => $model->users_profiles->is_worker ? $model->users_profiles->work : '-',
            'Семейное положение' => $model->users_profiles->family ? 'Женат' : 'Холост',
            'Дети' => $model->users_profiles->children ? $model->users_profiles->children : '-',
            'В какие дни и время готов работать' => $model->users_profiles->times,
            'Опыт работы грузчиком, такелажником, в строительстве' => $model->users_profiles->experience ? $model->users_profiles->experience : '-',
            'Наличие судимости' => $model->users_profiles->is_criminal ? 'Есть' : 'Нет',
            'Наличие автомобиля' => $model->users_profiles->is_car ? 'Есть' : 'Нет',

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
