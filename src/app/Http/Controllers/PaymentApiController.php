<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentApiController extends Controller
{
    public function createIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:100', 'max:1000000'],
        ]);

        Stripe::setApiKey(config('services.stripe.secret_key'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $validated['amount'],
            'currency' => 'jpy',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

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
