<template>
  <div class="superadmin-logistics-wrapper">
    <!-- Use header slot for back button (matches procurement UI) -->

    <!-- OwnerPanelLayout (adapted for superadmin) -->
    <OwnerPanelLayout
      panelTitle="Super Admin Logistics Panel"
      panelDescription="Monitor inventory, procurement requests, and manage across all branches."
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
        <!-- Branch Selector (matches procurement UI placement) -->
        <div class="branch-selector-section" style="margin-bottom: 1rem; display: flex; align-items: center;">
          <label style="font-weight:600; color:#1e293b; margin-right:0.75rem; font-size:0.95rem;">Select Branch:</label>
          <select v-model="selectedBranchId" @change="handleBranchChange" style="padding:0.45rem 0.6rem; border:1px solid #CBD5E1; border-radius:6px; background:white; font-size:0.9rem; min-width:220px;">
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }} (ID: {{ branch.id }})</option>
          </select>
        </div>
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
                  <td>{{ product.real_stock ?? product.stock }}</td>
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

        <!-- Logistics Transactions Section (NEW) -->
        <div class="panel-section">
          <h2 class="section-title">Real-Time Logistics Tracking</h2>
          <p class="section-description">Monitor all stock movements, deliveries, and transfers across branches</p>

          <!-- Logistics Dashboard Summary -->
          <div v-if="!logisticsDashboardLoading && logisticsDashboard" class="logistics-summary-grid">
            <div class="logistics-card">
              <div class="card-value">{{ logisticsDashboard.summary?.pending || 0 }}</div>
              <div class="card-label">Pending Operations</div>
            </div>
            <div class="logistics-card">
              <div class="card-value">{{ logisticsDashboard.summary?.in_progress || 0 }}</div>
              <div class="card-label">In Progress</div>
            </div>
            <div class="logistics-card">
              <div class="card-value">{{ logisticsDashboard.summary?.requires_verification || 0 }}</div>
              <div class="card-label">Requires Verification</div>
            </div>
            <div class="logistics-card warning">
              <div class="card-value">{{ logisticsDashboard.summary?.with_variance || 0 }}</div>
              <div class="card-label">Variances Detected</div>
            </div>
          </div>

          <!-- Logistics Transactions Table -->
          <div v-if="logisticsDashboardLoading" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Loading logistics data...</p>
          </div>

          <div v-else-if="logisticsTransactions.length > 0" class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Reference</th>
                  <th>Product</th>
                  <th>Type</th>
                  <th>Qty (Expected/Actual)</th>
                  <th>Status</th>
                  <th>From → To</th>
                  <th>Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="transaction in logisticsTransactions.slice(0, 10)" :key="transaction.id"
                    :class="transaction.is_duplicate ? 'row-duplicate' : ''">
                  <td><strong>{{ transaction.reference_number }}</strong></td>
                  <td>{{ transaction.product?.name || transaction.description }}</td>
                  <td>
                    <span :class="['badge', 'type-' + transaction.type]">
                      {{ transaction.type }}
                    </span>
                  </td>
                  <td>
                    <span :class="(transaction.actual_quantity && transaction.actual_quantity !== transaction.expected_quantity) ? 'variance-warning' : ''">
                      {{ transaction.expected_quantity }}{{ transaction.actual_quantity ? '/' + transaction.actual_quantity : '' }}
                    </span>
                  </td>
                  <td>
                    <span :class="['status-badge', 'status-' + transaction.status]">
                      {{ formatTransactionStatus(transaction.status) }}
                    </span>
                  </td>
                  <td>
                    <div class="branch-flow">
                      <span v-if="transaction.source_branch_id">{{ transaction.sourceBranch?.name || 'Unknown' }}</span>
                      <span v-else>—</span>
                      <span class="flow-arrow">→</span>
                      <span v-if="transaction.destination_branch_id">{{ transaction.destinationBranch?.name || 'Unknown' }}</span>
                      <span v-else>—</span>
                    </div>
                  </td>
                  <td>{{ formatDate(transaction.created_at) }}</td>
                  <td>
                    <button v-if="transaction.status !== 'completed' && !transaction.is_duplicate"
                            class="btn-link" @click="openUpdateStatus(transaction)">
                      Update
                    </button>
                    <span v-else class="text-muted">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="text-center mt-3">
              <button class="btn-secondary" @click="loadMoreTransactions">
                Load More Transactions
              </button>
            </div>
          </div>

          <div v-else class="empty-message">
            No logistics transactions recorded yet.
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

    <!-- UPDATE LOGISTICS STATUS MODAL (NEW) -->
    <transition name="fade">
      <div v-if="showTransactionModal" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Update Logistics Status</h3>
          <p v-if="selectedTransaction">
            Update <strong>{{ selectedTransaction.reference_number }}</strong>
            from <strong>{{ formatTransactionStatus(selectedTransaction.status) }}</strong>
          </p>
          <div class="logistics-status-buttons">
            <button v-if="selectedTransaction?.status === 'pending'"
                    class="btn-status" @click="updateTransactionStatus('in_transit')">
              Mark In Transit
            </button>
            <button v-if="selectedTransaction?.status === 'in_transit'"
                    class="btn-status" @click="updateTransactionStatus('at_destination')">
              Mark At Destination
            </button>
            <button v-if="selectedTransaction?.status === 'at_destination'"
                    class="btn-status" @click="updateTransactionStatus('verified')">
              Mark Verified
            </button>
            <button v-if="['verified', 'confirmed'].includes(selectedTransaction?.status)"
                    class="btn-status" @click="updateTransactionStatus('completed')">
              Mark Completed
            </button>
            <button class="btn-secondary" @click="showTransactionModal = false">Cancel</button>
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

