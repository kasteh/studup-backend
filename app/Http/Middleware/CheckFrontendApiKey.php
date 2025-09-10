<?php

namespace App\Http\Middleware;

use Closure;

class CheckFrontendApiKey
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $apiKey = $request->header('x-api-key');

        if ($apiKey !== config('app.frontend_api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
