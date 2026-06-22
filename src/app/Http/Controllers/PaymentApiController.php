<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StripePaymentIntentCreator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    public function __construct(
        private readonly StripePaymentIntentCreator $paymentIntentCreator
    ) {}

    public function createIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:100', 'max:1000000'],
        ]);

        $paymentIntent = $this->paymentIntentCreator->create($validated['amount']);

        Order::create([
            'user_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
            'stripe_payment_intent_id' => $paymentIntent->id,
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }
}
