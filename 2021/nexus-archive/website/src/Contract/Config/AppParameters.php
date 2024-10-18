<?php

declare(strict_types=1);

namespace App\Contract\Config;

use App\Contract\UserRoles;
use DateTimeInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use const JSON_PRETTY_PRINT;

final class AppParameters
{
    // doctrine parameters
    public const DOCTRINE_DEFAULT_CONNECTION_NAME = 'default';
    public const CACHE_POOL_NAME_DOCTRINE_QUERY_CACHE = 'doctrine.system_cache_pool';
    public const CACHE_POOL_NAME_DOCTRINE_RESULT_CACHE = 'doctrine.result_cache_pool';

    // framework parameters
    public const DEFAULT_LOCALE = 'en';
    public const SERIALIZER_DEFAULT_CONTEXT = [
        ObjectNormalizer::GROUPS => [AppSerializationGroups::DEFAULT],
        DateTimeNormalizer::FORMAT_KEY => DateTimeInterface::ISO8601,
        JsonEncode::OPTIONS => JSON_PRETTY_PRINT,
    ];

    // security parameters
    public const SECURITY_USER_PROVIDER_NAME = 'app_user_provider';
    public const SECURITY_USER_ENTITY_ID_FIELD = 'username';
    public const SECURITY_DEFAULT_ROLE = UserRoles::ROLE_USER;

    // value literals that are not defined as constants in package files
    public const CONFIGURATOR_IMPORT_TYPE_ANNOTATION = 'annotation';
    public const CONFIGURATOR_IMPORT_TYPE_GLOB = 'glob';
    public const DOCTRINE_COLUMN_TYPE_UUID = 'uuid';
}
