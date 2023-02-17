<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin/users', function () {
    $page = new \App\Models\WebPages();
    $page->params = [
        'cur' => 0,
        'buttons' => [
            ['name' => 'Все пользователи', 'link' => '/users'],
            ['name' => 'Забанненые пользователи', 'link' => '/users-ban'],
            ['name' => 'Черный список', 'link' => '/users-black']
        ],
    ];
    return view('liveware', [
        'title' => 'Пользователи',
        'live' => 'employees-table-view',
        'route' => 'dashboard',
        'name' => '',
        'model' => $page,
    ]);
})->name('users');

Route::get('/admin/users/view/{users}', function (\App\Models\Users $users) {
    //dump(Auth::user());
    //return view('crudbooster::home');
    return view('liveware', [
        'title' => 'Пользователи',//.$users->name,
        'live' => 'users-detail-view',
        'route' => 'users',
        'name' => 'Список пользователей',
        'model' => $users,
    ]);
    //return redirect('/home');
})->scopeBindings()->name('users-view');
