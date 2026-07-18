<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\App\Http\Controllers\InvitationAcceptanceController;
use Modules\Identity\App\Http\Controllers\InvitationController;
use Modules\Identity\App\Http\Controllers\LoginController;
use Modules\Identity\App\Http\Controllers\RegistrationController;
use Modules\Identity\App\Http\Controllers\TenantSelectionController;

Route::middleware(['guest', 'central'])->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
});

Route::middleware(['central', 'signed'])->group(function (): void {
    Route::get('/invitations/{invitation}/accept/{token}', [InvitationAcceptanceController::class, 'show'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/accept/{token}', [InvitationAcceptanceController::class, 'store'])->name('invitations.accept.store');
});

Route::middleware(['auth', 'central'])->post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'central'])->group(function (): void {
    Route::get('/tenant/select', [TenantSelectionController::class, 'index'])->name('tenant.selection');
    Route::post('/tenant/select/{tenant}', [TenantSelectionController::class, 'store'])->name('tenant.selection.store');
});

Route::middleware(['auth', 'tenant'])->prefix('tenant/invitations')->name('tenant.invitations.')->group(function (): void {
    Route::get('/', [InvitationController::class, 'index'])->name('index');
    Route::post('/', [InvitationController::class, 'store'])->name('store');
    Route::post('/{invitation}/resend', [InvitationController::class, 'resend'])->name('resend');
    Route::post('/{invitation}/revoke', [InvitationController::class, 'revoke'])->name('revoke');
});
