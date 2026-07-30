<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\App\Http\Controllers\InventoryBalanceController;
use Modules\Inventory\App\Http\Controllers\InventoryTransferController;
use Modules\Inventory\App\Http\Controllers\StockAdjustmentController;

Route::middleware(['auth', 'tenant'])->prefix('tenant/inventory')->name('inventory.')->group(function (): void {
    Route::get('/balances', [InventoryBalanceController::class, 'index'])->name('balances.index');
    Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
    Route::get('/opening-stock/create', [StockAdjustmentController::class, 'createOpening'])->name('adjustments.opening.create');
    Route::post('/opening-stock', [StockAdjustmentController::class, 'store'])->name('adjustments.opening.store');
    Route::get('/stock-adjustments/create', [StockAdjustmentController::class, 'createAdjustment'])->name('adjustments.create');
    Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
    Route::get('/stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('adjustments.show');
    Route::get('/transfers', [InventoryTransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [InventoryTransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [InventoryTransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{inventoryTransfer}', [InventoryTransferController::class, 'show'])->name('transfers.show');
    Route::post('/transfers/{inventoryTransfer}/approve', [InventoryTransferController::class, 'approve'])->name('transfers.approve');
    Route::post('/transfers/{inventoryTransfer}/cancel', [InventoryTransferController::class, 'cancel'])->name('transfers.cancel');
});
