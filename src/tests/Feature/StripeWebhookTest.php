<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_missing_signature(): void
    {
        $this->post('/api/webhook/stripe', [], [
            'CONTENT_TYPE' => 'application/json',
        ])->assertStatus(400);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

        $payload = $this->paymentIntentSucceededPayload('pi_invalid_sig');

        $this->call(
            'POST',
            '/api/webhook/stripe',
            server: $this->transformHeadersToServerVars([
                'Stripe-Signature' => 'invalid',
                'Content-Type' => 'application/json',
            ]),
            content: $payload,
        )->assertStatus(400);
    }

    public function test_webhook_updates_pending_order_to_succeeded(): void
    {
        $secret = 'whsec_test_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $user = User::factory()->create();
        $paymentIntentId = 'pi_webhook_success';

        Order::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'pending',
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);

        $payload = $this->paymentIntentSucceededPayload($paymentIntentId);
        $signature = $this->stripeWebhookSignature($payload, $secret);

        $this->call(
            'POST',
            '/api/webhook/stripe',
            server: $this->transformHeadersToServerVars([
                'Stripe-Signature' => $signature,
                'Content-Type' => 'application/json',
            ]),
            content: $payload,
        )->assertOk();

        $this->assertDatabaseHas('orders', [
            'stripe_payment_intent_id' => $paymentIntentId,
            'status' => 'succeeded',
        ]);
    }

    public function test_webhook_is_idempotent_for_already_succeeded_order(): void
    {
        $secret = 'whsec_test_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $user = User::factory()->create();
        $paymentIntentId = 'pi_webhook_idempotent';

        Order::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'succeeded',
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);

        $payload = $this->paymentIntentSucceededPayload($paymentIntentId);
        $signature = $this->stripeWebhookSignature($payload, $secret);

        $this->call(
            'POST',
            '/api/webhook/stripe',
            server: $this->transformHeadersToServerVars([
                'Stripe-Signature' => $signature,
                'Content-Type' => 'application/json',
            ]),
            content: $payload,
        )->assertOk();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'stripe_payment_intent_id' => $paymentIntentId,
            'status' => 'succeeded',
        ]);
    }

    private function paymentIntentSucceededPayload(string $paymentIntentId): string
    {
        return json_encode([
            'id' => 'evt_test',
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => $paymentIntentId,
                    'object' => 'payment_intent',
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }
}
