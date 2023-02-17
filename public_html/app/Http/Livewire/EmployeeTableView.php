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

class EmployeeTableView extends TableView
{
    use WithAlerts;

    protected $cabinet = null;
    protected $bots = null;

    protected $num = 1;
    protected $model = Employee::class;

    protected $paginate = 20;

    public $searchBy = ['name', 'phone'];

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->getCabinet();
        $this->getBots();
    }

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function getBots() {
        if ($this->bots) return $this->bots;
        $bots = Bot::select(['id', 'name'])->where('cabinet_id', $this->getCabinet()->id)->get();
        $select = [];
        foreach ($bots as $bot) {
            $select[$bot->id] = $bot->name;
        }
        $this->bots = $select;
        return $this->bots;
    }

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('ФИО')->sortBy('name'),
            Header::title('Телефон')->sortBy('phone'),
            Header::title('Рейтинг')->sortBy('reiting'),
            Header::title('ЧС')->sortBy('reiting'),
            Header::title('Бот')->sortBy('reiting'),
        ];
    }

    public function row(Employee $user)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            UI::link($user->name, route('employee-edit', $user)), //$user->name,
            UI::editable($user, 'phone'),
            (($user->status != Users::STATUS_WAIT) && ($user->status != Users::STATUS_NEW)) ? CRUI::number($user, 'reiting') : '',
            $user->status == Users::STATUS_BLACK ? '<span class="text-red-500 text-bold">'.date('d.m.Y', $user->black_at).'</span>' : '',
            //(@$user->bot->name,
            CRUI::select(@$user, 'bot_id', $this->bots),
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
            ->where('cabinet_id', $cabinet->id)
            ->where('is_deleted', 0)
            ->where('status', '<>', Users::STATUS_BAN)
            ->where('id_cms_privileges', Users::ROLE_USER);
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new ApproveEmployeeAction(),
            new ActiveEmployeeAction(),
            new BlackEmployeeAction(),
            new BanEmployeeAction(),
            new DeleteEmployeeAction(),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            //new ActivateUsersAction,
            //new ApproveEmployeesAction(),
            new DeleteEmployeesAction(),
        ];
    }

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(Employee $user, $data)
    {
        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
            try {
                if ($user->save()) $this->success('Телефон сохранен!');
                else $this->error('Не удалось сохранить телефон!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else if (isset($data['reiting'])) {
            $user->reiting = $data['reiting'];
            try {
                if ($user->save()) $this->success('Рейтинг сохранен!');
                else $this->error('Не удалось сохранить рейтинг!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else if (isset($data['bot_id'])) {
            $user->bot_id = $data['bot_id'];
            try {
                if ($user->save()) $this->success('Бот сохранен!');
                else $this->error('Не удалось сохранить бота!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        //$user->update($data);
        //$this->success();
    }

    protected function filters()
    {
        return [
            new UsersStatusFilter(),
            //new CreatedFilter,
            //new UsersTypeFilter
        ];
    }
}
