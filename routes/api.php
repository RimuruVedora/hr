<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EssRequestApiController;
use App\Http\Controllers\SyncController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ess/requests', [EssRequestApiController::class, 'index']);
    Route::post('/sync/receive', [SyncController::class, 'receiveData']);
});

// Employee Sync Route (Custom Token Auth)
Route::post('/employee/sync', [SyncController::class, 'syncEmployee']);

// ESS Request API (Custom Token Auth)
Route::get('/ess/request', [EssRequestApiController::class, 'getExternalRequests']);
Route::post('/ess/request', [EssRequestApiController::class, 'storeExternalRequest']);
