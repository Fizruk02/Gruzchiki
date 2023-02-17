<?php

namespace App\Constructor\middlewares;

use Closure;
use App\Constructor\helpers\BTBooster;
use Illuminate\Support\Facades\DB;

class BTBackend
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $admin_path = config('crudbooster.ADMIN_PATH') ?: 'admin';
        if (BTBooster::myId() == '') {
            $url = url($admin_path.'/login');

            return redirect($url)->with('message', cbLang('not_logged_in'));
        }
        if (BTBooster::isLocked()) {
            $url = url($admin_path.'/lock-screen');

            return redirect($url);
        }
        if($request->url()==BTBooster::adminPath('')){
            $menus=\Illuminate\Support\Facades\DB::table('cms_menus')->whereRaw("cms_menus.id IN (select id_cms_menus from cms_menus_privileges where id_cms_privileges = '".BTBooster::myPrivilegeId()."')")->where('is_dashboard', 1)->where('is_active', 1)->first();
            if ($menus) {
                if ($menus->type == 'Statistic') {
                    return redirect()->action('\App\Constructor\controllers\StatisticBuilderController@getDashboard');
                } elseif ($menus->type == 'Module') {
                    $module = BTBooster::first('cms_moduls', ['path' => $menus->path]);
                    return redirect()->action( $module->controller.'@getIndex');
                } elseif ($menus->type == 'Route') {
                    $action = str_replace("Controller", "Controller@", $menus->path);
                    $action = str_replace(['Get', 'Post'], ['get', 'post'], $action);
                    return redirect()->action($action);
                } elseif ($menus->type == 'Controller & Method') {
                    return redirect()->action($menus->path);
                } elseif ($menus->type == 'URL') {
                    return redirect($menus->path);
                }
            }
        }
        return $next($request);
    }
}
