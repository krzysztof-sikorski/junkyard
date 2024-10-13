<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\Service\ClockInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

final class DateTimeFactory
{
    public function __construct(private ClockInterface $clock)
    {
    }

    public function create(string $dateTimeString): ?DateTimeInterface
    {
        try {
            $instance = new DateTimeImmutable(datetime: $dateTimeString);
        } catch (Exception) {
            return null;
        }

        $instance->setTimezone(timezone: $this->clock->getUtcTimeZone());

        return $instance;
    }
}
