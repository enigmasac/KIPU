<?php

namespace App\Http\Middleware;

use App\Traits\Modules;
use App\Utilities\Versions;
use Closure;

class RedirectIfHitModuleSubscription
{
    use Modules;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
