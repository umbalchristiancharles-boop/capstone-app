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
      <hr-panel-content :staffList="staffList" :hrReports="hrReports" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import HrPanelContent from './hr/HrPanelContent.vue'
import axios from 'axios'

const userProfile = ref({})
const dashboardTotals = ref({ totalStaff: 0, activeStaff: 0, onLeave: 0 })
const staffList = ref([])
const hrReports = ref([])

onMounted(async () => {
  const res = await axios.get('/api/manager/hr/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const dash = await axios.get('/api/manager/hr/dashboard', { withCredentials: true })
  dashboardTotals.value = dash.data
  const staff = await axios.get('/api/manager/hr/staff', { withCredentials: true })
  staffList.value = staff.data
  const reports = await axios.get('/api/manager/hr/reports', { withCredentials: true })
  hrReports.value = reports.data
})
</script>
