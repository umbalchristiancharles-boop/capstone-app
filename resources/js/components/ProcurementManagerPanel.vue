<template>
  <OwnerPanelLayout ref="ownerLayout"
    :userProfile="userProfile"
    :panelTitle="'Manager Procurement Panel'"
    :panelDescription="'Manage procurement staff, view procurement reports, and monitor procurement status.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    :showProfileColumn="false"
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
      <div class="panel-actions mt-1">
        <button class="btn-primary" @click="openAddSupplier">Add Supplier</button>
      </div>
      <section class="supplier-products mt-1">
        <h2>Supplier Products (this branch)</h2>
        <div v-if="loadingProducts">Loading products...</div>
        <div v-else-if="!products.length">No products available in your branch.</div>
        <div v-else>
          <!-- Pending Supplier Products UI removed per request -->

          <div>
            <h3 class="section-subtitle">Published Products ({{ publishedProducts.length }})</h3>
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
      <section class="requests-history mt-1">
        <h2>Requests History</h2>
        <p class="section-description">All procurement requests for this branch (most recent first).</p>

        <div v-if="procurementHistoryLoading">Loading history...</div>
        <div v-else-if="!procurementHistory.length">No procurement requests found.</div>
        <div v-else>
          <div class="requests-container">
            <div class="requests-scroll">
              <table class="data-table">
                <thead>
                  <tr><th>Date</th><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Updated</th></tr>
                </thead>
                <tbody>
                  <tr v-for="r in procurementHistory" :key="'ph-'+r.id">
                    <td>{{ formatDate(r.created_at) }}</td>
                    <td><div class="product-name">{{ r.product?.name || r.purpose || '(no product)' }}</div></td>
                    <td>{{ r.quantity }}</td>
                    <td class="amount">{{ formatPrice(r.total_amount || r.price || 0) }}</td>
                    <td>
                      <span :class="['status-badge', getProcStatusClass(r.status)]">
                        {{ formatProcStatus(r.status, r.budget_approved) }}
                      </span>
                    </td>
                    <td>{{ formatDate(r.updated_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
      <section class="budget-requests mt-1">
        <h2>Budget Requests</h2>
        <p class="section-description">Create and view your branch budget requests.</p>

        <div class="mb-1">
          <button class="btn-primary" v-if="!showBudgetForm" @click="showBudgetForm = true">+ New Budget Request</button>
        </div>

        <div v-if="showBudgetForm" class="budget-form mt-sm">
          <div class="form-grid">
            <div class="form-label">Purpose</div>
            <div class="form-field">
              <textarea v-model="budgetForm.purpose" rows="3" placeholder="Describe the purpose of the budget" @input="clearBudgetFieldError"></textarea>
            </div>

            <div class="form-label">Requested Amount</div>
            <div class="form-field inline-controls">
              <div class="amount-input">
                <span class="currency">₱</span>
                <input v-model="budgetForm.requested_amount" type="number" step="0.01" placeholder="0.00" @input="validateAmountField" />
              </div>
              <div class="action-row">
                <button class="btn-budget" @click="submitBudgetRequest" :disabled="budgetSubmitting">{{ budgetSubmitting ? 'Submitting...' : 'Submit Request' }}</button>
                <button class="btn-outline btn-cancel-inline" @click="cancelBudgetForm" :disabled="budgetSubmitting">Cancel</button>
              </div>
              <div class="field-note">
                <div v-if="budgetFieldError" class="error-msg">{{ budgetFieldError }}</div>
              </div>
            </div>
          </div>

          <div v-if="budgetError" class="error-msg mt-sm">{{ budgetError }}</div>
        </div>

        <div class="mt-1">
          <h3>My Budget Requests</h3>
          <div v-if="budgetLoading">Loading...</div>
          <div v-else-if="!budgetRequests.length">No budget requests.</div>
          <table v-else class="data-table">
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
      <section class="requested-products mt-1">
        <h2>Requests From Logistics</h2>
        <p class="section-description">Inventory requests sent by Logistics Managers in your branch.</p>

        <div v-if="requestedProductsLoading">Loading requests...</div>
        <div v-else-if="!requestedProducts.length">No requests from logistics.</div>
        <div v-else>
          <div class="inline-row gap-sm align-center mb-1">
            <h3 class="no-margin">Pending Logistics Requests ({{ requestedProducts.length }})</h3>
            <button class="btn-refresh" @click="loadRequestedProducts">🔄 Refresh</button>
          </div>
          <div class="product-grid">
            <div v-for="p in requestedProducts" :key="'req-'+p.id" class="product-card">
              <div class="product-name">{{ p.name }}</div>
              <div class="product-meta">
                <div class="product-price">{{ formatPrice(p.price) }}</div>
                <div>
                  <template v-if="(p.procurement_status === 'pending' || p.status === 'pending') && !p.needs_supplier && (p.acknowledge_allowed === undefined ? true : p.acknowledge_allowed)">
                    <button class="btn-small btn-primary" @click="acknowledgeRequest(p)">Acknowledge</button>
                  </template>
                  <template v-else-if="(p.procurement_status === 'pending' || p.status === 'pending') && p.needs_supplier">
                    <button class="btn-small btn-warning" @click="requestSupplier(p)">Request Supplier for Product</button>
                  </template>
                  <template v-else-if="p.procurement_status === 'budget_pending' || p.status === 'budget_pending'">
                    <button class="btn-small btn-outline" disabled>Budget to be received</button>
                  </template>
                  <template v-else-if="p.procurement_status === 'pending_order_to_supplier' || p.status === 'pending_order_to_supplier' || p.procurement_status === 'ongoing_delivery' || p.status === 'ongoing_delivery' || p.procurement_status === 'receipt_confirmed' || p.receipt_confirmed">
                    <div v-if="p.existingOrder" class="inline-row gap-sm align-center">
                      <div class="status-badge status-warning">
                        Transaction Pending (ID: {{ p.existingOrder.id }})
                      </div>
                      <div v-if="(p.existingOrder && (p.existingOrder.status === 'on_delivery' || p.existingOrder.status === 'ongoing_delivery' || p.existingOrder.status === 'fulfilled')) || p.procurement_status === 'delivery_pending' || p.procurement_status === 'ongoing_delivery'">
                          <template v-if="p.receipt_confirmed || p.procurement_status === 'ongoing_delivery' || p.procurement_status === 'receipt_confirmed' || (p.existingOrder && p.existingOrder.receipt_confirmed)">
                            <button class="btn-small btn-primary" @click="markDeliveryComplete(p)" :disabled="isCompletingDelivery">{{ isCompletingDelivery ? 'Submitting...' : 'Complete Order' }}</button>
                          </template>
                          <template v-else>
                            <button class="btn-small btn-primary" @click="openReceiptModal(p)" :disabled="isCompletingDelivery">{{ isCompletingDelivery ? 'Submitting...' : 'Delivery complete' }}</button>
                          </template>
                        </div>
                    </div>
                      <div v-else-if="p.procurement_status === 'ongoing_delivery' || p.status === 'ongoing_delivery' || p.procurement_status === 'receipt_confirmed' || p.receipt_confirmed">
                      <button class="btn-small btn-primary"
                        @click="p.receipt_confirmed || p.procurement_status === 'receipt_confirmed' ? markDeliveryComplete(p) : openReceiptModal(p)"
                        :disabled="isCompletingDelivery">
                        {{ isCompletingDelivery ? 'Submitting...' : ((p.receipt_confirmed || p.procurement_status === 'receipt_confirmed') ? 'Complete Order' : 'Delivery complete') }}
                      </button>
                    </div>
                      <div v-else>
                      <button class="btn-small btn-primary"
                        @click="placeOrder(p)"
                        :disabled="isPlacingOrder || p.waiting_for_supplier">
                        {{ isPlacingOrder ? 'Placing...' : 'Place Order' }}
                      </button>
                      <div v-if="p.waiting_for_supplier" class="note-warning">Waiting for supplier confirmation</div>
                    </div>
                  </template>
                  <template v-else>
                    <button class="btn-small btn-outline" disabled>Unavailable</button>
                  </template>
                </div>
              </div>
              <div class="supplier-badge mt-sm">{{ p.supplier_name || (p.supplier?.full_name || 'Unknown Supplier') }}</div>
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
                      <span class="muted small-text">Loading default password...</span>
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

    <template #headerActions>
      <div class="header-profile-wrapper" @click.stop>
        <button class="header-profile-btn" @click="toggleProfileDropdown">
          <div class="header-avatar">
            <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
            <div v-else class="header-avatar-initials">{{ (userProfile.fullName || userProfile.full_name || 'U').charAt(0) }}</div>
          </div>
          <div class="header-name">{{ (userProfile.fullName || userProfile.full_name || '').toUpperCase() }}</div>
        </button>
        <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
          <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
          <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
        </div>
      </div>
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

  <!-- Receipt upload modal (supplier must paste/upload physical receipt) -->
  <transition name="fade">
    <div v-if="showReceiptModal" class="modal-backdrop" @click.self="closeReceiptModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>Upload Physical Receipt</h3>
          </div>
          <div class="modal-body">
            <div class="form-group full-span">
              <label>Please upload a clear photo of the physical receipt (required)</label>
              <input type="file" accept="image/*" @change="onReceiptSelected" />
            </div>
            <div class="form-group full-span" v-if="receiptPreview">
              <label>Preview</label>
              <img :src="receiptPreview" alt="receipt preview" class="receipt-preview" />
            </div>
            <div v-if="receiptError" class="error-msg">{{ receiptError }}</div>
            <div class="form-note">After you submit the receipt, Finance must confirm it before status becomes On Delivery.</div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeReceiptModal" :disabled="receiptUploading">Cancel</button>
            <button class="btn-primary" @click="submitReceipt" :disabled="!receiptFile || receiptUploading">{{ receiptUploading ? 'Uploading...' : 'Submit Receipt' }}</button>
          </div>
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
              <div v-else class="supplier-list-scroll">
                <div v-for="s in supplierList" :key="s.id" class="supplier-row">
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
import { ref, onMounted, computed, onUnmounted } from 'vue'
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
const budgetFieldError = ref('')

function clearBudgetFieldError() { budgetFieldError.value = '' }

function validateAmountField() {
  const amt = budgetForm.value.requested_amount
  if (amt === '' || amt === null || amt === undefined || Number(amt) <= 0) {
    budgetFieldError.value = 'Please enter an amount greater than 0.'
  } else {
    budgetFieldError.value = ''
  }
}

function cancelBudgetForm() {
  if (budgetSubmitting.value) return
  showBudgetForm.value = false
  budgetForm.value.purpose = ''
  budgetForm.value.requested_amount = ''
  budgetError.value = ''
  budgetFieldError.value = ''
}

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

// Header profile dropdown (procurement-specific)
const profileDropdownVisible = ref(false)
const ownerLayout = ref(null)

function toggleProfileDropdown() {
  profileDropdownVisible.value = !profileDropdownVisible.value
}

function closeProfileDropdown() { profileDropdownVisible.value = false }

// Open the OwnerPanelLayout info modal by clicking the left 'Info' button (UI-only trigger)
function openInfoFromHeader() {
  closeProfileDropdown()
  // Try calling OwnerPanelLayout exposed method first (works when left column hidden)
  try {
    if (ownerLayout.value && typeof ownerLayout.value.openInfoModal === 'function') {
      ownerLayout.value.openInfoModal()
      return
    }
  } catch (e) {}
  // Fallback: dispatch a global event the layout listens for (works even if ref not available)
  try { window.dispatchEvent(new Event('open-owner-info')); return } catch (e) {}
  // Last resort: find the left-side Info button and click it
  const infoBtn = document.querySelector('.admin-info-btn')
  if (infoBtn) infoBtn.click()
}

// Open info modal and try to click 'Edit Information' inside it to enter edit mode
function openEditProfileFromHeader() {
  closeProfileDropdown()
  // Try opening the global avatar picker exposed by OwnerPanelLayout
  try {
    if (ownerLayout.value && typeof ownerLayout.value.openAvatarPicker === 'function') {
      ownerLayout.value.openAvatarPicker()
      return
    }
  } catch (e) {}

  // Fallback: dispatch the existing edit-profile event so older layouts still work
  try { window.dispatchEvent(new Event('open-owner-edit-profile')); return } catch (e) {}

  // Last resort: attempt to find any visible avatar file input and click it
  const fileInput = document.querySelector('#avatar-input') || document.querySelector('#avatar-input-modal') || document.querySelector('#global-avatar-input')
  if (fileInput) fileInput.click()
}

function triggerLogoutFromHeader() {
  closeProfileDropdown()
  showLogoutConfirm.value = true
}

// Close dropdown when clicking outside
window.addEventListener('click', (e) => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})

// Procurement requests history
const procurementHistory = ref([])
const procurementHistoryLoading = ref(false)

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
  await loadProcurementHistory()
})

