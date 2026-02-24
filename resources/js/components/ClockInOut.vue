<template>
  <div class="clock-page">
    <!-- Clock Status Card -->
    <div class="clock-card">
      <div class="clock-display">
        <div class="current-time">{{ currentTime }}</div>
        <div class="current-date">{{ currentDate }}</div>
      </div>

      <div v-if="status" class="status-info">
        <div class="status-row">
          <span class="label">Clock In Time:</span>
          <span class="value">{{ status.clock_in_time || 'Not yet' }}</span>
        </div>
        <div class="status-row">
          <span class="label">Clock Out Time:</span>
          <span class="value">{{ status.clock_out_time || 'Not yet' }}</span>
        </div>
        <div class="status-row">
          <span class="label">Hours Worked:</span>
          <span class="value">{{ status.hours_worked }} hrs</span>
        </div>
        <div class="status-row">
          <span class="label">Status:</span>
          <span :class="['badge', status.is_clocked_in ? 'badge-active' : 'badge-inactive']">
            {{ status.is_clocked_in ? 'On Duty' : 'Off Duty' }}
          </span>
        </div>
      </div>

      <div class="button-group">
        <button
          @click="performClockIn"
          :disabled="status?.is_clocked_in || isProcessing"
          class="btn-clock-in"
        >
          <span v-if="!isProcessing">Clock In</span>
          <span v-else>Processing...</span>
        </button>
        <button
          @click="performClockOut"
          :disabled="!status?.is_clocked_in || isProcessing"
          class="btn-clock-out"
        >
          <span v-if="!isProcessing">Clock Out</span>
          <span v-else>Processing...</span>
        </button>
      </div>

      <div v-if="message" :class="['alert', messageType]">
        {{ message }}
      </div>
    </div>

    <!-- Attendance History -->
    <div class="history-section">
      <h2>Attendance History</h2>

      <div v-if="historyLoading" class="loading-state">
        <p>Loading history...</p>
      </div>

      <div v-else-if="history.length > 0" class="history-table-wrapper">
        <table class="history-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Clock In</th>
              <th>Clock Out</th>
              <th>Hours Worked</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="record in history" :key="record.date">
              <td>{{ record.date }}</td>
              <td>{{ record.clock_in }}</td>
              <td>{{ record.clock_out || '-' }}</td>
              <td>{{ record.hours_worked }}</td>
              <td>
                <span :class="['badge', 'badge-' + record.status]">
                  {{ formatStatus(record.status) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="empty-state">
        <p>No attendance records yet</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import '../css/adminpanel.css'

// State
const currentTime = ref('')
const currentDate = ref('')
const status = ref(null)
const history = ref([])
const isProcessing = ref(false)
const historyLoading = ref(false)
const message = ref('')
const messageType = ref('')

// Clock Update Interval
let clockInterval = null

// Methods
function updateClock() {
  const now = new Date()

  currentTime.value = now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  })

  currentDate.value = now.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function showMessage(text, type = 'success') {
  message.value = text
  messageType.value = `alert-${type}`
  setTimeout(() => {
    message.value = ''
  }, 3000)
}

async function loadStatus() {
  try {
    const res = await axios.get('/api/staff/attendance/status', {
      withCredentials: true
    })

    if (res.data.success) {
      status.value = res.data.status
    }
  } catch (error) {
    console.error('Status load error:', error)
  }
}

async function loadHistory() {
  historyLoading.value = true

  try {
    const res = await axios.get('/api/staff/attendance/history?limit=10', {
      withCredentials: true
    })

    if (res.data.success) {
      history.value = res.data.history
    }
  } catch (error) {
    console.error('History load error:', error)
  } finally {
    historyLoading.value = false
  }
}

async function performClockIn() {
  if (isProcessing.value) return

  isProcessing.value = true

  try {
    const res = await axios.post('/api/staff/clock-in', {}, {
      withCredentials: true
    })

    if (res.data.success) {
      showMessage('Clocked in successfully! ✓', 'success')
      await loadStatus()
    } else {
      showMessage(res.data.message || 'Failed to clock in', 'danger')
    }
  } catch (error) {
    console.error('Clock in error:', error)
    showMessage('Error clocking in. Please try again.', 'danger')
  } finally {
    isProcessing.value = false
  }
}

async function performClockOut() {
  if (isProcessing.value) return

  isProcessing.value = true

  try {
    const res = await axios.post('/api/staff/clock-out', {}, {
      withCredentials: true
    })

    if (res.data.success) {
      showMessage('Clocked out successfully! ✓', 'success')
      await loadStatus()
      await loadHistory()
    } else {
      showMessage(res.data.message || 'Failed to clock out', 'danger')
    }
  } catch (error) {
    console.error('Clock out error:', error)
    showMessage('Error clocking out. Please try again.', 'danger')
  } finally {
    isProcessing.value = false
  }
}

function formatStatus(status) {
  const labels = {
    'completed': 'Completed',
    'pending': 'Pending',
    'on_duty': 'On Duty',
    'off_duty': 'Off Duty',
  }
  return labels[status] || status
}

onMounted(() => {
  // Update clock immediately and then every second
  updateClock()
  clockInterval = setInterval(updateClock, 1000)

  // Load data
  loadStatus()
  loadHistory()

  // Refresh status every 30 seconds
  setInterval(loadStatus, 30000)
})

onUnmounted(() => {
  if (clockInterval) {
    clearInterval(clockInterval)
  }
})
</script>

<style scoped>
.clock-page {
  padding: 2rem;
  background: #f5f5f5;
  min-height: 100vh;
}

.clock-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  padding: 2rem;
  margin-bottom: 2rem;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.clock-display {
  text-align: center;
  padding: 2rem 0;
  border-bottom: 2px solid #eee;
  margin-bottom: 2rem;
}

.current-time {
  font-size: 3.5rem;
  font-weight: 700;
  color: #FF9A4A;
  letter-spacing: 2px;
  font-family: 'Courier New', monospace;
}

.current-date {
  font-size: 1.1rem;
  color: #666;
  margin-top: 0.5rem;
}

.status-info {
  margin-bottom: 2rem;
}

.status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #eee;
}

