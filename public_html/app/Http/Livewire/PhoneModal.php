<?php

namespace App\Http\Livewire;

use App\Constructor\Telegram;
use App\Models\Bot;
use App\Models\Cabinet;
use LivewireUI\Modal\ModalComponent;

class PhoneModal extends ModalComponent
{
    public $name;
    public $phone;
    public $cabinet_id;

    public function update()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        $form = request()->post();
        if ($phone && (strlen($phone) == 11)) {
            $cabinet = Cabinet::where('id', $this->cabinet_id)->first();
            $chat_id = $cabinet->users->id_chat;
            $bot = Bot::where('cabinet_id', $this->cabinet_id)->first();
            if ($bot) {
                $telegram = new Telegram($bot->bot_key);
                $telegram->tgMess('<b>Требуется обратный звонок</b>'
                    ."\n".'Имя: '.$this->name
                    ."\n".'Телефон: '.$this->phone,
                    $chat_id);
            }

            session()->flash('message', 'Заявка принята.');

            $this->phone = '';
            $this->name = '';
        } else {
            session()->flash('error', 'Введите номер телефона.');
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
        return view('components.phone-modal');
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
