<template>
  <OwnerPanelLayout
    ref="ownerLayout"
    :userProfile="userProfile"
    :panelTitle="'Logistics Manager Panel'"
    :panelDescription="'Monitor inventory, procurement requests, and manage budgets for your branch.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    :showProfileColumn="false"
    :ownerTwoColumnLayout="true"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
        <div class="hr-stats-grid">
          <div class="hr-stat-card hr-stat-card--total">
            <div class="hr-stat-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Total Products</span>
              <span class="hr-stat-value">{{ dashboardTotals.totalProducts }}</span>
            </div>
          </div>
          <div class="hr-stat-card hr-stat-card--active">
            <div class="hr-stat-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Low Stock</span>
              <span class="hr-stat-value">{{ dashboardTotals.lowStock }}</span>
            </div>
          </div>
          <div class="hr-stat-card hr-stat-card--leave" :class="{ 'stat-alert': managerPendingCount > 0 }">
            <div class="hr-stat-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Pending Requests</span>
              <span class="hr-stat-value">{{ dashboardTotals.pendingRequests }}</span>
            </div>
            <span v-if="managerPendingCount > 0" class="panel-badge">{{ managerPendingCount }}</span>
          </div>
        </div>
      <!-- Inventory Section (moved to staff) -->
      <div class="panel-section" v-if="false">
        <h2 class="section-title">Inventory Monitor</h2>
        <p class="section-description">Current stock levels for your branch (Read-only)</p>

        <!-- Branch selector: shown when user can select branch (main branch logistics) -->
        <div v-if="userProfile.can_select_branch" class="branch-filter-row">
          <label for="branchSelect">Branch</label>
          <select id="branchSelect" v-model="selectedBranch">
            <option value="" disabled>Select branch...</option>
            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
          <div v-if="branchesLoading" class="loading-spinner" style="width:20px;height:20px;margin-left:8px"></div>
          <div v-if="branchesError" class="error-message" style="margin-left:8px">{{ branchesError }}</div>
        </div>

        <div v-if="inventoryLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading inventory...</p>
        </div>

        <div v-else-if="inventoryError" class="error-container">
          <p class="error-message">{{ inventoryError }}</p>
          <button class="btn-retry" @click="fetchInventory">Retry</button>
        </div>

        <div v-else class="table-container inventory-table-container">
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
              <tr v-for="product in inventory" :key="product.id">
                <td>{{ product.name }}</td>
                <td>{{ product.category || 'N/A' }}</td>
                <td><span :class="['pricing-type-badge', 'type-' + product.per_pack_or_individual]">{{ formatPricingType(product.per_pack_or_individual) }}</span></td>
                <td>{{ product.real_stock ?? product.stock }}</td>
                <td>{{ product.min_stock }}</td>
                <td class="expiry-cell">
                  <span v-if="product.expires_at" :class="['expiry-date', isExpired(product.expires_at) ? 'expired' : isExpiringSoon(product.expires_at) ? 'expiring-soon' : '']">
                    {{ formatDate(product.expires_at) }}
                  </span>
                  <span v-else class="muted">N/A</span>
                </td>
                <td>
                  <span :class="['status-badge', product.status === 'OK' ? 'status-ok' : 'status-low']">
                    {{ product.status }}
                  </span>
                </td>
                <td>
                  <button
                    v-if="product.status !== 'OK' && canRequestProcurement"
                    class="btn-primary btn-small"
                    :disabled="requesting[product.id]"
                    @click="requestProcurement(product)"
                  >
                    {{ requesting[product.id] ? 'Requesting...' : 'Request Procurement' }}
                  </button>
                  <span v-else-if="product.status !== 'OK'" class="muted-note">Not allowed</span>
                </td>
              </tr>
              <tr v-if="inventory.length === 0">
                <td colspan="7" class="empty-message">No products found in your branch.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Procurement Requests Section (moved to staff) -->
      <div class="panel-section" v-if="false">
        <h2 class="section-title">Procurement Requests</h2>
        <p class="section-description">
          {{ canRequestProcurement ? 'Create procurement requests for products needing budget approval' : 'Read-only access. Main Branch logistics cannot create procurement requests.' }}
        </p>

        <!-- Create New Request Button -->
        <button v-if="!showProcRequestForm && canRequestProcurement" class="btn-primary" @click="showProcRequestForm = true">
          + New Procurement Request
        </button>

        <!-- Procurement Request Form -->
        <div v-if="showProcRequestForm && canRequestProcurement" class="form-container">
          <h3>Create New Procurement Request</h3>
          <form @submit.prevent="submitProcRequest">
            <div class="form-group">
              <label>Product</label>
              <select v-model="procRequestForm.product_id" required>
                <option value="">Select product...</option>
                <option v-for="p in products" :key="p.id" :value="p.id">
                  {{ p.name }} (₱{{ formatPrice(p.price) }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Quantity</label>
              <input
                type="number"
                v-model="procRequestForm.quantity"
                min="1"
                required
              />
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="cancelProcRequest">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="procRequestSubmitting">
                {{ procRequestSubmitting ? 'Submitting...' : 'Submit Request' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Procurement Requests Table -->
        <div class="requests-list">
          <h3>My Procurement Requests</h3>
          <div v-if="procRequestsLoading" class="loading-container small">
            <div class="loading-spinner"></div>
          </div>
          <div v-else class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Updated</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="req in procurementRequests" :key="req.id">
                  <td>
                    <div class="product-name">{{ req.product?.name || '(no product)' }}</div>
                  </td>
                  <td>{{ req.quantity }}</td>
                  <td class="amount">{{ formatPrice(req.total_amount) }}</td>
                  <td>
                    <span :class="['status-badge', getProcStatusClass(req.status)]">
                      {{ formatProcStatus(req.status, req.budget_approved) }}
                    </span>
                  </td>
                  <td>{{ formatDate(req.updated_at) }}</td>
                </tr>
                <tr v-if="procurementRequests.length === 0">
                  <td colspan="5" class="empty-message">No procurement requests.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Product Request Section (moved to staff) -->
      <div class="panel-section" v-if="false">
        <h2 class="section-title">Request New Products</h2>
        <p class="section-description">
          Request new products to be added to the inventory. Requests must be approved by the owner and main branch logistics before the product becomes available for procurement.
        </p>

        <!-- Create New Product Request Button -->
        <button v-if="!showProductRequestForm && canRequestProcurement" class="btn-primary" @click="showProductRequestForm = true">
          + Request New Product
        </button>

        <!-- Product Request Form -->
        <div v-if="showProductRequestForm && canRequestProcurement" class="form-container">
          <h3>Request New Product</h3>
          <form @submit.prevent="submitProductRequest">
            <div class="form-group">
              <label>Product Name*</label>
              <input
                v-model="productRequestForm.name"
                type="text"
                placeholder="e.g., Organic Chicken Breast"
                required
              />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea
                v-model="productRequestForm.description"
                placeholder="Optional details about the product (specifications, notes, etc.)"
                rows="3"
              ></textarea>
            </div>
            <div class="form-group">
              <label>Unit of Measurement</label>
              <select v-model="productRequestForm.unit">
                <option value="">-- Select unit (optional) --</option>
                <option value="pcs">Pieces (pcs)</option>
                <option value="g">Grams (g)</option>
                <option value="kg">Kilograms (kg)</option>
                <option value="ml">Milliliters (ml)</option>
                <option value="l">Liters (l)</option>
                <option value="pack">Pack</option>
                <option value="box">Box</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="cancelProductRequest">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="productRequestSubmitting">
                {{ productRequestSubmitting ? 'Submitting...' : 'Submit Request' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Product Requests Table -->
        <div class="requests-list">
          <h3>My Product Requests</h3>
          <div v-if="productRequestsLoading" class="loading-container small">
            <div class="loading-spinner"></div>
          </div>
          <div v-else class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Unit</th>
                  <th>Status</th>
                  <th>Requested</th>
                  <th>Approved By</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="req in productRequests" :key="req.id">
                  <td>
                    <div class="product-name">{{ req.name }}</div>
                    <div v-if="req.description" class="product-description">{{ req.description }}</div>
                  </td>
                  <td>{{ req.unit || 'N/A' }}</td>
                  <td>
                    <span :class="['status-badge', getProductReqStatusClass(req.approval_status)]">
                      {{ formatProductReqStatus(req.approval_status) }}
                    </span>
                  </td>
                  <td>{{ formatDate(req.created_at) }}</td>
                  <td>
                    <div v-if="req.approver">{{ req.approver.full_name }}</div>
                    <div v-else class="muted">-</div>
                  </td>
                  <td>
                    <div v-if="req.approval_notes" class="approval-notes">{{ req.approval_notes }}</div>
                    <div v-else class="muted">-</div>
                  </td>
                </tr>
                <tr v-if="productRequests.length === 0">
                  <td colspan="6" class="empty-message">No product requests yet.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Pending Stock Section (moved to Logistics) -->
      <div class="panel-section">
        <h2 class="section-title">
          Pending Stock
          <span v-if="managerPendingCount > 0" class="panel-badge">{{ managerPendingCount }}</span>
        </h2>
        <p class="section-description">Items delivered and awaiting inventory confirmation by Logistics.</p>

        <div v-if="pendingStockLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading pending stock...</p>
        </div>

        <div v-else-if="pendingStockError" class="error-container">
          <p class="error-message">{{ pendingStockError }}</p>
          <button class="btn-retry" @click="fetchPendingStock">Retry</button>
        </div>

        <div v-else class="logistics-three-panel">
          <div class="logistics-panel-card">
            <div class="panel-card-header">Pending Requests</div>
            <div class="panel-card-body">
              <div class="table-container">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Qty</th>
                      <th>Requested</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in pendingStock" :key="item.procurement_request_id || item.id">
                      <td>{{ item.product_name || '(no product)' }}</td>
                      <td>{{ item.quantity }}</td>
                      <td>{{ formatDate(item.created_at) }}</td>
                      <td>
                        <button class="btn-primary btn-small" @click="selectPending(item)">
                          Review
                        </button>
                      </td>
                    </tr>
                    <tr v-if="pendingStock.length === 0">
                      <td colspan="4" class="empty-message">No pending stock awaiting confirmation.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="logistics-panel-card">
            <div class="panel-card-header">Confirmation Form</div>
            <div class="panel-card-body">
              <div v-if="!selectedPending" class="empty-state">Select a pending request to confirm stock.</div>
              <div v-else class="confirm-form">
                <div class="detail-grid">
                  <div class="detail-row">
                    <span class="detail-label">Product</span>
                    <strong class="detail-value">{{ selectedPending.product_name || '(no product)' }}</strong>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Requested Qty</span>
                    <strong class="detail-value">{{ selectedPending.quantity }}</strong>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Requested At</span>
                    <strong class="detail-value">{{ formatDate(selectedPending.created_at) }}</strong>
                  </div>
                  <div v-if="selectedPending.supplier_name" class="detail-row">
                    <span class="detail-label">Supplier</span>
                    <strong class="detail-value">{{ selectedPending.supplier_name }}</strong>
                  </div>
                </div>

                <div class="form-group">
                  <label>Counted Stock</label>
                  <input v-model.number="confirmForm.counted_stock" type="number" min="0" />
                  <div v-if="varianceValue !== null" :class="['variance-pill', varianceValue === 0 ? 'variance-ok' : (varianceValue > 0 ? 'variance-over' : 'variance-under')]">
                    Variance: {{ varianceValue }}
                  </div>
                </div>

                <div class="form-group">
                  <label>Proof of Product Image</label>
                  <input type="file" accept="image/*" @change="onProofSelected" />
                  <div v-if="confirmForm.proof_image" class="file-chip">{{ confirmForm.proof_image.name }}</div>
                </div>

                <div class="form-group">
                  <label>Notes (optional)</label>
                  <textarea v-model="confirmForm.notes" rows="3" placeholder="Add variance notes if needed"></textarea>
                </div>

                <div v-if="confirmError" class="error-message">{{ confirmError }}</div>

                <div class="form-actions">
                  <button class="btn-secondary" type="button" @click="clearSelectedPending">Clear</button>
                  <button class="btn-primary" type="button" :disabled="confirmSubmitting" @click="submitPendingConfirmation">
                    {{ confirmSubmitting ? 'Confirming...' : 'Confirm Stock' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="logistics-panel-card">
            <div class="panel-card-header">Supplier Alerts</div>
            <div class="panel-card-body">
              <div v-if="varianceLoading" class="loading-container small">
                <div class="loading-spinner"></div>
              </div>
              <div v-else-if="varianceError" class="error-message">{{ varianceError }}</div>
              <div v-else-if="varianceAlerts.length === 0" class="empty-state">No variance alerts yet.</div>
              <div v-else class="alerts-list">
                <div v-for="alert in varianceAlerts" :key="alert.transaction_id" class="alert-card">
                  <div class="alert-title">{{ alert.product_name || 'Unknown product' }}</div>
                  <div class="alert-meta">Expected {{ alert.expected_quantity }} | Actual {{ alert.actual_quantity }}</div>
                  <div class="alert-variance">Variance: {{ alert.variance }}</div>
                  <div v-if="alert.variance_reason" class="alert-reason">{{ alert.variance_reason }}</div>
                  <a v-if="alert.proof_of_delivery_path" class="alert-link" :href="storageUrl(alert.proof_of_delivery_path)" target="_blank" rel="noopener">View proof</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Budget Request Section (legacy - hidden for logistics minimal view) -->
      <div class="panel-section" v-if="false">
        <h2 class="section-title">Budget Requests (Legacy)</h2>
        <!-- existing budget form/table code unchanged -->
        <button v-if="!showRequestForm" class="btn-primary" @click="showRequestForm = true">
          + New Budget Request
        </button>
        <!-- ... rest of existing budget code ... -->
      </div>
    </template>

    <template #headerActions>
      <div class="header-profile-wrapper" @click.stop>
        <button class="header-profile-btn" @click="toggleProfileDropdown">
          <div class="header-avatar">
            <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
            <div v-else class="header-avatar-initials">{{ (userProfile.fullName || userProfile.full_name || 'U').charAt(0) }}</div>
          </div>
          <div class="header-name">{{ ((userProfile.fullName || userProfile.full_name) || ((userProfile.role || 'Manager') + (userProfile.branch_name ? ' - ' + userProfile.branch_name : (userProfile.branch ? ' - ' + userProfile.branch : '')) )).toUpperCase() }}</div>
        </button>
        <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
          <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
          <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
        </div>

      </div>
    </template>

    <!-- Side panel removed as requested -->
  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Logistics Manager Panel?</h3>
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
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import { showToast } from './toastStore'

// basic state
const userProfile = ref({})
const dashboardTotals = ref({ totalProducts: 0, lowStock: 0, pendingRequests: 0 })

const inventory = ref([])
const inventoryLoading = ref(false)
const inventoryError = ref('')

const canRequestProcurement = ref(true)

// Branch selector state (main-branch users can select branch)
const branches = ref([])
const selectedBranch = ref(null)
const branchesLoading = ref(false)
const branchesError = ref('')
// announcements removed

const products = ref([])
const procurementRequests = ref([])
const procRequestsLoading = ref(false)
const procRequestForm = ref({ product_id: '', quantity: 1 })
const procRequestSubmitting = ref(false)
const showProcRequestForm = ref(false)

// Pending stock (procurements awaiting inventory confirmation) - moved to Logistics
const pendingStock = ref([])
const pendingStockLoading = ref(false)
const pendingStockError = ref('')
const confirmingPending = ref({})
const selectedPending = ref(null)
const confirmForm = ref({ counted_stock: 0, notes: '', proof_image: null })
const confirmSubmitting = ref(false)
const confirmError = ref('')
const varianceAlerts = ref([])
const varianceLoading = ref(false)
const varianceError = ref('')

// Product Request state
const productRequests = ref([])
const productRequestsLoading = ref(false)
const productRequestForm = ref({ name: '', description: '', unit: '' })
const productRequestSubmitting = ref(false)
const showProductRequestForm = ref(false)

// legacy budget request form toggle (used in template)
const showRequestForm = ref(false)

// map of productId => boolean for per-row requesting state
const requesting = ref({})
const hasNotified = ref(false)
const managerPendingCount = computed(() => {
  const dashboardPending = Number(dashboardTotals.value?.pendingRequests || 0)
  const procurementPending = (procurementRequests.value || []).filter(r => (r.status || '').toLowerCase() === 'pending').length
  const productPending = (productRequests.value || []).filter(r => (r.approval_status || '').toLowerCase() === 'pending_approval').length
  const stockPending = (pendingStock.value || []).length
  return Math.max(dashboardPending, procurementPending, productPending, stockPending, 0)
})
watch(managerPendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending logistics requests.', 'info')
    hasNotified.value = true
  }
})

// Helper: perform request with graceful handling and token-fallback on 401.
async function requestWithFallback(method, url, options = {}) {
  const token = (typeof localStorage !== 'undefined') ? localStorage.getItem('token') || '' : '';

  // Build initial headers (do not overwrite caller-provided headers)
  const baseHeaders = Object.assign({}, options.headers || {});
  if (token && !baseHeaders['Authorization'] && !baseHeaders['authorization']) {
    baseHeaders['Authorization'] = `Bearer ${token}`;
  }

  try {
    const res = await axios[method](url, Object.assign({}, options, { headers: baseHeaders }));
    return res;
  } catch (e) {
    const status = e?.response?.status;
    console.warn('[ManagerLogistics] Request failed', { method, url, status, message: e.message })
    try {
      console.debug('[ManagerLogistics] Request config:', e?.config || options)
      console.debug('[ManagerLogistics] Response headers:', e?.response?.headers)
      console.debug('[ManagerLogistics] Document.cookie:', typeof document !== 'undefined' ? document.cookie : null)
    } catch (logErr) {}

    // On 401/403 try a CSRF cookie refresh first (session-based auth may have expired)
    if (status === 401 || status === 403) {
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        // attach XSRF token from cookie if present
        try {
          const xsrf = (function(){ try { return decodeURIComponent(document.cookie.split(';').find(r=>r.trim().startsWith('XSRF-TOKEN='))?.split('=')[1]||'') } catch(e){return ''} })()
          if (xsrf) baseHeaders['X-XSRF-TOKEN'] = xsrf
        } catch (xx) {}
        const retry = await axios[method](url, Object.assign({}, options, { headers: baseHeaders }))
        return retry
      } catch (e2) {
        console.warn('[ManagerLogistics] CSRF cookie refresh retry failed', e2?.message || e2)
        try { console.debug('[ManagerLogistics] CSRF retry response headers:', e2?.response?.headers) } catch(_) {}
      }

      // If CSRF retry didn't succeed, try explicit token retry if token exists
      if (token) {
        try {
          const retryHeaders = Object.assign({}, baseHeaders, { Authorization: `Bearer ${token}` })
          const retryOpts = Object.assign({}, options, { headers: retryHeaders })
          const res2 = await axios[method](url, retryOpts)
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          return res2
        } catch (e3) {
          console.warn('[ManagerLogistics] Token retry failed', e3?.message || e3)
          try { console.debug('[ManagerLogistics] Token retry response headers:', e3?.response?.headers) } catch(_) {}
        }
      }
    }

    throw e
  }
}

// POST helper to support body + options signature
async function requestWithFallbackPost(url, data = {}, options = {}) {
  const token = (typeof localStorage !== 'undefined') ? localStorage.getItem('token') || '' : '';
  const baseHeaders = Object.assign({}, options.headers || {});
  if (token && !baseHeaders['Authorization'] && !baseHeaders['authorization']) {
    baseHeaders['Authorization'] = `Bearer ${token}`;
  }

  try {
    const res = await axios.post(url, data, Object.assign({}, options, { headers: baseHeaders }))
    return res
  } catch (e) {
    const status = e?.response?.status
    console.warn('[ManagerLogistics] POST error', { url, status, message: e.message })

    if (status === 401 || status === 403) {
      // Try CSRF cookie refresh first
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        try {
          const xsrf = (function(){ try { return decodeURIComponent(document.cookie.split(';').find(r=>r.trim().startsWith('XSRF-TOKEN='))?.split('=')[1]||'') } catch(e){return ''} })()
          if (xsrf) baseHeaders['X-XSRF-TOKEN'] = xsrf
        } catch (xx) {}
        const retry = await axios.post(url, data, Object.assign({}, options, { headers: baseHeaders }))
        return retry
      } catch (e2) {
        console.warn('[ManagerLogistics] POST CSRF retry failed', e2?.message || e2)
      }

      // Token fallback
      if (token) {
        try {
          const retryHeaders = Object.assign({}, baseHeaders, { Authorization: `Bearer ${token}` })
          const retryOpts = Object.assign({}, options, { headers: retryHeaders })
          const res2 = await axios.post(url, data, retryOpts)
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          return res2
        } catch (e3) {
          console.warn('[ManagerLogistics] POST token retry failed', e3?.message || e3)
        }
      }
    }

    throw e
  }
}

function formatPrice(n) {
  const num = Number(n || 0)
  if (Number.isNaN(num)) return '₱0.00'
  return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleString() } catch (e) { return d }
}

function isExpired(expiryDate) {
  if (!expiryDate) return false
  try {
    const expiry = new Date(expiryDate)
    const now = new Date()
    return expiry < now
  } catch (e) {
    return false
  }
}

function isExpiringSoon(expiryDate) {
  if (!expiryDate) return false
  try {
    const expiry = new Date(expiryDate)
    const now = new Date()
    const daysUntilExpiry = (expiry - now) / (1000 * 60 * 60 * 24)
    return daysUntilExpiry >= 0 && daysUntilExpiry <= 7
  } catch (e) {
    return false
  }
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

function formatPricingType(type) {
  const typeMap = {
    'individual': 'Individual',
    'per_pack': 'Per Pack',
    'both': 'Both'
  }
  return typeMap[type] || 'N/A'
}

const varianceValue = computed(() => {
  if (!selectedPending.value) return null
  const expected = Number(selectedPending.value.quantity ?? 0)
  const actual = Number(confirmForm.value.counted_stock ?? 0)
  if (Number.isNaN(actual)) return null
  return actual - expected
})

// fetchAnnouncements removed

async function fetchInventory() {
  inventoryLoading.value = true
  inventoryError.value = ''
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    const res = await requestWithFallback('get', '/api/manager/logistics/inventory', { params, withCredentials: true })
    const rawData = res.data?.products ?? res.data?.data ?? res.data ?? []
    inventory.value = (Array.isArray(rawData) ? rawData : []).map(p => ({
      ...p,
      // Treat min_stock <= 0 as "unset" and use a sensible default (10)
      min_stock: (Number(p.min_stock) > 0) ? Number(p.min_stock) : 10,
      stock: Number(p.stock ?? 0),
      status: (Number(p.stock ?? 0) < ((Number(p.min_stock) > 0) ? Number(p.min_stock) : 10)) ? 'LOW STOCK' : 'OK'
    }))
  } catch (e) {
    console.error('Inventory fetch error:', e)
    inventoryError.value = 'Failed to load inventory: ' + (e.response?.data?.message || e.message)
    inventory.value = []
  } finally {
    inventoryLoading.value = false
    try { updateDashboardTotals() } catch (e) { /* ignore */ }
  }
}

async function loadProducts() {
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    const res = await requestWithFallback('get', '/api/manager/logistics/products', { params, withCredentials: true })
    const rawData = res.data?.data ?? res.data ?? []
    products.value = Array.isArray(rawData) ? rawData : []
  } catch (e) {
    console.error('Products load error:', e)
    products.value = []
  }
}

async function fetchProcRequests() {
  procRequestsLoading.value = true
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    // Request completed requests as well for branch-wide view
    if (selectedBranch.value) params.include_completed = 1
    const res = await requestWithFallback('get', '/api/procurement-requests', { params, withCredentials: true })
    console.debug('ManagerLogisticsPanel.fetchProcRequests params:', params, 'res.data:', res.data)
    // Handle Laravel paginate() response or plain array
    const data = res.data?.data ?? res.data ?? (res.data ? [res.data] : [])
    procurementRequests.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('Proc requests error:', e)
    procurementRequests.value = []
  } finally {
    procRequestsLoading.value = false
    try { updateDashboardTotals() } catch (e) { /* ignore */ }
  }
}

async function fetchPendingStock() {
  pendingStockLoading.value = true
  pendingStockError.value = ''
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    const res = await requestWithFallback('get', '/api/staff/inventory/pending-procurements', { params, withCredentials: true })
    const data = res.data ?? []
    pendingStock.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('fetchPendingStock error', e)
    pendingStock.value = []
    pendingStockError.value = 'Failed to load pending stock: ' + (e.response?.data?.message || e.message)
  } finally {
    pendingStockLoading.value = false
    try { updateDashboardTotals() } catch (e) { /* ignore */ }
  }
}

