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
