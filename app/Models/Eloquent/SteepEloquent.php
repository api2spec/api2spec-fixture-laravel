<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Steep cycle Eloquent model.
 *
 * @property string $id UUID
 * @property string $brew_id Parent brew UUID
 * @property int $steep_number Steep number (1st, 2nd, etc.)
 * @property int $duration_seconds Steep duration (min 1)
 * @property int|null $rating Rating 1-5
 * @property string|null $notes Tasting notes (max 200 chars)
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 */
class SteepEloquent extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'steeps';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'brew_id',
        'steep_number',
        'duration_seconds',
        'rating',
        'notes',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'steep_number' => 'integer',
        'duration_seconds' => 'integer',
        'rating' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the brew that this steep belongs to.
     *
     * @return BelongsTo<BrewEloquent, $this>
     */
    public function brew(): BelongsTo
    {
        return $this->belongsTo(BrewEloquent::class, 'brew_id');
    }
}
