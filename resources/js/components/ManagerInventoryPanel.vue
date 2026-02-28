<template>
  <OwnerPanelLayout :userProfile="userProfile" :panelTitle="'Manager Inventory Panel'" :panelDescription="'Monitor stock, add/update products, and view inventory reports.'" @logout="showLogoutConfirm = true">
    <template #profileFooter>
      <button class="admin-info-btn admin-info-btn--center" @click="showInfoModal = true">Info</button>
    </template>
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Total Products:</span><span class="overview-value">{{ dashboardTotals.totalProducts }}</span></div>
        <div class="overview-card"><span class="overview-label">Low Stock:</span><span class="overview-value">{{ dashboardTotals.lowStock }}</span></div>
        <div class="overview-card"><span class="overview-label">Stock Value:</span><span class="overview-value">{{ dashboardTotals.stockValue }}</span></div>
      </div>
      <inventory-panel-content :products="products" :reports="inventoryReports" />
    </template>
  </OwnerPanelLayout>

  <!-- INFO MODAL (view/edit manager profile) -->
  <transition name="fade">
    <div v-if="showInfoModal" class="info-backdrop">
      <div class="info-modal">
        <h3>Manager Information</h3>
        <p class="info-sub">Update your personal details from this panel.</p>

        <div class="info-grid">
          <div class="info-row"><span class="info-label">Full name</span><span class="info-value" v-if="!isEditingInfo">{{ userProfile.fullName }}</span>
            <input v-else v-model="userProfile.fullName" class="info-input" type="text" />
          </div>

          <div class="info-row"><span class="info-label">Role</span><span class="info-value">{{ userProfile.role }}</span></div>

          <div class="info-row"><span class="info-label">Username</span><span class="info-value" v-if="!isEditingInfo">{{ userProfile.username }}</span>
            <input v-else v-model="userProfile.username" class="info-input" type="text" placeholder="Enter username" />
          </div>

          <div class="info-row"><span class="info-label">Email</span><span class="info-value" v-if="!isEditingInfo">{{ userProfile.email }}</span>
            <input v-else v-model="userProfile.email" class="info-input" type="email" />
          </div>

          <div class="info-row"><span class="info-label">Contact</span><span class="info-value" v-if="!isEditingInfo">{{ userProfile.contact }}</span>
            <input v-else v-model="userProfile.contact" class="info-input" type="text" />
          </div>

          <div class="info-row"><span class="info-label">Branch</span><span class="info-value">{{ typeof userProfile.branch === 'object' && userProfile.branch.name ? userProfile.branch.name : (userProfile.branch || 'Not assigned') }}</span></div>

          <template v-if="isEditingInfo">
            <div class="info-row info-row--password">
              <span class="info-label">New Password</span>
              <input v-model="userProfile.password" class="info-input" type="password" placeholder="Leave blank to keep current" />
            </div>

            <div class="info-row info-row--password">
              <span class="info-label">Confirm Password</span>
              <input v-model="userProfile.password_confirmation" class="info-input" type="password" placeholder="Re-enter new password" />
            </div>
          </template>
        </div>

        <div v-if="profileError" class="info-error">{{ profileError }}</div>
        <div v-if="profileSuccess" class="info-success">{{ profileSuccess }}</div>

        <div class="info-actions">
          <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>
          <button class="btn-primary" @click="isEditingInfo ? saveManagerInfo() : (isEditingInfo = true)" :disabled="isSavingProfile">
            {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
          </button>
        </div>
      </div>
    </div>
  </transition>

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
import InventoryPanelContent from './inventory/InventoryPanelContent.vue'
import axios from 'axios'

const userProfile = ref({})
const dashboardTotals = ref({ totalProducts: 0, lowStock: 0, stockValue: 0 })
const products = ref([])
const inventoryReports = ref([])

// UI / modal state
const showInfoModal = ref(false)
const isEditingInfo = ref(false)
const profileError = ref('')
const profileSuccess = ref('')
const isSavingProfile = ref(false)

const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/inventory/profile', { withCredentials: true })
    userProfile.value = res.data.user
  } catch (e) {}

  try {
    const dash = await axios.get('/api/manager/inventory/dashboard', { withCredentials: true })
    if (dash && dash.data && typeof dash.data === 'object') dashboardTotals.value = dash.data
  } catch (e) {}

  try {
    const prods = await axios.get('/api/manager/inventory/products', { withCredentials: true })
    if (prods && prods.data) {
      if (typeof prods.data === 'string' && prods.data.trim().toLowerCase().startsWith('<!doctype html')) {
        console.warn('Products API returned HTML — likely unauthorized or wrong route, redirecting to login')
        try { sessionStorage.setItem('skipRouteOverlay', '1') } catch (e) {}
        window.location.replace('/admin-login')
        return
      }
      if (Array.isArray(prods.data)) {
        products.value = prods.data
      } else if (Array.isArray(prods.data.data)) {
        products.value = prods.data.data
      } else {
        console.warn('Unexpected products response', prods.data)
        products.value = []
      }
    }
  } catch (e) { console.warn('Failed to load products', e) }

  try {
    const reports = await axios.get('/api/manager/inventory/reports', { withCredentials: true })
    if (reports && reports.data) {
      if (typeof reports.data === 'string' && reports.data.trim().toLowerCase().startsWith('<!doctype html')) {
        console.warn('Reports API returned HTML — likely unauthorized or wrong route, redirecting to login')
        try { sessionStorage.setItem('skipRouteOverlay', '1') } catch (e) {}
        window.location.replace('/admin-login')
        return
      }
      if (Array.isArray(reports.data)) {
        inventoryReports.value = reports.data
      } else if (Array.isArray(reports.data.data)) {
        inventoryReports.value = reports.data.data
      } else {
        console.warn('Unexpected reports response', reports.data)
        inventoryReports.value = []
      }
    }
  } catch (e) { console.warn('Failed to load reports', e) }
})

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false
  } else {
    showInfoModal.value = false
  }
}

async function saveManagerInfo() {
  isSavingProfile.value = true
  profileError.value = ''
  profileSuccess.value = ''
  try {
    const payload = {
      fullName: userProfile.value.fullName,
      email: userProfile.value.email,
      contact: userProfile.value.contact,
      password: userProfile.value.password || undefined,
      password_confirmation: userProfile.value.password_confirmation || undefined,
    }
    await axios.put('/api/manager/inventory/profile', payload, { withCredentials: true })
    profileSuccess.value = 'Profile updated.'
    isEditingInfo.value = false
  } catch (err) {
    profileError.value = err.response?.data?.message || 'Failed to save profile.'
  }
  isSavingProfile.value = false
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

function onProfileUpdated(newData) {
  Object.assign(userProfile.value, newData)
}
</script>
