<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Teapot Eloquent model.
 *
 * @property string $id UUID
 * @property string $name Teapot name (1-100 chars)
 * @property TeapotMaterial $material Teapot material
 * @property int $capacity_ml Capacity in milliliters (1-5000)
 * @property TeapotStyle $style Teapot style
 * @property string|null $description Optional description (max 500 chars)
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 */
class TeapotEloquent extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teapots';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'material',
        'capacity_ml',
        'style',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'material' => TeapotMaterial::class,
        'capacity_ml' => 'integer',
        'style' => TeapotStyle::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the brews for this teapot.
     *
     * @return HasMany<BrewEloquent, $this>
     */
    public function brews(): HasMany
    {
        return $this->hasMany(BrewEloquent::class, 'teapot_id');
    }
}
