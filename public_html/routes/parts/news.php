<?php
use Illuminate\Support\Facades\Route;

Route::get('/admin/news', function () {
    return view('liveware', [
        'title' => 'Новости',
        'live' => 'news-table-view',
        'route' => 'news-add',
        'name' => 'Создать',
        'icon' => 'plus',
        'ico_class' => 'text-green-600'
    ]);
})->name('news');

Route::get('/admin/news/edit/{news}', function (\App\Models\News $news) {
    if (!$news || ($news->cabinet_id != \App\Models\Cabinet::curCabinet()->id)) abort(404);
    return view('liveware', [
        'title' => 'Новости',
        'live' => 'news-edit',
        'route' => 'news',
        'name' => 'Список новостей',
        'model' => $news,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->scopeBindings()->name('news-edit');

Route::post('/admin/news/edit/{news}', function (\App\Models\News $news) {
    if (!$news || ($news->cabinet_id != \App\Models\Cabinet::curCabinet()->id)) abort(404);
    return view('liveware', [
        'title' => 'Новости',
        'live' => 'news-edit',
        'route' => 'news',
        'name' => 'Список новостей',
        'model' => $news,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
    //return redirect('/home');
})->scopeBindings()->name('news-edit');

Route::get('/admin/news/add', function () {
    $news = new \App\Models\News();
    return view('liveware', [
        'title' => 'Новости',
        'live' => 'news-edit',
        'route' => 'news',
        'name' => 'Список новостей',
        'model' => $news,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('news-add');

Route::post('/admin/news/add', function () {
    $news = new \App\Models\News();
    return view('liveware', [
        'title' => 'Новости',
        'live' => 'news-edit',
        'route' => 'news',
        'name' => 'Список новостей',
        'model' => $news,
        'icon' => 'list',
        'ico_class' => 'text-gray-600'
    ]);
})->name('news-add');
