<?php

declare(strict_types=1);

namespace App\Service;

use function bin2hex;
use function random_bytes;

final class TokenGenerator
{
    private const VALUE_BYTES_LENGTH = 32;

    public function generate(): string
    {
        return bin2hex(string: random_bytes(length: self::VALUE_BYTES_LENGTH));
    }
}
