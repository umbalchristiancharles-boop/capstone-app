<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Staff Inventory'"
    :panelDescription="'Manage branch inventory, track stock levels, and confirm deliveries.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Total Products:</span><span class="overview-value">{{ dashboardTotals.totalProducts }}</span></div>
        <div class="overview-card"><span class="overview-label">Low Stock:</span><span class="overview-value">{{ dashboardTotals.lowStock }}</span></div>
        <div class="overview-card"><span class="overview-label">Stock Value:</span><span class="overview-value">{{ dashboardTotals.stockValue }}</span></div>
      </div>
      <div class="inventory-content">
        <!-- Product List Section -->
        <div class="section">
          <h2>Product List</h2>
          <div v-if="!products.length" class="empty-message">No products found.</div>
          <div v-else>
            <div v-for="cat in productCategories" :key="cat" class="category-section">
              <h3 class="category-title">{{ cat || 'Uncategorized' }}</h3>
              <table class="products-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Price (PHP)</th>
                    <th>Stock</th>
                    <th>Min Stock</th>
                    <th>Expires</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="product in getProductsByCategory(cat)" :key="product.id" :class="{ expired: isProductExpired(product) }">
                    <td>{{ product.name }}</td>
                    <td>{{ product.sku }}</td>
                    <td>{{ formatPrice(product.price) }}</td>
                    <td>{{ product.real_stock ?? product.stock }}</td>
                    <td>{{ product.min_stock }}</td>
                    <td>
                      <span v-if="product.expires_at" :class="getExpiryClass(product)">{{ formatDate(product.expires_at) }}</span>
                      <span v-else class="expiry-none">—</span>
                    </td>
                    <td>
                      <span v-if="product.is_out_of_stock" class="status-badge status-out">Out of Stock</span>
                      <span v-else-if="product.is_low_stock" class="status-badge status-low">Low Stock</span>
                      <span v-else class="status-badge status-ok">OK</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Pending Stock Confirmations Section -->
        <div class="section" :class="{ 'stat-alert': inventoryPendingCount > 0 }">
          <h2>
            Pending Stock Confirmations
            <span v-if="inventoryPendingCount > 0" class="panel-badge">{{ inventoryPendingCount }}</span>
          </h2>
          <div v-if="!pendingProcurements.length" class="empty-message">No pending confirmations.</div>
          <table v-else class="procurements-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Uploaded</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="proc in pendingProcurements" :key="proc.id">
                <td>{{ proc.id }}</td>
                <td>{{ proc.product_name || 'Unknown' }}</td>
                <td>{{ proc.quantity }}</td>
                <td>{{ formatDate(proc.created_at) }}</td>
                <td><button class="btn-primary" @click="openConfirmModal(proc)">Confirm Stock</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Confirmed Stock History Section -->
        <div class="section">
          <h2>Confirmed Stock History</h2>
          <div v-if="!confirmedProcurements.length" class="empty-message">No confirmed stock history.</div>
          <table v-else class="procurements-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Confirmed By</th>
                <th>When</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="proc in confirmedProcurements" :key="proc.id">
                <td>{{ proc.id }}</td>
                <td>{{ proc.product_name || 'Unknown' }}</td>
                <td>{{ proc.quantity }}</td>
                <td>{{ proc.confirmed_by || '-' }}</td>
                <td>{{ formatDate(proc.confirmed_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Inventory Reports Section -->
        <div class="section">
          <h2>Inventory Reports</h2>
          <div v-if="!inventoryReports.length" class="empty-message">No inventory reports available.</div>
          <div v-else class="reports-list">
            <div v-for="report in inventoryReports" :key="report.id" class="report-card">
              <h3>{{ report.title }}</h3>
              <p class="report-type">{{ report.type }}</p>
              <div v-if="report.summary" class="report-summary">
                <div>Total Products: {{ report.summary.total_products }}</div>
                <div>Low Stock Items: {{ report.summary.low_stock_items }}</div>
                <div>Out of Stock: {{ report.summary.out_of_stock_items }}</div>
                <div>Total Value: PHP {{ formatPrice(report.summary.total_inventory_value) }}</div>
              </div>
              <p class="report-date">Generated: {{ formatDate(report.generated_at) }}</p>
            </div>
          </div>
        </div>
      </div>
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

  <!-- CONFIRM STOCK MODAL -->
  <transition name="fade">
    <div v-if="showConfirmModal" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Confirm Stock Delivery</h3>
        <p>{{ activeProcurement?.product_name }} - Quantity: {{ activeProcurement?.quantity }}</p>
        <div class="info-grid">
          <div class="info-row">
            <span class="info-label">Counted Quantity:</span>
            <input v-model.number="confirmStock" type="number" min="0" class="confirm-input" />
          </div>
        </div>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelConfirmModal">Cancel</button>
          <button class="btn-confirm" @click="submitConfirmStock" :disabled="isConfirming">{{ isConfirming ? 'Confirming...' : 'Confirm' }}</button>
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
import { ref, onMounted, computed, watch } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'

const userProfile = ref({})
const dashboardTotals = ref({ totalProducts: 0, lowStock: 0, stockValue: 0 })
const products = ref([])
const inventoryReports = ref([])
const pendingProcurements = ref([])
const confirmedProcurements = ref([])
const notificationCounts = ref({ inventory: 0 })
const hasNotified = ref(false)
const inventoryPendingCount = computed(() => {
  const apiPending = Number(notificationCounts.value?.inventory || 0)
  const localPending = (pendingProcurements.value || []).length
  return Math.max(apiPending, localPending, 0)
})

// UI / modal state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const showConfirmModal = ref(false)
const isConfirming = ref(false)
const activeProcurement = ref(null)
const confirmStock = ref(0)
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

// Detect if this is a staff or manager user to use correct API endpoint
function getApiPrefix() {
  try {
    const userStr = localStorage.getItem('user')
    if (!userStr) return '/api/manager/inventory'
    const user = JSON.parse(userStr)
    const role = (user.role || '').toLowerCase()
    
    console.debug('[getApiPrefix] User role:', role, 'Full user:', user)
    
    // Staff role uses staff inventory endpoints
    if (role === 'staff') {
      console.debug('[getApiPrefix] Using staff endpoint')
      return '/api/staff/inventory'
    }
    
    // Manager, Custom, and other roles use manager inventory endpoints
    console.debug('[getApiPrefix] Using manager endpoint')
    return '/api/manager/inventory'
  } catch (e) {
    console.error('[getApiPrefix] Error:', e)
    return '/api/manager/inventory'
  }
}

const apiPrefix = ref(getApiPrefix())

onMounted(async () => {
  try {
    const res = await axios.get(`${apiPrefix.value}/profile`, { withCredentials: true })
    userProfile.value = res.data.user
  } catch (e) {}

  try {
    const dash = await axios.get(`${apiPrefix.value}/dashboard`, { withCredentials: true })
    if (dash && dash.data && typeof dash.data === 'object') dashboardTotals.value = dash.data
  } catch (e) {}

  try {
    const prods = await axios.get(`${apiPrefix.value}/products`, { withCredentials: true })
    if (prods && prods.data) {
      if (typeof prods.data === 'string' && prods.data.trim().toLowerCase().startsWith('<!doctype html')) {
        console.warn('Products API returned HTML — likely unauthorized or wrong route, redirecting to login')
        try { sessionStorage.setItem('skipRouteOverlay', '1') } catch (e) {}
        window.location.replace('/staff-landing')
        return
      }
      if (Array.isArray(prods.data)) {
        products.value = prods.data
      } else if (Array.isArray(prods.data.data)) {
        products.value = prods.data.data
      } else if (Array.isArray(prods.data.products)) {
        products.value = prods.data.products
      } else {
        console.warn('Unexpected products response', prods.data)
        products.value = []
      }
    }
  } catch (e) { 
    console.warn('Failed to load products', e.response?.status, e.response?.data || e.message)
  }

  try {
    const reports = await axios.get(`${apiPrefix.value}/reports`, { withCredentials: true })
    if (reports && reports.data) {
      if (typeof reports.data === 'string' && reports.data.trim().toLowerCase().startsWith('<!doctype html')) {
        console.warn('Reports API returned HTML — likely unauthorized or wrong route, redirecting to login')
        try { sessionStorage.setItem('skipRouteOverlay', '1') } catch (e) {}
        window.location.replace('/staff-landing')
        return
      }
      if (Array.isArray(reports.data)) {
        inventoryReports.value = reports.data
      } else if (Array.isArray(reports.data.data)) {
        inventoryReports.value = reports.data.data
      } else if (Array.isArray(reports.data.reports)) {
        inventoryReports.value = reports.data.reports
      } else {
        console.warn('Unexpected reports response', reports.data)
        inventoryReports.value = []
      }
    }
  } catch (e) { console.warn('Failed to load reports', e) }

  try {
    const pending = await axios.get(`${apiPrefix.value}/pending-procurements`, { withCredentials: true })
    if (pending && pending.data) {
      if (Array.isArray(pending.data)) {
        pendingProcurements.value = pending.data
      } else if (Array.isArray(pending.data.data)) {
        pendingProcurements.value = pending.data.data
      } else {
        pendingProcurements.value = []
      }
    }
  } catch (e) { console.warn('Failed to load pending procurements', e) }

  try {
    const confirmed = await axios.get(`${apiPrefix.value}/confirmed-procurements`, { withCredentials: true })
    if (confirmed && confirmed.data) {
      if (Array.isArray(confirmed.data)) {
        confirmedProcurements.value = confirmed.data
      } else if (Array.isArray(confirmed.data.data)) {
        confirmedProcurements.value = confirmed.data.data
      } else {
        confirmedProcurements.value = []
      }
    }
  } catch (e) { console.warn('Failed to load confirmed procurements', e) }

  await loadPanelNotifications()
})

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      const count = Number(res.data.counts?.inventory || 0)
      notificationCounts.value = { inventory: Number.isNaN(count) ? 0 : count }
    }
  } catch (e) {
    notificationCounts.value = { inventory: 0 }
  }
}

