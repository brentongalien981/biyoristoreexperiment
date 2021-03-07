<?php

namespace App\Http\Middleware;

use Closure;

class AllowFrontendRefererOnly
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
        $theHeaders = getallheaders();
        $refererUrl = $theHeaders['Referer'] ?? null;

        if (
            isset($refererUrl)
            && $refererUrl === env('APP_FRONTEND_REFERER_URL')
        ) {
            return $next($request);
        }

        return response("BmdException: Bad Frontend-Referer URL.", 401);
    }
}
