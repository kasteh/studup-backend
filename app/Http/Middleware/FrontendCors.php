<?php

namespace App\Http\Middleware;

use Closure;

class FrontendCors
{
    public function handle($request, Closure $next)
    {
        $origin = $request->headers->get('Origin');

        $allowedOrigins = [
            'http://localhost:3000',
            'https://studup.vercel.app',
        ];

        $originAllowed = in_array($origin, $allowedOrigins);

        if ($originAllowed) {
            if ($request->isMethod('OPTIONS')) {
                return response()
                    ->json([], 204)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            $response = $next($request);

            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');

            return $response;
        }

        return $next($request);
    }
}
