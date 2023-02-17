<?php
use Illuminate\Support\Facades\Route;

/**
 * @var $title string
 * @var $live string
 * @var $route string
 * @var $name string
 * @var $model Object
 * @var $params array
 * @var $scripts string
 */
if (empty($model) && empty($params)) $html = \Livewire\Livewire::mount($live, [])->html();
else if (empty($model)) $html = \Livewire\Livewire::mount($live, $params)->html();
else $html = \Livewire\Livewire::mount($live, [
        'model' => $model,
        'return_url' => @$route ? '/'.Route::getRoutes()->getByName($route)->uri : ''
    ])->html();
//$html = \Livewire\Livewire::mount('user-detail-view', ['model' => 1])->html();
//$liveware = '<livewire:cabinet-table-view />';
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">

            {{--
            <a href="{{ route($route) }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition">{{ $name }}</a>
            --}}
            @if(@$name)
                <div class="block lg:justify-items-end">
                    <div x-data="{ tooltip: false }" class="cursor-pointer relative inline-flex float-right"
                         @mousemove.away="tooltip = false"
                    >
                        <span @mousemove="tooltip = true" @mouseleave="tooltip = false">
                            <a @if (@$scripts) {!! $scripts !!} href="#!" @else href="{{ is_array($route) ? route($route['name'], $route['params']) : route($route) }}" @endif class="border-transparent text-gray-600 rounded-full hover:text-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                                <i data-feather="{{ @$icon ? $icon : 'list' }}" class="{{@$ico_class}}"></i>
                            </a>
                        </span>

                        <div class="relative" x-show.transition.origin.top="tooltip" style="display: none;z-index: 1000;">
                            <div class="flex justify-center absolute top-0 z-10 w-32 p-2 -mt-3 text-sm leading-tight text-white transform -translate-x-1/2 -translate-y-full bg-gray-800 rounded-md shadow-md">
                                {{ $name }}
                            </div>
                            <svg class="absolute z-10 w-6 h-6 text-gray-800 transform -translate-x-8 -translate-y-5 fill-current stroke-current" width="8" height="8">
                                <rect x="12" y="-10" width="8" height="8" transform="rotate(45)"></rect>
                            </svg>
                        </div>
                    </div>
                </div>
            @endif
            {{ $title }}
        </h2>

    </x-slot>

    @if (session()->has('success'))
        <div class="py-8 bg-green-100" id="result-time">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-green-800 leading-tight">{{ session('success') }}</h2>
            </div>
        </div>
        <script>
            setTimeout(function(){
                document.getElementById('result-time').style.display = 'none';
            }, 5000);
        </script>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg">
                {!! $html !!}
            </div>
        </div>
    </div>
</x-app-layout>
