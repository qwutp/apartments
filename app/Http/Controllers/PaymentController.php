<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function initiate(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // Redirect to Yoomoney payment
        $yoomoneyUrl = config('services.yoomoney.url');
        $shopId = config('services.yoomoney.shop_id');
        $sceneId = config('services.yoomoney.scene_id');

        $params = [
            'shopId' => $shopId,
            'sceneId' => $sceneId,
            'orderNumber' => 'BOOKING-' . $booking->id,
            'customerNumber' => auth()->id(),
            'sum' => $booking->total_price,
            'orderDetails' => 'Booking for ' . $booking->apartment->name,
            'returnUrl' => route('payment.callback'),
        ];

        return redirect($yoomoneyUrl . '?' . http_build_query($params));
    }

    public function callback(Request $request)
    {
        Log::info('Yoomoney callback received', $request->all());

        // Parse booking ID from order number
        $orderNumber = $request->input('orderNumber');
        if (!preg_match('/BOOKING-(\d+)/', $orderNumber, $matches)) {
            return response()->json(['error' => 'Invalid order number'], 400);
        }

        $bookingId = $matches[1];
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Verify payment with Yoomoney (in production, verify the signature)
        if ($request->input('status') === 'success') {
            $booking->update([
                'status' => 'paid',
                'payment_id' => $request->input('paymentId'),
                'paid_at' => now(),
            ]);

            // Update apartment status if all bookings are confirmed
            $this->updateApartmentStatus($booking->apartment_id);

            return redirect()->route('bookings.success', $bookingId);
        }

        return redirect()->route('bookings.failed', $bookingId);
    }

    public function success(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        return view('payments.success', ['booking' => $booking->load('apartment')]);
    }

    public function failed(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        return view('payments.failed', ['booking' => $booking]);
    }

    private function updateApartmentStatus($apartmentId)
    {
        $apartment = \App\Models\Apartment::find($apartmentId);
        $hasActiveBooking = $apartment->bookings()
            ->where('status', 'paid')
            ->where('check_out', '>', now())
            ->exists();

        $apartment->update([
            'status' => $hasActiveBooking ? 'booked' : 'available',
        ]);
    }
}
