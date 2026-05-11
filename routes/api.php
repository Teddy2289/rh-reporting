<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\PlanningSlotController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — RH Planning
|--------------------------------------------------------------------------
|
| Prefix : /api
| Auth   : JWT-Auth (Bearer token)
|
*/

// ─── Auth (public) ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ─── Protected routes (JWT) ─────────────────────────────────────────────────
// On utilise 'auth:api' car nous avons configuré le driver 'jwt' sur le guard 'api' dans auth.php
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']); // Optionnel : utile pour JWT
    });

    // Departments
    Route::apiResource('departments', DepartmentController::class);

    // Clients
    Route::apiResource('clients', ClientController::class);

    // Missions
    Route::apiResource('missions', MissionController::class);

    // Agents
    Route::apiResource('agents', AgentController::class);

    // Planning Slots
    Route::prefix('planning')->group(function () {
        Route::get('/', [PlanningSlotController::class, 'index']);
        Route::post('/', [PlanningSlotController::class, 'store']);
        Route::get('/{planningSlot}', [PlanningSlotController::class, 'show']);
        Route::put('/{planningSlot}', [PlanningSlotController::class, 'update']);
        Route::delete('/{planningSlot}', [PlanningSlotController::class, 'destroy']);
        Route::post('/generate', [PlanningSlotController::class, 'generate']);
        Route::post('/bulk', [PlanningSlotController::class, 'bulkUpdate']);
    });

    // Leaves (Congés)
    Route::prefix('leaves')->group(function () {
        Route::get('/', [LeaveController::class, 'index']);
        Route::post('/', [LeaveController::class, 'store']);
        Route::get('/balance', [LeaveController::class, 'balance']);
        Route::get('/{leave}', [LeaveController::class, 'show']);
        Route::delete('/{leave}', [LeaveController::class, 'destroy']);
        Route::post('/{leave}/approve', [LeaveController::class, 'approve']);
        Route::post('/{leave}/refuse', [LeaveController::class, 'refuse']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/hours', [ReportController::class, 'agentHours']);
        Route::get('/dashboard', [ReportController::class, 'dashboard']);
        Route::get('/team', [ReportController::class, 'teamReport']);
        Route::get('/export', [ReportController::class, 'export']);
    });

    Route::get('activity-logs', [ActivityLogController::class, 'index']);
    Route::post('activity-logs/bulk', [ActivityLogController::class, 'bulkStore']);
});
