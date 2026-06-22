<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->statefulApi()->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_login_with_invalid_credentials_returns_validation_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_endpoint_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_user_endpoint_returns_unauthorized_when_guest(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->statefulApi()->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk();

        $this->assertAuthenticated('web');

        $this->sanctumHeaders()
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->assertGuest('web');
    }
}
