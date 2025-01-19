<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use function date_default_timezone_set;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        date_default_timezone_set(timezoneId: 'UTC');
        parent::boot();
    }
}
