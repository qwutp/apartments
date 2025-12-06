<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function apiStore(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'passport_series' => 'nullable|string|max:10',
            'passport_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
        ]);

        $apartment = Apartment::findOrFail($validated['apartment_id']);

        if ($validated['guests'] > ($apartment->max_guests ?? 10)) {
            return response()->json([
                'success' => false,
                'message' => 'На указанный период невозможно разместить такое количество гостей.',
            ], 422);
        }

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);

        $overlap = Booking::where('apartment_id', $apartment->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Выбранные даты уже заняты. Пожалуйста, выберите другие даты.',
            ], 409);
        }

        $nights = max(1, $checkOut->diffInDays($checkIn));
        $totalPrice = ($apartment->price_per_night ?? $apartment->price ?? 0) * $nights;

        $booking = DB::transaction(function () use ($user, $apartment, $validated, $checkIn, $checkOut, $totalPrice) {
            $user->update(array_filter([
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'patronymic' => $validated['patronymic'] ?? null,
                'passport_series' => $validated['passport_series'] ?? null,
                'passport_number' => $validated['passport_number'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
            ], fn ($value) => !is_null($value) && $value !== ''));

            return Booking::create([
                'user_id' => $user->id,
                'apartment_id' => $apartment->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => $validated['guests'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'success' => true,
            'booking' => $this->formatBooking($booking),
        ], 201);
    }

    public function apiShow(Booking $booking)
    {
        $this->authorizeBooking($booking);

        return response()->json([
            'success' => true,
            'booking' => $this->formatBooking($booking),
        ]);
    }

    public function apiActive()
    {
        $bookings = Auth::user()->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('check_out', '>=', now())
            ->with('apartment.images')
            ->orderByDesc('check_in')
            ->get()
            ->map(fn ($booking) => $this->formatBooking($booking));

        return response()->json($bookings);
    }

    public function apiPast()
    {
        $bookings = Auth::user()->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('check_out', '<', now())
            ->with('apartment.images')
            ->orderByDesc('check_in')
            ->get()
            ->map(fn ($booking) => $this->formatBooking($booking, true));

        return response()->json($bookings);
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function formatBooking(Booking $booking, bool $includeReviewData = false): array
    {
        $booking->loadMissing('apartment.images');
        $firstImage = $booking->apartment->images->first();

        $imageUrl = null;
        if ($firstImage) {
            $path = $firstImage->image_path ?? $firstImage->url ?? '';
            if ($path) {
                if (str_starts_with($path, 'http')) {
                    $imageUrl = $path;
                } else {
                    $cleanPath = ltrim($path, '/');
                    $imageUrl = str_starts_with($cleanPath, 'images/apartments/')
                        ? '/' . $cleanPath
                        : asset('storage/' . ltrim($cleanPath, 'storage/'));
                }
            }
        }

        $data = [
            'id' => $booking->id,
            'status' => $booking->status,
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'guests' => $booking->guests,
            'total_price' => $booking->total_price,
            'paid_at' => $booking->paid_at,
            'apartment' => [
                'id' => $booking->apartment->id,
                'name' => $booking->apartment->name,
                'address' => $booking->apartment->address,
                'image' => $imageUrl,
            ],
        ];

        if ($includeReviewData) {
            $data['can_review'] = $booking->status === 'paid';
        }

        return $data;
    }
}
