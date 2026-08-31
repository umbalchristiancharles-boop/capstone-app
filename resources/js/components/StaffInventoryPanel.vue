<template>
  <div class="admin-page">
    <section class="admin-layout no-profile-column">
      <main class="admin-main">
        <header class="admin-main-header">
          <div class="admin-main-header-top">
            <div class="header-left-slot"></div>
            <div>
              <h1>{{ panelTitle }}</h1>
              <p>{{ panelDescription }}</p>
            </div>
            <div class="header-actions-top">
              <div class="header-profile-wrapper">
                <button class="header-profile-btn">
                  <div class="header-avatar">
                    <div class="header-avatar-initials">{{ profileInitial }}</div>
                  </div>
                  <div class="header-name">{{ userProfile.roleLabel || (userProfile.role || 'STAFF') }} - {{ userProfile.branch || 'BRANCH' }}</div>
                </button>
              </div>
            </div>
          </div>
        </header>

        <div class="hr-stats-grid">
          <div class="hr-stat-card hr-stat-card--total">
            <div class="hr-stat-icon"> <!-- icon --> </div>
            <div class="hr-stat-content"><span class="hr-stat-label">Total Products</span><span class="hr-stat-value">{{ totalProducts }}</span></div>
          </div>
          <div class="hr-stat-card hr-stat-card--active">
            <div class="hr-stat-icon"> <!-- icon --> </div>
            <div class="hr-stat-content"><span class="hr-stat-label">Low Stock</span><span class="hr-stat-value">{{ lowStockCount }}</span></div>
          </div>
          <div class="hr-stat-card hr-stat-card--leave" :class="{ 'stat-alert': inventoryPendingCount > 0 }">
            <div class="hr-stat-icon"> <!-- icon --> </div>
            <div class="hr-stat-content"><span class="hr-stat-label">Pending Requests</span><span class="hr-stat-value">{{ pendingCount }}</span></div>
            <span v-if="inventoryPendingCount > 0" class="panel-badge">{{ inventoryPendingCount }}</span>
          </div>
        </div>

        <!-- Inventory Section -->
        <div class="panel-section">
          <h2 class="section-title">Inventory Monitor</h2>
          <p class="section-description">Current stock levels for your branch (Read-only)</p>

          <div class="table-container inventory-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Category</th>
                  <th>Pricing Type</th>
                  <th>Stock Count</th>
                  <th>Minimum Stock</th>
                  <th>Expires At</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in products" :key="p.id">
                  <td><div class="product-name">{{ p.name }}</div></td>
                  <td>{{ p.category || p.type || '—' }}</td>
                  <td><span class="pricing-type-badge">{{ p.pricing_type || 'N/A' }}</span></td>
                  <td>{{ p.stock ?? 0 }}</td>
                  <td>{{ p.min_stock ?? p.minimum_stock ?? 10 }}</td>
                  <td class="expiry-cell">
                    <span v-if="(p.expires_at || p.expiresAt)" class="expiry-date">{{ formatDate(p.expires_at || p.expiresAt) }}</span>
                    <span v-else class="muted">N/A</span>
                  </td>
                  <td>
                    <span class="status-badge" :class="statusClass(p)">{{ statusLabel(p) }}</span>
                  </td>
                  <td>
                    <button v-if="(p.stock ?? 0) <= (p.min_stock ?? p.minimum_stock ?? 10)" class="btn-primary btn-small" @click="requestProcurement(p)">Request Procurement</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Procurement Requests Section -->
        <div class="panel-section">
          <h2 class="section-title">Procurement Requests</h2>
          <p class="section-description">Create procurement requests for products needing budget approval</p>
          <button class="btn-primary" @click="newRequest"> + New Procurement Request </button>

          <div class="requests-list" style="margin-top:12px">
            <h3>My Procurement Requests</h3>
            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Updated</th></tr>
                </thead>
                <tbody>
                  <tr v-for="r in procurementRequests" :key="r.id">
                    <td><div class="product-name">{{ r.product_name || r.product?.name || '—' }}</div></td>
                    <td>{{ r.quantity }}</td>
                    <td class="amount">{{ formatCurrency(r.total || r.amount || (r.quantity * (r.unit_price || r.price || 0))) }}</td>
                    <td><span class="status-badge" :class="statusBadgeClass(r.status)">{{ (r.status || '').toUpperCase() || (r.approval_status || '').toUpperCase() }}</span></td>
                    <td>{{ formatDate(r.updated_at || r.updatedAt || r.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Budget Requests (legacy) -->
        <div class="panel-section">
          <h2 class="section-title">Budget Requests (Legacy)</h2>
          <button class="btn-primary"> + New Budget Request </button>
        </div>
      </main>

      <aside class="admin-side">
        <section class="panel-block announcements-panel">
          <div class="panel-header announcements-header"><h2>Announcements</h2></div>
          <div class="panel-body">
            <ul class="announcement-list">
              <li v-for="a in announcements" :key="a.id" class="announcement-item">
                <div class="announcement-title">{{ a.title || a.heading || 'Update' }}</div>
                <div class="announcement-meta">{{ formatDate(a.created_at || a.createdAt) }} • {{ a.audience || a.scope || 'all' }}</div>
                <div class="announcement-message">{{ a.message || a.body || a.content }}</div>
              </li>
            </ul>
          </div>
        </section>
      </aside>
    </section>

    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from Staff Inventory Panel?</h3>
          <p>This will end your current session for Chikin Tayo Staff.</p>
          <div class="logout-actions">
            <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
            <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showOverlay" class="loading-overlay">
        <div class="logo-loading-box">
          <img :src="logoImg" alt="Chikin Tayo" class="logo-loading-img" />
          <p>{{ overlayText }}</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import InventoryStaffPanel from './inventory/InventoryStaffPanel.vue'
import axios from 'axios'
import { showToast } from './toastStore'
import '../css/adminpanel.css'

const userProfile = ref({})
const products = ref([])
const procurementRequests = ref([])
const announcements = ref([])
const router = useRouter()
const notificationCounts = ref({ inventory: 0 })
const hasNotified = ref(false)

const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

const panelTitle = 'Logistics Manager Panel'
const panelDescription = 'Monitor inventory, procurement requests, and manage budgets for your branch.'

const profileInitial = computed(() => {
  const name = userProfile.value.fullName || userProfile.value.name || userProfile.value.username || ''
  return (name || 'U').substring(0,1).toUpperCase()
})

const totalProducts = computed(() => (products.value || []).length)
const lowStockCount = computed(() => {
  return (products.value || []).reduce((acc, p) => {
    const stock = Number(p.stock || 0)
    const min = Number(p.min_stock ?? p.minimum_stock ?? 10)
    return acc + (stock <= min ? 1 : 0)
  }, 0)
})
const pendingCount = computed(() => (procurementRequests.value || []).length)
const inventoryPendingCount = computed(() => {
  const apiPending = Number(notificationCounts.value?.inventory || 0)
  const localPending = Number(pendingCount.value || 0)
  return Math.max(apiPending, localPending, 0)
})

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleString() } catch (e) { return d }
}

function formatCurrency(v) {
  if (v === null || v === undefined) return '-'
  return Number(v).toLocaleString(undefined, { style: 'currency', currency: 'PHP' })
}

function statusLabel(p) {
  const stock = Number(p.stock || 0)
  const min = Number(p.min_stock ?? p.minimum_stock ?? 10)
  if (stock <= 0) return 'LOW STOCK'
  if (stock <= min) return 'LOW STOCK'
  return 'OK'
}

function statusClass(p) {
  const lbl = statusLabel(p)
  return lbl === 'OK' ? 'status-ok' : 'status-low'
}

function statusBadgeClass(status) {
  if (!status) return ''
  const s = (status || '').toLowerCase()
  if (s.includes('pending')) return 'status-pending'
  if (s.includes('approved')) return 'status-approved'
  if (s.includes('budget')) return 'status-approved'
  return ''
}

async function requestProcurement(prod) {
  if (!(await (window.swalConfirm ? window.swalConfirm(`Create procurement request for ${prod.name}?`) : Promise.resolve(confirm(`Create procurement request for ${prod.name}?`))))) return

  try {
    const minStock = Number(prod.min_stock ?? prod.minimum_stock ?? 10)
    const currentStock = Number(prod.stock ?? 0)
    const qty = Math.max(minStock - currentStock, minStock)

    const payload = {
      product_id: prod.id,
      quantity: qty
    }

    console.log('Creating procurement request:', payload)
    const response = await axios.post('/api/procurement-requests', payload, { withCredentials: true })
    console.log('Success response:', response.data)
    
    if (window.swal) {
      window.swal('Success!', `✅ Procurement request created for ${qty} units`, 'success')
    } else {
      alert(`✅ Procurement request created for ${qty} units`)
    }
    
    await fetchProcurements()
    await fetchProducts()
  } catch (e) {
    console.error('requestProcurement error:', e)
    console.error('Error response:', e.response?.data)
    console.error('Error status:', e.response?.status)
    
    // Handle duplicate active procurement request (409 Conflict)
    if (e.response?.status === 409) {
      const data = e.response.data
      const message = `${data.error}\n\n${data.details}\n\nExisting Request ID: ${data.existing_request_id}\nStatus: ${data.existing_status}`
      
      if (window.swal) {
        window.swal('⚠️ Cannot Create Duplicate Request', message, 'warning')
      } else {
        alert(message)
      }
    } else if (e.response?.data?.error) {
      const errorMsg = e.response.data.error
      if (window.swal) {
        window.swal('Error', `❌ ${errorMsg}`, 'error')
      } else {
        alert(`❌ Error: ${errorMsg}`)
      }
    } else {
      const errorMsg = `Failed to create procurement request: ${e.message}`
      if (window.swal) {
        window.swal('Error', `❌ ${errorMsg}`, 'error')
      } else {
        alert(`❌ ${errorMsg}`)
      }
    }
  }
}

function newRequest() {
  // placeholder for creating a new procurement
  alert('Open new procurement request form')
}
onMounted(async () => {
  try {
    const res = await axios.get('/api/staff/inventory/profile', { withCredentials: true })
    userProfile.value = res.data.user || {}
  } catch (e) { console.error('profile fetch failed', e) }

  // initial fetch
  await fetchProducts()
  await fetchProcurements()
  await loadPanelNotifications()
  // announcements
  try {
    const ann = await axios.get('/api/announcements', { withCredentials: true })
    announcements.value = Array.isArray(ann.data) ? ann.data : (ann.data?.announcements || ann.data?.data || [])
  } catch (e) { announcements.value = [] }
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
    showToast('You have pending inventory confirmations.', 'info')
    hasNotified.value = true
  }
})

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