async function fetchVarianceAlerts() {
  varianceLoading.value = true
  varianceError.value = ''
  try {
    const res = await requestWithFallback('get', '/api/staff/inventory/variance-alerts', { withCredentials: true })
    const data = res.data?.data ?? []
    varianceAlerts.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('fetchVarianceAlerts error', e)
    varianceAlerts.value = []
    varianceError.value = 'Failed to load supplier alerts: ' + (e.response?.data?.message || e.message)
  } finally {
    varianceLoading.value = false
  }
}

function updateDashboardTotals() {
  const inv = inventory.value || []
  const procs = procurementRequests.value || []
  const prodReqs = productRequests.value || []
  dashboardTotals.value.totalProducts = inv.length
  dashboardTotals.value.lowStock = inv.filter(i => (i.status || '').toString().toLowerCase() !== 'ok').length
  // include procurement requests + product requests + pending stock awaiting inventory confirmation
  dashboardTotals.value.pendingRequests = procs.filter(r => (r.status || '').toString().toLowerCase() === 'pending').length
    + prodReqs.filter(r => (r.approval_status || '').toString().toLowerCase() === 'pending_approval').length
    + (pendingStock.value || []).length
}

async function fetchBranches() {
  branchesLoading.value = true
  branchesError.value = ''
  try {
    const res = await requestWithFallback('get', '/api/manager/logistics/branches', { withCredentials: true })
    const data = res.data?.data ?? res.data ?? []
    branches.value = Array.isArray(data) ? data : []
    // set selected branch: prefer current user branch if present, else first
    if (!selectedBranch.value) {
      // try to set from userProfile if available
      if (userProfile.value && userProfile.value.branch_id) {
        selectedBranch.value = userProfile.value.branch_id
      } else if (branches.value.length > 0) {
        selectedBranch.value = branches.value[0].id
      }
    }
  } catch (e) {
    console.error('Branches fetch error:', e)
    branches.value = []
    branchesError.value = 'Failed to load branches'
  } finally {
    branchesLoading.value = false
  }
}

