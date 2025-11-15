<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function create(Apartment $apartment)
    {
        return view('bookings.create', [
            'apartment' => $apartment->load('images'),
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
        ]);

        $apartment = Apartment::find($validated['apartment_id']);
        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $nights = $checkOut->diffInDays($checkIn);
        $totalPrice = $apartment->price_per_night * $nights;

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'apartment_id' => $apartment->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $validated['guests'],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        return redirect()->route('bookings.checkout', $booking->id);
    }

    public function checkout(Booking $booking)
    {
        return view('bookings.checkout', [
            'booking' => $booking->load('apartment.images', 'user'),
        ]);
    }

    public function activeBookings()
    {
        $bookings = Auth::user()->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('check_out', '>=', now())
            ->with('apartment.images')
            ->get();

        return view('bookings.active', ['bookings' => $bookings]);
    }

    public function pastBookings()
    {
        $bookings = Auth::user()->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('check_out', '<', now())
            ->with('apartment.images')
            ->get();

        return view('bookings.past', ['bookings' => $bookings]);
    }

    public function cancel(Booking $booking, Request $request)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->input('reason'),
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
