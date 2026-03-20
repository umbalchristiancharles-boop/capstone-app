<template>
  <div class="superadmin-logistics-wrapper">
    <!-- Standalone Back Button (top-left, like current LogisticsManager) -->
    <button @click="goBackToSuperAdmin" class="back-to-superadmin-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Super Admin
    </button>

    <!-- Branch Selector (at top) -->
    <div class="branch-selector-section">
      <label>Select Branch:</label>
      <select v-model="selectedBranchId" @change="handleBranchChange">
        <option value="">All Branches</option>
        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
          {{ branch.name }}
        </option>
      </select>
    </div>

    <!-- OwnerPanelLayout (adapted for superadmin) -->
    <OwnerPanelLayout
      panelTitle="Super Admin Logistics Panel"
      panelDescription="Monitor inventory, procurement requests, and manage across all branches."
    >
      <template #main>
        <!-- Inventory Section (filtered by branch) -->
        <div class="panel-section">
          <h2 class="section-title">Inventory Monitor ({{ selectedBranchName }})</h2>
          <p class="section-description">Current stock levels {{ selectedBranchId ? `for ${selectedBranchName}` : 'across all branches' }} (Read-only)</p>

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
                  <td>{{ product.name }} {{ product.branch_name ? `(${product.branch_name})` : '' }}</td>
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
                  <td colspan="5" class="empty-message">No products found{{ selectedBranchId ? ` for ${selectedBranchName}` : '' }}.</td>
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
                <label>Branch (optional)</label>
                <select v-model="procRequestForm.branch_id">
                  <option value="">Any Branch</option>
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }}
                  </option>
                </select>
              </div>
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
                    <th>Branch</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="req in procurementRequests" :key="req.id">
                    <td>{{ req.product?.name }}</td>
                    <td>{{ req.branch?.name || 'N/A' }}</td>
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
                    <td colspan="6" class="empty-message">No procurement requests.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>
    </OwnerPanelLayout>

    <!-- LOGOUT CONFIRM (same as ManagerLogisticsPanel) -->
    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from Super Admin Logistics Panel?</h3>
          <p>This will end your current session for Chikin Tayo.</p>
          <div class="logout-actions">
            <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
            <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
const router = useRouter()

// Profile (superadmin) - removed, not used in this panel

// Branch state
const branches = ref([])
const selectedBranchId = ref('')
const selectedBranchName = computed(() => branches.value.find(b => b.id == selectedBranchId.value)?.name || 'All')

// Inventory
const inventory = ref([])
const inventoryLoading = ref(false)
const inventoryError = ref('')
const requesting = ref({})

// Products (for proc form, filtered)
const products = ref([])

// Procurement Requests
const procurementRequests = ref([])
const procRequestsLoading = ref(false)
const procRequestForm = ref({ branch_id: '', product_id: '', quantity: 1 })
const procRequestSubmitting = ref(false)
const showProcRequestForm = ref(false)

// Modals
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)

// Utils
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

// Navigation
function goBackToSuperAdmin() {
  router.push('/super-admin-panel')
}

// Branch handling
async function fetchBranches() {
  try {
    const res = await axios.get('/api/superadmin/logistics/branches', { withCredentials: true })
    branches.value = res.data || []
  } catch (e) {
    console.error('Failed to fetch branches:', e)
  }
}

function handleBranchChange() {
  fetchInventory()
  loadProducts()
  fetchProcRequests()
}

// Inventory (superadmin endpoints)
async function fetchInventory() {
  inventoryLoading.value = true
  inventoryError.value = ''
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/superadmin/logistics/products', { params, withCredentials: true })
    const rawData = res.data?.data ?? res.data ?? res.data ?? []
    inventory.value = (Array.isArray(rawData) ? rawData : []).map(p => ({
      ...p,
      status: (p.stock <= (p.min_stock ?? 0)) ? 'LOW STOCK' : 'OK'
    }))
  } catch (e) {
    console.error('Inventory fetch error:', e)
    inventoryError.value = 'Failed to load inventory: ' + (e.response?.data?.message || e.message)
    inventory.value = []
  } finally {
    inventoryLoading.value = false
  }
}

