<?php

declare(strict_types=1);

use App\Contract\Config\AppParameters;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig) {
    $frameworkConfig->defaultLocale(value: AppParameters::DEFAULT_LOCALE);

    $translatorConfig = $frameworkConfig->translator();
    $translatorConfig->defaultPath(value: '%kernel.project_dir%/translations');
    $translatorConfig->fallbacks(value: [AppParameters::DEFAULT_LOCALE]);
};
