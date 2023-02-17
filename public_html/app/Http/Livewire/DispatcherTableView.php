<?php

namespace App\Http\Livewire;

use App\Models\Dispatcher;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use App\Actions\DeleteDispatcherAction;
use App\Actions\DeleteDispatchersAction;
use LaravelViews\Views\Traits\WithAlerts;
use App\Filters\UsersActiveFilter;
use LaravelViews\Actions\RedirectAction;

class DispatcherTableView extends TableView
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
        ];
    }

    public function row(Dispatcher $user)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            UI::link($user->name, route('disp-edit', $user)), //$user->name,
            UI::editable($user, 'phone'),
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $cabinet = \App\Models\Cabinet::curCabinet();
        return Dispatcher::query()->where('cabinet_id', $cabinet->id)->where('id_cms_privileges', 5);
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new DeleteDispatcherAction,
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            //new ActivateUsersAction,
            new DeleteDispatchersAction,
        ];
    }

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(Dispatcher $user, $data)
    {
        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
            try {
                if ($user->save()) $this->success('Телефон сохранен!');
                else $this->error('Не удались сохранить телефон!');
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
            //new UsersActiveFilter,
            //new CreatedFilter,
            //new UsersTypeFilter
        ];
    }
}
