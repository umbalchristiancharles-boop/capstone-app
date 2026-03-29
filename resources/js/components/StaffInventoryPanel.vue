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
          <div class="hr-stat-card hr-stat-card--leave">
            <div class="hr-stat-icon"> <!-- icon --> </div>
            <div class="hr-stat-content"><span class="hr-stat-label">Pending Requests</span><span class="hr-stat-value">{{ pendingCount }}</span></div>
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
                  <td class="expiry-cell"><span class="expiry-date">{{ formatDate(p.expires_at || p.expiresAt) }}</span></td>
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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import InventoryStaffPanel from './inventory/InventoryStaffPanel.vue'
import axios from 'axios'
import '../css/adminpanel.css'

const userProfile = ref({})
const products = ref([])
const procurementRequests = ref([])
const pendingProcurements = ref([])
const confirmedProcurements = ref([])
const announcements = ref([])
const router = useRouter()

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
const pendingCount = computed(() => (pendingProcurements.value || []).length)

const procurementRequestsCombined = computed(() => {
  // combine pending + confirmed or fallback to procurementRequests
  if ((pendingProcurements.value || []).length || (confirmedProcurements.value || []).length) {
    return [...(pendingProcurements.value || []), ...(confirmedProcurements.value || [])]
  }
  return procurementRequests.value || []
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

function requestProcurement(prod) {
  // minimal placeholder: open new request modal or navigate
  alert('Request procurement for: ' + (prod.name || prod.id))
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

  try {
    const prods = await axios.get('/api/staff/inventory/products', { withCredentials: true })
    products.value = Array.isArray(prods.data) ? prods.data : (prods.data?.data || [])
  } catch (e) { console.error('products fetch failed', e) }

  // procurements
  try {
    const pending = await axios.get('/api/staff/inventory/pending-procurements', { withCredentials: true })
    pendingProcurements.value = Array.isArray(pending.data) ? pending.data : (pending.data?.data || [])
  } catch (e) { pendingProcurements.value = [] }

  try {
    const confirmed = await axios.get('/api/staff/inventory/confirmed-procurements', { withCredentials: true })
    confirmedProcurements.value = Array.isArray(confirmed.data) ? confirmed.data : (confirmed.data?.data || [])
  } catch (e) { confirmedProcurements.value = [] }

  // fallback generic procurements endpoint
  try {
    const reqs = await axios.get('/api/staff/inventory/procurements', { withCredentials: true })
    procurementRequests.value = Array.isArray(reqs.data) ? reqs.data : (reqs.data?.data || [])
  } catch (e) { procurementRequests.value = [] }

  // announcements
  try {
    const ann = await axios.get('/api/announcements', { withCredentials: true })
    announcements.value = Array.isArray(ann.data) ? ann.data : (ann.data?.announcements || ann.data?.data || [])
  } catch (e) { announcements.value = [] }
})

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

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
