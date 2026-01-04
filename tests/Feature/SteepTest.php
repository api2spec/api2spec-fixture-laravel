<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SteepTest extends TestCase
{
    private function createBrew(): string
    {
        // Create teapot
        $teapotResponse = $this->postJson('/api/teapots', [
            'name' => 'Test Teapot',
            'material' => 'ceramic',
            'capacityMl' => 500,
        ]);
        $teapotId = $teapotResponse->json('id');

        // Create tea
        $teaResponse = $this->postJson('/api/teas', [
            'name' => 'Test Tea',
            'type' => 'green',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 120,
        ]);
        $teaId = $teaResponse->json('id');

        // Create brew
        $brewResponse = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        return $brewResponse->json('id');
    }

    public function test_can_list_steeps(): void
    {
        $brewId = $this->createBrew();

        $response = $this->getJson("/api/brews/{$brewId}/steeps");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination' => [
                    'page',
                    'limit',
                    'total',
                    'totalPages',
                ],
            ]);
    }

    public function test_list_steeps_brew_not_found(): void
    {
        $response = $this->getJson('/api/brews/non-existent-id/steeps');

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    public function test_can_create_steep(): void
    {
        $brewId = $this->createBrew();

        $response = $this->postJson("/api/brews/{$brewId}/steeps", [
            'durationSeconds' => 30,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'brewId',
                'steepNumber',
                'durationSeconds',
                'rating',
                'notes',
                'createdAt',
            ])
            ->assertJson([
                'brewId' => $brewId,
                'steepNumber' => 1,
                'durationSeconds' => 30,
            ]);
    }

    public function test_create_steep_with_rating_and_notes(): void
    {
        $brewId = $this->createBrew();

        $response = $this->postJson("/api/brews/{$brewId}/steeps", [
            'durationSeconds' => 45,
            'rating' => 5,
            'notes' => 'Perfect steep!',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'steepNumber' => 1,
                'durationSeconds' => 45,
                'rating' => 5,
                'notes' => 'Perfect steep!',
            ]);
    }

    public function test_steep_numbers_increment(): void
    {
        $brewId = $this->createBrew();

        // Create first steep
        $response1 = $this->postJson("/api/brews/{$brewId}/steeps", [
            'durationSeconds' => 30,
        ]);
        $response1->assertJson(['steepNumber' => 1]);

        // Create second steep
        $response2 = $this->postJson("/api/brews/{$brewId}/steeps", [
            'durationSeconds' => 45,
        ]);
        $response2->assertJson(['steepNumber' => 2]);

        // Create third steep
        $response3 = $this->postJson("/api/brews/{$brewId}/steeps", [
            'durationSeconds' => 60,
        ]);
        $response3->assertJson(['steepNumber' => 3]);
    }

    public function test_create_steep_validation_error(): void
    {
        $brewId = $this->createBrew();

        $response = $this->postJson("/api/brews/{$brewId}/steeps", []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['durationSeconds']);
    }

    public function test_create_steep_invalid_rating(): void
    {
        $brewId = $this->createBrew();

        $response = $this->postJson("/api/brews/{$brewId}/steeps", [
            'durationSeconds' => 30,
            'rating' => 6, // Invalid: max is 5
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_create_steep_brew_not_found(): void
    {
        $response = $this->postJson('/api/brews/non-existent-id/steeps', [
            'durationSeconds' => 30,
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }
}
