import { reactive } from 'vue'

export const toastState = reactive({ toasts: [] })

export function showToast(message, type = 'success', duration = 4000) {
  const id = Date.now() + Math.floor(Math.random() * 1000)
  const t = { id, message, type }
  toastState.toasts.push(t)
  setTimeout(() => {
    const idx = toastState.toasts.findIndex(x => x.id === id)
    if (idx !== -1) toastState.toasts.splice(idx, 1)
  }, duration)
}

export function clearToasts() {
  toastState.toasts.splice(0, toastState.toasts.length)
}
