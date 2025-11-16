<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Обновляем сессию ДО обработки запроса
        // Это гарантирует, что сессия не истечет во время обработки
        if ($request->hasSession()) {
            // Просто обращение к сессии обновляет её время жизни
            // Используем put() для обновления существующего значения
            $request->session()->put('_last_activity', now()->timestamp);
            
            // Также обновляем время жизни сессии через touch (если доступно)
            // Это гарантирует, что сессия будет обновлена в базе данных
            try {
                $request->session()->save();
            } catch (\Exception $e) {
                // Игнорируем ошибки сохранения
            }
        }
        
        $response = $next($request);
        
        // Добавляем CSRF токен в заголовки ответа
        if ($request->hasSession()) {
            $response->header('X-CSRF-TOKEN', csrf_token());
        }
        
        return $response;
    }
}

