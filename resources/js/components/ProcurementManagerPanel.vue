<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Manager Procurement Panel'"
    :panelDescription="'Manage procurement staff, view procurement reports, and monitor procurement status.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="hr-stats-grid">
        <div class="hr-stat-card hr-stat-card--total">
          <div class="hr-stat-icon">
            <!-- icon reused from HR panel -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Total Suppliers</span>
{{ dashboardTotals.totalSuppliers }}
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--active">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Active Suppliers</span>
{{ dashboardTotals.activeSuppliers }}
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--leave">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Pending Requests</span>
            <span class="hr-stat-value">{{ dashboardTotals.pendingRequests }}</span>
          </div>
        </div>
      </div>
      <div class="panel-actions" style="margin-top:1rem">
        <button class="btn-primary" @click="openAddSupplier">Add Supplier</button>
      </div>
      <section class="supplier-products" style="margin-top:1rem">
        <h2>Supplier Products (this branch)</h2>
        <div v-if="loadingProducts">Loading products...</div>
        <div v-else-if="!products.length">No products available in your branch.</div>
        <div v-else>
          <!-- Pending Supplier Products UI removed per request -->

          <div>
            <h3 style="margin:0 0 8px 0">Published Products ({{ publishedProducts.length }})</h3>
            <div class="product-grid">
              <div v-for="p in publishedProducts" :key="p.id" class="product-card">
                <div class="product-name">{{ p.name }}</div>
                <div class="product-meta">
                  <div class="product-price">{{ formatPrice(p.price) }}</div>
                  <div class="supplier-badge">{{ p.supplier_name || 'Unknown Supplier' }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="budget-requests" style="margin-top:1rem">
        <h2>Budget Requests</h2>
        <p class="section-description">Create and view your branch budget requests.</p>

        <div style="margin-bottom:0.5rem">
          <button class="btn-primary" v-if="!showBudgetForm" @click="showBudgetForm = true">+ New Budget Request</button>
          <button class="btn-outline" v-else @click="showBudgetForm = false">Cancel</button>
        </div>

        <div v-if="showBudgetForm" class="budget-form" style="margin-top:0.75rem; max-width:520px">
          <div class="form-group full-span">
            <label>Purpose</label>
            <textarea v-model="budgetForm.purpose" rows="3" placeholder="Describe the purpose of the budget"></textarea>
          </div>
          <div class="form-group">
            <label>Requested Amount</label>
            <input v-model="budgetForm.requested_amount" type="number" step="0.01" placeholder="0.00" />
          </div>
          <div style="margin-top:8px">
            <button class="btn-primary" @click="submitBudgetRequest" :disabled="budgetSubmitting">{{ budgetSubmitting ? 'Submitting...' : 'Submit Request' }}</button>
          </div>
          <div v-if="budgetError" class="error-msg" style="margin-top:8px">{{ budgetError }}</div>
        </div>

        <div style="margin-top:1rem">
          <h3>My Budget Requests</h3>
          <div v-if="budgetLoading">Loading...</div>
          <div v-else-if="!budgetRequests.length">No budget requests.</div>
          <table v-else class="data-table" style="width:100%">
            <thead>
              <tr><th>Date</th><th>Purpose</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in budgetRequests" :key="r.id">
                <td>{{ formatDate(r.date_requested) }}</td>
                <td>{{ r.purpose }}</td>
                <td>₱{{ r.requested_amount }}</td>
                <td>{{ r.status }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      <section class="requested-products" style="margin-top:1rem">
        <h2>Requests From Logistics</h2>
        <p class="section-description">Inventory requests sent by Logistics Managers in your branch.</p>

        <div v-if="requestedProductsLoading">Loading requests...</div>
        <div v-else-if="!requestedProducts.length">No requests from logistics.</div>
        <div v-else>
          <div style="display:flex; gap:0.5rem; align-items:center; margin-bottom:1rem">
            <h3 style="margin:0">Pending Logistics Requests ({{ requestedProducts.length }})</h3>
            <button class="btn-primary" @click="loadRequestedProducts" style="padding:6px 12px; font-size:0.85rem">🔄 Refresh</button>
          </div>
          <div class="product-grid">
            <div v-for="p in requestedProducts" :key="'req-'+p.id" class="product-card">
              <div class="product-name">{{ p.name }}</div>
              <div class="product-meta">
                <div class="product-price">{{ formatPrice(p.price) }}</div>
                <div>
                  <template v-if="(p.procurement_status === 'pending' || p.status === 'pending') && !p.needs_supplier && (p.acknowledge_allowed === undefined ? true : p.acknowledge_allowed)">
                    <button class="btn-primary" @click="acknowledgeRequest(p)" style="padding:6px 10px; border-radius:8px">Acknowledge</button>
                  </template>
                  <template v-else-if="(p.procurement_status === 'pending' || p.status === 'pending') && p.needs_supplier">
                    <button class="btn-primary" @click="requestSupplier(p)" style="padding:6px 10px; border-radius:8px; background:#f59e0b;">Request Supplier for Product</button>
                  </template>
                  <template v-else-if="p.procurement_status === 'budget_pending' || p.status === 'budget_pending'">
                    <button class="btn-outline" disabled style="padding:6px 10px; border-radius:8px">Budget to be received</button>
                  </template>
                  <template v-else-if="p.procurement_status === 'pending_order_to_supplier' || p.status === 'pending_order_to_supplier' || p.procurement_status === 'ongoing_delivery' || p.status === 'ongoing_delivery'">
                    <div v-if="p.existingOrder" style="display:flex; gap:0.5rem; align-items:center">
                      <div class="status-badge" style="background:#fbbf24; color:#92400e; padding:6px 10px; border-radius:8px; font-size:0.9rem; font-weight:600;">
                        Transaction Pending (ID: {{ p.existingOrder.id }})
                      </div>
                      <div v-if="(p.existingOrder && (p.existingOrder.status === 'on_delivery' || p.existingOrder.status === 'ongoing_delivery' || p.existingOrder.status === 'fulfilled')) || p.procurement_status === 'delivery_pending' || p.procurement_status === 'ongoing_delivery'">
                        <button class="btn-primary" @click="markDeliveryComplete(p)" :disabled="isCompletingDelivery" style="padding:6px 10px; border-radius:8px">{{ isCompletingDelivery ? 'Submitting...' : 'Delivery complete' }}</button>
                      </div>
                    </div>
                    <div v-else-if="p.procurement_status === 'ongoing_delivery' || p.status === 'ongoing_delivery'">
                      <button class="btn-primary" @click="markDeliveryComplete(p)" :disabled="isCompletingDelivery" style="padding:6px 10px; border-radius:8px">{{ isCompletingDelivery ? 'Submitting...' : 'Delivery complete' }}</button>
                    </div>
                      <div v-else>
                      <button class="btn-primary" 
                        @click="placeOrder(p)" 
                        :disabled="isPlacingOrder || (p.procurementRequest && !p.procurementRequest.supplier_confirmed) || (p.supplier_confirmed === false)"
                        style="padding:6px 10px; border-radius:8px">
                        {{ isPlacingOrder ? 'Placing...' : 'Place Order' }}
                      </button>
                      <div v-if="(p.procurementRequest && !p.procurementRequest.supplier_confirmed) || (p.supplier_confirmed === false)" style="margin-top:6px; color:#92400e; font-weight:600; font-size:0.9rem">
                        Waiting for supplier confirmation
                      </div>
                    </div>
                  </template>
                  <template v-else>
                    <button class="btn-outline" disabled style="padding:6px 10px; border-radius:8px">Unavailable</button>
                  </template>
                </div>
              </div>
              <div class="supplier-badge" style="margin-top:6px">{{ p.supplier_name || (p.supplier?.full_name || 'Unknown Supplier') }}</div>
            </div>
          </div>
        </div>
      </section>
      <transition name="fade">
        <div v-if="showAddModal" class="modal-backdrop" @click.self="closeAddSupplier">
          <div class="modal">
            <div class="modal-card">
              <div class="modal-header">
                <h3>Create Supplier Account</h3>
              </div>
              <div class="modal-body">
                <div class="form-group full-span">
                  <label>Full Name</label>
                  <input v-model="supplierForm.fullName" type="text" placeholder="Supplier full name" />
                </div>

                <div class="form-group full-span">
                  <label>Business Name</label>
                  <input v-model="supplierForm.businessName" type="text" placeholder="Company/Business name" />
                </div>

                <div class="form-group">
                  <label>Username</label>
                  <input v-model="supplierForm.username" type="text" placeholder="username" />
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input v-model="supplierForm.email" type="email" placeholder="supplier@example.com" />
                </div>

                <div class="form-group">
                  <label>Phone</label>
                  <input v-model="supplierForm.phone" type="text" placeholder="optional" />
                </div>

                <div class="form-group password-group">
                  <label>Default Password</label>
                  <div class="password-display-container">
                    <!-- Password Display Card -->
                    <div class="password-display-card">
                      <div class="password-display-label">Default Password (will be set automatically):</div>
                      <div class="password-display-value">
                        <span class="password-text">{{ fetchedDefaultPassword || 'Chikintayo_123' }}</span>
                        <button type="button" class="btn btn-primary btn-copy" @click="copyDefaultToClipboard">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                          </svg>
                          Copy Password
                        </button>
                      </div>
                      <div class="form-hint">This password will be assigned to the supplier account. Leave blank to use default (backend auto-generates if needed).</div>
                    </div>
                    
                    <!-- Loading state -->
                    <div v-if="fetchingDefaultPassword" class="password-loading">
                      <span style="color:#6b7280; font-size:0.9rem;">Loading default password...</span>
                    </div>
                  </div>
                </div>

                <div v-if="formError" class="error-msg">{{ formError }}</div>
                <div v-if="formSuccess" class="success-msg">{{ formSuccess }}</div>
              </div>
              <div class="modal-footer">
                <button class="btn-outline" @click="closeAddSupplier" :disabled="isSubmitting">Cancel</button>
                <button class="btn-primary" @click="submitAddSupplier" :disabled="isSubmitting">Create</button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </template>

    <template #side>
      <section class="panel-block hr-settings-panel">
        <div class="panel-header"><h2>Procurement Settings</h2></div>
        <div class="panel-body panel-body--list">
          <div class="side-item"><span>View procurement orders and supplier info</span></div>
        </div>
      </section>
    </template>
  
  </OwnerPanelLayout>

  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Procurement Manager Panel?</h3>
        <p>This will end your current session for Chikin Tayo Manager.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>
  <!-- Supplier selection modal -->
  <transition name="fade">
    <div v-if="supplierModalVisible" class="modal-backdrop" @click.self="closeSupplierModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>Select Supplier</h3>
          </div>
          <div class="modal-body">
            <div class="form-group full-span">
              <label>Choose a supplier to fulfill: <strong>{{ pendingOrderProduct?.name || '' }}</strong></label>
            </div>
            <div class="form-group full-span">
              <div v-if="supplierLoading">Loading suppliers...</div>
              <div v-else-if="!supplierList.length">No suppliers available.</div>
              <div v-else style="max-height:260px; overflow:auto">
                <div v-for="s in supplierList" :key="s.id" style="display:flex; align-items:center; gap:0.5rem; padding:6px 0">
                  <input type="radio" :id="'sup-'+s.id" :value="s.id" v-model="selectedSupplierId" />
                  <label :for="'sup-'+s.id">{{ s.full_name || s.username }} ({{ s.email || 'no-email' }})</label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Quantity (optional)</label>
              <input type="number" v-model.number="pendingOrderQty" min="1" />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeSupplierModal">Cancel</button>
            <button class="btn-primary" @click="confirmSupplierSelection">Confirm</button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const router = useRouter()
const userProfile = ref({})
const dashboardTotals = ref({ totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 })
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)

// Budget request state
const budgetRequests = ref([])
const budgetLoading = ref(false)
const showBudgetForm = ref(false)
const budgetForm = ref({ purpose: '', requested_amount: '' })
const budgetSubmitting = ref(false)
const budgetError = ref('')

// Products for procurement manager (branch-scoped)
const products = ref([])
const loadingProducts = ref(false)

const pendingProducts = computed(() => (products.value || []).filter(p => !p.is_published))
const publishedProducts = computed(() => (products.value || []).filter(p => p.is_published))

// Requested products (logistics requests)
const requestedProducts = ref([])
const requestedProductsLoading = ref(false)
const isPlacingOrder = ref(false)
const isCompletingDelivery = ref(false)

// Add Supplier modal state
const showAddModal = ref(false)

const isSubmitting = ref(false)
const supplierForm = ref({ 
  username: '', 
  email: '', 
  fullName: '', 
  businessName: '',
  phone: '', 
  password: '' 
})
const formError = ref('')
const formSuccess = ref('')

// Default password state
const fetchedDefaultPassword = ref(null)
const fetchingDefaultPassword = ref(false)

async function refreshAllData() {
  try {
    const dash = await axios.get('/api/manager/procurement/dashboard', { withCredentials: true })
    dashboardTotals.value = dash.data || {}
  } catch (e) {
    dashboardTotals.value = { totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 }
  }
}

function goToStaffManagement() {
  window.location.href = '/manager/procurement/staff-management'
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/procurement/profile', { withCredentials: true })
    userProfile.value = res.data.user || {}
  } catch (e) {
    // ignore
  }
  await refreshAllData()
  try {
    await loadProducts()
  } catch (e) {}
})

