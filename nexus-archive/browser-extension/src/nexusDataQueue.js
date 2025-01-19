/* exported NexusDataQueue */
/* global NexusData */
'use strict'

class NexusDataQueue {
  constructor() {
    this._data = new Map()
  }

  push(nexusData) {
    if (!(nexusData instanceof NexusData)) {
      window.console.error('[NexusDataQueue] push: argument is not an instance of NexusData!')
    }
    this._data.set(nexusData.requestId, nexusData)
  }

  get(requestId) {
    if (this._data.has(requestId)) {
      return this._data.get(requestId)
    }
    return null
  }

  has(requestId) {
    return this._data.has(requestId)
  }

  delete(requestId) {
    if (this._data.has(requestId)) {
      this._data.delete(requestId)
    }
  }
}
