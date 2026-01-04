<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class BrewTest extends TestCase
{
    private function createTeapot(): string
    {
        $response = $this->postJson('/api/teapots', [
            'name' => 'Test Teapot',
            'material' => 'ceramic',
            'capacityMl' => 500,
        ]);

        return $response->json('id');
    }

    private function createTea(): string
    {
        $response = $this->postJson('/api/teas', [
            'name' => 'Test Tea',
            'type' => 'green',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 120,
        ]);

        return $response->json('id');
    }

    public function test_can_list_brews(): void
    {
        $response = $this->getJson('/api/brews');

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

    public function test_can_create_brew(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'teapotId',
                'teaId',
                'status',
                'waterTempCelsius',
                'notes',
                'startedAt',
                'completedAt',
                'createdAt',
                'updatedAt',
            ])
            ->assertJson([
                'teapotId' => $teapotId,
                'teaId' => $teaId,
                'status' => 'preparing',
                'waterTempCelsius' => 80, // From the tea's steepTempCelsius
            ]);
    }

    public function test_create_brew_with_custom_temp(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
            'waterTempCelsius' => 85,
            'notes' => 'Testing a higher temp',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'waterTempCelsius' => 85,
                'notes' => 'Testing a higher temp',
            ]);
    }

    public function test_create_brew_validation_error(): void
    {
        $response = $this->postJson('/api/brews', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['teapotId', 'teaId']);
    }

    public function test_create_brew_teapot_not_found(): void
    {
        $teaId = $this->createTea();

        $response = $this->postJson('/api/brews', [
            'teapotId' => '00000000-0000-0000-0000-000000000000',
            'teaId' => $teaId,
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ]);
    }

    public function test_create_brew_tea_not_found(): void
    {
        $teapotId = $this->createTeapot();

        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => '00000000-0000-0000-0000-000000000000',
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ]);
    }

    public function test_can_get_brew(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        $createResponse = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        $id = $createResponse->json('id');

        $response = $this->getJson("/api/brews/{$id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'teapotId' => $teapotId,
                'teaId' => $teaId,
            ]);
    }

    public function test_get_brew_not_found(): void
    {
        $response = $this->getJson('/api/brews/non-existent-id');

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    public function test_can_patch_brew(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        $createResponse = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        $id = $createResponse->json('id');

        $response = $this->patchJson("/api/brews/{$id}", [
            'status' => 'steeping',
            'notes' => 'Updated notes',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'status' => 'steeping',
                'notes' => 'Updated notes',
            ]);
    }

    public function test_can_delete_brew(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        $createResponse = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        $id = $createResponse->json('id');

        $response = $this->deleteJson("/api/brews/{$id}");

        $response->assertStatus(204);

        // Verify it's deleted
        $this->getJson("/api/brews/{$id}")->assertStatus(404);
    }

    public function test_can_list_brews_by_teapot(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        // Create a brew for this teapot
        $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        $response = $this->getJson("/api/teapots/{$teapotId}/brews");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination',
            ]);
    }

    public function test_list_brews_by_teapot_not_found(): void
    {
        $response = $this->getJson('/api/teapots/non-existent-id/brews');

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }
}
