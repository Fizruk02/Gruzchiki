<?php

namespace App\Http\Livewire;

//use App\Actions\DeleteRequestAction;
//use App\Actions\DeleteRequestsAction;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\OrdersFields;
use App\Models\OrdersValues;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Actions\RedirectAction;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\TableView;
use LaravelViews\Views\Traits\WithAlerts;

class ClientTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = OrdersValues::class;
    protected $cabinet = null;

    protected $paginate = 20;

    public $searchBy = ['orders_values.value', 'nv.value'];

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function headers(): array
    {
        return [
            '#',
            Header::title('Имя заказчика')->sortBy('name'),
            Header::title('Номер телефона')->sortBy('phone'),
            Header::title('Количество заказов')->sortBy('count'),
        ];
    }

    public function row(OrdersValues $ov)
    {
        $row = [
            ($this->page -  1) * $this->paginate + $this->num++,
            UI::link($ov->name, route('clients-view', $ov->phone)),
            $ov->phone,
            $ov->count,
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
        $cabinet = $this->getCabinet();
        //dd($cabinet->id);

        $name_id = OrdersFields::where('type', 'client_name')->where('cabinet_id', $cabinet->id)->first()->id;
        $phone_id = OrdersFields::where('type', 'client_phone')->where('cabinet_id', $cabinet->id)->first()->id;
        //dd($fields->id);

        $select = [
            'orders_values.value as phone',
            //'orders.id as oid',
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
        if (!$this->sortBy) {
            $r->orderBy('name');
        }

        return $r;
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            //new RedirectAction('orders-view', 'Просмотр', 'eye'),
            //new RedirectAction('orders-edit', 'Редактировать', 'edit'),
            //new DeleteRequestAction(),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            //new DeleteRequestsAction(),
        ];
    }

    protected function filters()
    {
        return [
            //new UsersActiveFilter,
        ];
    }
}
