<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaShellTest extends TestCase
{
    public function test_spa_fallback_returns_shell(): void
    {
        $response = $this->get('/payment');

        $response
            ->assertOk()
            ->assertSee('id="root"', false);
    }
}
