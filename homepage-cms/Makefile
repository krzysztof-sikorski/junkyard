SHELL := /bin/sh
.POSIX: # enable POSIX compatibility
.SUFFIXES: # no special suffixes
.DEFAULT_GOAL := default

default:
	@echo "Please choose target explicitly."

.PHONY: git_push_all
git_push_all:
	git remote | xargs -L1 git push --verbose --all
	git remote | xargs -L1 git push --verbose --tags

.PHONY: clean_all
clean_all: clean_cache clean_logs clean_packages

.PHONY: clean_cache
clean_cache:
	rm --force --verbose .php-cs-fixer.cache
	rm --force --recursive --verbose var/cache

.PHONY: clean_logs
clean_logs:
	rm --force --recursive --verbose var/log

.PHONY: clean_packages
clean_packages:
	rm --force --recursive --verbose vendor
	rm --force --recursive --verbose tools/parallel-lint/vendor
	rm --force --recursive --verbose tools/php-cs-fixer/vendor

.PHONY: clean_maker_results
clean_maker_results:
	rm --force --recursive --verbose src/Maker

.PHONY: install_all
install_all:
	composer install
	composer --working-dir=tools/parallel-lint install
	composer --working-dir=tools/php-cs-fixer install

.PHONY: install_quiet
install_quiet:
	composer install --quiet

.PHONY: install_parallel_lint
install_parallel_lint:
	composer --quiet --working-dir=tools/parallel-lint install

.PHONY: install_php_cs_fixer
install_php_cs_fixer:
	composer --quiet --working-dir=tools/php-cs-fixer install

.PHONY: check_all
check_all: check_security check_requirements

.PHONY: check_requirements
check_requirements: install_quiet
	composer check-platform-reqs
	symfony local:check:requirements

.PHONY: check_security
check_security: install_quiet
	composer update --dry-run roave/security-advisories
	symfony local:check:security

.PHONY: lint_php_syntax
lint_php_syntax: install_parallel_lint
	tools/parallel-lint/vendor/bin/parallel-lint --show-deprecated bin config public src .php-cs-fixer.dist.php

.PHONY: fix_coding_style
fix_coding_style: install_php_cs_fixer
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -vvv

.PHONY: test_canonical_domain_redirects
test_canonical_domain_redirects:
	curl -s -L -D - 'http://dummy.localhost:8000/' -o /dev/null -w '%{url_effective}\n\n'
	curl -s -L -D - 'http://dummy.localhost:8000/qwerty?foo=bar&baz=quux' -o /dev/null -w '%{url_effective}\n\n'
