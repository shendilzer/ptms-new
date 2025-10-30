<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\MotorcycleController;
use App\Http\Controllers\TodaController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Dashboard data endpoints
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('dashboard/assets', [DashboardController::class, 'assets'])->name('dashboard.assets');

    // Add these new endpoints for operator management dashboard
    Route::get('dashboard/operator-stats', [DashboardController::class, 'operatorStats'])->name('dashboard.operator-stats');
    Route::get('dashboard/recent-operators', [DashboardController::class, 'recentOperators'])->name('dashboard.recent-operators');
    Route::get('dashboard/overview', [DashboardController::class, 'overview'])->name('dashboard.overview');
});

/* ============================================================================
   OPERATOR MANAGEMENT ROUTES
   ============================================================================ */
Route::middleware(['auth', 'verified'])->group(function () {
    // Operator Routes
    Route::post('operators/list', [OperatorController::class, 'list'])->name('operators.list');
    Route::get('operators/statistics', [OperatorController::class, 'statistics'])->name('operators.statistics');
    Route::resource('operators', OperatorController::class)->except(['create', 'edit']);

    // Driver Routes
    Route::post('drivers/list', [DriverController::class, 'list'])->name('drivers.list');
    Route::resource('drivers', DriverController::class)->except(['create', 'edit']);

    // Motorcycle Routes
    Route::post('motorcycles/list', [MotorcycleController::class, 'list'])->name('motorcycles.list');
    Route::resource('motorcycles', MotorcycleController::class)->except(['create', 'edit']);

    // TODA Routes
    Route::post('toda/list', [TodaController::class, 'list'])->name('toda.list');
    Route::resource('toda', TodaController::class)->except(['create', 'edit']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
