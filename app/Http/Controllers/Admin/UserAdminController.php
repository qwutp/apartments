<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function show(User $user)
    {
        $bookingCount = $user->bookings()->where('status', '!=', 'cancelled')->count();
        $pastBookingCount = $user->bookings()->where('check_out', '<', now())->where('status', '!=', 'cancelled')->count();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'patronymic' => $user->patronymic,
            'passport_series' => $user->passport_series,
            'passport_number' => $user->passport_number,
            'booking_count' => $bookingCount,
            'past_booking_count' => $pastBookingCount,
        ]);
    }

    public function index(Request $request)
    {
        $users = User::where('role', 'user')
            ->with(['bookings' => function ($query) {
                $query->where('status', 'paid')
                    ->where('check_out', '>=', now())
                    ->where('check_in', '<=', now())
                    ->with('apartment:id,name,address')
                    ->latest();
            }])
            ->get()
            ->map(function ($user) {
                // Получаем текущее активное бронирование
                $currentBooking = $user->bookings
                    ->where('status', 'paid')
                    ->where('check_out', '>=', now())
                    ->where('check_in', '<=', now())
                    ->first();
                
                // Считаем прошлые бронирования отдельным запросом
                $pastBookingCount = Booking::where('user_id', $user->id)
                    ->where('check_out', '<', now())
                    ->where('status', '!=', 'cancelled')
                    ->count();
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'full_name' => trim(($user->last_name ?? '') . ' ' . ($user->first_name ?? '') . ' ' . ($user->patronymic ?? '')),
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'patronymic' => $user->patronymic,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'passport_series' => $user->passport_series,
                    'passport_number' => $user->passport_number,
                    'current_apartment' => $currentBooking && $currentBooking->apartment ? [
                        'id' => $currentBooking->apartment->id,
                        'name' => $currentBooking->apartment->name,
                        'address' => $currentBooking->apartment->address,
                    ] : null,
                    'past_booking_count' => $pastBookingCount,
                ];
            });

        return response()->json($users);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $users = User::where('role', 'user')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('patronymic', 'like', "%{$query}%");
            })
            ->with(['bookings' => function ($query) {
                $query->where('status', 'paid')
                    ->where('check_out', '>=', now())
                    ->where('check_in', '<=', now())
                    ->with('apartment:id,name,address')
                    ->latest()
                    ->limit(1);
            }])
            ->get()
            ->map(function ($user) {
                $currentBooking = $user->bookings->first();
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'full_name' => trim(($user->last_name ?? '') . ' ' . ($user->first_name ?? '') . ' ' . ($user->patronymic ?? '')),
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'patronymic' => $user->patronymic,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'current_apartment' => $currentBooking ? [
                        'id' => $currentBooking->apartment->id,
                        'name' => $currentBooking->apartment->name,
                        'address' => $currentBooking->apartment->address,
                    ] : null,
                ];
            });

        return response()->json($users);
    }
}
