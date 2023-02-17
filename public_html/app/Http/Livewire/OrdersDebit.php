<?php

namespace App\Http\Livewire;

use App\Actions\DeleteEmployeeAction;
use App\Models\Bot;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\OrdersFields;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\TableView;

class OrdersDebit extends TableView
{
    protected $modelClass = \App\Models\Orders::class;

    protected $num = 1;
    public $bot_id = null;

    protected $cabinet = null;
    public $model = null;
    public $searchBy = [];

    protected $paginate = 20;

    public $itemComponent = 'components.orders-debit';

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.orders-debit", $data);
    }

    public function getPaginatedQueryProperty()
    {
        return $this->query->paginate($this->paginate, ['*'], 'debitOrderPage'.$this->bot_id);
    }

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('Заголовок')->sortBy('name'),
            Header::title('Долг')->sortBy('debt'),
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $cabinet = Cabinet::curCabinet();
        $bots = Bot::where('cabinet_id', $cabinet->id)->get();

        $bot_id = intval(\request()->get('bot_id'));
        $start = strtotime(\request()->get('profit_start', date('d.m.Y')).' 00:00');
        $end = strtotime(\request()->get('profit_end', date('d.m.Y')).' 00:00');

        $field_id = OrdersFields::where('cabinet_id', $cabinet->id)->where('type', 'title')->first()['id'];
        $query = Orders::selectRaw('orders.id, orders_balance.`debt`, orders_values.value as name')
            ->join('orders_balance', 'orders_id', 'orders.id')
            ->join('orders_values', function ($join) USE ($field_id) {
                $join->on('orders.id', '=', 'orders_values.orders_id')->where('orders_values.orders_fields_id', $field_id);
            })
            ->where('debt', '>', 0)
            ->where('cabinet_id', $cabinet->id);
        if ($bot_id) {
            $this->bot_id = $bot_id;
            $query->where('bot_id', $bot_id);
        }
        if ($start) $query->where('time_at', '>=', $start);
        if ($end) $query->where('time_at', '<', $end + 3600 * 24);
        //$orders = $query->first();
        return $query;
    }

    public function row(Orders $order)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            UI::link($order->name, route('orders-edit', $order->id)),
            number_format($order->debt, 2, '.', ' ')
        ];
    }

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }
}
