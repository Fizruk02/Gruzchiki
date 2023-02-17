<?php
use App\Models\Users;
?>
@if (Auth::user())
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Главная') }}
            </h2>
        </x-slot>
    </x-app-layout>
@else
    <x-main-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Главная') }}
            </h2>
        </x-slot>
    </x-main-layout>
    {{--
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 overflow-y-visible">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Главная
        </h2>
        <a href="/login">Войти</a>
    </div>
    --}}
@endif
{{--
@if ((Auth::user()->id_cms_privileges != Users::ROLE_SUPERADMIN) && (strtotime(\App\Models\Cabinet::curCabinet()->finish_at) < time()))
    @include('buy')
@else
    @if (Auth::user()->id_cms_privileges == Users::ROLE_ADMIN)
        @include('ok')
    @endif
@endif
--}}
