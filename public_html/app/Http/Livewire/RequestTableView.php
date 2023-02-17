<?php

namespace App\Http\Livewire;

use App\Actions\DeleteCabinetAction;
use App\Actions\DeleteCabinetsAction;
use App\Actions\DeleteRequestAction;
use App\Actions\DeleteRequestsAction;
use App\Models\Cabinet;
use App\Models\Request;
use App\Models\RequestFields;
use App\Models\RequestValues;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\Traits\WithAlerts;

class RequestTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = Request::class;
    protected $cabinet = null;
    protected $fields = null;

    protected $paginate = 20;

    public $searchBy = [];

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function getFields() {
        $cabinet = $this->getCabinet();
        if ($this->fields) return $this->fields;
        return $this->fields = RequestFields::where('cabinet_id', $cabinet->id)->where('is_list', 1)->orderBy('sort')->get();
    }

    public function headers(): array
    {
        $headers = [
            '#',
            'Дата',
            'Номер',
        ];

        $cabinet = $this->getCabinet();
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $headers[] = Header::title($field->name)->sortBy('field_'.$field->id);
        }

        return $headers;
    }

    public function row(Request $req)
    {
        $row = [
            ($this->page -  1) * $this->paginate + $this->num++,
            date('H:i d.m.Y', strtotime($req->created_at)),
            $req->number,
        ];
        $first = true;
        foreach ($this->fields as $field) {
            foreach ($req->request_values as $rv) {
                if ($rv->request_fields_id == $field->id) {
                    if ($first) $row[] = UI::link($rv->value,route('request-view', $req->id));
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
        $select = ['request.*'];
        $this->searchBy = [];
        foreach ($fields as $field) {
            //$this->searchBy[] = 'field_'.$field->id;
            $this->searchBy[] = 'rv'.$field->id.'.value';
            $field_id = $field->id;
            Request::resolveRelationUsing('rv'.$field_id, function ($requestModel) USE ($field_id) {
                return $requestModel->hasOne(RequestValues::class, 'request_id')->where('request_fields_id', $field_id);
            });
        }

//$this->searchBy[] = 'cabinet_id';
//dump($this);
//dump($this->searchBy);
//dump($this->sortBy);
        $r = Request::query()
            //->select(['request.*', 'request_values.request_fields_id', 'request_values.value'])
            ->with('request_values')
            ->where('request.cabinet_id', $cabinet->id)
            //->join('request_values', 'request.id', '=', 'request_values.request_id')
        ;
        /*if ($this->search) {
            foreach ($fields as $field) {
                $field_id = $field->id;
                $r->join('request_values as rv'.$field_id, function ($join) USE ($field_id) {
                    $join->on('request.id', '=', 'rv'.$field_id.'.request_id')->where('rv'.$field_id.'.request_fields_id', $field_id);
                });
                //$select[] = DB::raw('rv'.$field_id.'.value as field_'.$field_id);

            }
            //$r->select($select);
        }*/
        if ($this->sortBy) {
            $field_id = str_replace('field_', '', $this->sortBy);
            if (intval($field_id)) {
                $r->join('request_values', function ($join) USE ($field_id) {
                    $join->on('request.id', '=', 'request_values.request_id')->where('request_values.request_fields_id', $field_id);
                });
                $select[] = 'request_values.value as '.$this->sortBy;
                $r->select($select);
            }
        }

        return $r;
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new RedirectAction('request-view', 'Просмотр', 'eye'),
            new DeleteRequestAction(),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            new DeleteRequestsAction(),
        ];
    }

    protected function filters()
    {
        return [
            //new UsersActiveFilter,
        ];
    }
}
