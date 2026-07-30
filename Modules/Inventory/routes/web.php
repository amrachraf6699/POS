<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\App\Http\Controllers\InventoryBalanceController;

Route::middleware(['auth', 'tenant'])->prefix('tenant/inventory')->name('inventory.')->group(function (): void {
    Route::get('/balances', [InventoryBalanceController::class, 'index'])->name('balances.index');
});