function cancelLogout() { showLogoutConfirm.value = false }
async function confirmLogout() { 
  try { await axios.post('/api/logout', {}, { withCredentials: true }) 
  } catch (e) {} finally { 
    localStorage.clear(); 
    sessionStorage.clear(); 
    window.location.replace('/staff-landing') 
  } 
}

function onProfileUpdated(updatedProfile) { 
  userProfile.value = { ...userProfile.value, ...updatedProfile } 
}

defineExpose({ refreshAllData, onProfileUpdated })

async function fetchDefaultPassword() {
  const userRole = window.userRole || '';
  if (userRole !== 'OWNER' && userRole !== 'ADMIN' && userRole !== 'SUPER_ADMIN' && userRole !== 'SUPERADMIN') {
    fetchedDefaultPassword.value = 'Chikintayo_123';
    return;
  }
  
  if (fetchingDefaultPassword.value) return
  fetchingDefaultPassword.value = true
  try {
    const res = await axios.get('/api/admin/config/default-password', { withCredentials: true })
    if (res.data && res.data.success && res.data.default_password) {
      fetchedDefaultPassword.value = res.data.default_password
    } else {
      fetchedDefaultPassword.value = 'Chikintayo_123'
    }
  } catch (e) {
    fetchedDefaultPassword.value = 'Chikintayo_123'
  } finally {
    fetchingDefaultPassword.value = false
  }
}

