<?php

namespace App\Http\Livewire;

use App\Actions\ExelExportAction;
use App\Constructor\Facades\CRUI;
use App\Filters\OrdersUsersStatusFilter;
use App\Models\Orders;
use App\Models\OrdersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Users;
use LaravelViews\Facades\Header;
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
use LaravelViews\Facades\UI;

class OrdersUserTableView extends TableView
{
    use WithAlerts;
    use WithActions;

    protected $num = 1;
    protected $model = Users::class;
    public $order_id = null;

    public $filters = ['orders-users-status-filter' => 3];
    protected $paginate = 1000;

    //public $searchBy = ['users.name', 'users.phone', 'users_profiles.comment', 'users_profiles.city', 'cabinet.users.name'];
    public $searchBy = ['users.name', 'users.phone', 'users_profiles.comment', 'users_profiles.city'];

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.orders-users", $data);
    }

    public function getPaginatedQueryProperty()
    {
        return $this->query->paginate($this->paginate, ['*'], 'userPage'.$this->order_id);
    }

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('Телефон')->sortBy('users.phone'),
            Header::title('ФИО')->sortBy('users.name'),
            Header::title('Рейтинг')->sortBy('users.reiting'),
            Header::title('Напомнить'),
            Header::title('Утвержден'),
            Header::title('Ответственный'),
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        //dd($this->filters);
        //orders-users-status-filter
        if (!isset($this->filters['orders-users-status-filter']))
            $this->filters['orders-users-status-filter'] = 3;

        $order_id = $this->order_id;
        $query = Users::query()
            ->select([
                'users.*', 'orders_users.id as ouid', 'orders_users.status',
                'orders_users.brigadier', 'orders_users.approved', 'orders_users.remember'
            ])
            ->join('orders_users', function ($join) USE ($order_id) {
                $join->on('users.id', '=', 'orders_users.user_id')->where('orders_users.order_id', $order_id);
            })
            //->join('orders_users', 'users.id', '=', 'orders_users.user_id')
            ->where('users.id_cms_privileges', Users::ROLE_USER)
            ->whereRaw('`users`.`cabinet_id` IS NOT NULL');

        if (!$this->sortBy) $query->orderBy('id', 'desc');
        return $query;
    }

    public function row(Users $users)
    {
        if ($users->status < -1) $date = date(' d.m.Y', $users->black_at);
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            Auth::user()->id_cms_privileges == Users::ROLE_DISPETCHER ? UI::link($users->phone, '/admin/employees/view/'.$users->id) : UI::link($users->phone, '/admin/employees/edit/'.$users->id),
                //'<a href="/employees/view/'.$users->id.'" target="_blank">'.$users->phone.'</a>' :
                //'<a href="/employees/edit/'.$users->id.'" target="_blank">'.$users->phone.'</a>',
            $users->name,
            CRUI::number($users, 'reiting'),
            CRUI::check($users, 'remember'),
            CRUI::check($users, 'approved', $this->getColor($users->status), $this->getTitle($users->status)),
            CRUI::check($users, 'brigadier'),
            //$users->reiting
        ];
    }

    public function getColor($status)
    {
        if ($status == 3) return 'text-gray-700';     //$title='Подтвердил заказ';
        if ($status == 4) return 'text-gray-700';     //$title='Одобрен';}
        if ($status == 5) return 'text-yellow-500';   //$title='Выехал';}
        if ($status == 6) return 'text-green-500';    //$title='На месте';}
        if ($status == 7) return 'text-blue-500';     //$title='Заказ выполнен';}
        if ($status == 8) return 'text-purple-500';   //$title='Оплата получена';}
        if ($status == 100) return 'text-red-500';    //$title='Возникла проблема';}

        return null;
    }

    public function getTitle($status)
    {
        if ($status == 3) return 'Подтвердил заказ';
        if ($status == 4) return 'Одобрен';
        if ($status == 5) return 'Выехал';
        if ($status == 6) return 'На месте';
        if ($status == 7) return 'Заказ выполнен';
        if ($status == 8) return 'Оплата получена';
        if ($status == 100) return 'Возникла проблема';

        return null;
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

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(Users $user, $data)
    {
        if (isset($data['reiting'])) {
            $user->reiting = $data['reiting'];
            try {
                if ($user->save()) $this->success('Рейтинг сохранен!');
                else $this->error('Не удалось сохранить рейтинг!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else if (isset($data['approved']) || isset($data['brigadier'])) {
            try {
                if (DB::table('orders_users')->where('order_id', $this->order_id)->where('user_id', $user->id)->update($data)) {
                    $users = OrdersUsers::where('order_id', $this->order_id)->where('status', '=>', 4)->first();
                    if ($users) $user->sendMessage('У нас есть работа для Вас. Ожидайте звонка от менеджера');
                    $this->success('Cтатус сохранен!');
                } else
                    $this->error('Не удалось сохранить статус!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else if (isset($data['remember'])) {
            try {
                if (!DB::table('orders_users')->where('order_id', $this->order_id)->where('user_id', $user->id)->update($data))
                    $this->error('Не удалось сохранить статус!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
//        $user->update($data);
//        $this->success();
    }

    protected function filters()
    {
        return [
            new OrdersUsersStatusFilter(),
        ];
    }
}
