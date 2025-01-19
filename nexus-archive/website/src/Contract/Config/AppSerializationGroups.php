<?php

declare(strict_types=1);

namespace App\Contract\Config;

// TODO: convert into native enum when 8.1 is available in production
final class AppSerializationGroups
{
    public const DEFAULT = 'app.default';
    public const ENTITY_USER = 'app.entity.user';
    public const ENTITY_USER_ACCESS_TOKEN = 'app.entity.user_access_token';
}
