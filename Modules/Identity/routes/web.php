<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\App\Http\Controllers\RegistrationController;
use Modules\Identity\App\Http\Controllers\TenantSelectionController;

Route::middleware(['guest', 'central'])->group(function (): void {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'central'])->group(function (): void {
    Route::get('/tenant/select', [TenantSelectionController::class, 'index'])->name('tenant.selection');
    Route::post('/tenant/select/{tenant}', [TenantSelectionController::class, 'store'])->name('tenant.selection.store');
});
