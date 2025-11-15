<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function create(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        return view('reviews.create', ['booking' => $booking]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $booking = Booking::find($validated['booking_id']);

        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if user already has a review for this apartment
        $existingReview = Review::where('user_id', Auth::id())
            ->where('apartment_id', $booking->apartment_id)
            ->first();

        if ($existingReview) {
            return back()->withErrors('You have already reviewed this apartment.');
        }

        Review::create([
            'user_id' => Auth::id(),
            'apartment_id' => $booking->apartment_id,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('apartments.show', $booking->apartment_id)->with('success', 'Review added successfully.');
    }
}
