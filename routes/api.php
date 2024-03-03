<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Core\ProviderController;
use App\Http\Controllers\API\V1\Auth\AuthApiController;
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
Route::apiResource('providers', ProviderController::class);

Route::prefix('v1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('check-email', [AuthApiController::class, 'checkEmailAvailability']);
        Route::get('check-username', [AuthApiController::class, 'checkUsernameAvailability']);
        Route::post('signup', [AuthApiController::class, 'signup']);
        Route::post('signin', [AuthApiController::class, 'signin']);
        Route::middleware('auth:api')->group(function () {
            Route::post('update-profile', [AuthApiController::class, 'updateProfile']);
        });
    });
});
