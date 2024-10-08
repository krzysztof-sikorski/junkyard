<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig, ContainerConfigurator $containerConfigurator) {
    $routerConfig = $frameworkConfig->router();
    $routerConfig->utf8(value: true);

    if ('prod' === $containerConfigurator->env()) {
        $routerConfig->strictRequirements(value: null);
    }
};
