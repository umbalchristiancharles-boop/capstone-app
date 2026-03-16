<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Supplier Panel'"
    :panelDescription="'Manage suppliers, view deliveries, and monitor supplier performance.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Active Deliveries:</span><span class="overview-value">{{ dashboardTotals.activeDeliveries }}</span></div>
        <div class="overview-card"><span class="overview-label">Pending Orders:</span><span class="overview-value">{{ dashboardTotals.pendingOrders }}</span></div>
      </div>

      <logistics-panel-content :deliveries="deliveries" :suppliers="suppliers" />
    </template>
  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Supplier Panel?</h3>
        <p>This will end your current session for Chikin Tayo.</p>
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
import LogisticsPanelContent from './logistics/LogisticsPanelContent.vue'
import axios from 'axios'

const userProfile = ref({})
const dashboardTotals = ref({ totalSuppliers: 0, activeDeliveries: 0, pendingOrders: 0 })
const suppliers = ref([])
const deliveries = ref([])

// UI / modal state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
    userProfile.value = res.data.user
  } catch (e) {}

  try {
    const dash = await axios.get('/api/manager/logistics/dashboard', { withCredentials: true })
    if (dash && dash.data && typeof dash.data === 'object') dashboardTotals.value = {
      totalSuppliers: dash.data.totalSuppliers || dash.data.total_suppliers || 0,
      activeDeliveries: dash.data.activeDeliveries || dash.data.active_deliveries || 0,
      pendingOrders: dash.data.pendingOrders || dash.data.pending_orders || 0
    }
  } catch (e) {}

  try {
    const sres = await axios.get('/api/logistics/suppliers', { withCredentials: true })
    if (sres && sres.data) {
      if (Array.isArray(sres.data)) suppliers.value = sres.data
      else if (Array.isArray(sres.data.data)) suppliers.value = sres.data.data
      else suppliers.value = []
    }
  } catch (e) { console.warn('Failed to load suppliers', e) }

  try {
    const dres = await axios.get('/api/logistics/deliveries', { withCredentials: true })
    if (dres && dres.data) {
      if (Array.isArray(dres.data)) deliveries.value = dres.data
      else if (Array.isArray(dres.data.data)) deliveries.value = dres.data.data
      else deliveries.value = []
    }
  } catch (e) { console.warn('Failed to load deliveries', e) }
})

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
    try { window.location.replace('/staff-landing') } catch (e) { /* ignore */ }
  }, 600)
}

function onProfileUpdated(newData) {
  Object.assign(userProfile.value, newData)
}
</script>

<style scoped>
@import '../css/adminpanel.css';
</style>