async function loadProcurementHistory() {
  procurementHistoryLoading.value = true
  try {
    const res = await axios.get('/api/procurement-requests', { withCredentials: true })
    const data = res.data?.data ?? res.data ?? (res.data ? [res.data] : [])
    procurementHistory.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.warn('Failed to load procurement history', e)
    procurementHistory.value = []
  } finally {
    procurementHistoryLoading.value = false
  }
}

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
    const res = await axios.post(`/api/manager/procurement/products/${pendingOrderProduct.value.id}/place-order`, payload, { withCredentials: true })
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

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleString() } catch (e) { return d }
}

function getProcStatusClass(status) {
  switch ((status || '').toLowerCase()) {
    case 'completed': return 'status-approved'
    case 'approved': return 'status-approved'
    case 'pending': return 'status-pending'
    default: return 'status-pending'
  }
}

function formatProcStatus(status, budgetApproved) {
  if (budgetApproved) return 'BUDGET APPROVED'
  return (status || '').toUpperCase()
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
    // If product has an assigned supplier, include it so Manager endpoint
    // creates a SupplierOrder (transaction pending) instead of auto-publishing.
    if (!product.supplier_id) {
      openSupplierModal(product, qty)
      return
    }
    if (product.supplier_id) payload.supplier_id = product.supplier_id

    // Use manager procurement endpoint which creates the SupplierOrder record
    const res = await axios.post(`/api/manager/procurement/products/${product.id}/place-order`, payload, { withCredentials: true })

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

// Receipt / completion workflow:
// Supplier must upload a photo of the physical receipt. The receipt is posted
// to the same complete endpoint as multipart/form-data. Finance must confirm
// the receipt on the backend before status becomes 'on_delivery'.
const showReceiptModal = ref(false)
const receiptFile = ref(null)
const receiptPreview = ref(null)
const receiptError = ref('')
const receiptUploading = ref(false)
const receiptPreviewProduct = ref(null)

function openReceiptModal(product) {
  if (!product || !product.procurement_request_id) return
  receiptPreviewProduct.value = product
  receiptFile.value = null
  receiptPreview.value = null
  receiptError.value = ''
  showReceiptModal.value = true
}

function closeReceiptModal() {
  if (receiptUploading.value) return
  showReceiptModal.value = false
  receiptFile.value = null
  receiptPreview.value = null
  receiptError.value = ''
  receiptPreviewProduct.value = null
}

function onReceiptSelected(e) {
  const f = (e.target && e.target.files && e.target.files[0]) || null
  if (!f) { receiptFile.value = null; receiptPreview.value = null; return }
  // Basic client-side check
  if (!f.type.startsWith('image/')) { receiptError.value = 'Please select an image file.'; receiptFile.value = null; return }
  receiptFile.value = f
  receiptError.value = ''
  const reader = new FileReader()
  reader.onload = (ev) => { receiptPreview.value = ev.target.result }
  reader.readAsDataURL(f)
}

async function submitReceipt() {
  if (!receiptPreviewProduct.value || !receiptPreviewProduct.value.procurement_request_id) return
  if (!receiptFile.value) { receiptError.value = 'Receipt image is required.'; return }
  receiptUploading.value = true
  isCompletingDelivery.value = true
  receiptError.value = ''
  try {
    const id = receiptPreviewProduct.value.procurement_request_id
    const fd = new FormData()
    fd.append('receipt', receiptFile.value)
    // optional: include note identifying supplier user
    const res = await axios.post(`/api/procurement-requests/${id}/complete`, fd, { headers: { 'Content-Type': 'multipart/form-data' }, withCredentials: true })
    alert(res.data?.message || 'Receipt submitted. Awaiting finance confirmation.')

    // Update local item to indicate receipt submitted and awaiting finance
    try {
      const prod = receiptPreviewProduct.value
      prod.procurement_status = res.data?.procurement_status || 'receipt_submitted'
      if (prod.existingOrder) prod.existingOrder.status = res.data?.order_status || prod.existingOrder.status
    } catch (e) { /* ignore local update failures */ }

    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
    closeReceiptModal()
  } catch (e) {
    console.error('Receipt upload failed', e)
    receiptError.value = e.response?.data?.message || 'Failed to upload receipt'
    alert(receiptError.value)
  } finally {
    receiptUploading.value = false
    isCompletingDelivery.value = false
  }
}

async function markDeliveryComplete(product) {
  if (!product || !product.procurement_request_id) return
  if (!confirm(`Mark delivery complete for ${product.name || 'this item'}? This will set the request as completed.`)) return
  isCompletingDelivery.value = true
  try {
    const id = product.procurement_request_id
    const payload = {}
    const res = await axios.post(`/api/procurement-requests/${id}/complete`, payload, { withCredentials: true })
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

// Listen for global receiptConfirmed events (dispatched by Finance panel) and refresh lists
function onReceiptConfirmed(e) {
  try {
    loadRequestedProducts()
    loadProducts()
    refreshAllData()
  } catch (err) { console.warn('onReceiptConfirmed handler failed', err) }
}

window.addEventListener('receiptConfirmed', onReceiptConfirmed)

onUnmounted(() => {
  try { window.removeEventListener('receiptConfirmed', onReceiptConfirmed) } catch (e) { /* ignore */ }
})

</script>

<style scoped>
/* Use StaffIndex theme tokens for this panel to match color, font, and UI */
:deep(.admin-page) {
  background: var(--bg-main) !important;
  color: var(--text-dark) !important;
  font-family: 'Inter', 'Poppins', sans-serif !important;
}

:deep(.admin-layout) {
  background: transparent !important;
  border-radius: 12px !important;
  padding: 20px !important;
  box-shadow: 0 8px 24px rgba(16,24,40,0.06) !important;
}

/* When the left profile column is hidden for procurement, make the main area wider
   and keep the announcements/side panel at a comfortable width. */
:deep(.admin-layout.no-profile-column) {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 1rem;
}

:deep(.admin-layout.no-profile-column) .admin-main { width: 100%; }
:deep(.admin-layout.no-profile-column) .admin-side { width: 360px; }

/* Make internal cards follow the same surface / dirty-white look */
.hr-stat-card,
.product-card,
.modal,
.password-display-card {
  background: var(--surface-card) !important;
  color: var(--text-dark) !important;
  border: 1px solid var(--border-stroke) !important;
}

.hr-stat-card { box-shadow: 0 8px 24px rgba(16,24,40,0.06); border-radius: 12px; }
.product-card { box-shadow: 0 10px 20px rgba(0,0,0,0.08); border-radius: 10px; }
.modal { box-shadow: 0 18px 40px rgba(0,0,0,0.12); }

/* Buttons use same rounded 'pill' visual for primary actions */
.modal-footer .btn-primary,
.btn-primary {
  background: var(--dirty-white);
  color: var(--text-dark);
  border: none;
  border-radius: 999px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.08);
}

/* Ensure inputs and labels use shared text color */
.modal-body label,
.modal-body input,
.form-group label,
.product-meta,
.password-display-label {
  color: var(--text-dark);
  font-family: 'Inter', 'Poppins', sans-serif;
}

/* Slight layout tweak so the panel feels less tight inside admin wrapper */
:deep(.admin-page) > .admin-layout {
  gap: 1rem;
}

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
  z-index: 100101; /* ensure modal floats above sticky table headers */
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

/* Requests history table tweaks */
.requests-history .data-table th,
.requests-history .data-table td { padding: 10px 12px; }
.requests-history .data-table td.amount { text-align: right; white-space: nowrap; font-weight:600 }
.requests-history .product-name { white-space: normal; word-break: break-word; max-width: 420px }
.modal-footer .btn-primary { background: #4b1ddf; color: #fff; }

/* Requests History: outer card allows overflow; inner element handles scrolling */
.requests-container {
  overflow: visible; /* allow popovers/expanded info to escape rounded corners */
  background: var(--surface-card);
  padding: 0; /* inner scrollable area will include padding */
  border-radius: 10px;
  border: 1px solid var(--border-stroke);
}

.requests-scroll {
  max-height: 320px;
  overflow-y: auto;
  padding: 0 12px 12px 12px; /* keep previous padding inside scroll area */
  /* extend to cover parent's horizontal padding so no white sliver shows */
  width: calc(100% + 24px);
  margin-left: -12px;
  margin-right: -12px;
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.04);
  background: transparent;
}
.requests-scroll .data-table { margin: 0; }
.requests-scroll .data-table thead th {
  position: sticky;
  top: 0;
  background: var(--dirty-white);
  z-index: 2;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

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
.modal-backdrop { z-index: 100100; }

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

/* Utility spacing classes */
.mt-1 { margin-top: 1rem; }
.mt-sm { margin-top: 8px; }
.mb-1 { margin-bottom: 1rem; }
.no-margin { margin: 0; }

/* Layout helpers */
.inline-row { display:flex; }
.gap-sm { gap: 0.5rem; }
.align-center { align-items: center; }

.section-subtitle { margin: 0 0 8px 0; font-size: 1rem; font-weight: 700; }

/* Budget form specific styles */
.budget-form { display: block; background: transparent; padding: 8px 0; }
.budget-form .form-row { display: flex; gap: 12px; align-items: end; flex-wrap:wrap }
.budget-form .form-group.amount { width: 260px; display:flex; flex-direction:column }
.budget-form .form-group.amount label { font-size:0.9rem }
.budget-form .input-amount { padding:8px 10px; border-radius:8px; border:1px solid #ddd; width:100%; max-width:260px }
.budget-form .form-actions { display:flex; align-items:center }
.btn-budget { background: linear-gradient(180deg,#ff781a,#ff5a00); color: #fff; border: none; padding: 8px 14px; border-radius: 999px; box-shadow: 0 8px 18px rgba(255,90,0,0.18); cursor:pointer }
.btn-budget:disabled { opacity:0.6; cursor:default }
.btn-outline { background: #f0f0f0; color: #333; border-radius: 999px; padding: 6px 10px }

/* Improved budget form grid and controls */
.budget-form .form-grid { display: grid; grid-template-columns: 140px 1fr; gap: 10px 18px; align-items: start; max-width: 760px }
.budget-form .form-label { color: #4b1d1d; font-weight:700; padding-top:6px }
.budget-form textarea { width:100%; min-height:68px; max-width:100%; padding:8px 10px; border-radius:8px; border:1px solid #e6e6e6; background:#fff }
.budget-form .inline-controls { display:flex; gap:12px; align-items:center }
.budget-form .amount-input { display:flex; align-items:center; gap:6px; background:#fff; border:1px solid #eee; padding:6px 8px; border-radius:8px }
.budget-form .amount-input .currency { color:#0b6e3a; font-weight:700 }
.budget-form .amount-input input { border: none; outline: none; width:120px; font-weight:700 }
.budget-form .form-field { display:flex; flex-direction:column }
.budget-form .btn-budget { padding: 8px 12px; height:40px }
.budget-form .btn-outline { margin-bottom: 8px }


/* Button variants */
.btn-small { padding: 6px 10px; border-radius: 8px; font-size: 0.95rem; border: 1px solid var(--border-stroke); background: var(--surface-card); color: var(--text-dark); cursor: pointer; }
.btn-small:focus { outline: 3px solid rgba(3,37,65,0.08); }
.btn-refresh {
  padding: 6px 12px;
  font-size: 0.85rem;
  border-radius: 8px;
  border: none;
  background: transparent;
  box-shadow: none;
}
.btn-refresh:focus { outline: none; box-shadow: none; }
.btn-primary { background: var(--dirty-white); color: var(--text-dark); border: none; border-radius: 999px; padding: 8px 14px; box-shadow: 0 8px 18px rgba(0,0,0,0.06); cursor: pointer; font-weight:600; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline { background: transparent; border: 1px solid var(--border-stroke); color: var(--text-dark); padding: 8px 12px; border-radius: 8px; cursor: pointer; }
.btn-outline[disabled], .btn-outline.disabled { opacity: 0.6; cursor: not-allowed; }
.btn-warning { background: var(--orange); color: var(--dirty-white); border: none; box-shadow: 0 8px 18px rgba(255,107,28,0.12); }

/* Status badge variants */
.status-badge.status-warning { background: #fbbf24; color: #92400e; padding:6px 10px; border-radius:8px; font-size:0.9rem; font-weight:600; }

/* Table polish */
.data-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
.data-table thead th { background: var(--dirty-white); position: sticky; top: 0; z-index: 2; }
.data-table tbody tr:nth-child(odd) { background: rgba(3,37,65,0.02); }
.data-table th, .data-table td { padding: 8px 10px; }

/* Stat cards consistent */
.hr-stat-card { min-height: 88px; display:flex; align-items:center; gap:0.75rem; padding: 1rem; }

/* Accessibility focus */
button:focus, a:focus, input:focus, select:focus { outline: 3px solid rgba(3,37,65,0.08); outline-offset: 2px; }

/* Small utility and component tweaks */
.muted { color: #6b7280; }
.small-text { font-size: 0.9rem; }
.receipt-preview { max-width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; }
.form-note { font-size: 0.9rem; color: #374151; grid-column: 1 / -1; margin-top: 6px; }
.supplier-list-scroll { max-height: 260px; overflow: auto; }
.supplier-row { display:flex; align-items:center; gap:0.5rem; padding:6px 0; }
.note-warning { margin-top:6px; color:#92400e; font-weight:600; font-size:0.9rem }

/* Header profile dropdown styles (procurement panel only) */
.header-profile-wrapper { position:relative; display:flex; align-items:center }
.header-profile-btn { display:flex; gap:8px; align-items:center; background:transparent; border:none; cursor:pointer; padding:6px 8px; border-radius:8px }
.header-avatar { width:36px; height:36px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f3f4f6 }
.header-avatar-img { width:100%; height:100%; background-size:cover; background-position:center }
.header-avatar-initials { font-weight:700; color:#374151 }
.header-name { font-weight:700; color:#333; font-size:0.86rem }
.header-profile-dropdown { position:absolute; right:0; top:46px; background:#fff; border-radius:8px; box-shadow:0 8px 24px rgba(16,24,40,0.12); padding:6px; min-width:160px; z-index:100200 }
.dropdown-item { display:block; width:100%; text-align:left; padding:8px 12px; background:transparent; border:none; color:#374151; cursor:pointer }
.dropdown-item:hover { background:#f7f7f8 }

/* Hide the small account ID in the left profile column for Procurement panel
   — the account ID will be visible inside the Info modal only. */
:deep(.admin-profile-column .admin-id-block) { display: none !important }

/* Info modal: procurement-specific visual refresh (colors, font, spacing) */
:deep(.info-modal) {
  max-width: 520px;
  background: #ffffff;
  color: #1f2937;
  border-radius: 12px;
  padding: 18px;
  box-shadow: 0 18px 40px rgba(3,37,65,0.08);
  font-family: 'Inter', 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}
:deep(.info-modal h3) { margin:0; font-size:1.05rem; color:#111827; font-weight:700 }
:deep(.info-modal .info-sub) { color:#6b7280; margin-bottom:12px }
:deep(.info-modal .info-grid) { display:flex; flex-direction:column; gap:10px }
:deep(.info-modal .info-row) { display:flex; justify-content:space-between; align-items:center; gap:8px; padding:8px 0; border-bottom: 1px solid #f3f4f6 }
:deep(.info-modal .info-row:last-child) { border-bottom: none }
:deep(.info-modal .info-label) { color:#6b7280; font-size:0.9rem; font-weight:600 }
:deep(.info-modal .info-value) { color:#111827; font-size:0.95rem; font-weight:700 }
</style>
