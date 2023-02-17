<?php

namespace App\Http\Livewire;

use App\Models\RequestFields;
use Illuminate\Support\Collection;
use App\Models\Request;
use App\Models\RequestValues;
use LaravelViews\Views\Traits\WithAlerts;
use LaravelViews\Views\View;

class CalcForm extends View
{
    use WithAlerts;

    public $count_workers = 59;
    public $count_days = 22;
    public $count_time = 8;
    public $result = null;

    public $model = null;

    public $itemComponent = 'components.calc-form';

    public function render()
    {
        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.calc-form", $data);
    }

    public function getPaginatedQueryProperty()
    {
        return null;
    }

    public function calculate()
    {
    }

    /**
     * Collects all data to be passed to the view, this includes the items searched on the database
     * through the filters, this data will be passed to livewire render method
     */
    protected function getRenderData()
    {
        $this->result = number_format(intval($this->count_workers * $this->count_days * $this->count_time * 6.25), 0, '', ' ');
        return [
            'count_workers' => $this->count_workers,
            'count_days' => $this->count_days,
            'count_time' => $this->count_time,
            'result' => $this->result,
        ];
    }

}
