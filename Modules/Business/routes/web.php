<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\App\Http\Controllers\BusinessSettingsController;

Route::middleware(['auth', 'tenant'])
    ->prefix('tenant/settings/business')
    ->name('business.settings.')
    ->group(function (): void {
        Route::get('/', [BusinessSettingsController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/', [BusinessSettingsController::class, 'update'])->name('update');
    });
