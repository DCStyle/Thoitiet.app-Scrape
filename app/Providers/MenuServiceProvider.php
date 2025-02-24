<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class MenuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            $view->with('menuItems', Cache::rememberForever('menu_items', function () {
                return Menu::with('children')
                    ->whereNull('parent_id')
                    ->orderBy('order')
                    ->get();
            }));
        });
    }
}
