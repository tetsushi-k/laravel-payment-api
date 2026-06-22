<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\StripePaymentIntentCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\PaymentIntent;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_payment_intent_requires_authentication(): void
    {
        $this->postJson('/api/payment/intent', ['amount' => 1000])
            ->assertUnauthorized();
    }

    public function test_payment_intent_validates_amount(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/payment/intent', ['amount' => 50])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_payment_intent_creates_order_and_returns_client_secret(): void
    {
        $user = User::factory()->create();

        $paymentIntent = PaymentIntent::constructFrom([
            'id' => 'pi_test_123',
            'object' => 'payment_intent',
            'client_secret' => 'pi_test_123_secret_test',
        ]);

        $mock = Mockery::mock(StripePaymentIntentCreator::class);
        $mock->shouldReceive('create')
            ->once()
            ->with(1000)
            ->andReturn($paymentIntent);
        $this->instance(StripePaymentIntentCreator::class, $mock);

        $response = $this->actingAs($user)->postJson('/api/payment/intent', [
            'amount' => 1000,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('clientSecret', 'pi_test_123_secret_test');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'pending',
            'stripe_payment_intent_id' => 'pi_test_123',
        ]);
    }

    public function test_orders_returns_only_authenticated_users_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'succeeded',
            'stripe_payment_intent_id' => 'pi_user_order',
        ]);

        Order::create([
            'user_id' => $otherUser->id,
            'amount' => 2000,
            'status' => 'pending',
            'stripe_payment_intent_id' => 'pi_other_order',
        ]);

        $response = $this->actingAs($user)->getJson('/api/orders');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.stripe_payment_intent_id', 'pi_user_order')
            ->assertJsonPath('meta.per_page', 20);
    }

    public function test_stripe_config_returns_public_key(): void
    {
        config(['services.stripe.public_key' => 'pk_test_example']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/config/stripe')
            ->assertOk()
            ->assertJsonPath('publicKey', 'pk_test_example');
    }
}
