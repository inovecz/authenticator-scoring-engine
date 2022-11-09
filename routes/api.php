<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BlacklistController;
use App\Http\Controllers\API\ScoringEngineController;

Route::post('score-login', [ScoringEngineController::class, 'scoreLogin']);

Route::prefix('blacklists')->group(function () {
    Route::get('/', [BlacklistController::class, 'getByType']);
    Route::post('/add', [BlacklistController::class, 'updateOrCreate']);
    Route::delete('/', [BlacklistController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
