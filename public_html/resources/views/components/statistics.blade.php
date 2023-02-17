{{-- table-view.table-view

Base layout to render all the UI componentes related to the table view, this is the main file for this view,
the rest of the files are included from here

You can customize all the html and css classes but YOU MUST KEEP THE BLADE AND LIVEWIERE DIRECTIVES

UI components used:
  - table-view.filters
  - components.alert
  - components.table
  - components.paginator
@dump($model)
--}}
<div class="flex flex-wrap pt-4 md:grid md:grid-cols-3 md:gap-6">
    <div class="sm:col-span-2 md:col-span-1 px-4 w-full">
        {{--<form wire:model="profit" wire:submit.prevent="submit" class="w-full">--}}
        <form action="" class="w-full">
        <div class="mb-5">
            <div class="flex mb-2">
                <select class='w-full border-yellow-200 transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent' id="period_id" name="period_id">
                    <option value='0'>--За все время--</option>
                    @foreach($periods as $key => $name)
                        <?php
                        $select = ($period_id == $key) ? "selected" : "";
                        ?>
                        <option {{$select}} value='{{$key}}'>{{$name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex mb-2">
                <select class='w-full border-yellow-200 transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent' id="city_id" name="city_id">
                    <option value='0'>--Все города--</option>
                    @foreach($cities as $key => $name)
                        <?php
                        $select = ($city_id == $key) ? "selected" : "";
                        ?>
                        <option {{$select}} value='{{$key}}'>{{$name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex mb-2">
                <select class='w-full border-yellow-200 transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent' id="cabinet_id" name="cabinet_id">
                    <option value='0'>--Все администраторы--</option>
                    @foreach($cabinets as $key => $name)
                        <?php
                        $select = ($cabinet_id == $key) ? "selected" : "";
                        ?>
                        <option {{$select}} value='{{$key}}'>{{$name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex mt-5">
                <button class="p-1 px-2 border-2 border-yellow-400 bg-yellow-300 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out" type="submit">Вывести</button>
            </div>
        </div>
        </form>
    </div>
    @if(request()->get('period_id') !== null)
    <div class="md:col-span-2 sm:col-span-2 px-4 xl:px-0 w-full">
        @livewire('cabinet-result', ['period_id' => $period_id, 'city_id' => $city_id, 'cabinet_id' => $cabinet_id], key('cr'))
    </div>
    @endif
</div>