watch(inventoryPendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending stock confirmations.', 'info')
    hasNotified.value = true
  }
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

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

function openConfirmModal(proc) {
  activeProcurement.value = proc
  confirmStock.value = proc.quantity
  showConfirmModal.value = true
}

function cancelConfirmModal() {
  showConfirmModal.value = false
  activeProcurement.value = null
  confirmStock.value = 0
}

async function submitConfirmStock() {
  if (!activeProcurement.value || confirmStock.value < 0) {
    showToast('Invalid quantity', 'warning')
    return
  }

  isConfirming.value = true
  try {
    await axios.post(
      `${apiPrefix.value}/procurements/${activeProcurement.value.id}/confirm-stock`,
      { counted_stock: confirmStock.value },
      { withCredentials: true }
    )
    // Reload pending procurements after confirmation
    try {
      await Promise.all([
        loadPendingProcurements(),
        loadConfirmedProcurements()
      ])
    } catch (reloadErr) {
      console.error('Error reloading procurements after confirmation', reloadErr)
    }
    showToast('Stock confirmed successfully', 'success')
    cancelConfirmModal()
  } catch (e) {
    console.error('Failed to confirm stock', e)
    const errMsg = e.response?.data?.message || 'Failed to confirm stock'
    showToast(errMsg, 'error')
  } finally {
    isConfirming.value = false
  }
}

