<?php

namespace App\Http\Livewire;

use App\Constructor\Telegram;
use App\Models\Bot;
use App\Models\Cabinet;
use App\Models\Request;
use App\Models\RequestFields;
use App\Models\RequestValues;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LaravelViews\Views\Traits\WithAlerts;
use Livewire\Exceptions\CannotBindToModelDataWithoutValidationRuleException;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\HydrationMiddleware\HashDataPropertiesForDirtyDetection;
use LivewireUI\Modal\ModalComponent;

class RequestModal extends ModalComponent
{
    use WithAlerts;

    public $fields = [];
    public $fnames = [];
    public $types = [];
    public $phones = [];
    public $requires = [];
    public $cabinet_id;

    public Collection $inputs;

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

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

    public function mount()
    {
        $this->fill([
            'inputs' => collect($this->fields),
        ]);
    }

    public function update()
    {
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

        if(!$count || ($count != $count_req)) $is_error = true;
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
                foreach ($this->fields as $key => $item) {
                    $f = new RequestValues();
                    $f->request_id = $request->id;
                    $f->request_fields_id = str_replace('field_', '', $key);
                    $f->value = $item;
                    $f->save();
                }
                session()->flash('message', 'Заявка принята.');
            } else {
                session()->flash('error', 'Не удалось отправить заявку.');
            }
        }
    }

    public function render()
    {
        $this->init();
        return view('components.request-modal');
    }

    public static function modalMaxWidth(): string
    {
        return 'sm';
    }

    public static function closeModalOnEscape(): bool
    {
        return true;
    }
}
