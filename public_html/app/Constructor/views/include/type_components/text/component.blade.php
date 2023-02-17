<?php
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\MessageBag;
?>
<li class="{{ @$stripe && @$loop->index %2 === 0 ? 'bg-gray-100' : '' }} px-4 py-2 border-b border-gray-200 sm:flex sm:items-center form-group {{$header_group_class}}
{{ (@$errors->first($name))?"has-error":"" }}" id='form-group-{{$name}}' style="{{@$form['style']}}" title="{{@$form['title']}}">
    @if ($form['label'] != null)
    <label class="text-xs leading-4 font-semibold uppercase tracking-wider text-gray-900 sm:w-3/12">
        {{$form['label']}}
        @if($required)
            <span class='text-danger' title='{!! cbLang('this_field_is_required') !!}'>*</span>
        @endif
    </label>
    @endif
    <div class="mt-1 text-sm leading-5 sm:mt-0 @if ($form['label'] != null) sm:w-9/12 @else w-full @endif ">
        <?php $validation['max'] = $validation['max'] ?? ''; ?>
        @if ($form['label'] == null && $required) <div class="text-danger absolute px-1" style="user-select: none;">*</div> @endif
        <input type='text' title="{{$form['label']}}"
               {{$required}} {{$readonly}} {!!$placeholder!!} {{$disabled}} {{$validation['max']?"maxlength=".$validation['max']:""}} class='form-control  transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent border-gray-300 w-full'
               name="{{$name}}" id="{{$name}}" value='{{$value}}'
        />

        <div class="text-danger">{!! @$errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}</div>
            <p class='help-block'>@if (@$form['html']) {!! @$form['help'] !!} @else {{ @$form['help'] }} @endif</p>
    </div>
</li>
