<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin/banned', function () {
    return view('liveware', [
        'title' => 'Заблокированные сотрудники',
        'live' => 'banned-table-view',
        'route' => 'dashboard',
        'name' => '',
        'icon' => '',
        'ico_class' => 'text-green-600'
    ]);
    //return redirect('/home');
})->name('banned');

Route::get('/admin/banned/edit/{employee}', function (\App\Models\Employee $employee) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if (($employee->cabinet_id != $cabinet->id) || ($employee->id_cms_privileges != 2))
        abort(404);

    return view('liveware', [
        'title' => 'Заблокированные сотрудники',
        'live' => 'employee-edit',
        'route' => 'banned',
        'name' => '',
        'model' => $employee,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('banned-edit');

Route::post('/admin/banned/edit/{employee}', function (\App\Models\Employee $employee) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if (($employee->cabinet_id != $cabinet->id) || ($employee->id_cms_privileges != 2))
        abort(404);

    return view('liveware', [
        'title' => 'Заблокированные сотрудники',
        'live' => 'employee-edit',
        'route' => 'banned',
        'name' => '',
        'model' => $employee,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->scopeBindings()->name('banned-edit');
