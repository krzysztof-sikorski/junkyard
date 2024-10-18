<?php

declare(strict_types=1);

namespace App\Contract;

// TODO: convert into native enum when PHP 8.1 is available on production
final class PageViewSubmissionResultStatus
{
    public const SUCCESS = 'success';
    public const ERROR_JSON_DECODE = 'json-decode';
    public const ERROR_JSON_SCHEMA = 'json-schema';
    public const ERROR_ACCESS_TOKEN = 'access-token';
}