// Logistics monitoring
const logisticsDashboard = ref(null)
const logisticsDashboardLoading = ref(false)
const logisticsTransactions = ref([])
const logisticsPage = ref(1)
const selectedTransaction = ref(null)
const showTransactionModal = ref(false)

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
  fetchLogisticsDashboard()
  fetchLogisticsTransactions()
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

// Logistics monitoring functions
async function fetchLogisticsDashboard() {
  logisticsDashboardLoading.value = true
  try {
    const params = selectedBranchId.value ? { branch_id: selectedBranchId.value } : {}
    const res = await axios.get('/api/superadmin/logistics/dashboard', { params, withCredentials: true })
    logisticsDashboard.value = res.data?.data || {}
  } catch (e) {
    console.error('Failed to fetch logistics dashboard:', e)
  } finally {
    logisticsDashboardLoading.value = false
  }
}

async function fetchLogisticsTransactions() {
  logisticsDashboardLoading.value = true
  try {
    const params = {
      branch_id: selectedBranchId.value || null,
      per_page: 25,
      page: logisticsPage.value,
    }
    const res = await axios.get('/api/superadmin/logistics/transactions', { params, withCredentials: true })
    logisticsTransactions.value = res.data?.data?.data || []
  } catch (e) {
    console.error('Failed to fetch logistics transactions:', e)
    logisticsTransactions.value = []
  } finally {
    logisticsDashboardLoading.value = false
  }
}

function loadMoreTransactions() {
  logisticsPage.value++
  fetchLogisticsTransactions()
}

function formatTransactionStatus(status) {
  const statusMap = {
    'pending': 'Pending',
    'in_transit': 'In Transit',
    'at_destination': 'At Destination',
    'verified': 'Verified',
    'confirmed': 'Confirmed',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
  }
  return statusMap[status] || status.toUpperCase()
}

function openUpdateStatus(transaction) {
  selectedTransaction.value = transaction
  showTransactionModal.value = true
}

async function updateTransactionStatus(newStatus) {
  if (!selectedTransaction.value) return
  try {
    await axios.post(`/api/superadmin/logistics/transactions/${selectedTransaction.value.id}/update-status`,
      { status: newStatus, notes: `Updated to ${newStatus}` },
      { withCredentials: true }
    )
    alert('Logistics status updated successfully')
    showTransactionModal.value = false
    fetchLogisticsDashboard()
    fetchLogisticsTransactions()
  } catch (e) {
    alert('Failed to update logistics status: ' + (e.response?.data?.error || e.message))
  }
}

async function requestProcurement(product) {
  if (!confirm(`Create procurement request for ${product.name}?`)) return

  requesting.value[product.id] = true
  const minStock = Number(product.min_stock ?? 10)
  const currentStock = Number(product.real_stock ?? product.stock ?? 0)
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
      fetchProcRequests().catch(e => { console.error('fetchProcRequests failed:', e); }),
      fetchLogisticsDashboard().catch(e => { console.error('fetchLogisticsDashboard failed:', e); }),
      fetchLogisticsTransactions().catch(e => { console.error('fetchLogisticsTransactions failed:', e); }),
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

/* Back Button: keep it inline with the header so it aligns responsively */
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

.back-to-superadmin-btn:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.16);
}

