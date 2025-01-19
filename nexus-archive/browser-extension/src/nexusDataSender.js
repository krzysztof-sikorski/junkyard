/* exported NexusDataSender */
/* global NexusData, Preferences */
'use strict'

class NexusDataSender {
  constructor(preferences) {
    if (!(preferences instanceof Preferences)) {
      window.console.error('[NexusDataSender] constructor: argument is not an instance of Preferences!')
    }
    this._preferences = preferences
  }

  _formatDate(value) {
    return value instanceof Date ? value.toISOString() : null
  }

  send(nexusData) {
    if (!(nexusData instanceof NexusData)) {
      window.console.error('[NexusDataSender] send: argument is not an instance of NexusData!')
    }

    if (false === this._preferences.isConfigured()) {
      window.console.error('[NexusDataSender] send: preferences are not configured!')
      return
    }

    const jsonData = {
      requestStartedAt: this._formatDate(nexusData.requestStartedAt),
      responseCompletedAt: this._formatDate(nexusData.responseCompletedAt),
      method: nexusData.method,
      url: nexusData.url,
      formData: nexusData.formData,
      responseBody: nexusData.responseBody,
    }

    const formData = new FormData()
    formData.append('userAccessToken', this._preferences.userAccessToken)
    formData.append('jsonData', JSON.stringify(jsonData, null, 2))

    const fetchOptions = {
      method: 'POST',
      body: formData,
      mode: 'no-cors',
    }

    window.fetch(this._preferences.trackerSubmitUrl, fetchOptions).catch(
      error => {
        window.console.error(`[NexusDataSender] Failed to send data: ${error}`)
      }
    )
  }
}
