<?php

use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/admin/orders', function () {
    return view('liveware', [
        'title' => 'Заказы',
        'live' => 'orders-table-view',
        'route' => 'orders-add',
        'name' => Auth::user()->id_cms_privileges == Users::ROLE_DISPETCHER ? '' : 'Создать',
        'icon' => 'plus',
        'ico_class' => 'text-green-600'
    ]);
})->name('orders');

Route::get('/admin/orders/view/{orders}', function (\App\Models\Orders $orders) {
    if (!$orders || ($orders->cabinet_id != \App\Models\Cabinet::curCabinet()->id)) abort(404);
    return view('liveware', [
        'title' => 'Заказы',
        'live' => 'orders-detail-view',
        'route' => 'orders',
        'name' => 'Список заказов',
        'model' => $orders,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->scopeBindings()->name('orders-view');

Route::get('/admin/orders/edit/{orders}', function (\App\Models\Orders $orders) {
    if (!$orders || ($orders->cabinet_id != \App\Models\Cabinet::curCabinet()->id)) abort(404);
    return view('liveware', [
        'title' => 'Заказы',
        'live' => 'orders-edit',
        'route' => 'orders',
        'name' => 'Список заказов',
        'model' => $orders,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->scopeBindings()->name('orders-edit');

Route::post('/admin/orders/edit/{orders}', function (\App\Models\Orders $orders) {
    if (!$orders || ($orders->cabinet_id != \App\Models\Cabinet::curCabinet()->id)) abort(404);
    return view('liveware', [
        'title' => 'Заказы',
        'live' => 'orders-edit',
        'route' => 'orders',
        'name' => 'Список заказов',
        'model' => $orders,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('orders-edit');

Route::get('/admin/orders/add', function () {
    $orders = new \App\Models\Orders();
    return view('liveware', [
        'title' => 'Заказы',
        'live' => 'orders-edit',
        'route' => 'orders',
        'name' => 'Список заказов',
        'model' => $orders,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('orders-add');

Route::post('/admin/orders/add', function () {
    $orders = new \App\Models\Orders();
    return view('liveware', [
        'title' => 'Заказы',
        'live' => 'orders-edit',
        'route' => 'orders',
        'name' => 'Список заказов',
        'model' => $orders,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('orders-add');
