<?php
/**
 * My personal coding standard
 *
 * @author Krzysztof Sikorski
 * @copyright 2023 Krzysztof Sikorski
 */

declare(strict_types=1);

use KrzysztofSikorski\CodingStandard\PhpCsFixerRulesFactory;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/tools/php-cs-fixer/vendor/autoload.php';

$configName = 'My personal coding standard';

$header = <<<'HEADER'
    My personal coding standard

    @author Krzysztof Sikorski
    @copyright 2023 Krzysztof Sikorski
    HEADER;

$finder = new Finder();
$finder->files();
$finder->in(dirs: __DIR__);
$finder->ignoreDotFiles(ignoreDotFiles: false);
$finder->ignoreVCSIgnored(ignoreVCSIgnored: true);

$finder->append(iterator: [__FILE__]);

$rules = PhpCsFixerRulesFactory::create(header: $header);

$config = new Config(name: $configName);
$config->setFinder(finder: $finder);
$config->setRiskyAllowed(isRiskyAllowed: true);
$config->setRules(rules: $rules);

return $config;
