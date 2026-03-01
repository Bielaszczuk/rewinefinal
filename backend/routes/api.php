<?php

use App\Http\Controllers\Admin\EventAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\WineAdminController;
use App\Http\Controllers\Admin\WineryAdminController;
use App\Http\Controllers\AiProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CellarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WineComparisonController;
use App\Http\Controllers\WineController;
use App\Http\Controllers\WineryController;
use App\Http\Controllers\WineRouteController;
use Illuminate\Support\Facades\Route;

// ──────────────── Health ────────────────
Route::get('/health', [HealthController::class, 'health']);
Route::get('/version', [HealthController::class, 'version']);

// ──────────────── Auth ────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.auth');
});

// ──────────────── Users ────────────────
Route::prefix('users')->middleware('jwt.auth')->group(function () {
    // Current user profile
    Route::patch('/me', [UserController::class, 'updateMe']);
    Route::post('/me/change-password', [UserController::class, 'changePassword']);
    Route::get('/me/stats', [UserController::class, 'getStats']);

    // Admin user management
    Route::get('/', [UserController::class, 'index'])->middleware('role:ROLE_ADMIN,ROLE_MODERATOR');
    Route::get('/{id}', [UserController::class, 'show'])->middleware('role:ROLE_ADMIN,ROLE_MODERATOR');
    Route::patch('/{id}/role', [UserController::class, 'updateRole'])->middleware('role:ROLE_ADMIN');
    Route::post('/{id}/ban', [UserController::class, 'ban'])->middleware('role:ROLE_ADMIN,ROLE_MODERATOR');
    Route::post('/{id}/unban', [UserController::class, 'unban'])->middleware('role:ROLE_ADMIN,ROLE_MODERATOR');
    Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('role:ROLE_ADMIN');
});

// ──────────────── Cellar ────────────────
Route::prefix('cellar')->middleware('jwt.auth')->group(function () {
    Route::get('/', [CellarController::class, 'index']);
    Route::post('/', [CellarController::class, 'store']);
    Route::get('/stats', [CellarController::class, 'stats']);
    Route::patch('/{entryId}', [CellarController::class, 'update']);
    Route::delete('/{entryId}', [CellarController::class, 'destroy']);
});

// ──────────────── Wines ────────────────
Route::prefix('wines')->group(function () {
    // AI comparison (before {id} to avoid routing conflict)
    Route::post('/compare', [WineComparisonController::class, 'compare'])->middleware('jwt.auth');

    // Public list endpoints
    Route::get('/featured', [WineController::class, 'featured']);
    Route::get('/top-rated', [WineController::class, 'topRated']);
    Route::get('/recent', [WineController::class, 'recent']);
    Route::get('/recommended', [WineController::class, 'recommended']);
    Route::get('/popular', [WineController::class, 'popular']);
    Route::get('/search', [WineController::class, 'index']);

    // Main paginated list
    Route::get('/', [WineController::class, 'index']);

    // Single wine (optional auth for enriched response)
    Route::get('/{id}', [WineController::class, 'show'])->middleware('jwt.optional');
    Route::get('/{id}/similar', [WineController::class, 'similar']);

    // Reviews (nested under wine)
    Route::prefix('/{wineId}/reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->middleware('jwt.optional');
        Route::post('/', [ReviewController::class, 'store'])->middleware('jwt.auth');
        Route::patch('/{id}', [ReviewController::class, 'update'])->middleware('jwt.auth');
        Route::delete('/{id}', [ReviewController::class, 'destroy'])->middleware('jwt.auth');
        Route::post('/{id}/like', [ReviewController::class, 'like'])->middleware('jwt.auth');
        Route::delete('/{id}/like', [ReviewController::class, 'unlike'])->middleware('jwt.auth');
    });

    // AI Profiles (nested under wine)
    Route::prefix('/{wineId}/ai-profile')->middleware('jwt.auth')->group(function () {
        Route::get('/', [AiProfileController::class, 'show']);
        Route::post('/', [AiProfileController::class, 'generate']);
        Route::post('/generate', [AiProfileController::class, 'generate']);
        Route::get('/status', [AiProfileController::class, 'status']);
    });
});

// ──────────────── Events ────────────────
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/nearby', [EventController::class, 'nearby']);
    Route::get('/{id}', [EventController::class, 'show']);
    Route::post('/', [EventController::class, 'store'])->middleware(['jwt.auth', 'role:ROLE_ADMIN,ROLE_PARTNER']);
    Route::put('/{id}', [EventController::class, 'update'])->middleware('jwt.auth');
    Route::delete('/{id}', [EventController::class, 'destroy'])->middleware('jwt.auth');
});