// Products (for dropdown)
async function loadProducts() {
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/superadmin/logistics/products', { params, withCredentials: true })
    const rawData = res.data?.data ?? res.data ?? []
    products.value = Array.isArray(rawData) ? rawData : []
  } catch (e) {
    console.error('Products load error:', e)
    products.value = []
  }
}

// Procurement Requests
async function fetchProcRequests() {
  procRequestsLoading.value = true
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/procurement-requests', { params, withCredentials: true })
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
      ...procRequestForm.value,
      branch_id: procRequestForm.value.branch_id || null
    }
    await axios.post('/api/procurement-requests', payload, { withCredentials: true })
    alert('Procurement request created')
    showProcRequestForm.value = false
    procRequestForm.value = { branch_id: selectedBranchId.value, product_id: '', quantity: 1 }
    await fetchProcRequests()
    await fetchInventory()
  } catch (e) {
    alert('Failed to create procurement request: ' + (e.response?.data?.message || e.message))
  } finally {
    procRequestSubmitting.value = false
  }
}

function cancelProcRequest() {
  showProcRequestForm.value = false
  procRequestForm.value = { branch_id: selectedBranchId.value, product_id: '', quantity: 1 }
}

async function requestProcurement(product) {
  if (!confirm(`Create procurement request for ${product.name}?`)) return

  requesting.value[product.id] = true
  const minStock = Number(product.min_stock ?? 10)
  const currentStock = Number(product.stock ?? 0)
  const qty = Math.max(minStock - currentStock, minStock)

  try {
    const payload = {
      product_id: product.id,
      quantity: qty,
      branch_id: product.branch_id
    }
    await axios.post('/api/procurement-requests', payload, { withCredentials: true })
    alert('Procurement request created')
    await fetchProcRequests()
    await fetchInventory()
  } catch (e) {
    alert('Failed to create procurement request')
  } finally {
    requesting.value[product.id] = false
  }
}

// Superadmin profile handling removed — account panel is hidden in this view

// Logout handlers (same)
function cancelLogout() {
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  localStorage.clear()
  sessionStorage.clear()
  router.push('/staff-landing')
}

onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
  } catch (e) {
    console.warn('CSRF cookie fetch failed:', e)
  }

  try {
    await Promise.all([
      fetchBranches().catch(e => { console.error('fetchBranches failed:', e); }),
      fetchInventory().catch(e => { console.error('fetchInventory failed:', e); }),
      loadProducts().catch(e => { console.error('loadProducts failed:', e); }),
      fetchProcRequests().catch(e => { console.error('fetchProcRequests failed:', e); })
    ])
  } catch (error) {
    console.error('SuperAdminLogisticsPanel mount error:', error)
  }
})

defineExpose({ fetchInventory })
</script>

<style scoped>
.superadmin-logistics-wrapper {
  min-height: 100vh;
}

/* Back Button (standalone, top-left) */
.back-to-superadmin-btn {
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 100;
  padding: 12px 20px;
  background: rgba(255,255,255,0.95);
  border: none;
  border-radius: 8px;
  color: #333;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.back-to-superadmin-btn:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.back-to-superadmin-btn svg {
  stroke: #666;
}

/* Branch Selector */
.branch-selector-section {
  background: rgba(255,255,255,0.95);
  padding: 16px 24px;
  margin: 80px 20px 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: 400px;
}

.branch-selector-section label {
  font-weight: 600;
  color: #4b2a06;
  white-space: nowrap;
}

.branch-selector-section select {
  flex: 1;
  padding: 10px 12px;
  border: 2px solid #ff9f43;
  border-radius: 8px;
  font-size: 14px;
  background: white;
}

.branch-selector-section select:focus {
  outline: none;
  border-color: #ff7a18;
  box-shadow: 0 0 0 3px rgba(255,159,67,0.2);
}

/* Layout adjustments for OwnerPanelLayout */
:deep(.panel-section) {
  margin-top: 20px;
}

/* Table enhancements for branch */
.data-table td:first-child {
  font-weight: 500;
}

/* All original CSS from ManagerLogisticsPanel */
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

.btn-small { 
  padding: 6px 10px; 
  font-size: 0.85rem; 
  border-radius: 6px 
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

.form-group input, .form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
}

.form-group input:focus, .form-group select:focus {
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

.btn-primary:hover:not(:disabled) {
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

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

