<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use function date_default_timezone_set;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function initializeContainer()
    {
        date_default_timezone_set(timezoneId: 'UTC');

        parent::initializeContainer();
    }
}
