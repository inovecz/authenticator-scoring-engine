<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\BlacklistController;
use App\Http\Controllers\API\LoginAttemptController;
use App\Http\Controllers\API\ScoringEngineController;

Route::post('score-login', [ScoringEngineController::class, 'scoreLogin']);
Route::post('confirm-login-attempt', [ScoringEngineController::class, 'confirmLoginAttempt']);

Route::prefix('blacklists')->group(function () {
    Route::get('/', [BlacklistController::class, 'getByType']);
    Route::post('/', [BlacklistController::class, 'updateOrCreate']);
    Route::delete('/{blacklist}', [BlacklistController::class, 'destroy']);
    Route::get('/count', [BlacklistController::class, 'getCount']);
    Route::post('/{type}/datatable', [BlacklistController::class, 'getDatatable'])->whereIn('type', \App\Enums\BlacklistTypeEnum::values());
    Route::get('/{blacklist}/toggle-active', [BlacklistController::class, 'toggleActive'])->where('blacklist', '[0-9]+');
});

Route::prefix('login-attempts')->group(function () {
    Route::post('/datatable', [LoginAttemptController::class, 'getDatatable']);
});

Route::prefix('settings')->group(function () {
    Route::get('/', [SettingController::class, 'getAll']);
    Route::post('/', [SettingController::class, 'storeSetting']);
    Route::get('/{key}', [SettingController::class, 'getByKey'])->where('key', '[0-9a-zA-Z\.\-\_]+');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
