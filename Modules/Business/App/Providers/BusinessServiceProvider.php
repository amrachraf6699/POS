<?php

namespace Modules\Business\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Business\App\Domain\Settings\BusinessSettingsService;

class BusinessServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Business';

    protected string $moduleNameLower = 'business';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->singleton(BusinessSettingsService::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }

    protected function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
        Blade::componentNamespace('Modules\\Business\\App\\View\\Components', $this->moduleNameLower);
    }
}
