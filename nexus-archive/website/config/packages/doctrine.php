<?php

declare(strict_types=1);

use App\Contract\Config\AppParameters;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\DoctrineConfig;
use Symfony\Config\FrameworkConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (
    DoctrineConfig $doctrineConfig,
    FrameworkConfig $frameworkConfig,
    ContainerConfigurator $containerConfigurator
) {
    $dbalConfig = $doctrineConfig->dbal();
    $dbalConfig->defaultConnection(value: AppParameters::DOCTRINE_DEFAULT_CONNECTION_NAME);

    $defaultConnectionConfig = $dbalConfig->connection(name: AppParameters::DOCTRINE_DEFAULT_CONNECTION_NAME);
    $defaultConnectionConfig->url(value: env(name: 'DATABASE_URL')->resolve());

    $ormConfig = $doctrineConfig->orm();
    $ormConfig->autoGenerateProxyClasses(value: true);

    $entityManagerConfig = $ormConfig->entityManager(name: AppParameters::DOCTRINE_DEFAULT_CONNECTION_NAME);
    $entityManagerConfig->namingStrategy(value: 'doctrine.orm.naming_strategy.underscore_number_aware');
    $entityManagerConfig->autoMapping(value: true);

    $mappingConfig = $entityManagerConfig->mapping(name: 'App');
    $mappingConfig->dir(value: '%kernel.project_dir%/src/Doctrine/Entity');
    $mappingConfig->prefix('App\Doctrine\Entity');
    $mappingConfig->alias(value: 'App');
    $mappingConfig->isBundle(value: false);

    if ('prod' === $containerConfigurator->env()) {
        $ormConfig->autoGenerateProxyClasses(value: false);

        $queryCacheDriverConfig = $entityManagerConfig->queryCacheDriver();
        $queryCacheDriverConfig->type(value: 'pool');
        $queryCacheDriverConfig->pool(AppParameters::CACHE_POOL_NAME_DOCTRINE_QUERY_CACHE);

        $resultCacheDriverConfig = $entityManagerConfig->resultCacheDriver();
        $resultCacheDriverConfig->type(value: 'pool');
        $resultCacheDriverConfig->pool(AppParameters::CACHE_POOL_NAME_DOCTRINE_RESULT_CACHE);

        $cacheConfig = $frameworkConfig->cache();
        $cacheConfig->pool(name: AppParameters::CACHE_POOL_NAME_DOCTRINE_QUERY_CACHE)->adapters(['cache.system']);
        $cacheConfig->pool(name: AppParameters::CACHE_POOL_NAME_DOCTRINE_RESULT_CACHE)->adapters(['cache.app']);
    }

    if ('test' === $containerConfigurator->env()) {
        // "TEST_TOKEN" is typically set by ParaTest
        $defaultConnectionConfig->dbnameSuffix('_test' . env(name: 'TEST_TOKEN')->default(''));
    }
};