function copyDefaultToClipboard() {
  const passwordToCopy = fetchedDefaultPassword.value || 'Chikintayo_123'
  if (!passwordToCopy) return
  try {
    navigator.clipboard?.writeText(passwordToCopy)
    alert('Password copied to clipboard: ' + passwordToCopy)
  } catch (e) {
    const textArea = document.createElement('textarea')
    textArea.value = passwordToCopy
    document.body.appendChild(textArea)
    textArea.select()
    document.execCommand('copy')
    document.body.removeChild(textArea)
    alert('Password copied to clipboard: ' + passwordToCopy)
  }
}

function openAddSupplier() {
  supplierForm.value = { 
    username: '', 
    email: '', 
    fullName: '', 
    businessName: '',
    phone: '', 
    password: '' 
  }
  formError.value = ''
  formSuccess.value = ''
  fetchedDefaultPassword.value = null
  showAddModal.value = true
  fetchDefaultPassword()
  console.log('openAddSupplier called')
}

function closeAddSupplier() {
  if (isSubmitting.value) return
  showAddModal.value = false
}

async function submitAddSupplier() {
  if (isSubmitting.value) return
  isSubmitting.value = true
  try {
    const payload = {
      username: supplierForm.value.username,
      email: supplierForm.value.email,
      fullName: supplierForm.value.fullName,
      businessName: supplierForm.value.businessName,
      phone: supplierForm.value.phone,
      password: supplierForm.value.password || undefined, // optional
    }
    const res = await axios.post('/api/manager/procurement/suppliers', payload, { withCredentials: true })
    // refresh and close
    await refreshAllData()
    showAddModal.value = false
    alert(res.data.message || 'Supplier created successfully')
  } catch (err) {
    const msg = err?.response?.data?.message || 'Failed to create supplier'
    formError.value = msg
    alert(msg)
  } finally {
    isSubmitting.value = false
  }
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    const pres = await axios.get('/api/manager/procurement/products', { withCredentials: true })
    if (pres && pres.data) {
      // supports both {data: [...] } and direct array
      if (Array.isArray(pres.data)) products.value = pres.data
      else if (Array.isArray(pres.data.data)) products.value = pres.data.data
      else products.value = []
    }
  } catch (e) {
    console.warn('Failed to load procurement products', e)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

async function loadRequestedProducts() {
  requestedProductsLoading.value = true
  try {
    const res = await axios.get('/api/procurement-requests/requested-products', { withCredentials: true })
    requestedProducts.value = res.data || []
  } catch (e) {
    console.warn('Failed to load requested products', e)
    requestedProducts.value = []
  } finally {
    requestedProductsLoading.value = false
  }
}

async function acknowledgeRequest(product) {
  if (!confirm(`Acknowledge logistics request for ${product.name}? (Sends to finance for budget)`)) return
  try {
    const requestId = product.procurement_request_id || product.id
    const res = await axios.post(`/api/procurement-requests/${requestId}/status`, { }, { withCredentials: true })
    alert('Request acknowledged and sent to finance')
    await loadRequestedProducts()
    await loadProducts()
  } catch (e) {
    alert('Failed to acknowledge request')
  }
}

async function requestSupplier(product) {
  if (!confirm(`Request suppliers to provide ${product.name}?`)) return
  try {
    const requestId = product.procurement_request_id || product.id
    const res = await axios.post(`/api/procurement-requests/${requestId}/broadcast`, {}, { withCredentials: true })
    alert(res.data?.message || 'Supplier request broadcasted')
    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.error('requestSupplier failed', e)
    alert(e.response?.data?.message || 'Failed to request supplier')
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/procurement/profile', { withCredentials: true })
    userProfile.value = res.data.user || {}
  } catch (e) {
    // ignore
  }
  await refreshAllData()
  await loadProducts()
  await loadRequestedProducts()
  await fetchBudgetRequests()
})

