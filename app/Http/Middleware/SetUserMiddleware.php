<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ($request->user()->isUser() || $request->user()->isAdmin())) {
            return $next($request);
        }

        return abort(403, 'Unauthorized access.');
    }
}
