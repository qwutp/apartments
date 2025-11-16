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

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не можете оставить отзыв на это бронирование'
            ], 403);
        }

        // Check if user already has a review for this apartment
        $existingReview = Review::where('user_id', Auth::id())
            ->where('apartment_id', $booking->apartment_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже оставили отзыв на этот апартамент'
            ], 422);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'apartment_id' => $booking->apartment_id,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Отзыв успешно добавлен',
            'review' => $review->load('user')
        ]);
    }

    public function apiGetBookingsForReview()
    {
        // Получаем все бронирования пользователя, на которые можно оставить отзыв
        $bookings = Auth::user()->bookings()
            ->where('status', 'paid')
            ->where('check_out', '<=', now()) // Бронирование завершено
            ->with(['apartment.images'])
            ->get()
            ->filter(function($booking) {
                // Проверяем, что отзыв еще не оставлен
                $existingReview = Review::where('user_id', Auth::id())
                    ->where('apartment_id', $booking->apartment_id)
                    ->first();
                return !$existingReview;
            })
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'apartment_id' => $booking->apartment_id,
                    'apartment' => [
                        'id' => $booking->apartment->id,
                        'name' => $booking->apartment->name,
                        'address' => $booking->apartment->address,
                        'images' => $booking->apartment->images->map(function($img) {
                            $path = $img->image_path;
                            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                                return ['id' => $img->id, 'url' => $path];
                            }
                            $path = ltrim($path, '/');
                            if (str_starts_with($path, 'images/apartments/')) {
                                $url = asset($path);
                            } else {
                                $path = ltrim($path, 'storage/');
                                $url = asset('storage/' . $path);
                            }
                            return ['id' => $img->id, 'url' => $url];
                        })
                    ],
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                ];
            });

        return response()->json($bookings->values());
    }
}
