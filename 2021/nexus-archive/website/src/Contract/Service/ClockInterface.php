<?php

declare(strict_types=1);

namespace App\Contract\Service;

use DateTimeInterface;
use DateTimeZone;

interface ClockInterface
{
    public function getUtcTimeZone(): DateTimeZone;

    public function getCurrentDateTime(): DateTimeInterface;
}
