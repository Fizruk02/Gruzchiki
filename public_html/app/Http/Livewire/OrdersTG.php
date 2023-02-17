<?php

namespace App\Http\Livewire;

use App\Actions\DeleteEmployeeAction;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Views\TableView;

class OrdersTG extends TableView
{
    protected $modelClass = \App\Models\Users::class;

    protected $num = 1;
    //protected $model = Users::class;
    public $order_id = null;
    public $status_id = null;

    protected $cabinet = null;
    public $model = null;
    public $searchBy = [];

    public $itemComponent = 'components.orders-tg';

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.orders-tg", $data);
    }

    public function getPaginatedQueryProperty()
    {
        return $this->query->paginate($this->paginate, ['*'], 'userOrderPage'.$this->order_id.$this->status_id);
    }

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('Телефон')->sortBy('users.phone'),
            Header::title('ФИО')->sortBy('users.name'),
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        //dd($this->status_id);
        //dd($this->filters);
        //orders-users-status-filter
        if (!isset($this->filters['orders-users-status-filter']))
            $this->filters['orders-users-status-filter'] = 3;

        $order_id = $this->order_id;
        $query = Users::query()
            ->select([
                'users.*', 'orders_users.id as ouid', 'orders_users.status', 'orders_users.brigadier', 'orders_users.approved'
            ])
            ->join('orders_users', function ($join) USE ($order_id) {
                $join->on('users.id', '=', 'orders_users.user_id')->where('orders_users.order_id', $order_id);
            })
            //->join('orders_users', 'users.id', '=', 'orders_users.user_id')
            ->where('users.id_cms_privileges', Users::ROLE_USER)
            ->whereRaw('`users`.`cabinet_id` IS NOT NULL');
        if ($this->status_id) $query->where('orders_users.status', '>', 0);
        else $query->where('orders_users.status', '=', 0);

        if (!$this->sortBy) $query->orderBy('id', 'desc');
        return $query;
    }

    public function row(Users $users)
    {
        if ($users->status < -1) $date = date(' d.m.Y', $users->black_at);

        $name = $users->name;
        if (!$this->status_id) {
            $name = $users->is_deleted ?
                '<span class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest transition float-right">Удален</span>' :
                '<button wire:click.prevent="executeAction(\'delete-employee-action\', '.$users->id.')" class="inline-flex items-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition float-right">Удалить контакт</button>';
            $name .= '<div class="py-3">'.$users->name.'</div>';
            $name .= '<hr>';
            $name .= '<span class="font-bold">Причина:</span> Пользователь заблокировал бот';
        }
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            @$users->phone,
            $name,
        ];
    }

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function actionsByRow()
    {
        return [
            new DeleteEmployeeAction(),
        ];
    }
}