// ──────────────── Wine Routes ────────────────
Route::prefix('wine-routes')->group(function () {
    Route::get('/hierarchy', [WineRouteController::class, 'hierarchy']);
    Route::get('/countries', [WineRouteController::class, 'countries']);
    Route::get('/countries/{country}/regions', [WineRouteController::class, 'regions']);
    Route::get('/countries/{country}/regions/{region}/subregions', [WineRouteController::class, 'subregions']);
    Route::get('/', [WineRouteController::class, 'index']);
    Route::get('/{id}', [WineRouteController::class, 'show']);
});

// ──────────────── Wineries (Public) ────────────────
Route::prefix('wineries')->group(function () {
    Route::get('/', [WineryController::class, 'index']);
    Route::get('/{id}', [WineryController::class, 'show']);
});

// ──────────────── Admin ────────────────
Route::prefix('admin')->middleware(['jwt.auth', 'role:ROLE_ADMIN,ROLE_MODERATOR'])->group(function () {
    // Dashboard stats
    Route::get('/stats', [\App\Http\Controllers\AdminDashboardController::class, 'stats']);

    // Wines CRUD
    Route::get('/wines', [WineAdminController::class, 'index']);
    Route::post('/wines', [WineAdminController::class, 'store']);
    Route::get('/wines/{id}', [WineAdminController::class, 'show']);
    Route::patch('/wines/{id}', [WineAdminController::class, 'update']);
    Route::delete('/wines/{id}', [WineAdminController::class, 'destroy'])->middleware('role:ROLE_ADMIN');
    Route::post('/wines/{id}/toggle-featured', [WineAdminController::class, 'toggleFeatured']);
    Route::post('/wines/{id}/toggle-active', [WineAdminController::class, 'toggleActive']);

    // Wineries CRUD
    Route::get('/wineries', [WineryAdminController::class, 'index']);
    Route::post('/wineries', [WineryAdminController::class, 'store']);
    Route::get('/wineries/{id}', [WineryAdminController::class, 'show']);
    Route::patch('/wineries/{id}', [WineryAdminController::class, 'update']);
    Route::delete('/wineries/{id}', [WineryAdminController::class, 'destroy'])->middleware('role:ROLE_ADMIN');

    // Events CRUD
    Route::get('/events', [EventAdminController::class, 'index']);
    Route::post('/events', [EventAdminController::class, 'store']);
    Route::get('/events/{id}', [EventAdminController::class, 'show']);
    Route::patch('/events/{id}', [EventAdminController::class, 'update']);
    Route::delete('/events/{id}', [EventAdminController::class, 'destroy'])->middleware('role:ROLE_ADMIN');

    // Users Management (Admin only)
    Route::middleware('role:ROLE_ADMIN')->group(function () {
        Route::get('/users', [UserAdminController::class, 'index']);
        Route::get('/users/{id}', [UserAdminController::class, 'show']);
        Route::patch('/users/{id}', [UserAdminController::class, 'update']);
        Route::delete('/users/{id}', [UserAdminController::class, 'destroy']);
        Route::post('/users/{id}/assign-role', [UserAdminController::class, 'assignRole']);
    });

    // Reviews moderation (TODO: create ReviewAdminController)
    // Route::get('/reviews', [ReviewAdminController::class, 'index']);
    // Route::delete('/reviews/{id}', [ReviewAdminController::class, 'destroy']);

    // Wine Routes CRUD
    Route::get('/routes', [\App\Http\Controllers\WineRouteAdminController::class, 'index']);
    Route::post('/routes', [\App\Http\Controllers\WineRouteAdminController::class, 'store']);
    Route::get('/routes/{id}', [\App\Http\Controllers\WineRouteAdminController::class, 'show']);
    Route::patch('/routes/{id}', [\App\Http\Controllers\WineRouteAdminController::class, 'update']);
    Route::delete('/routes/{id}', [\App\Http\Controllers\WineRouteAdminController::class, 'destroy'])->middleware('role:ROLE_ADMIN');
});

// ──────────────── Partner Management ────────────────
Route::prefix('partner')->middleware(['jwt.auth', 'role:ROLE_PARTNER,ROLE_ADMIN'])->group(function () {
    // My Events - Partners can manage their own events
    Route::get('/events', [EventController::class, 'myEvents']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
});
