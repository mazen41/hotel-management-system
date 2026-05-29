<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Dashboard\DashboardController;
use App\Http\Controllers\Api\Room\RoomController;
use App\Http\Controllers\Api\RoomType\RoomTypeController;
use App\Http\Controllers\Api\Settings\HotelSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Hotel Management SaaS
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api (configured in bootstrap/app.php).
| Authentication uses Laravel Sanctum token-based auth.
|
*/

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ─── Protected Routes (Sanctum) ───────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/kpis',             [DashboardController::class, 'kpis']);
        Route::get('/recent-activity',  [DashboardController::class, 'recentActivity']);
        Route::get('/occupancy-trend',  [DashboardController::class, 'occupancyTrend']);
    });

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/hotel',  [HotelSettingsController::class, 'show']);
        Route::post('/hotel', [HotelSettingsController::class, 'update']);
    });

    // Room Types
    Route::prefix('room-types')->group(function () {
        Route::get('/',          [RoomTypeController::class, 'index']);
        Route::post('/',         [RoomTypeController::class, 'store']);
        Route::get('/{roomType}',[RoomTypeController::class, 'show']);
        Route::put('/{roomType}',[RoomTypeController::class, 'update']);
        Route::delete('/{roomType}',[RoomTypeController::class, 'destroy']);
    });

    // Rooms
    Route::prefix('rooms')->group(function () {
        Route::get('/',     [RoomController::class, 'index']);
        Route::post('/',    [RoomController::class, 'store']);
        Route::get('/{room}',[RoomController::class, 'show']);
        Route::put('/{room}',[RoomController::class, 'update']);
        Route::delete('/{room}',[RoomController::class, 'destroy']);
    });
});
