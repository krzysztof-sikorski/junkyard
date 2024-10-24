SHELL := /bin/sh
.POSIX: # enable POSIX compatibility
.SUFFIXES: # no special suffixes
.DEFAULT_GOAL := default

BACKUP_TITLE = Save all changes
DOCKER_BASE_CMD = docker container run --rm --user=$$(id --user):$$(id --group)
DOCKER_IMAGE_EDITORCONFIG_CHECKER = mstruebing/editorconfig-checker:v3.0.3

# Dummy entry to force make to do nothing by default
.PHONY: default
default:
	@echo "Please choose target explicitly."

# Git helper: squash all changes into root commit
.PHONY: git_squash_all
git_squash_all:
	git add --all
	git commit --amend --reset-author --gpg-sign --message="${BACKUP_TITLE}"

# Git helper: push current branch to all configured remotes
.PHONY: git_push_all
git_push_all:
	git remote | xargs -L1 git push --verbose --force-with-lease

# Lint all files against EditorConfig settings
.PHONY: lint_editorconfig
lint_editorconfig:
	${DOCKER_BASE_CMD} --volume=$$PWD:/check:ro \
		${DOCKER_IMAGE_EDITORCONFIG_CHECKER} \
		ec -config .editorconfig-checker.json
