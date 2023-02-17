<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin/request', function () {
    return view('liveware', [
        'title' => 'Заявки',
        'live' => 'request-table-view',
        'route' => 'dashboard',
        'name' => '',
        'icon' => '',
        'ico_class' => ''
    ]);
})->name('request');

Route::get('/admin/request/view/{request}', function (\App\Models\Request $request) {
    if (!$request || ($request->cabinet_id != \App\Models\Cabinet::curCabinet()->id)) abort(404);
    //dump(Auth::user());
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Заявки',
        'live' => 'request-detail-view',
        'route' => 'request',
        'name' => 'Список заявок',
        'model' => $request,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('request-view');
