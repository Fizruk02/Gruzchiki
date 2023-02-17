<?php

namespace App\Http\Livewire;

use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use App\Actions\ActivateUserAction;
use App\Actions\ActivateUsersAction;
use App\Actions\DeleteUserAction;
use App\Actions\DeleteUsersAction;
use LaravelViews\Views\Traits\WithAlerts;
use App\Filters\UsersActiveFilter;
use LaravelViews\Actions\RedirectAction;

class UsersTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = User::class;

    protected $paginate = 20;

    public $searchBy = ['name', 'phone'];

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('ФИО')->sortBy('name'),
            Header::title('Телефон')->sortBy('phone'),
            Header::title('Администратор')->sortBy('phone'),
            Header::title('Город')->sortBy('phone'),
            Header::title('Комментарий')->sortBy('phone'),
            Header::title('Статус')->sortBy('status'),
        ];
    }

    public function row(User $user)
    {
        $role = match ($user->role_id) {
            0 => 'Гость',
            1 => 'Пользователь',
            100 => 'Админ',
            default => 'Гость'
        };
        $class = match ($user->role_id) {
            0 => 'default',
            1 => 'success',
            100 => 'danger',
            default => 'warning'
        };
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            //$user->id,
            UI::link($user->name, route('cabinets', $user)).' '.UI::badge($role, $class), //$user->name,
            UI::editable($user, 'phone'),
            UI::editable($user, 'phone'),
            UI::editable($user, 'phone'),
            UI::editable($user, 'phone'),
            //$user->email,
            $user->status ? UI::icon('check', 'success') : '',
            //$model->created_at,
            //$model->updated_at
        ];
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new ActivateUserAction,
            new DeleteUserAction,
            new RedirectAction('cabinets', 'Просмотр', 'eye'),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            new ActivateUsersAction,
            new DeleteUsersAction,
        ];
    }

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(User $user, $data)
    {
        $user->update($data);
        $this->success();
    }

    protected function filters()
    {
        return [
            new UsersActiveFilter,
            //new CreatedFilter,
            //new UsersTypeFilter
        ];
    }
}
