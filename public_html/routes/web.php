<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\TestCase;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::post('livewire/message/livewire-ui-modal', [\Livewire\Controllers\HttpConnectionHandler::class, '__invoke']);

Route::get('/', function () {
    return view('home');
})->name('GetIndex');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'timezone'
])->group(function () {
    Route::get('/admin/dashboard', function () {
        //dump(Auth::user());
        //return view('crudbooster::home');
        return view('home');
        //return redirect('/home');
    })->name('dashboard');

    //Route::post('livewire/message/{name}', [\Livewire\Controllers\HttpConnectionHandler::class, '__invoke']);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'orders',
    'timezone'
])->group(function () {
    require __DIR__.'/parts/orders.php';

    Route::get('/admin/employees/view/{employee}', function (\App\Models\Employee $employee) {
        $cabinet = \App\Models\Cabinet::curCabinet();
        if (($employee->cabinet_id != $cabinet->id) || ($employee->id_cms_privileges != 2))
            abort(404);

        if ($employee->is_deleted)
            return redirect()->to('employees');

        return view('liveware', [
            'title' => 'Сотрудники',
            'live' => 'employees-detail-view',
            //'route' => 'employees',
            //'name' => '',
            'model' => $employee,
            //'icon' => 'list',
            //'ico_class' => 'text-gray-600'
        ]);
        //return redirect('/home');
    })->scopeBindings()->name('employee-view');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'cabinet'
])->group(function () {
    $dirs = array_diff(scandir(__DIR__), ['..', '.']);
    foreach ($dirs as $dir) {
        $dname =  __DIR__ . '/' . $dir;
        if (!is_dir($dname)) continue;

        $files = array_diff(scandir($dname), ['..', '.']);
        foreach ($files as $file) {
            if ($file == 'orders.php') continue;
            require_once "$dname/$file";
        }
    }

    /*Route::get('/users-ban', function () {
        $page = new \App\Models\WebPages();
        $page->params = [
            'cur' => 1,
            'buttons' => [
                ['name' => 'Все пользователи', 'link' => '/users'],
                ['name' => 'Забанненые пользователи', 'link' => '/users-ban'],
                ['name' => 'Черный список', 'link' => '/users-black']
            ],
        ];
        return view('liveware', [
            'title' => 'Забанненые пользователи',
            'live' => 'page',
            'route' => 'dashboard',
            'name' => 'Главная',
            'model' => $page,
        ]);
    })->name('users-ban');

    Route::get('/users-black', function () {
        $page = new \App\Models\WebPages();
        $page->params = [
            'cur' => 2,
            'buttons' => [
                ['name' => 'Все пользователи', 'link' => '/users'],
                ['name' => 'Забанненые пользователи', 'link' => '/users-ban'],
                ['name' => 'Черный список', 'link' => '/users-black']
            ],
        ];
        return view('liveware', [
            'title' => 'Черный список',
            'live' => 'page',
            'route' => 'dashboard',
            'name' => 'Главная',
            'model' => $page,
        ]);
    })->name('users-black');*/


    Route::get('/admin/accounting', function () {
        return view('liveware', [
            'title' => 'Бухгалтерия',
            'live' => 'profit',
            'route' => 'dashboard',
            'name' => 'Главная',
        ]);
    })->name('accounting');


    //dump(__('Search'));
    //dump(Auth::user());
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'superadmin'
])->group(function () {
    require_once __DIR__.'/cabinets.php';
    require_once __DIR__.'/users.php';

    Route::get('/admin/statictics', function () {
        return view('liveware', [
            'title' => 'Статистика',
            'live' => 'statistics',
            'route' => 'dashboard',
            'name' => 'Главная',
            //'model' => new \App\Models\WebPages(),
        ]);
    })->name('statictics');

    Route::get('/admin/landing/{id}', function ($id) {
        $cabinet = \App\Models\Cabinet::where('id', $id)->first();
        return view('liveware', [
            'title' => 'Лендинг',
            'live' => 'landing-edit',
            'route' => 'dashboard',
            'name' => 'Главная',
            'model' => $cabinet,
            'params' => ['return_url' => '/admin/landing/'.$id],
        ]);
    })->name('admin-landing-edit');

    Route::post('/admin/landing/{id}', function ($id) {
        $cabinet = \App\Models\Cabinet::where('id', $id)->first();
        return view('liveware', [
            'title' => 'Лендинг',
            'live' => 'landing-edit',
            'route' => 'dashboard',
            'name' => 'Главная',
            'model' => $cabinet,
            'params' => ['return_url' => '/admin/landing/'.$id],
        ]);
    })->name('admin-landing-edit');

    Route::get('/admin/fields/order/{id}/', function ($id) {
        //$cabinet = \App\Models\Cabinet::where('id', $id)->first();
        return view('liveware', [
            'title' => 'Настройка заказа',
            'live' => 'orders-fields-table-view',
            'route' => ['name' => 'orders-fields-add', 'params' => ['id' => $id]],
            'name' => 'Добавить',
            'icon' => 'plus',
            'ico_class' => 'text-green-600',
            'scripts' => '@click="console.log(window.livewire);window.livewire.emit(\'add-field\', {id: '.$id.'});"',
            //'model' => $cabinet,
            'params' => ['cabinet_id' => $id],
        ]);
    })->name('orders-fields');

    Route::get('/admin/fields/order/{id}/add', function ($id) {
        $cabinet = \App\Models\Cabinet::where('id', $id)->first();
        $of = new \App\Models\OrdersFields();
        $of->cabinet_id = $id;
        $of->name = '-';
        $of->type = 'custom';
        $of->is_first = 0;
        $of->is_accept = 0;
        $of->is_2hours = 0;
        $of->is_30minutes = 0;
        $of->is_require = 0;
        $of->is_visible = 1;
        $of->class = 'w-full';
        $of->is_label = 0;
        $of->save();
        return redirect('/admin/fields/order/'.$id.'/');
    })->name('orders-fields-add');

    Route::get('/admin/fields/request/{id}/', function ($id) {
        //$cabinet = \App\Models\Cabinet::where('id', $id)->first();
        return view('liveware', [
            'title' => 'Настройка заявки',
            'live' => 'requests-fields-table-view',
            'route' => ['name' => 'request-fields-add', 'params' => ['id' => $id]],
            'name' => 'Добавить',
            'icon' => 'plus',
            'ico_class' => 'text-green-600',
            'scripts' => '@click="console.log(window.livewire);window.livewire.emit(\'add-field\', {id: '.$id.'});"',
            //'model' => $cabinet,
            'params' => ['cabinet_id' => $id],
        ]);
    })->name('request-fields');

    Route::get('/admin/fields/request/{id}/add', function ($id) {
        $cabinet = \App\Models\Cabinet::where('id', $id)->first();
        $of = new \App\Models\RequestFields();
        $of->cabinet_id = $id;
        $of->name = '-';
        $of->is_list = 0;
        $of->save();
        return redirect('/admin/fields/request/'.$id.'/');
    })->name('request-fields-add');

    require_once __DIR__.'/../app/Constructor/routes.php';
});

Route::get('/page/{landing}', function ($landing) {
    if(!$landing) abort(404);

    $cabinet = \App\Models\Cabinet::where('domain', $landing)->first();
    if (!$cabinet) abort(404);

    $request = \App\Models\RequestFields::where('cabinet_id', $cabinet->id)->orderBy('sort')->get();
    if (!$request->count()) abort(404);

    $phone_src = $cabinet->users->phone.'';
    $phone = substr($phone_src,0,1).'('.substr($phone_src,1,3).') '.substr($phone_src,4,3).'-'.substr($phone_src,7,2).'-'.substr($phone_src,9,2);
    return view('landing', [
        'cabinet_id' => $cabinet->id,
        'fields' => $request,
        'phone' => $phone,
        'phone_src' => $phone_src,
        'city' => $cabinet->city,
    ]);
})->name('landing');
