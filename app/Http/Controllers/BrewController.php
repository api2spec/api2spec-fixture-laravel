<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BrewStatus;
use App\Http\Requests\Brew\PatchBrewRequest;
use App\Http\Requests\Brew\StoreBrewRequest;
use App\Http\Requests\Steep\StoreSteepRequest;
use App\Models\Brew;
use App\Models\Steep;
use App\Services\MemoryStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BrewController extends Controller
{
    public function __construct(
        private readonly MemoryStore $store
    ) {}

    /**
     * List all brews.
     *
     * GET /api/brews
     *
     * Query Parameters:
     * - page: Page number (default: 1, min: 1)
     * - limit: Items per page (default: 20, min: 1, max: 100)
     * - status: Filter by status (enum: BrewStatus)
     * - teapotId: Filter by teapot ID
     * - teaId: Filter by tea ID
     *
     * @return JsonResponse 200: Paginated brew list
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 20);
        $status = $request->query('status');
        $teapotId = $request->query('teapotId');
        $teaId = $request->query('teaId');

        $page = max(1, $page);
        $limit = min(100, max(1, $limit));

        $brews = $this->store->listBrews(
            page: $page,
            limit: $limit,
            status: $status ? BrewStatus::tryFrom($status) : null,
            teapotId: $teapotId,
            teaId: $teaId,
        );

        $total = $this->store->countBrews(
            status: $status ? BrewStatus::tryFrom($status) : null,
            teapotId: $teapotId,
            teaId: $teaId,
        );

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

        return response()->json([
            'data' => array_map(fn (Brew $b) => $b->toArray(), $brews),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ]);
    }

    /**
     * List brews for a specific teapot.
     *
     * GET /api/teapots/{teapotId}/brews
     *
     * Path Parameters:
     * - teapotId: Teapot UUID (required)
     *
     * Query Parameters:
     * - page: Page number (default: 1, min: 1)
     * - limit: Items per page (default: 20, min: 1, max: 100)
     *
     * @return JsonResponse 200: Paginated brew list
     * @return JsonResponse 404: Teapot not found
     */
    public function indexByTeapot(Request $request, string $teapotId): JsonResponse
    {
        $teapot = $this->store->getTeapot($teapotId);

        if ($teapot === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ], 404);
        }

        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 20);

        $page = max(1, $page);
        $limit = min(100, max(1, $limit));

        $brews = $this->store->listBrewsByTeapot($teapotId, $page, $limit);
        $total = $this->store->countBrewsByTeapot($teapotId);

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

        return response()->json([
            'data' => array_map(fn (Brew $b) => $b->toArray(), $brews),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ]);
    }

    /**
     * Create a new brew.
     *
     * POST /api/brews
     *
     * Request Body: StoreBrewRequest
     *
     * @return JsonResponse 201: Created brew
     * @return JsonResponse 422: Validation error
     */
    public function store(StoreBrewRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Verify teapot exists
        $teapot = $this->store->getTeapot($data['teapotId']);
        if ($teapot === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Teapot not found',
            ], 404);
        }

        // Verify tea exists
        $tea = $this->store->getTea($data['teaId']);
        if ($tea === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Tea not found',
            ], 404);
        }

        $now = Carbon::now();
        $brew = new Brew(
            id: (string) Str::uuid(),
            teapot_id: $data['teapotId'],
            tea_id: $data['teaId'],
            status: BrewStatus::Preparing,
            water_temp_celsius: $data['waterTempCelsius'] ?? $tea->steep_temp_celsius,
            notes: $data['notes'] ?? null,
            started_at: $now,
            completed_at: null,
            created_at: $now,
            updated_at: $now,
        );

        $this->store->createBrew($brew);

        return response()->json($brew->toArray(), 201);
    }

    /**
     * Get a brew by ID.
     *
     * GET /api/brews/{id}
     *
     * Path Parameters:
     * - id: Brew UUID (required)
     *
     * @return JsonResponse 200: Brew
     * @return JsonResponse 404: Not found
     */
    public function show(string $id): JsonResponse
    {
        $brew = $this->store->getBrew($id);

        if ($brew === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ], 404);
        }

        return response()->json($brew->toArray());
    }

    /**
     * Partially update a brew.
     *
     * PATCH /api/brews/{id}
     *
     * Path Parameters:
     * - id: Brew UUID (required)
     *
     * Request Body: PatchBrewRequest
     *
     * @return JsonResponse 200: Updated brew
     * @return JsonResponse 404: Not found
     * @return JsonResponse 422: Validation error
     */
    public function patch(PatchBrewRequest $request, string $id): JsonResponse
    {
        $existing = $this->store->getBrew($id);

        if ($existing === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ], 404);
        }

        $data = $request->validated();

        $completedAt = $existing->completed_at;
        if (array_key_exists('completedAt', $data)) {
            $completedAt = $data['completedAt'] !== null
                ? Carbon::parse($data['completedAt'])
                : null;
        }

        $brew = new Brew(
            id: $id,
            teapot_id: $existing->teapot_id,
            tea_id: $existing->tea_id,
            status: isset($data['status'])
                ? BrewStatus::from($data['status'])
                : $existing->status,
            water_temp_celsius: $existing->water_temp_celsius,
            notes: array_key_exists('notes', $data)
                ? $data['notes']
                : $existing->notes,
            started_at: $existing->started_at,
            completed_at: $completedAt,
            created_at: $existing->created_at,
            updated_at: Carbon::now(),
        );

        $this->store->updateBrew($brew);

        return response()->json($brew->toArray());
    }

    /**
     * Delete a brew.
     *
     * DELETE /api/brews/{id}
     *
     * Path Parameters:
     * - id: Brew UUID (required)
     *
     * @return Response 204: No content
     * @return JsonResponse 404: Not found
     */
    public function destroy(string $id): Response|JsonResponse
    {
        if (!$this->store->deleteBrew($id)) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ], 404);
        }

        return response()->noContent();
    }

    /**
     * List steeps for a brew.
     *
     * GET /api/brews/{brewId}/steeps
     *
     * Path Parameters:
     * - brewId: Brew UUID (required)
     *
     * Query Parameters:
     * - page: Page number (default: 1, min: 1)
     * - limit: Items per page (default: 20, min: 1, max: 100)
     *
     * @return JsonResponse 200: Paginated steep list
     * @return JsonResponse 404: Brew not found
     */
    public function indexSteeps(Request $request, string $brewId): JsonResponse
    {
        $brew = $this->store->getBrew($brewId);

        if ($brew === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ], 404);
        }

        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 20);

        $page = max(1, $page);
        $limit = min(100, max(1, $limit));

        $steeps = $this->store->listSteepsByBrew($brewId, $page, $limit);
        $total = $this->store->countSteepsByBrew($brewId);

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

        return response()->json([
            'data' => array_map(fn (Steep $s) => $s->toArray(), $steeps),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ]);
    }

    /**
     * Add a steep to a brew.
     *
     * POST /api/brews/{brewId}/steeps
     *
     * Path Parameters:
     * - brewId: Brew UUID (required)
     *
     * Request Body: StoreSteepRequest
     *
     * @return JsonResponse 201: Created steep
     * @return JsonResponse 404: Brew not found
     * @return JsonResponse 422: Validation error
     */
    public function storeSteep(StoreSteepRequest $request, string $brewId): JsonResponse
    {
        $brew = $this->store->getBrew($brewId);

        if ($brew === null) {
            return response()->json([
                'code' => 'NOT_FOUND',
                'message' => 'Brew not found',
            ], 404);
        }

        $data = $request->validated();

        $steep = new Steep(
            id: (string) Str::uuid(),
            brew_id: $brewId,
            steep_number: $this->store->getNextSteepNumber($brewId),
            duration_seconds: $data['durationSeconds'],
            rating: $data['rating'] ?? null,
            notes: $data['notes'] ?? null,
            created_at: Carbon::now(),
        );

        $this->store->createSteep($steep);

        return response()->json($steep->toArray(), 201);
    }
}