// Fetch helpers so we can poll for updates
async function fetchProducts() {
  try {
    const res = await axios.get('/api/staff/inventory/products', { withCredentials: true })
    const raw = Array.isArray(res.data) ? res.data : (res.data?.data || [])

    // Deduplicate products by slug (fallback to name). Prefer:
    // 1) published items over unpublished
    // 2) items with higher stock when published state is equal
    const map = new Map()
    for (const p of raw) {
      const key = (p.slug || p.name || '').toString().toLowerCase()
      if (!map.has(key)) {
        map.set(key, p)
        continue
      }
      const existing = map.get(key)
      const existStock = Number(existing.stock || 0)
      const thisStock = Number(p.stock || 0)

      // Prefer higher stock first (so confirmed/ordered items show their actual counts).
      if (thisStock > existStock) {
        map.set(key, p)
        continue
      }

      // If stock is equal, prefer published item.
      if (thisStock === existStock) {
        if ((existing.is_published ? 1 : 0) < (p.is_published ? 1 : 0)) {
          map.set(key, p)
        }
      }
      // otherwise keep existing (higher stock wins)
    }

    const normalized = Array.from(map.values()).map(p => ({
      ...p,
      min_stock: p.min_stock ?? p.minimum_stock ?? 10,
      expires_at: p.expires_at ?? p.expiresAt ?? null,
      stock: Number(p.stock ?? 0)
    }))

    products.value = normalized
  } catch (e) {
    console.error('products fetch failed', e)
  }
}

