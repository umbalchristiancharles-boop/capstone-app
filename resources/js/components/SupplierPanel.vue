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
                <div class="product-card-header">
                  <div class="product-name">{{ p.name }}</div>
                  <button class="btn-edit" @click="openEditProductModal(p)" title="Edit product">✎</button>
                </div>
                <div class="product-category" v-if="p.category">{{ p.category }}</div>
                <div class="product-type" v-if="p.per_pack_or_individual" :class="'type-' + p.per_pack_or_individual">
                  {{ formatPricingType(p.per_pack_or_individual) }}
                </div>
                <div class="product-meta">
                  <div class="product-price">{{ formatPrice(p.price) }}</div>
                  <div class="product-expiry" v-if="p.expires_at">
                    <span class="expiry-label">Expires:</span> {{ formatDate(p.expires_at) }}
                  </div>
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
              <label>Category (e.g., Beverage, Meat, Vegetable, Condiment)</label>
              <select v-model="submitForm.category">
                <option value="">Select a category</option>
                <option value="Beverage">Beverage</option>
                <option value="Meat">Meat</option>
                <option value="Vegetable">Vegetable</option>
                <option value="Grain">Grain</option>
                <option value="Condiment">Condiment</option>
                <option value="Dairy">Dairy</option>
                <option value="Egg">Egg</option>
                <option value="Spice">Spice</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="form-group full-span">
              <label>Pricing Type</label>
              <div class="pricing-type-options">
                <div class="option-group">
                  <input 
                    type="radio" 
                    id="submit-type-individual" 
                    value="individual" 
                    v-model="submitForm.per_pack_or_individual"
                  />
                  <label for="submit-type-individual" class="option-label">
                    <span class="option-badge type-individual">Individual</span>
                    <span class="option-desc">Sold by individual units</span>
                  </label>
                </div>
                <div class="option-group">
                  <input 
                    type="radio" 
                    id="submit-type-per_pack" 
                    value="per_pack" 
                    v-model="submitForm.per_pack_or_individual"
                  />
                  <label for="submit-type-per_pack" class="option-label">
                    <span class="option-badge type-per_pack">Per Pack</span>
                    <span class="option-desc">Sold in packs only</span>
                  </label>
                </div>
                <div class="option-group">
                  <input 
                    type="radio" 
                    id="submit-type-both" 
                    value="both" 
                    v-model="submitForm.per_pack_or_individual"
                  />
                  <label for="submit-type-both" class="option-label">
                    <span class="option-badge type-both">Both Options</span>
                    <span class="option-desc">Can be sold both ways</span>
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Unit Price (PHP)</label>
              <input v-model.number="submitForm.price" type="number" step="0.01" placeholder="0.00" />
            </div>
            <div class="form-group">
              <label>Expiration Date</label>
              <input v-model="submitForm.expires_at" type="datetime-local" />
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

  <!-- EDIT PRODUCT MODAL - Edit one field at a time -->
  <transition name="fade">
    <div v-if="editProductModalVisible" class="modal-backdrop" @click.self="closeEditProductModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>Edit Product: {{ editForm.name }}</h3>
          </div>
          <div class="modal-body">
            <!-- Field selector -->
            <div class="field-selector">
              <button 
                v-for="field in editFields" 
                :key="field.id"
                class="field-btn"
                :class="{ active: editFieldType === field.id }"
                @click="selectEditField(field.id)"
              >
                {{ field.label }}
              </button>
            </div>

            <!-- Category field -->
            <div v-if="editFieldType === 'category'" class="edit-field-section">
              <label>Edit Category</label>
              <select v-model="editForm.category" class="full-width-input">
                <option value="">Select a category</option>
                <option value="Beverage">Beverage</option>
                <option value="Meat">Meat</option>
                <option value="Vegetable">Vegetable</option>
                <option value="Grain">Grain</option>
                <option value="Condiment">Condiment</option>
                <option value="Dairy">Dairy</option>
                <option value="Egg">Egg</option>
                <option value="Spice">Spice</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- Pricing Type field -->
            <div v-if="editFieldType === 'pricing'" class="edit-field-section">
              <label>Edit Pricing Type</label>
              <div class="pricing-type-options">
                <div class="option-group">
                  <input 
                    type="radio" 
                    id="edit-type-individual" 
                    value="individual" 
                    v-model="editForm.per_pack_or_individual"
                  />
                  <label for="edit-type-individual" class="option-label">
                    <span class="option-badge type-individual">Individual</span>
                    <span class="option-desc">Sold by individual units</span>
                  </label>
                </div>
                <div class="option-group">
                  <input 
                    type="radio" 
                    id="edit-type-per_pack" 
                    value="per_pack" 
                    v-model="editForm.per_pack_or_individual"
                  />
                  <label for="edit-type-per_pack" class="option-label">
                    <span class="option-badge type-per_pack">Per Pack</span>
                    <span class="option-desc">Sold in packs only</span>
                  </label>
                </div>
                <div class="option-group">
                  <input 
                    type="radio" 
                    id="edit-type-both" 
                    value="both" 
                    v-model="editForm.per_pack_or_individual"
                  />
                  <label for="edit-type-both" class="option-label">
                    <span class="option-badge type-both">Both Options</span>
                    <span class="option-desc">Can be sold both ways</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Price field -->
            <div v-if="editFieldType === 'price'" class="edit-field-section">
              <label>Edit Unit Price (PHP)</label>
              <input v-model.number="editForm.price" type="number" step="0.01" placeholder="0.00" class="full-width-input" />
            </div>

            <!-- Expiration Date field -->
            <div v-if="editFieldType === 'expiration'" class="edit-field-section">
              <label>Edit Expiration Date</label>
              <input v-model="editForm.expires_at" type="datetime-local" class="full-width-input" />
            </div>

            <div v-if="editError" class="error-msg">{{ editError }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeEditProductModal">Cancel</button>
            <button class="btn-primary" @click="saveProductChanges" :disabled="editSubmitting || !editFieldType">{{ editSubmitting ? 'Saving...' : 'Save Changes' }}</button>
          </div>
        </div>
      </div>
    </div>
  </transition>

  <!-- FULLSCREEN LOADING OVERLAY -->
  <LoadingOverlay :show="showOverlay" :text="overlayText" :logo-src="logoImg" />
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import LogisticsPanelContent from './logistics/LogisticsPanelContent.vue'
import LoadingOverlay from './LoadingOverlay.vue'
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
const submitForm = ref({ name: '', price: null, category: '', per_pack_or_individual: '', expires_at: '' })
const submitSubmitting = ref(false)
const submitError = ref('')
const currentSubmitOrderId = ref(null)
const lastOrderCheck = ref(new Date().toISOString())

// Edit product modal state
const editProductModalVisible = ref(false)
const editFieldType = ref(null) // 'category', 'pricing', 'price', 'expiration'
const editFields = [
  { id: 'category', label: 'Category' },
  { id: 'pricing', label: 'Pricing Type' },
  { id: 'price', label: 'Price' },
  { id: 'expiration', label: 'Expiration' }
]
const editForm = ref({ id: null, name: '', price: null, category: '', per_pack_or_individual: '', expires_at: '' })
const editSubmitting = ref(false)
const editError = ref('')

function selectEditField(fieldId) {
  editFieldType.value = fieldId
  editError.value = ''
}

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
    // initialize lastOrderCheck to latest returned order timestamp (created or updated)
    try {
      const maxTs = orders.value.reduce((max, o) => {
        const candidates = [o.updated_at || o.updatedAt, o.created_at || o.createdAt].filter(Boolean)
        const latest = candidates.reduce((m, t) => (t && new Date(t) > new Date(m) ? t : m), max)
        return latest
      }, lastOrderCheck.value)
      lastOrderCheck.value = maxTs || lastOrderCheck.value
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
      // find any non-broadcast orders created or updated after lastOrderCheck
      const newReal = list.filter(o => {
        const created = o.created_at || o.createdAt
        const updated = o.updated_at || o.updatedAt
        const createdNew = created && new Date(created) > new Date(lastOrderCheck.value)
        const updatedNew = updated && new Date(updated) > new Date(lastOrderCheck.value)
        return !o.is_broadcast && (createdNew || updatedNew)
      })
      if (newReal && newReal.length > 0) {
        showToast('New order placed for your products', 'info')
        // update orders list and last check (consider both created_at and updated_at)
        orders.value = list
        const maxTs = list.reduce((max, o) => {
          const candidates = [o.created_at || o.createdAt, o.updated_at || o.updatedAt].filter(Boolean)
          const latest = candidates.reduce((m, t) => (t && new Date(t) > new Date(m) ? t : m), max)
          return latest
        }, lastOrderCheck.value)
        lastOrderCheck.value = maxTs
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
    try {
      await loadOrders()
    } catch (loadErr) {
      console.error('Failed to reload orders', loadErr)
    }
  } catch (e) {
    console.error('completeTransaction failed', e)
    showToast('Failed to complete transaction', 'error')
  }
}

function openSupplierSubmitModal(order) {
  // Prefill product name if procurement request provides it
  submitError.value = ''
  submitForm.value = { name: '', price: null, category: '', per_pack_or_individual: '', expires_at: '' }
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
  submitForm.value = { name: '', price: null, category: '', per_pack_or_individual: '', expires_at: '' }
  currentSubmitOrderId.value = null
}

function openEditProductModal(product) {
  if (!product) return
  editError.value = ''
  editFieldType.value = 'price' // Default to editing price
  editForm.value = {
    id: product.id,
    name: product.name,
    price: product.price,
    category: product.category || '',
    per_pack_or_individual: product.per_pack_or_individual || '',
    expires_at: product.expires_at ? formatDateTimeLocal(product.expires_at) : ''
  }
  editProductModalVisible.value = true
}

function closeEditProductModal() {
  if (editSubmitting.value) return
  editProductModalVisible.value = false
  editError.value = ''
  editFieldType.value = null
  editForm.value = { id: null, name: '', price: null, category: '', per_pack_or_individual: '', expires_at: '' }
}

async function saveProductChanges() {
  if (!editForm.value.id) return
  if (!editFieldType.value) { editError.value = 'Please select a field to edit'; return }
  
  // Validate only the selected field
  if (editFieldType.value === 'category' && !editForm.value.category) { 
    editError.value = 'Category is required'; return 
  }
  if (editFieldType.value === 'pricing' && !editForm.value.per_pack_or_individual) { 
    editError.value = 'Pricing type is required'; return 
  }
  if (editFieldType.value === 'price' && (!editForm.value.price || editForm.value.price <= 0)) { 
    editError.value = 'Price is required and must be greater than 0'; return 
  }
  if (editFieldType.value === 'expiration' && !editForm.value.expires_at) { 
    editError.value = 'Expiration date is required'; return 
  }
  
  editSubmitting.value = true
  editError.value = ''
  try {
    // Build payload with only the selected field
    const payload = {}
    if (editFieldType.value === 'category') payload.category = editForm.value.category
    if (editFieldType.value === 'pricing') payload.per_pack_or_individual = editForm.value.per_pack_or_individual
    if (editFieldType.value === 'price') payload.price = editForm.value.price
    if (editFieldType.value === 'expiration') payload.expires_at = editForm.value.expires_at
    
    const res = await axios.put(`/api/staff/inventory/products/${editForm.value.id}`, payload, { withCredentials: true })
    if (res && res.data) {
      showToast('Product updated successfully', 'success')
      await loadProducts()
      closeEditProductModal()
    } else {
      const msg = res.data?.error || res.data?.message || 'Failed to update product'
      editError.value = msg
      showToast(msg, 'error')
    }
  } catch (e) {
    console.error('saveProductChanges failed', e)
    const msg = e.response?.data?.error || e.response?.data?.message || 'Failed to update product'
    editError.value = msg
    showToast(msg, 'error')
  } finally {
    editSubmitting.value = false
  }
}

async function submitProductForm() {
  if (!currentSubmitOrderId.value) return
  if (!submitForm.value.name) { submitError.value = 'Product name is required'; return }
  if (!submitForm.value.category) { submitError.value = 'Category is required'; return }
  if (!submitForm.value.per_pack_or_individual) { submitError.value = 'Pricing type is required'; return }
  if (submitForm.value.price === null || submitForm.value.price === undefined) { submitError.value = 'Price is required'; return }
  if (!submitForm.value.expires_at) { submitError.value = 'Expiration date is required'; return }
  submitSubmitting.value = true
  submitError.value = ''
  try {
    const payload = { 
      name: submitForm.value.name, 
      price: submitForm.value.price,
      category: submitForm.value.category,
      per_pack_or_individual: submitForm.value.per_pack_or_individual,
      expires_at: submitForm.value.expires_at
    }
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

    // Only show "Transaction complete" for actual placed orders (is_broadcast = false)
    // NOT for initial submission broadcasts (is_broadcast = true)
    // This ensures only the selected supplier sees the complete button
    if (order.is_broadcast !== false) return false

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

function formatDateTimeLocal(dt) {
  try {
    if (!dt) return ''
    const d = new Date(dt)
    if (isNaN(d.getTime())) return ''
    // Format for datetime-local input: YYYY-MM-DDTHH:mm
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${year}-${month}-${day}T${hours}:${minutes}`
  } catch (e) {
    return ''
  }
}

function formatPricingType(type) {
  const typeMap = {
    'individual': 'Individual',
    'per_pack': 'Per Pack',
    'both': 'Both (Individual & Per Pack)'
  }
  return typeMap[type] || type
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
.product-name { font-weight:700; color:#0f172a; font-size:0.95rem }
.product-category { color:#7c3aed; font-size:0.8rem; font-weight:600; margin-top:4px; text-transform:capitalize }
.product-type { display:inline-block; font-size:0.75rem; font-weight:600; padding:3px 8px; border-radius:6px; margin-top:4px; text-transform:capitalize }
.product-type.type-individual { background:#dbeafe; color:#1e40af }
.product-type.type-per_pack { background:#d1fae5; color:#065f46 }
.product-type.type-both { background:#fef3c7; color:#92400e }
.product-meta { display:flex; flex-direction:column; gap:6px; margin-top:8px }
.product-price { color:#0b6e3a; font-weight:700; font-size:0.95rem }
.product-expiry { color:#7c2d12; font-size:0.8rem; display:flex; gap:4px }
.expiry-label { font-weight:600 }
.product-stock { color:#6b7280 }
.product-card-header { display:flex; justify-content:space-between; align-items:flex-start; gap:8px }
.btn-edit { background:none; border:none; color:#7c3aed; cursor:pointer; font-size:1.2rem; padding:0 }
.btn-edit:hover { opacity:.7 }

/* Pricing type options */
.pricing-type-options { display:flex; flex-direction:column; gap:10px; margin-top:6px; padding:8px; background:#f9fafb; border-radius:8px; border:1px solid #e5e7eb }
.option-group { display:flex; align-items:flex-start; gap:12px; cursor:pointer; padding:8px; border-radius:6px; transition:background 0.2s }
.option-group:hover { background:#f3f4f6 }
.option-group input[type="radio"] { margin-top:5px; cursor:pointer; accent-color:#7c3aed }
.option-label { display:flex; flex-direction:column; gap:4px; cursor:pointer; flex:1 }
.option-badge { display:inline-block; padding:4px 10px; border-radius:6px; font-size:0.85rem; font-weight:600; width:fit-content }
.option-desc { font-size:0.8rem; color:#6b7280 }

/* Receipt modal styles */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:1200 }
.modal { position:relative; max-height:90vh; overflow-y:auto }
.modal-card { background:#fff; border-radius:12px; box-shadow:0 18px 54px rgba(15,23,42,0.12); min-width:500px; max-width:600px; overflow:hidden }
.modal-header { background:#f9fafb; padding:1.25rem 1.5rem; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center }
.modal-header h3 { margin:0; font-size:1.1rem; font-weight:700; color:#111827 }
.modal-body { padding:1.5rem; max-height:calc(90vh - 180px); overflow-y:auto; display:flex; flex-direction:column; gap:1rem }
.modal-footer { padding:1rem 1.5rem; border-top:1px solid #e5e7eb; display:flex; gap:0.75rem; justify-content:flex-end; background:#f9fafb }
.form-group { display:flex; flex-direction:column; gap:6px }
.form-group.full-span { grid-column:1/-1 }
.form-group label { font-weight:600; color:#374151; font-size:0.9rem }
.form-group input, .form-group select { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:0.95rem }
.form-group input:focus, .form-group select:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.1) }
.error-msg { background:#fee2e2; color:#dc2626; padding:10px 12px; border-radius:8px; font-size:0.9rem; margin-top:8px }

/* Field selector for edit modal */
.field-selector { display:flex; flex-wrap:wrap; gap:0.5rem; padding:0.5rem 0; border-bottom:1px solid #e5e7eb; margin-bottom:1rem }
.field-btn { padding:8px 12px; border-radius:6px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:500; cursor:pointer; transition:all 0.2s }
.field-btn:hover { border-color:#7c3aed; color:#7c3aed }
.field-btn.active { background:#7c3aed; color:#fff; border-color:#7c3aed }

/* Edit field section */
.edit-field-section { display:flex; flex-direction:column; gap:0.75rem }
.edit-field-section label { font-weight:600; color:#374151; font-size:0.95rem }
.full-width-input { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:0.95rem; width:100% }
.full-width-input:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.1) }


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
