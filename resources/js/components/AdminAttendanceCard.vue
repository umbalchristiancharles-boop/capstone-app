<template>
  <section class="admin-attendance-self" aria-labelledby="admin-attendance-self-title">
    <header class="admin-attendance-self__header">
      <div>
        <h2 id="admin-attendance-self-title">Today's Attendance</h2>
        <p>Review your current shift and attendance times.</p>
      </div>
      <span
        class="admin-attendance-self__status"
        :class="attendanceStatus.is_clocked_in ? 'is-on-duty' : 'is-off-duty'"
      >
        {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
      </span>
    </header>

    <div class="admin-attendance-self__body">
      <section class="admin-attendance-self__summary" aria-labelledby="admin-attendance-summary-title">
        <div>
          <h3 id="admin-attendance-summary-title">Shift summary</h3>
          <p>{{ attendanceStatus.is_clocked_in ? 'Your shift is currently in progress.' : 'Your shift is currently not active.' }}</p>
        </div>
        <div class="admin-attendance-self__times">
          <div class="admin-attendance-self__time">
            <span>Clock In</span>
            <strong>{{ attendanceStatus.clock_in_time || '—' }}</strong>
          </div>
          <div class="admin-attendance-self__time">
            <span>Clock Out</span>
            <strong>{{ attendanceStatus.clock_out_time || '—' }}</strong>
          </div>
          <div v-if="attendanceStatus.hours_worked > 0" class="admin-attendance-self__time">
            <span>Hours Worked</span>
            <strong>{{ attendanceStatus.hours_worked }} hrs</strong>
          </div>
        </div>
        <p v-if="statusError" class="admin-attendance-self__message is-error" role="alert">{{ statusError }}</p>
      </section>

      <section class="admin-attendance-self__actions" aria-labelledby="admin-attendance-actions-title">
        <div>
          <h3 id="admin-attendance-actions-title">Attendance actions</h3>
          <p>Clock in or out when you are ready.</p>
        </div>
        <div class="admin-attendance-self__buttons">
          <button
            type="button"
            class="admin-attendance-self__clock-in"
            :disabled="attendanceStatus.is_clocked_in || processing || locationLoading"
            @click="openFaceCapture"
          >
            {{ locationLoading && !userLocation ? 'Checking location...' : processing ? 'Processing...' : 'Clock In' }}
          </button>
          <button
            type="button"
            class="admin-attendance-self__clock-out"
            :disabled="!attendanceStatus.is_clocked_in || processing || !canClockOut || locationLoading"
            @click="clockOut"
          >
            {{ processing ? 'Processing...' : 'Clock Out' }}
          </button>
        </div>

        <p v-if="locationError" class="admin-attendance-self__message is-error" role="status">{{ locationError }}</p>
        <p v-else-if="userLocation" class="admin-attendance-self__message is-success" role="status">✓ Location verified</p>
        <p v-else-if="locationLoading" class="admin-attendance-self__message" role="status">Checking your location…</p>
        <p v-if="clockoutRestriction" class="admin-attendance-self__message is-warning" role="status">
          Cannot clock out before {{ scheduledTimeOut }}.
        </p>
        <p v-if="attendanceMessage" class="admin-attendance-self__message" :class="`is-${attendanceMessageType}`" role="status">
          {{ attendanceMessage }}
        </p>
      </section>
    </div>

    <div v-if="showFaceCapture" class="admin-attendance-self__backdrop" @click.self="cancelFaceCapture">
      <section class="admin-attendance-self__camera-modal" role="dialog" aria-modal="true" aria-labelledby="admin-attendance-camera-title">
        <h2 id="admin-attendance-camera-title">Verify your identity</h2>
        <p>Take a photo to clock in.</p>
        <div class="admin-attendance-self__camera">
          <video ref="cameraVideo" autoplay playsinline></video>
          <p v-if="cameraError" role="alert">{{ cameraError }}</p>
        </div>
        <div class="admin-attendance-self__camera-actions">
          <button v-if="cameraError" type="button" @click="startCamera">Try again</button>
          <button type="button" :disabled="isCapturing || !!cameraError || !cameraStream" @click="capturePhoto">
            {{ isCapturing ? 'Capturing...' : 'Capture photo' }}
          </button>
          <button type="button" class="is-secondary" @click="cancelFaceCapture">Cancel</button>
        </div>
      </section>
    </div>
  </section>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'

const attendanceStatus = ref({
  is_clocked_in: false,
  clock_in_time: null,
  clock_out_time: null,
  hours_worked: 0,
})
const attendanceSettings = ref({
  early_clockout_override: false,
  auto_clockout_time: '22:00',
})
const statusError = ref('')
const attendanceMessage = ref('')
const attendanceMessageType = ref('')
const processing = ref(false)
const locationLoading = ref(false)
const locationError = ref('')
const userLocation = ref(null)
const showFaceCapture = ref(false)
const cameraVideo = ref(null)
const cameraStream = ref(null)
const cameraError = ref('')
const isCapturing = ref(false)
let locationInterval = null
let messageTimeout = null

const scheduledTimeOut = computed(() => {
  const [hourText = '22', minuteText = '00'] = (attendanceSettings.value.auto_clockout_time || '22:00').split(':')
  const hour = Number(hourText)
  const minute = Number(minuteText)
  const date = new Date()
  date.setHours(hour, minute, 0, 0)
  return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
})

const canClockOut = computed(() => {
  if (!attendanceStatus.value.is_clocked_in) return false
  if (attendanceSettings.value.early_clockout_override) return true
  const [hourText = '22', minuteText = '00'] = (attendanceSettings.value.auto_clockout_time || '22:00').split(':')
  return new Date().getHours() * 60 + new Date().getMinutes() >= Number(hourText) * 60 + Number(minuteText)
})

const clockoutRestriction = computed(() => (
  attendanceStatus.value.is_clocked_in &&
  !canClockOut.value
))

async function loadAttendanceStatus() {
  statusError.value = ''
  try {
    const response = await axios.get('/api/staff/attendance/status', { withCredentials: true })
    if (!response.data?.success) throw new Error(response.data?.message || 'Unable to load attendance status.')
    const status = response.data.status || {}
    attendanceStatus.value = {
      is_clocked_in: Boolean(response.data.clocked_in ?? status.is_clocked_in),
      clock_in_time: response.data.time_in || status.clock_in_time || null,
      clock_out_time: response.data.time_out || status.clock_out_time || null,
      hours_worked: Number(status.hours_worked || 0),
    }
  } catch (error) {
    console.error('Failed to load Admin attendance status:', error)
    statusError.value = error.response?.data?.message || 'Unable to load attendance status.'
  }
}

async function loadAttendanceSettings() {
  try {
    const response = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (response.data?.ok && response.data.data) {
      attendanceSettings.value = {
        early_clockout_override: Boolean(response.data.data.early_clockout_override),
        auto_clockout_time: response.data.data.auto_clockout_time || '22:00',
      }
    }
  } catch (error) {
    console.error('Failed to load Admin attendance settings:', error)
  }
}

async function getUserLocation() {
  locationLoading.value = true
  locationError.value = ''
  if (!navigator.geolocation) {
    locationError.value = 'Geolocation is not supported by your browser.'
    userLocation.value = null
    locationLoading.value = false
    return
  }

  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      })
    })
    userLocation.value = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude,
    }
  } catch (error) {
    console.error('Unable to retrieve Admin attendance location:', error)
    userLocation.value = null
    locationError.value = 'Unable to retrieve your location. Enable location services to clock in or out.'
  } finally {
    locationLoading.value = false
  }
}

