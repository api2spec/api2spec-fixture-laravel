<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TeaTest extends TestCase
{
    public function test_can_list_teas(): void
    {
        $response = $this->getJson('/api/teas');

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

    public function test_can_create_tea(): void
    {
        $response = $this->postJson('/api/teas', [
            'name' => 'Dragon Well',
            'type' => 'green',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 120,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'type',
                'origin',
                'caffeineLevel',
                'steepTempCelsius',
                'steepTimeSeconds',
                'description',
                'createdAt',
                'updatedAt',
            ])
            ->assertJson([
                'name' => 'Dragon Well',
                'type' => 'green',
                'caffeineLevel' => 'medium', // Default value
                'steepTempCelsius' => 80,
                'steepTimeSeconds' => 120,
            ]);
    }

    public function test_can_create_tea_with_all_fields(): void
    {
        $response = $this->postJson('/api/teas', [
            'name' => 'Gyokuro',
            'type' => 'green',
            'origin' => 'Uji, Japan',
            'caffeineLevel' => 'high',
            'steepTempCelsius' => 60,
            'steepTimeSeconds' => 90,
            'description' => 'Premium shade-grown green tea',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'name' => 'Gyokuro',
                'type' => 'green',
                'origin' => 'Uji, Japan',
                'caffeineLevel' => 'high',
                'description' => 'Premium shade-grown green tea',
            ]);
    }

    public function test_create_tea_validation_error(): void
    {
        $response = $this->postJson('/api/teas', [
            'name' => '',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'steepTempCelsius', 'steepTimeSeconds']);
    }

    public function test_create_tea_invalid_type(): void
    {
        $response = $this->postJson('/api/teas', [
            'name' => 'Test',
            'type' => 'invalid-type',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 120,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_can_get_tea(): void
    {
        // Create a tea first
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Earl Grey',
            'type' => 'black',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 180,
        ]);

        $id = $createResponse->json('id');

        $response = $this->getJson("/api/teas/{$id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'name' => 'Earl Grey',
                'type' => 'black',
            ]);
    }

    public function test_get_tea_not_found(): void
    {
        $response = $this->getJson('/api/teas/non-existent-id');

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    public function test_can_update_tea(): void
    {
        // Create a tea first
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Original Name',
            'type' => 'green',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 120,
        ]);

        $id = $createResponse->json('id');

        $response = $this->putJson("/api/teas/{$id}", [
            'name' => 'Updated Name',
            'type' => 'black',
            'caffeineLevel' => 'high',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 180,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'name' => 'Updated Name',
                'type' => 'black',
                'caffeineLevel' => 'high',
            ]);
    }

    public function test_can_patch_tea(): void
    {
        // Create a tea first
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Original Name',
            'type' => 'green',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 120,
        ]);

        $id = $createResponse->json('id');

        $response = $this->patchJson("/api/teas/{$id}", [
            'name' => 'Patched Name',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'name' => 'Patched Name',
                'type' => 'green', // Should remain unchanged
            ]);
    }

    public function test_can_delete_tea(): void
    {
        // Create a tea first
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'To Delete',
            'type' => 'herbal',
            'steepTempCelsius' => 100,
            'steepTimeSeconds' => 300,
        ]);

        $id = $createResponse->json('id');

        $response = $this->deleteJson("/api/teas/{$id}");

        $response->assertStatus(204);

        // Verify it's deleted
        $this->getJson("/api/teas/{$id}")->assertStatus(404);
    }
}
