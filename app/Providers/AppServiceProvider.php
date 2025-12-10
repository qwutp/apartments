<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

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

    public function boot(): void
    {
        // Гарантируем наличие папки для файловых сессий
        if (config('session.driver') === 'file') {
            File::ensureDirectoryExists(storage_path('framework/sessions'));
        }
    }
}