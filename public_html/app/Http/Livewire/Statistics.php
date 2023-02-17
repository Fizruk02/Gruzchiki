<?php

namespace App\Http\Livewire;

use App\Constructor\Facades\CRUI;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrdersController;
use App\Models\Bot;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\OrdersFields;
use App\Models\OrdersValues;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\ListView;
use LaravelViews\Views\TableView;
use LaravelViews\Views\View;

class Statistics extends View
{
    public $period_id = 0;
    public $city_id = null;
    public $cabinet_id = 0;

    public $profit = null;

    protected $cabinet = null;
    public $model = null;

    public $itemComponent = 'components.statistics';

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.statistics", $data);
    }

    public function getPaginatedQueryProperty()
    {
        return null;
    }

    /*public function mount(): void
    {

        $this->form->fill([
            'bot_id' => $this->bot_id,

            //'content' => $this->post->content,

        ]);

    }*/

    public function submit()
    {
        $validatedData = $this->validate([
            'bot_id' => 'required',
            //'email' => 'required|email',
            //'body' => 'required',
        ]);

        /*Contact::create($validatedData);*/

        //return redirect()->to('/accounting');
    }

    /**
     * Collects all data to be passed to the view, this includes the items searched on the database
     * through the filters, this data will be passed to livewire render method
     */
    protected function getRenderData()
    {
        $period_id = intval(\request()->get('period_id'));
        $city_id = \request()->get('city_id');
        $cabinet_id = intval(\request()->get('cabinet_id'));
        //$clients = intval(\request()->get('clients'));

        if (\request()->isMethod('GET')) {
            $this->period_id = $period_id;
            $this->city_id = $city_id;
            $this->cabinet_id = $cabinet_id;
        }

        $select = [
            'us.name as name',
        ];

        /*$result = Cabinet::query()->select($select)
            ->addSelect(DB::raw('count(od.id) as `count_orders`'))
            ->addSelect(DB::raw('count(uo.id) as `count_workers`'))
            ->addSelect(DB::raw('sum(ob.profit) as `count_profit`'))
            ->addSelect(DB::raw('sum(ob.expense) as `count_expense`'))
            ->join('users as us', 'us.id', 'cabinet.users_id')
            ->join('orders as od', 'od.cabinet_id', 'cabinet.id')
            ->join('orders_balance as ob', 'od.id', 'ob.orders_id')
            ->join('orders_users as uo', function ($join) {
                $join->on('od.id', '=', 'uo.order_id');
                $join->where('uo.status', '>=', 4);
            })
            ->groupBy(['name'])
            ->get();*/

        $periods = [
            1 => 'Месяц',
            2 => 'Квартал',
            3 => 'Полугодие',
            4 => 'Год',
        ];

        $admins = [];
        $cabinets = Cabinet::selectRaw('cabinet.id, users.name')->join('users', 'users.id', 'cabinet.users_id')->get();
        foreach ($cabinets as $cabinet) {
            $admins[$cabinet->id] = $cabinet->name;
        }

        $filters = [];
        $cityes = Cabinet::select('city')->distinct()->get();
        foreach ($cityes as $city) {
            if(!$city->city) continue;
            $filters[$city->city] = $city->city;
        }
        $cityes = $filters;

        /*$clients = [];
        $name_id = OrdersFields::where('type', 'client_name')->where('cabinet_id', $cabinet->id)->first()->id;
        $phone_id = OrdersFields::where('type', 'client_phone')->where('cabinet_id', $cabinet->id)->first()->id;

        $select = [
            'orders_values.value as phone',
            'nv.value as name',
        ];
        OrdersValues::resolveRelationUsing('orders_values', function ($requestModel) {
            return $requestModel->hasOne(OrdersValues::class, 'id');
        });
        OrdersValues::resolveRelationUsing('nv', function ($requestModel) USE ($name_id) {
            return $requestModel->hasOne(OrdersValues::class, 'orders_id', 'orders_id')->where('orders_fields_id', $name_id);
        });
        $r = OrdersValues::query()->select($select)->addSelect(DB::raw('count(*) as `count`'))
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'orders_values.orders_id');
            })
            ->join('orders_values as nv', function ($join) USE ($name_id) {
                $join->on('orders.id', '=', 'nv.orders_id')->where('nv.orders_fields_id', $name_id);
            })
            ->where('orders.cabinet_id', $cabinet->id)
            ->where('orders_values.orders_fields_id', $phone_id)
        ;
        $r->groupBy(['phone', 'name']);
        /*if (!$this->sortBy) {
            $r->orderBy('name');
        }*/

        return [
            'periods' => $periods,
            'cabinets' => $admins,
            'cities' => $cityes,
            //'clients' => $clients,

            'period_id' => $period_id,
            'city_id' => $city_id,
            'cabinet_id' => $cabinet_id,
            //'clients_id' => $period_id,
            //'result' => $result,
        ];
    }

}
