<?php

namespace Modules\Woocommerce\Http\Middleware;

use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ('custom' === $request->get('type', 'auto')) {
            return $next($request);
        }

        if (null === setting('woocommerce.url')) {
            return redirect()->route('woocommerce.auth.show');
        }

        return $next($request);
    }
}
