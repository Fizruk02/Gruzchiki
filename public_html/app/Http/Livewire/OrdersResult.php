<?php

namespace App\Http\Livewire;

use App\Constructor\Facades\CRUI;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrdersController;
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

class OrdersResult extends View
{
    public $order_id = null;
    protected $cabinet = null;
    public $model = null;

    public $itemComponent = 'components.orders-result';

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.orders-result", $data);
    }

    public function getPaginatedQueryProperty()
    {
        return null;
    }

    /**
     * Collects all data to be passed to the view, this includes the items searched on the database
     * through the filters, this data will be passed to livewire render method
     */
    protected function getRenderData()
    {
        return [];
    }

}
