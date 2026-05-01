<?php

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\StatsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);
Route::get('/stats', [StatsController::class, 'index']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent']);
})->middleware(['auth:sanctum'])->name('verification.send');

Route::middleware(['auth:sanctum', 'verified.api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::delete('/bookmarks/{job_listing_id}', [BookmarkController::class, 'destroy']);
    Route::get('/bookmarks', [BookmarkController::class, 'index']);

    // JobPosting routes
    Route::get('/job-listings', [JobPostingController::class, 'index']);
    Route::post('/job-listings', [JobPostingController::class, 'store']);
    Route::get('/job-listings/{jobPosting}', [JobPostingController::class, 'show']);
    Route::put('/job-listings/{jobPosting}', [JobPostingController::class, 'update']);
    Route::delete('/job-listings/{jobPosting}', [JobPostingController::class, 'destroy']);
    Route::post('/job-listings/{id}/restore', [JobPostingController::class, 'restore']);

    // JobApplication routes
    Route::get('/applications', [JobApplicationController::class, 'index']);
    Route::post('/apply', [JobApplicationController::class, 'store'])->middleware('throttle:apply');
    Route::get('/applications/{jobApplication}', [JobApplicationController::class, 'show']);
    Route::put('/applications/{jobApplication}', [JobApplicationController::class, 'update']);
    Route::patch('/applications/{id}/status', [JobApplicationController::class, 'updateStatus']);
    Route::delete('/applications/{jobApplication}', [JobApplicationController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    // JobPosting routes
    Route::post('/logout', [AuthController::class, 'logout']);
});
