<?php

namespace App\Http\Livewire;

//use App\Actions\DeleteRequestAction;
//use App\Actions\DeleteRequestsAction;
use App\Actions\ApproveOrderAction;
use App\Actions\CloseOrderAction;
use App\Actions\DeleteOrderAction;
use App\Actions\OpenOrderAction;
use App\Actions\SendOrderAction;
use App\Filters\BotFilter;
use App\Filters\OrderActiveFilter;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\OrdersFields;
use App\Models\OrdersUsers;
use App\Models\OrdersValues;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Views\ListView;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\Traits\WithAlerts;

class OrdersTableView extends ListView
{
    use WithAlerts;

    public $itemComponent = 'components.orders-list-item-component';
    public $component = 'components.orders-list-view';

    protected $num = 1;
    protected $model = Orders::class;
    protected $cabinet = null;
    protected $fields = null;

    protected $paginate = 20;

    public $searchBy = [];

    public function render()
    {
        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.orders-list-view", $data);
    }

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function getFields() {
        $cabinet = $this->getCabinet();
        if ($this->fields) return $this->fields;
        return $this->fields = OrdersFields::where('cabinet_id', $cabinet->id)->orderBy('sort')->get();
    }

    public function data(Orders $order)
    {
        $num = ($this->page -  1) * $this->paginate + $this->num++;
        return [
            'number' => $num,
            'document' => $order->number,
            'phone' => $order->getField('client_phone'),
            'title' => $order->getTitle(),
            'subtitle' => 'Заказчик: ' . $order->getInfo(),
            //'data' => $order->getField('work_day_at').' в '.$order->getField('work_time_at'),
            'data' => date('d.m.Y', $order->time_at).' в '.date('H:i', $order->time_at),
        ];
    }

    public function headers(): array
    {
        $headers = ['#'];

        $cabinet = $this->getCabinet();
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $headers[] = Header::title($field->name)->sortBy('field_'.$field->id);
        }

        return $headers;
    }

    public function row(Orders $req)
    {
        $user_p = Auth::user()->id_cms_privileges;
        $row = [
            ($this->page -  1) * $this->paginate + $this->num++,
        ];
        $first = true;
        foreach ($this->fields as $field) {
            foreach ($req->orders_values as $rv) {
                if ($rv->orders_fields_id == $field->id) {
                    if ($first) $row[] = ($user_p == Users::ROLE_ADMIN) ? UI::link($rv->value, route('orders-edit', $req->id)) : UI::link($rv->value,route('orders-view', $req->id));
                    else $row[] = $rv->value;
                    break;
                }
            }
        }
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
        $fields = $this->getFields();
        //dump($fields);
        $select = ['orders.*'];
        $this->searchBy = [];
        foreach ($fields as $field) {
            //$this->searchBy[] = 'field_'.$field->id;
            $this->searchBy[] = 'ov'.$field->id.'.value';
            $field_id = $field->id;
            Orders::resolveRelationUsing('ov'.$field_id, function ($requestModel) USE ($field_id) {
                return $requestModel->hasOne(OrdersValues::class, 'orders_id')->where('orders_fields_id', $field_id);
            });
        }

        $r = Orders::query()
            ->with('orders_values')
            ->where('orders.cabinet_id', $cabinet->id)
        ;
        if ($this->sortBy) {
            $field_id = str_replace('field_', '', $this->sortBy);
            if (intval($field_id)) {
                $r->join('orders_values', function ($join) USE ($field_id) {
                    $join->on('orders.id', '=', 'orders_values.orders_id')->where('orders_values.orders_fields_id', $field_id);
                });
                $select[] = 'orders_values.value as '.$this->sortBy;
                $r->select($select);
            }
        } else $r->orderBy('id', 'desc');

        return $r;
    }

    public function setNotify($id, $time)
    {
        if (Auth::user()->id_cms_privileges == Users::ROLE_DISPETCHER) {
            if (Auth::user()->timezone) {
                config(['timezone' => Auth::user()->timezone]);
                date_default_timezone_set(Auth::user()->timezone);
            }
        }
        $cabinet = $this->getCabinet();
        $order = Orders::where('orders.cabinet_id', $cabinet->id)->where('id', $id)->first();
        if ($order) {
            $order->notify_at = strtotime($time);
            $order->save();
        }
    }

    public function sendAproved($id, $message)
    {
        //dd($message);
        $cabinet = $this->getCabinet();
        $order = Orders::where('orders.cabinet_id', $cabinet->id)->where('id', $id)->first();
        if ($order) {
            $users = OrdersUsers::where('order_id', $id)->where(['approved' => 1])->get();
            foreach ($users as $user) {
                $user->user->sendMessage($message);
            }
            $this->success('Подтверждения разосланы!');
        }
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new RedirectAction('orders-view', 'Просмотр', 'eye'),
            new RedirectAction('orders-edit', 'Редактировать', 'edit'),
            new DeleteOrderAction(),
            new CloseOrderAction(),
            new OpenOrderAction(),
            new SendOrderAction(),
            new ApproveOrderAction(),
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
            new BotFilter(),
            new OrderActiveFilter(),
        ];
    }
}
