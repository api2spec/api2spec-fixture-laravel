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