.back-to-superadmin-btn svg { stroke: #666 }

/* Branch Selector */
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
  /* Limit height so long tables don't stretch the page; enable internal scrolling */
  max-height: 420px;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

/* Keep table header visible while scrolling the table body */
.data-table thead th {
  position: sticky;
  top: 0;
  z-index: 6;
  background: #fff4e6; /* match header background */
}

/* Ensure thead row doesn't collapse visually when sticky */
.data-table thead tr {
  display: table-row;
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

/* Ensure announcements/right side doesn't overlap main content when profile column hidden */
:deep(.admin-layout.no-profile-column) {
  display: grid !important;
  grid-template-columns: 1fr 360px !important;
  gap: 1rem !important;
}

:deep(.admin-layout.no-profile-column) .admin-main {
  width: 100% !important;
}

:deep(.admin-side) {
  /* Keep the side panel in normal flow so it scrolls with the page
     instead of staying fixed (user requested it should move on scroll). */
  position: relative;
  align-self: start;
  max-height: none;
  overflow: visible;
  padding-right: 8px;
}

/* Make the announcements panel visually match the main `panel-section` */
:deep(.announcements-panel) {
  background: rgba(255,255,255,0.95);
  border-radius: 16px;
  padding: 20px 18px;
  margin-bottom: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border: 1px solid rgba(0,0,0,0.04);
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
}

:deep(.announcements-panel .panel-header) {
  padding: 0 6px 10px 6px;
}

:deep(.announcement-list) {
  list-style: none;
  margin: 0;
  padding: 6px 0 0 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

:deep(.announcement-item) {
  background: #fff;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.04);
  box-shadow: 0 2px 8px rgba(3,37,65,0.04);
}

:deep(.announcement-title) {
  font-weight: 700;
  margin-bottom: 4px;
}

:deep(.announcement-meta) {
  font-size: 12px;
  color: rgba(0,0,0,0.5);
  margin-bottom: 6px;
}

@media (max-width: 1200px) {
  :deep(.announcements-panel) {
    max-width: 100%;
    padding: 10px;
  }

  :deep(.announcement-list) {
    padding: 0;
  }

  :deep(.announcement-item) {
    font-size: 0.95rem;
  }
}

@media (max-width: 768px) {
  :deep(.admin-side) {
    position: relative !important;
    top: auto !important;
    max-height: none !important;
    overflow: visible !important;
    padding-right: 0 !important;
  }

  :deep(.announcements-panel) {
    padding: 8px 6px !important;
    margin-bottom: 12px;
  }

  :deep(.announcement-meta) { font-size: 11px }
  :deep(.announcement-message) { font-size: 0.95rem }
}

/* Remove OwnerPanelLayout's orange gradient for this specific view and
   make the layout responsive: collapse right column under main on smaller screens */
.superadmin-logistics-wrapper :deep(.min-h-screen) {
  /* kept for compatibility when layout is nested, but also add a global override below */
  background: transparent !important;
}

:deep(.min-h-screen) {
  /* Override the orange gradient used on OwnerPanelLayout root */
  background: transparent !important;
}

@media (max-width: 1200px) {
  :deep(.admin-layout.no-profile-column) {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 1rem !important;
  }

  :deep(.admin-side) {
    position: relative !important;
    top: auto !important;
    max-height: none !important;
    overflow: visible !important;
    padding-right: 0 !important;
    order: 3 !important;
  }

  .branch-selector-section {
    margin: 16px 12px !important;
    max-width: calc(100% - 24px) !important;
  }

  .back-to-superadmin-btn {
    padding: 8px 12px !important;
    display: inline-flex !important;
  }
}

/* Logistics Monitoring Styles */
.logistics-summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.logistics-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  padding: 20px;
  color: white;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.logistics-card.warning {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-value {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 8px;
}

.card-label {
  font-size: 14px;
  opacity: 0.9;
  font-weight: 500;
}

.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.type-procurement {
  background: #e3f2fd;
  color: #1976d2;
}

.type-transfer {
  background: #f3e5f5;
  color: #7b1fa2;
}

.type-delivery {
  background: #e0f2f1;
  color: #00796b;
}

.type-incoming {
  background: #fff3e0;
  color: #e65100;
}

.type-outgoing {
  background: #fce4ec;
  color: #c2185b;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-in_transit {
  background: #cce5ff;
  color: #004085;
}

.status-at_destination {
  background: #d1ecf1;
  color: #0c5460;
}

.status-verified {
  background: #d4edda;
  color: #155724;
}

.status-confirmed {
  background: #d4edda;
  color: #155724;
}

.status-completed {
  background: #d4edda;
  color: #155724;
}

.status-cancelled {
  background: #f8d7da;
  color: #721c24;
}

.branch-flow {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #666;
}

.flow-arrow {
  color: #999;
  font-weight: bold;
}

.variance-warning {
  color: #dc3545;
  font-weight: 600;
}

.row-duplicate {
  opacity: 0.6;
  background-color: #f5f5f5;
}

.btn-link {
  background: none;
  border: none;
  color: #667eea;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  padding: 0;
}

.btn-link:hover {
  text-decoration: underline;
  color: #764ba2;
}

.text-muted {
  color: #999;
  font-size: 12px;
}

.mt-3 {
  margin-top: 20px;
}

.text-center {
  text-align: center;
}

.logistics-status-buttons {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 16px;
}

.btn-status {
  padding: 12px 16px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.btn-status:hover {
  background: #5568d3;
}
</style>

