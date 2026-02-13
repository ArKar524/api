<?php

use App\Http\Controllers\Admin\AdminCarApprovalController;
use App\Http\Controllers\Admin\AdminOwnerController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\CarListController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Owner\CarController;
use App\Http\Controllers\Driver\TripApplicationController as DriverTripApplicationController;
use App\Http\Controllers\Owner\TripController as OwnerTripController;
use App\Http\Controllers\Owner\TripApplicationController as OwnerTripApplicationController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/ping', function (Request $request) {
    return response()->json(['pong' => '1234']);
});

Route::get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => [
            'user' => new UserResource($request->user()),
        ],
        'message' => 'Profile fetched.',
        'errors' => null,
    ]);
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
Route::get('cars/{car}', [CarListController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::delete('notifications/{id}/delete', [NotificationController::class, 'delete']);

    Route::prefix('owner')->middleware('role:owner')->group(function () {
        Route::get('cars', [CarController::class, 'index']);
        Route::get('cars/{car}', [CarListController::class, 'show']);
        Route::post('kyc', [KycController::class, 'submitOwner']);
        Route::post('cars', [CarController::class, 'store']);
        Route::post('trips', [OwnerTripController::class, 'store']);
        Route::patch('trips/{trip}', [OwnerTripController::class, 'update']);
        Route::get('trips', [OwnerTripController::class, 'index']);
        Route::patch('trip-applications/{tripApplication}', [OwnerTripApplicationController::class, 'update']);
        Route::get('trip-applications', [OwnerTripApplicationController::class, 'index']);
    });

    Route::prefix('driver')->middleware('role:driver')->group(function () {
        Route::post('kyc', [KycController::class, 'submitDriver']);
        Route::post('trip-applications', [DriverTripApplicationController::class, 'store']);
        Route::get('trips', [DriverTripApplicationController::class, 'index']);
    });

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('owners', [AdminOwnerController::class, 'index']);
        Route::get('owners/{id}', [AdminOwnerController::class, 'show']);
        Route::post('verifications/{id}/review', [AdminVerificationController::class, 'review']);
        Route::post('cars/{id}/review', [AdminCarApprovalController::class, 'review']);
    });
});
