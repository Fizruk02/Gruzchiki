<?php

Route::get('/settings/landing/edit', function (\App\Models\Cabinet $cabinet) {
    //dd(Auth::user());
    $cabinet = \App\Models\Cabinet::curCabinet();
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Настройки',
        'live' => 'landing-edit',
        'route' => 'dashboard',
        'name' => '',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('landing-edit');

Route::post('/settings/landing/edit', function (\App\Models\Cabinet $cabinet) {
    //dd(Auth::user());
    $cabinet = \App\Models\Cabinet::curCabinet();
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Настройки',
        'live' => 'landing-edit',
        'route' => 'landing-edit',
        'name' => 'Главная',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('landing-edit');
