<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
Route::put('/recover-password', [AuthenticationController::class, 'recoverPassword'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/authenticated-user', [AuthenticationController::class, 'getAuthenticatedUser']);
    Route::get('/check-authentication', [AuthenticationController::class, 'checkAuthentication']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    Route::get('/menu', [AuthenticationController::class, 'menu']);

    Route::put('/alterar-senha', [AuthController::class, 'changePassword']);

    Route::get('/check-permission', [PermissionController::class, 'checkPermission']);

    Route::get('/monitoramento-solicitacoes', [SupportRequestController::class, 'monitoramentoSolicitacoes']);
    Route::get('/solicitacoes-usuario', [SupportRequestController::class, 'solicitacoesUsuario']);

    Route::put('/concluir-solicitacao/{id}', [SupportRequestController::class, 'concluirSolicitacao']);
    Route::put('/recusar-solicitacao/{id}', [SupportRequestController::class, 'recusarSolicitacao']);
    Route::put('/iniciar-solicitacao/{id}', [SupportRequestController::class, 'iniciarSolicitacao']);

    Route::apiResources([
        'support-request' => SupportRequestController::class,
        'users'           => UserController::class,
        'roles'           => RoleController::class,
        'permissions'     => PermissionController::class,
    ]);
});


