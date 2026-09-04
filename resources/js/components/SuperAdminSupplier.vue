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

          <!-- stats grid removed as requested -->

          <!-- Orders table (read-only monitoring) -->
          <div class="panel-section" style="padding:0">
            <h3 style="margin:0 0 12px 0; position:relative;">
              Supplier Orders
              <span v-if="supplierPendingCount > 0" class="panel-badge">{{ supplierPendingCount }}</span>
            </h3>
            <div v-if="ordersLoading" class="loading-container"><div class="loading-spinner"></div><p>Loading orders...</p></div>
            <div v-else class="requests-container">
              <div class="orders-table-wrapper">
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
                          <th>Supplier Input</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="order in orders" :key="order.id">
                          <td>{{ order.product?.name }}</td>
                          <td>{{ order.branch?.name || order.branch_id }}</td>
                          <td>{{ order.quantity }}</td>
                          <td>{{ formatPrice(order.product?.price * order.quantity) }}</td>
                          <td>
                            <div v-if="order.product_id" class="supplier-input-review">
                              <img v-if="order.product?.image_url" :src="order.product.image_url" alt="Supplier product" class="supplier-product-image" />
                              <div>
                                <div>{{ order.supplier?.full_name || order.supplier?.username || 'Supplier' }}</div>
                                <button v-if="!order.admin_confirmed" class="btn-small btn-primary" @click="confirmSupplierOrder(order)">Confirm Product</button>
                                <span v-else class="status-badge status-approved">Admin confirmed</span>
                              </div>
                            </div>
                            <span v-else class="muted">No submission</span>
                          </td>
                          <td><span :class="['status-badge', getStatusClass(order.status)]">{{ order.status }}</span></td>
                        </tr>
                        <tr v-if="orders.length === 0"><td colspan="6" class="empty-message">No orders.</td></tr>
                      </tbody>
                    </table>
                  </div>
                  <button v-if="showOrdersArrows" class="scroll-btn scroll-btn--right" @click="scrollContainer(ordersTableRef, 1)">▶</button>
                </div>
                <button v-if="showOrdersVertArrows" class="scroll-vertical-btn top" @click="scrollOrdersVertical(-1)">▲</button>
                <button v-if="showOrdersVertArrows" class="scroll-vertical-btn bottom" @click="scrollOrdersVertical(1)">▼</button>
              </div>
            </div>
          </div>

          <!-- Deliveries / Supplier list -->
          <div class="panel-section" style="margin-top:12px">
            <h3 style="margin:0 0 12px 0">Deliveries & Logistics Transactions</h3>
            <div v-if="deliveriesLoading" class="loading-container"><div class="loading-spinner"></div><p>Loading deliveries...</p></div>
            <div v-else>
              <div v-if="deliveries.length === 0" class="empty-message">No deliveries found.</div>
              <div v-else class="scroll-wrapper">
                <button v-if="showDeliveriesArrows" class="scroll-btn scroll-btn--left" @click="scrollContainer(deliveriesTableRef, -1)">◀</button>
                <div ref="deliveriesTableRef" class="table-container">
                  <table class="data-table">
                    <thead><tr><th>Transaction ID</th><th>Product</th><th>Quantity</th><th>Branch</th><th>Status</th><th>Initiated</th></tr></thead>
                    <tbody>
                      <tr v-for="d in deliveries" :key="d.id">
                        <td>{{ d.id }}</td>
                        <td>{{ d.product?.name || 'N/A' }}</td>
                        <td>{{ d.quantity }} {{ d.unit || '' }}</td>
                        <td>{{ d.branch?.name || 'N/A' }}</td>
                        <td><span :class="['status-badge', d.status === 'completed' ? 'status-ok' : d.status === 'cancelled' ? 'status-rejected' : 'status-pending']">{{ d.status }}</span></td>
                        <td>{{ new Date(d.initiated_at).toLocaleDateString() }}</td>
                      </tr>
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
            <div v-else-if="!products.length" class="empty-message">No products found.</div>
            <div v-else class="products-table-wrapper">
              <div class="table-container" ref="productsTableRef">
                <table class="products-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Price</th>
                      <th>Stock</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="p in products" :key="p.id">
                      <td>{{ p.name }}</td>
                      <td>{{ formatPrice(p.price) }}</td>
                      <td>{{ p.stock ?? '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <button v-if="showProductsArrows" class="scroll-vertical-btn top" @click="scrollProducts(-1)">▲</button>
              <button v-if="showProductsArrows" class="scroll-vertical-btn bottom" @click="scrollProducts(1)">▼</button>
            </div>
          </section>
        </div>
      </template>
    </OwnerPanelLayout>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import { useTheme } from '../composables/useTheme'
import { showToast } from './toastStore'

const router = useRouter()
const { initializeTheme } = useTheme()
const suppliers = ref([])
const branches = ref([])
const selectedBranchId = ref('')

// notification refs need to be declared before any computed that reads them
const notificationCounts = ref({ supplier: 0 })
const hasNotified = ref(false)

// orders referenced by computed properties — declare early to avoid TDZ
const orders = ref([])

function handleBranchChange() {
  fetchSuppliers()
    // Reload monitoring data for selected branch
  loadDashboardTotals().catch(()=>{})
  loadOrders().catch(()=>{})
  loadDeliveries().catch(()=>{})
  loadProducts().catch(()=>{})
  loadPanelNotifications().catch(()=>{})
}

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      notificationCounts.value = { supplier: Number(res.data.counts?.supplier || 0) }
    }
  } catch (e) {
    notificationCounts.value = { supplier: 0 }
  }
}

