<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\App\Http\Controllers\BranchAssignmentController;
use Modules\Business\App\Http\Controllers\BranchController;
use Modules\Business\App\Http\Controllers\BusinessDashboardController;
use Modules\Business\App\Http\Controllers\BusinessSettingsController;

Route::middleware(['auth', 'tenant'])->group(function (): void {
    Route::get('/tenant/dashboard', BusinessDashboardController::class)->name('business.dashboard');
    Route::get('/home', fn () => redirect()->route('business.dashboard'))->name('home');
});

Route::middleware(['auth', 'tenant'])
    ->prefix('tenant/branches')
    ->name('business.branches.')
    ->group(function (): void {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{branch}', [BranchController::class, 'update'])->name('update');
        Route::post('/{branch}/deactivate', [BranchController::class, 'deactivate'])->name('deactivate');
        Route::post('/{branch}/assignments', [BranchAssignmentController::class, 'store'])->name('assignments.store');
        Route::patch('/{branch}/assignments/{user}', [BranchAssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/{branch}/assignments/{user}', [BranchAssignmentController::class, 'destroy'])->name('assignments.destroy');
    });

Route::middleware(['auth', 'tenant'])
    ->prefix('tenant/settings/business')
    ->name('business.settings.')
    ->group(function (): void {
        Route::get('/', [BusinessSettingsController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/', [BusinessSettingsController::class, 'update'])->name('update');
    });
