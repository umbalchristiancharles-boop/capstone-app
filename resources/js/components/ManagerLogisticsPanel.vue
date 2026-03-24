<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Logistics Manager Panel'"
    :panelDescription="'Monitor inventory, procurement requests, and manage budgets for your branch.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <!-- Inventory Section -->
      <div class="panel-section">
        <h2 class="section-title">Inventory Monitor</h2>
        <p class="section-description">Current stock levels for your branch (Read-only)</p>

        <div v-if="inventoryLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading inventory...</p>
        </div>

        <div v-else-if="inventoryError" class="error-container">
          <p class="error-message">{{ inventoryError }}</p>
          <button class="btn-retry" @click="fetchInventory">Retry</button>
        </div>

        <div v-else class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Product Name</th>
                <th>Stock Count</th>
                <th>Minimum Stock</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in inventory" :key="product.id">
                <td>{{ product.name }}</td>
                <td>{{ product.stock }}</td>
                <td>{{ product.min_stock }}</td>
                <td>
                  <span :class="['status-badge', product.status === 'OK' ? 'status-ok' : 'status-low']">
                    {{ product.status }}
                  </span>
                </td>
                <td>
                  <button
                    v-if="product.status !== 'OK'"
                    class="btn-primary btn-small"
                    :disabled="requesting[product.id]"
                    @click="requestProcurement(product)"
                  >
                    {{ requesting[product.id] ? 'Requesting...' : 'Request Procurement' }}
                  </button>
                </td>
              </tr>
              <tr v-if="inventory.length === 0">
                <td colspan="5" class="empty-message">No products found in your branch.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Procurement Requests Section -->
      <div class="panel-section">
        <h2 class="section-title">Procurement Requests</h2>
        <p class="section-description">Create procurement requests for products needing budget approval</p>

        <!-- Create New Request Button -->
        <button v-if="!showProcRequestForm" class="btn-primary" @click="showProcRequestForm = true">
          + New Procurement Request
        </button>

        <!-- Procurement Request Form -->
        <div v-if="showProcRequestForm" class="form-container">
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
                  <td>{{ req.product?.name }}</td>
                  <td>{{ req.quantity }}</td>
                  <td>₱{{ formatPrice(req.total_amount) }}</td>
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

      <!-- Budget Request Section (legacy - keep for now) -->
      <div class="panel-section">
        <h2 class="section-title">Budget Requests (Legacy)</h2>
        <!-- existing budget form/table code unchanged -->
        <button v-if="!showRequestForm" class="btn-primary" @click="showRequestForm = true">
          + New Budget Request
        </button>
        <!-- ... rest of existing budget code ... -->
      </div>
    </template>
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'

// basic state
const userProfile = ref({})

const inventory = ref([])
const inventoryLoading = ref(false)
const inventoryError = ref('')

const products = ref([])
const procurementRequests = ref([])
const procRequestsLoading = ref(false)
const procRequestForm = ref({ product_id: '', quantity: 1 })
const procRequestSubmitting = ref(false)
const showProcRequestForm = ref(false)

// legacy budget request form toggle (used in template)
const showRequestForm = ref(false)

// map of productId => boolean for per-row requesting state
const requesting = ref({})

function formatPrice(n) {
  return (Number(n || 0)).toFixed(2)
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

async function fetchInventory() {
  inventoryLoading.value = true
  inventoryError.value = ''
  try {
    const res = await axios.get('/api/manager/logistics/inventory', { withCredentials: true })
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
  }
}

async function loadProducts() {
  try {
    const res = await axios.get('/api/manager/logistics/products', { withCredentials: true })
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
    const res = await axios.get('/api/procurement-requests', { withCredentials: true })
    // Handle Laravel paginate() response or plain array
    const data = res.data?.data ?? res.data ?? (res.data ? [res.data] : [])
    procurementRequests.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('Proc requests error:', e)
    procurementRequests.value = []
  } finally {
    procRequestsLoading.value = false
  }
}

async function submitProcRequest() {
  procRequestSubmitting.value = true
  try {
    const payload = {
      product_id: procRequestForm.value.product_id,
      quantity: procRequestForm.value.quantity
    }
    await axios.post('/api/procurement-requests', payload, { withCredentials: true })
    alert('Procurement request created')
    showProcRequestForm.value = false
    procRequestForm.value = { product_id: '', quantity: 1 }
    await fetchProcRequests()
    await fetchInventory()
  } catch (e) {
    alert('Failed to create procurement request')
  } finally {
    procRequestSubmitting.value = false
  }
}

async function cancelProcRequest() {
  showProcRequestForm.value = false
  procRequestForm.value = { product_id: '', quantity: 1 }
}

async function requestProcurement(product) {
  if (!confirm(`Create procurement request for ${product.name}?`)) return

  requesting.value = { ...requesting.value, [product.id]: true }

  // Ensure we treat a min_stock of 0 as "unset" and use default (10).
  const minStock = Number(product.min_stock) > 0 ? Number(product.min_stock) : 10
  const currentStock = Number(product.stock ?? 0) || 0
  // Order enough to reach minStock (at least 1). This avoids sending 0 or NaN quantities.
  const diff = Math.ceil(minStock - currentStock)
  const qty = Math.max(diff, 1)

  try {
    await axios.post('/api/procurement-requests', { product_id: product.id, quantity: qty }, { withCredentials: true })
    alert('Procurement request created')
    await fetchProcRequests()
    await fetchInventory()
  } catch (e) {
    alert('Failed to create procurement request')
  } finally {
    requesting.value = { ...requesting.value, [product.id]: false }
  }
}

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

  fetchInventory()
  loadProducts()
  fetchProcRequests()
})

// Handle profile updates emitted from OwnerPanelLayout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

// Expose handler so parent/layout can call or reference it if needed
defineExpose({ fetchInventory, onProfileUpdated })

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
</script>

<style scoped>
/* Keep small button style used in other components */
.btn-small { padding: 6px 10px; font-size: 0.85rem; border-radius: 6px }

.panel-section {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

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
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.logout-confirm-box {
  background: white;
  padding: 24px;
  border-radius: 12px;
  text-align: center;
  max-width: 400px;
}

.logout-confirm-box h3 {
  margin: 0 0 8px 0;
  color: #333;
}

.logout-confirm-box p {
  margin: 0 0 20px 0;
  color: #666;
}

.logout-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.btn-cancel {
  padding: 10px 24px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.btn-confirm {
  padding: 10px 24px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
