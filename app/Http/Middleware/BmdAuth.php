<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\BmdHelpers\BmdAuthProvider;
use Illuminate\Support\Facades\Cache;

class BmdAuth
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
        // Instantiate the BmdAuthProvider Singleton.
        BmdAuthProvider::setInstance($request->bmdToken, $request->authProviderId);

        if (BmdAuthProvider::check()) {
            return $next($request);
        }

        return response("BmdAuth: You're unauthenticated dude.", 401);
    }
}
