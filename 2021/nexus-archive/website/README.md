# Nexus Archive

The <q>Nexus Archive</q> website, based on Symfony framework.

## Licence

This project is licensed under [European Union Public Licence (EUPL)][EUPL].

For convenience an English text of the licence is included
in [LICENSE.txt](./LICENSE.txt) file.

## Repositories

Source code is primarily hosted
on [my private Git server](https://git.zerozero.pl/nexus-archive), but for
convenience and redundancy it is also mirrored to a few popular code hosting
portals:

- [Gitlab mirror](https://gitlab.com/krzysztof-sikorski/nexus-archive)
- [GitHub mirror](https://github.com/krzysztof-sikorski/nexus-archive)
- [Launchpad mirror](https://git.launchpad.net/nexus-archive)

## Installation and deployment

This is a standard Symfony-based web application, requiring only a standard
software stack of:

- an http server (e.g. Nginx)
- PHP binaries and some standard extensions (
  see [composer.json file](./composer.json) for details)
- [Composer][Composer] tool (for fetching and installing third-party PHP
  libraries)
- a relational database server supporting SQL language (e.g. PostgreSQL)

You can find some generic advice in Symfony documentation,
in [installation][SymfonyInstallation]
and [deployment][SymfonyDeployment] chapters.

The application was only tested on PostgreSQL, but it should theoretically work
on any database engine that is supported by Doctrine library.
Check [Doctrine documentation][DoctrineVendors] for details.

On Linux Mint (and probably also Ubuntu or Debian) you can use following
commands to install required system packages:

```shell
sudo apt-get install php-cli php-fpm postgresql # basic packages
sudo apt-get install php-xml php-mbstring php-intl php-xml # required or recommended by Symfony
sudo apt-get install php-pgsql # required by application design
```

Remember to also configure periodic execution of following console commands
(e.g. via cron jobs or systemd timers):

- `bin/console app:worker:parser` for parsing submitted data
- `bin/console app:worker:prune-database` for pruning unwanted rows from db

## Development notes

- some classes are loaded from `var\cache` directory, so you have to
  execute `bin/console cache:warmup` to have them available for IDE
  autocompletion

[EUPL]:
https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12

[Composer]:
https://getcomposer.org/

[SymfonyInstallation]:
https://symfony.com/doc/current/setup.html

[SymfonyDeployment]:
https://symfony.com/doc/current/deployment.html

[DoctrineVendors]:
https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/introduction.html
