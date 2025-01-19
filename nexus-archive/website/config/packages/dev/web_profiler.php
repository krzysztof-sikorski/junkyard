<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;
use Symfony\Config\WebProfilerConfig;

return static function (WebProfilerConfig $webProfilerConfig, FrameworkConfig $frameworkConfig) {
    $webProfilerConfig->toolbar(value: true);
    $webProfilerConfig->interceptRedirects(value: false);

    $profilerConfig = $frameworkConfig->profiler();
    $profilerConfig->onlyExceptions(value: false);
};
