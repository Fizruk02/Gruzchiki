<?php

Route::get('/settings/dispatchers', function () {
    return view('liveware', [
        'title' => 'Диспетчера',
        'live' => 'dispatcher-table-view',
        'route' => 'disp-add',
        'name' => 'Создать',
        'icon' => 'plus',
        'ico_class' => 'text-green-600'
    ]);
})->name('dispatchers');

Route::get('/settings/dispatchers/edit/{dispatcher}', function (\App\Models\Dispatcher $dispatcher) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if (($dispatcher->cabinet_id != $cabinet->id) || ($dispatcher->id_cms_privileges != 5))
        abort(404);

    return view('liveware', [
        'title' => 'Диспетчера',
        'live' => 'dispatcher-edit',
        'route' => 'dispatchers',
        'name' => '',
        'model' => $dispatcher,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('disp-edit');

Route::post('/settings/dispatchers/edit/{dispatcher}', function (\App\Models\Dispatcher $dispatcher) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if ($dispatcher->cabinet_id != $cabinet->id)
        abort(404);

    return view('liveware', [
        'title' => 'Диспетчера',
        'live' => 'dispatcher-edit',
        'route' => 'dispatchers',
        'name' => '',
        'model' => $dispatcher,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->scopeBindings()->name('disp-edit');

Route::get('/settings/dispatchers/add', function () {
    $dispatcher = new \App\Models\Dispatcher();
    return view('liveware', [
        'title' => 'Новый диспетчер',
        'live' => 'dispatcher-edit',
        'route' => 'dispatchers',
        'name' => '',
        'model' => $dispatcher,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('disp-add');

Route::post('/settings/dispatchers/add', function () {
    $dispatcher = new \App\Models\Dispatcher();
    return view('liveware', [
        'title' => 'Новый диспетчер',
        'live' => 'dispatcher-edit',
        'route' => 'dispatchers',
        'name' => '',
        'model' => $dispatcher,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('disp-add');