async function loadPendingProcurements() {
  try {
    const res = await axios.get(`${apiPrefix.value}/pending-procurements`, { withCredentials: true })
    if (res && res.data) {
      if (Array.isArray(res.data)) {
        pendingProcurements.value = res.data
      } else if (Array.isArray(res.data.data)) {
        pendingProcurements.value = res.data.data
      } else {
        pendingProcurements.value = []
      }
    }
  } catch (e) { console.warn('Failed to load pending procurements', e) }
}

async function loadConfirmedProcurements() {
  try {
    const res = await axios.get(`${apiPrefix.value}/confirmed-procurements`, { withCredentials: true })
    if (res && res.data) {
      if (Array.isArray(res.data)) {
        confirmedProcurements.value = res.data
      } else if (Array.isArray(res.data.data)) {
        confirmedProcurements.value = res.data.data
      } else {
        confirmedProcurements.value = []
      }
    }
  } catch (e) { console.warn('Failed to load confirmed procurements', e) }
}

function onProfileUpdated(newData) {
  Object.assign(userProfile.value, newData)
}

function formatDate(dateString) {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleString()
  } catch (e) {
    return dateString
  }
}

function formatPrice(price) {
  if (!price) return '0.00'
  return parseFloat(price).toFixed(2)
}

// Category grouping and expiration utilities

