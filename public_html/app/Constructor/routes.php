<?php

use App\Constructor\helpers\BTRouter;

BTRouter::route();
//config(['crudbooster.ADMIN_PATH' => '']);
//BTRouter::route();
app('view')->addNamespace('crudbooster', __DIR__.'/views');
//dd(\Illuminate\Support\Facades\Route::getRoutes());
