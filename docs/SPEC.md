# Laravel Fixture Specification

**Repository:** `api2spec-fixture-laravel`  
**GitHub:** `github.com/api2spec/api2spec-fixture-laravel`  
**Purpose:** Target fixture (no native OpenAPI generation)

---

## Quick Reference

| Property | Value |
|----------|-------|
| Language | PHP 8.3+ |
| Framework | Laravel 11.x |
| Schema Library | Form Requests + Eloquent |
| Package Manager | Composer |
| Test Runner | PHPUnit / Pest |

---

## Project Setup

### Initialize

```bash
composer create-project laravel/laravel api2spec-fixture-laravel
cd api2spec-fixture-laravel
```

### composer.json (relevant sections)

```json
{
    "name": "api2spec/api2spec-fixture-laravel",
    "description": "Laravel fixture API for api2spec. TIF-compliant.",
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "pestphp/pest": "^2.0",
        "laravel/pint": "^1.0"
    }
}
```

---

## Directory Structure

```
api2spec-fixture-laravel/
├── docs/
│   └── SPEC.md                      # This file
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TeapotController.php
│   │   │   ├── TeaController.php
│   │   │   ├── BrewController.php
│   │   │   └── HealthController.php
│   │   ├── Requests/
│   │   │   ├── Teapot/
│   │   │   │   ├── StoreTeapotRequest.php
│   │   │   │   ├── UpdateTeapotRequest.php
│   │   │   │   └── PatchTeapotRequest.php
│   │   │   ├── Tea/
│   │   │   │   ├── StoreTeaRequest.php
│   │   │   │   ├── UpdateTeaRequest.php
│   │   │   │   └── PatchTeaRequest.php
│   │   │   ├── Brew/
│   │   │   │   ├── StoreBrewRequest.php
│   │   │   │   └── PatchBrewRequest.php
│   │   │   └── Steep/
│   │   │       └── StoreSteepRequest.php
│   │   └── Resources/
│   │       ├── TeapotResource.php
│   │       ├── TeaResource.php
│   │       ├── BrewResource.php
│   │       └── SteepResource.php
│   ├── Models/
│   │   ├── Teapot.php
│   │   ├── Tea.php
│   │   ├── Brew.php
│   │   └── Steep.php
│   ├── Enums/
│   │   ├── TeapotMaterial.php
│   │   ├── TeapotStyle.php
│   │   ├── TeaType.php
│   │   ├── CaffeineLevel.php
│   │   └── BrewStatus.php
│   └── Services/
│       └── MemoryStore.php          # In-memory store (no DB for fixture)
├── routes/
│   └── api.php                      # API routes
├── tests/
│   └── Feature/
│       └── TeapotTest.php
├── expected/
│   └── openapi.yaml                 # Expected api2spec output
├── api2spec.config.php              # api2spec configuration
├── composer.json
└── README.md
```

---

## Enums to Implement

