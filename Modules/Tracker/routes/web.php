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
Route::get('__tracker/problems', [TrackerController::class, 'problems'])->name('tracker.problems');
Route::get('__tracker/problems/{issue}', [TrackerController::class, 'problem'])->name('tracker.problems.show');
Route::post('__tracker/problems/{issue}/resolve', [TrackerController::class, 'resolve'])->name('tracker.problems.resolve');
Route::get('__tracker/phases/{phase}', [TrackerController::class, 'phase'])->name('tracker.phases.show');
Route::get('__tracker/phases/{phase}/tasks/{task}', [TrackerController::class, 'task'])->name('tracker.tasks.show');