async function submitProcRequest() {
  if (!canRequestProcurement.value) {
    showToast('Main Branch logistics cannot create procurement requests.', 'warning')
    return
  }

  procRequestSubmitting.value = true
  try {
    const payload = {
      product_id: procRequestForm.value.product_id,
      quantity: procRequestForm.value.quantity
    }
    await requestWithFallbackPost('/api/procurement-requests', payload, { withCredentials: true })
    showToast('Procurement request created', 'success')
    showProcRequestForm.value = false
    procRequestForm.value = { product_id: '', quantity: 1 }
    try {
      await fetchProcRequests()
      await fetchInventory()
    } catch (fetchErr) {
      console.error('Error refreshing data after procurement request', fetchErr)
    }
  } catch (e) {
    console.error('submitProcRequest error', e)
    showToast(e.response?.data?.message || 'Failed to create procurement request', 'error')
  } finally {
    procRequestSubmitting.value = false
  }
}

async function cancelProcRequest() {
  showProcRequestForm.value = false
  procRequestForm.value = { product_id: '', quantity: 1 }
}

async function requestProcurement(product) {
  if (!canRequestProcurement.value) {
    showToast('Main Branch logistics cannot create procurement requests.', 'warning')
    return
  }

  if (!(await window.swalConfirm(`Create procurement request for ${product.name}?`))) return

  requesting.value = { ...requesting.value, [product.id]: true }

  try {
    // Ensure we treat a min_stock of 0 as "unset" and use default (10).
    const minStock = Number(product.min_stock) > 0 ? Number(product.min_stock) : 10
    const currentStock = Number(product.real_stock ?? product.stock ?? 0) || 0
    // Order enough to reach minStock (at least 1). This avoids sending 0 or NaN quantities.
    const diff = Math.ceil(minStock - currentStock)
    const qty = Math.max(diff, 1)

    await requestWithFallbackPost('/api/procurement-requests', { product_id: product.id, quantity: qty }, { withCredentials: true })
    showToast('Procurement request created', 'success')
    try {
      await fetchProcRequests()
      await fetchInventory()
    } catch (fetchErr) {
      console.error('Error refreshing data after procurement request', fetchErr)
    }
  } catch (e) {
    console.error('requestProcurement error', e)
    showToast(e.response?.data?.message || 'Failed to create procurement request', 'error')
  } finally {
    requesting.value = { ...requesting.value, [product.id]: false }
  }
}

