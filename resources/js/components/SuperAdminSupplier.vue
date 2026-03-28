<template>
  <div class="superadmin-logistics-wrapper">
    <OwnerPanelLayout
      panelTitle="Super Admin Supplier Panel"
      panelDescription="Manage suppliers, view supplier inventory, and monitor supplier requests across branches."
      :showProfileColumn="false"
    >
      <template #headerLeft>
        <button class="btn-secondary back-to-superadmin-btn" @click="goBackToSuperAdmin">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          Back to Super Admin
        </button>
      </template>

      <template #main>
        <div class="branch-selector-section" style="margin-bottom: 1rem; display: flex; align-items: center;">
          <label style="font-weight:600; color:#1e293b; margin-right:0.75rem; font-size:0.95rem;">Select Branch:</label>
          <select v-model="selectedBranchId" @change="handleBranchChange" style="padding:0.45rem 0.6rem; border:1px solid #CBD5E1; border-radius:6px; background:white; font-size:0.9rem; min-width:220px;">
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }} (ID: {{ branch.id }})</option>
          </select>
        </div>
        <div class="panel-section">
          <h2 class="section-title">Supplier Management</h2>
          <p class="section-description">View suppliers and supplier activity for the selected branch.</p>

          <div class="hr-stats-grid" style="display:flex;gap:12px;margin-bottom:12px">
            <div class="overview-card"><span class="overview-label">Active Deliveries:</span><span class="overview-value">&nbsp;{{ dashboardTotals.activeDeliveries }}</span></div>
            <div class="overview-card"><span class="overview-label">Pending Orders:</span><span class="overview-value">&nbsp;{{ dashboardTotals.pendingOrders }}</span></div>
            <div class="overview-card"><span class="overview-label">Total Suppliers:</span><span class="overview-value">&nbsp;{{ dashboardTotals.totalSuppliers }}</span></div>
          </div>

          <!-- Orders table (read-only monitoring) -->
          <div class="panel-section" style="padding:0">
            <h3 style="margin:0 0 12px 0">Supplier Orders</h3>
            <div v-if="ordersLoading" class="loading-container"><div class="loading-spinner"></div><p>Loading orders...</p></div>
            <div v-else class="requests-container">
              <div class="scroll-wrapper">
                <button v-if="showOrdersArrows" class="scroll-btn scroll-btn--left" @click="scrollContainer(ordersTableRef, -1)">◀</button>
                <div ref="ordersTableRef" class="requests-scroll table-container">
                  <table class="data-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Branch</th>
                      <th>Qty</th>
                      <th>Total</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in orders" :key="order.id">
                      <td>{{ order.product?.name }}</td>
                      <td>{{ order.branch?.name || order.branch_id }}</td>
                      <td>{{ order.quantity }}</td>
                      <td>{{ formatPrice(order.product?.price * order.quantity) }}</td>
                      <td><span :class="['status-badge', getStatusClass(order.status)]">{{ order.status }}</span></td>
                    </tr>
                    <tr v-if="orders.length === 0"><td colspan="5" class="empty-message">No orders.</td></tr>
                  </tbody>
                  </table>
                </div>
                <button v-if="showOrdersArrows" class="scroll-btn scroll-btn--right" @click="scrollContainer(ordersTableRef, 1)">▶</button>
              </div>
            </div>
          </div>

          <!-- Deliveries / Supplier list -->
          <div class="panel-section" style="margin-top:12px">
            <h3 style="margin:0 0 12px 0">Deliveries</h3>
            <div v-if="deliveriesLoading" class="loading-container"><div class="loading-spinner"></div><p>Loading deliveries...</p></div>
            <div v-else>
              <div v-if="deliveries.length === 0" class="empty-message">No deliveries found.</div>
              <div v-else class="scroll-wrapper">
                <button v-if="showDeliveriesArrows" class="scroll-btn scroll-btn--left" @click="scrollContainer(deliveriesTableRef, -1)">◀</button>
                <div ref="deliveriesTableRef" class="table-container">
                  <table class="data-table">
                    <thead><tr><th>Delivery ID</th><th>Supplier</th><th>Branch</th><th>Status</th></tr></thead>
                    <tbody>
                      <tr v-for="d in deliveries" :key="d.id"><td>{{ d.id }}</td><td>{{ d.supplier?.name || d.supplier_name }}</td><td>{{ d.branch?.name || d.branch_name }}</td><td><span :class="['status-badge', d.status === 'delivered' ? 'status-ok' : 'status-pending']">{{ d.status }}</span></td></tr>
                    </tbody>
                  </table>
                </div>
                <button v-if="showDeliveriesArrows" class="scroll-btn scroll-btn--right" @click="scrollContainer(deliveriesTableRef, 1)">▶</button>
              </div>
            </div>
          </div>

          <!-- Products grid (read-only monitoring) -->
          <section class="supplier-products" style="margin-top:12px">
            <h3>Your Supplier Products</h3>
            <div v-if="loadingProducts">Loading products...</div>
            <div v-else-if="!products.length">No products found.</div>
            <div v-else class="product-grid">
              <div v-for="p in products" :key="p.id" class="product-card">
                <div class="product-card-header">
                  <div class="product-name">{{ p.name }}</div>
                </div>
                <div class="product-meta">
                  <div class="product-price">{{ formatPrice(p.price) }}</div>
                  <div class="product-stock">Stock: {{ p.stock ?? '-' }}</div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </template>
    </OwnerPanelLayout>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'

