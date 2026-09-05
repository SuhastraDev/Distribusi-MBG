<?php

use App\Http\Controllers\Api\FrontendDataController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionReportController;
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

    // Registered before index/show: GET /distribution-runs/create is a literal
    // path that must be matched before the show route's {distribution_run}
    // wildcard, or Laravel treats "create" as a route-model-binding lookup and
    // 404s. Kepala SPPG is a monitoring-only role: it can view runs but not
    // create them.
    Route::resource('distribution-runs', DistributionRunController::class)
        ->only(['create', 'store'])
        ->middleware('role:admin,petugas');

    Route::resource('distribution-runs', DistributionRunController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,petugas,kepala_sppg');

    // Field execution (Start/Complete/Cancel, delivery status, GPS position) is
    // exclusive to the officer physically assigned to the run - not admin, so
    // "who generated the route" and "who actually departed" can't be confused.
    Route::post('/distribution-runs/{distribution_run}/start', [DistributionRunController::class, 'start'])
        ->middleware('role:petugas')
        ->name('distribution-runs.start');

    Route::post('/distribution-runs/{distribution_run}/complete', [DistributionRunController::class, 'complete'])
        ->middleware('role:petugas')
        ->name('distribution-runs.complete');

    Route::post('/distribution-runs/{distribution_run}/cancel', [DistributionRunController::class, 'cancel'])
        ->middleware('role:petugas')
        ->name('distribution-runs.cancel');

    Route::put('/distribution-runs/{distribution_run}/destinations/{destination}', [DistributionRunController::class, 'updateDestination'])
        ->middleware('role:petugas')
        ->name('distribution-runs.destinations.update');

    Route::post('/distribution-runs/{distribution_run}/positions', [OfficerPositionController::class, 'store'])
        ->middleware('role:petugas')
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
        ->middleware('role:petugas')
        ->name('distribution-runs.route-plan.generate');

    Route::get('/reports/distributions', [DistributionReportController::class, 'index'])
        ->middleware('role:admin,kepala_sppg')
        ->name('reports.distributions.index');

    Route::get('/reports/distributions/export', [DistributionReportController::class, 'export'])
        ->middleware('role:admin,kepala_sppg')
        ->name('reports.distributions.export');

    Route::get('/reports/distributions/export-excel', [DistributionReportController::class, 'exportExcel'])
        ->middleware('role:admin,kepala_sppg')
        ->name('reports.distributions.export-excel');

    Route::get('/reports/distributions/{distribution_run}', [DistributionReportController::class, 'show'])
        ->middleware('role:admin,kepala_sppg')
        ->name('reports.distributions.show');

    Route::prefix('api/frontend')
        ->middleware('role:admin,petugas,kepala_sppg')
        ->name('api.frontend.')
        ->group(function (): void {
            Route::get('/dashboard-summary', [FrontendDataController::class, 'dashboardSummary'])
                ->name('dashboard-summary');

            Route::get('/distribution-runs', [FrontendDataController::class, 'distributionRuns'])
                ->name('distribution-runs.index');

            Route::get('/distribution-runs/{distribution_run}', [FrontendDataController::class, 'distributionRunDetail'])
                ->name('distribution-runs.show');

            Route::get('/route-plans/{route_plan}/map', [FrontendDataController::class, 'routeMap'])
                ->name('route-plans.map');

            Route::get('/reports/distributions/summary', [FrontendDataController::class, 'reportSummary'])
                ->middleware('role:admin,kepala_sppg')
                ->name('reports.distributions.summary');
        });
});
