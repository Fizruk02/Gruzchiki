@props(['actions', 'model'])

@foreach ($actions as $action)
  @if ($action->renderIf($model, $this))
    <x-lv-tooltip :tooltip="$action->title">
      @if(@$action->color)
        <x-lv-icon-button :icon="$action->icon" :color="@$action->color" size="sm" wire:click.prevent="executeAction('{{ $action->id }}', '{{ $model->getKey() }}')" />
      @else
        <x-lv-icon-button :icon="$action->icon" size="sm" wire:click.prevent="executeAction('{{ $action->id }}', '{{ $model->getKey() }}')" />
      @endif
    </x-lv-tooltip>
  @endif
@endforeach
