<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Определяем origin из запроса и разрешаем только известные dev-хосты
        $origin = $request->headers->get('Origin');
        $allowed = [
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8001',
            'http://127.0.0.1:8001',
        ];

        if ($origin && in_array($origin, $allowed, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        if ($request->getMethod() === 'OPTIONS') {
            $response->setStatusCode(200);
        }
        
        return $response;
    }
}