<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\App\Http\Controllers\RegistrationController;

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
});
