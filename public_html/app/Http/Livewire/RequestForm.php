<?php

namespace App\Http\Livewire;

use App\Models\RequestFields;
use Illuminate\Support\Collection;
use App\Models\Request;
use App\Models\RequestValues;
use LaravelViews\Views\Traits\WithAlerts;
use LaravelViews\Views\View;

class RequestForm extends View
{
    use WithAlerts;

    public $fields = [];
    public $fnames = [];
    public $types = [];
    public $phones = [];
    public $requires = [];
    public $cabinet_id;

    public Collection $inputs;

    public $model = null;

    public $itemComponent = 'components.request-form';

    public function init() {
        $fields = RequestFields::where('cabinet_id', $this->cabinet_id)->orderBy('sort')->get();
        foreach ($fields as $field) {
            $fname = 'field_'.$field->id;
            $this->fields[$fname] = '';
            $this->fnames[$fname] = $field->name;
            $this->types[$fname] = $field->is_text;
            $this->phones[$fname] = $field->is_number;
            $this->requires[$fname] = $field->is_required;

            $this->inputs->push([$fname => '']);
        }
    }

    public function render()
    {
        $this->init();
        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.request-form", $data);
    }

    public function mount()
    {
        $this->fill([
            'inputs' => collect($this->fields),
        ]);
    }

    public function getPaginatedQueryProperty()
    {
        return null;
    }

    public function submit()
    {
        /*$this->dispatchBrowserEvent('alert',
            ['type' => 'success',  'message' => 'User Created Successfully!']);
        return;*/
        $count_req = 0;
        foreach ($this->requires as $r)
            if ($r) $count_req++;

        $is_error = false;
        $count = 0;
        foreach ($this->inputs as $key => $item) {
            if (is_array($item)) continue;
            if (!$item) $is_error = true;
            $this->fields[$key] = $item;
            if ($this->requires[$key]) $count++;
            if ($this->phones[$key]) {
                $phone = preg_replace('/[^0-9]/', '', $item);
                if (strlen($phone) != 11) {
                    $is_error = false;
                    session()->flash('error', 'Не верный формат номера.');
                    return;
                }
            }
        }
        //dd($this->fields);
        if(!$count || ($count != $count_req)) $is_error = true;
        //dd($is_error);
        //if ($is_error) $this->error('Все поля должны быть заполнены');
        if ($is_error) {session()->flash('error', 'Все поля со * должны быть заполнены.');}
        else {
            $request = new Request();
            $request->cabinet_id = $this->cabinet_id;

            $num = 1;
            $nb = date('d.m/');
            while (Request::where('cabinet_id', $this->cabinet_id)->where('number', $nb.$num)->first()) {
                $num++;
            }
            $request->number = $nb.$num;

            if ($request->save()) {
                foreach ($this->fields as $key => &$item) {
                    $f = new RequestValues();
                    $f->request_id = $request->id;
                    $f->request_fields_id = str_replace('field_', '', $key);
                    $f->value = $item;
                    $f->save();
                    $item = '';
                }

                //$this->inputs->empty();
                $this->fill([
                    'inputs' => collect($this->fields),
                ]);
                $this->init();
                //dd($this->inputs);
                session()->flash('message', 'Заявка принята.');
                //$this->success('Заявка успешно отправлена');
            } else {
                session()->flash('error', 'Не удалось отправить заявку.');
                //$this->error('Не удалось отправить заявку.');
            }
        }

        /*$is_error = false;
        //$valids = [];
        foreach ($this->fields as $key => $field) {
            //$valids[$key] = 'required';
            if (!$field) $is_error = true;
        }
        if ($is_error) $this->error('Все поля должны быть заполнены');
        else {
            $this->success('Заявка успешно отправлена');

        }
        //dd($this->fields);
        //dd($valids);
        //$validatedData = $this->validate($valids);
        //dd($validatedData);

        /*Contact::create($validatedData);*/

        //return redirect()->to('/accounting');
    }

    /**
     * Collects all data to be passed to the view, this includes the items searched on the database
     * through the filters, this data will be passed to livewire render method
     */
    protected function getRenderData()
    {
        return [
            'fields' => $this->fields,
        ];
    }

}
