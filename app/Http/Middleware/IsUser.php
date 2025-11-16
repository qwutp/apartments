<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsUser
{
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем авторизацию
        if (!$request->user()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'User not authenticated'
            ], 401);
        }
        
        // Проверяем роль пользователя
        if ($request->user()->isUser() || $request->user()->isAdmin()) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Forbidden',
            'message' => 'Access denied. User role: ' . $request->user()->role
        ], 403);
    }
}