const supplierPendingCount = computed(() => {
  const apiPending = Number(notificationCounts.value?.supplier || 0)
  const localPending = (orders.value || []).filter(o => (o.status || '').toLowerCase() === 'pending').length
  return Math.max(apiPending, localPending, 0)
})

watch(supplierPendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending supplier orders.', 'info')
    hasNotified.value = true
  }
})

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
      const params = selectedBranchId.value ? { branch_id: Number(selectedBranchId.value) } : {}
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
const ordersLoading = ref(false)
const deliveries = ref([])
const deliveriesLoading = ref(false)
const products = ref([])
const loadingProducts = ref(false)
// refs for scrollable tables
const ordersTableRef = ref(null)
const deliveriesTableRef = ref(null)
const productsTableRef = ref(null)
const showOrdersArrows = ref(false)
const showDeliveriesArrows = ref(false)
const showOrdersVertArrows = ref(false)
const showProductsArrows = ref(false)

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
      const params = selectedBranchId.value ? { branch_id: Number(selectedBranchId.value) } : {}
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
      const params = selectedBranchId.value ? { branch_id: Number(selectedBranchId.value) } : {}
    const res = await axios.get('/api/superadmin/logistics/supplier-orders', { params, withCredentials: true })
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

async function confirmSupplierOrder(order) {
  if (!order?.id || order.admin_confirmed) return
  if (!(await window.swalConfirm(`Confirm the product submitted by ${order.supplier?.full_name || order.supplier?.username || 'this supplier'}?`))) return
  try {
    await axios.post(`/api/superadmin/logistics/supplier-orders/${order.id}/confirm`, {}, { withCredentials: true })
    showToast('Supplier product confirmed. Procurement can now acknowledge it.', 'success')
    await loadOrders()
    await loadPanelNotifications()
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to confirm supplier product.', 'error')
  }
}

