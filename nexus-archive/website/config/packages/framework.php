<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig, ContainerConfigurator $containerConfigurator) {
    $frameworkConfig->secret(value: '%env(APP_SECRET)%');
    $frameworkConfig->httpMethodOverride(value: false);

    $sessionConfig = $frameworkConfig->session();
    $sessionConfig->storageFactoryId(value: 'session.storage.factory.native');
    $sessionConfig->handlerId(value: null);
    $sessionConfig->cookieSecure(value: 'auto');
    $sessionConfig->cookieSamesite(value: 'lax');

    $phpErrorsConfig = $frameworkConfig->phpErrors();
    $phpErrorsConfig->log(value: true);

    if ('test' === $containerConfigurator->env()) {
        $frameworkConfig->test(value: true);
        $sessionConfig->storageFactoryId(value: 'session.storage.factory.mock_file');
    }
};
