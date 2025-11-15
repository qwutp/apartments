<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Отключаем Vite helper
        $this->app->bind('Illuminate\Foundation\Vite', function () {
            return new class {
                public function __invoke($entrypoints = null, $buildDirectory = null) {
                    return '';
                }
                public function __call($method, $parameters) {
                    return '';
                }
                public function toHtml() {
                    return '';
                }
            };
        });
    }
}