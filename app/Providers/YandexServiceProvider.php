<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class YandexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $socialite = $this->app->make(Factory::class);
        
        $socialite->extend('yandex', function ($app) use ($socialite) {
            $config = $app['config']['services.yandex'];
            
            return $socialite->buildProvider(
                \App\Socialite\YandexProvider::class, // Мы создадим этот класс
                $config
            );
        });
    }
}