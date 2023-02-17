<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sections extends Model
{
    use HasFactory;

    public function render($page = null) : string | null
    {
        $role_id = Auth::guest() ? 0 : Auth::user()->role_id;
        if (!$this->isAccess($role_id)) return null;
        $exp = explode('/', $this->section_tpl);
        if (count($exp) == 1) {
            //dump($exp[0]);
            $classNameModule = ucfirst($exp[0]);
            $moduleClass = 'App\Modules\\'.$classNameModule.'\\'.$classNameModule.'Module';
            if (!class_exists($moduleClass)) return null;
            $module = new $moduleClass(['section' => $this, 'page' => $page]);
            return $module->invoke();
        } else if ($exp[0] == 'modules') {
            $classNameModule = ucfirst($exp[1]);
            $moduleClass = 'App\Modules\\'.$classNameModule.'\\'.$classNameModule.'Module';
            if (!class_exists($moduleClass)) return null;
            $module = new $moduleClass(['section' => $this, 'page' => $page]);
            if (isset($exp[2]) && $exp[2]) {
                $controllerClass = 'App\Modules\\' . $exp[1] . '\controllers\\' . ucfirst($exp[2]).'Controller';
                $action = $exp[3].'Action';
            } else {
                $controllerClass = $module->defaultController;
                $action = $module->defaultRoute.'Action';
            }
            $controller = new $controllerClass(['section' => $this, 'page' => $page]);
            return $controller->$action();
        }

        return null;
    }

    /**
     * Получить роли у кого есть доступ
     */
    public function isAccess($role_id)
    {
        if (empty($this->roles) || !$this->roles) return true;

        $roles = json_decode($this->roles, true);
        return in_array($role_id, $roles);
    }
}
