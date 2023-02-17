@php
    $time = date('g:i A', strtotime($value));
    $value = date('d.m.Y', strtotime($value));
@endphp
<li class='{{ @$stripe && @$loop->index %2 === 0 ? 'bg-gray-100' : '' }} px-4 py-2 border-b border-gray-200 sm:flex sm:items-center form-group form-datepicker {{$header_group_class}} {{ ($errors->first($name))?"has-error":"" }}' id='form-group-{{$name}}'
     style="{{@$form['style']}}">
    <label class='text-xs leading-4 font-semibold uppercase tracking-wider text-gray-900 sm:w-3/12'>{{$form['label']}}
        @if($required)
            <span class='text-danger' title='{!! cbLang('this_field_is_required') !!}'>*</span>
        @endif
    </label>

    <div class="sm:w-9/12">
        <div class="input-group">
            <div class="flex justify-left">
                <div class="relative w-full">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                    </div>

                    <div x-data="{ datetimeFields: '' }" x-init="datetime_{{$name}};">
                    <input type='text' datepicker-format="dd.mm.yyyy" datepicker title="{{$form['label']}}" readonly id="datetime_{{$name}}" name="{{$name}}"
                       {{$required}} {{$readonly}} {!!$placeholder!!} {{$disabled}}
                       class='form-control notfocus pl-10 input_date transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent border-gray-300 w-full'
                       value='{{$value}}' />
                    </div>
                </div>
            </div>
        </div>
        <div class="text-danger">{!! $errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}</div>
        <p class='help-block'>{{ @$form['help'] }}</p>
    </div>
</li>


