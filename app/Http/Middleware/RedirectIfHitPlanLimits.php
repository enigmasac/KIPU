<?php

namespace App\Http\Middleware;

use App\Traits\Plans;
use Closure;

class RedirectIfHitPlanLimits
{
    use Plans;

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
