<?php

namespace Modules\Inventory\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class InventoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Inventory', 'Database/Migrations'));
        $this->loadViewsFrom(module_path('Inventory', 'resources/views'), 'inventory');
        Blade::componentNamespace('Modules\\Inventory\\App\\View\\Components', 'inventory');
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
