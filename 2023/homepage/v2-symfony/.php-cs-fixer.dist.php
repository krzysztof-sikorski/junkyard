<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

use KrzysztofSikorski\CodingStandard\PhpCsFixerRulesFactory;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/tools/php-cs-fixer/vendor/autoload.php';

$configName = 'My personal coding standard';
$header = 'Simple CMS for small websites.';

$finder = Finder::create();
$finder->files();
$finder->in(dirs: __DIR__);
$finder->exclude(dirs: ['tools', 'var', 'vendor']);
$finder->append(iterator: [__FILE__, \implode(separator: \DIRECTORY_SEPARATOR, array: [__DIR__, 'bin', 'console'])]);

$rules = PhpCsFixerRulesFactory::create(header: $header);

$config = new Config(name: $configName);
$config->setFinder(finder: $finder);
$config->setRiskyAllowed(isRiskyAllowed: true);
$config->setRules(rules: $rules);

return $config;