### app/Enums/TeapotMaterial.php

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum TeapotMaterial: string
{
    case Ceramic = 'ceramic';
    case CastIron = 'cast-iron';
    case Glass = 'glass';
    case Porcelain = 'porcelain';
    case Clay = 'clay';
    case StainlessSteel = 'stainless-steel';

    /**
     * Get all enum values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

### app/Enums/TeapotStyle.php

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum TeapotStyle: string
{
    case Kyusu = 'kyusu';
    case Gaiwan = 'gaiwan';
    case English = 'english';
    case Moroccan = 'moroccan';
    case Turkish = 'turkish';
    case Yixing = 'yixing';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

### app/Enums/TeaType.php

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum TeaType: string
{
    case Green = 'green';
    case Black = 'black';
    case Oolong = 'oolong';
    case White = 'white';
    case Puerh = 'puerh';
    case Herbal = 'herbal';
    case Rooibos = 'rooibos';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

### app/Enums/CaffeineLevel.php

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum CaffeineLevel: string
{
    case None = 'none';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

### app/Enums/BrewStatus.php

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum BrewStatus: string
{
    case Preparing = 'preparing';
    case Steeping = 'steeping';
    case Ready = 'ready';
    case Served = 'served';
    case Cold = 'cold';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

---

## Models to Implement

### app/Models/Teapot.php

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Support\Carbon;

/**
 * Teapot entity.
 *
 * @property string $id UUID
 * @property string $name Teapot name (1-100 chars)
 * @property TeapotMaterial $material Teapot material
 * @property int $capacity_ml Capacity in milliliters (1-5000)
 * @property TeapotStyle $style Teapot style
 * @property string|null $description Optional description (max 500 chars)
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 */
class Teapot
{
    public function __construct(
        public string $id,
        public string $name,
        public TeapotMaterial $material,
        public int $capacity_ml,
        public TeapotStyle $style,
        public ?string $description,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    /**
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'material' => $this->material->value,
            'capacityMl' => $this->capacity_ml,
            'style' => $this->style->value,
            'description' => $this->description,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

### app/Models/Tea.php

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use Illuminate\Support\Carbon;

/**
 * Tea entity.
 *
 * @property string $id UUID
 * @property string $name Tea name (1-100 chars)
 * @property TeaType $type Tea type
 * @property string|null $origin Origin region (max 100 chars)
 * @property CaffeineLevel $caffeine_level Caffeine level
 * @property int $steep_temp_celsius Steeping temperature (60-100)
 * @property int $steep_time_seconds Steeping time (1-600)
 * @property string|null $description Optional description (max 1000 chars)
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 */
class Tea
{
    public function __construct(
        public string $id,
        public string $name,
        public TeaType $type,
        public ?string $origin,
        public CaffeineLevel $caffeine_level,
        public int $steep_temp_celsius,
        public int $steep_time_seconds,
        public ?string $description,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'origin' => $this->origin,
            'caffeineLevel' => $this->caffeine_level->value,
            'steepTempCelsius' => $this->steep_temp_celsius,
            'steepTimeSeconds' => $this->steep_time_seconds,
            'description' => $this->description,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

### app/Models/Brew.php

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BrewStatus;
use Illuminate\Support\Carbon;

/**
 * Brew session entity.
 *
 * @property string $id UUID
 * @property string $teapot_id Teapot UUID
 * @property string $tea_id Tea UUID
 * @property BrewStatus $status Brew status
 * @property int $water_temp_celsius Water temperature (60-100)
 * @property string|null $notes Brewing notes (max 500 chars)
 * @property Carbon $started_at Start timestamp
 * @property Carbon|null $completed_at Completion timestamp
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 */
class Brew
{
    public function __construct(
        public string $id,
        public string $teapot_id,
        public string $tea_id,
        public BrewStatus $status,
        public int $water_temp_celsius,
        public ?string $notes,
        public Carbon $started_at,
        public ?Carbon $completed_at,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'teapotId' => $this->teapot_id,
            'teaId' => $this->tea_id,
            'status' => $this->status->value,
            'waterTempCelsius' => $this->water_temp_celsius,
            'notes' => $this->notes,
            'startedAt' => $this->started_at->toIso8601String(),
            'completedAt' => $this->completed_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

### app/Models/Steep.php

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;

/**
 * Steep cycle entity.
 *
 * @property string $id UUID
 * @property string $brew_id Parent brew UUID
 * @property int $steep_number Steep number (1st, 2nd, etc.)
 * @property int $duration_seconds Steep duration (min 1)
 * @property int|null $rating Rating 1-5
 * @property string|null $notes Tasting notes (max 200 chars)
 * @property Carbon $created_at Creation timestamp
 */
class Steep
{
    public function __construct(
        public string $id,
        public string $brew_id,
        public int $steep_number,
        public int $duration_seconds,
        public ?int $rating,
        public ?string $notes,
        public Carbon $created_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'brewId' => $this->brew_id,
            'steepNumber' => $this->steep_number,
            'durationSeconds' => $this->duration_seconds,
            'rating' => $this->rating,
            'notes' => $this->notes,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
```

---

## Form Requests to Implement

### app/Http/Requests/Teapot/StoreTeapotRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Teapot;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for creating a teapot.
 *
 * @property string $name Teapot name (required, 1-100 chars)
 * @property string $material Teapot material (required, enum)
 * @property int $capacityMl Capacity in milliliters (required, 1-5000)
 * @property string|null $style Teapot style (optional, enum, default: english)
 * @property string|null $description Description (optional, max 500 chars)
 */
class StoreTeapotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'material' => ['required', 'string', Rule::enum(TeapotMaterial::class)],
            'capacityMl' => ['required', 'integer', 'min:1', 'max:5000'],
            'style' => ['sometimes', 'string', Rule::enum(TeapotStyle::class)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get validated data with defaults applied.
     *
     * @return array<string, mixed>
     */
    public function validatedWithDefaults(): array
    {
        $data = $this->validated();
        $data['style'] ??= TeapotStyle::English->value;
        return $data;
    }
}
```

### app/Http/Requests/Teapot/UpdateTeapotRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Teapot;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PUT (full replacement) of a teapot.
 *
 * @property string $name Teapot name (required)
 * @property string $material Teapot material (required)
 * @property int $capacityMl Capacity in milliliters (required)
 * @property string $style Teapot style (required)
 * @property string|null $description Description (optional)
 */
class UpdateTeapotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'material' => ['required', 'string', Rule::enum(TeapotMaterial::class)],
            'capacityMl' => ['required', 'integer', 'min:1', 'max:5000'],
            'style' => ['required', 'string', Rule::enum(TeapotStyle::class)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
```

### app/Http/Requests/Teapot/PatchTeapotRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Teapot;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PATCH (partial update) of a teapot.
 *
 * @property string|null $name Teapot name (optional)
 * @property string|null $material Teapot material (optional)
 * @property int|null $capacityMl Capacity in milliliters (optional)
 * @property string|null $style Teapot style (optional)
 * @property string|null $description Description (optional)
 */
class PatchTeapotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:1', 'max:100'],
            'material' => ['sometimes', 'string', Rule::enum(TeapotMaterial::class)],
            'capacityMl' => ['sometimes', 'integer', 'min:1', 'max:5000'],
            'style' => ['sometimes', 'string', Rule::enum(TeapotStyle::class)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
```

### app/Http/Requests/Tea/StoreTeaRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Tea;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for creating a tea.
 *
 * @property string $name Tea name (required, 1-100 chars)
 * @property string $type Tea type (required, enum)
 * @property string|null $origin Origin region (optional, max 100 chars)
 * @property string|null $caffeineLevel Caffeine level (optional, default: medium)
 * @property int $steepTempCelsius Steeping temperature (required, 60-100)
 * @property int $steepTimeSeconds Steeping time (required, 1-600)
 * @property string|null $description Description (optional, max 1000 chars)
 */
class StoreTeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'type' => ['required', 'string', Rule::enum(TeaType::class)],
            'origin' => ['sometimes', 'nullable', 'string', 'max:100'],
            'caffeineLevel' => ['sometimes', 'string', Rule::enum(CaffeineLevel::class)],
            'steepTempCelsius' => ['required', 'integer', 'min:60', 'max:100'],
            'steepTimeSeconds' => ['required', 'integer', 'min:1', 'max:600'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function validatedWithDefaults(): array
    {
        $data = $this->validated();
        $data['caffeineLevel'] ??= CaffeineLevel::Medium->value;
        return $data;
    }
}
```

### app/Http/Requests/Brew/StoreBrewRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Brew;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request body for creating a brew.
 *
 * @property string $teapotId Teapot UUID (required)
 * @property string $teaId Tea UUID (required)
 * @property int|null $waterTempCelsius Water temperature (optional, 60-100)
 * @property string|null $notes Brewing notes (optional, max 500 chars)
 */
class StoreBrewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'teapotId' => ['required', 'string', 'uuid'],
            'teaId' => ['required', 'string', 'uuid'],
            'waterTempCelsius' => ['sometimes', 'integer', 'min:60', 'max:100'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
```

### app/Http/Requests/Brew/PatchBrewRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Brew;

use App\Enums\BrewStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PATCH of a brew.
 *
 * @property string|null $status Brew status (optional, enum)
 * @property string|null $notes Brewing notes (optional, max 500 chars)
 * @property string|null $completedAt Completion timestamp (optional, ISO 8601)
 */
class PatchBrewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::enum(BrewStatus::class)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
            'completedAt' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
```

### app/Http/Requests/Steep/StoreSteepRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Steep;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request body for creating a steep.
 *
 * @property int $durationSeconds Steep duration (required, min 1)
 * @property int|null $rating Rating 1-5 (optional)
 * @property string|null $notes Tasting notes (optional, max 200 chars)
 */
class StoreSteepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'durationSeconds' => ['required', 'integer', 'min:1'],
            'rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:200'],
        ];
    }
}
```

---

## Controllers to Implement

### Route Summary Table

| Method | Path | Request Body | Query Params | Success | Errors |
|--------|------|--------------|--------------|---------|--------|
| GET | `/api/teapots` | — | page, limit, material, style | 200 | — |
| POST | `/api/teapots` | StoreTeapotRequest | — | 201 | 422 |
| GET | `/api/teapots/{id}` | — | — | 200 | 404 |
| PUT | `/api/teapots/{id}` | UpdateTeapotRequest | — | 200 | 404, 422 |
| PATCH | `/api/teapots/{id}` | PatchTeapotRequest | — | 200 | 404, 422 |
| DELETE | `/api/teapots/{id}` | — | — | 204 | 404 |
| GET | `/api/teapots/{teapotId}/brews` | — | page, limit | 200 | 404 |
| GET | `/api/teas` | — | page, limit, type, caffeineLevel | 200 | — |
| POST | `/api/teas` | StoreTeaRequest | — | 201 | 422 |
| GET | `/api/teas/{id}` | — | — | 200 | 404 |
| PUT | `/api/teas/{id}` | UpdateTeaRequest | — | 200 | 404, 422 |
| PATCH | `/api/teas/{id}` | PatchTeaRequest | — | 200 | 404, 422 |
| DELETE | `/api/teas/{id}` | — | — | 204 | 404 |
| GET | `/api/brews` | — | page, limit, status, teapotId, teaId | 200 | — |
| POST | `/api/brews` | StoreBrewRequest | — | 201 | 422 |
| GET | `/api/brews/{id}` | — | — | 200 | 404 |
| PATCH | `/api/brews/{id}` | PatchBrewRequest | — | 200 | 404, 422 |
| DELETE | `/api/brews/{id}` | — | — | 204 | 404 |
| GET | `/api/brews/{brewId}/steeps` | — | page, limit | 200 | 404 |
| POST | `/api/brews/{brewId}/steeps` | StoreSteepRequest | — | 201 | 404, 422 |
| GET | `/api/health` | — | — | 200 | — |
| GET | `/api/health/live` | — | — | 200 | — |
| GET | `/api/health/ready` | — | — | 200/503 | — |
| GET | `/api/brew` | — | — | **418** | — |

### app/Http/Controllers/TeapotController.php

```php
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

        $totalPages = (int) ceil($total / $limit);

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
```

### app/Http/Controllers/HealthController.php

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class HealthController extends Controller
{
    /**
     * Basic health check.
     *
     * GET /api/health
     *
     * @return JsonResponse 200: Health status
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => Carbon::now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    }

    /**
     * Liveness probe.
     *
     * GET /api/health/live
     *
     * @return JsonResponse 200: Liveness status
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Readiness probe.
     *
     * GET /api/health/ready
     *
     * @return JsonResponse 200: Ready (all checks pass)
     * @return JsonResponse 503: Not ready (some checks fail)
     */
    public function ready(): JsonResponse
    {
        $checks = [
            ['name' => 'memory', 'status' => 'ok'],
            ['name' => 'database', 'status' => 'ok'],
        ];

        $allOk = collect($checks)->every(fn ($c) => $c['status'] === 'ok');
        $status = $allOk ? 'ok' : 'degraded';
        $statusCode = $allOk ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => Carbon::now()->toIso8601String(),
            'checks' => $checks,
        ], $statusCode);
    }

    /**
     * TIF 418 signature endpoint.
     *
     * GET /api/brew
     *
     * @return JsonResponse 418: I'm a teapot
     */
    public function brew(): JsonResponse
    {
        return response()->json([
            'error' => "I'm a teapot",
            'message' => 'This server is TIF-compliant and cannot brew coffee',
            'spec' => 'https://teapotframework.dev',
        ], 418);
    }
}
```

---

## Routes

### routes/api.php

```php
<?php

use App\Http\Controllers\BrewController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TeaController;
use App\Http\Controllers\TeapotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health routes
Route::get('/health', [HealthController::class, 'health']);
Route::get('/health/live', [HealthController::class, 'live']);
Route::get('/health/ready', [HealthController::class, 'ready']);
Route::get('/brew', [HealthController::class, 'brew']);

// Teapot routes
Route::apiResource('teapots', TeapotController::class)->except(['edit', 'create']);
Route::patch('/teapots/{id}', [TeapotController::class, 'patch']);
Route::get('/teapots/{teapotId}/brews', [BrewController::class, 'indexByTeapot']);

// Tea routes
Route::apiResource('teas', TeaController::class)->except(['edit', 'create']);
Route::patch('/teas/{id}', [TeaController::class, 'patch']);

// Brew routes
Route::apiResource('brews', BrewController::class)->only(['index', 'store', 'show', 'destroy']);
Route::patch('/brews/{id}', [BrewController::class, 'patch']);
Route::get('/brews/{brewId}/steeps', [BrewController::class, 'indexSteeps']);
Route::post('/brews/{brewId}/steeps', [BrewController::class, 'storeSteep']);
```

---

## In-Memory Store

### app/Services/MemoryStore.php

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BrewStatus;
use App\Enums\CaffeineLevel;
use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use App\Enums\TeaType;
use App\Models\Brew;
use App\Models\Steep;
use App\Models\Tea;
use App\Models\Teapot;

class MemoryStore
{
    /** @var array<string, Teapot> */
    private array $teapots = [];

    /** @var array<string, Tea> */
    private array $teas = [];

    /** @var array<string, Brew> */
    private array $brews = [];

    /** @var array<string, Steep> */
    private array $steeps = [];

    // Teapot methods

    /**
     * @return Teapot[]
     */
    public function listTeapots(
        int $page = 1,
        int $limit = 20,
        ?TeapotMaterial $material = null,
        ?TeapotStyle $style = null,
    ): array {
        $filtered = array_filter($this->teapots, function (Teapot $t) use ($material, $style) {
            if ($material !== null && $t->material !== $material) {
                return false;
            }
            if ($style !== null && $t->style !== $style) {
                return false;
            }
            return true;
        });

        $offset = ($page - 1) * $limit;
        return array_slice(array_values($filtered), $offset, $limit);
    }

    public function countTeapots(
        ?TeapotMaterial $material = null,
        ?TeapotStyle $style = null,
    ): int {
        $filtered = array_filter($this->teapots, function (Teapot $t) use ($material, $style) {
            if ($material !== null && $t->material !== $material) {
                return false;
            }
            if ($style !== null && $t->style !== $style) {
                return false;
            }
            return true;
        });

        return count($filtered);
    }

    public function createTeapot(Teapot $teapot): void
    {
        $this->teapots[$teapot->id] = $teapot;
    }

    public function getTeapot(string $id): ?Teapot
    {
        return $this->teapots[$id] ?? null;
    }

    public function updateTeapot(Teapot $teapot): void
    {
        $this->teapots[$teapot->id] = $teapot;
    }

    public function deleteTeapot(string $id): bool
    {
        if (!isset($this->teapots[$id])) {
            return false;
        }
        unset($this->teapots[$id]);
        return true;
    }

    // Similar methods for Tea, Brew, Steep...
}
```

### app/Providers/AppServiceProvider.php

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\MemoryStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register MemoryStore as singleton
        $this->app->singleton(MemoryStore::class, function () {
            return new MemoryStore();
        });
    }

    public function boot(): void
    {
        //
    }
}
```

---

## api2spec Configuration

### api2spec.config.php

```php
<?php

return [
    'framework' => 'laravel',
    'entry' => [
        'routes/api.php',
        'app/Http/Controllers/**/*.php',
    ],
    'exclude' => [
        '**/*Test.php',
    ],
    'output' => [
        'path' => 'generated/openapi.yaml',
        'format' => 'yaml',
    ],
    'openapi' => [
        'info' => [
            'title' => 'Tea Brewing API',
            'version' => '1.0.0',
            'description' => 'Laravel fixture API for api2spec. TIF-compliant.',
        ],
        'servers' => [
            ['url' => 'http://localhost:8000', 'description' => 'Development'],
        ],
        'tags' => [
            ['name' => 'teapots', 'description' => 'Teapot management'],
            ['name' => 'teas', 'description' => 'Tea catalog'],
            ['name' => 'brews', 'description' => 'Brewing sessions'],
            ['name' => 'health', 'description' => 'Health checks'],
        ],
    ],
    'schemas' => [
        'include' => [
            'app/Http/Requests/**/*.php',
            'app/Models/**/*.php',
            'app/Enums/**/*.php',
        ],
    ],
    'frameworkOptions' => [
        'laravel' => [
            'routeFile' => 'routes/api.php',
            'formRequests' => true,
        ],
    ],
];
```

---

## Implementation Checklist

### Phase 1: Setup
- [ ] Create Laravel project with `composer create-project`
- [ ] Configure for API-only (remove web routes, views)
- [ ] Create directory structure

### Phase 2: Enums
- [ ] app/Enums/TeapotMaterial.php
- [ ] app/Enums/TeapotStyle.php
- [ ] app/Enums/TeaType.php
- [ ] app/Enums/CaffeineLevel.php
- [ ] app/Enums/BrewStatus.php

### Phase 3: Models
- [ ] app/Models/Teapot.php
- [ ] app/Models/Tea.php
- [ ] app/Models/Brew.php
- [ ] app/Models/Steep.php

### Phase 4: Form Requests
- [ ] app/Http/Requests/Teapot/StoreTeapotRequest.php
- [ ] app/Http/Requests/Teapot/UpdateTeapotRequest.php
- [ ] app/Http/Requests/Teapot/PatchTeapotRequest.php
- [ ] app/Http/Requests/Tea/StoreTeaRequest.php
- [ ] app/Http/Requests/Tea/UpdateTeaRequest.php
- [ ] app/Http/Requests/Tea/PatchTeaRequest.php
- [ ] app/Http/Requests/Brew/StoreBrewRequest.php
- [ ] app/Http/Requests/Brew/PatchBrewRequest.php
- [ ] app/Http/Requests/Steep/StoreSteepRequest.php

### Phase 5: Store & Service
- [ ] app/Services/MemoryStore.php
- [ ] Register singleton in AppServiceProvider

### Phase 6: Controllers
- [ ] app/Http/Controllers/TeapotController.php
- [ ] app/Http/Controllers/TeaController.php
- [ ] app/Http/Controllers/BrewController.php
- [ ] app/Http/Controllers/HealthController.php

### Phase 7: Routes
- [ ] routes/api.php

### Phase 8: Config & Expected Output
- [ ] api2spec.config.php
- [ ] expected/openapi.yaml
- [ ] README.md

### Phase 9: Validation
- [ ] Run `php artisan serve` and test all endpoints
- [ ] Verify 418 response at GET /api/brew
- [ ] Run api2spec and compare output

---

## Notes for Claude Code

1. **Form Requests are the source of truth** — Validation rules in `rules()` define the schema
2. **Enums with `values()` helper** — Makes validation rules cleaner with `Rule::enum()`
3. **Models use `toArray()` with camelCase** — Manual serialization for consistent JSON output
4. **DocBlocks document the API** — Include @property, @param, @return for api2spec
5. **Use `Route::apiResource()`** — Generates RESTful routes automatically
6. **422 for validation errors** — Laravel's default, not 400
7. **The 418 endpoint is required** — TIF signature at GET /api/brew
8. **PUT vs PATCH** — PUT uses `required`, PATCH uses `sometimes`
9. **MemoryStore as singleton** — Register in AppServiceProvider for DI

---

## Testing the Fixture

```bash
# Start the server
php artisan serve

# Test endpoints
curl http://localhost:8000/api/health
curl http://localhost:8000/api/brew  # Should return 418

# Create a teapot
curl -X POST http://localhost:8000/api/teapots \
  -H "Content-Type: application/json" \
  -d '{"name":"My Kyusu","material":"clay","capacityMl":350,"style":"kyusu"}'

# List teapots
curl http://localhost:8000/api/teapots

# Get teapot by ID
curl http://localhost:8000/api/teapots/{id}
```
