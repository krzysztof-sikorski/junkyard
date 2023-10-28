# Coding standard

Helper classes for enforcing my personal coding standard in PHP code.

## Licence

This mini-library is licensed under [MIT License][MIT].

Full text of the licence is attached in [LICENSE.txt](./LICENSE.txt) file.

## Installation

```shell
composer require --dev krzysztof-sikorski/coding-standard
```

## Usage

Create or update a configuration file `.php-cs-fixer.dist.php`
in the root of your project:

```php
use KrzysztofSikorski\CodingStandard\PhpCsFixer\RulesFactory;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

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
```

## Source code

This repository is mirrored to multiple services for redundancy:

- <https://codeberg.org/krzysztof-sikorski/php-coding-standard>
- <https://git.disroot.org/krzysztof-sikorski/php-coding-standard>
- <https://gitlab.com/krzysztof-sikorski/php-coding-standard>
- <https://github.com/krzysztof-sikorski/php-coding-standard>

[MIT]:
https://spdx.org/licenses/MIT.html
