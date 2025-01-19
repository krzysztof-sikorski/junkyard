/* global NexusDataQueue, NexusDataSender, Preferences, WebRequestMonitor */
'use strict'

const preferences = new Preferences()
preferences.load()
preferences.listenForStorageChanges()

const nexusDataQueue = new NexusDataQueue()

const nexusDataSender = new NexusDataSender(preferences)

const webRequestMonitor = new WebRequestMonitor(nexusDataQueue, nexusDataSender)
webRequestMonitor.addListeners()
