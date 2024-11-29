<?php

declare(strict_types=1);

namespace App\Contract\Entity;

// TODO convert into native enum when PHP 8.1 is available
final class LeaderboardTypes
{
    public const BREATH = 'breath';
    public const CAREER = 'career';

    public static function cases(): array
    {
        return [
            self::BREATH,
            self::CAREER,
        ];
    }
}
