<?php

namespace App\Http\Livewire;

use App\Models\Request;
use App\Models\RequestFields;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Views\DetailView;
use App\Models\Users;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Facades\UI;

class RequestDetailView extends DetailView
{
    //public $title = "Title";
    //public $subtitle = "Subtitle or description";

    protected $modelClass = \App\Models\Request::class;
    protected $cabinet = null;
    protected $fields = null;

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function getFields() {
        $cabinet = $this->getCabinet();
        if ($this->fields) return $this->fields;
        return $this->fields = RequestFields::where('cabinet_id', $cabinet->id)->orderBy('sort')->get();
    }

    public function heading(Request $model)
    {
        return [
            "Заявка #{$model->number}",
            "Просмотр",
        ];
    }

    /**
     * @param $model Model instance
     * @return Array Array with all the detail data or the components
     */
    public function detail(Request $model)
    {

        $fields = $this->getFields();
        $row = [];
        foreach ($fields as $field) {
            foreach ($model->request_values as $rv) {
                if ($rv->request_fields_id == $field->id) {
                    $row[$field->name] = $rv->value;
                    break;
                }
            }
        }
        //UI::link($cabinet->users->name, route('cabinet-edit', $cabinet->id)), //$user->name,
        return $row;
    }
}
