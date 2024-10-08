SHELL := /bin/sh
.POSIX: # enable POSIX compatiblity
.SUFFIXES: # no special suffixes
.DEFAULT_GOAL := default

.PHONY: default
default:
	@echo "Please choose target explicitly."

.PHONY: git_push_all
git_push_all:
	git remote | xargs -L1 git push --verbose --all
	git remote | xargs -L1 git push --verbose --tags

.PHONY: check
check: check_requirements check_security

.PHONY: check_requirements
check_requirements:
	cd bedrock && composer check-platform-reqs

.PHONY: check_security
check_security:
	cd bedrock && composer update --dry-run roave/security-advisories

.PHONY: install
install:
	cd bedrock && composer install

.PHONY: clean
clean: clean_cache clean_packages

.PHONY: clean_cache
clean_cache:
	rm -rfv bedrock/web/app/cache

.PHONY:
clean_packages:
	rm -rfv bedrock/vendor
	rm -rfv bedrock/web/app/mu-plugins
	rm -rfv bedrock/web/app/plugins
	rm -rfv bedrock/web/app/themes
	rm -rfv bedrock/web/app/upgrade
	rm -rfv bedrock/web/wp
	git checkout -- bedrock/web/app/mu-plugins
	git checkout -- bedrock/web/app/plugins
	git checkout -- bedrock/web/app/themes

.PHONY: delete_uploads
delete_uploads:
	rm -rfv bedrock/web/app/uploads
	git checkout -- bedrock/web/app/uploads

.PHONY: dev_server_start
dev_server_start:
	sudo systemctl start mysql.service
	symfony local:server:start --document-root=bedrock/web --daemon --no-tls

.PHONY: dev_server_stop
dev_server_stop:
	symfony local:server:stop
	sudo systemctl stop mysql.service

.PHONY: dev_server_log
dev_server_log:
	symfony local:server:log
