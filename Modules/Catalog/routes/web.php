<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant'])->group(function (): void {
    // Catalog routes are registered in the secured application slice.
});
