<?php

// app/Http/Middleware/CheckTokenMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        // Hole das Token-Modell anhand des Token-IDs
        $token = PersonalAccessToken::findToken($request->bearerToken());

        // Überprüfe, ob das Token-Modell gefunden wurde und gültig ist
        if (!$token || $token->revoked) {
            return response()->json(['error' => 'Unauthenticated. Invalid token.'], 401);
        }

        return $next($request);
    }
}