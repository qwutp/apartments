<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function search(Request $request)
    {
        $query = $request->input('query');

        $users = User::where('role', 'user')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->get();

        return response()->json($users);
    }
}
