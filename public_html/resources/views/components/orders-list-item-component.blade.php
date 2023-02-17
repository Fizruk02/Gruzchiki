{{--
<x-lv-actions :actions="$actions" :model="$model" />
<x-lv-actions.drop-down :actions="$actions" :model="$model" />
--}}
<?php
use App\Models\Users;
?>
@props(['title', 'subtitle', 'actions', 'model', 'data', 'number', 'phone'])

@php
    $users = \App\Models\OrdersUsers::where('order_id', $model->id)->where('status', '>=', 1)->orderBy('status', 'desc')->get();
    $count_views = 0;
    foreach ($users as $user) {
        if ($user->status == 1) $count_views++;
    }
@endphp

<div class="mt-4 mb-8">
    <div>
        <div class="mb-2">
            <div class="items-center space-x-4">
                <div class="flex-1">
                      <div class="text-sm font-bold text-gray-900">
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-500 border-gray-500 border-0 font-bold text-white">
                                {!! $count_views !!}
                            </span>
                            <span class="px-1 py-1 inline-flex text-xs leading-5 rounded-full bg-gray-500 border-gray-500 border-2 text-white">
                                <i data-feather="eye" class="feather-12"></i>
                            </span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border-yellow-200 border-2 font-bold">
                                {!! $data !!}
                            </span>
                            @if (Auth::user()->id_cms_privileges != Users::ROLE_DISPETCHER)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-200 border-yellow-200 border-2 font-bold">
                                  Тел.: <span class="text-blue-700 mx-1">{!! $phone !!}</span>
                            </span>
                            @endif
                            @if (!$model->active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-200 border-yellow-200 border-2 font-bold">
                                  В {{$model->balance->profit}} Зат {{$model->balance->expense}} П {{$model->balance->profit - $model->balance->expense}}
                            </span>
                            @endif
                            {!! $document !!} {!! $title !!}
                      </div>
                </div>
            </div>
        </div>

        <div>
            <div>
                <div class="flex flex-wrap">
                    <button @click="if (editing{{$model->id}}) { editing{{$model->id}} = false; } else editing{{$model->id}} = true;"
                            class="flex mb-1 p-1 px-2 mr-1 border-2 border-gray-300 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out whitespace-no-wrap"
                            :class="editing{{$model->id}} ? 'bg-gray-700 text-white border-gray-700' : 'text-gray-900'"
                    >
                        <i data-feather="user"></i>
                        Показать откликнувшихся
                    </button>

                    @if (\Illuminate\Support\Facades\Auth::user()->id_cms_privileges == \App\Models\Users::ROLE_ADMIN)
                    <button wire:click.prevent="executeAction('redirect-action-orders-edit', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-gray-300 text-blue-700 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out whitespace-no-wrap">
                        <i data-feather="edit" class=""></i>
                        Редактировать
                    </button>
                    @else
                        <button wire:click.prevent="executeAction('redirect-action-orders-view', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-gray-300 text-blue-700 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            <i data-feather="edit" class=""></i>
                            Редактировать
                        </button>
                    @endif

                    @if ($model->active && !$model->status)
                        <button wire:click.prevent="executeAction('send-order-action', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-yellow-300 bg-yellow-300 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            <i data-feather="mail" class=""></i>
                            Разослать
                        </button>
                    @else
                        <button wire:click.prevent="executeAction('send-order-action', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-red-700 bg-yellow-300 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            <i data-feather="mail" class=""></i>
                            Разослать
                        </button>
                    @endif

                    @if ($model->active)
                        <button wire:click.prevent="executeAction('close-order-action', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-orange-300 bg-orange-300 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            <i data-feather="eye-off" class=""></i>
                            Закрыть
                        </button>
                    @else
                        <button wire:click.prevent="executeAction('open-order-action', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-blue-300 bg-blue-300 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            <i data-feather="eye" class="mr-1"></i>
                            Открыть
                        </button>
                    @endif

                    <button wire:click.prevent="executeAction('delete-order-action', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-red-500 bg-red-500 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                        <i data-feather="trash" class=""></i>
                        Удалить
                    </button>
                </div>

                <div x-show="!modaling">
                    <div class="" wire:poll.visible.30s>
                    </div>
                </div>
                <div class="bg-gray-100 my-3 p-0 rounded-xl border-2 border-gray-300 h-[2rem] flex overflow-auto">
                    @php
                        $count = intval($model->getField('need_employ'));
                        $w = $count ? number_format(100/$count, 2, '.', '') : 0;
                        $i = 0;
                        foreach ($users as $user) {
                            if(!$user->approved) continue;
                            $c = 'bg-gray-100';
                            $title = '';
                            if ($user->status == 3) {$c = 'bg-gray-200';$title='Подтвердил заказ';}
                            else if ($user->status == 4) {$c = 'bg-gray-300';$title='Одобрен';}
                            else if ($user->status == 5) {$c = 'bg-yellow-300';$title='Выехал';}
                            else if ($user->status == 6) {$c = 'bg-green-300';$title='На месте';}
                            else if ($user->status == 7) {$c = 'bg-blue-300';$title='Заказ выполнен';}
                            else if ($user->status == 8) {$c = 'bg-purple-300';$title='Оплата получена';}
                            else if ($user->status == 100) {$c = 'bg-red-700';$title='Возникла проблема';}
                            echo '<span class="inline-flex '.$c.'" style="width:'.$w.'%;" title="'.$title.'">&nbsp;</span>';
                            $i++;
                            if ($i == $count) break;
                        }
                    @endphp
                </div>

                <div x-show="editing{{$model->id}}" class="w-full overflow-x-hidden">
                    <div x-cloak
                       x-ref="input"
                       class="block"
                    >
                        {{--
                        @livewire('employees-orders-table-view') data-modal-toggle="notifysend-modal"
                        x-data="{ modal{{$model->id}}: false, modalObj:false }" x-init="modalObj=document.getElementById('notifysend-modal'); modal{{$model->id}} = new Modal(modalObj, options);"
                        @click="modal{{$model->id}}.show();"
                        --}}
                        @livewire('orders-user-table-view', ['order_id' => $model->id], key('ous-'.$model->id))
                    </div>
                    <div class="float-left flex flex-wrap">
                        <button @click="modaling = true;" data-modal-toggle="notifysend_modal{{$model->id}}" class="flex mb-1 p-1 mr-1 px-2 border-2 hover:text-white hover:bg-gray-700 hover:border-gray-700 rounded-full hover:text-white hover:bg-gray-700 border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Напомнить
                        </button>
                        <div class="flex mb-1 pt-1 mr-1">
                            @if ($model->notify_at)
                                {{date('d.m.Y H:i', $model->notify_at)}}
                            @endif
                        </div>
                    </div>

                    <div class="md:float-right md:flex flex-wrap">
                        <button wire:click.prevent="executeAction('approve-order-action', '{{$model->id}}')" class="flex mb-1 p-1 mr-1 px-2 border-2 border-yellow-300 bg-yellow-300 text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Подтвердить
                        </button>
                        <button @click="modaling = true;" data-modal-toggle="msgsend_modal{{$model->id}}" class="flex mb-1 p-1 mr-1 px-2 border-2 hover:text-white hover:bg-gray-700 hover:border-gray-700 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Сообщение утвержденным
                        </button>
                        <button wire:click.prevent="sendAproved({{$model->id}}, message_apr{{$model->id}}.value)" class="flex mb-1 p-1 mr-1 px-2 border-2 border-yellow-300 bg-white text-gray-900 rounded-full hover:text-white hover:bg-gray-700 hover:border-gray-700 focus:outline-none focus:text-gray-700 focus:bg-gray-100 transition duration-150 ease-in-out">
                            Отправить
                        </button>
                    </div>
                </div>
            </div>
            <!-- Main modal -->
            <div id="notifysend_modal{{$model->id}}" data-modal-placement="center" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full justify-center items-center">
                <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                    <!-- Modal content -->
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <button @click="modaling = false;" type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="notifysend_modal{{$model->id}}">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Закрыть</span>
                        </button>
                        <div class="py-6 px-6 lg:px-8">
                            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Напомнить</h3>
                            <div>
                                <label for="notify_at{{$model->id}}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Дата и время</label>
                                <input type="text" name="notify_at{{$model->id}}" id="notify_at{{$model->id}}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                                       rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                       placeholder="Дата и время" value="{{date('d.m.Y H:i', $model->notify_at ? $model->notify_at : time())}}" required>
                            </div>
                            <button @click="modaling = false;" wire:click.prevent="setNotify({{$model->id}}, notify_at{{$model->id}}.value)" data-modal-toggle="notifysend_modal{{$model->id}}" type="button" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="msgsend_modal{{$model->id}}" data-modal-placement="center" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full justify-center items-center">
                <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                    <!-- Modal content -->
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <button @click="modaling = false;" type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="msgsend_modal{{$model->id}}">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Закрыть</span>
                        </button>
                        <div class="py-6 px-6 lg:px-8">
                            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Сообщение утвержденным</h3>
                            <div>
                                <input type="text" name="message_apr{{$model->id}}" id="message_apr{{$model->id}}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                                       rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                       placeholder="Введите текст сообщения" value="" required>
                            </div>
                            <button @click="modaling = false;" data-modal-toggle="msgsend_modal{{$model->id}}" type="button" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            {{--
            @push('bottom')
                <script type="text/javascript">
                    var targetEl = document.getElementById('notifysend-modal{{$model->id}}');
                    var options = {
                        placement: 'bottom-right',
                        backdropClasses: 'bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-40',
                        onHide: () => {
                            console.log('modal is hidden');
                        },
                        onShow: () => {
                            console.log('modal is shown');
                        },
                        onToggle: () => {
                            console.log('modal has been toggled');
                        }
                    };
                    var modal = new Modal(targetEl, options);
                </script>
            @endpush
            --}}
            <!-- Main modal -->
        </div>
    </div>
</div>
