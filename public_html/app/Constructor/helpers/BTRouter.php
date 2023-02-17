<?php

namespace App\Constructor\helpers;


use App\Constructor\middlewares\BTAuthAPI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

require_once __DIR__.'/Helper.php';

class BTRouter
{
    private static $bt_namespace = '\App\Constructor\controllers';

    public static function getBTControllerFiles() {
        $controllers = glob(__DIR__.'/../controllers/*.php');

        $result = [];
        foreach ($controllers as $file) {
            $result[] = str_replace('.php', '', basename($file));
        }

        return $result;
    }

    private static function apiRoute() {
        // API Authentication
        Route::group(['middleware'=>['api'],'namespace'=>static::$bt_namespace], function() {
            Route::post("api/get-token","ApiAuthorizationController@postGetToken");
        });

        Route::group(['middleware' => ['api', BTAuthAPI::class], 'namespace' => 'App\Http\Controllers'], function () {

            $dir = scandir(base_path("app/Http/Controllers"));
            foreach ($dir as $v) {
                $v = str_replace('.php', '', $v);
                $names = array_filter(preg_split('/(?=[A-Z])/', str_replace('Controller', '', $v)));
                $names = strtolower(implode('_', $names));

                if (substr($names, 0, 4) == 'api_') {
                    $names = str_replace('api_', '', $names);
                    Route::any('api/'.$names, $v.'@execute_api');
                }
            }

        });
    }

    private static function uploadRoute() {
        Route::group([
            'middleware' => ['web'],
            'namespace' => static::$bt_namespace], function () {
            Route::get('api-documentation', ['uses' => 'ApiCustomController@apiDocumentation', 'as' => 'apiDocumentation']);
            Route::get('download-documentation-postman', ['uses' => 'ApiCustomController@getDownloadPostman', 'as' => 'downloadDocumentationPostman']);
            Route::get('uploads/{one?}/{two?}/{three?}/{four?}/{five?}', ['uses' => 'FileController@getPreview', 'as' => 'fileControllerPreview']);
        });
    }

    private static function authRoute() {
        Route::group(['middleware' => ['web'], 'prefix' => config('crudbooster.ADMIN_PATH'), 'namespace' => static::$bt_namespace], function () {

            Route::post('unlock-screen', ['uses' => 'AdminController@postUnlockScreen', 'as' => 'postUnlockScreen']);
            Route::get('lock-screen', ['uses' => 'AdminController@getLockscreen', 'as' => 'getLockScreen']);
            Route::post('forgot', ['uses' => 'AdminController@postForgot', 'as' => 'postForgot']);
            Route::get('forgot', ['uses' => 'AdminController@getForgot', 'as' => 'getForgot']);
            Route::post('register', ['uses' => 'AdminController@postRegister', 'as' => 'postRegister']);
            Route::get('register', ['uses' => 'AdminController@getRegister', 'as' => 'getRegister']);
            Route::get('logout', ['uses' => 'AdminController@getLogout', 'as' => 'getLogout']);
            Route::post('login', ['uses' => 'AdminController@postLogin', 'as' => 'postLogin']);
            Route::get('login', ['uses' => 'AdminController@getLogin', 'as' => 'getLogin']);
        });
    }

    private static function userControllerRoute() {
        Route::group([
            'middleware' => ['web', '\App\Constructor\middlewares\BTBackend'],
            'prefix' => config('crudbooster.ADMIN_PATH'),
            'namespace' => 'App\Http\Controllers',
        ], function () {

            $modules = [];
            try {
                // Todo: change table
                $modules = db('cms_moduls')
                    ->where('path', '!=', '')
                    ->where('controller', '!=', '')
                    ->whereNotNull("path")
                    ->whereNotNull("controller")
                    ->where('is_protected', 0)
                    ->where('deleted_at', null)
                    ->get();
            } catch (\Exception $e) {
                Log::error("Load cms moduls is failed. Caused = " . $e->getMessage());
            }

            foreach ($modules as $v) {
                if (@$v->path && @$v->controller) {
                    try {
                        BTBooster::routeController($v->path, $v->controller);
                    } catch (\Exception $e) {
                        Log::error("Path = ".$v->path."\nController = ".$v->controller."\nError = ".$e->getMessage());
                    }
                }
            }

        });
    }

    private static function btRoute() {
        /*Route::middleware([
            'auth:sanctum',
            config('jetstream.auth_session'),
            'verified'
        ])->group(function () {
            Route::get('/dashboard', function () {
                return view('dashboard');
            })->name('dashboard');
        });*/

        Route::group([
            'middleware' => ['web', '\App\Constructor\middlewares\BTBackend'],
            'prefix' => config('crudbooster.ADMIN_PATH'),
            'namespace' => static::$bt_namespace,
        ], function () {

            // Todo: change table
            if (request()->is(config('crudbooster.ADMIN_PATH'))) {
                $menus = db('cms_menus')->where('is_dashboard', 1)->first();
                if ($menus) {
                    Route::get('/', 'StatisticBuilderController@getDashboard');
                } else {
                    BTBooster::routeController('/', 'AdminController', static::$bt_namespace);
                }
            }


            BTBooster::routeController('api_generator', 'ApiCustomController', static::$bt_namespace);

            // Todo: change table
            $modules = [];
            try {
                $modules = db('cms_moduls')->whereIn('controller', BTRouter::getBTControllerFiles())->get();
            } catch (\Exception $e) {
                Log::error("Load cms moduls is failed. Caused = " . $e->getMessage());
            }

            foreach ($modules as $v) {
                if (@$v->path && @$v->controller) {
                    try {
                        BTBooster::routeController($v->path, $v->controller, static::$bt_namespace);
                    } catch (\Exception $e) {
                        Log::error("Path = ".$v->path."\nController = ".$v->controller."\nError = ".$e->getMessage());
                    }
                }
            }
        });
    }

    public static function route() {

        static::apiRoute();
        static::uploadRoute();
        //static::authRoute();
        static::userControllerRoute();
        static::btRoute();
    }

}
