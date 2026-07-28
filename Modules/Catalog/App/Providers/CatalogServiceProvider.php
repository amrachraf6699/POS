<?php

namespace Modules\Catalog\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class CatalogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Catalog', 'Database/Migrations'));
        $this->loadViewsFrom(module_path('Catalog', 'resources/views'), 'catalog');
        Blade::componentNamespace('Modules\\Catalog\\App\\View\\Components', 'catalog');
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
