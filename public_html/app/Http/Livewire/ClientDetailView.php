<?php

namespace App\Http\Livewire;

use App\Models\Orders;
use App\Models\OrdersFields;
use App\Models\OrdersValues;
use App\Models\Request;
use App\Models\RequestFields;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Views\DetailView;
use App\Models\Users;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;

class ClientDetailView extends DetailView
{
    //public $title = "Title";
    //public $subtitle = "Subtitle or description";

    protected $modelClass = \App\Models\OrdersValues::class;
    protected $cabinet = null;
    protected $fields = null;

    protected $orders = [];

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function getFields() {
        $cabinet = $this->getCabinet();
        if ($this->fields) return $this->fields;
        return $this->fields = OrdersFields::where('cabinet_id', $cabinet->id)->orderBy('sort')->get();
    }

    public function heading($model)
    {
        $phone_id = $model->orders_fields_id;
        $name_id = OrdersFields::where('type', 'client_name')->where('cabinet_id', $this->getCabinet()->id)->first()->id;
        //$title_id = OrdersFields::where('type', 'title')->where('cabinet_id', $this->getCabinet()->id)->first()->id;
        $ov = OrdersValues::where('orders_fields_id', $name_id)->where('orders_id', $model->orders_id)->first();
        $orders = Orders::select(['orders.*', 'orders_values.value'])->join('orders_values', function ($join) USE ($phone_id) {
                $join->on('orders.id', '=', 'orders_values.orders_id')->where('orders_values.orders_fields_id', $phone_id);
            })->where('orders_values.value', $model->value)->orderBy('time_at', 'desc')->get();
        $this->orders = $orders;
        return [
            "Клиент ".$ov->value.' ('.$model->value.')',
            'Заказы',
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the detail data or the components
     */
    public function detail(OrdersValues $model)
    {
        $i = 1;
        foreach ($this->orders as $order) {
            $row["$i. ". ( $order->time_at ? date('d.m.Y H:i', $order->time_at) : '-')] = $order->getField('title');
            $i++;
        }
        return $row;
    }
}
