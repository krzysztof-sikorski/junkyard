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
clean_all: clean_cache clean_packages

.PHONY: clean_cache
clean_cache:
	rm --force --verbose .php-cs-fixer.cache

.PHONY: clean_packages
clean_packages:
	rm --force --recursive --verbose vendor
	rm --force --recursive --verbose tools/parallel-lint/vendor
	rm --force --recursive --verbose tools/php-cs-fixer/vendor

.PHONY: install
install:
	composer install
	composer --working-dir=tools/parallel-lint install
	composer --working-dir=tools/php-cs-fixer install

.PHONY: check_requirements
check_requirements:
	composer check-platform-reqs
	composer --working-dir=tools/parallel-lint check-platform-reqs
	composer --working-dir=tools/php-cs-fixer check-platform-reqs

.PHONY: lint_all
lint_all: lint_php_syntax lint_coding_style

.PHONY: lint_php_syntax
lint_php_syntax:
	tools/parallel-lint/vendor/bin/parallel-lint --show-deprecated src

.PHONY: lint_coding_style
lint_coding_style:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -vvv --dry-run

.PHONY: fix_coding_style
fix_coding_style:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -vvv
