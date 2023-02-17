@php
    $is_error = false;
    if (session()->has('error')) $is_error = session('error');

    $is_success = false;
    if (session()->has('message')) $is_success = session('message');
@endphp
<x-modal formAction="update()">
    <x-slot name="title">
        <button wire:click="$emit('closeModal')" class="close nofonts float-right">×</button>
        <h4 class="textable"><p><strong>Обратный звонок</strong></p></h4>
    </x-slot>

    <x-slot name="content">
        @if ($is_error)

            <div class="alert alert-danger">

                {{ $is_error }}

            </div>

        @endif
        @if ($is_success)

            <div class="alert alert-success">

                {{ $is_success }}

            </div>
        @else
            <div class="fields">
                <div class="field pb-4" data-type="name">
                    <div class="name">Ваше имя</div>
                    <div class="input">
                        <input wire:model="name" class="form-control text" style="border-radius: 5px;">
                    </div>
                </div>
                <div class="field pb-4" data-type="phone">
                    <div class="name">
                        Ваш номер телефона<span class="required text-red-500">&nbsp;*</span>
                    </div>
                    <div class="input">
                        <input wire:model="phone" class="form-control text" style="border-radius: 5px;" name="phone" value="{{$phone}}">
                    </div>
                </div>
            </div>
        @endif
    </x-slot>

    <x-slot name="buttons">
        @if (!$is_success)
            <button type="submit" class="w-full phone-submit">Заказать звонок</button>
        @endif
    </x-slot>
</x-modal>
