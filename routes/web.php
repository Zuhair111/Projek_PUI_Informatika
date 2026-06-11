<?php

use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\GeofencingController;
use App\Http\Controllers\Dashboard\KaryawanController;
use App\Http\Controllers\Dashboard\LogDeteksiController;
use App\Http\Controllers\Dashboard\RekapPresensiController;
use App\Http\Controllers\Dashboard\UjiLatensiController;
use App\Http\Middleware\EnsureAdministrator;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('dashboard.login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('dashboard.login.submit');
});

Route::middleware([EnsureAdministrator::class])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::prefix('/dashboard')->name('dashboard.')->group(function (): void {
        Route::resource('/karyawan', KaryawanController::class)->except(['show'])->names('karyawan');
        Route::resource('/geofencing', GeofencingController::class)->except(['show'])->names('geofencing');
        Route::patch('/geofencing/{geofencing}/toggle', [GeofencingController::class, 'toggleAktif'])
            ->name('geofencing.toggle-aktif');

        Route::get('/rekap-presensi', [RekapPresensiController::class, 'index'])->name('rekap.index');
        Route::get('/rekap-presensi/export/pdf', [RekapPresensiController::class, 'exportPdf'])->name('rekap.export.pdf');
        Route::get('/rekap-presensi/export/excel', [RekapPresensiController::class, 'exportExcel'])->name('rekap.export.excel');

        Route::get('/log-deteksi', [LogDeteksiController::class, 'index'])->name('log-deteksi.index');

        Route::get('/uji-latensi', [UjiLatensiController::class, 'index'])->name('uji-latensi.index');
        Route::get('/uji-latensi/api/geofencing', [UjiLatensiController::class, 'proxyGeofencing'])->name('uji-latensi.geofencing');
        Route::post('/uji-latensi/api/presensi', [UjiLatensiController::class, 'proxyPresensi'])->name('uji-latensi.presensi');
        Route::post('/uji-latensi/api/log-deteksi', [UjiLatensiController::class, 'proxyLogDeteksi'])->name('uji-latensi.log-deteksi');
        Route::post('/uji-latensi/bersihkan', [UjiLatensiController::class, 'bersihkan'])->name('uji-latensi.bersihkan');
        Route::get('/uji-latensi/real/latensi', [UjiLatensiController::class, 'realLatensi'])->name('uji-latensi.real.latensi');
        Route::get('/uji-latensi/real/geofence', [UjiLatensiController::class, 'realGeofence'])->name('uji-latensi.real.geofence');
        Route::get('/uji-latensi/real/mock', [UjiLatensiController::class, 'realMock'])->name('uji-latensi.real.mock');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('dashboard.logout');
});
