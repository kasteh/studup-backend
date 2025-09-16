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

        if ($origin && in_array($origin, $allowedOrigins)) {
            $headers = [
                'Access-Control-Allow-Origin'      => $origin,
                'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, X-API-KEY, Accept',
                'Access-Control-Allow-Credentials' => 'true',
            ];

            // Si c’est une préflight request (OPTIONS), on répond direct
            if ($request->getMethod() === 'OPTIONS') {
                return response()->json('OK', 200, $headers);
            }

            // Sinon on ajoute les headers à la réponse normale
            $response = $next($request);
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }

            return $response;
        }

        // Si l’origine n’est pas autorisée, on continue sans ajouter CORS
        return $next($request);
    }
}
