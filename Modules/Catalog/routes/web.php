<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\App\Http\Controllers\CategoryController;
use Modules\Catalog\App\Http\Controllers\TaxRateController;

Route::middleware(['auth', 'tenant'])->prefix('tenant/catalog')->name('catalog.')->group(function (): void {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::match(['put', 'patch'], '/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/tax-rates/create', [TaxRateController::class, 'create'])->name('tax-rates.create');
    Route::post('/tax-rates', [TaxRateController::class, 'store'])->name('tax-rates.store');
    Route::get('/tax-rates/{taxRate}/edit', [TaxRateController::class, 'edit'])->name('tax-rates.edit');
    Route::match(['put', 'patch'], '/tax-rates/{taxRate}', [TaxRateController::class, 'update'])->name('tax-rates.update');
    Route::post('/tax-rates/{taxRate}/versions', [TaxRateController::class, 'storeVersion'])->name('tax-rates.versions.store');
    Route::post('/tax-rates/{taxRate}/deactivate', [TaxRateController::class, 'deactivate'])->name('tax-rates.deactivate');
});
