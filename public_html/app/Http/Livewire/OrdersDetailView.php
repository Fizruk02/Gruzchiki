<?php

namespace App\Http\Livewire;

use App\Models\Orders;
use App\Models\OrdersFields;
use App\Models\Request;
use App\Models\RequestFields;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Views\DetailView;
use App\Models\Users;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;

class OrdersDetailView extends DetailView
{
    //public $title = "Title";
    //public $subtitle = "Subtitle or description";

    protected $modelClass = \App\Models\Orders::class;
    protected $cabinet = null;
    protected $fields = null;

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function getFields() {
        $cabinet = $this->getCabinet();
        if ($this->fields) return $this->fields;
        return $this->fields = OrdersFields::where('cabinet_id', $cabinet->id)->orderBy('sort')->get();
    }

    public function heading(Orders $model)
    {
        return [
            "Заказ #{$model->number}",
            "Просмотр",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the detail data or the components
     */
    public function detail(Orders $model)
    {

        $fields = $this->getFields();
        $row = [];
        foreach ($fields as $field) {
            if (($field->type == 'client_phone') && (Auth::user()->id_cms_privileges == Users::ROLE_DISPETCHER)) continue;
            foreach ($model->orders_values as $rv) {
                if ($rv->orders_fields_id == $field->id) {
                    //dump($field);
                    if ($field->type == 'work_day_at') $row[$field->name] = '<div class="">'.date('d.m.Y', $model->time_at).'</div>';
                    else if ($field->type == 'work_time_at') $row[$field->name] = '<div class="">'.date('H:i', $model->time_at).'</div>';
                    else $row[$field->name] = '<div class="">'.$rv->value.'</div>';
                    $views = [];
                    if ($field->is_first) $views[] = 'Виден всем';
                    if ($field->is_accept) $views[] = 'Виден принявшим';
                    if ($field->is_2hours) $views[] = 'Виден за 2 часа';
                    if ($field->is_30minutes) $views[] = 'Виден за 30 минут';
                    //if (!empty($views)) $row[$field->name] .= '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-200 border-yellow-200 border-2 font-bold" title="Видимость">'.implode(', ', $views).'</span>';
                    break;
                }
            }
        }
        //$row['Выручка'] = $model->balance->profit;
        //$row['Затраты'] = $model->balance->expense;
        //$row['Долг по заказу'] = $model->balance->debt;
        $row['Коментарии к заказу'] = $model->balance->comments;
        //UI::link($cabinet->users->name, route('cabinet-edit', $cabinet->id)), //$user->name,
        return $row;
    }
}
