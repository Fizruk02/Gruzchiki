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
            <div @if($is_success) style="display: none;" @endif;>
                <input wire:model="name" class="calc-section__form-input" placeholder="Имя" type="text" id="name" name="name" value="{{@$name}}">
                @error('name') <div class="alert alert-danger" style="padding-bottom: 10px;">{{ $message }}</div> @enderror
                <input wire:model="email" class="calc-section__form-input" placeholder="Почта" type="text" id="email" name="email" value="{{@$email}}">
                @error('email') <div class="alert alert-danger" style="padding-bottom: 10px;">{{ $message }}</div> @enderror
                <input wire:model="phone" class="calc-section__form-input" placeholder="Телефон" type="text" id="phone" name="phone" value="{{@$phone}}">
                @error('phone') <div class="alert alert-danger" style="padding-bottom: 10px;">{{ $message }}</div> @enderror
                <input wire:model="tariff" type="hidden" id="tariff" name="tariff" value="{{@$tariff}}">
                <button class="calc-section__form-button button" >Заказать звонок</button>
            </div>
        @endif
    </x-slot>

    <x-slot name="buttons">
    </x-slot>
</x-modal>



