<?php

namespace App\Constructor\middlewares;

use Closure;
use App\Constructor\helpers\BTBooster;

class BTAuthAPI
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


        BTBooster::authAPI();

        return $next($request);
    }
}
