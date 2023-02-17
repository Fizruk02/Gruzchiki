<?php

use App\Models\OrdersFields;
use Illuminate\Support\Facades\Route;

Route::get('/admin/clients', function () {
    return view('liveware', [
        'title' => 'Клиенты',
        'route' => 'clients',
        'name' => '',
        'live' => 'client-table-view',
    ]);
})->name('clients');

Route::get('/admin/clients/view/{phone}', function ($phone) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    $phone_id = OrdersFields::where('type', 'client_phone')->where('cabinet_id', $cabinet->id)->first()->id;

    $ov = \App\Models\OrdersValues::query()
        ->select(['orders_values.*', 'orders.cabinet_id'])
        ->join('orders', function ($join) {
            $join->on('orders.id', '=', 'orders_values.orders_id');
        })
        ->where('orders.cabinet_id', $cabinet->id)
        ->where('orders_values.orders_fields_id', $phone_id)
        ->where('value', $phone)
        ->first();

    if (!$ov) abort(404);

    //$ov = new \App\Models\OrdersValues();
    //$ov->value = $phone;
    return view('liveware', [
        'title' => 'Клиенты',
        'live' => 'client-detail-view',
        'route' => 'clients',
        'name' => 'Список клиентов',
        'model' => $ov,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->scopeBindings()->name('clients-view');
