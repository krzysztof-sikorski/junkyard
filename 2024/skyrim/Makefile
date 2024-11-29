SHELL := /bin/sh
.POSIX: # enable POSIX compatibility
.SUFFIXES: # no special suffixes
.DEFAULT_GOAL := default

APP_BASE_DIR = ${HOME}/.local/share/Steam/steamapps/common/Skyrim Special Edition/Data
RSYNC_COMMON_ARGS = --verbose --archive --acls --xattrs

# Dummy entry to force make to do nothing by default
.PHONY: default
default:
	@echo "Please choose target explicitly."

# Delete files
.PHONY: clear
clear:
	find "${APP_BASE_DIR}" -type f -name '*.txt' -print -delete

# Install scripts
.PHONY: install
install:
	rsync ${RSYNC_COMMON_ARGS} "data/" "${APP_BASE_DIR}"
