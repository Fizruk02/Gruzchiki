<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin/cabinets', function () {
    //dump(Auth::user());
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Личные кабинеты',
        'live' => 'cabinet-table-view',
        'route' => 'cabinet-add',
        'name' => 'Создать',
        'icon' => 'plus',
        'ico_class' => 'text-green-600'
    ]);
    //return redirect('/home');
})->name('cabinets');

Route::get('/admin/cabinets/edit/{cabinet}', function (\App\Models\Cabinet $cabinet) {
    //dump(Auth::user());
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Личные кабинеты',
        'live' => 'cabinet-edit',
        'route' => 'cabinets',
        'name' => 'Список кабинетов',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('cabinet-edit');

Route::post('/admin/cabinets/edit/{cabinet}', function (\App\Models\Cabinet $cabinet) {
    //dd($cabinet);
    //dump(Auth::user());
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Кабинет: '.$cabinet->users->name,
        'live' => 'cabinet-edit',
        'route' => 'cabinets',
        'name' => 'Список кабинетов',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('cabinet-edit');

Route::get('/admin/cabinets/plus/{cabinet}', function (\App\Models\Cabinet $cabinet) {
    $dateAt = strtotime('+'.request()->get('plus').' MONTH', strtotime($cabinet->finish_at));
    $cabinet->finish_at = date('Y-m-d H:i:s', $dateAt);
    $cabinet->save();
    session()->flash('success', 'Кабинет '.$cabinet->users->name.' продлен.');
    return redirect()->back();
})->name('cabinet-plus');

Route::get('/admin/cabinets/add', function () {
    $cabinet = new \App\Models\Cabinet();
    $cabinet->users = new \App\Models\UserAdmin();
    return view('liveware', [
        'title' => 'Новый кабинет',
        'live' => 'cabinet-edit',
        'route' => 'cabinets',
        'name' => 'Список кабинетов',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('cabinet-add');

Route::post('/admin/cabinets/add', function () {
    $cabinet = new \App\Models\Cabinet();
    $cabinet->users = new \App\Models\UserAdmin();
    return view('liveware', [
        'title' => 'Новый кабинет',
        'live' => 'cabinet-edit',
        'route' => 'cabinets',
        'name' => 'Список кабинетов',
        'model' => $cabinet,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('cabinet-add');
