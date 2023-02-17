{{-- components.editable

Render an editable input field --}}
@props(['model', 'field' => '', 'list' => []])

<div x-data="{
    field: '{{ $field }}',
    id: {{ $model->id }},
    value: {{ json_encode($model->$field) }},
    original: {{ json_encode($model->$field) }},
    value_name: {{ json_encode(@$list[$model->$field]) }},
    editing: false
  }"
  @click.away="editing = false; value = original;">
  <select x-cloak
    x-ref="input"
    x-show="editing"
    x-model="value"
    {{--@change="alert(value);"--}}
    @keydown.enter="$wire.update(id, {
      [field]: value
    }); editing = false;"
    @keydown.escape="editing = false; value = original;"
    class="block appearance-none w-full bg-white border-gray-300 hover:border-gray-500 px-2 py-1 rounded focus:outline-none focus:bg-white focus:border-blue-600 focus:border-2 border"
  >
      @if (!isset($list[$model->$field]))
          <option value=""></option>
      @endif
      @foreach($list as $key => $item)
          <option value="{{$key}}">{{$item}}</option>
      @endforeach
  </select>
  <div x-show="!editing"
    @click="editing = true; $nextTick(() => {$refs.input.focus()})"
    x-html="value_name"
    class='transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent'>
    {!! @$list[$model->$field] !!}
  </div>

</div>
