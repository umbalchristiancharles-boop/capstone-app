<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Manager Finance Panel'"
    :panelDescription="'View financial reports, approve transactions, and analyze revenue.'"
    :enableProfileUpdate="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Total Sales:</span><span class="overview-value">{{ dashboardTotals.totalSales }}</span></div>
        <div class="overview-card"><span class="overview-label">Pending Approvals:</span><span class="overview-value">{{ dashboardTotals.pendingApprovals }}</span></div>
        <div class="overview-card"><span class="overview-label">Revenue:</span><span class="overview-value">{{ dashboardTotals.revenue }}</span></div>
      </div>
      <finance-panel-content :reports="financeReports" :transactions="transactions" />
    </template>
  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Manager Panel?</h3>
        <p>This will end your current session for Chikin Tayo Manager.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>

  <!-- FULLSCREEN LOADING OVERLAY -->
  <transition name="fade">
    <div v-if="showOverlay" class="loading-overlay">
      <div class="logo-loading-box">
        <img :src="logoImg" alt="Chikin Tayo" class="logo-loading-img" />
        <p>{{ overlayText }}</p>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import FinancePanelContent from './finance/FinancePanelContent.vue'
import axios from 'axios'

// Logo image
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

// Helper function to safely extract array from response
const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && response?.[key]?.data) return response[key].data
  return []
}

const userProfile = ref({})
const dashboardTotals = ref({ totalSales: 0, pendingApprovals: 0, revenue: 0 })
const financeReports = ref([])
const transactions = ref([])

// Handle profile update from layout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  overlayText.value = 'Logging out...'
  showOverlay.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/') } catch (e) { /* ignore */ }
  }, 600)
}

onMounted(async () => {
  const res = await axios.get('/api/manager/finance/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const dash = await axios.get('/api/manager/finance/dashboard', { withCredentials: true })
  dashboardTotals.value = dash.data
  const reports = await axios.get('/api/manager/finance/reports', { withCredentials: true })
  financeReports.value = extractArray(reports.data, 'reports')
  const tx = await axios.get('/api/manager/finance/transactions', { withCredentials: true })
  transactions.value = extractArray(tx.data, 'transactions')
})
</script>
