/* exported Preferences */
'use strict'

class Preferences {
  constructor() {
    this._userAccessToken = null
    this._trackerSubmitUrl = null
  }

  get userAccessToken() {
    return this._userAccessToken
  }

  set userAccessToken(value) {
    this._userAccessToken = value
  }

  get trackerSubmitUrl() {
    return this._trackerSubmitUrl
  }

  set trackerSubmitUrl(value) {
    this._trackerSubmitUrl = value
  }

  get _storage() {
    return browser.storage.sync
  }

  hasUserAccessToken() {
    return null !== this._userAccessToken && 0 < this._userAccessToken.length
  }

  hasTrackerSubmitUrl() {
    return null !== this._trackerSubmitUrl && 0 < this._trackerSubmitUrl.length
  }

  isConfigured() {
    return this.hasUserAccessToken() && this.hasTrackerSubmitUrl()
  }

  load() {
    const storageGetter = this._storage.get(null)

    storageGetter.then(
      results => {
        if (Object.prototype.hasOwnProperty.call(results, 'userAccessToken')) {
          this.userAccessToken = results.userAccessToken
        }
        if (Object.prototype.hasOwnProperty.call(results, 'trackerSubmitUrl')) {
          this.trackerSubmitUrl = results.trackerSubmitUrl
        }
      },
      error => {
        const message = `Error loading preferences: ${error}`
        window.console.error(message)
        window.alert(message)
      }
    )

    return storageGetter
  }

  save() {
    const storageSetter = this._storage.set({
      userAccessToken: this.userAccessToken,
      trackerSubmitUrl: this.trackerSubmitUrl
    })

    storageSetter.catch(
      error => {
        const message = `Error saving preferences: ${error}`
        window.console.error(message)
        window.alert(message)
      }
    )

    return storageSetter
  }

  listenForStorageChanges() {
    browser.storage.onChanged.addListener((changes, areaName) => {
      if ('sync' === areaName) {
        if (Object.prototype.hasOwnProperty.call(changes, 'userAccessToken')) {
          this.userAccessToken = changes.userAccessToken.newValue
        }
        if (Object.prototype.hasOwnProperty.call(changes, 'trackerSubmitUrl')) {
          this.trackerSubmitUrl = changes.trackerSubmitUrl.newValue
        }
      }
    })
  }
}
