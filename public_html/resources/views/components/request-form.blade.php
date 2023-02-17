@php
    $is_error = false;
    if (session()->has('error')) $is_error = session('error');

    $is_success = false;
    if (session()->has('message')) $is_success = session('message');
@endphp
<form wire:submit.prevent="submit()">
    <p class="headerForm">Получить расчет</p>
    @if ($is_error)
        <div class="alert alert-danger">
            {{ $is_error }}
        </div>
    @endif
    @if ($is_success)
        <div class="alert alert-success">
            {{ $is_success }}
        </div>
    @endif

    @foreach($fields as $key => $field)
        <div class="inputs">
            <p>
                {{$fnames[$key]}}
                @if($requires[$key]) <span class="required" style="color: rgba(255,0,0,.5);">&nbsp;*</span> @endif
            </p>
            @if (!$types[$key])
                <input wire:model="inputs.{{$key}}" type="text" value="{{$field}}"
                       class="form-control text"
                       style="border-radius: 5px;  font-size: 14px; background-color: rgb(46, 46, 46); border-color: rgb(21, 21, 21); color: rgb(209, 209, 209);">
            @else
                <textarea wire:model="inputs.{{$key}}" type="text"
                          class="form-control textarea"
                          style="border-radius: 5px;  font-size: 14px; background-color: rgb(46, 46, 46); border-color: rgb(21, 21, 21); color: rgb(209, 209, 209); min-height: 100px;">{{$field}}</textarea>
            @endif
        </div>
    @endforeach
    <button class="formButton" type="submit">Оставить заявку!</button>
</form>

