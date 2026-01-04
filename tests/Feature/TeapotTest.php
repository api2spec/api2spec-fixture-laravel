<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TeapotTest extends TestCase
{
    public function test_can_list_teapots(): void
    {
        $response = $this->getJson('/api/teapots');

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

    public function test_can_create_teapot(): void
    {
        $response = $this->postJson('/api/teapots', [
            'name' => 'My Kyusu',
            'material' => 'clay',
            'capacityMl' => 350,
            'style' => 'kyusu',
        ]);

        $response
            ->assertStatus(201)
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
                'name' => 'My Kyusu',
                'material' => 'clay',
                'capacityMl' => 350,
                'style' => 'kyusu',
            ]);
    }

    public function test_create_teapot_uses_default_style(): void
    {
        $response = $this->postJson('/api/teapots', [
            'name' => 'My English Pot',
            'material' => 'ceramic',
            'capacityMl' => 1000,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'style' => 'english',
            ]);
    }

    public function test_create_teapot_validation_error(): void
    {
        $response = $this->postJson('/api/teapots', [
            'name' => '',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'material', 'capacityMl']);
    }

    public function test_create_teapot_invalid_material(): void
    {
        $response = $this->postJson('/api/teapots', [
            'name' => 'Test',
            'material' => 'invalid-material',
            'capacityMl' => 500,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['material']);
    }

    public function test_can_get_teapot(): void
    {
        // Create a teapot first
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Test Teapot',
            'material' => 'glass',
            'capacityMl' => 500,
        ]);

        $id = $createResponse->json('id');

        $response = $this->getJson("/api/teapots/{$id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'name' => 'Test Teapot',
                'material' => 'glass',
            ]);
    }

    public function test_get_teapot_not_found(): void
    {
        $response = $this->getJson('/api/teapots/non-existent-id');

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }

    public function test_can_update_teapot(): void
    {
        // Create a teapot first
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Original Name',
            'material' => 'ceramic',
            'capacityMl' => 500,
        ]);

        $id = $createResponse->json('id');

        $response = $this->putJson("/api/teapots/{$id}", [
            'name' => 'Updated Name',
            'material' => 'porcelain',
            'capacityMl' => 600,
            'style' => 'english',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'name' => 'Updated Name',
                'material' => 'porcelain',
                'capacityMl' => 600,
            ]);
    }

    public function test_can_patch_teapot(): void
    {
        // Create a teapot first
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'Original Name',
            'material' => 'ceramic',
            'capacityMl' => 500,
        ]);

        $id = $createResponse->json('id');

        $response = $this->patchJson("/api/teapots/{$id}", [
            'name' => 'Patched Name',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $id,
                'name' => 'Patched Name',
                'material' => 'ceramic', // Should remain unchanged
                'capacityMl' => 500, // Should remain unchanged
            ]);
    }

    public function test_can_delete_teapot(): void
    {
        // Create a teapot first
        $createResponse = $this->postJson('/api/teapots', [
            'name' => 'To Delete',
            'material' => 'ceramic',
            'capacityMl' => 500,
        ]);

        $id = $createResponse->json('id');

        $response = $this->deleteJson("/api/teapots/{$id}");

        $response->assertStatus(204);

        // Verify it's deleted
        $this->getJson("/api/teapots/{$id}")->assertStatus(404);
    }

    public function test_delete_teapot_not_found(): void
    {
        $response = $this->deleteJson('/api/teapots/non-existent-id');

        $response
            ->assertStatus(404)
            ->assertJson([
                'code' => 'NOT_FOUND',
            ]);
    }
}
