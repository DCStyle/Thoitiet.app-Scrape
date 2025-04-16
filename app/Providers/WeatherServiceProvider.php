<?php

namespace App\Providers;

use App\Services\WeatherService;
use App\View\Components\WeatherWidget;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class WeatherServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WeatherService::class, function ($app) {
            return new WeatherService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Blade component
        Blade::component('weather-widget', WeatherWidget::class);
    }
}
