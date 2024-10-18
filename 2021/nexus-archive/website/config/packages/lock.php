<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (FrameworkConfig $frameworkConfig) {
    $lockConfig = $frameworkConfig->lock();
    $lockConfig->enabled(value: true);
    $lockConfig->resource(name: 'default', value: env(name: 'LOCK_DSN'));
};
