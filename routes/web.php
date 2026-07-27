<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\RecipientController;
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
});
