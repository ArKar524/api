<?php

use App\Http\Controllers\Admin\AdminCarApprovalController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Owner\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth:sanctum')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('owner')->middleware('role:owner')->group(function () {
        Route::post('kyc', [KycController::class, 'submitOwner']);
        Route::post('cars', [CarController::class, 'store']);
    });

    Route::prefix('driver')->middleware('role:driver')->group(function () {
        Route::post('kyc', [KycController::class, 'submitDriver']);
    });

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::post('verifications/{id}/review', [AdminVerificationController::class, 'review']);
        Route::post('cars/{id}/review', [AdminCarApprovalController::class, 'review']);
    });
});
