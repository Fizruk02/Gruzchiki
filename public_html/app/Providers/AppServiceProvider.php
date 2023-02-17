<?php

namespace App\Providers;

use App\Constructor\CRUI\CRUI;
use App\Constructor\helpers\BTBooster;
use App\Http\Controllers\BTHook;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use App\Constructor\CRUI\Variants;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $file =  __DIR__ . '/helpers.php';
        if (file_exists($file)) {
            require_once($file);
        }

        $this->app->bind('crui', CRUI::class);
        $this->app->bind('variants', function () {
            return new Variants;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user &&
                Hash::check($request->password, $user->password)) {

                if ($user->id_cms_privileges == Users::ROLE_DISPETCHER) {
                    if ($user->timezone) {
                        config(['timezone' => $user->timezone]);
                        date_default_timezone_set($user->timezone);
                    }
                }
                $priv = DB::table("cms_privileges")->where("id", $user->id_cms_privileges)->first();

                $roles = DB::table('cms_privileges_roles')->where('id_cms_privileges', $user->id_cms_privileges)->join('cms_moduls', 'cms_moduls.id', '=', 'id_cms_moduls')->select('cms_moduls.name', 'cms_moduls.path', 'is_visible', 'is_create', 'is_read', 'is_edit', 'is_delete')->get();

                $photo = ($user->photo) ? asset($user->photo) : asset('vendor/crudbooster/avatar.jpg');
                Session::put('admin_id', $user->id);
                Session::put('admin_is_superadmin', $priv->is_superadmin);
                Session::put('admin_name', $user->name);
                Session::put('admin_photo', $photo);
                Session::put('admin_privileges_roles', $roles);
                Session::put("admin_privileges", $user->id_cms_privileges);
                Session::put('admin_privileges_name', $priv->name);
                Session::put('admin_lock', 0);
                Session::put('theme_color', $priv->theme_color);
                Session::put("appname", get_setting('appname'));

                BTBooster::insertLog(cbLang("log_login", ['email' => $user->email, 'ip' => \Illuminate\Support\Facades\Request::server('REMOTE_ADDR')]));

                $cb_hook_session = new BTHook();
                $cb_hook_session->afterLogin();

                return $user;
            }
        });
    }
}
