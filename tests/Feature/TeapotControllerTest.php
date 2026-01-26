<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TeapotControllerTest extends TestCase
{
    /**
     * Test listing teapots returns empty list initially.
     */
    public function test_index_returns_empty_list_initially(): void
    {
        $response = $this->getJson('/api/teapots');

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
     * Test creating a teapot with valid data returns 201.
     */
    public function test_store_creates_teapot_with_valid_data(): void
    {
        $payload = [
            'name' => 'Classic Ceramic Teapot',
            'material' => 'ceramic',
            'capacityMl' => 500,
            'style' => 'english',
            'description' => 'A beautiful ceramic teapot',
        ];

        $response = $this->postJson('/api/teapots', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'material',
                'capacityMl',
                'style',
                'description',
                'createdAt',
                'updatedAt',
            ])
            ->assertJson([
                'name' => 'Classic Ceramic Teapot',
                'material' => 'ceramic',
                'capacityMl' => 500,
                'style' => 'english',
                'description' => 'A beautiful ceramic teapot',
            ]);
    }

    /**
     * Test creating a teapot uses default style when not provided.
     */
    public function test_store_uses_default_style(): void
    {
        $payload = [
            'name' => 'Simple Teapot',
            'material' => 'glass',
            'capacityMl' => 300,
        ];

        $response = $this->postJson('/api/teapots', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'style' => 'english',
            ]);
    }

    /**
     * Test creating a teapot with missing required fields returns 422.
     */
    public function test_store_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/teapots', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'material', 'capacityMl']);
    }

    /**
     * Test creating a teapot with invalid material returns 422.
     */
    public function test_store_fails_with_invalid_material(): void
    {
        $payload = [
            'name' => 'Invalid Teapot',
            'material' => 'plastic',
            'capacityMl' => 500,
        ];

        $response = $this->postJson('/api/teapots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['material']);
    }

    /**
     * Test creating a teapot with invalid capacity returns 422.
     */
    public function test_store_fails_with_capacity_out_of_range(): void
    {
        $payload = [
            'name' => 'Giant Teapot',
            'material' => 'ceramic',
            'capacityMl' => 10000,
        ];

        $response = $this->postJson('/api/teapots', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['capacityMl']);
    }

    /**
     * Test retrieving a teapot by ID returns 200.
     */
    public function test_show_returns_teapot_by_id(): void
    {
        // First create a teapot
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Test Teapot',
            'material' => 'porcelain',
            'capacityMl' => 400,
        ]);

        $teapotId = $createResponse->json('id');

        $response = $this->getJson("/api/teapots/{$teapotId}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $teapotId,
                'name' => 'Test Teapot',
                'material' => 'porcelain',
                'capacityMl' => 400,
            ]);
    }

    /**
     * Test retrieving a non-existent teapot returns 404.
     */
    public function test_show_returns_404_for_nonexistent_teapot(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/teapots/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ]);
    }

    /**
     * Test updating a teapot with PUT returns 200.
     */
    public function test_update_replaces_teapot_with_put(): void
    {
        // First create a teapot
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Original Teapot',
            'material' => 'ceramic',
            'capacityMl' => 300,
            'style' => 'english',
            'description' => 'Original description',
        ]);

        $teapotId = $createResponse->json('id');

        // Update with PUT (full replacement)
        $payload = [
            'name' => 'Updated Teapot',
            'material' => 'cast-iron',
            'capacityMl' => 600,
            'style' => 'kyusu',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/teapots/{$teapotId}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $teapotId,
                'name' => 'Updated Teapot',
                'material' => 'cast-iron',
                'capacityMl' => 600,
                'style' => 'kyusu',
                'description' => 'Updated description',
            ]);
    }

    /**
     * Test updating a non-existent teapot returns 404.
     */
    public function test_update_returns_404_for_nonexistent_teapot(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $payload = [
            'name' => 'Updated Teapot',
            'material' => 'ceramic',
            'capacityMl' => 500,
            'style' => 'english',
        ];

        $response = $this->putJson("/api/teapots/{$fakeId}", $payload);

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    /**
     * Test partial update with PATCH returns 200.
     */
    public function test_patch_partially_updates_teapot(): void
    {
        // First create a teapot
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Original Teapot',
            'material' => 'ceramic',
            'capacityMl' => 300,
            'style' => 'english',
            'description' => 'Original description',
        ]);

        $teapotId = $createResponse->json('id');

        // Partial update with PATCH
        $response = $this->patchJson("/api/teapots/{$teapotId}", [
            'name' => 'Patched Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $teapotId,
                'name' => 'Patched Name',
                'material' => 'ceramic',
                'capacityMl' => 300,
                'style' => 'english',
                'description' => 'Original description',
            ]);
    }

    /**
     * Test PATCH with invalid data returns 422.
     */
    public function test_patch_fails_with_invalid_data(): void
    {
        // First create a teapot
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Test Teapot',
            'material' => 'ceramic',
            'capacityMl' => 300,
        ]);

        $teapotId = $createResponse->json('id');

        $response = $this->patchJson("/api/teapots/{$teapotId}", [
            'material' => 'invalid-material',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['material']);
    }

    /**
     * Test deleting a teapot returns 204.
     */
    public function test_destroy_deletes_teapot(): void
    {
        // First create a teapot
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Teapot To Delete',
            'material' => 'glass',
            'capacityMl' => 250,
        ]);

        $teapotId = $createResponse->json('id');

        // Delete it
        $response = $this->deleteJson("/api/teapots/{$teapotId}");

        $response->assertStatus(204);

        // Verify it's gone
        $this->getJson("/api/teapots/{$teapotId}")
            ->assertStatus(404);
    }

    /**
     * Test deleting a non-existent teapot returns 404.
     */
    public function test_destroy_returns_404_for_nonexistent_teapot(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->deleteJson("/api/teapots/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    /**
     * Test listing teapots with pagination.
     */
    public function test_index_with_pagination(): void
    {
        // Create multiple teapots
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/teapots', [
                'name' => "Teapot {$i}",
                'material' => 'ceramic',
                'capacityMl' => 300 + ($i * 50),
            ]);
        }

        // Get first page with limit of 2
        $response = $this->getJson('/api/teapots?page=1&limit=2');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.limit', 2)
            ->assertJsonPath('pagination.total', 5)
            ->assertJsonPath('pagination.totalPages', 3);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test listing teapots with material filter.
     */
    public function test_index_with_material_filter(): void
    {
        // Create teapots with different materials
        $this->postJson('/api/teapots', [
            'name' => 'Ceramic Teapot',
            'material' => 'ceramic',
            'capacityMl' => 300,
        ]);

        $this->postJson('/api/teapots', [
            'name' => 'Glass Teapot',
            'material' => 'glass',
            'capacityMl' => 400,
        ]);

        $response = $this->getJson('/api/teapots?material=ceramic');

        $response->assertStatus(200);

        // All returned teapots should be ceramic
        $data = $response->json('data');
        foreach ($data as $teapot) {
            $this->assertEquals('ceramic', $teapot['material']);
        }
    }

    /**
     * Test listing teapots with style filter.
     */
    public function test_index_with_style_filter(): void
    {
        // Create teapots with different styles
        $this->postJson('/api/teapots', [
            'name' => 'English Teapot',
            'material' => 'porcelain',
            'capacityMl' => 500,
            'style' => 'english',
        ]);

        $this->postJson('/api/teapots', [
            'name' => 'Japanese Teapot',
            'material' => 'clay',
            'capacityMl' => 200,
            'style' => 'kyusu',
        ]);

        $response = $this->getJson('/api/teapots?style=kyusu');

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $teapot) {
            $this->assertEquals('kyusu', $teapot['style']);
        }
    }

    /**
     * Test all valid material values.
     */
    public function test_store_accepts_all_valid_materials(): void
    {
        $materials = ['ceramic', 'cast-iron', 'glass', 'porcelain', 'clay', 'stainless-steel'];

        foreach ($materials as $material) {
            $response = $this->postJson('/api/teapots', [
                'name' => "Teapot - {$material}",
                'material' => $material,
                'capacityMl' => 300,
            ]);

            $response->assertStatus(201)
                ->assertJson(['material' => $material]);
        }
    }

    /**
     * Test all valid style values.
     */
    public function test_store_accepts_all_valid_styles(): void
    {
        $styles = ['kyusu', 'gaiwan', 'english', 'moroccan', 'turkish', 'yixing'];

        foreach ($styles as $style) {
            $response = $this->postJson('/api/teapots', [
                'name' => "Teapot - {$style}",
                'material' => 'ceramic',
                'capacityMl' => 300,
                'style' => $style,
            ]);

            $response->assertStatus(201)
                ->assertJson(['style' => $style]);
        }
    }
}