const productCategories = computed(() => {
  const categories = new Set()
  products.value.forEach(p => {
    categories.add(p.category || 'Uncategorized')
  })
  return Array.from(categories).sort()
})

function getProductsByCategory(category) {
  return products.value.filter(p => (p.category || 'Uncategorized') === category)
}

function formatDateShort(dateStr) {
  if (!dateStr) return '—'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
  } catch (e) {
    return dateStr
  }
}

function isProductExpired(product) {
  if (!product.expires_at) return false
  try {
    const expiryDate = new Date(product.expires_at)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return expiryDate < today
  } catch (e) {
    return false
  }
}

function getExpiryClass(product) {
  if (!product.expires_at) return 'expiry-none'
  try {
    const expiryDate = new Date(product.expires_at)
    const today = new Date()
    const tomorrow = new Date(today)
    tomorrow.setDate(tomorrow.getDate() + 1)
    const weekFromNow = new Date(today)
    weekFromNow.setDate(weekFromNow.getDate() + 7)
    
    today.setHours(0, 0, 0, 0)
    expiryDate.setHours(0, 0, 0, 0)
    
    if (expiryDate < today) return 'expiry-expired'
    if (expiryDate <= tomorrow) return 'expiry-critical'
    if (expiryDate <= weekFromNow) return 'expiry-warning'
    return 'expiry-ok'
  } catch (e) {
    return 'expiry-none'
  }
}
</script>

<style scoped>
.inventory-content {
  padding: 20px;
}

.section {
  margin-bottom: 40px;
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section h2 {
  font-size: 1.3em;
  margin-bottom: 16px;
  color: #333;
  border-bottom: 2px solid #f39c12;
  padding-bottom: 10px;
}

.empty-message {
  text-align: center;
  color: #999;
  padding: 20px;
  font-style: italic;
}

.products-table,
.procurements-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.products-table thead,
.procurements-table thead {
  background-color: #f8f9fa;
}

.products-table th,
.procurements-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
  color: #333;
}

.products-table td,
.procurements-table td {
  padding: 12px;
  border-bottom: 1px solid #dee2e6;
}

.products-table tbody tr:hover,
.procurements-table tbody tr:hover {
  background-color: #f9f9f9;
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.85em;
  font-weight: 600;
}

.status-ok {
  background-color: #d4edda;
  color: #155724;
}

.status-low {
  background-color: #fff3cd;
  color: #856404;
}

.status-out {
  background-color: #f8d7da;
  color: #721c24;
}

.reports-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-top: 10px;
}

.report-card {
  border: 1px solid #dee2e6;
  padding: 16px;
  border-radius: 6px;
  background: #f9f9f9;
}

.report-card h3 {
  margin: 0 0 8px 0;
  color: #333;
  font-size: 1em;
}

.report-type {
  margin: 0 0 12px 0;
  color: #666;
  font-size: 0.85em;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.report-summary {
  background: white;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 10px;
  font-size: 0.9em;
}

.report-summary div {
  margin: 4px 0;
  color: #555;
}

.report-date {
  margin: 0;
  font-size: 0.8em;
  color: #999;
}

.info-grid {
  margin: 16px 0;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.info-label {
  font-weight: 600;
  min-width: 150px;
  color: #333;
}

.confirm-input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1em;
  flex: 1;
  max-width: 200px;
}

.btn-primary {
  background-color: #f39c12;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9em;
  font-weight: 600;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: #e67e22;
}

.btn-primary:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* Category grouping styles */
.category-section {
  margin-bottom: 30px;
}

.category-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #333;
  margin: 15px 0 10px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #f39c12;
}

/* Expiration date styles */
.expiry-expired {
  background: #fee2e2;
  color: #7f1d1d;
  padding: 3px 6px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.85rem;
}

.expiry-critical {
  background: #fef3c7;
  color: #92400e;
  padding: 3px 6px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.85rem;
}

.expiry-warning {
  background: #fef08a;
  color: #713f12;
  padding: 3px 6px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.85rem;
}

.expiry-ok {
  background: #dcfce7;
  color: #166534;
  padding: 3px 6px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.85rem;
}

.expiry-none {
  color: #9ca3af;
  font-size: 0.85rem;
}

.products-table tbody tr.expired {
  background: #fef2f2;
}

.products-table tbody tr.expired .name {
  color: #7f1d1d;
  font-weight: 700;
}
</style>