async function fetchProcurements() {
  try {
    const res = await axios.get('/api/staff/inventory/procurements', { withCredentials: true })
    procurementRequests.value = Array.isArray(res.data) ? res.data : (res.data?.data || [])
  } catch (e) {
    procurementRequests.value = []
  }
}

// Poll for updates so staff UI reflects confirmations done by logistics
const pollIntervalMs = 15000
let pollId = null
onMounted(() => {
  pollId = setInterval(() => {
    fetchProducts()
    fetchProcurements()
  }, pollIntervalMs)
})
onUnmounted(() => {
  if (pollId) clearInterval(pollId)
})

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try { try { localStorage.clear(); sessionStorage.clear(); } catch (e) {} window.location.replace('/logout') } catch (e) {}
  overlayText.value = 'Logging out...'
  try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}
  showOverlay.value = true
  showLogoutConfirm.value = false
    setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/staff-landing') ; window.location.reload(); } catch (e) { router.push('/staff-landing').catch(() => {}) }
  }, 600)
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

function onProfileUpdated(newData) {
  Object.assign(userProfile.value, newData)
}
</script>

<style scoped>
.hr-stat-card { position: relative; }
.panel-badge { position:absolute; top:-8px; right:-8px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }
.stat-alert { border:1px solid #fecaca; box-shadow:0 0 0 2px rgba(239,68,68,0.12) }
</style>
