<?php

use App\Http\Controllers\Admin\AdminCarApprovalController;
use App\Http\Controllers\Admin\AdminCarPageController;
use App\Http\Controllers\Admin\AdminOwnerPageController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    // return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('cars', [AdminCarPageController::class, 'index'])->name('cars.index');
    Route::get('cars/{id}', [AdminCarPageController::class, 'show'])->name('cars.show');
    Route::post('cars/{id}/review', [AdminCarApprovalController::class, 'review'])->name('cars.review');
    Route::get('owners', [AdminOwnerPageController::class, 'index'])->name('owners.index');
    Route::get('owners/{id}', [AdminOwnerPageController::class, 'show'])->name('owners.show');
    Route::post('verifications/{id}/review', [AdminVerificationController::class, 'review'])
        ->name('verifications.review');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
