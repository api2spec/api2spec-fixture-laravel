<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    /**
     * Test basic health check returns 200 with expected structure.
     */
    public function test_health_returns_ok_status(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
            ])
            ->assertJson([
                'status' => 'ok',
                'version' => '1.0.0',
            ]);
    }

    /**
     * Test liveness probe returns 200.
     */
    public function test_liveness_probe_returns_ok(): void
    {
        $response = $this->getJson('/api/health/live');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    /**
     * Test readiness probe returns 200 with checks.
     */
    public function test_readiness_probe_returns_ok_with_checks(): void
    {
        $response = $this->getJson('/api/health/ready');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    '*' => ['name', 'status'],
                ],
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    /**
     * Test TIF 418 brew endpoint returns I'm a teapot.
     */
    public function test_brew_returns_418_teapot_response(): void
    {
        $response = $this->getJson('/api/brew');

        $response->assertStatus(418)
            ->assertJsonStructure([
                'error',
                'message',
                'spec',
            ])
            ->assertJson([
                'error' => "I'm a teapot",
                'spec' => 'https://teapotframework.dev',
            ]);
    }
}