// branch selector removed: Main Branch users are redirected to main-branch panel

onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
  } catch (e) {
    // ignore - best-effort to get CSRF cookie for authenticated requests
  }

  // Auth check - do not redirect on failure (fixes logout issue). Log error and continue with empty profile.
  try {
    const profileRes = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
    userProfile.value = profileRes.data.user || profileRes.data || {}
    canRequestProcurement.value = userProfile.value?.can_request_procurement !== false
  } catch (err) {
    console.warn('Profile check failed:', err.response?.status, err.message)
    if (err?.response?.status === 401) {
      // Try token fallback silently
      try {
        const token = localStorage.getItem('token')
        if (token) {
          const res2 = await axios.get('/api/manager/logistics/profile', { headers: { Authorization: `Bearer ${token}` } })
          userProfile.value = res2.data.user || res2.data || {}
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          return // success
        }
      } catch (err2) {
        console.warn('Token fallback failed:', err2.message)
      }
    }
    // Set empty profile and continue - no redirect (fixes auto-logout)
    userProfile.value = {}
  }

  // Load branches first (if user can select), then data using selected branch
  await fetchBranches()

  // React to branch changes to reload tables
  watch(selectedBranch, async (newVal, oldVal) => {
    // fetch updated data for the selected branch
    await Promise.all([fetchInventory(), loadProducts(), fetchProcRequests(), fetchProductRequests(), fetchPendingStock(), fetchVarianceAlerts()])
  })

  // initial load
  await Promise.all([fetchInventory(), loadProducts(), fetchProcRequests(), fetchProductRequests(), fetchPendingStock(), fetchVarianceAlerts()])
})