async function openFaceCapture() {
  if (processing.value || attendanceStatus.value.is_clocked_in) return
  attendanceMessage.value = ''
  showFaceCapture.value = true
  cameraError.value = ''
  await startCamera()
}

async function startCamera() {
  stopCamera()
  cameraError.value = ''
  if (!navigator.mediaDevices?.getUserMedia) {
    cameraError.value = 'Camera access is unavailable. Use HTTPS or localhost and enable a camera.'
    return
  }
  try {
    let stream
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
      })
    } catch (error) {
      if (error.name !== 'OverconstrainedError' && error.name !== 'NotFoundError') throw error
      stream = await navigator.mediaDevices.getUserMedia({ video: true })
    }
    cameraStream.value = stream
    await nextTick()
    if (cameraVideo.value) cameraVideo.value.srcObject = stream
  } catch (error) {
    console.error('Unable to access camera for Admin attendance:', error)
    cameraError.value = error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError'
      ? 'Camera permission is blocked. Allow camera access, then try again.'
      : 'Unable to access camera. Check browser permissions, then try again.'
    stopCamera()
  }
}

function stopCamera() {
  cameraStream.value?.getTracks().forEach(track => track.stop())
  cameraStream.value = null
}

async function capturePhoto() {
  const video = cameraVideo.value
  if (!video?.videoWidth) {
    attendanceMessage.value = 'Camera is not ready. Please try again.'
    attendanceMessageType.value = 'error'
    return
  }
  isCapturing.value = true
  const canvas = document.createElement('canvas')
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight
  const context = canvas.getContext('2d')
  if (!context) {
    isCapturing.value = false
    attendanceMessage.value = 'Unable to capture a photo. Please try again.'
    attendanceMessageType.value = 'error'
    return
  }
  context.drawImage(video, 0, 0, canvas.width, canvas.height)
  const faceImage = canvas.toDataURL('image/jpeg', 0.8)
  cancelFaceCapture()
  await clockIn(faceImage)
}

