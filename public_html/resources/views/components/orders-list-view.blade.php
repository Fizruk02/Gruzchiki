<?php
    $eitems = [];
    $eitems[] = 'editing: false';
    foreach ($items as $item) {
        $eitems[] = 'editing'.$item->id.': false';
    }
    //dump(implode(', ', $eitems));
?>
<x-lv-layout>
  {{-- Search input and filters --}}
  <div class="px-4 pt-4">
    @include('laravel-views::components.toolbar.toolbar')
  </div>

  <div x-data="{modaling: false, {{implode(', ', $eitems)}}}">
    @foreach ($items as $item)
      <div class="border-b border-gray-200">
        @if ($this->hasBulkActions)
          <div class="h-full flex items-center pl-3 md:pl-4">
            <x-lv-checkbox wire:model="selected" value="{{ $item->getKey() }}" />
          </div>
        @endif
        <div class="py-2 px-3 md:px-4 flex-1">
            <x-lv-dynamic-component :view="$itemComponent" :data="array_merge($this->data($item), ['actions' => $actionsByRow, 'model' => $item])" />
        </div>
      </div>
    @endforeach
  </div>

  {{-- Paginator, loading indicator and totals --}}
  <div class="mt-4 px-4 pb-4">
     {{ $items->links() }}
  </div>
</x-lv-layout>