function selectPending(item) {
  if (!item) return
  selectedPending.value = item
  confirmForm.value = {
    counted_stock: Number(item.quantity ?? 0),
    notes: '',
    proof_image: null,
  }
  confirmError.value = ''
}

function clearSelectedPending() {
  selectedPending.value = null
  confirmForm.value = { counted_stock: 0, notes: '', proof_image: null }
  confirmError.value = ''
}

function onProofSelected(e) {
  const file = e?.target?.files?.[0] || null
  confirmForm.value = { ...confirmForm.value, proof_image: file }
}

async function submitPendingConfirmation() {
  if (!selectedPending.value) {
    showToast('Select a pending request first', 'warning')
    return
  }

  const id = selectedPending.value.procurement_request_id || selectedPending.value.id
  if (!id) return

  const qty = Number(confirmForm.value.counted_stock)
  if (Number.isNaN(qty) || qty < 0) {
    confirmError.value = 'Enter a valid counted stock value'
    return
  }

  if (!confirmForm.value.proof_image) {
    confirmError.value = 'Proof image is required'
    return
  }

  confirmSubmitting.value = true
  confirmError.value = ''
  confirmingPending.value = { ...confirmingPending.value, [id]: true }

  try {
    const formData = new FormData()
    formData.append('counted_stock', String(qty))
    if (confirmForm.value.notes) formData.append('notes', confirmForm.value.notes)
    formData.append('proof_image', confirmForm.value.proof_image)

    // Use manager logistics endpoint for confirming stock
    await requestWithFallbackPost(`/api/manager/logistics/procurements/${id}/confirm-stock`, formData, { withCredentials: true })
    showToast('Stock confirmed', 'success')
    clearSelectedPending()
    await Promise.all([fetchPendingStock(), fetchInventory(), fetchProcRequests(), fetchVarianceAlerts()])
  } catch (e) {
    console.error('submitPendingConfirmation error', e)
    confirmError.value = e.response?.data?.message || 'Failed to confirm stock'
  } finally {
    confirmSubmitting.value = false
    confirmingPending.value = { ...confirmingPending.value, [id]: false }
  }
}

