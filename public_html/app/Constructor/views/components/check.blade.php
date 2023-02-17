{{-- components.editable

Render an editable input field --}}
@php
//dump($class);
//dump($model->$field);
    $key = 'id';
    if (!isset($model->{$key})) $key = 'ID';
    if (!@$class) $class = 'text-green-500';
    $checked = '<span class="'.$class.'"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check '.$class.'"><polyline points="20 6 9 17 4 12"></polyline></svg></span>';
    $unchecked = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check text-gray-300"><polyline points="20 6 9 17 4 12"></polyline></svg>';
@endphp
@props(['model', 'field' => ''])
<div x-data="{
    field: '{{ $field }}',
    id: {{ $model->{$key} }},
    value: {{ json_encode($model->$field) }},
    original: {{ json_encode($model->$field) }},
    editing: false,
    code: {{ intval($model->{$field}) }} ? '{{ $checked }}' : '{{ $unchecked }}'
  }"
  @click.away="editing = false; value = original;">

  <div x-show="!editing"
    @click="if (value == 0) {
            value = 1;
            code = '{{ $checked }}'
       } else {
            value = 0;
            code = '{{ $unchecked }}';
       }
       $wire.update(id, {
        [field]: value
        }); editing = false;"
    {{--x-html="function () { if (value) return 11; return 22; }"--}}
    x-html="code"
    class='inline-flex transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-gray-100 hover:border-gray-100 border border-transparent'
    title="{{@$title}}"
  >
    {{--!! $model->$field !!--}}
  </div>
{{--
<i x-show="!editing && value" data-feather="{{ $icon }}" class="{{ variants()->featherIcon($type)->class() }} {{ $class }}"></i>
<i x-show="!editing && !value" data-feather="{{ $icon }}" class="text-gray-300 {{ $class }}"></i>
--}}
</div>
