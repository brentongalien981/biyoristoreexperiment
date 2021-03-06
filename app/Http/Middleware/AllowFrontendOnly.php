<?php

namespace App\Http\Middleware;

use Closure;

class AllowFrontendOnly
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
        $frontendUrl = $theHeaders['Origin'] ?? null;

        if (
            isset($frontendUrl)
            && $frontendUrl === env('APP_FRONTEND_URL')
        ) {
            return $next($request);
        }

        return response("BmdException: Bad Frontend URL.", 401);
    }
}
