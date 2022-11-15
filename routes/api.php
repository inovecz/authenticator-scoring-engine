<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BlacklistController;
use App\Http\Controllers\API\ScoringEngineController;

Route::post('score-login', [ScoringEngineController::class, 'scoreLogin']);

Route::prefix('blacklists')->group(function () {
    Route::get('/', [BlacklistController::class, 'getByType']);
    Route::delete('/', [BlacklistController::class, 'destroy']);
    Route::get('/count', [BlacklistController::class, 'getCount']);
    Route::post('/add', [BlacklistController::class, 'updateOrCreate']);
    Route::post('/{type}/datatable', [BlacklistController::class, 'getDatatable'])->whereIn('type', \App\Enums\BlacklistTypeEnum::values());
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