function storageUrl(path) {
  if (!path) return ''
  if (String(path).startsWith('http')) return path
  if (String(path).startsWith('/storage/')) return path
  return '/storage/' + String(path).replace(/^\/?storage\//, '')
}

// Handle profile updates emitted from OwnerPanelLayout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

// Expose handler so parent/layout can call or reference it if needed
defineExpose({ fetchInventory, onProfileUpdated })

// Header profile dropdown (match Procurement panel behavior)
const profileDropdownVisible = ref(false)
const ownerLayout = ref(null)

function toggleProfileDropdown() {
  profileDropdownVisible.value = !profileDropdownVisible.value
}

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

function openEditProfileFromHeader() {
  closeProfileDropdown()
  try {
    if (ownerLayout.value && typeof ownerLayout.value.openAvatarPicker === 'function') {
      ownerLayout.value.openAvatarPicker()
      return
    }
  } catch (e) {}
  try { window.dispatchEvent(new Event('open-owner-edit-profile')); return } catch (e) {}
  const fileInput = document.querySelector('#avatar-input') || document.querySelector('#avatar-input-modal') || document.querySelector('#global-avatar-input')
  if (fileInput) fileInput.click()
}

async function triggerLogoutFromHeader() {
  closeProfileDropdown()
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('triggerLogoutFromHeader failed', e) }
}

// Close dropdown when clicking outside
window.addEventListener('click', (e) => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})

// Logout modal state and handlers (keeps behavior consistent with other manager panels)
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

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
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
    try { window.location.replace('/staff-landing') } catch (e) { /* ignore */ }
  }, 600)
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

