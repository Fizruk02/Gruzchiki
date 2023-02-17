<?php
use App\Models\Users;
?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-green-100 border-2 border-green-900 overflow-hidden shadow-xl sm:rounded-lg p-5 text-green-900">
            @php
            $phone = \App\Models\Users::where('id_cms_privileges', Users::ROLE_SUPERADMIN)->first()->phone;
            $date = \App\Models\Cabinet::curCabinet()->finish_at;
            @endphp
            <div class="text-2xl">Срок работы кабинета истекает {{$date}}</div>
            <div>Для продления кабинета, свяжитесь с администратором по телефону {{$phone}}</div>
        </div>
    </div>
</div>
