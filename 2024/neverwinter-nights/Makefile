SHELL := /bin/sh
.POSIX: # enable POSIX compatibility
.SUFFIXES: # no special suffixes
.DEFAULT_GOAL := default

NWN_BASE_DIR_LINUX = ${HOME}/.local/share/Neverwinter Nights
NWN_BASE_DIR_WINDOWS = ${HOME}/.local/share/Steam/steamapps/compatdata/704450/pfx/drive_c/users/steamuser/Documents/Neverwinter Nights
RSYNC_COMMON_ARGS = --verbose --archive --acls --xattrs --delete

# Dummy entry to force make to do nothing by default
.PHONY: default
default:
	@echo "Please choose target explicitly."

# Initialize directories
data/localvault:
	mkdir --parents --verbose data/localvault
data/modules:
	mkdir --parents --verbose data/modules

# Delete data from Windows game profile
.PHONY: clear_windows
clear_windows:
	find "${NWN_BASE_DIR_WINDOWS}/localvault" -type f -print -delete
	find "${NWN_BASE_DIR_WINDOWS}/modules" -type f -print -delete

# Restore data from backup to Linux game profile
.PHONY: push_linux_localvault
push_linux_localvault: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} data/localvault/ "${NWN_BASE_DIR_LINUX}/localvault"
.PHONY: push_linux_modules
push_linux_modules: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} data/modules/ "${NWN_BASE_DIR_LINUX}/modules"

# Restore data from backup to Windows game profile
.PHONY: push_windows_localvault
push_windows_localvault: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} data/localvault/ "${NWN_BASE_DIR_WINDOWS}/localvault"
.PHONY: push_windows_modules
push_windows_modules: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} data/modules/ "${NWN_BASE_DIR_WINDOWS}/modules"

# Backup data from Linux game profile
.PHONY: pull_linux_localvault
pull_linux_localvault: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} "${NWN_BASE_DIR_LINUX}/localvault/" data/localvault
.PHONY: pull_linux_modules
pull_linux_modules: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} "${NWN_BASE_DIR_LINUX}/modules/" data/modules

# Backup data from Windows game profile
.PHONY: pull_windows_localvault
pull_windows_localvault: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} "${NWN_BASE_DIR_WINDOWS}/localvault/" data/localvault
.PHONY: pull_windows_modules
pull_windows_modules: data/localvault data/modules
	rsync ${RSYNC_COMMON_ARGS} --exclude='temp*' --exclude='*.BackupMod' "${NWN_BASE_DIR_WINDOWS}/modules/" data/modules
