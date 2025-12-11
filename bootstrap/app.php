<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'user' => \App\Http\Middleware\IsUser::class,
            'cors' => \App\Http\Middleware\Cors::class, // Кастомный CORS
        ]);
        
        // Для API - добавляем кастомный CORS и сессии ПЕРВЫМИ
        $middleware->api(prepend: [
            \App\Http\Middleware\Cors::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);
        
        // Для web маршрутов
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
        
        // CSRF исключения
        $middleware->validateCsrfTokens(except: [
            'auth/yandex/callback',
            'yandex/callback',
            'api/login',
            'api/logout',
            'api/register',
            'api/sanctum/csrf-cookie',
            'api/*', // Можно отключить для всех API если нужно
        ]);
        
        // Добавляем CORS в API группу
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\Cors::class,
        ]);
        
        // Добавляем CORS в web группу (для маршрутов которые может вызывать Vue)
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\Cors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();