<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/admin/settings/bots', function () {
    //dump(Auth::user());
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Чат-боты',
        'live' => 'bots-table-view',
        'route' => 'bot-add',
        'name' => 'Создать',
        'icon' => 'plus',
        'ico_class' => 'text-green-600'
    ]);
    //return redirect('/home');
})->name('bots');

Route::get('/admin/settings/bots/edit/{bot}', function (\App\Models\Bot $bot) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if ($bot->cabinet_id != $cabinet->id)
        abort(404);

    return view('liveware', [
        'title' => 'Чат-бот',
        'live' => 'bot-edit',
        'route' => 'bots',
        'name' => '',
        'model' => $bot,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('bot-edit');

Route::post('/admin/settings/bots/edit/{bot}', function (\App\Models\Bot $bot) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if ($bot->cabinet_id != $cabinet->id)
        abort(404);

    return view('liveware', [
        'title' => 'Чат-бот',
        'live' => 'bot-edit',
        'route' => 'bots',
        'name' => '',
        'model' => $bot,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('bot-edit');

Route::get('/admin/settings/bots/add', function () {
    $bot = new \App\Models\Bot();
    return view('liveware', [
        'title' => 'Новый чат-бот',
        'live' => 'bot-edit',
        'route' => 'bots',
        'name' => '',
        'model' => $bot,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('bot-add');

Route::post('/admin/settings/bots/add', function () {
    $bot = new \App\Models\Bot();
    return view('liveware', [
        'title' => 'Новый чат-бот',
        'live' => 'bot-edit',
        'route' => 'bots',
        'name' => '',
        'model' => $bot,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('bot-add');
