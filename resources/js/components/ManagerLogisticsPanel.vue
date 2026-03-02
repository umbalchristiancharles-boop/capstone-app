<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Manager Logistics Panel'"
    :panelDescription="'Track deliveries and manage suppliers.'"
    :enableProfileUpdate="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Active Deliveries:</span><span class="overview-value">{{ dashboardTotals.activeDeliveries }}</span></div>
        <div class="overview-card"><span class="overview-label">Suppliers:</span><span class="overview-value">{{ dashboardTotals.suppliers }}</span></div>
      </div>
      <logistics-panel-content :deliveries="deliveries" :suppliers="suppliers" />
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
import LogisticsPanelContent from './logistics/LogisticsPanelContent.vue'
import axios from 'axios'

// Logo image
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

const userProfile = ref({})
const dashboardTotals = ref({ activeDeliveries: 0, suppliers: 0 })
const deliveries = ref([])
const suppliers = ref([])

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
  const res = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const dash = await axios.get('/api/manager/logistics/dashboard', { withCredentials: true })
  dashboardTotals.value = dash.data
  const del = await axios.get('/api/manager/logistics/deliveries', { withCredentials: true })
  deliveries.value = del.data
  const supp = await axios.get('/api/manager/logistics/suppliers', { withCredentials: true })
  suppliers.value = supp.data
})
</script>