async function loadDeliveries() {
  deliveriesLoading.value = true
  try {
      const params = selectedBranchId.value ? { branch_id: Number(selectedBranchId.value) } : {}
    const res = await axios.get('/api/superadmin/logistics/deliveries', { params, withCredentials: true })
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
      const params = selectedBranchId.value ? { branch_id: Number(selectedBranchId.value) } : {}
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
  // check overflow for products table after data is loaded
  setTimeout(checkProductsOverflow, 60)
}

function scrollContainer(refEl, dir) {
  try {
    const el = (refEl && refEl.value) ? refEl.value : null
    if (!el) return
    const amount = Math.floor(el.clientWidth * 0.7) * dir
    el.scrollBy({ left: amount, behavior: 'smooth' })
  } catch (e) { console.warn('scrollContainer failed', e) }
}

function scrollProducts(dir) {
  try {
    const el = (productsTableRef && productsTableRef.value) ? productsTableRef.value : null
    if (!el) return
    const amount = Math.floor(el.clientHeight * 0.6) * dir
    el.scrollBy({ top: amount, behavior: 'smooth' })
  } catch (e) { console.warn('scrollProducts failed', e) }
}

function scrollOrdersVertical(dir) {
  try {
    const el = (ordersTableRef && ordersTableRef.value) ? ordersTableRef.value : null
    if (!el) return
    const amount = Math.floor(el.clientHeight * 0.6) * dir
    el.scrollBy({ top: amount, behavior: 'smooth' })
  } catch (e) { console.warn('scrollOrdersVertical failed', e) }
}

function checkOrdersOverflow() {
  try {
    const el = ordersTableRef.value
    if (!el) return showOrdersArrows.value = false
    showOrdersArrows.value = el.scrollWidth > el.clientWidth + 4
  } catch (e) { showOrdersArrows.value = false }
}

function checkOrdersVerticalOverflow() {
  try {
    const el = ordersTableRef.value
    if (!el) return showOrdersVertArrows.value = false
    showOrdersVertArrows.value = el.scrollHeight > el.clientHeight + 8
  } catch (e) { showOrdersVertArrows.value = false }
}

function checkDeliveriesOverflow() {
  try {
    const el = deliveriesTableRef.value
    if (!el) return showDeliveriesArrows.value = false
    showDeliveriesArrows.value = el.scrollWidth > el.clientWidth + 4
  } catch (e) { showDeliveriesArrows.value = false }
}

function checkProductsOverflow() {
  try {
    const el = productsTableRef.value
    if (!el) return showProductsArrows.value = false
    showProductsArrows.value = el.scrollHeight > el.clientHeight + 8
  } catch (e) { showProductsArrows.value = false }
}

function handleResize() {
  checkOrdersOverflow()
  checkDeliveriesOverflow()
  checkProductsOverflow()
  checkOrdersVerticalOverflow()
}

onMounted(async () => {
  initializeTheme()
  try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
  await Promise.all([fetchBranches().catch(()=>{}), fetchSuppliers().catch(()=>{}), loadDashboardTotals().catch(()=>{}), loadOrders().catch(()=>{}), loadDeliveries().catch(()=>{}), loadProducts().catch(()=>{}), loadPanelNotifications().catch(()=>{})])
  // check overflow for tables and listen for resizes
  setTimeout(() => { checkOrdersOverflow(); checkDeliveriesOverflow(); checkProductsOverflow(); checkOrdersVerticalOverflow() }, 100)
  window.addEventListener('resize', handleResize)
})

// Watch branch selection and reload when changed (handles programmatic changes)
watch(selectedBranchId, (nv, ov) => {
  if (nv !== ov) handleBranchChange()
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

.panel-badge { position:absolute; top:-8px; right:-8px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }

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
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

/* Dark Mode - Branch Selector */
:global(.dark-mode) .branch-selector-section {
  background: #2d2d2d !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.5) !important;
}

:global(.dark-mode) .branch-selector-section label {
  color: #e5e7eb !important;
}

:global(.dark-mode) .branch-selector-section select {
  background: #1a1a1a !important;
  color: #e5e7eb !important;
  border-color: #444 !important;
}

:global(.dark-mode) .branch-selector-section select option {
  background: #1a1a1a !important;
  color: #e5e7eb !important;
}

.panel-section { background: rgba(255,255,255,0.95); border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: background-color 0.3s ease, box-shadow 0.3s ease; }

.supplier-input-review { display:flex; align-items:center; gap:8px; min-width:190px; }
.supplier-product-image { width:44px; height:44px; object-fit:cover; border-radius:6px; border:1px solid #d1d5db; }

:global(.dark-mode) .panel-section {
  background: rgba(45,45,45,0.9);
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #4b2a06;
  margin: 0 0 8px 0;
  transition: color 0.3s ease;
}

:global(.dark-mode) .section-title {
  color: #e5e7eb;
}

.section-description {
  font-size: 14px;
  color: #666;
  margin: 0 0 16px 0;
  transition: color 0.3s ease;
}

:global(.dark-mode) .section-description {
  color: #9ca3af;
}

.table-container { overflow-x: auto; max-height: 420px; overflow-y: auto; -webkit-overflow-scrolling: touch; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th { position: sticky; top: 0; z-index: 6; background: #fff4e6; }
.data-table th, .data-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #eee; }
.data-table th { background: #fff4e6; font-weight: 600; color: #5a2c0a; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
.data-table td { color: #333; }

:global(.dark-mode) .data-table thead th {
  background: rgba(45,45,45,0.8);
}
:global(.dark-mode) .data-table th {
  background: rgba(45,45,45,0.8);
  color: #e5e7eb;
}
:global(.dark-mode) .data-table td {
  border-bottom-color: #444;
  color: #d1d5db;
}
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

/* Make tables fill the available container width and prevent external
   styles that force a minimum width from creating horizontal overflow. */
:deep(.requests-scroll .data-table) {
  min-width: 0 !important;
  width: 100% !important;
  table-layout: fixed;
}

:deep(.requests-scroll) {
  width: 100% !important;
  overflow-x: auto;
}

:deep(.data-table th), :deep(.data-table td) {
  white-space: normal;
  word-break: break-word;
}

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
:deep(.announcements-panel) { max-width: 100%; box-sizing: border-box; margin-top: 120px; }
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
.products-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}
.products-table thead th {
  text-align: left;
  padding: 12px 16px;
  background: #fff4e6;
  color: #5a2c0a;
  font-weight: 600;
  font-size: 13px;
  position: sticky;
  top: 0;
  z-index: 6;
}
.products-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #eee;
  color: #333;
}

:global(.dark-mode) .products-table thead th {
  background: rgba(45,45,45,0.8);
  color: #e5e7eb;
}
:global(.dark-mode) .products-table td {
  border-bottom-color: #444;
  color: #d1d5db;
}
.products-table-wrapper .table-container { max-height: 480px; overflow: auto; }
.products-table tbody tr:last-child td { border-bottom: none; }

/* Custom vertical scrollbar visuals and overlay arrow buttons */
.products-table-wrapper { position: relative; }
.products-table-wrapper .table-container { scrollbar-width: thin; scrollbar-color: #8b8b8b #f1f1f1; }
.products-table-wrapper .table-container::-webkit-scrollbar { width: 12px; }
.products-table-wrapper .table-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 8px; }
.products-table-wrapper .table-container::-webkit-scrollbar-thumb { background: #9b9b9b; border-radius: 8px; border: 2px solid #f1f1f1; }

.scroll-vertical-btn {
  position: absolute;
  right: 12px;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border-radius: 6px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  border: 1px solid rgba(0,0,0,0.06);
  cursor: pointer;
  color: #666;
  font-size: 10px;
  line-height: 1;
  padding: 0;
}
.scroll-vertical-btn.top { top: 10px; }
.scroll-vertical-btn.bottom { bottom: 10px; transform: rotate(180deg); }

/* Orders table wrapper custom scrollbar + overlay buttons */
.orders-table-wrapper { position: relative; }
.orders-table-wrapper .table-container { scrollbar-width: thin; scrollbar-color: #8b8b8b #f1f1f1; }
.orders-table-wrapper .table-container::-webkit-scrollbar { width: 12px; }
.orders-table-wrapper .table-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 8px; }
.orders-table-wrapper .table-container::-webkit-scrollbar-thumb { background: #9b9b9b; border-radius: 8px; border: 2px solid #f1f1f1; }

@media (max-width: 640px) {
  .orders-table-wrapper .table-container::-webkit-scrollbar { width: 8px; }
}

/* hide native scrollbar on small screens while preserving functionality */
@media (max-width: 640px) {
  .products-table-wrapper .table-container::-webkit-scrollbar { width: 8px; }
}
</style>
