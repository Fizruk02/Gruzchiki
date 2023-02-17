<?php

namespace App\Http\Livewire;

use App\Actions\ExelExportAction;
use App\Constructor\Facades\CRUI;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrdersController;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\OrdersFields;
use App\Models\OrdersValues;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use LaravelViews\Actions\Action;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\ListView;
use LaravelViews\Views\TableView;
use LaravelViews\Views\View;

class CabinetResultOne extends TableView
{
    public $period_id = null;
    public $cabinet_id = null;

    protected $num = 1;
    protected $model = Orders::class;

    protected $paginate = 20;

    //public $searchBy = ['name'];

    /*public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.cabinet-result", $data);
    }*/

    public function headers(): array
    {
        return [
            '#',
            Header::title('Заказ'),
            Header::title('Дата'),
            Header::title('Работников'),
            Header::title('Выручка'),
            Header::title('Сумма затрат')
        ];
    }

    public function row(Orders $orders)
    {
        //dump($orders->users);
        $count = 0;
        foreach ($orders->users as $user) {
            if($user->approved) $count++;
        }
        $row = [
            ($this->page -  1) * $this->paginate + $this->num++,
            $orders->name,//.' ('.$orders->id.')',
            date('d.m.Y H:i', $orders->time_at),
            //number_format($cabinet->count_orders, 0, '', ' '),
            number_format($count, 0, '', ' '),
            number_format($orders->balance->profit, 0, '', ' '),
            number_format($orders->balance->expense, 0, '', ' '),
        ];
        //UI::link($cabinet->users->name, route('cabinet-edit', $cabinet->id)), //$user->name,
        return $row;
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $this->sortBy = null;
        $of = OrdersFields::where('cabinet_id', $this->cabinet_id)->where('type', 'title')->first();
        $field_id = $of->id;

        $select = [
            'orders.*',
            'ov.value as name',
            //'ob.profit',
            //'ob.expense',
            //'ob.debt',
        ];

        $result = Orders::query()->select($select)->distinct()
            ->with('orders_values')
            ->with('balance')
            ->with('users')
            //->addSelect(DB::raw('count(uo.id) as `count_workers`'))
            //->addSelect(DB::raw('sum(ob.profit) as `count_profit`'))
            //->addSelect(DB::raw('sum(ob.expense) as `count_expense`'))
            //->addSelect(DB::raw('count(od.id) as `count_orders`'))
            //->addSelect(DB::raw('count(uo.id) as `count_workers`'))
            //->addSelect(DB::raw('sum(ob.profit) as `count_profit`'))
            //->addSelect(DB::raw('sum(ob.expense) as `count_expense`'))
            //->join('users as us', 'us.id', 'cabinet.users_id')
            //->join('orders as od', 'od.cabinet_id', 'cabinet.id')
            ->leftJoin('orders_users as uo', function ($join) {
                $join->on('orders.id', '=', 'uo.order_id');
                $join->where('uo.approved', 1);
            })
            ->join('orders_values as ov', function ($join) USE ($field_id) {
                $join->on('orders.id', '=', 'ov.orders_id');
                $join->where('ov.orders_fields_id', $field_id);
            })
            ->join('orders_balance as ob', 'orders.id', 'ob.orders_id');

        if ($this->period_id == 1) {
            $time = strtotime(date('Y-m-01 00:00:00'));
            $result->where('orders.time_at','>=', $time);
        } else if ($this->period_id == 2) {
            $m = date('m');
            $kv = intval($m / 3) * 3 + 1;
            if($kv < 10) $kv = '0'.$kv;
            $time = strtotime(date('Y-'.$kv.'-01 00:00:00'));
            $result->where('orders.time_at','>=', $time);
        } else if ($this->period_id == 3) {
            $m = date('m');
            if ($m >= 6) $m = '06';
            else $m = '01';
            $time = strtotime(date('Y-'.$m.'-01 00:00:00'));
            $result->where('orders.time_at','>=', $time);
        } else if ($this->period_id == 4) {
            $time = strtotime(date('Y-01-01 00:00:00'));
            $result->where('orders.time_at','>=', $time);
        }

        if ($this->cabinet_id) $result->where('orders.cabinet_id', $this->cabinet_id);

        $result->orderBy('time_at', 'desc');
        //$result->groupBy(['id']);
//dd($result->get());
        return $result;
    }

    public function getPaginatedQueryProperty()
    {
        return $this->query->paginate($this->paginate, ['*'], 'cabinetPage'.$this->cabinet_id);
    }

}
