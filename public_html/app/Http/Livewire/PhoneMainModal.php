<?php

namespace App\Http\Livewire;

use App\Constructor\Telegram;
use App\Models\Bot;
use App\Models\Cabinet;
use App\Models\Settings;
use App\Models\User;
use LivewireUI\Modal\ModalComponent;

class PhoneMainModal extends ModalComponent
{
    public $name;
    public $email;
    public $phone;
    public $tariff;

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

    public function update()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        $form = request()->post();
        if ($phone && (strlen($phone) == 11)) {
            $bot_key = Settings::where('t_key', 'bot_key')->first();    //5823246437:AAFW5ohCMraMOLhr5Gf80diiFIpP4_PQeaY
            $admin = User::where('id', 1)->first();
            $telegram = new Telegram($bot_key->value);
            $telegram->tgMess('<b>Требуется обратный звонок по франшизе</b>'
                ."\n".'Имя: '.$this->name
                ."\n".'Почта: '.$this->email
                ."\n".'Телефон: '.$this->phone
                .($this->tariff ? "\n".'Тариф: '.$this->tariff : ''),
                $admin->id_chat);

            session()->flash('message', 'Заявка принята.');

            $this->phone = '';
            $this->email = '';
            $this->name = '';
            $this->tariff = '';
        } else {
            $this->validate();
        }

        //$cabinet
        //$this->closeModal();
    }

    /*public function mount($phone)
    {
        $this->phone = $phone;
    }*/

    public function render()
    {
        return view('components.phone-main-modal', [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'tariff' => $this->tariff,
        ]);
    }

    public static function modalMaxWidth(): string
    {
        // 'sm'
        // 'md'
        // 'lg'
        // 'xl'
        // '2xl'
        // '3xl'
        // '4xl'
        // '5xl'
        // '6xl'
        // '7xl'
        return 'sm';
    }

    public static function closeModalOnEscape(): bool
    {
        return true;
    }
}