function cancelFaceCapture() {
  stopCamera()
  showFaceCapture.value = false
  cameraError.value = ''
  isCapturing.value = false
}

function showAttendanceMessage(message, type) {
  attendanceMessage.value = message
  attendanceMessageType.value = type
  if (messageTimeout) clearTimeout(messageTimeout)
  messageTimeout = setTimeout(() => {
    attendanceMessage.value = ''
  }, 5000)
}

async function clockIn(faceImage) {
  if (processing.value) return
  if (!userLocation.value) {
    await getUserLocation()
    if (!userLocation.value) {
      showAttendanceMessage('Location is required to clock in.', 'error')
      return
    }
  }
  processing.value = true
  try {
    const response = await axios.post('/api/staff/clock-in', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude,
      face_image: faceImage,
    }, { withCredentials: true })
    if (!response.data?.success && !response.data?.ok) {
      throw new Error(response.data?.message || 'Unable to clock in.')
    }
    showAttendanceMessage('Clocked in successfully.', 'success')
    await loadAttendanceStatus()
  } catch (error) {
    console.error('Admin attendance clock-in failed:', error)
    showAttendanceMessage(error.response?.data?.message || error.message || 'Unable to clock in.', 'error')
  } finally {
    processing.value = false
  }
}

async function clockOut() {
  if (processing.value || !attendanceStatus.value.is_clocked_in || !canClockOut.value) return
  if (!userLocation.value) {
    await getUserLocation()
    if (!userLocation.value) {
      showAttendanceMessage('Location is required to clock out.', 'error')
      return
    }
  }
  processing.value = true
  try {
    const response = await axios.post('/api/staff/clock-out', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude,
    }, { withCredentials: true })
    if (!response.data?.success && !response.data?.ok) {
      throw new Error(response.data?.message || 'Unable to clock out.')
    }
    showAttendanceMessage('Clocked out successfully.', 'success')
    await loadAttendanceStatus()
  } catch (error) {
    console.error('Admin attendance clock-out failed:', error)
    showAttendanceMessage(error.response?.data?.message || error.message || 'Unable to clock out.', 'error')
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  loadAttendanceStatus()
  loadAttendanceSettings()
  getUserLocation()
  locationInterval = window.setInterval(getUserLocation, 300000)
})

