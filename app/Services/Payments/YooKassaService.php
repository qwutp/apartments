<?php

namespace App\Services\Payments;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;
use YooKassa\Model\Payment;

class YooKassaService
{
    private Client $client;

    public function __construct()
    {
        $shopId = config('services.yookassa.shop_id');
        $secretKey = config('services.yookassa.secret_key');

        if (!$shopId || !$secretKey) {
            throw new \RuntimeException('YooKassa credentials are not configured.');
        }

        $this->client = new Client();
        $this->client->setAuth($shopId, $secretKey);
    }

    public function createPayment(Booking $booking, string $returnUrl): Payment
    {
        $booking->loadMissing('apartment');

        $amount = number_format($booking->total_price, 2, '.', '');

        $payload = [
            'amount' => [
                'value' => $amount,
                'currency' => 'RUB',
            ],
            'capture' => true,
            'description' => sprintf('Бронирование №%d (%s)', $booking->id, $booking->apartment->name ?? ''),
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $returnUrl,
            ],
            'metadata' => [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ],
        ];

        Log::info('Creating YooKassa payment', [
            'booking_id' => $booking->id,
            'payload' => $payload,
        ]);

        return $this->client->createPayment($payload, uniqid('', true));
    }

    public function getPayment(string $paymentId): ?Payment
    {
        if (!$paymentId) {
            return null;
        }

        return $this->client->getPaymentInfo($paymentId);
    }
}


