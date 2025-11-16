<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;

class BookingAdminController extends Controller
{
    public function decline(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'] ?? null,
            'cancelled_at' => now(),
        ]);

        // Create a notification for the user about the declined booking
        Notification::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'title' => 'Booking Declined',
            'message' => 'Your booking for ' . $booking->apartment->name . ' has been declined.',
            'type' => 'booking_cancelled',
        ]);

        return back()->with('success', 'Booking declined successfully.');
    }

    public function getCalendarData(Request $request)
    {
        $apartmentId = $request->input('apartment_id');

        if (!$apartmentId) {
            return response()->json([]);
        }

        $bookings = Booking::where('apartment_id', $apartmentId)
            ->where('status', '!=', 'cancelled')
            ->with('user', 'apartment:id,name,address')
            ->orderBy('check_in', 'asc')
            ->get()
            ->map(function ($booking) {
                $fullName = trim(
                    ($booking->user->last_name ?? '') . ' ' . 
                    ($booking->user->first_name ?? '') . ' ' . 
                    ($booking->user->patronymic ?? '')
                );
                
                return [
                    'id' => $booking->id,
                    'check_in' => $booking->check_in->format('Y-m-d'),
                    'check_out' => $booking->check_out->format('Y-m-d'),
                    'checkIn' => $booking->check_in->format('Y-m-d'),
                    'checkOut' => $booking->check_out->format('Y-m-d'),
                    'user' => [
                        'id' => $booking->user->id,
                        'name' => $booking->user->name,
                        'full_name' => $fullName ?: $booking->user->name,
                        'first_name' => $booking->user->first_name,
                        'last_name' => $booking->user->last_name,
                        'patronymic' => $booking->user->patronymic,
                        'email' => $booking->user->email,
                        'phone' => $booking->user->phone,
                    ],
                    'apartment' => [
                        'id' => $booking->apartment->id,
                        'name' => $booking->apartment->name,
                        'address' => $booking->apartment->address,
                    ],
                    'status' => $booking->status,
                    'guests' => $booking->guests,
                    'total_price' => $booking->total_price,
                ];
            });

        return response()->json($bookings);
    }
}