const router = useRouter()
const suppliers = ref([])
const branches = ref([])
const selectedBranchId = ref('')

function handleBranchChange() {
  fetchSuppliers()
  // Reload monitoring data for selected branch
  loadDashboardTotals().catch(()=>{})
  loadOrders().catch(()=>{})
  loadDeliveries().catch(()=>{})
  loadProducts().catch(()=>{})
}

async function fetchBranches() {
  try {
    const res = await axios.get('/api/superadmin/logistics/branches', { withCredentials: true })
    branches.value = res.data || []
  } catch (e) {
    branches.value = []
  }
}

function goBackToSuperAdmin() {
  router.push('/super-admin-panel')
}

async function fetchSuppliers() {
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/superadmin/suppliers', { params, withCredentials: true })
    suppliers.value = Array.isArray(res.data) ? res.data : (res.data?.data || [])
  } catch (e) {
    if (e?.response?.status === 401) {
      suppliers.value = []
      return
    }
    console.error('Failed to fetch suppliers:', e)
    suppliers.value = []
  }
}

// Monitoring data
const dashboardTotals = ref({ totalSuppliers: 0, activeDeliveries: 0, pendingOrders: 0 })
const orders = ref([])
const ordersLoading = ref(false)
const deliveries = ref([])
const deliveriesLoading = ref(false)
const products = ref([])
const loadingProducts = ref(false)
// refs for scrollable tables
const ordersTableRef = ref(null)
const deliveriesTableRef = ref(null)
const showOrdersArrows = ref(false)
const showDeliveriesArrows = ref(false)

function getStatusClass(status) {
  switch ((status || '').toLowerCase()) {
    case 'fulfilled': return 'status-approved'
    case 'cancelled': return 'status-rejected'
    case 'delivered': return 'status-ok'
    default: return 'status-pending'
  }
}

function formatPrice(val) {
  if (val === null || val === undefined) return '₱0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '₱0.00'
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function loadDashboardTotals() {
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/manager/logistics/dashboard', { params, withCredentials: true })
    const d = res.data || {}
    dashboardTotals.value = {
      totalSuppliers: d.totalSuppliers || d.total_suppliers || 0,
      activeDeliveries: d.activeDeliveries || d.active_deliveries || 0,
      pendingOrders: d.pendingOrders || d.pending_orders || 0
    }
  } catch (e) {
    if (e?.response?.status === 401) return
    console.warn('Failed to load dashboard totals', e)
  }
}

async function loadOrders() {
  ordersLoading.value = true
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/supplier-orders', { params, withCredentials: true })
    const raw = res.data.data || res.data || []
    orders.value = Array.isArray(raw) ? raw : []
  } catch (e) {
    if (e?.response?.status === 401) {
      // Not authenticated - treat as empty dataset without noisy console error
      orders.value = []
      return
    }
    console.warn('Failed to load supplier orders', e)
    orders.value = []
  } finally { ordersLoading.value = false }
}

async function loadDeliveries() {
  deliveriesLoading.value = true
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/logistics/deliveries', { params, withCredentials: true })
    const raw = res.data.data || res.data || []
    deliveries.value = Array.isArray(raw) ? raw : []
  } catch (e) {
    if (e?.response?.status === 401) {
      deliveries.value = []
      return
    }
    console.warn('Failed to load deliveries', e)
    deliveries.value = []
  } finally { deliveriesLoading.value = false }
  // check overflow for deliveries table after data is loaded
  setTimeout(checkDeliveriesOverflow, 50)
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/superadmin/logistics/products', { params, withCredentials: true })
    const raw = res.data.data || res.data || []
    products.value = Array.isArray(raw) ? raw : []
  } catch (e) {
    if (e?.response?.status === 401) {
      products.value = []
      return
    }
    console.warn('Failed to load products', e)
    products.value = []
  } finally { loadingProducts.value = false }
}

