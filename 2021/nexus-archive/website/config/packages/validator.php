<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig, ContainerConfigurator $containerConfigurator) {
    $validationConfig = $frameworkConfig->validation();
    $validationConfig->emailValidationMode(value: 'html5');

    if ('test' === $containerConfigurator->env()) {
        $validationConfig->notCompromisedPassword()->enabled(value: false);
    }
};
