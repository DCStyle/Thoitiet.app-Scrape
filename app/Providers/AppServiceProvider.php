<?php

namespace App\Providers;

use App\Services\AirQualityService;
use App\Services\ContentMirrorService;
use App\View\Composers\FooterComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ContentMirrorService::class, function ($app) {
            return new ContentMirrorService();
        });

        $this->app->singleton(AirQualityService::class, function ($app) {
            return new AirQualityService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.footer', FooterComposer::class);
    }
}
