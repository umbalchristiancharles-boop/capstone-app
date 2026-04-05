<template>
  <!-- Main Branch Finance Panel wraps ManagerFinancePanel for /main-branch/finance route -->
  <!-- Main Branch Finance Manager sees aggregated data from ALL branches -->
  <div class="main-branch-finance-panel">
    <div class="content-area">
      <ManagerFinancePanel :isMainBranchFinance="true" />
    </div>

    <aside class="sidebar">
      <section class="panel-block announcements-panel">
        <div class="panel-header announcements-header">
          <h2>Announcements</h2>
        </div>
        <div class="panel-body">
          <div v-if="loadingAnnouncements" style="text-align:center; padding:12px; color:#9ca3af;">Loading...</div>
          <div v-else-if="announcements.length === 0" style="text-align:center; padding:12px; color:#9ca3af;">No announcements</div>
          <ul v-else class="announcement-list">
            <li v-for="a in announcements" :key="a.id" class="announcement-item">
              <div class="announcement-title">{{ a.title }}</div>
              <div class="announcement-meta">{{ new Date(a.created_at).toLocaleString() }} • {{ a.target }}</div>
              <div class="announcement-message">{{ a.message }}</div>
            </li>
          </ul>
        </div>
      </section>

      <div v-if="!hideAttendanceCard" class="attendance-card" style="margin-top:12px;">
        <div class="attendance-header">
          <span class="attendance-title">Attendance</span>
          <span :class="['attendance-status-badge', attendanceStatus.is_clocked_in ? 'status-on-duty' : 'status-off-duty']">
            {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
          </span>
        </div>
        <div class="attendance-buttons">
          <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing" class="btn-clock-in">{{ isAttendanceProcessing ? '...' : 'Clock In' }}</button>
          <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">{{ isAttendanceProcessing ? '...' : 'Clock Out' }}</button>
        </div>
        <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction"><span class="restriction-icon">🔒</span><span>Cannot clock out before {{ scheduledTimeOut }}</span></div>
        <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ManagerFinancePanel from './ManagerFinancePanel.vue'

const announcements = ref([])
const loadingAnnouncements = ref(false)
const attendanceStatus = ref({ is_clocked_in: false, clock_in_time: null, clock_out_time: null, hours_worked: 0 })
const isAttendanceProcessing = ref(false)
const attendanceMessage = ref('')
const attendanceMessageType = ref('')
const attendanceSettings = ref({ early_clockout_override: false, scheduled_time_out: '17:00:00' })

const scheduledTimeOut = computed(() => {
  const time = attendanceSettings.value.scheduled_time_out || '17:00:00'
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour % 12 || 12
  return `${hour12}:${minutes} ${ampm}`
})

const canClockOut = computed(() => {
  if (!attendanceStatus.value.is_clocked_in) return false
  if (attendanceSettings.value.early_clockout_override) return true
  const now = new Date()
  const currentTotalMinutes = now.getHours() * 60 + now.getMinutes()
  const [scheduledHours, scheduledMinutes] = (attendanceSettings.value.scheduled_time_out || '17:00:00').split(':')
  const scheduledTotalMinutes = parseInt(scheduledHours) * 60 + parseInt(scheduledMinutes)
  return currentTotalMinutes >= scheduledTotalMinutes
})

const hideAttendanceCard = computed(() => {
  try {
    return new URLSearchParams(window.location.search).get('from') === 'custom-panel'
  } catch (e) {
    return false
  }
})

async function loadAttendanceStatus() {
  try {
    const res = await axios.get('/api/manager/attendance/status', { withCredentials: true })
    if (res.data && res.data.success) {
      attendanceStatus.value = {
        is_clocked_in: !!res.data.clocked_in,
        clock_in_time: res.data.time_in || res.data.status?.clock_in_time || null,
        clock_out_time: res.data.time_out || res.data.status?.clock_out_time || null,
        hours_worked: res.data.status?.hours_worked || 0
      }
    }
  } catch (e) {
    // ignore
  }
}

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      attendanceSettings.value = {
        early_clockout_override: res.data.data.early_clockout_override || false,
        scheduled_time_out: res.data.data.scheduled_time_out || '17:00:00'
      }
    }
  } catch (e) {
    // ignore
  }
}