.status-row:last-child {
  border-bottom: none;
}

.status-row .label {
  color: #666;
  font-weight: 500;
}

.status-row .value {
  color: #333;
  font-weight: 600;
}

.badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.badge-active {
  background: #d4edda;
  color: #155724;
}

.badge-inactive {
  background: #f8d7da;
  color: #721c24;
}

.badge-completed {
  background: #d4edda;
  color: #155724;
}

.badge-pending {
  background: #fff3cd;
  color: #856404;
}

.button-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin: 2rem 0;
}

.btn-clock-in,
.btn-clock-out {
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.btn-clock-in {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.btn-clock-in:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-clock-in:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-clock-out {
  background: linear-gradient(135deg, #dc3545, #ff6b6b);
  color: white;
}

.btn-clock-out:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-clock-out:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.alert {
  padding: 1rem;
  border-radius: 4px;
  text-align: center;
  font-weight: 500;
  animation: slideIn 0.3s ease;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-danger {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.history-section {
  max-width: 900px;
  margin: 0 auto;
}

.history-section h2 {
  color: #333;
  margin-bottom: 1.5rem;
}

.history-table-wrapper {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
}

.history-table thead {
  background: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
}

.history-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #333;
  font-size: 0.9rem;
}

.history-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.history-table tbody tr:hover {
  background: #f8f9fa;
}

.empty-state,
.loading-state {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
  color: #666;
}

@media (max-width: 768px) {
  .clock-page {
    padding: 1rem;
  }

  .clock-card {
    padding: 1.5rem;
  }

  .current-time {
    font-size: 2.5rem;
  }

  .button-group {
    grid-template-columns: 1fr;
  }

  .history-table {
    font-size: 0.85rem;
  }

  .history-table th,
  .history-table td {
    padding: 0.75rem 0.5rem;
  }
}
</style>
