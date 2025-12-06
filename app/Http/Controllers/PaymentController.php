<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\Payments\YooKassaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Model\PaymentStatus;

class PaymentController extends Controller
{
    public function __construct(private readonly YooKassaService $payments)
    {
    }

    public function createPayment(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Бронирование уже оплачено.',
                'booking' => $this->formatBooking($booking),
            ]);
        }

        $returnUrl = route('payment.return', ['booking' => $booking->id]);

        try {
            $payment = $this->payments->createPayment($booking, $returnUrl);

            $booking->update([
                'payment_id' => $payment->getId(),
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->getId(),
                'confirmation_url' => $payment->getConfirmation()->getConfirmationUrl(),
            ]);
        } catch (\Throwable $e) {
            Log::error('YooKassa payment create error', [
                'booking_id' => $booking->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось создать платеж. Попробуйте позже.',
            ], 500);
        }
    }

    public function return(Booking $booking, Request $request)
    {
        $paymentId = $request->query('paymentId') ?? $booking->payment_id;
        $status = 'failed';

        if ($paymentId) {
            try {
                $payment = $this->payments->getPayment($paymentId);
                if ($payment && $this->paymentMatchesBooking($payment, $booking) && $payment->getStatus() === PaymentStatus::SUCCEEDED) {
                    $this->markPaid($booking, $paymentId);
                    $status = 'success';
                }
            } catch (\Throwable $e) {
                Log::error('YooKassa return error', [
                    'booking_id' => $booking->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return redirect('/user?tab=active-bookings&payment=' . $status);
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info('YooKassa webhook received', $data);

        $event = $data['event'] ?? null;
        $object = $data['object'] ?? null;

        if (!$event || !$object) {
            return response()->json(['status' => 'ignored'], 400);
        }

        if ($event === 'payment.succeeded' && isset($object['metadata']['booking_id'])) {
            $booking = Booking::find($object['metadata']['booking_id']);
            if ($booking) {
                $this->markPaid($booking, $object['id'] ?? null);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function markPaid(Booking $booking, ?string $paymentId): void
    {
        if ($booking->status === 'paid') {
            return;
        }

        $booking->update([
            'status' => 'paid',
            'payment_id' => $paymentId ?? $booking->payment_id,
            'paid_at' => now(),
        ]);

        $this->updateApartmentStatus($booking);
    }

    private function paymentMatchesBooking($payment, Booking $booking): bool
    {
        $metadata = $payment?->getMetadata();
        if (!$metadata) {
            return false;
        }

        $bookingId = $metadata->offsetExists('booking_id') ? (int) $metadata->offsetGet('booking_id') : null;

        return $bookingId === $booking->id;
    }

    private function updateApartmentStatus(Booking $booking): void
    {
        $apartment = $booking->apartment()->first();

        if (!$apartment) {
            return;
        }

        $hasActiveBooking = $apartment->bookings()
            ->where('status', 'paid')
            ->where('check_out', '>', now())
            ->exists();

        $apartment->update([
            'status' => $hasActiveBooking ? 'booked' : 'available',
        ]);
    }

    private function formatBooking(Booking $booking): array
    {
        $booking->loadMissing('apartment.images');

        return [
            'id' => $booking->id,
            'status' => $booking->status,
            'total_price' => $booking->total_price,
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'apartment' => [
                'id' => $booking->apartment->id,
                'name' => $booking->apartment->name,
                'address' => $booking->apartment->address,
                'images' => $booking->apartment->images,
            ],
        ];
    }
}
