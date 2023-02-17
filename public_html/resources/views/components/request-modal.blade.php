@php
    $is_error = false;
    if (session()->has('error')) $is_error = session('error');

    $is_success = false;
    if (session()->has('message')) $is_success = session('message');
@endphp
<x-modal formAction="update()">
    <x-slot name="title">
        <button wire:click="$emit('closeModal')" class="close nofonts float-right">×</button>
        <h4 class="textable"><p><strong>Заявка</strong></p></h4>
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
                @foreach($fields as $key => $field)
                <div class="field">
                    <div class="name">
                        {{$fnames[$key]}}
                        @if($requires[$key]) <span class="required" style="color: rgba(255,0,0,.5);">&nbsp;*</span> @endif
                    </div>
                    <div class="input pb-4">
                        @if (!$types[$key])
                            <input wire:model="inputs.{{$key}}" type="text" value="{{$field}}"
                                   class="form-control text"
                                   style="border-radius: 5px; font-size: 14px;">
                        @else
                            <textarea wire:model="inputs.{{$key}}" type="text"
                                      class="form-control textarea"
                                      style="border-radius: 5px; font-size: 14px;">{{$field}}</textarea>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </x-slot>

    <x-slot name="buttons">
        @if (!$is_success)
            <button type="submit" class="w-full form-submit">Заказать звонок</button>
        @endif
    </x-slot>
</x-modal>
