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
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\ListView;
use LaravelViews\Views\TableView;
use LaravelViews\Views\View;

class Profit extends View
{
    public $bot_id = 0;

    public $profit_start = null;
    public $profit_end = null;

    public $profit = null;

    protected $cabinet = null;
    public $model = null;

    public $itemComponent = 'components.profit';

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.profit", $data);
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
        $cabinet = Cabinet::curCabinet();
        $bots = Bot::where('cabinet_id', $cabinet->id)->get();
//dump($this->bot_id);
        $bot_id = intval(\request()->get('bot_id'));
        $start = strtotime(\request()->get('profit_start', date('d.m.Y')).' 00:00');
        $end = strtotime(\request()->get('profit_end', date('d.m.Y')).' 00:00');
        //$bot_id = $this->bot_id;
        //$start = $this->profit_start;
        //$end = $this->profit_end;

        $request = Orders::selectRaw('COUNT(*) as `count`, SUM(profit) as profit, SUM(expense) as expense, SUM(debt) as debt')
            ->join('orders_balance', 'orders_id', 'orders.id')
            ->where('cabinet_id', $cabinet->id);
        if ($bot_id) {
            $this->bot_id = $bot_id;
            $request->where('bot_id', $bot_id);
        }
        if ($start) $request->where('time_at', '>=', $start);
        if ($end) $request->where('time_at', '<', $end + 3600 * 24);
        $orders = $request->first();
        //dd($orders);

        $result = [
            'start' => date('d.m.Y', $start),
            'end' => date('d.m.Y', $end),
            'orders' => $orders->count,
            'profit' => $orders->profit,
            'expense' => $orders->expense,
            'debt' => $orders->debt,
        ];

        return [
            'bots' => $bots,
            'result' => $result,
        ];
    }

}
