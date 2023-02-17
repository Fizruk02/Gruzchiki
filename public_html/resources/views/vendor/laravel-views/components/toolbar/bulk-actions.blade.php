<div class="flex space-x-1">
  @if (count($selected) > 0 && $this->hasBulkActions)
    <x-lv-drop-down label='Действия'>
      <x-lv-drop-down.header label='{{ count($selected) }} Выбрано' />
      <x-lv-actions.icon-and-title :actions="$this->bulkActions" />
    </x-lv-drop-down>
  @endif

  @if (@$this && isset($this->hasExportActions) && @$this->hasExportActions)
      <x-lv-drop-down label='Экспорт'>
          <x-lv-actions.icon-and-title :actions="$this->exportActions" :export="1" />
      </x-lv-drop-down>
  @endif

  @if (@$this && $this->hasBulkActions && isset($headers) <= 0)
    <button
      wire:click="$set('allSelected', {{ !$allSelected }})"
      class="border border-transparent hover:border-gray-300 focus:border-gray-300 focus:outline-none flex items-center text-xs px-3 py-2 rounded hover:shadow-sm font-medium"
    >
      {{ __($allSelected ? 'Unselect all' : 'Select all') }}
    </button>
  @endif
</div>
