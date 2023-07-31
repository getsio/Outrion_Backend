<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key'); // Der API-Schlüssel wird im Request-Header 'X-API-Key' erwartet

        // Überprüfe, ob der API-Schlüssel gültig ist
        if ($apiKey !== config('app.api_key')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
