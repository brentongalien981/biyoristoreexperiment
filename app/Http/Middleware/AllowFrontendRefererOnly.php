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
        $refererUrl = substr($refererUrl, 0, strlen(env('APP_FRONTEND_REFERER_URL')));

        if (
            isset($refererUrl)
            && $refererUrl === env('APP_FRONTEND_REFERER_URL')
        ) {
            return $next($request);
        }

        $responseMsg = 'BmdException: Bad Frontend-Referer';
        // $responseMsg = 'BmdException: Bad Frontend-Referer URL ==> ' . $refererUrl . ' .';
        return response($responseMsg, 501);
    }
}