// Supplier selection modal state
const supplierModalVisible = ref(false)
const supplierList = ref([])
const supplierLoading = ref(false)
const selectedSupplierId = ref(null)
const pendingOrderProduct = ref(null)
const pendingOrderQty = ref(null)

function openSupplierModal(product, qty) {
  pendingOrderProduct.value = product
  pendingOrderQty.value = qty ?? null
  selectedSupplierId.value = null
  supplierModalVisible.value = true
  supplierLoading.value = true
  axios.get('/api/manager/logistics/suppliers', { withCredentials: true })
    .then(res => {
      supplierList.value = (res.data && res.data.suppliers) || []
    }).catch(() => {
      supplierList.value = []
    }).finally(() => { supplierLoading.value = false })
}

function closeSupplierModal() {
  supplierModalVisible.value = false
  pendingOrderProduct.value = null
  pendingOrderQty.value = null
  selectedSupplierId.value = null
}

async function confirmSupplierSelection() {
  if (!pendingOrderProduct.value) return
  if (!selectedSupplierId.value) { alert('Please select a supplier'); return }
  isPlacingOrder.value = true
  try {
    const payload = { supplier_id: selectedSupplierId.value }
    if (pendingOrderQty.value) payload.quantity = pendingOrderQty.value
    const res = await axios.post(`/api/procurement.products/${pendingOrderProduct.value.id}/place-order`, payload, { withCredentials: true })
    const supplierOrder = res.data.supplier_order
    const procReq = res.data.procurement_request
    alert(res.data.message || 'Order placed successfully')
    // update local lists
    await loadProducts()
    await loadRequestedProducts()
    await refreshAllData()
  } catch (e) {
    console.error('confirmSupplierSelection failed', e)
    alert(e.response?.data?.error || e.response?.data?.message || 'Failed to place order')
  } finally {
    isPlacingOrder.value = false
    closeSupplierModal()
  }
}

