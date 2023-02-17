<?php
use Illuminate\Support\Facades\Route;
use App\Models\ExelExport;
use App\Models\Users;
use Maatwebsite\Excel\Facades\Excel;

/*Route::get('/export', function () {
    $data = new ExelExport([['aaa','aaaaa'],['aaa','bbbb'],['ccc',1]]);
    //dd(debug_backtrace());
    return Excel::download($data, 'users.xlsx');
})->name('export');

Route::get('/test', function () {
    $u = Users::with(['cabinet', 'users_profiles'])->where('id', 156)->first();
    dump($u);
});*/
