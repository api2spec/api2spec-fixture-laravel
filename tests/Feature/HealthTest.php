<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response
            ->assertStatus(200)
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

    public function test_live_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health/live');

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_ready_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health/ready');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_brew_endpoint_returns_418(): void
    {
        $response = $this->getJson('/api/brew');

        $response
            ->assertStatus(418)
            ->assertJson([
                'error' => "I'm a teapot",
                'message' => 'This server is TIF-compliant and cannot brew coffee',
                'spec' => 'https://teapotframework.dev',
            ]);
    }
}
