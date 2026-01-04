<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use App\Http\Requests\Teapot\PatchTeapotRequest;
use App\Http\Requests\Teapot\StoreTeapotRequest;
use App\Http\Requests\Teapot\UpdateTeapotRequest;
use App\Models\Teapot;
use App\Services\MemoryStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TeapotController extends Controller
{
    public function __construct(
        private readonly MemoryStore $store
    ) {}

    /**
     * List all teapots.
     *
     * GET /api/teapots
     *
     * Query Parameters:
     * - page: Page number (default: 1, min: 1)
     * - limit: Items per page (default: 20, min: 1, max: 100)
     * - material: Filter by material (enum: TeapotMaterial)
     * - style: Filter by style (enum: TeapotStyle)
     *
     * @return JsonResponse 200: Paginated teapot list
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 20);
        $material = $request->query('material');
        $style = $request->query('style');

        $page = max(1, $page);
        $limit = min(100, max(1, $limit));

        $teapots = $this->store->listTeapots(
            page: $page,
            limit: $limit,
            material: $material ? TeapotMaterial::tryFrom($material) : null,
            style: $style ? TeapotStyle::tryFrom($style) : null,
        );

        $total = $this->store->countTeapots(
            material: $material ? TeapotMaterial::tryFrom($material) : null,
            style: $style ? TeapotStyle::tryFrom($style) : null,
        );

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

        return response()->json([
            'data' => array_map(fn (Teapot $t) => $t->toArray(), $teapots),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ]);
    }

    /**
     * Create a new teapot.
     *
     * POST /api/teapots
     *
     * Request Body: StoreTeapotRequest
     *
     * @return JsonResponse 201: Created teapot
     * @return JsonResponse 422: Validation error
     */
    public function store(StoreTeapotRequest $request): JsonResponse
    {
        $data = $request->validatedWithDefaults();

        $now = Carbon::now();
        $teapot = new Teapot(
            id: (string) Str::uuid(),
            name: $data['name'],
            material: TeapotMaterial::from($data['material']),
            capacity_ml: $data['capacityMl'],
            style: TeapotStyle::from($data['style']),
            description: $data['description'] ?? null,
            created_at: $now,
            updated_at: $now,
        );

        $this->store->createTeapot($teapot);

        return response()->json($teapot->toArray(), 201);
    }

    /**
     * Get a teapot by ID.
     *
     * GET /api/teapots/{id}
     *
     * Path Parameters:
     * - id: Teapot UUID (required)
     *
     * @return JsonResponse 200: Teapot
     * @return JsonResponse 404: Not found
     */
    public function show(string $id): JsonResponse
    {
        $teapot = $this->store->getTeapot($id);

        if ($teapot === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ], 404);
        }

        return response()->json($teapot->toArray());
    }

    /**
     * Update a teapot (full replacement).
     *
     * PUT /api/teapots/{id}
     *
     * Path Parameters:
     * - id: Teapot UUID (required)
     *
     * Request Body: UpdateTeapotRequest
     *
     * @return JsonResponse 200: Updated teapot
     * @return JsonResponse 404: Not found
     * @return JsonResponse 422: Validation error
     */
    public function update(UpdateTeapotRequest $request, string $id): JsonResponse
    {
        $existing = $this->store->getTeapot($id);

        if ($existing === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ], 404);
        }

        $data = $request->validated();

        $teapot = new Teapot(
            id: $id,
            name: $data['name'],
            material: TeapotMaterial::from($data['material']),
            capacity_ml: $data['capacityMl'],
            style: TeapotStyle::from($data['style']),
            description: $data['description'] ?? null,
            created_at: $existing->created_at,
            updated_at: Carbon::now(),
        );

        $this->store->updateTeapot($teapot);

        return response()->json($teapot->toArray());
    }

    /**
     * Partially update a teapot.
     *
     * PATCH /api/teapots/{id}
     *
     * Path Parameters:
     * - id: Teapot UUID (required)
     *
     * Request Body: PatchTeapotRequest
     *
     * @return JsonResponse 200: Updated teapot
     * @return JsonResponse 404: Not found
     * @return JsonResponse 422: Validation error
     */
    public function patch(PatchTeapotRequest $request, string $id): JsonResponse
    {
        $existing = $this->store->getTeapot($id);

        if ($existing === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ], 404);
        }

        $data = $request->validated();

        $teapot = new Teapot(
            id: $id,
            name: $data['name'] ?? $existing->name,
            material: isset($data['material'])
                ? TeapotMaterial::from($data['material'])
                : $existing->material,
            capacity_ml: $data['capacityMl'] ?? $existing->capacity_ml,
            style: isset($data['style'])
                ? TeapotStyle::from($data['style'])
                : $existing->style,
            description: array_key_exists('description', $data)
                ? $data['description']
                : $existing->description,
            created_at: $existing->created_at,
            updated_at: Carbon::now(),
        );

        $this->store->updateTeapot($teapot);

        return response()->json($teapot->toArray());
    }

    /**
     * Delete a teapot.
     *
     * DELETE /api/teapots/{id}
     *
     * Path Parameters:
     * - id: Teapot UUID (required)
     *
     * @return Response 204: No content
     * @return JsonResponse 404: Not found
     */
    public function destroy(string $id): Response|JsonResponse
    {
        if (!$this->store->deleteTeapot($id)) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ], 404);
        }

        return response()->noContent();
    }
}
