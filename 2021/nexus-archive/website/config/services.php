<?php

declare(strict_types=1);

use App\Command\WorkerParseCommand;
use App\Contract\Config\AppParameters;
use App\Contract\Config\AppTags;
use App\Contract\Service\Parser\ParserInterface;
use App\Service\ParserSelector;
use App\Service\Serializer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Serializer\SerializerInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(
        resource: __DIR__ . '/packages/*.php',
        type: AppParameters::CONFIGURATOR_IMPORT_TYPE_GLOB,
    );

    $envConfigDir = __DIR__ . '/packages/' . $containerConfigurator->env();
    if (is_dir($envConfigDir)) {
        $containerConfigurator->import(
            resource: $envConfigDir . '/*.php',
            type: AppParameters::CONFIGURATOR_IMPORT_TYPE_GLOB,
        );
    }

    $servicesConfigurator = $containerConfigurator->services();

    $defaultsConfigurator = $servicesConfigurator->defaults();
    $defaultsConfigurator->autowire(autowired: true);
    $defaultsConfigurator->autoconfigure(autoconfigured: true);

    $instanceofConfigurator = $defaultsConfigurator->instanceof(fqcn: ParserInterface::class);
    $instanceofConfigurator->tag(name: AppTags::PARSER);

    $prototypeConfigurator = $servicesConfigurator->load(
        namespace: 'App\\',
        resource: __DIR__ . '/../src/',
    );
    $excludes = [
        __DIR__ . '/../src/DependencyInjection/',
        __DIR__ . '/../src/Entity/',
        __DIR__ . '/../src/Kernel.php',
    ];
    $prototypeConfigurator->exclude(excludes: $excludes);

    $prototypeConfigurator = $servicesConfigurator->load(
        namespace: 'App\\Controller\\',
        resource: __DIR__ . '/../src/Controller/',
    );
    $prototypeConfigurator->tag(name: 'controller.service_arguments');

    $servicesConfigurator->set(id: Serializer::class)->decorate(id: SerializerInterface::class);

    $serviceConfigurator = $servicesConfigurator->set(id: ParserSelector::class);
    $serviceConfigurator->arg(key: '$parsers', value: tagged_iterator(tag: AppTags::PARSER));

    $serviceConfigurator = $servicesConfigurator->set(id: WorkerParseCommand::class);
    $serviceConfigurator->arg(key: '$batchSize', value: env(name: 'APP_WORKER_PARSER_BATCH_SIZE')->int());
    $serviceConfigurator->arg(key: '$maxIterations', value: env(name: 'APP_WORKER_PARSER_MAX_ITERATIONS')->int());
    $serviceConfigurator->arg(key: '$maxDurationStr', value: env(name: 'APP_WORKER_PARSER_MAX_DURATION'));
};