async function fetchBudgetRequests() {
  budgetLoading.value = true
  try {
    const res = await axios.get('/api/procurement/budget/my-requests', { withCredentials: true })
    if (res.data && res.data.ok) {
      budgetRequests.value = res.data.requests || []
    } else {
      budgetRequests.value = []
    }
  } catch (e) {
    console.error('Failed to load budget requests', e)
    budgetRequests.value = []
  } finally {
    budgetLoading.value = false
  }
}

async function submitBudgetRequest() {
  if (budgetSubmitting.value) return
  budgetError.value = ''
  if (!budgetForm.value.purpose || !budgetForm.value.requested_amount) {
    budgetError.value = 'Please fill purpose and amount.'
    return
  }
  budgetSubmitting.value = true
  try {
    const payload = {
      purpose: budgetForm.value.purpose,
      requested_amount: budgetForm.value.requested_amount
    }
    const res = await axios.post('/api/procurement/budget/create', payload, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Budget request created')
      showBudgetForm.value = false
      budgetForm.value.purpose = ''
      budgetForm.value.requested_amount = ''
      await fetchBudgetRequests()
    } else {
      budgetError.value = res.data?.message || 'Failed to create request'
    }
  } catch (e) {
    console.error('Create budget request failed', e)
    budgetError.value = e.response?.data?.message || 'Failed to create request'
  } finally {
    budgetSubmitting.value = false
  }
}