async function performClockIn() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''
  try {
    const res = await axios.post('/api/manager/clock-in', {}, { withCredentials: true })
    if (res.data && res.data.success) {
      attendanceMessage.value = 'Clocked in successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking in'
    attendanceMessageType.value = 'error'
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

async function performClockOut() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''
  try {
    const res = await axios.post('/api/manager/clock-out', {}, { withCredentials: true })
    if (res.data && res.data.success) {
      attendanceMessage.value = 'Clocked out successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking out'
    attendanceMessageType.value = 'error'
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

const loadAnnouncements = async () => {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    if (res.data && res.data.ok) {
      announcements.value = res.data.announcements || []
    }
  } catch (e) {
    console.error('Failed to load announcements:', e)
  } finally {
    loadingAnnouncements.value = false
  }
}

onMounted(() => {
  loadAnnouncements()
  if (!hideAttendanceCard.value) {
    loadAttendanceStatus()
    loadAttendanceSettings()
  }
})
</script>

<style scoped>
.main-branch-finance-panel {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 20px;
  position: relative;
}

.announcements-panel {
  background: #fff;
  border-radius: 8px;
  padding: 12px;
  box-shadow: 0 1px 2px rgba(16,24,40,0.05);
  max-width: 380px;
}

.content-area {
  flex: 1 1 auto;
}

.sidebar {
  width: 320px;
  display: block;
}

/* Position the sidebar to the right of the centered main content so it
   appears in the gap between main content and the outer admin-side column. */
@media (min-width: 1100px) {
  .main-branch-finance-panel .sidebar {
    position: absolute;
    top: 120px; /* aligns roughly with top of KPI area — adjust if needed */
    right: 20px;
    /* keep it visually in the gap — reduce overlap on narrow screens */
  }
  .main-branch-finance-panel .announcements-panel {
    position: relative;
    width: 320px;
  }
}

.announcements-header h2 {
  margin: 0 0 8px 0;
  font-size: 1.1rem;
}

.announcement-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.announcement-item {
  border-bottom: 1px solid #f3f4f6;
  padding-bottom: 8px;
}

.announcement-title {
  font-weight: 700;
}

.announcement-meta {
  color: #6b7280;
  font-size: 0.85rem;
  margin: 4px 0;
}

.announcement-message {
  color: #374151;
}

.attendance-card {
  background: #ffffff;
  border-radius: 8px;
  padding: 12px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 2px rgba(16,24,40,0.05);
}

.attendance-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.attendance-title {
  font-weight: 700;
  color: #1f2937;
  font-size: 0.95rem;
}

.attendance-status-badge {
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
}

.status-on-duty {
  background: #d4edda;
  color: #155724;
}

.status-off-duty {
  background: #f8d7da;
  color: #721c24;
}

.attendance-buttons {
  display: flex;
  gap: 8px;
}

.btn-clock-in,
.btn-clock-out {
  flex: 1;
  padding: 8px 10px;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-clock-in {
  background: #16a34a;
  color: white;
}

.btn-clock-in:hover:not(:disabled) {
  background: #15803d;
}

.btn-clock-in:disabled {
  background: #d1d5db;
  cursor: not-allowed;
  opacity: 0.7;
}

.btn-clock-out {
  background: #dc2626;
  color: white;
}

.btn-clock-out:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-clock-out:disabled {
  background: #d1d5db;
  cursor: not-allowed;
  opacity: 0.7;
}

.btn-disabled {
  background: #9ca3af !important;
  cursor: not-allowed !important;
}

.clockout-restriction {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 6px;
  background: #fff3cd;
  border: 1px solid #facc15;
  border-radius: 6px;
  color: #7c5b00;
  font-size: 0.7rem;
  margin-top: 8px;
}

.restriction-icon {
  font-size: 0.85rem;
  font-weight: 700;
}

.attendance-message {
  padding: 6px;
  border-radius: 4px;
  text-align: center;
  font-size: 0.75rem;
  font-weight: 600;
  margin-top: 8px;
}

.attendance-message.success {
  background: #d4edda;
  color: #155724;
}

.attendance-message.error {
  background: #f8d7da;
  color: #721c24;
}

@media (max-width: 900px) {
  .main-branch-finance-panel {
    flex-direction: column;
  }
  .sidebar {
    width: 100%;
    justify-content: stretch;
  }
  .announcements-panel {
    position: static;
  }
}
</style>
