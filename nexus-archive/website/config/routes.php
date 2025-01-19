<?php

declare(strict_types=1);

use App\Contract\Config\AppParameters;
use App\Contract\Config\AppRoutes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator) {
    $routingConfigurator->import(
        resource: __DIR__ . '/../src/Controller/',
        type: AppParameters::CONFIGURATOR_IMPORT_TYPE_ANNOTATION,
    );

    $routingConfigurator->import(
        resource: __DIR__ . '/../src/EasyAdmin/Controller/',
        type: AppParameters::CONFIGURATOR_IMPORT_TYPE_ANNOTATION,
    );

    $routingConfigurator->import(
        resource: __DIR__ . '/../src/Kernel.php',
        type: AppParameters::CONFIGURATOR_IMPORT_TYPE_ANNOTATION,
    );

    $routeConfigurator = $routingConfigurator->add(name: AppRoutes::LOGOUT, path: '/logout');
    $methods = [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ];
    $routeConfigurator->methods(methods: $methods);

    if ('dev' === $routingConfigurator->env()) {
        $importConfigurator = $routingConfigurator->import(
            resource: '@FrameworkBundle/Resources/config/routing/errors.xml',
        );
        $importConfigurator->prefix(prefix: '/_error');

        $importConfigurator = $routingConfigurator->import(
            resource: '@WebProfilerBundle/Resources/config/routing/wdt.xml',
        );
        $importConfigurator->prefix(prefix: '/_wdt');

        $importConfigurator = $routingConfigurator->import(
            resource: '@WebProfilerBundle/Resources/config/routing/profiler.xml',
        );
        $importConfigurator->prefix(prefix: '/_profiler');
    }
};
