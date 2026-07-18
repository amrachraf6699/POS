<?php

use Illuminate\Support\Facades\Route;
use Modules\Tracker\App\Http\Controllers\TrackerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('__tracker', [TrackerController::class, 'index'])->name('tracker.dashboard');
