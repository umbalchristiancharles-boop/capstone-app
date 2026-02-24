<template>
  <OwnerPanelLayout :userProfile="userProfile" :panelTitle="'Manager Finance Panel'" :panelDescription="'View financial reports, approve transactions, and analyze revenue.'">
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Total Sales:</span><span class="overview-value">{{ dashboardTotals.totalSales }}</span></div>
        <div class="overview-card"><span class="overview-label">Pending Approvals:</span><span class="overview-value">{{ dashboardTotals.pendingApprovals }}</span></div>
        <div class="overview-card"><span class="overview-label">Revenue:</span><span class="overview-value">{{ dashboardTotals.revenue }}</span></div>
      </div>
      <finance-panel-content :reports="financeReports" :transactions="transactions" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import FinancePanelContent from './finance/FinancePanelContent.vue'
import axios from 'axios'

const userProfile = ref({})
const dashboardTotals = ref({ totalSales: 0, pendingApprovals: 0, revenue: 0 })
const financeReports = ref([])
const transactions = ref([])

onMounted(async () => {
  const res = await axios.get('/api/manager/finance/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const dash = await axios.get('/api/manager/finance/dashboard', { withCredentials: true })
  dashboardTotals.value = dash.data
  const reports = await axios.get('/api/manager/finance/reports', { withCredentials: true })
  financeReports.value = reports.data
  const tx = await axios.get('/api/manager/finance/transactions', { withCredentials: true })
  transactions.value = tx.data
})
</script>
