/* global Preferences */
'use strict'

const preferences = new Preferences()

const getOptionsForm = () => document.getElementById('optionsForm')

const saveForm = event => {
  event.preventDefault()

  const optionsForm = getOptionsForm()
  preferences.userAccessToken = optionsForm.elements['userAccessToken'].value
  preferences.trackerSubmitUrl = optionsForm.elements['trackerSubmitUrl'].value
  preferences.save()
}

const initForm = () => {
  const optionsForm = getOptionsForm()
  optionsForm.addEventListener('submit', saveForm)

  preferences.load().then(
    () => {
      optionsForm.elements['userAccessToken'].value = preferences.userAccessToken
      optionsForm.elements['trackerSubmitUrl'].value = preferences.trackerSubmitUrl
    }
  )
}

document.addEventListener('DOMContentLoaded', initForm)
