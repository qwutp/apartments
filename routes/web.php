<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ApartmentAdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use Illuminate\Support\Facades\Route;


Route::get('/api/check-auth', function() {
    return [
        'user' => auth()->check() ? [
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'role' => auth()->user()->role
        ] : null
    ];
});

// Admin apartment routes - protected with admin middleware
Route::prefix('api')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/apartments', [ApartmentAdminController::class, 'store']);
    Route::put('/apartments/{id}', [ApartmentAdminController::class, 'update']);
    Route::delete('/apartments/{id}', [ApartmentAdminController::class, 'destroy']);
});

// API маршруты
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Public API routes
Route::get('/api/apartments', [ApartmentController::class, 'apiIndex']);
Route::get('/api/apartments/{id}', [ApartmentController::class, 'apiShow']);
Route::get('/api/apartments/search', [ApartmentController::class, 'apiSearch']);

// Protected API routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'changePassword'])->name('password.change');

    Route::get('/bookings/active', [BookingController::class, 'activeBookings'])->name('bookings.active');
    Route::get('/bookings/past', [BookingController::class, 'pastBookings'])->name('bookings.past');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::post('/apartments/{apartment}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites', [FavoriteController::class, 'list'])->name('favorites.list');

    Route::get('/reviews/{booking}/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/payment/{booking}', [PaymentController::class, 'initiate'])->name('payment.initiate');
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/payment/{booking}/success', [PaymentController::class, 'success'])->name('bookings.success');

    // Admin API routes
    Route::prefix('admin')->group(function () {
        
   // Booking Admin API Routes
    Route::post('/bookings/{booking}/decline', [BookingAdminController::class, 'decline'])->name('admin.bookings.decline');
    Route::get('/calendar/data', [BookingAdminController::class, 'getCalendarData'])->name('admin.calendar.data');
    
    // User Admin API Routes
    Route::get('/users/{user}', [UserAdminController::class, 'show'])->name('admin.users.show');
    Route::get('/users/search', [UserAdminController::class, 'search'])->name('admin.users.search');
});

});

Route::get('/sanctum/csrf-cookie', function() {
    return response()->json([
        'message' => 'CSRF cookie set',
        'csrf_token' => csrf_token()
    ])->cookie('XSRF-TOKEN', csrf_token(), 120, null, null, false, false)
      ->header('X-CSRF-TOKEN', csrf_token());
});

Route::get('/api/check-auth', function() {
    return [
        'user' => auth()->check() ? [
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'role' => auth()->user()->role
        ] : null
    ];
});

// Public API routes (no auth required)
Route::prefix('api')->group(function () {
    Route::get('/apartments', [ApartmentController::class, 'apiIndex']);
    Route::get('/apartments/{id}', [ApartmentController::class, 'apiShow']);
    Route::get('/apartments/search', [ApartmentController::class, 'apiSearch']);
});

Route::get('/admin/apartment/create', function () {
    return view('app'); // SPA fallback
})->middleware(['auth', 'admin']);

Route::get('/admin/apartment/edit/{id}', function () {
    return view('app'); // SPA fallback
})->middleware(['auth', 'admin']);

// SPA Fallback - ДОЛЖЕН БЫТЬ ПОСЛЕДНИМ
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');