function scrollContainer(refEl, dir) {
  try {
    const el = (refEl && refEl.value) ? refEl.value : null
    if (!el) return
    const amount = Math.floor(el.clientWidth * 0.7) * dir
    el.scrollBy({ left: amount, behavior: 'smooth' })
  } catch (e) { console.warn('scrollContainer failed', e) }
}

function checkOrdersOverflow() {
  try {
    const el = ordersTableRef.value
    if (!el) return showOrdersArrows.value = false
    showOrdersArrows.value = el.scrollWidth > el.clientWidth + 4
  } catch (e) { showOrdersArrows.value = false }
}

function checkDeliveriesOverflow() {
  try {
    const el = deliveriesTableRef.value
    if (!el) return showDeliveriesArrows.value = false
    showDeliveriesArrows.value = el.scrollWidth > el.clientWidth + 4
  } catch (e) { showDeliveriesArrows.value = false }
}

function handleResize() {
  checkOrdersOverflow()
  checkDeliveriesOverflow()
}

onMounted(async () => {
  try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
  await Promise.all([fetchBranches().catch(()=>{}), fetchSuppliers().catch(()=>{}), loadDashboardTotals().catch(()=>{}), loadOrders().catch(()=>{}), loadDeliveries().catch(()=>{}), loadProducts().catch(()=>{})])
  // check overflow for tables and listen for resizes
  setTimeout(() => { checkOrdersOverflow(); checkDeliveriesOverflow() }, 100)
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  try { window.removeEventListener('resize', handleResize) } catch (e) {}
})
</script>

<style scoped>
/* Layout and spacing matched to SuperAdminLogisticsPanel */
.back-to-superadmin-btn {
  position: relative;
  margin: 0;
  padding: 10px 16px;
  background: rgba(255,255,255,0.95);
  border: none;
  border-radius: 8px;
  color: #333;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: transform 0.18s, box-shadow 0.18s;
}

.back-to-superadmin-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.16); }

.branch-selector-section {
  background: rgba(255,255,255,0.95);
  padding: 12px 18px;
  margin: 0 0 1rem 0;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: 520px;
  align-self: start;
}

.panel-section { background: rgba(255,255,255,0.95); border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.section-title { font-size: 20px; font-weight: 600; color: #4b2a06; margin: 0 0 8px 0; }
.section-description { font-size: 14px; color: #666; margin: 0 0 16px 0; }

.table-container { overflow-x: auto; max-height: 420px; overflow-y: auto; -webkit-overflow-scrolling: touch; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th { position: sticky; top: 0; z-index: 6; background: #fff4e6; }
.data-table th, .data-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #eee; }
.data-table th { background: #fff4e6; font-weight: 600; color: #5a2c0a; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
.empty-message { text-align: center; color: #999; font-style: italic; }
.status-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
.status-ok { background: rgba(46, 204, 113, 0.15); color: #27ae60; }
.status-low { background: rgba(231, 76, 60, 0.15); color: #e74c3c; }

.loading-container { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 20px; }
.loading-spinner { width:36px; height:36px; border:3px solid rgba(255,159,67,0.3); border-top:3px solid #ff9f43; border-radius:50%; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }

/* Scroll wrapper & buttons */
.scroll-wrapper { position: relative; display: flex; align-items: center; gap: 8px; }
.scroll-btn { background: rgba(255,255,255,0.95); border: 1px solid var(--border-stroke); padding: 10px 12px; border-radius: 8px; cursor: pointer; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
.scroll-btn:active { transform: translateY(1px); }
.scroll-btn--left { margin-left: 2px }
.scroll-btn--right { margin-right: 2px }
.requests-scroll { overflow-x: auto; }
.requests-scroll::-webkit-scrollbar { height: 8px }
.requests-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 6px }

.table-container { overflow-x: auto; }
.table-container::-webkit-scrollbar { height: 8px }
.table-container::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 6px }

/* Ensure announcements on this view are responsive and scroll with the page
   instead of remaining sticky and getting "left behind" when the main content scrolls. */
:deep(.admin-layout.no-profile-column) .admin-side {
  position: relative !important;
  top: auto !important;
  max-height: none !important;
  overflow: visible !important;
  padding-right: 0 !important;
}

/* Make announcements panel adapt to viewport and keep header visible when
   the list itself becomes scrollable. */
:deep(.announcements-panel) { max-width: 100%; box-sizing: border-box; }
:deep(.announcements-panel .panel-header) {
  position: sticky;
  top: 0;
  z-index: 6;
  background: transparent;
}
:deep(.announcements-panel .panel-body) {
  overflow: auto;
  max-height: calc(100vh - 200px);
  min-height: 0;
}
</style>
