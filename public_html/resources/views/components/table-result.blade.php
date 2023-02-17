{{-- components.table

Renders a data table
You can customize all the html and css classes but YOU MUST KEEP THE BLADE AND LIVEWIRE DIRECTIVES,

props:
  - headers
  - itmes
  - actionsByRow --}}
<?php
    $count_act = 0;
    $show_action = true;
    foreach ($actionsByRow as $action) {
        if (@$action->is_hidden)
            $count_act++;
    }
    if ($count_act == count($actionsByRow)) $show_action = false;
?>
<table class="min-w-full">

  <thead class="border-b border-t border-gray-200 bg-gray-100 text-xs leading-4 font-semibold uppercase tracking-wider text-left">
    <tr>
      @if ($this->hasBulkActions)
        <th class="pl-3">
          <span class="flex items-center justify-center">
            <x-lv-checkbox wire:model="allSelected" />
          </span>
        </th>
      @endif
      {{-- Renders all the headers --}}
      @foreach ($headers as $header)
        <th class="px-3 py-3" {{ is_object($header) && ! empty($header->width) ? 'width=' . $header->width . '' : '' }}>
          @if (is_string($header))
            {{ $header }}
          @else
            @if ($header->isSortable())
              <div class="flex">
                <a href="#!" wire:click.prevent="sort('{{ $header->sortBy }}')" class="flex-1">
                  {{ $header->title }}
                </a>
                <a href="#!" wire:click.prevent="sort('{{ $header->sortBy }}')" class="flex">
                  <i data-feather="chevron-up" class="{{ $sortBy === $header->sortBy && $sortOrder === 'asc' ? 'text-gray-900' : 'text-gray-400'}} h-4 w-4"></i>
                  <i data-feather="chevron-down" class="{{ $sortBy === $header->sortBy && $sortOrder === 'desc' ? 'text-gray-900' : 'text-gray-400'}} h-4 w-4"></i>
                </a>
              </div>
            @else
              {{ $header->title }}
            @endif
          @endif
        </th>
      @endforeach
      {{-- This is a empty cell just in case there are action rows --}}
      @if ($show_action && count($actionsByRow) > 0)
        <th></th>
      @endif
    </tr>
  </thead>

  <tbody>
    @foreach ($items as $item)
      <tr class="border-b border-gray-200 text-sm" wire:key="{{ $item->getKey() }}">
        @if ($this->hasBulkActions)
          <td class="pl-3">
            <span class="flex items-center justify-center">
              <x-lv-checkbox value="{{ $item->getKey() }}" wire:model="selected" />
            </span>
          </td>
        @endif
        {{-- Renders all the content cells --}}
        @foreach ($view->row($item) as $key => $column)
          <td class="px-3 py-2" @if(!$key) rowspan="2" @endif>
            {!! $column !!}
          </td>
        @endforeach

        {{-- Renders all the actions row --}}
        @if ($show_action && count($actionsByRow) > 0)
          <td>
            <div class="px-3 py-2 flex justify-end">
              <x-lv-actions :actions="$actionsByRow" :model="$item" />
            </div>
          </td>
        @endif
      </tr>
      <tr>
          <td colspan="5" id="cab_order_{{$item->id}}" style="display: none;" class="bg-gray-100">
            @livewire('cabinet-result-one', ['period_id' => $period_id, 'cabinet_id' => $item->id], key('cro'.$item->id))
          </td>
      </tr>
    @endforeach
  </tbody>
</table>

@push('bottom')
<script>
    function getRealDisplay(elem) {
        if (elem.currentStyle) {
            return elem.currentStyle.display
        } else if (window.getComputedStyle) {
            var computedStyle = window.getComputedStyle(elem, null )

            return computedStyle.getPropertyValue('display')
        }
    }

    function hide(el) {
        if (!el.getAttribute('displayOld')) {
            el.setAttribute("displayOld", el.style.display)
        }

        el.style.display = "none"
    }

    displayCache = {}

    function isHidden(el) {
        var width = el.offsetWidth, height = el.offsetHeight,
            tr = el.nodeName.toLowerCase() === "tr"

        return width === 0 && height === 0 && !tr ?
            true : width > 0 && height > 0 && !tr ? false :	getRealDisplay(el)
    }

    function toggle(id) {
        let el = document.getElementById(id);
        isHidden(el) ? show(el) : hide(el)
    }


    function show(el) {

        if (getRealDisplay(el) != 'none') return

        var old = el.getAttribute("displayOld");
        el.style.display = old || "";

        if ( getRealDisplay(el) === "none" ) {
            var nodeName = el.nodeName, body = document.body, display

            if ( displayCache[nodeName] ) {
                display = displayCache[nodeName]
            } else {
                var testElem = document.createElement(nodeName)
                body.appendChild(testElem)
                display = getRealDisplay(testElem)

                if (display === "none" ) {
                    display = "block"
                }

                body.removeChild(testElem)
                displayCache[nodeName] = display
            }

            el.setAttribute('displayOld', display)
            el.style.display = display
        }
    }
</script>
@endpush
