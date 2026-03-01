<template>
  <OwnerPanelLayout :userProfile="userProfile" :panelTitle="'Manager HR Panel'" :panelDescription="'Manage staff, view HR reports, and monitor staff status.'">
    <template #main>
      <!-- HR-specific dashboard cards -->
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Total Staff:</span><span class="overview-value">{{ dashboardTotals.totalStaff }}</span></div>
        <div class="overview-card"><span class="overview-label">Active Staff:</span><span class="overview-value">{{ dashboardTotals.activeStaff }}</span></div>
        <div class="overview-card"><span class="overview-label">On Leave:</span><span class="overview-value">{{ dashboardTotals.onLeave }}</span></div>
      </div>
      <!-- HR Reports, Staff Table, etc. -->
      <hr-panel-content :staffList="staffList" :hrReports="hrReports" @refresh="refreshAllData" />
    </template>

    <template #side>
      <!-- Attendance Settings Section -->
      <section class="panel-block">
        <div class="panel-header">
          <h2>Attendance Settings</h2>
        </div>

        <!-- Early Clock-Out Override Toggle for HR only -->
        <div class="attendance-override-toggle" v-if="userProfile.role === 'HR'">
          <div class="toggle-label">
            <span class="toggle-title">Enable Early Clock-Out</span>
            <span class="toggle-desc">Allow staff to clock out before scheduled time</span>
          </div>
          <label class="toggle-switch">
            <input
              type="checkbox"
              v-model="earlyClockoutOverride"
              @change="toggleEarlyClockout"
              :disabled="isTogglingOverride"
            >
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="panel-body panel-body--list">
          <div class="side-item">
            <span>View and manage staff attendance records</span>
          </div>
        </div>
      </section>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import HrPanelContent from './hr/HrPanelContent.vue'
import axios from 'axios'

const router = useRouter()
const errorMessage = ref('')

// Helper function to safely extract array from response
const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && response?.[key]?.data) return response[key].data
  return []
}

const userProfile = ref({})
const dashboardTotals = ref({ totalStaff: 0, activeStaff: 0, onLeave: 0 })
const staffList = ref([])
const hrReports = ref([])

// Early clock-out override toggle
const earlyClockoutOverride = ref(false)
const isTogglingOverride = ref(false)

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      earlyClockoutOverride.value = res.data.data.early_clockout_override || false
    }
  } catch (e) {
    console.error('Failed to load attendance settings:', e)
  }
}

async function toggleEarlyClockout() {
  isTogglingOverride.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.patch('/api/attendance/override', {
      early_clockout_override: earlyClockoutOverride.value
    }, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert(res.data.message || 'Settings updated successfully')
    } else {
      earlyClockoutOverride.value = !earlyClockoutOverride.value
      alert(res.data.message || 'Failed to update settings')
    }
  } catch (e) {
    earlyClockoutOverride.value = !earlyClockoutOverride.value
    alert(e.response?.data?.message || 'Error updating settings')
  } finally {
    isTogglingOverride.value = false
  }
}

// Function to refresh all data
async function refreshAllData() {
  errorMessage.value = ''

  try {
    const dash = await axios.get('/api/manager/hr/dashboard', { withCredentials: true })
    dashboardTotals.value = dash.data
  } catch (err) {
    errorMessage.value = 'Failed to load dashboard data.'
  }

  try {
    const staff = await axios.get('/api/manager/hr/staff', { withCredentials: true })
    staffList.value = extractArray(staff.data, 'staffList')
  } catch (err) {
    staffList.value = []
    errorMessage.value = 'Failed to load staff list.'
  }

  try {
    const reports = await axios.get('/api/manager/hr/reports', { withCredentials: true })
    hrReports.value = extractArray(reports.data, 'hrReports')
  } catch (err) {
    hrReports.value = []
    errorMessage.value = 'Failed to load HR reports.'
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/hr/profile', { withCredentials: true })
    userProfile.value = res.data.user
  } catch (err) {
    if (err.response && err.response.status === 401) {
      // Not authenticated, redirect to login
      router.push('/login')
      return
    } else {
      errorMessage.value = 'Failed to load profile. Please try again later.'
      return
    }
  }

  await refreshAllData()
  // Load attendance settings after profile is loaded
  loadAttendanceSettings()
})

// Expose refresh function for child components
defineExpose({
  refreshAllData
})
</script>