// Helper to format price nicely for display
function formatPrice(val) {
  if (val === null || val === undefined) return '₱0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '₱0.00'
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function placeOrder(product) {
  if (!product || !product.id || isPlacingOrder.value) return
  
  isPlacingOrder.value = true
  
  try {
    // Prompt for quantity (optional)
    const qtyInput = prompt('Enter quantity to order from supplier (leave blank to accept request quantity):', '')
    let qty = null
    if (qtyInput !== null && qtyInput !== '') {
      qty = parseInt(qtyInput, 10)
      if (Number.isNaN(qty) || qty < 1) {
        alert('Invalid quantity (must be 1+)')
        return
      }
    }

    const payload = {}
    if (qty !== null) payload.quantity = qty
    // If product has no assigned supplier, open modal to select one
    if (!product.supplier_id) {
      openSupplierModal(product, qty)
      return
    }

    // Use procurement endpoint which creates the SupplierOrder record
    const res = await axios.post(`/api/procurement.products/${product.id}/place-order`, payload, { withCredentials: true })
    
    // Handle response and update local UI immediately
    const supplierOrder = res.data.supplier_order
    const procReq = res.data.procurement_request

    if (res.data.message?.includes('already placed')) {
      alert(res.data.message)
    } else {
      alert(res.data.message || 'Order placed successfully')
    }

    // Optimistically update local product entries so the Place Order button hides
    try {
      // Update products list
      const idx = products.value.findIndex(p => p.id === product.id)
      if (idx > -1) {
        if (supplierOrder) products.value[idx].existingOrder = supplierOrder
        if (procReq && procReq.status) {
          products.value[idx].procurement_status = procReq.status
          products.value[idx].status = procReq.status
        }
      }

      // Update requestedProducts list (if present)
      const ridx = requestedProducts.value.findIndex(p => p.id === product.id)
      if (ridx > -1) {
        if (supplierOrder) requestedProducts.value[ridx].existingOrder = supplierOrder
        if (procReq && procReq.status) {
          requestedProducts.value[ridx].procurement_status = procReq.status
          requestedProducts.value[ridx].status = procReq.status
        }
      }
    } catch (e) {
      // ignore local update failures
    }

    // Refresh lists to ensure server canonical state (non-blocking)
    await loadProducts()
    await loadRequestedProducts()
    await refreshAllData()
  } catch (e) {
    console.error('Place order failed', e)
    alert(e.response?.data?.error || e.response?.data?.message || 'Failed to place order')
  } finally {
    isPlacingOrder.value = false
  }
}

async function markDeliveryComplete(product) {
  if (!product || !product.procurement_request_id) return
  if (!confirm(`Mark delivery complete for ${product.name}? This will set the request as completed.`)) return
  isCompletingDelivery.value = true
  try {
    const res = await axios.post(`/api/procurement-requests/${product.procurement_request_id}/complete`, {}, { withCredentials: true })
    alert(res.data?.message || 'Procurement request marked completed')
    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.error('Mark delivery complete failed', e)
    alert(e.response?.data?.error || e.response?.data?.message || 'Failed to mark delivery complete')
  } finally {
    isCompletingDelivery.value = false
  }
}

</script>

<style scoped>
/* Reuse styles from HR panel; keep minimal overrides */
.hr-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.hr-stat-card { background: white; border-radius: 8px; padding: 1rem; display:flex; gap:0.75rem; align-items:center; color: #1b1b1f; }
.hr-stat-value { font-weight:700; font-size:1.25rem; }

/* Modal overrides for better contrast and layout inside this panel */
.modal {
  background: #ffffff;
  color: #1b1b1f;
  border-radius: 12px;
  width: 92%;
  max-width: 720px;
  margin: 0 12px;
  box-shadow: 0 18px 40px rgba(0,0,0,0.35);
}

.modal-card { overflow: hidden; }

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #1b1b1f;
}

.modal-body { 
  padding: 1rem 1.25rem; 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 0.75rem; 
}
.modal-body .form-group { display: flex; flex-direction: column; gap: 6px; }
.modal-body .form-group.full-span { grid-column: 1 / -1; }
.modal-body label { color: #333; font-size: 0.85rem; }
.modal-body input { padding: 8px 10px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #111; }

.error-msg { color: #a33; grid-column: 1 / -1; padding-top: 6px; }
.success-msg { color: #167a3e; grid-column: 1 / -1; padding-top: 6px; }

.modal-footer { padding: 10px 14px; display:flex; justify-content:flex-end; gap:0.5rem; background: #fafafa; }
.modal-footer .btn-outline { background: transparent; border: 1px solid #ccc; color: #333; }
.modal-footer .btn-primary { background: #4b1ddf; color: #fff; }

/* Password Display Styles */
.password-display-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.password-display-card {
  background: linear-gradient(135deg, #fef3e2 0%, #fde8d4 100%);
  border: 2px solid #ff9a56;
  border-radius: 10px;
  padding: 1.25rem;
}

.password-display-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #92400e;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.password-display-value {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.password-text {
  font-family: 'Courier New', monospace;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  background: #fff;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  letter-spacing: 1px;
}

.btn-copy {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  white-space: nowrap;
  background: #4b1ddf;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.password-display-card .form-hint {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: #92400e;
}

.password-loading {
  display: flex;
  align-items: center;
  padding: 0.5rem;
}

/* Ensure backdrop has high z-index inside component scope */
.modal-backdrop { z-index: 2000; }

/* Product grid styles for supplier products */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
  margin-top: 0.75rem;
}

.product-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 0.9rem;
  box-shadow: 0 6px 18px rgba(15,23,42,0.06);
  border: 1px solid #eef2f6;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.product-name { font-weight: 700; color: #111827; }
.product-meta { display:flex; justify-content:space-between; align-items:center; gap:0.5rem }
.product-price { color: #0b6e3a; font-weight:700 }
.supplier-badge { background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem }
</style>
