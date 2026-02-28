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
})

// Expose refresh function for child components
defineExpose({
  refreshAllData
})
</script>
