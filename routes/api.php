<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Публичные маршруты - используем api методы
Route::get('/apartments', [ApartmentController::class, 'apiIndex']);
Route::get('/apartments/{id}', [ApartmentController::class, 'apiShow'])->where('id', '[0-9]+');
Route::get('/apartments/search', [ApartmentController::class, 'apiSearch']);

// Аутентификация
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Получение CSRF токена для Vue
Route::get('/csrf-cookie', function() {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Защищенные маршруты
Route::middleware(['auth'])->group(function () {
    // Пользователь
    Route::get('/user', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });

    // Бронирования
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    // Избранное
    Route::get('/favorites', [FavoriteController::class, 'apiList']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy']);

    // Отзывы
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    
    // Тестовый маршрут для отладки
    Route::get('/debug/session', function() {
        return response()->json([
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'authenticated' => auth()->check(),
        ]);
    });
});