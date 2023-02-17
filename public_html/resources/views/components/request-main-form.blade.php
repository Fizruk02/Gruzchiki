@php
    $is_error = false;
    if (session()->has('error')) $is_error = session('error');

    $is_success = false;
    if (session()->has('message')) $is_success = session('message');
@endphp
<form wire:submit.prevent="submit()">
    @if ($is_error)
        <div class="alert alert-danger">
            {{ $is_error }}
        </div>
    @endif
    @if ($is_success)
        <div class="alert alert-success" style="text-align: {{$align}}">
            {{ $is_success }}
        </div>
    @endif

    @if ($is_section)
        <div @if($is_success) style="display: none;" @endif;>
            <input wire:model="name" class="calc-section__form-input" placeholder="Имя" type="text" id="name" name="name" value="{{@$name}}">
            @error('name') <div class="alert alert-danger" style="padding-bottom: 10px;">{{ $message }}</div> @enderror
            <input wire:model="email" class="calc-section__form-input" placeholder="Почта" type="text" id="email" name="email" value="{{@$email}}">
            @error('email') <div class="alert alert-danger" style="padding-bottom: 10px;">{{ $message }}</div> @enderror
            <input wire:model="phone" class="calc-section__form-input" placeholder="Телефон" type="text" id="phone" name="phone" value="{{@$phone}}">
            @error('phone') <div class="alert alert-danger" style="padding-bottom: 10px;">{{ $message }}</div> @enderror
            <button class="calc-section__form-button button" >Заказать звонок</button>
        </div>
    @else
        <div class="form-section__form" @if($is_success) style="display: none;" @endif;>
            <div style="width: 100%;">
                @error('name') <span class="alert alert-danger">{{ $message }}</span> @enderror
                @error('email') <span class="alert alert-danger">{{ $message }}</span> @enderror
                @error('phone') <span class="alert alert-danger">{{ $message }}</span> @enderror
            </div>
            <input wire:model="name" class="form-section__form-input" placeholder="Имя" type="text" id="name" name="name" value="{{@$name}}">
            <input wire:model="email" class="form-section__form-input" placeholder="Почта" type="text" id="email" name="email" value="{{@$email}}">
            <input wire:model="phone" class="form-section__form-input" placeholder="Телефон" type="text" id="phone" name="phone" value="{{@$phone}}">
            <button class="form-section__form-button button">Заказать звонок</button>
        </div>
    @endif
</form>