onBeforeUnmount(() => {
  stopCamera()
  if (locationInterval) window.clearInterval(locationInterval)
  if (messageTimeout) window.clearTimeout(messageTimeout)
})
</script>

<style scoped>
.admin-attendance-self {
  display: grid;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1.2rem;
  border: 1px solid #f1e5d8;
  border-radius: 16px;
  background: linear-gradient(135deg, #fffaf5 0%, #fff 72%);
  box-shadow: 0 8px 22px rgba(66, 33, 11, 0.06);
  color: #3d2a1f;
}

.admin-attendance-self__header,
.admin-attendance-self__body,
.admin-attendance-self__buttons,
.admin-attendance-self__camera-actions {
  display: flex;
  gap: 1rem;
}

.admin-attendance-self__header {
  align-items: center;
  justify-content: space-between;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(219, 188, 160, 0.4);
}

.admin-attendance-self h2,
.admin-attendance-self h3,
.admin-attendance-self p {
  margin: 0;
}

.admin-attendance-self h2 {
  font-size: 1.15rem;
  font-weight: 800;
}

.admin-attendance-self h3 {
  font-size: 0.95rem;
  font-weight: 800;
}

.admin-attendance-self__header p,
.admin-attendance-self__summary > div:first-child p,
.admin-attendance-self__actions > div:first-child p {
  margin-top: 0.3rem;
  color: #7c6758;
  font-size: 0.82rem;
  line-height: 1.45;
}

.admin-attendance-self__status {
  flex: 0 0 auto;
  padding: 0.4rem 0.75rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.is-on-duty {
  color: #15803d;
  background: rgba(34, 197, 94, 0.14);
}

.is-off-duty {
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.12);
}

.admin-attendance-self__body {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
  align-items: stretch;
}

.admin-attendance-self__summary,
.admin-attendance-self__actions {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 1rem;
  padding: 1.1rem;
  border: 1px solid rgba(219, 188, 160, 0.38);
  border-radius: 14px;
  background: linear-gradient(180deg, #fffdfa 0%, #fff7ef 100%);
}

.admin-attendance-self__times {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 0.7rem;
}

.admin-attendance-self__time {
  display: grid;
  min-height: 76px;
  align-content: center;
  gap: 0.45rem;
  padding: 0.85rem;
  border: 1px solid rgba(219, 188, 160, 0.34);
  border-radius: 11px;
  background: rgba(255, 255, 255, 0.82);
}

.admin-attendance-self__time span {
  color: #7c6758;
  font-size: 0.75rem;
  font-weight: 700;
}

.admin-attendance-self__time strong {
  color: #3d2a1f;
  font-size: 1rem;
  font-variant-numeric: tabular-nums;
}

.admin-attendance-self__buttons {
  gap: 0.65rem;
}

.admin-attendance-self__buttons button,
.admin-attendance-self__camera-actions button {
  flex: 1 1 9rem;
  min-height: 44px;
  padding: 0.7rem 0.85rem;
  border: 0;
  border-radius: 11px;
  color: #fff;
  background: #243447;
  font: inherit;
  font-size: 0.86rem;
  font-weight: 800;
  cursor: pointer;
}

.admin-attendance-self__buttons .admin-attendance-self__clock-out {
  background: #922f26;
}

.admin-attendance-self__buttons button:disabled,
.admin-attendance-self__camera-actions button:disabled {
  opacity: 0.58;
  cursor: not-allowed;
}

.admin-attendance-self__message {
  padding: 0.7rem 0.8rem;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 10px;
  color: #475569;
  background: rgba(255, 255, 255, 0.7);
  font-size: 0.82rem;
  line-height: 1.45;
}

.admin-attendance-self__message.is-success {
  border-color: rgba(34, 197, 94, 0.3);
  color: #15803d;
  background: rgba(34, 197, 94, 0.1);
}

.admin-attendance-self__message.is-warning {
  border-color: rgba(245, 158, 11, 0.35);
  color: #92400e;
  background: rgba(245, 158, 11, 0.1);
}

.admin-attendance-self__message.is-error {
  border-color: rgba(239, 68, 68, 0.3);
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.1);
}

.admin-attendance-self__backdrop {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: grid;
  place-items: center;
  overflow-y: auto;
  padding: clamp(12px, 4vw, 24px);
  background: rgba(15, 23, 42, 0.68);
  backdrop-filter: blur(4px);
}

.admin-attendance-self__camera-modal {
  width: min(100%, 540px);
  max-height: min(90vh, 760px);
  overflow-y: auto;
  padding: clamp(18px, 3vw, 28px);
  border: 1px solid #f1e5d8;
  border-top: 4px solid #f59e0b;
  border-radius: 18px;
  background: #fffaf5;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
}

.admin-attendance-self__camera-modal h2 {
  margin: 0;
  color: #3d2a1f;
  font-size: 1.35rem;
  text-align: center;
}

.admin-attendance-self__camera-modal > p {
  margin: 0.5rem 0 0;
  color: #7c6758;
  font-size: 0.85rem;
  line-height: 1.5;
  text-align: center;
}

.admin-attendance-self__camera {
  position: relative;
  aspect-ratio: 16 / 9;
  min-height: 220px;
  max-height: 52vh;
  margin-top: 1.1rem;
  overflow: hidden;
  border-radius: 12px;
  background: #111827;
}

.admin-attendance-self__camera video {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scaleX(-1);
}

.admin-attendance-self__camera p {
  position: absolute;
  top: 50%;
  left: 50%;
  width: min(90%, 360px);
  margin: 0;
  padding: 1rem;
  transform: translate(-50%, -50%);
  border-radius: 10px;
  background: rgba(185, 28, 28, 0.92);
  color: #fff;
  text-align: center;
}

.admin-attendance-self__camera-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1rem;
}

