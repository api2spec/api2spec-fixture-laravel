<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TeaControllerTest extends TestCase
{
    /**
     * Test listing teas returns empty list initially.
     */
    public function test_index_returns_empty_list_initially(): void
    {
        $response = $this->getJson('/api/teas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination' => [
                    'page',
                    'limit',
                    'total',
                    'totalPages',
                ],
            ])
            ->assertJson([
                'data' => [],
                'pagination' => [
                    'page' => 1,
                    'limit' => 20,
                    'total' => 0,
                    'totalPages' => 0,
                ],
            ]);
    }

    /**
     * Test creating a tea with valid data returns 201.
     */
    public function test_store_creates_tea_with_valid_data(): void
    {
        $payload = [
            'name' => 'Sencha Green Tea',
            'type' => 'green',
            'origin' => 'Japan',
            'caffeineLevel' => 'medium',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 120,
            'description' => 'A classic Japanese green tea',
        ];

        $response = $this->postJson('/api/teas', $payload);

        $response->assertStatus(201)
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
                'name' => 'Sencha Green Tea',
                'type' => 'green',
                'origin' => 'Japan',
                'caffeineLevel' => 'medium',
                'steepTempCelsius' => 75,
                'steepTimeSeconds' => 120,
            ]);
    }

    /**
     * Test creating a tea uses default caffeine level when not provided.
     */
    public function test_store_uses_default_caffeine_level(): void
    {
        $payload = [
            'name' => 'Simple Tea',
            'type' => 'black',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 300,
        ];

        $response = $this->postJson('/api/teas', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'caffeineLevel' => 'medium',
            ]);
    }

    /**
     * Test creating a tea with missing required fields returns 422.
     */
    public function test_store_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/teas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'steepTempCelsius', 'steepTimeSeconds']);
    }

    /**
     * Test creating a tea with invalid type returns 422.
     */
    public function test_store_fails_with_invalid_type(): void
    {
        $payload = [
            'name' => 'Invalid Tea',
            'type' => 'coffee',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 180,
        ];

        $response = $this->postJson('/api/teas', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /**
     * Test creating a tea with temperature out of range returns 422.
     */
    public function test_store_fails_with_temperature_out_of_range(): void
    {
        // Too low
        $response = $this->postJson('/api/teas', [
            'name' => 'Cold Tea',
            'type' => 'green',
            'steepTempCelsius' => 50,
            'steepTimeSeconds' => 180,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steepTempCelsius']);

        // Too high
        $response = $this->postJson('/api/teas', [
            'name' => 'Hot Tea',
            'type' => 'green',
            'steepTempCelsius' => 110,
            'steepTimeSeconds' => 180,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steepTempCelsius']);
    }

    /**
     * Test creating a tea with steep time out of range returns 422.
     */
    public function test_store_fails_with_steep_time_out_of_range(): void
    {
        // Too low (0)
        $response = $this->postJson('/api/teas', [
            'name' => 'Flash Steep Tea',
            'type' => 'green',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steepTimeSeconds']);

        // Too high (> 600)
        $response = $this->postJson('/api/teas', [
            'name' => 'Long Steep Tea',
            'type' => 'green',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 700,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steepTimeSeconds']);
    }

    /**
     * Test retrieving a tea by ID returns 200.
     */
    public function test_show_returns_tea_by_id(): void
    {
        // First create a tea
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Test Tea',
            'type' => 'oolong',
            'steepTempCelsius' => 85,
            'steepTimeSeconds' => 180,
        ]);

        $teaId = $createResponse->json('id');

        $response = $this->getJson("/api/teas/{$teaId}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $teaId,
                'name' => 'Test Tea',
                'type' => 'oolong',
            ]);
    }

    /**
     * Test retrieving a non-existent tea returns 404.
     */
    public function test_show_returns_404_for_nonexistent_tea(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/teas/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ]);
    }

    /**
     * Test updating a tea with PUT returns 200.
     */
    public function test_update_replaces_tea_with_put(): void
    {
        // First create a tea
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Original Tea',
            'type' => 'green',
            'origin' => 'China',
            'caffeineLevel' => 'low',
            'steepTempCelsius' => 70,
            'steepTimeSeconds' => 120,
            'description' => 'Original description',
        ]);

        $teaId = $createResponse->json('id');

        // Update with PUT (full replacement)
        $payload = [
            'name' => 'Updated Tea',
            'type' => 'black',
            'origin' => 'India',
            'caffeineLevel' => 'high',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 300,
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/teas/{$teaId}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $teaId,
                'name' => 'Updated Tea',
                'type' => 'black',
                'origin' => 'India',
                'caffeineLevel' => 'high',
                'steepTempCelsius' => 95,
                'steepTimeSeconds' => 300,
            ]);
    }

    /**
     * Test updating a non-existent tea returns 404.
     */
    public function test_update_returns_404_for_nonexistent_tea(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $payload = [
            'name' => 'Updated Tea',
            'type' => 'green',
            'caffeineLevel' => 'medium',
            'steepTempCelsius' => 80,
            'steepTimeSeconds' => 180,
        ];

        $response = $this->putJson("/api/teas/{$fakeId}", $payload);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    /**
     * Test partial update with PATCH returns 200.
     */
    public function test_patch_partially_updates_tea(): void
    {
        // First create a tea
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Original Tea',
            'type' => 'green',
            'origin' => 'Japan',
            'caffeineLevel' => 'medium',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 120,
            'description' => 'Original description',
        ]);

        $teaId = $createResponse->json('id');

        // Partial update with PATCH
        $response = $this->patchJson("/api/teas/{$teaId}", [
            'name' => 'Patched Tea Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $teaId,
                'name' => 'Patched Tea Name',
                'type' => 'green',
                'origin' => 'Japan',
                'caffeineLevel' => 'medium',
                'steepTempCelsius' => 75,
                'steepTimeSeconds' => 120,
            ]);
    }

    /**
     * Test PATCH with invalid data returns 422.
     */
    public function test_patch_fails_with_invalid_data(): void
    {
        // First create a tea
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Test Tea',
            'type' => 'green',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 120,
        ]);

        $teaId = $createResponse->json('id');

        $response = $this->patchJson("/api/teas/{$teaId}", [
            'type' => 'invalid-type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /**
     * Test deleting a tea returns 204.
     */
    public function test_destroy_deletes_tea(): void
    {
        // First create a tea
        $createResponse = $this->postJson('/api/teas', [
            'name' => 'Tea To Delete',
            'type' => 'herbal',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 300,
        ]);

        $teaId = $createResponse->json('id');

        // Delete it
        $response = $this->deleteJson("/api/teas/{$teaId}");

        $response->assertStatus(204);

        // Verify it's gone
        $this->getJson("/api/teas/{$teaId}")
            ->assertStatus(404);
    }

    /**
     * Test deleting a non-existent tea returns 404.
     */
    public function test_destroy_returns_404_for_nonexistent_tea(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->deleteJson("/api/teas/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    /**
     * Test listing teas with pagination.
     */
    public function test_index_with_pagination(): void
    {
        // Create multiple teas
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/teas', [
                'name' => "Tea {$i}",
                'type' => 'green',
                'steepTempCelsius' => 75,
                'steepTimeSeconds' => 120,
            ]);
        }

        // Get first page with limit of 2
        $response = $this->getJson('/api/teas?page=1&limit=2');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.limit', 2)
            ->assertJsonPath('pagination.total', 5)
            ->assertJsonPath('pagination.totalPages', 3);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test listing teas with type filter.
     */
    public function test_index_with_type_filter(): void
    {
        // Create teas with different types
        $this->postJson('/api/teas', [
            'name' => 'Green Tea',
            'type' => 'green',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 120,
        ]);

        $this->postJson('/api/teas', [
            'name' => 'Black Tea',
            'type' => 'black',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 300,
        ]);

        $response = $this->getJson('/api/teas?type=green');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $tea) {
            $this->assertEquals('green', $tea['type']);
        }
    }

    /**
     * Test listing teas with caffeine level filter.
     */
    public function test_index_with_caffeine_level_filter(): void
    {
        // Create teas with different caffeine levels
        $this->postJson('/api/teas', [
            'name' => 'High Caffeine Tea',
            'type' => 'black',
            'caffeineLevel' => 'high',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 300,
        ]);

        $this->postJson('/api/teas', [
            'name' => 'No Caffeine Tea',
            'type' => 'herbal',
            'caffeineLevel' => 'none',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 300,
        ]);

        $response = $this->getJson('/api/teas?caffeineLevel=none');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $tea) {
            $this->assertEquals('none', $tea['caffeineLevel']);
        }
    }

    /**
     * Test all valid tea type values.
     */
    public function test_store_accepts_all_valid_types(): void
    {
        $types = ['green', 'black', 'oolong', 'white', 'puerh', 'herbal', 'rooibos'];

        foreach ($types as $type) {
            $response = $this->postJson('/api/teas', [
                'name' => "Tea - {$type}",
                'type' => $type,
                'steepTempCelsius' => 80,
                'steepTimeSeconds' => 180,
            ]);

            $response->assertStatus(201)
                ->assertJson(['type' => $type]);
        }
    }

    /**
     * Test all valid caffeine level values.
     */
    public function test_store_accepts_all_valid_caffeine_levels(): void
    {
        $levels = ['none', 'low', 'medium', 'high'];

        foreach ($levels as $level) {
            $response = $this->postJson('/api/teas', [
                'name' => "Tea - {$level} caffeine",
                'type' => 'green',
                'caffeineLevel' => $level,
                'steepTempCelsius' => 75,
                'steepTimeSeconds' => 120,
            ]);

            $response->assertStatus(201)
                ->assertJson(['caffeineLevel' => $level]);
        }
    }

    /**
     * Test creating tea with boundary values for temperature.
     */
    public function test_store_accepts_boundary_temperatures(): void
    {
        // Minimum temperature (60)
        $response = $this->postJson('/api/teas', [
            'name' => 'Min Temp Tea',
            'type' => 'green',
            'steepTempCelsius' => 60,
            'steepTimeSeconds' => 120,
        ]);

        $response->assertStatus(201)
            ->assertJson(['steepTempCelsius' => 60]);

        // Maximum temperature (100)
        $response = $this->postJson('/api/teas', [
            'name' => 'Max Temp Tea',
            'type' => 'black',
            'steepTempCelsius' => 100,
            'steepTimeSeconds' => 300,
        ]);

        $response->assertStatus(201)
            ->assertJson(['steepTempCelsius' => 100]);
    }

    /**
     * Test creating tea with boundary values for steep time.
     */
    public function test_store_accepts_boundary_steep_times(): void
    {
        // Minimum time (1 second)
        $response = $this->postJson('/api/teas', [
            'name' => 'Quick Steep Tea',
            'type' => 'green',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJson(['steepTimeSeconds' => 1]);

        // Maximum time (600 seconds / 10 minutes)
        $response = $this->postJson('/api/teas', [
            'name' => 'Long Steep Tea',
            'type' => 'herbal',
            'steepTempCelsius' => 95,
            'steepTimeSeconds' => 600,
        ]);

        $response->assertStatus(201)
            ->assertJson(['steepTimeSeconds' => 600]);
    }
}
