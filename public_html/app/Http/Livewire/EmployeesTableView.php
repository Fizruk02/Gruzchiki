<?php

namespace App\Http\Livewire;

use App\Actions\ExelExportAction;
use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Users;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use App\Actions\ActivateUserAction;
use App\Actions\ActivateUsersAction;
use App\Actions\DeleteUserAction;
use App\Actions\DeleteUsersAction;
use LaravelViews\Views\Traits\WithActions;
use LaravelViews\Views\Traits\WithAlerts;
use App\Filters\UsersActiveFilter;
use App\Filters\UsersAdminFilter;
use LaravelViews\Actions\RedirectAction;
use App\Http\Livewire\WithExport;

class EmployeesTableView extends TableView
{
    use WithAlerts;
    use WithActions;
    use WithExport;

    protected $num = 1;
    protected $model = Users::class;

    protected $paginate = 20;

    //public $searchBy = ['users.name', 'users.phone', 'users_profiles.comment', 'users_profiles.city', 'cabinet.users.name'];
    public $searchBy = ['users.name', 'users.phone', 'users_profiles.comment', 'users_profiles.city'];

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('ФИО')->sortBy('users.name'),
            Header::title('Телефон')->sortBy('users.phone'),
            Header::title('Администратор')->sortBy('admin_name'),
            Header::title('Город')->sortBy('users_profiles.city'),
            Header::title('Комментарий')->sortBy('users_profiles.comment'),
            Header::title('Статус')->sortBy('users.status'),
            Header::title('Дата')->sortBy('users.black_at'),
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        return Users::query()
            ->select([
                'users.*',
                'users_profiles.city', 'users_profiles.comment',
                'cabinet.users_id',
                'admin.name as admin_name'
            ])
            ->with(['cabinet', 'users_profiles'])
            ->join('users_profiles', 'users.id', '=', 'users_profiles.users_id')
            ->join('cabinet', 'users.cabinet_id', '=', 'cabinet.id')
            ->join('users as admin', 'cabinet.users_id', '=', 'admin.id')
            ->where('users.id_cms_privileges', Users::ROLE_USER)
            ->whereRaw('`users`.`cabinet_id` IS NOT NULL');
    }

    public function row(Users $users)
    {
        $status = match ($users->status) {
            0 => 'check',
            -1 => 'check',
            -20 => 'eye-off',
            default => 'alert-circle'
        };
        $class = match ($users->status) {
            0 => 'success',
            -1 => 'warning',
            -20 => 'danger',
            default => 'default'
        };
        $date = '';
        if ($users->status < -1) $date = date(' d.m.Y', $users->black_at);

        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            //$users->id,
            UI::link($users->name, route('users-view', $users)),
            @$users->phone,
            @$users->cabinet->users->name,
            @$users->users_profiles->city,
            //'usersProfiles.city',
            //UI::editable($user, 'phone'),
            @$users->users_profiles->comment,
            UI::icon($status, $class),
            $date,
            //$model->created_at,
            //$model->updated_at
        ];
    }

    public function headersExport(): array
    {
        return [
            '#',
            'ФИО',
            'Телефон',
            'Администратор',
            'Город',
            'Комментарий',
            'Статус',
            'Дата',
        ];
    }

    public function rowExport(Users $users)
    {
        $status = match ($users->status) {
            0 => 'Активен',
            -1 => 'Новый',
            -20 => 'Забанен',
            default => 'Черный список'
        };
        $date = '';
        if ($users->status < -1) $date = date('d.m.Y', $users->black_at);
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            $users->name,
            @$users->phone,
            @$users->cabinet->users->name,
            @$users->users_profiles->city,
            @$users->users_profiles->comment,
            $status,
            $date,
            //$model->created_at,
            //$model->updated_at
        ];
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            //new ActivateUserAction,
            //new DeleteUserAction,
            //new RedirectAction('cabinets', 'Просмотр', 'eye'),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            //new ActivateUsersAction,
            //new DeleteUsersAction,
            //new ExelExportAction,
        ];
    }

    /** For export actions */
    protected function exportActions()
    {
        return [
            new ExelExportAction(),
        ];
    }

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(Users $user, $data)
    {
        $user->update($data);
        $this->success();
    }

    protected function filters()
    {
        return [
            new UsersActiveFilter,
            new UsersAdminFilter,
            //new CreatedFilter,
            //new UsersTypeFilter
        ];
    }
}
