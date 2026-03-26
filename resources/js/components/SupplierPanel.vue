<template>
  <OwnerPanelLayout ref="ownerLayout"
    :userProfile="userProfile"
    :panelTitle="'Supplier Panel'"
    :panelDescription="'Manage suppliers, view deliveries, and monitor supplier performance.'"
    :showProfileColumn="false"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="panel-content">
        <div class="hr-stats-grid">
          <div class="hr-stat-card hr-stat-card--total">
            <div class="hr-stat-icon">📦</div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Active Deliveries</span>
              <span class="hr-stat-value">{{ dashboardTotals.activeDeliveries }}</span>
            </div>
          </div>
          <div class="hr-stat-card hr-stat-card--active">
            <div class="hr-stat-icon">🕒</div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Pending Orders</span>
              <span class="hr-stat-value">{{ dashboardTotals.pendingOrders }}</span>
            </div>
          </div>
          <div class="hr-stat-card hr-stat-card--leave">
            <div class="hr-stat-icon">🏷️</div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Total Suppliers</span>
              <span class="hr-stat-value">{{ dashboardTotals.totalSuppliers }}</span>
            </div>
          </div>
        </div>

      <!-- Orders Section (merged) -->
      <div class="panel-section">
        <h2 class="section-title">Your Orders</h2>
        <div v-if="ordersLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading orders...</p>
        </div>
        <div v-else class="requests-container">
          <div class="requests-scroll table-container">
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
                <td class="action-cell">
                  <template v-if="order.status === 'pending'">
                    <div v-if="canSubmitProduct(order)">
                      <button class="btn-primary btn-small" @click="openSupplierSubmitModal(order)">Product available</button>
                    </div>
                    <div v-else>
                      <div v-if="canCompleteTransaction(order)">
                        <button class="btn-primary btn-small" @click="completeTransaction(order.id)">Transaction complete</button>
                      </div>
                      <div v-else-if="order.product && order.product.id">
                        <button class="btn-disabled btn-small" disabled>Product submitted</button>
                        <div class="muted small-text" v-if="order.product.created_at" style="margin-top:4px">Submitted: {{ formatDate(order.product.created_at) }}</div>
                      </div>
                      <div v-else>
                        <button class="btn-disabled btn-small" disabled>Waiting for procurement order</button>
                      </div>
                    </div>
                  </template>
                  <template v-else-if="order.status === 'fulfilled'">
                    <button class="btn-disabled btn-small" disabled>Completed</button>
                  </template>
                  <template v-else-if="order.status === 'on_delivery'">
                    <button class="btn-disabled btn-small" disabled>On delivery</button>
                  </template>
                  <template v-else-if="order.status === 'cancelled'">
                    <button class="btn-muted btn-small" disabled>Cancelled</button>
                  </template>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="6" class="empty-message">No orders yet.</td>
              </tr>
            </tbody>
            </table>
          </div>
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
            </div>
            </template>

            <template #headerActions>
              <div class="header-actions-top">
                <div class="header-profile-wrapper" @click.stop>
                  <button class="header-profile-btn" @click="toggleProfileDropdown">
                    <div class="header-avatar">
                      <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
                      <div v-else class="header-avatar-initials">{{ (userProfile.fullName || userProfile.full_name || 'S').charAt(0) }}</div>
                    </div>
                    <div class="header-name">{{ ((userProfile.fullName || userProfile.full_name) ? (userProfile.fullName || userProfile.full_name).toUpperCase() : 'SUPPLIER') }} - {{ (userProfile.branch_name || userProfile.branch || userProfile.branch_id || 'Branch') }}</div>
                  </button>
                  <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
                    <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
                    <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
                  </div>
                </div>
              </div>
            </template>


  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
  <!-- RECEIPT MODAL -->
  <transition name="fade">
    <div v-if="showReceiptModal" class="modal-backdrop">
      <div class="receipt-box">
        <h3>Transaction Receipt</h3>
        <div class="receipt-body">
          <p><strong>Order ID:</strong> {{ receiptData.id }}</p>
          <p><strong>Product:</strong> {{ receiptData.product?.name }}</p>
          <p><strong>Branch:</strong> {{ receiptData.branch?.name || receiptData.branch_id }}</p>
          <p><strong>Quantity:</strong> {{ receiptData.quantity }}</p>
          <p><strong>Total:</strong> {{ formatPrice(receiptData.product?.price * receiptData.quantity) }}</p>
          <p><strong>Status:</strong> <span :class="['status-badge', getStatusClass(receiptData.status)]">{{ receiptData.status }}</span></p>
          <div v-if="receiptData.procurementRequest">
            <p><strong>Procurement Request ID:</strong> {{ receiptData.procurementRequest.id }}</p>
            <p><strong>Procurement Status:</strong> {{ receiptData.procurementRequest.status }}</p>
          </div>
        </div>
        <div class="receipt-actions">
          <button class="btn-secondary" @click="closeReceipt">Close</button>
          <button class="btn-primary" @click="printReceipt">Print</button>
        </div>
      </div>
    </div>
  </transition>
  <!-- Submit product modal for supplier to add product and price -->
  <transition name="fade">
    <div v-if="supplierSubmitModalVisible" class="modal-backdrop" @click.self="closeSupplierSubmitModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>Product Request - Add Product</h3>
          </div>
          <div class="modal-body">
            <div class="form-group full-span">
              <label>Product Name</label>
              <input v-model="submitForm.name" type="text" placeholder="Product name" readonly />
            </div>
            <div class="form-group">
              <label>Unit Price (PHP)</label>
              <input v-model.number="submitForm.price" type="number" step="0.01" placeholder="0.00" />
            </div>
            <div v-if="submitError" class="error-msg">{{ submitError }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeSupplierSubmitModal">Cancel</button>
            <button class="btn-primary" @click="submitProductForm" :disabled="submitSubmitting">{{ submitSubmitting ? 'Submitting...' : 'Submit Product' }}</button>
          </div>
        </div>
      </div>
    </div>
  </transition>
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
import { ref, onMounted, onUnmounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import LogisticsPanelContent from './logistics/LogisticsPanelContent.vue'
import axios from 'axios'
import { showToast } from './toastStore'

const userProfile = ref({})
const dashboardTotals = ref({ totalSuppliers: 0, activeDeliveries: 0, pendingOrders: 0 })
const deliveries = ref([])
const products = ref([])
const loadingProducts = ref(false)
const orders = ref([])
const ordersLoading = ref(false)
const suppliers = ref([])

// UI / modal state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
// Receipt modal state
const showReceiptModal = ref(false)
const receiptData = ref({})
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href
// Supplier submit modal state
const supplierSubmitModalVisible = ref(false)
const submitForm = ref({ name: '', price: null })
const submitSubmitting = ref(false)
const submitError = ref('')
const currentSubmitOrderId = ref(null)
const lastOrderCheck = ref(new Date().toISOString())

// Header/profile dropdown state and owner layout ref
const profileDropdownVisible = ref(false)
const ownerLayout = ref(null)

function toggleProfileDropdown() { profileDropdownVisible.value = !profileDropdownVisible.value }
function closeProfileDropdown() { profileDropdownVisible.value = false }

function openInfoFromHeader() {
  closeProfileDropdown()
  try {
    if (ownerLayout.value && typeof ownerLayout.value.openInfoModal === 'function') {
      ownerLayout.value.openInfoModal()
      return
    }
  } catch (e) {}
  try { window.dispatchEvent(new Event('open-owner-info')); return } catch (e) {}
  const infoBtn = document.querySelector('.admin-info-btn')
  if (infoBtn) infoBtn.click()
}

function triggerLogoutFromHeader() {
  closeProfileDropdown()
  showLogoutConfirm.value = true
}

// Close dropdown when clicking outside
window.addEventListener('click', (e) => { try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {} })

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
    // initialize lastOrderCheck to latest returned order created_at
    try {
      const maxCreated = orders.value.reduce((max, o) => {
        const t = o.created_at || o.createdAt || o.createdAt;
        return t && new Date(t) > new Date(max) ? t : max
      }, lastOrderCheck.value)
      lastOrderCheck.value = maxCreated || lastOrderCheck.value
    } catch (e) {}
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

// Poll for new real (non-broadcast) supplier orders and notify supplier
let _ordersPollTimer = null

function normalizeSupplierOrder(order) {
  if (!order || typeof order !== 'object') return order
  return {
    ...order,
    procurementRequest: order.procurementRequest || order.procurement_request || null,
    branch: order.branch || null,
    product: order.product || null,
  }
}

function startOrdersPolling() {
  stopOrdersPolling()
  _ordersPollTimer = setInterval(async () => {
    try {
      const res = await axios.get('/api/supplier-orders', { withCredentials: true })
      const rawList = res.data.data || res.data || []
      const list = Array.isArray(rawList) ? rawList.map(normalizeSupplierOrder) : []
      // find any non-broadcast orders created after lastOrderCheck
      const newReal = list.filter(o => !o.is_broadcast && o.created_at && new Date(o.created_at) > new Date(lastOrderCheck.value))
      if (newReal && newReal.length > 0) {
        showToast('New order placed for your products', 'info')
        // update orders list and last check
        orders.value = list
        const maxCreated = list.reduce((max, o) => {
          const t = o.created_at || max
          return t && new Date(t) > new Date(max) ? t : max
        }, lastOrderCheck.value)
        lastOrderCheck.value = maxCreated
      }
    } catch (e) {
      // ignore polling errors
    }
  }, 15000)
}

function stopOrdersPolling() {
  if (_ordersPollTimer) {
    clearInterval(_ordersPollTimer)
    _ordersPollTimer = null
  }
}

// Orders for supplier
async function loadOrders() {
  ordersLoading.value = true
  try {
    const res = await axios.get('/api/supplier-orders', { withCredentials: true })
    const rawList = res.data.data || res.data || []
    orders.value = Array.isArray(rawList) ? rawList.map(normalizeSupplierOrder) : []
    dashboardTotals.value.pendingOrders = orders.value.filter(o => o.status === 'pending').length
    // fulfilled count could be used elsewhere
  } catch (e) {
    console.error('Failed to load orders', e)
  } finally {
    ordersLoading.value = false
  }
}

async function fulfillOrder(id) {
  // kept for backward compatibility but unused in the new flow
  return
}

async function completeTransaction(id) {
  if (!confirm('Complete this transaction and show receipt?')) return
  try {
    // Mark the supplier order as on_delivery so the backend finalizes procurement
    const res = await axios.put(`/api/supplier-orders/${id}/status`, { status: 'on_delivery' }, { withCredentials: true })
    if (res && res.data) {
      receiptData.value = normalizeSupplierOrder(res.data)
      showReceiptModal.value = true
    }
    await loadOrders()
  } catch (e) {
    showToast('Failed to complete transaction', 'error')
  }
}

function openSupplierSubmitModal(order) {
  // Prefill product name if procurement request provides it
  submitError.value = ''
  submitForm.value = { name: '', price: null }
  currentSubmitOrderId.value = null
  if (!order) return
  currentSubmitOrderId.value = order.id
  // Try to prefill from procurementRequest or product name
  const suggested = order.procurementRequest?.product?.name || order.product?.name || ''
  submitForm.value.name = suggested
  supplierSubmitModalVisible.value = true
}

function closeSupplierSubmitModal() {
  if (submitSubmitting.value) return
  supplierSubmitModalVisible.value = false
  submitError.value = ''
  submitForm.value = { name: '', price: null }
  currentSubmitOrderId.value = null
}

async function submitProductForm() {
  if (!currentSubmitOrderId.value) return
  if (!submitForm.value.name) { submitError.value = 'Product name is required'; return }
  if (submitForm.value.price === null || submitForm.value.price === undefined) { submitError.value = 'Price is required'; return }
  submitSubmitting.value = true
  submitError.value = ''
  try {
  const payload = { name: submitForm.value.name, price: submitForm.value.price }
    const res = await axios.post(`/api/supplier-orders/${currentSubmitOrderId.value}/submit-product`, payload, { withCredentials: true })
    if (res && res.data && res.data.ok) {
      showToast('Product submitted and linked to order', 'success')
      await loadOrders()
      await loadProducts()
      closeSupplierSubmitModal()
    } else {
      const msg = res.data?.error || res.data?.message || 'Failed to submit product'
      submitError.value = msg
      showToast(msg, 'error')
    }
  } catch (e) {
    console.error('submitProductForm failed', e)
    const msg = e.response?.data?.error || e.response?.data?.message || 'Failed to submit product'
    submitError.value = msg
    showToast(msg, 'error')
  } finally {
    submitSubmitting.value = false
  }
}

function closeReceipt() {
  showReceiptModal.value = false
  receiptData.value = {}
}

function printReceipt() {
  try {
    const html = document.createElement('div')
    html.innerHTML = `
      <h3>Transaction Receipt</h3>
      <p>Order ID: ${receiptData.value.id || ''}</p>
      <p>Product: ${receiptData.value.product?.name || ''}</p>
      <p>Branch: ${receiptData.value.branch?.name || receiptData.value.branch_id || ''}</p>
      <p>Quantity: ${receiptData.value.quantity || ''}</p>
      <p>Total: ${receiptData.value.product ? (Number(receiptData.value.product.price || 0) * Number(receiptData.value.quantity || 0)).toFixed(2) : ''}</p>
    `
    const w = window.open('', '_blank')
    if (!w) return alert('Unable to open print window')
    w.document.write('<html><head><title>Receipt</title></head><body>')
    w.document.write(html.innerHTML)
    w.document.write('</body></html>')
    w.document.close()
    w.focus()
    w.print()
    w.close()
    } catch (e) {
    console.warn('Print failed', e)
    showToast('Failed to print receipt', 'error')
  }
}

function getStatusClass(status) {
  switch (status) {
    case 'fulfilled': return 'status-approved'
    case 'cancelled': return 'status-rejected'
    case 'on_delivery': return 'status-on-delivery'
    default: return 'status-pending'
  }
}

function canSubmitProduct(order) {
  // Allow supplier to submit a product when:
  // - There's no linked product yet
  // - OR the linked product is not from this supplier
  // - OR the linked product has no positive price
  try {
    const myId = userProfile.value?.id
    const prod = order.product
    if (!prod) return true
    // If product has no price or price <= 0 -> supplier should supply price
    const price = Number(prod.price || 0)
    if (isNaN(price) || price <= 0) return true
    // If product.supplier_id is different from the current supplier (order.supplier_id), allow submission
    // order.supplier_id should equal current user's id for supplier orders
    if (prod.supplier_id && Number(prod.supplier_id) !== Number(order.supplier_id)) return true
    // Otherwise product already provided by this supplier with price > 0 -> no need to submit
    return false
  } catch (e) {
    return true
  }
}

function canCompleteTransaction(order) {
  try {
    if (!order || order.status !== 'pending') return false
    if (!order.product || !order.product.id) return false

    // Supplier can complete transaction only after procurement/finance flow has
    // reached order-ready or delivery states for the linked request.
    const reqStatus = order.procurementRequest?.status || ''
    const allowed = ['pending_order_to_supplier', 'delivery_pending', 'ongoing_delivery', 'on_delivery']
    return allowed.includes(reqStatus)
  } catch (e) {
    return false
  }
}

function formatPrice(val) {
  if (val === null || val === undefined) return '₱0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '₱0.00'
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dt) {
  try {
    const d = new Date(dt)
    if (isNaN(d.getTime())) return ''
    return d.toLocaleString()
  } catch (e) {
    return ''
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

/* Receipt modal styles */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:1200 }
.receipt-box { background:#fff; padding:1rem 1.25rem; border-radius:10px; width:420px; box-shadow:0 12px 36px rgba(15,23,42,0.18); border:1px solid #eef2f6 }
.receipt-box h3 { margin:0 0 0.6rem 0 }
.receipt-body p { margin:6px 0 }
.receipt-actions { display:flex; justify-content:flex-end; gap:0.5rem; margin-top:0.75rem }
.receipt-actions .btn-secondary { background:#f3f4f6; border:1px solid #e5e7eb; padding:6px 10px; border-radius:6px }
.receipt-actions .btn-primary { background:#0b6e3a; color:#fff; padding:6px 10px; border-radius:6px; border:none }

/* Layout refinements for SupplierPanel */
.panel-content { display:flex; flex-direction:column; gap:0.9rem }
.panel-section { margin-bottom:0.75rem }
.table-container { overflow-x:auto; background:transparent; border-radius:8px }
.data-table { width:100%; border-collapse:separate; border-spacing:0; min-width:720px }
.data-table th, .data-table td { padding:10px 12px; border-bottom:1px solid #eef2f6; vertical-align:middle }
.data-table thead th { background:transparent; color:#374151; font-weight:700; text-align:left }
.action-cell { min-width:200px; white-space:nowrap; text-align:right }
.status-badge { display:inline-block; padding:4px 8px; border-radius:8px; font-size:0.9rem }
.btn-small { padding:6px 8px; font-size:0.85rem }

.product-grid { gap:0.75rem }
.product-card { display:flex; flex-direction:column; justify-content:space-between }

@media (max-width: 900px) {
  .overview-grid { flex-direction:column }
  .data-table { min-width:600px }
  .product-grid { grid-template-columns: repeat(auto-fill,minmax(160px,1fr)) }
}

@media (max-width: 480px) {
  .data-table { min-width:480px }
  .action-cell { min-width:160px }
}

/* Scroll container similar to ProcurementManagerPanel */
.requests-container { background:transparent; border-radius:8px }
.requests-scroll { max-height:360px; overflow:auto; padding-right:6px }
.requests-scroll .data-table { min-width:640px }

/* When profile column is hidden, lay out main + side like other manager panels */
:deep(.admin-layout.no-profile-column) {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 1rem;
}

:deep(.admin-layout.no-profile-column) .admin-main { width: 100%; }
:deep(.admin-layout.no-profile-column) .admin-side { width: 360px; }

/* Ensure announcements panel fits inside the side column and doesn't overlap */
.announcements-panel { max-width: 100%; box-sizing: border-box }

</style>
