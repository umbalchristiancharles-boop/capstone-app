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

          <logistics-panel-content :deliveries="deliveries" :suppliers="suppliers" @product-added="onProductAdded" />

          <section class="supplier-products">
            <h2>Your Products</h2>
            <div v-if="loadingProducts">Loading products...</div>
            <div v-else-if="!products.length">No products yet.</div>
            <ul v-else>
              <li v-for="p in products" :key="p.id">{{ p.name }} — ₱{{ p.price }}</li>
            </ul>
          </section>
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
const deliveries = ref([])
const products = ref([])
const loadingProducts = ref(false)

// UI / modal state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

onMounted(async () => {
  try {
    // Try /api/me then /api/profile then manager profile as last resort
    let res = null
    try {
      res = await axios.get('/api/me', { withCredentials: true })
    } catch (e) {
      try {
        res = await axios.get('/api/profile', { withCredentials: true })
      } catch (e2) {
        try {
          res = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
        } catch (e3) {
          res = null
        }
      }
    }

    if (res && res.data) {
      // Debug: log raw profile response to help diagnose missing fields
      try { console.debug('profile response', res.data) } catch (e) {}

      const raw = res.data.user || res.data || {}

      // Normalize user profile fields to what OwnerPanelLayout expects
      const normalized = {
        id: raw.id,
        username: raw.username || raw.user_name || raw.user || null,
        fullName: raw.fullName || raw.full_name || raw.name || raw.username || null,
        full_name: raw.fullName || raw.full_name || raw.name || raw.username || null,
        role: (raw.role || raw.user_role || raw.type || '') ? String(raw.role || raw.user_role || raw.type) : null,
        email: raw.email || null,
        contact: raw.contact || raw.phone_number || raw.phone || null,
        branch_id: raw.branch_id || raw.branch || null,
        accountId: raw.accountId || raw.account_id || (raw.id ? 'kk' + String(raw.id).padStart(5, '0') : null),
        avatarUrl: (raw.avatarUrl || raw.avatar_url) ? (raw.avatarUrl || raw.avatar_url) : null,
      }

      userProfile.value = normalized
    }
  } catch (e) {}

  try {
    // Only request manager/logistics dashboard if user has a manager/admin role
    const roleUpper = (userProfile.value.role || '').toString().toUpperCase()
    const managerRoles = ['MANAGER', 'MANAGER_HR', 'OWNER', 'ADMIN', 'SUPER_ADMIN']
    if (managerRoles.includes(roleUpper)) {
      const dash = await axios.get('/api/manager/logistics/dashboard', { withCredentials: true })
      if (dash && dash.data && typeof dash.data === 'object') dashboardTotals.value = {
        totalSuppliers: dash.data.totalSuppliers || dash.data.total_suppliers || 0,
        activeDeliveries: dash.data.activeDeliveries || dash.data.active_deliveries || 0,
        pendingOrders: dash.data.pendingOrders || dash.data.pending_orders || 0
      }
    }
  } catch (e) {}

  try {
    // Only load suppliers/deliveries when manager/admin role
    const roleUpper = (userProfile.value.role || '').toString().toUpperCase()
    const managerRoles = ['MANAGER', 'MANAGER_HR', 'OWNER', 'ADMIN', 'SUPER_ADMIN']
    if (managerRoles.includes(roleUpper)) {
        // suppliers list removed from supplier panel UI

      try {
        const dres = await axios.get('/api/logistics/deliveries', { withCredentials: true })
        if (dres && dres.data) {
          if (Array.isArray(dres.data)) deliveries.value = dres.data
          else if (Array.isArray(dres.data.data)) deliveries.value = dres.data.data
          else deliveries.value = []
        }
      } catch (e) { console.warn('Failed to load deliveries', e) }
    }
  } catch (e) { console.warn('Failed to determine role for loading logistics data', e) }

  // Load products for the current user's branch (show supplier products)
  try {
    if (userProfile.value && (userProfile.value.branch_id || userProfile.value.id)) {
      console.debug('Loading products for user', userProfile.value)
      await loadProducts()
    }
  } catch (e) { console.warn('Failed to load supplier products', e) }
})

async function loadProducts() {
  loadingProducts.value = true
  try {
    const pres = await axios.get('/api/staff/inventory/products', { withCredentials: true })
    if (pres && pres.data) {
      if (Array.isArray(pres.data)) products.value = pres.data
      else if (Array.isArray(pres.data.data)) products.value = pres.data.data
      else products.value = []
    }
  } catch (e) {
    console.warn('Failed to load products', e)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

function onProductAdded(newProduct) {
  // If we already have products loaded, add the new one at top; otherwise try reloading
  try {
    if (products.value && Array.isArray(products.value)) {
      products.value.unshift(newProduct)
    } else {
      loadProducts()
    }
  } catch (e) {
    loadProducts()
  }
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
