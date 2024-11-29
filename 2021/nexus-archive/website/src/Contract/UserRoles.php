<?php

declare(strict_types=1);

namespace App\Contract;

use function array_filter;
use function array_values;
use function in_array;

// TODO convert into native enum when PHP 8.1 is released
final class UserRoles
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    private const ALL_ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
    ];

    public static function isValidRole(string $role): bool
    {
        return in_array(needle: $role, haystack: self::ALL_ROLES, strict: true);
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    public static function normalize(array $roles): array
    {
        $callback = static function (string $role) use ($roles) {
            return in_array($role, $roles, true);
        };
        return array_values(array: array_filter(array: self::ALL_ROLES, callback: $callback));
    }
}
