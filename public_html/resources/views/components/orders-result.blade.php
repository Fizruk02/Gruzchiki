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
    $status = $model->status ? 'Отправлен' : 'Не отправлен';
    $type = $model->type_send == 0 ? 'Без отправления' : ($model->type_send == 1 ? 'Мгновенная': 'Отложенная');
    if ($model->type_send == 2) $time = date('d.m.Y H:i', $model->send_at);
    else $time = '-';
    $count_s = \App\Models\OrdersUsers::where('order_id', $model->id)->where('status', '>', 0)->count();
    $count_e = \App\Models\OrdersUsers::where('order_id', $model->id)->where('status', '=', 0)->count();
    $summ = number_format($model->balance->profit - $model->balance->expense, 2, '.', ' ');
    //dump($model->balance)
@endphp
<div x-data="{
                id: {{ $model->id }},
                view_tg: false
            }">
    <div class="px-4">
        <div class="mb-5">
            <div class="flex">
                <div class="bg-yellow-200 p-1 px-3 mr-3">Прибыль</div>
                <div class="p-1 px-3"><span class="bg-blue-300 text-white px-1 font-bold">{{$summ}}</span></div>
            </div>
            <div class="text-gray-600 font-bold">(Прибыль считается автоматически после сохранения заказа)</div>
        </div>
        <div class="font-bold">Статус</div>
        <div>{{$status}}</div>
        <div class="font-bold">Тип отправки</div>
        <div>{{$type}}</div>
        <div class="font-bold">Время отправки</div>
        <div>{{$time}}</div>
        <div class="font-bold">Статистика рассылки</div>
        <div>
            (<a @click="if (view_tg) { view_tg = false; } else view_tg = true;" class="cursor-pointer text-blue-500">{{$count_s}}</a>
            /
            <a @click="if (view_tg) { view_tg = false; } else view_tg = true;" class="cursor-pointer text-blue-500">{{$count_e}}</a>)
        </div>
    </div>

    <div  x-show="view_tg2">
        <div class="px-4 pt-3">
            <div class="p-0 text-2xl mb-2 font-bold">Статистика</div>

            <div class="p-0 text-xl font-bold">Заказ #{{ $model->id }}</div>
            <div class="p-1 border border-yellow-300 mb-2">Рассылка {{$model->send_at ? date('d.m.Y', $model->send_at) : '-'}} в {{$model->send_at ? date('H:i', $model->send_at) : '-'}}</div>

            <div class="font-bold">Всего отправлено</div>
            <div>{{$count_s+$count_e}}</div>
            <div class="font-bold">Из них успешно</div>
            <div>{{$count_s}}</div>
            <div class="font-bold">С ошибкой</div>
            <div>{{$count_e}}</div>

            <div class="mt-3 font-bold">Дата рассылки</div>
            <div>{{$model->start_at ? date('d.m.Y', $model->start_at) : '-'}}</div>
            <div class="font-bold">Время начала</div>
            <div>{{$model->start_at ? date('H:i:s', $model->start_at) : '-'}}</div>
            <div class="font-bold">Время окончания</div>
            <div>{{$model->end_at ? date('H:i:s', $model->end_at) : '-'}}</div>
        </div>

        <div class="px-4 pt-5">
            <div class="px-0 text-xl font-bold"><div class="float-left bg-gray-600 rounded-full mr-2 text-white px-3 py-1"><div style="margin-top: -4px;">{{$count_s}}</div></div> Отправка успешна</div>
        </div>
        @livewire('orders-t-g', ['order_id' => $model->id, 'status_id' => 1], key('stg-'.$model->id))

        <div class="px-4 pt-5">
            <div class="px-0 text-xl font-bold"><div class="float-left bg-gray-600 rounded-full mr-2 text-white px-3 py-1"><div style="margin-top: -4px;">{{$count_e}}</div></div> Отправка с ошибкой</div>
        </div>
        @livewire('orders-t-g', ['order_id' => $model->id, 'status_id' => 0], key('etg-'.$model->id))
    </div>
</div>