.admin-attendance-self__camera-actions button:not(.is-secondary) {
  border: 1px solid #c2410c;
  color: #fff;
  background: linear-gradient(135deg, #c2410c, #d97706);
}

.admin-attendance-self__camera-actions button:not(.is-secondary):hover:not(:disabled) {
  border-color: #9a3412;
  color: #fff;
  background: linear-gradient(135deg, #9a3412, #b45309);
}

.admin-attendance-self__camera-actions button:not(.is-secondary):disabled {
  border-color: #8b5e3c;
  color: #fff;
  background: #8b5e3c;
  opacity: 1;
}

.admin-attendance-self__camera-actions button.is-secondary {
  border: 1px solid #e2d8ce;
  color: #5b4638;
  background: #fff;
}

.admin-attendance-self__camera-actions .is-secondary {
  color: #5b4638;
}

@media (max-width: 760px) {
  .admin-attendance-self__body {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 520px) {
  .admin-attendance-self {
    padding: 0.9rem;
  }

  .admin-attendance-self__header {
    align-items: flex-start;
  }

  .admin-attendance-self__body {
    gap: 0.75rem;
  }

  .admin-attendance-self__backdrop {
    align-items: start;
  }

  .admin-attendance-self__camera-modal {
    max-height: calc(100vh - 24px);
  }

  .admin-attendance-self__camera {
    min-height: 190px;
  }

  .admin-attendance-self__camera-actions button {
    flex: 1 1 140px;
  }

  .admin-attendance-self__summary,
  .admin-attendance-self__actions {
    padding: 0.85rem;
  }

  .admin-attendance-self__times {
    grid-template-columns: 1fr;
  }

  .admin-attendance-self__buttons {
    flex-direction: column;
  }
}
</style>
