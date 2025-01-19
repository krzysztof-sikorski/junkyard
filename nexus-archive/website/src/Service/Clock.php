<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Service\ClockInterface;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final class Clock implements ClockInterface
{
    public function getUtcTimeZone(): DateTimeZone
    {
        return new DateTimeZone(timezone: 'UTC');
    }

    public function getCurrentDateTime(): DateTimeInterface
    {
        $timezone = $this->getUtcTimeZone();
        return new DateTimeImmutable(datetime: 'now', timezone: $timezone);
    }
}
