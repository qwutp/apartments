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

        $bookings = Booking::where('apartment_id', $apartmentId)
            ->where('status', '!=', 'cancelled')
            ->with('user')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'checkIn' => $booking->check_in->format('Y-m-d'),
                    'checkOut' => $booking->check_out->format('Y-m-d'),
                    'user' => $booking->user->name,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->phone,
                    'status' => $booking->status,
                ];
            });

        return response()->json($bookings);
    }
}
