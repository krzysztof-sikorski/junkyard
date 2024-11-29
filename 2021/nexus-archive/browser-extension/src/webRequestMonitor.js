/* exported WebRequestMonitor */
/* global NexusData, NexusDataQueue, NexusDataSender */
'use strict'

class WebRequestMonitor {
  constructor(nexusDataQueue, nexusDataSender) {
    if (!(nexusDataQueue instanceof NexusDataQueue)) {
      window.console.error('[NexusDataSender] constructor: argument is not an instance of NexusDataQueue!')
    }
    if (!(nexusDataSender instanceof NexusDataSender)) {
      window.console.error('[NexusDataSender] constructor: argument is not an instance of NexusDataSender!')
    }
    this._nexusDataQueue = nexusDataQueue
    this._nexusDataSender = nexusDataSender
  }

  _attachResponseDataFilter(nexusData) {
    const decoder = new TextDecoder('utf-8')
    const encoder = new TextEncoder()
    const responseDataFilter = browser.webRequest.filterResponseData(nexusData.requestId)
    responseDataFilter.ondata = event => {
      const bodyPart = decoder.decode(event.data, {stream: true})
      responseDataFilter.write(encoder.encode(bodyPart))
      nexusData.appendResponseBodyPart(bodyPart)
    }
    responseDataFilter.onstop = event => {
      const bodyPart = decoder.decode(event.data, {stream: false})
      responseDataFilter.write(encoder.encode(bodyPart))
      responseDataFilter.close()
      nexusData.appendResponseBodyPart(bodyPart)
    }
  }

  _onBeforeRequest(details) {
    const nexusData = new NexusData()
    nexusData.requestId = details.requestId
    nexusData.requestStartedAt = details.timeStamp
    nexusData.method = details.method
    nexusData.url = details.url
    if (
      null !== details.requestBody &&
      Object.prototype.hasOwnProperty.call(details.requestBody, 'formData')
    ) {
      nexusData.formData = details.requestBody.formData
    }
    this._nexusDataQueue.push(nexusData)
    this._attachResponseDataFilter(nexusData)
  }

  _onCompleted(details) {
    const requestId = details.requestId
    if (false !== this._nexusDataQueue.has(requestId)) {
      const nexusData = this._nexusDataQueue.get(requestId)
      nexusData.responseCompletedAt = details.timeStamp
      this._nexusDataQueue.delete(requestId)
      this._nexusDataSender.send(nexusData)
    }
  }

  _onErrorOccurred(details) {
    this._nexusDataQueue.delete(details.requestId)
    window.console.error('[WebRequestMonitor] Error has occurred: ' + details.error)
  }

  addListeners() {
    const requestFilters = {
      types: ['main_frame'],
      urls: [
        '*://*.nexusclash.com/*'
      ]
    }
    browser.webRequest.onBeforeRequest.addListener(
      this._onBeforeRequest.bind(this),
      requestFilters,
      ['blocking', 'requestBody']
    )
    browser.webRequest.onCompleted.addListener(
      this._onCompleted.bind(this),
      requestFilters
    )
    browser.webRequest.onErrorOccurred.addListener(
      this._onErrorOccurred.bind(this),
      requestFilters
    )
  }
}