// Product Request Functions
async function fetchProductRequests() {
  productRequestsLoading.value = true
  try {
    const res = await requestWithFallback('get', '/api/product-requests', { withCredentials: true })
    const data = res.data?.data ?? res.data ?? []
    productRequests.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('Product requests fetch error:', e)
    productRequests.value = []
  } finally {
    productRequestsLoading.value = false
  }
}

async function submitProductRequest() {
  if (!canRequestProcurement.value) {
    showToast('Main Branch logistics cannot request new products.', 'warning')
    return
  }

  productRequestSubmitting.value = true
  try {
    const payload = {
      name: productRequestForm.value.name,
      description: productRequestForm.value.description || null,
      unit: productRequestForm.value.unit || null
    }
    await requestWithFallbackPost('/api/product-requests', payload, { withCredentials: true })
    showToast('Product request submitted for approval', 'success')
    showProductRequestForm.value = false
    productRequestForm.value = { name: '', description: '', unit: '' }
    try {
      await fetchProductRequests()
    } catch (fetchErr) {
      console.error('Error refreshing product requests', fetchErr)
    }
  } catch (e) {
    console.error('submitProductRequest error', e)
    showToast(e.response?.data?.error || 'Failed to submit product request', 'error')
  } finally {
    productRequestSubmitting.value = false
  }
}

function cancelProductRequest() {
  showProductRequestForm.value = false
  productRequestForm.value = { name: '', description: '', unit: '' }
}

function getProductReqStatusClass(status) {
  switch ((status || '').toLowerCase()) {
    case 'approved': return 'status-approved'
    case 'rejected': return 'status-rejected'
    case 'pending_approval': return 'status-pending'
    default: return 'status-pending'
  }
}

function formatProductReqStatus(status) {
  const statusMap = {
    'pending_approval': 'PENDING APPROVAL',
    'approved': 'APPROVED',
    'rejected': 'REJECTED'
  }
  return statusMap[status] || (status || '').toUpperCase()
}

</script>

<style scoped>
.panel-badge {
  position: absolute;
  top: -8px;
  right: -16px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
}

.hr-stat-card {
  position: relative;
}

.section-title {
  position: relative;
  display: inline-block;
}
/* Keep small button style used in other components */
.btn-small { padding: 6px 10px; font-size: 0.85rem; border-radius: 6px }

.panel-section {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* When profile column is hidden, lay out main + side as two columns so
   announcements (in the side) stay on the right and do not overlap main */
:deep(.admin-layout.no-profile-column) {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 1rem;
}
:deep(.admin-layout.no-profile-column) .admin-main { width: 100%; }
:deep(.admin-layout.no-profile-column) .admin-side { width: 360px; }

.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #4b2a06;
  margin: 0 0 8px 0;
}

.section-description {
  font-size: 14px;
  color: #666;
  margin: 0 0 16px 0;
}

.branch-filter-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 14px;
}

.branch-filter-row label {
  font-size: 14px;
  color: #4b2a06;
  font-weight: 600;
}

.branch-filter-row select {
  min-width: 240px;
  padding: 8px 10px;
  border: 1px solid #d7d7d7;
  border-radius: 8px;
  font-size: 14px;
  background: #fff;
}

.branch-filter-row select:focus {
  outline: none;
  border-color: #ff9f43;
  box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.15);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.loading-container.small {
  padding: 20px;
}

.loading-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid rgba(255, 159, 67, 0.3);
  border-top: 3px solid #ff9f43;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  background: #fff5f5;
  border-radius: 8px;
}

.error-message {
  color: #dc3545;
  margin-bottom: 12px;
}

