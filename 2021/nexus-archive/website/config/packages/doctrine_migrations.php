<?php

declare(strict_types=1);

use Symfony\Config\DoctrineMigrationsConfig;

return static function (DoctrineMigrationsConfig $doctrineMigrationsConfig) {
    $doctrineMigrationsConfig->migrationsPath(
        namespace: 'DoctrineMigrations',
        value: '%kernel.project_dir%/migrations'
    );
    $doctrineMigrationsConfig->enableProfiler(value: '%kernel.debug%');
};
