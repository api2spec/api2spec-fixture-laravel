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
