<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Config\MonologConfig;

return static function (MonologConfig $monologConfig, ContainerConfigurator $containerConfigurator) {
    // As of Symfony 5.1, deprecations are logged in the dedicated "deprecation" channel when it exists
    $monologConfig->channels(value: ['deprecation']);

    if ('dev' === $containerConfigurator->env()) {
        $deprecationHandlerConfig = $monologConfig->handler(name: 'deprecation');
        $deprecationHandlerConfig->type(value: 'stream');
        $deprecationHandlerConfig->path(value: '%kernel.logs_dir%/%kernel.environment%.deprecation.log');
        $deprecationHandlerConfig->channels()->elements(value: ['deprecation']);

        $mainHandlerConfig = $monologConfig->handler(name: 'main');
        $mainHandlerConfig->type(value: 'stream');
        $mainHandlerConfig->path(value: '%kernel.logs_dir%/%kernel.environment%.log');
        $mainHandlerConfig->level(value: 'debug');
        $mainHandlerConfig->channels()->elements(value: ['!event']);

        $consoleHandlerConfig = $monologConfig->handler(name: 'console');
        $consoleHandlerConfig->type(value: 'console');
        $consoleHandlerConfig->processPsr3Messages(value: true);
        $consoleHandlerConfig->channels()->elements(value: ['!event', '!doctrine', '!console']);
    }

    if ('prod' === $containerConfigurator->env()) {
        $mainHandlerConfig = $monologConfig->handler(name: 'main');
        $mainHandlerConfig->type(value: 'fingers_crossed');
        $mainHandlerConfig->actionLevel(value: 'error');
        $mainHandlerConfig->handler(value: 'nested');
        $mainHandlerConfig->excludedHttpCode()->code(Response::HTTP_NOT_FOUND);
        $mainHandlerConfig->excludedHttpCode()->code(Response::HTTP_METHOD_NOT_ALLOWED);
        $mainHandlerConfig->bufferSize(value: 50); // How many messages should be saved? Prevent memory leaks

        $nestedHandlerConfig = $monologConfig->handler(name: 'nested');
        $nestedHandlerConfig->type(value: 'stream');
        $nestedHandlerConfig->path(value: 'php://stderr');
        $nestedHandlerConfig->level(value: 'debug');
        $nestedHandlerConfig->formatter(value: 'monolog.formatter.json');

        $consoleHandlerConfig = $monologConfig->handler(name: 'console');
        $consoleHandlerConfig->type(value: 'console');
        $consoleHandlerConfig->processPsr3Messages(value: false);
        $consoleHandlerConfig->channels()->elements(value: ['!event', '!doctrine']);
    }

    if ('test' === $containerConfigurator->env()) {
        $mainHandlerConfig = $monologConfig->handler(name: 'main');
        $mainHandlerConfig->type(value: 'fingers_crossed');
        $mainHandlerConfig->actionLevel(value: 'error');
        $mainHandlerConfig->handler(value: 'nested');
        $mainHandlerConfig->excludedHttpCode()->code(Response::HTTP_NOT_FOUND);
        $mainHandlerConfig->excludedHttpCode()->code(Response::HTTP_METHOD_NOT_ALLOWED);
        $mainHandlerConfig->channels()->elements(value: ['!event']);

        $nestedHandlerConfig = $monologConfig->handler(name: 'nested');
        $nestedHandlerConfig->type(value: 'stream');
        $nestedHandlerConfig->path(value: '%kernel.logs_dir%/%kernel.environment%.log');
        $nestedHandlerConfig->level(value: 'debug');
    }
};
