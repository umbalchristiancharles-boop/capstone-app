import Swal from 'sweetalert2'
import axios from 'axios'

const THEME = {
  confirmButtonColor: '#FF6A3D',
  cancelButtonColor: '#6B7280'
}

export function swalAlert(message, type = 'info', title = '') {
  const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info'
  return Swal.fire({
    title: title || '',
    text: message || '',
    icon,
    confirmButtonColor: THEME.confirmButtonColor,
    background: '#ffffff'
  })
}

export async function swalConfirm(message, title = 'Are you sure?') {
  const res = await Swal.fire({
    title,
    text: message,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'Cancel',
    confirmButtonColor: THEME.confirmButtonColor,
    cancelButtonColor: THEME.cancelButtonColor,
    reverseButtons: true
  })
  return !!res.isConfirmed
}

export async function swalPrompt(message, title = '', input = 'text') {
  const res = await Swal.fire({
    title,
    text: message,
    input,
    showCancelButton: true,
    confirmButtonText: 'OK',
    cancelButtonText: 'Cancel',
    confirmButtonColor: THEME.confirmButtonColor,
    cancelButtonColor: THEME.cancelButtonColor
  })
  if (res.isConfirmed) return res.value
  return null
}

export async function swalConfirmLogout({ useApi = true, message = 'You will be logged out of your session.', title = 'Confirm logout' } = {}) {
  // Prevent multiple simultaneous logout confirmations
  if (window.__swalLogoutPending) return false
  window.__swalLogoutPending = true
  const ok = await swalConfirm(message, title)
  if (!ok) {
    window.__swalLogoutPending = false
    return false
  }

  try {
    if (useApi) {
      try { await axios.post('/api/logout', {}, { withCredentials: true }) } catch (e) { /* ignore */ }
      try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
      try { window.location.replace('/staff-landing') } catch (e) { window.location.href = '/staff-landing' }
    } else {
      try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
      try { window.location.replace('/logout') } catch (e) { window.location.href = '/logout' }
    }
  } catch (e) {
    console.error('swalConfirmLogout failed', e)
  }
  finally {
    window.__swalLogoutPending = false
  }
  return true
}

// Expose utilities globally for existing code to use
window.Swal = Swal
window.swalAlert = swalAlert
window.swalConfirm = swalConfirm
window.swalPrompt = swalPrompt
window.swalConfirmLogout = swalConfirmLogout

// Replace default alert with SweetAlert visually (non-blocking)
window.alert = function (msg) {
  try { swalAlert(String(msg || '')) } catch (e) { console.error('swal alert failed', e); }
}

export default Swal
