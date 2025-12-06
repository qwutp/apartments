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


// Admin apartment routes - protected with admin middleware
Route::prefix('api')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/apartments', [ApartmentAdminController::class, 'store']);
    Route::match(['put', 'post'], '/apartments/{id}', [ApartmentAdminController::class, 'update'])->where('id', '[0-9]+');
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
});

Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
Route::get('/payment/{booking}/return', [PaymentController::class, 'return'])->name('payment.return');

Route::get('/sanctum/csrf-cookie', function() {
    return response()->json([
        'message' => 'CSRF cookie set',
        'csrf_token' => csrf_token()
    ])->cookie('XSRF-TOKEN', csrf_token(), 120, null, null, false, false)
      ->header('X-CSRF-TOKEN', csrf_token());
});

// Check auth endpoint - должен быть доступен без авторизации
Route::get('/api/check-auth', function() {
    try {
        $user = null;
        if (auth()->check()) {
            $user = [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role
            ];
        }
        
        return response()->json([
            'user' => $user
        ])->header('X-CSRF-TOKEN', csrf_token());
    } catch (\Exception $e) {
        \Log::error('Check auth error: ' . $e->getMessage());
        return response()->json([
            'user' => null,
            'error' => $e->getMessage()
        ], 500);
    }
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