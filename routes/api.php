<?php

use App\Http\Controllers\Admin\AdminCarApprovalController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CarListController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Owner\CarController;
use App\Http\Controllers\Driver\RentalRequestController as DriverRentalRequestController;
use App\Http\Controllers\Owner\RentalRequestController as OwnerRentalRequestController;
use App\Http\Controllers\Owner\RentalController as OwnerRentalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/ping', function (Request $request) {
    return response()->json(['pong' => '1234']);
});

Route::get('/user', function (Request $request) {
    return response()->json($request->user());
})->middleware('auth:sanctum');
Route::post('login', [AuthController::class, 'login']);

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::get('cars', [CarListController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('owner')->middleware('role:owner')->group(function () {
        Route::get('cars', [CarController::class, 'index']);
        Route::post('kyc', [KycController::class, 'submitOwner']);
        Route::post('cars', [CarController::class, 'store']);
        Route::get('rental-requests', [OwnerRentalRequestController::class, 'index']);
        Route::get('rentals', [OwnerRentalController::class, 'index']);
    });

    Route::prefix('driver')->middleware('role:driver')->group(function () {
        Route::post('kyc', [KycController::class, 'submitDriver']);
        Route::get('rental-requests', [DriverRentalRequestController::class, 'index']);
    });

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::post('verifications/{id}/review', [AdminVerificationController::class, 'review']);
        Route::post('cars/{id}/review', [AdminCarApprovalController::class, 'review']);
    });
});
