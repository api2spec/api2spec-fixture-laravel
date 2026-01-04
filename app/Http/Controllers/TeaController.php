<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use App\Http\Requests\Tea\PatchTeaRequest;
use App\Http\Requests\Tea\StoreTeaRequest;
use App\Http\Requests\Tea\UpdateTeaRequest;
use App\Models\Tea;
use App\Services\MemoryStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TeaController extends Controller
{
    public function __construct(
        private readonly MemoryStore $store
    ) {}

    /**
     * List all teas.
     *
     * GET /api/teas
     *
     * Query Parameters:
     * - page: Page number (default: 1, min: 1)
     * - limit: Items per page (default: 20, min: 1, max: 100)
     * - type: Filter by type (enum: TeaType)
     * - caffeineLevel: Filter by caffeine level (enum: CaffeineLevel)
     *
     * @return JsonResponse 200: Paginated tea list
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 20);
        $type = $request->query('type');
        $caffeineLevel = $request->query('caffeineLevel');

        $page = max(1, $page);
        $limit = min(100, max(1, $limit));

        $teas = $this->store->listTeas(
            page: $page,
            limit: $limit,
            type: $type ? TeaType::tryFrom($type) : null,
            caffeineLevel: $caffeineLevel ? CaffeineLevel::tryFrom($caffeineLevel) : null,
        );

        $total = $this->store->countTeas(
            type: $type ? TeaType::tryFrom($type) : null,
            caffeineLevel: $caffeineLevel ? CaffeineLevel::tryFrom($caffeineLevel) : null,
        );

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

        return response()->json([
            'data' => array_map(fn (Tea $t) => $t->toArray(), $teas),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ]);
    }

    /**
     * Create a new tea.
     *
     * POST /api/teas
     *
     * Request Body: StoreTeaRequest
     *
     * @return JsonResponse 201: Created tea
     * @return JsonResponse 422: Validation error
     */
    public function store(StoreTeaRequest $request): JsonResponse
    {
        $data = $request->validatedWithDefaults();

        $now = Carbon::now();
        $tea = new Tea(
            id: (string) Str::uuid(),
            name: $data['name'],
            type: TeaType::from($data['type']),
            origin: $data['origin'] ?? null,
            caffeine_level: CaffeineLevel::from($data['caffeineLevel']),
            steep_temp_celsius: $data['steepTempCelsius'],
            steep_time_seconds: $data['steepTimeSeconds'],
            description: $data['description'] ?? null,
            created_at: $now,
            updated_at: $now,
        );

        $this->store->createTea($tea);

        return response()->json($tea->toArray(), 201);
    }

    /**
     * Get a tea by ID.
     *
     * GET /api/teas/{id}
     *
     * Path Parameters:
     * - id: Tea UUID (required)
     *
     * @return JsonResponse 200: Tea
     * @return JsonResponse 404: Not found
     */
    public function show(string $id): JsonResponse
    {
        $tea = $this->store->getTea($id);

        if ($tea === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ], 404);
        }

        return response()->json($tea->toArray());
    }

    /**
     * Update a tea (full replacement).
     *
     * PUT /api/teas/{id}
     *
     * Path Parameters:
     * - id: Tea UUID (required)
     *
     * Request Body: UpdateTeaRequest
     *
     * @return JsonResponse 200: Updated tea
     * @return JsonResponse 404: Not found
     * @return JsonResponse 422: Validation error
     */
    public function update(UpdateTeaRequest $request, string $id): JsonResponse
    {
        $existing = $this->store->getTea($id);

        if ($existing === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ], 404);
        }

        $data = $request->validated();

        $tea = new Tea(
            id: $id,
            name: $data['name'],
            type: TeaType::from($data['type']),
            origin: $data['origin'] ?? null,
            caffeine_level: CaffeineLevel::from($data['caffeineLevel']),
            steep_temp_celsius: $data['steepTempCelsius'],
            steep_time_seconds: $data['steepTimeSeconds'],
            description: $data['description'] ?? null,
            created_at: $existing->created_at,
            updated_at: Carbon::now(),
        );

        $this->store->updateTea($tea);

        return response()->json($tea->toArray());
    }

    /**
     * Partially update a tea.
     *
     * PATCH /api/teas/{id}
     *
     * Path Parameters:
     * - id: Tea UUID (required)
     *
     * Request Body: PatchTeaRequest
     *
     * @return JsonResponse 200: Updated tea
     * @return JsonResponse 404: Not found
     * @return JsonResponse 422: Validation error
     */
    public function patch(PatchTeaRequest $request, string $id): JsonResponse
    {
        $existing = $this->store->getTea($id);

        if ($existing === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ], 404);
        }

        $data = $request->validated();

        $tea = new Tea(
            id: $id,
            name: $data['name'] ?? $existing->name,
            type: isset($data['type'])
                ? TeaType::from($data['type'])
                : $existing->type,
            origin: array_key_exists('origin', $data)
                ? $data['origin']
                : $existing->origin,
            caffeine_level: isset($data['caffeineLevel'])
                ? CaffeineLevel::from($data['caffeineLevel'])
                : $existing->caffeine_level,
            steep_temp_celsius: $data['steepTempCelsius'] ?? $existing->steep_temp_celsius,
            steep_time_seconds: $data['steepTimeSeconds'] ?? $existing->steep_time_seconds,
            description: array_key_exists('description', $data)
                ? $data['description']
                : $existing->description,
            created_at: $existing->created_at,
            updated_at: Carbon::now(),
        );

        $this->store->updateTea($tea);

        return response()->json($tea->toArray());
    }

    /**
     * Delete a tea.
     *
     * DELETE /api/teas/{id}
     *
     * Path Parameters:
     * - id: Tea UUID (required)
     *
     * @return Response 204: No content
     * @return JsonResponse 404: Not found
     */
    public function destroy(string $id): Response|JsonResponse
    {
        if (!$this->store->deleteTea($id)) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ], 404);
        }

        return response()->noContent();
    }
}
