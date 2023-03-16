<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/teste', function (Request $request) {
    return 'Teste';
});

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
Route::put('/recover-password', [AuthenticationController::class, 'recoverPassword'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/authenticated-user', [AuthenticationController::class, 'getAuthenticatedUser']);
    Route::get('/check-authentication', [AuthenticationController::class, 'checkAuthentication']);

    Route::get('/check-permission', [AuthenticationController::class, 'checkPermission']);

    Route::apiResources([
        'user' => UserController::class
    ]);
});