.btn-retry {
  padding: 8px 16px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.btn-retry:hover {
  background: #c82333;
}

.table-container {
  overflow-x: auto;
}

.logistics-three-panel {
  display: grid;
  grid-template-columns: 1.2fr 1fr 0.9fr;
  gap: 16px;
}

.logistics-panel-card {
  border: 1px solid #f0e6d8;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
}

.panel-card-header {
  background: #fff4e6;
  padding: 12px 16px;
  font-weight: 600;
  color: #5a2c0a;
  font-size: 14px;
}

.panel-card-body {
  padding: 16px;
}

.confirm-form .form-group {
  margin-bottom: 14px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 12px;
  margin-bottom: 16px;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 12px;
  color: #8b5a2b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 14px;
  color: #2f2f2f;
}

.file-chip {
  margin-top: 8px;
  display: inline-flex;
  padding: 4px 8px;
  border-radius: 999px;
  background: #f1f5f9;
  font-size: 12px;
  color: #475569;
}

.variance-pill {
  display: inline-flex;
  margin-top: 8px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.variance-ok {
  background: rgba(34, 197, 94, 0.15);
  color: #15803d;
}

.variance-over {
  background: rgba(59, 130, 246, 0.15);
  color: #1d4ed8;
}

.variance-under {
  background: rgba(239, 68, 68, 0.15);
  color: #b91c1c;
}

.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.alert-card {
  border: 1px solid #f1e1c9;
  border-radius: 12px;
  padding: 12px;
  background: #fffaf2;
}

.alert-title {
  font-weight: 600;
  color: #4b2a06;
  margin-bottom: 4px;
}

.alert-meta {
  font-size: 12px;
  color: #6b7280;
}

.alert-variance {
  font-size: 13px;
  font-weight: 600;
  color: #b45309;
  margin-top: 6px;
}

.alert-reason {
  margin-top: 6px;
  color: #4b5563;
  font-size: 12px;
}

.alert-link {
  display: inline-flex;
  margin-top: 8px;
  font-size: 12px;
  color: #1d4ed8;
}

.empty-state {
  color: #8b8b8b;
  font-size: 13px;
}

@media (max-width: 1100px) {
  .logistics-three-panel {
    grid-template-columns: 1fr;
  }
}

.inventory-table-container {
  max-height: 360px;
  overflow-y: auto;
}

.inventory-table-container table thead {
  position: sticky;
  top: 0;
  z-index: 2;
  background: #fff4e6;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.data-table td.amount {
  text-align: right;
  white-space: nowrap;
  font-weight: 600;
}

.product-name {
  white-space: normal;
  word-break: break-word;
  max-width: 380px;
}

.data-table th {
  background: #fff4e6;
  font-weight: 600;
  color: #5a2c0a;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.data-table td {
  color: #333;
  font-size: 14px;
}

.empty-message {
  text-align: center;
  color: #999;
  font-style: italic;
}

.muted-note {
  color: #7a7a7a;
  font-size: 12px;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-ok {
  background: rgba(46, 204, 113, 0.15);
  color: #27ae60;
}

.status-low {
  background: rgba(231, 76, 60, 0.15);
  color: #e74c3c;
}

.status-approved {
  background: rgba(46, 204, 113, 0.15);
  color: #27ae60;
}

.pricing-type-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: capitalize;
}

.pricing-type-badge.type-individual {
  background: #dbeafe;
  color: #1e40af;
}

.pricing-type-badge.type-per_pack {
  background: #d1fae5;
  color: #065f46;
}

.pricing-type-badge.type-both {
  background: #fef3c7;
  color: #92400e;
}

.expiry-cell {
  white-space: nowrap;
}

.expiry-date {
  font-size: 0.9rem;
  font-weight: 500;
}

.expiry-date.expired {
  color: #dc2626;
  background: rgba(220, 38, 38, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}

.expiry-date.expiring-soon {
  color: #d97706;
  background: rgba(217, 119, 6, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}

.status-rejected {
  background: rgba(231, 76, 60, 0.15);
  color: #e74c3c;
}

.status-pending {
  background: rgba(241, 196, 15, 0.15);
  color: #f39c12;
}

.form-container {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  border: 1px solid #e9ecef;
}

.form-container h3 {
  margin: 0 0 16px 0;
  color: #4b2a06;
  font-size: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-weight: 500;
  color: #333;
  margin-bottom: 6px;
}

.form-group textarea,
.form-group input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
}

.form-group textarea:focus,
.form-group input:focus {
  outline: none;
  border-color: #ff9f43;
  box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.15);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-primary {
  padding: 10px 20px;
  background: #ff9f43;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #ffb366;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 10px 20px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #5a6268;
}

.success-message {
  background: #d4edda;
  color: #155724;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  border: 1px solid #c3e6cb;
}

.requests-list h3 {
  margin: 24px 0 16px 0;
  color: #4b2a06;
  font-size: 16px;
}

.logout-confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 45;
}

.logout-confirm-box {
  background: var(--surface-card);
  color: var(--text-primary);
  padding: 18px 20px 16px;
  border-radius: 12px;
  max-width: 360px;
  width: 100%;
  box-shadow: 0 12px 30px rgba(16,24,40,0.08);
  border: 1px solid var(--border-stroke);
}

.logout-confirm-box h3 {
  margin-bottom: 6px;
  font-size: 0.98rem;
}

.logout-confirm-box p {
  font-size: 0.8rem;
  opacity: 0.9;
}

.logout-actions {
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn-cancel,
.btn-confirm {
  border-radius: 999px;
  border: none;
  padding: 6px 14px;
  font-size: 0.8rem;
  cursor: pointer;
}

.btn-cancel {
  background: rgba(16,24,40,0.04);
  color: var(--text-primary);
}

.btn-confirm {
  background: var(--alert);
  color: #ffffff;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
}

.logo-loading-box {
  text-align: center;
}

.logo-loading-img {
  width: 120px;
  margin-bottom: 16px;
}

/* Announcements panel uses the default layout so it scrolls with page */
/* Force the side column to normal document flow to override other compiled styles */
:deep(.admin-layout.no-profile-column) .admin-side {
  position: static !important;
  top: auto !important;
  align-self: stretch !important;
  margin-top: 0 !important;
  max-height: none !important;
  overflow: visible !important;
  padding-right: 0 !important;
}

:deep(.announcements-panel .panel-header) {
  position: static !important;
}

:deep(.announcements-panel .panel-body) {
  overflow: visible !important;
  max-height: none !important;
}

@media (min-width: 1000px) {
  /* Ensure we override the layout's sticky side column at large viewports
     so announcements become part of the document flow and scroll away. */
  :deep(.admin-layout.no-profile-column) .admin-side {
    position: static !important;
    top: auto !important;
    align-self: stretch !important;
     /* align announcements vertically with the first panel (Inventory Monitor)
       tuned for desktop layouts; reduced for tighter alignment */
     margin-top: 212px !important;
    max-height: none !important;
    overflow: visible !important;
    padding-right: 0 !important;
  }

  :deep(.announcements-panel) {
    max-height: none !important;
    overflow: visible !important;
  }

  :deep(.announcements-panel .panel-header) {
    position: static !important;
  }

  :deep(.announcements-panel .panel-body) {
    overflow: visible !important;
    max-height: none !important;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Panel-specific header profile appearance to match procurement layout badge */
.header-profile-btn {
  border: 1px solid rgba(0,0,0,0.08);
  background: #fff;
  padding: 6px 10px;
  border-radius: 8px;
}
.header-name { font-size: 0.8rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 320px }

/* Avatar styles (initials / image) */
.header-avatar { width:36px; height:36px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f3f4f6; margin-right:8px }
.header-avatar-img { width:100%; height:100%; background-size:cover; background-position:center }
.header-avatar-initials { font-weight:700; color:#374151 }

/* Product request specific styles */
.product-description {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
  font-style: italic;
}

.approval-notes {
  font-size: 12px;
  color: #666;
  max-width: 300px;
  word-wrap: break-word;
}

.muted {
  color: #999;
  font-style: italic;
}

.muted-note {
  color: #999;
  font-size: 12px;
}
</style>
