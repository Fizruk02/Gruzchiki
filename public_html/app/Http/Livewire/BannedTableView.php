<?php

namespace App\Http\Livewire;

use App\Actions\ActivateUserAction;
use App\Actions\ActiveEmployeeAction;
use App\Actions\ApproveEmployeeAction;
use App\Actions\ApproveEmployeesAction;
use App\Actions\BanEmployeeAction;
use App\Actions\BlackEmployeeAction;
use App\Actions\DeleteEmployeeAction;
use App\Actions\DeleteEmployeesAction;
use App\Filters\UsersActiveFilter;
use App\Filters\UsersStatusFilter;
use App\Models\Bot;
use App\Models\Employee;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\Traits\WithAlerts;
use App\Constructor\Facades\CRUI;

class BannedTableView extends TableView
{
    use WithAlerts;

    protected $cabinet = null;
    protected $bots = null;

    protected $num = 1;
    protected $model = Employee::class;

    protected $paginate = 20;

    public $searchBy = ['name', 'phone', 'comment'];

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->getCabinet();
    }

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('ФИО')->sortBy('name'),
            Header::title('Телефон')->sortBy('phone'),
            Header::title('Дата постановки в бан')->sortBy('black_at'),
            Header::title('Комментарий')->sortBy('comment'),
        ];
    }

    public function row(Employee $user)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            UI::link($user->name, route('banned-edit', $user)), //$user->name,
            $user->phone,
            '<span class="text-red-500 text-bold">'.date('d.m.Y', $user->black_at).'</span>',
            @$user->users_profiles->comment
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $cabinet = $this->getCabinet();
        return Employee::query()
            ->select([
                'users.*',
                'users_profiles.comment as comment'
            ])
            ->with('users_profiles')
            ->join('users_profiles', 'users.id', '=', 'users_profiles.users_id')
            ->where('cabinet_id', $cabinet->id)
            ->where('status', '=', Users::STATUS_BAN)
            ->where('id_cms_privileges', Users::ROLE_USER);
    }
}
