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
@php
    $start = request()->get('profit_start', date('d.m.Y'));
    $end = request()->get('profit_end', date('d.m.Y'));
    //dump($bot_id);
@endphp
<div class="flex flex-wrap pt-4">
    <div class="sm:w-3/12 px-4">
        {{--<form wire:model="profit" wire:submit.prevent="submit" class="w-full">--}}
        <form action="" class="w-full">
        <div class="mb-5">
            <div class="flex mb-2">
                <select class='w-full border-yellow-200 transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent' id="bot_id" name="bot_id">
                    <option value='0'>--Все боты--</option>
                    @foreach($bots as $bot)
                        <?php
                        $select = ($this->bot_id == $bot->id) ? "selected" : "";
                        ?>
                        <option {{$select}} value='{{$bot->id}}'>{{$bot->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex mb-2">
                <div class="input-group w-full rounded overflow-hidden">
                    <div class="flex justify-left">
                        <div class="relative w-full">
                            <div class="flex absolute inset-y-0 px-0 pr-2 items-center pl-3 pointer-events-none bg-yellow-400">
                                От
                            </div>
                            <div class="flex absolute inset-y-0 right-0 pr-2 items-center pl-3 pointer-events-none bg-yellow-400">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                            </div>

                            <div x-data="{ datetimeFields: '' }" x-init="datetime_profit_start;">
                                <input type='text' datepicker-format="dd.mm.yyyy" datepicker title="От" readonly id="datetime_profit_start" name="profit_start"
                                       class='border-yellow-200 notfocus pl-12 input_date transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent w-full'
                                       value='{{$start}}' />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex mb-2">
                <div class="input-group w-full rounded overflow-hidden">
                    <div class="flex justify-left">
                        <div class="relative w-full">
                            <div class="flex absolute inset-y-0 px-0 pr-2 items-center pl-3 pointer-events-none bg-yellow-400">
                                По
                            </div>
                            <div class="flex absolute inset-y-0 right-0 pr-2 items-center pl-3 pointer-events-none bg-yellow-400">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                            </div>

                            <div x-data="{ datetimeFields: '' }" x-init="datetime_profit_end;">
                                <input type='text' datepicker-format="dd.mm.yyyy" datepicker title="От" readonly id="datetime_profit_end" name="profit_end"
                                       class='border-yellow-200 notfocus pl-12 input_date transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent w-full'
                                       value='{{$end}}' />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex mt-5">
                <button class="p-1 px-2 border-2 border-yellow-400 bg-yellow-300 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out" type="submit">Подсчитать</button>
            </div>
        </div>
        </form>
    </div>
    @if(request()->get('bot_id') !== null)
    <div class="sm:w-9/12 px-4">
        <div class="text-2xl mb-3">Финансовый отчет</div>
        <div class="flex mb-1">
            <span class="mr-3 sm:w-2/12">Период</span><span class="bg-yellow-200 p-1 px-3 mr-3">с {{$result['start']}} по {{$result['end']}}</span>
        </div>
        <div class="flex mb-1">
            <span class="mr-3 sm:w-2/12">Заказов</span><span class="bg-yellow-200 p-1 px-3 mr-3">{{$result['orders']}}</span>
        </div>
        <div class="flex mb-1">
            <span class="mr-3 sm:w-2/12">Выручка</span><span class="bg-yellow-200 p-1 px-3 mr-3">{{number_format($result['profit'], 2, '.', ' ')}}</span>
        </div>
        <div class="flex mb-1">
            <span class="mr-3 sm:w-2/12">Затраты</span><span class="bg-yellow-200 p-1 px-3 mr-3">{{number_format($result['expense'], 2, '.', ' ')}}</span>
        </div>
        <div class="flex mb-1">
            <span class="mr-3 sm:w-2/12">Прибыль</span><span class="bg-yellow-200 p-1 px-3 mr-3">{{number_format($result['profit'] - $result['expense'], 2, '.', ' ')}}</span>
        </div>
        <div class="flex mb-1">
            <span class="mr-3 sm:w-2/12">Долг</span><span class="bg-yellow-200 p-1 px-3 mr-3">{{number_format($result['debt'], 2, '.', ' ')}}</span>
        </div>
        @livewire('orders-debit', [], key('od'))
    </div>
    @endif
</div>
@push('bottom')
    <script type="text/javascript">
        function datetime_profit_start() {
            const datepickerEl = document.getElementById('datetime_profit_start');
            new Datepicker(datepickerEl, {
                // options
                format: "dd.mm.yyyy",
                language: "ru",
            });
        }
        function datetime_profit_end() {
            const datepickerEl = document.getElementById('datetime_profit_end');
            new Datepicker(datepickerEl, {
                // options
                format: "dd.mm.yyyy",
                language: "ru",
            });
        }
    </script>
@endpush
