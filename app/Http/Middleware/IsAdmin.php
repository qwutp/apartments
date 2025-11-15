<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'User not authenticated'
            ], 401);
        }
        
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'User is not an admin. Role: ' . $request->user()->role
            ], 403);
        }

        return $next($request);
    }
}
