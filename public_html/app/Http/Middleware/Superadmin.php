<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\Users;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class Superadmin extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);
        if (Auth::user()->id_cms_privileges != Users::ROLE_SUPERADMIN) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
