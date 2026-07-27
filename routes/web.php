<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionRunController;
use App\Http\Controllers\DistributionScheduleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\OfficerPositionController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\RoutePlanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'redirect'])
        ->name('dashboard');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/petugas/dashboard', [DashboardController::class, 'officer'])
        ->middleware('role:petugas')
        ->name('officer.dashboard');

    Route::get('/kepala-sppg/dashboard', [DashboardController::class, 'head'])
        ->middleware('role:kepala_sppg')
        ->name('head.dashboard');

    Route::get('/change-password', [PasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('/change-password', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::resource('officers', OfficerController::class)
        ->middleware('role:admin');

    Route::resource('locations', LocationController::class)
        ->middleware('role:admin');

    Route::resource('recipients', RecipientController::class)
        ->middleware('role:admin');

    Route::resource('distribution-schedules', DistributionScheduleController::class)
        ->middleware('role:admin');

    Route::post('/distribution-schedules/{distribution_schedule}/destinations', [DistributionScheduleController::class, 'storeDestination'])
        ->middleware('role:admin')
        ->name('distribution-schedules.destinations.store');

    Route::delete('/distribution-schedules/{distribution_schedule}/destinations/{destination}', [DistributionScheduleController::class, 'destroyDestination'])
        ->middleware('role:admin')
        ->name('distribution-schedules.destinations.destroy');

    Route::resource('distribution-runs', DistributionRunController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware('role:admin,petugas,kepala_sppg');

    Route::post('/distribution-runs/{distribution_run}/start', [DistributionRunController::class, 'start'])
        ->middleware('role:admin,petugas')
        ->name('distribution-runs.start');

    Route::post('/distribution-runs/{distribution_run}/complete', [DistributionRunController::class, 'complete'])
        ->middleware('role:admin,petugas')
        ->name('distribution-runs.complete');

    Route::post('/distribution-runs/{distribution_run}/cancel', [DistributionRunController::class, 'cancel'])
        ->middleware('role:admin,petugas')
        ->name('distribution-runs.cancel');

    Route::put('/distribution-runs/{distribution_run}/destinations/{destination}', [DistributionRunController::class, 'updateDestination'])
        ->middleware('role:admin,petugas')
        ->name('distribution-runs.destinations.update');

    Route::post('/distribution-runs/{distribution_run}/positions', [OfficerPositionController::class, 'store'])
        ->middleware('role:admin,petugas')
        ->name('distribution-runs.positions.store');

    Route::get('/distribution-runs/{distribution_run}/positions/latest', [OfficerPositionController::class, 'latest'])
        ->middleware('role:admin,petugas,kepala_sppg')
        ->name('distribution-runs.positions.latest');

    Route::resource('route-plans', RoutePlanController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,petugas,kepala_sppg');

    Route::get('/route-plans/{route_plan}/map-data', [RoutePlanController::class, 'mapData'])
        ->middleware('role:admin,petugas,kepala_sppg')
        ->name('route-plans.map-data');

    Route::post('/distribution-runs/{distribution_run}/route-plan', [RoutePlanController::class, 'generate'])
        ->middleware('role:admin,petugas')
        ->name('distribution-runs.route-plan.generate');
});
