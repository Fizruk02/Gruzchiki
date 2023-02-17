@props(['actions', 'model' => null, 'export' => 0, 'color' => 'gray'])

@foreach ($actions as $action)
  @if ($action->renderIf($model, $this))
    <button
      wire:click.prevent="{{ $model ? "executeAction('{$action->id}','{$model->getKey()}')" : ( @$export ? "executeExportAction('{$action->id}')" : "executeBulkAction('{$action->id}')") }}"
      title="{{ $action->title}}"
      class="group flex items-center px-4 py-2 text-gray-700 hover:bg-{{$color}}-100 hover:text-{{$color}}-900 w-full focus:outline-none"
    >
      <i data-feather="{{ $action->icon }}" class="mr-3 h-4 w-4 text-{{$color}}-600 group-hover:text-{{$color}}-700"></i>
      {{ $action->title }}
    </button>
  @endif
@endforeach
