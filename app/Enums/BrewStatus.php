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
