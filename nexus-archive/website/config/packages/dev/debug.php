<?php

declare(strict_types=1);

use Symfony\Config\DebugConfig;

return static function (DebugConfig $debugConfig) {
    // Forwards VarDumper Data clones to a centralized server
    // allowing to inspect dumps on CLI or in your browser.
    // See the "server:dump" command to start a new server.
    $debugConfig->dumpDestination(value: 'tcp://%env(VAR_DUMPER_SERVER)%');
};
