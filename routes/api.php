<?php

use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::get('/apartments', [ApartmentController::class, 'apiIndex']);
Route::get('/apartments/{id}', [ApartmentController::class, 'apiShow']);
Route::get('/apartments/search', [ApartmentController::class, 'apiSearch']);

// Protected API routes
Route::middleware('auth', 'user')->group(function () {
    Route::get('/bookings/active', [BookingController::class, 'apiActive']);
    Route::get('/bookings/past', [BookingController::class, 'apiPast']);
    Route::post('/bookings', [BookingController::class, 'apiStore']);
    Route::post('/apartments/{apartment}/favorite', [FavoriteController::class, 'apiToggle']);
    Route::get('/favorites', [FavoriteController::class, 'apiList']);
    Route::get('/reviews/bookings', [ReviewController::class, 'apiGetBookingsForReview']);
    Route::post('/reviews', [ReviewController::class, 'apiStore']);
});

// Admin API routes
Route::middleware('auth', 'admin')->group(function () {
    Route::get('/users/{user}', [UserAdminController::class, 'show']);
    Route::get('/users/search', [UserAdminController::class, 'search']);
    Route::get('/bookings/calendar', [BookingAdminController::class, 'getCalendarData']);
    Route::post('/bookings/{booking}/decline', [BookingAdminController::class, 'decline']);
    Route::get('/apartments', [ApartmentController::class, 'apiIndex']);
    Route::post('/apartments', [ApartmentController::class, 'apiStore']);
    Route::put('/apartments/{id}', [ApartmentController::class, 'apiUpdate']);
    Route::delete('/apartments/{id}', [ApartmentController::class, 'apiDestroy']);
});
