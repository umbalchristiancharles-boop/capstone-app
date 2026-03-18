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

      <!-- Orders Section (merged) -->
      <div class="panel-section">
        <h2 class="section-title">Your Orders</h2>
        <div v-if="ordersLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading orders...</p>
        </div>
        <div v-else class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Branch</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id">
                <td>{{ order.product?.name }}</td>
                <td>{{ order.branch?.name || order.branch_id }}</td>
                <td>{{ order.quantity }}</td>
                <td>{{ formatPrice(order.product?.price * order.quantity) }}</td>
                <td>
                  <span :class="['status-badge', getStatusClass(order.status)]">
                    {{ order.status }}
                  </span>
                </td>
                <td>
                  <button v-if="order.status === 'pending'" class="btn-primary btn-small" @click="fulfillOrder(order.id)">Fulfill</button>
                  <button v-else-if="order.status === 'fulfilled'" class="btn-secondary btn-small" disabled>Fulfilled</button>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="6" class="empty-message">No orders yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <logistics-panel-content :deliveries="deliveries" :suppliers="suppliers" @product-added="onProductAdded" />

          <section class="supplier-products">
            <h2>Your Products</h2>
            <div v-if="loadingProducts">Loading products...</div>
            <div v-else-if="!products.length">No products yet.</div>
            <div v-else class="product-grid">
              <div v-for="p in products" :key="p.id" class="product-card">
                <div class="product-name">{{ p.name }}</div>
                <div class="product-meta">
                  <div class="product-price">{{ formatPrice(p.price) }}</div>
                </div>
              </div>
            </div>
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
const orders = ref([])
const ordersLoading = ref(false)

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

  // load supplier orders for supplier user
  try {
    await loadOrders()
  } catch (e) { console.warn('Failed to load supplier orders', e) }

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

// Orders for supplier
async function loadOrders() {
  ordersLoading.value = true
  try {
    const res = await axios.get('/api/supplier-orders', { withCredentials: true })
    orders.value = res.data.data || res.data || []
    dashboardTotals.value.pendingOrders = orders.value.filter(o => o.status === 'pending').length
    // fulfilled count could be used elsewhere
  } catch (e) {
    console.error('Failed to load orders', e)
  } finally {
    ordersLoading.value = false
  }
}

async function fulfillOrder(id) {
  if (!confirm('Mark as fulfilled?')) return
  try {
    await axios.put(`/api/supplier-orders/${id}/status`, { status: 'fulfilled' }, { withCredentials: true })
    await loadOrders()
  } catch (e) {
    alert('Failed to update order')
  }
}

function getStatusClass(status) {
  switch (status) {
    case 'fulfilled': return 'status-approved'
    case 'cancelled': return 'status-rejected'
    default: return 'status-pending'
  }
}

function formatPrice(val) {
  if (val === null || val === undefined) return '₱0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '₱0.00'
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
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
/* Supplier panel product grid */
.overview-grid { display:flex; gap:0.75rem; margin-bottom:0.75rem }
.overview-card { background:#fff; border-radius:10px; padding:0.75rem 1rem; box-shadow:0 6px 18px rgba(15,23,42,0.04); border:1px solid #eef2f6; display:flex; gap:0.5rem; align-items:center }
.overview-label { color:#6b7280; font-weight:600 }
.overview-value { font-weight:700; color:#111827; margin-left:6px }

.supplier-products { margin-top:1rem }
.product-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap:0.75rem; margin-top:0.5rem }
.product-card { background:#fff; border-radius:10px; padding:0.75rem; box-shadow:0 8px 24px rgba(15,23,42,0.06); border:1px solid #eef2f6 }
.product-name { font-weight:700; color:#0f172a }
.product-meta { display:flex; justify-content:space-between; align-items:center; margin-top:6px }
.product-price { color:#0b6e3a; font-weight:700 }
.product-stock { color:#6b7280 }

</style>
