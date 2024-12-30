<?php

declare(strict_types=1);

namespace App\Contract\Entity\Nexus;

// TODO convert into native enum when PHP 8.1 is available on production
class GamePeriodIdEnum
{
    public const BREATH_4 = 1;
    public const BREATH_5_LAUNCH = 2;
    public const BREATH_5_OUTER_PLANES = 3;
    public const BREATH_5_STRONGHOLDS = 4;
}
