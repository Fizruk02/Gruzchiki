<?php

namespace App\Http\Livewire;

use App\Constructor\Telegram;
use App\Models\Settings;
use App\Models\User;
use LaravelViews\Views\Traits\WithAlerts;
use LaravelViews\Views\View;

class RequestMainForm extends View
{
    use WithAlerts;

    public $name = '';
    public $phone = '';
    public $email = '';

    public $align = 'center';
    public $is_section = false;

    public $model = null;

    public $itemComponent = 'components.request-main-form';

    protected $rules = [
        'name' => 'min:6',
        'email' => 'email',
        'phone' => 'required'
    ];

    protected $messages = [
        'name.min' => 'Имя должно содержать не менее :min символов.',
        'phone.required' => 'Телефон должен быть заполнен обязательно.',
        'email.email' => 'Email адрес введен не корректно.',
    ];

    protected $validationAttributes = [
        'email' => 'email адрес'
    ];

    public function render()
    {
        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.request-main-form", $data);
    }

    public function getPaginatedQueryProperty()
    {
        return null;
    }

    public function submit()
    {
        $this->validate();

        /*$this->dispatchBrowserEvent('alert',
            ['type' => 'success',  'message' => 'User Created Successfully!']);
        return;*/

        if ($this->phone) {
            $phone = preg_replace('/[^0-9]/', '', $this->phone);
            if (strlen($phone) != 11) {
                session()->flash('error', 'Не верный формат номера телефона.');
                $this->addError('phone', 'Не верный формат номера телефона.');
                return;
            }
        }
        $bot_key = Settings::where('t_key', 'bot_key')->first();    //5823246437:AAFW5ohCMraMOLhr5Gf80diiFIpP4_PQeaY
        $admin = User::where('id', 1)->first();
        $telegram = new Telegram($bot_key->value, $admin->id_chat);
        $code = $telegram->tgMess('<b>Требуется обратный звонок по франшизе</b>'
            ."\n".'Имя: '.$this->name
            ."\n".'Почта: '.$this->email
            ."\n".'Телефон: '.$this->phone,
            $admin->id_chat);
        session()->flash('message', 'Заявка принята.');
        //session()->flash('error', 'Не удалось отправить заявку.');
    }

    /**
     * Collects all data to be passed to the view, this includes the items searched on the database
     * through the filters, this data will be passed to livewire render method
     */
    protected function getRenderData()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'align' => $this->align,
            'is_section' => $this->is_section,
        ];
    }

}
