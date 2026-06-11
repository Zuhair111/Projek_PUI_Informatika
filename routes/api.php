<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GeofencingController;
use App\Http\Controllers\Api\MockDetectionLogController;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Middleware\EnsureApiTokenIsValid;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware([EnsureApiTokenIsValid::class])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/geofencing/aktif', [GeofencingController::class, 'active']);
    Route::get('/geofencing', [GeofencingController::class, 'index']);
    Route::post('/presensi', [PresensiController::class, 'store']);
    Route::get('/presensi/riwayat', [PresensiController::class, 'riwayat']);
    Route::post('/log-deteksi', [MockDetectionLogController::class, 'store']);
    Route::get('/health', static fn () => response()->json(['success' => true, 'message' => 'API aktif']));

    // Attendance API, geofence API, and anti-mock validation API will be added in next stages.
});
