<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FrontendCors
{
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');

        $allowedOrigins = [
            'http://localhost:3000',
            'https://studup.vercel.app',
        ];

        // Répondre aux pré-vols OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response()
                ->json([], 204)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, x-api-key')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        // Pour les autres requêtes, ajouter les headers CORS
        $response = $next($request);

        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, x-api-key');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
