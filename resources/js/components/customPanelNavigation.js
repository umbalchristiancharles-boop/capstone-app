export function isCustomPanelOrigin() {
  try {
    return new URLSearchParams(window.location.search).get('from') === 'custom-panel'
  } catch (error) {
    return false
  }
}

export function leaveToCustomPanel() {
  if (!isCustomPanelOrigin()) return false
  window.location.replace('/custom-panel')
  return true
}
