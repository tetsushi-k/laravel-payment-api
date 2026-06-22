<?php

namespace App\Services;

use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripePaymentIntentCreator
{
    public function create(int $amount): PaymentIntent
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));

        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'jpy',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }
}
