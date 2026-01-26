<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class BrewControllerTest extends TestCase
{
    /**
     * Helper method to create a teapot for testing.
     */
    private function createTeapot(): string
    {
        $response = $this->postJson('/api/teapots', [
            'name' => 'Test Teapot',
            'material' => 'ceramic',
            'capacityMl' => 400,
        ]);

        return $response->json('id');
    }

    /**
     * Helper method to create a tea for testing.
     */
    private function createTea(): string
    {
        $response = $this->postJson('/api/teas', [
            'name' => 'Test Tea',
            'type' => 'green',
            'steepTempCelsius' => 75,
            'steepTimeSeconds' => 120,
        ]);

        return $response->json('id');
    }

    /**
     * Helper method to create a brew for testing.
     */
    private function createBrew(): array
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        return [
            'brewId' => $response->json('id'),
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ];
    }

    /**
     * Test listing brews returns empty list initially.
     */
    public function test_index_returns_empty_list_initially(): void
    {
        $response = $this->getJson('/api/brews');

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
     * Test creating a brew with valid data returns 201.
     */
    public function test_store_creates_brew_with_valid_data(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        $payload = [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
            'waterTempCelsius' => 80,
            'notes' => 'First brew of the day',
        ];

        $response = $this->postJson('/api/brews', $payload);

        $response->assertStatus(201)
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
                'waterTempCelsius' => 80,
                'notes' => 'First brew of the day',
            ]);
    }

    /**
     * Test creating a brew defaults water temperature from tea.
     */
    public function test_store_defaults_water_temp_from_tea(): void
    {
        $teapotId = $this->createTeapot();

        // Create a tea with specific steep temp
        $teaResponse = $this->postJson('/api/teas', [
            'name' => 'Test Tea',
            'type' => 'oolong',
            'steepTempCelsius' => 85,
            'steepTimeSeconds' => 180,
        ]);
        $teaId = $teaResponse->json('id');

        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'waterTempCelsius' => 85,
            ]);
    }

    /**
     * Test creating a brew with missing required fields returns 422.
     */
    public function test_store_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/brews', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['teapotId', 'teaId']);
    }

    /**
     * Test creating a brew with invalid UUID returns 422.
     */
    public function test_store_fails_with_invalid_uuid(): void
    {
        $response = $this->postJson('/api/brews', [
            'teapotId' => 'not-a-uuid',
            'teaId' => 'also-not-a-uuid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['teapotId', 'teaId']);
    }

    /**
     * Test creating a brew with non-existent teapot returns 404.
     */
    public function test_store_fails_with_nonexistent_teapot(): void
    {
        $teaId = $this->createTea();

        $response = $this->postJson('/api/brews', [
            'teapotId' => '00000000-0000-0000-0000-000000000000',
            'teaId' => $teaId,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ]);
    }

    /**
     * Test creating a brew with non-existent tea returns 404.
     */
    public function test_store_fails_with_nonexistent_tea(): void
    {
        $teapotId = $this->createTeapot();

        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => '00000000-0000-0000-0000-000000000000',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ]);
    }

    /**
     * Test creating a brew with water temp out of range returns 422.
     */
    public function test_store_fails_with_water_temp_out_of_range(): void
    {
        $teapotId = $this->createTeapot();
        $teaId = $this->createTea();

        // Too low
        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
            'waterTempCelsius' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['waterTempCelsius']);

        // Too high
        $response = $this->postJson('/api/brews', [
            'teapotId' => $teapotId,
            'teaId' => $teaId,
            'waterTempCelsius' => 110,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['waterTempCelsius']);
    }

    /**
     * Test retrieving a brew by ID returns 200.
     */
    public function test_show_returns_brew_by_id(): void
    {
        $ids = $this->createBrew();

        $response = $this->getJson("/api/brews/{$ids['brewId']}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $ids['brewId'],
                'teapotId' => $ids['teapotId'],
                'teaId' => $ids['teaId'],
                'status' => 'preparing',
            ]);
    }

    /**
     * Test retrieving a non-existent brew returns 404.
     */
    public function test_show_returns_404_for_nonexistent_brew(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/brews/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ]);
    }

    /**
     * Test partial update with PATCH returns 200.
     */
    public function test_patch_partially_updates_brew(): void
    {
        $ids = $this->createBrew();

        $response = $this->patchJson("/api/brews/{$ids['brewId']}", [
            'status' => 'steeping',
            'notes' => 'Updated notes',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $ids['brewId'],
                'status' => 'steeping',
                'notes' => 'Updated notes',
            ]);
    }

    /**
     * Test PATCH with invalid status returns 422.
     */
    public function test_patch_fails_with_invalid_status(): void
    {
        $ids = $this->createBrew();

        $response = $this->patchJson("/api/brews/{$ids['brewId']}", [
            'status' => 'invalid-status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test PATCH can set completedAt.
     */
    public function test_patch_can_set_completed_at(): void
    {
        $ids = $this->createBrew();
        $completedAt = '2024-01-15T10:30:00+00:00';

        $response = $this->patchJson("/api/brews/{$ids['brewId']}", [
            'status' => 'ready',
            'completedAt' => $completedAt,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ready',
            ]);

        // Verify completedAt is set (format may vary slightly)
        $this->assertNotNull($response->json('completedAt'));
    }

    /**
     * Test deleting a brew returns 204.
     */
    public function test_destroy_deletes_brew(): void
    {
        $ids = $this->createBrew();

        $response = $this->deleteJson("/api/brews/{$ids['brewId']}");

        $response->assertStatus(204);

        // Verify it's gone
        $this->getJson("/api/brews/{$ids['brewId']}")
            ->assertStatus(404);
    }

    /**
     * Test deleting a non-existent brew returns 404.
     */
    public function test_destroy_returns_404_for_nonexistent_brew(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->deleteJson("/api/brews/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    /**
     * Test listing brews by teapot.
     */
    public function test_index_by_teapot_returns_brews(): void
    {
        $ids = $this->createBrew();

        $response = $this->getJson("/api/teapots/{$ids['teapotId']}/brews");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'teapotId',
                        'teaId',
                        'status',
                    ],
                ],
                'pagination',
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test listing brews by non-existent teapot returns 404.
     */
    public function test_index_by_teapot_returns_404_for_nonexistent_teapot(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/teapots/{$fakeId}/brews");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ]);
    }

    /**
     * Test listing brews with status filter.
     */
    public function test_index_with_status_filter(): void
    {
        $ids = $this->createBrew();

        // Update to steeping status
        $this->patchJson("/api/brews/{$ids['brewId']}", [
            'status' => 'steeping',
        ]);

        // Create another brew (will be 'preparing')
        $this->createBrew();

        $response = $this->getJson('/api/brews?status=steeping');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $brew) {
            $this->assertEquals('steeping', $brew['status']);
        }
    }

    /**
     * Test all valid brew status values.
     */
    public function test_patch_accepts_all_valid_statuses(): void
    {
        $statuses = ['preparing', 'steeping', 'ready', 'served', 'cold'];

        foreach ($statuses as $status) {
            $ids = $this->createBrew();

            $response = $this->patchJson("/api/brews/{$ids['brewId']}", [
                'status' => $status,
            ]);

            $response->assertStatus(200)
                ->assertJson(['status' => $status]);
        }
    }

    // ========================================
    // Steep Tests
    // ========================================

    /**
     * Test listing steeps for a brew returns empty list initially.
     */
    public function test_index_steeps_returns_empty_list_initially(): void
    {
        $ids = $this->createBrew();

        $response = $this->getJson("/api/brews/{$ids['brewId']}/steeps");

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
                    'total' => 0,
                ],
            ]);
    }

    /**
     * Test creating a steep returns 201.
     */
    public function test_store_steep_creates_steep_with_valid_data(): void
    {
        $ids = $this->createBrew();

        $payload = [
            'durationSeconds' => 60,
            'rating' => 4,
            'notes' => 'Smooth and aromatic',
        ];

        $response = $this->postJson("/api/brews/{$ids['brewId']}/steeps", $payload);

        $response->assertStatus(201)
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
                'brewId' => $ids['brewId'],
                'steepNumber' => 1,
                'durationSeconds' => 60,
                'rating' => 4,
                'notes' => 'Smooth and aromatic',
            ]);
    }

    /**
     * Test steep numbers increment correctly.
     */
    public function test_steep_numbers_increment(): void
    {
        $ids = $this->createBrew();

        // First steep
        $response1 = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
            'durationSeconds' => 30,
        ]);
        $response1->assertStatus(201)
            ->assertJson(['steepNumber' => 1]);

        // Second steep
        $response2 = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
            'durationSeconds' => 45,
        ]);
        $response2->assertStatus(201)
            ->assertJson(['steepNumber' => 2]);

        // Third steep
        $response3 = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
            'durationSeconds' => 60,
        ]);
        $response3->assertStatus(201)
            ->assertJson(['steepNumber' => 3]);
    }

    /**
     * Test creating a steep with missing required fields returns 422.
     */
    public function test_store_steep_fails_without_duration(): void
    {
        $ids = $this->createBrew();

        $response = $this->postJson("/api/brews/{$ids['brewId']}/steeps", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['durationSeconds']);
    }

    /**
     * Test creating a steep with invalid duration returns 422.
     */
    public function test_store_steep_fails_with_invalid_duration(): void
    {
        $ids = $this->createBrew();

        $response = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
            'durationSeconds' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['durationSeconds']);
    }

    /**
     * Test creating a steep with rating out of range returns 422.
     */
    public function test_store_steep_fails_with_rating_out_of_range(): void
    {
        $ids = $this->createBrew();

        // Too low
        $response = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
            'durationSeconds' => 60,
            'rating' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        // Too high
        $response = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
            'durationSeconds' => 60,
            'rating' => 6,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    /**
     * Test creating a steep for non-existent brew returns 404.
     */
    public function test_store_steep_returns_404_for_nonexistent_brew(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->postJson("/api/brews/{$fakeId}/steeps", [
            'durationSeconds' => 60,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ]);
    }

    /**
     * Test listing steeps for non-existent brew returns 404.
     */
    public function test_index_steeps_returns_404_for_nonexistent_brew(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/brews/{$fakeId}/steeps");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ]);
    }

    /**
     * Test all valid rating values.
     */
    public function test_store_steep_accepts_all_valid_ratings(): void
    {
        $ids = $this->createBrew();

        for ($rating = 1; $rating <= 5; $rating++) {
            $response = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
                'durationSeconds' => 60,
                'rating' => $rating,
            ]);

            $response->assertStatus(201)
                ->assertJson(['rating' => $rating]);
        }
    }

    /**
     * Test steep with null rating (optional field).
     */
    public function test_store_steep_allows_null_rating(): void
    {
        $ids = $this->createBrew();

        $response = $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
            'durationSeconds' => 60,
            'rating' => null,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'rating' => null,
            ]);
    }

    /**
     * Test listing steeps with pagination.
     */
    public function test_index_steeps_with_pagination(): void
    {
        $ids = $this->createBrew();

        // Create 5 steeps
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson("/api/brews/{$ids['brewId']}/steeps", [
                'durationSeconds' => 30 + ($i * 10),
            ]);
        }

        $response = $this->getJson("/api/brews/{$ids['brewId']}/steeps?page=1&limit=2");

        $response->assertStatus(200)
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.limit', 2)
            ->assertJsonPath('pagination.total', 5)
            ->assertJsonPath('pagination.totalPages', 3);

        $this->assertCount(2, $response->json('data'));
    }
}
