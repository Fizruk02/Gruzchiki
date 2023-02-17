<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin/employees', function () {
    return view('liveware', [
        'title' => 'Сотрудники',
        'live' => 'employee-table-view',
        'route' => 'dashboard',
        'name' => '',
        'icon' => '',
        'ico_class' => 'text-green-600'
    ]);
    //return redirect('/home');
})->name('employees');

Route::get('/admin/employees/edit/{employee}', function (\App\Models\Employee $employee) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if (($employee->cabinet_id != $cabinet->id) || ($employee->id_cms_privileges != 2))
        abort(404);

    if ($employee->is_deleted)
        return redirect()->to('employees');

    return view('liveware', [
        'title' => 'Сотрудники',
        'live' => 'employee-edit',
        'route' => 'employees',
        'name' => '',
        'model' => $employee,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('employee-edit');

Route::post('/admin/employees/edit/{employee}', function (\App\Models\Employee $employee) {
    $cabinet = \App\Models\Cabinet::curCabinet();
    if (($employee->cabinet_id != $cabinet->id) || ($employee->id_cms_privileges != 2))
        abort(404);

    return view('liveware', [
        'title' => 'Сотрудники',
        'live' => 'employee-edit',
        'route' => 'employees',
        'name' => '',
        'model' => $employee,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->scopeBindings()->name('employee-edit');
