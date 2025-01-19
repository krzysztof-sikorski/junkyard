<?php

declare(strict_types=1);

use App\Service\MainMenuGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\TwigConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (TwigConfig $twigConfig, ContainerConfigurator $containerConfigurator) {
    $twigConfig->defaultPath(value: '%kernel.project_dir%/templates');

    $mainMenuConfig = $twigConfig->global(key: 'mainMenuGenerator');
    $mainMenuConfig->value(value: service(serviceId: MainMenuGenerator::class));

    if ('test' === $containerConfigurator->env()) {
        $twigConfig->strictVariables(value: true);
    }
};
