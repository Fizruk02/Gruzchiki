<?php

namespace App\Http\Livewire;

use App\Actions\ExelExportAction;
use App\Constructor\Facades\CRUI;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrdersController;
use App\Models\Cabinet;
use App\Models\OrdersBalance;
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

class CabinetResult extends TableView
{
    use WithExport;

    public $period_id = null;
    public $city_id = null;
    public $cabinet_id = null;

    protected $num = 1;
    protected $model = Cabinet::class;

    protected $paginate = 20;

    public $searchBy = ['name'];

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this,
                'period_id' => $this->period_id,
            ]
        );

        return view("components.cabinet-result", $data);
    }

    public function headers(): array
    {
        return [
            '#',
            Header::title('Кабинет')->sortBy('name'),
            Header::title('Заказов')->sortBy('count_orders'),
            Header::title('Работников')->sortBy('count_workers'),
            Header::title('Выручка')->sortBy('count_profit'),
            Header::title('Сумма затрат')->sortBy('count_expense')
        ];
    }

    public function headersExport(): array
    {
        return [
            '#',
            'Кабинет',
            'Заказов',
            'Работников',
            'Выручка',
            'Сумма затрат',
        ];
    }

    public function row(Cabinet $cabinet)
    {
        $row = [
            ($this->page -  1) * $this->paginate + $this->num++,
            '<a href="#" onclick="toggle(\'cab_order_'.$cabinet->id.'\');"><span class="text-blue-500">'.$cabinet->name.'</span></a>',//.' ('.$cabinet->id.')',
            number_format($cabinet->count_orders, 0, '', ' '),
            number_format($cabinet->count_workers, 0, '', ' '),
            number_format($cabinet->count_profit, 0, '', ' '),
            number_format($cabinet->count_expense, 0, '', ' '),
        ];
        //UI::link($cabinet->users->name, route('cabinet-edit', $cabinet->id)), //$user->name,
        return $row;
    }

    public function rowExport(Cabinet $cabinet)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            $cabinet->name,
            number_format($cabinet->count_orders, 0, '', ' '),
            number_format($cabinet->count_workers, 0, '', ' '),
            number_format($cabinet->count_profit, 0, '', ' '),
            number_format($cabinet->count_expense, 0, '', ' '),
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $select = [
            'cabinet.id as id',
            'us.name as name',
        ];

        $result = Cabinet::query()->select($select)->distinct()
            ->addSelect(DB::raw('count(DISTINCT od.id) as `count_orders`'))
            ->addSelect(DB::raw('SUM(count_user) as `count_workers`'))

            //->addSelect(DB::raw('(SELECT vp.profit FROM orders_balance as vp WHERE vp.orders_id = od.id) as count_profit'))
            //->addSelect(DB::raw('SUM(SELECT COUNT(*) as c FROM `orders_users` as ou WHERE ou.order_id = od.id AND ou.`status` >= 4) as `count_workers`'))
            ->addSelect(DB::raw('sum(ob.profit) as `count_profit`'))
            ->addSelect(DB::raw('sum(ob.expense) as `count_expense`'))
            ->join('users as us', 'us.id', 'cabinet.users_id')
            ->join('orders as od', 'od.cabinet_id', 'cabinet.id')
            ->join('orders_balance as ob', 'od.id', 'ob.orders_id')
            ->leftJoin(
                DB::raw('(SELECT order_id, count(id) as count_user FROM `orders_users` WHERE approved = 1 GROUP BY order_id) as uo'),
                'od.id', '=', 'uo.order_id'
            )
            /*->leftJoin('orders_users as uo', function ($join) {
                $join->on('od.id', '=', 'uo.order_id');
                $join->where('uo.approved', 1);
            })*/
        ;

        if ($this->period_id == 1) {
            $time = strtotime(date('Y-m-01 00:00:00'));
            $result->where('od.time_at','>=', $time);
        } else if ($this->period_id == 2) {
            $m = date('m');
            $kv = intval($m / 3) * 3 + 1;
            if($kv < 10) $kv = '0'.$kv;
            $time = strtotime(date('Y-'.$kv.'-01 00:00:00'));
            $result->where('od.time_at','>=', $time);
        } else if ($this->period_id == 3) {
            $m = date('m');
            if ($m >= 6) $m = '06';
            else $m = '01';
            $time = strtotime(date('Y-'.$m.'-01 00:00:00'));
            $result->where('od.time_at','>=', $time);
        } else if ($this->period_id == 4) {
            $time = strtotime(date('Y-01-01 00:00:00'));
            $result->where('od.time_at','>=', $time);
        }

        if ($this->city_id) $result->where('cabinet.city', $this->city_id);
        if ($this->cabinet_id) $result->where('cabinet.id', $this->cabinet_id);

        $result->groupBy(['id']);

        //dd($result->get());

        return $result;
    }

    /** For export actions */
    protected function exportActions()
    {
        return [
            new ExelExportAction(),
        ];
    }
}
