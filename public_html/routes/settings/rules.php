<?php

Route::get('/settings', function () {
    $page = new \App\Models\WebPages();
    return view('liveware', [
        'title' => 'Настройки',
        'live' => 'page',
        'route' => 'dashboard',
        'name' => 'Главная',
        'model' => $page,
    ]);
})->name('settings');

Route::get('/settings/rules/edit', function (\App\Models\Cabinet $cabinet) {
    //dd(Auth::user());
    $cabinet = \App\Models\Cabinet::curCabinet();
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Настройки',
        'live' => 'rules-edit',
        'route' => 'dashboard',
        'name' => '',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('rules-edit');

Route::post('/settings/rules/edit', function (\App\Models\Cabinet $cabinet) {
    //dd(Auth::user());
    $cabinet = \App\Models\Cabinet::curCabinet();
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Настройки',
        'live' => 'rules-edit',
        'route' => 'rules-edit',
        'name' => 'Главная',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('rules-edit');
