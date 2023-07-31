<?php

// app/Http/Middleware/CheckTokenMiddleware.php

namespace App\Http\Middleware;

use Closure;

class CheckTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Unauthenticated. Missing or invalid token.'], 401);
        }

        return $next($request);
    }
}