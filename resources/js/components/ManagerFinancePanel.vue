<template>
  <OwnerPanelLayout
    ref="ownerLayout"
    :userProfile="userProfile"
    :panelTitle="'Finance Manager Panel'"
    :panelDescription="'Manage budget approvals and monitor your branch financial performance'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    :showProfileColumn="false"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="manager-finance">


    <div class="page-header">
      <h1 class="page-title">Finance Manager Panel</h1>
      <p class="page-subtitle">Manage budget approvals and monitor your branch financial performance</p>
    </div>

    <div class="filter-bar">
      <div class="filter-group">
        <label>Date Range:</label>
        <select v-model="selectedRange" @change="onRangeChange">
          <option value="today">Today</option>
          <option value="yesterday">Yesterday</option>
          <option value="thisWeek">This Week</option>
          <option value="thisMonth">This Month</option>
          <option value="lastMonth">Last Month</option>
          <option value="all">All Time</option>
        </select>
      </div>
      <button class="btn-refresh" @click="refreshDashboard">Refresh</button>
    </div>

    <div v-if="budgetLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading budget requests...</p>
    </div>

    <div v-else class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon revenue-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Total Sales</span>
          <span class="kpi-value">{{ dashboardTotals.totalSales || '₱0' }}</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon orders-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Total Orders</span>
          <span class="kpi-value">{{ dashboardTotals.totalOrders ?? (transactions.length || 0) }}</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon expenses-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Pending Approvals</span>
          <span class="kpi-value">{{ pendingBudgetCount }}</span>
        </div>
      </div>

      <div class="kpi-card highlight">
        <div class="kpi-icon profit-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="20" x2="12" y2="10"></line>
            <line x1="18" y1="20" x2="18" y2="4"></line>
            <line x1="6" y1="20" x2="6" y2="16"></line>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Net Revenue</span>
          <span class="kpi-value">{{ dashboardTotals.revenue || '₱0' }}</span>
        </div>
      </div>
    </div>

    <!-- Budget Requests Section (keeps existing functionality) -->
    <div class="branch-stats panel-section">
      <h2 class="section-title">Budget Request Approvals</h2>
      <p class="section-description">Review and manage budget requests for your branch</p>

      <div v-if="budgetLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading budget requests...</p>
      </div>

      <div v-else class="table-container">
        <table class="branch-table data-table">
          <thead>
            <tr>
              <th>Date Requested</th>
              <th>Requester</th>
              <th>Purpose</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="req in budgetRequests" :key="req.id">
              <td>{{ formatDate(req.date_requested) }}</td>
              <td>{{ req.requester_name }}</td>
              <td>{{ req.purpose }}</td>
              <td>₱{{ req.requested_amount }}</td>
              <td>
                <span :class="['status-badge', getStatusClass(req.status)]">{{ req.status }}</span>
              </td>
              <td>
                <div v-if="req.status === 'Pending'" class="action-buttons">
                  <button class="btn-approve" @click="approveRequest(req.id)" :disabled="processingId === req.id">{{ processingId === req.id ? 'Processing...' : 'Approve' }}</button>
                  <button class="btn-reject" @click="rejectRequest(req.id)" :disabled="processingId === req.id">{{ processingId === req.id ? 'Processing...' : 'Reject' }}</button>
                </div>
                <div v-else-if="req.status === 'Approved' && req.purpose && /Procurement Request #\d+/i.test(req.purpose)">
                  <button class="btn-approve" @click="markBudgetGiven(req.id)" :disabled="processingId === req.id">{{ processingId === req.id ? 'Processing...' : 'Budget Given' }}</button>
                </div>
                <span v-else class="processed-info">{{ req.status }} by {{ req.processed_by || 'Unknown' }}<br><small>{{ formatDate(req.date_processed) }}</small></span>
              </td>
            </tr>
            <tr v-if="budgetRequests.length === 0">
              <td colspan="6" class="empty-message">No budget requests found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Branch Budgets (new) -->
    <div class="branch-stats panel-section">
      <h2 class="section-title">Receipt Submissions</h2>
      <p class="section-description">Review uploaded physical receipts from suppliers and confirm them to proceed to delivery.</p>

      <div v-if="receiptsLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading receipt submissions...</p>
      </div>

      <div v-else class="table-container">
        <table class="branch-table data-table">
          <thead>
            <tr>
              <th>Request ID</th>
              <th>Product</th>
              <th>Branch</th>
              <th>Uploaded At</th>
              <th>Receipt</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in receiptSubmissions" :key="r.id">
              <td>{{ r.id }}</td>
              <td>{{ r.product?.name || '(no product)' }}</td>
              <td>{{ r.branch?.name || 'N/A' }}</td>
              <td>{{ formatDate(r.receipt_uploaded_at) }}</td>
              <td>
                <a href="#" @click.prevent="openReceiptPreview(r)">View Receipt</a>
              </td>
              <td>
                <button class="btn-approve" @click="confirmReceipt(r.id)" :disabled="confirmingId === r.id">{{ confirmingId === r.id ? 'Confirming...' : 'Confirm Receipt' }}</button>
              </td>
            </tr>
            <tr v-if="receiptSubmissions.length === 0">
              <td colspan="6" class="empty-message">No receipt submissions awaiting confirmation.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="branch-stats panel-section">
      <h2 class="section-title">Branch Budgets</h2>
      <p class="section-description">View and edit branch budgets. Changes are applied immediately.</p>

      <div v-if="branchesLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading branches...</p>
      </div>

      <div v-else class="table-container">
        <table class="branch-table data-table">
          <thead>
            <tr>
              <th>Branch</th>
              <th>Code</th>
              <th>Budget</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in branches" :key="b.id">
              <td>{{ b.name }}</td>
              <td>{{ b.code }}</td>
              <td>
                <div v-if="editingBudgetId === b.id">
                  <input type="number" v-model="editBudgetValue" step="0.01" />
                </div>
                <div v-else>
                  ₱{{ Number(b.budget || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                </div>
              </td>
              <td>
                <div v-if="editingBudgetId === b.id">
                  <button class="btn-approve" @click="saveBudget(b.id)">Save</button>
                  <button class="btn-reject" @click="cancelEditBudget">Cancel</button>
                </div>
                <div v-else>
                  <button class="btn-secondary" @click="startEditBudget(b.id, b.budget)">Edit</button>
                </div>
              </td>
            </tr>
            <tr v-if="branches.length === 0">
              <td colspan="4" class="empty-message">No branches found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Finance Reports Section (kept as-is) -->
    <finance-panel-content :reports="financeReports" :transactions="transactions" />

    <!-- LOGOUT & OVERLAY (kept unchanged) -->
    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from Finance Manager Panel?</h3>
          <p>This will end your current session for Chikin Tayo.</p>
          <div class="logout-actions">
            <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
            <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showOverlay" class="loading-overlay">
        <div class="logo-loading-box">
          <img :src="logoImg" alt="Chikin Tayo" class="logo-loading-img" />
          <p>{{ overlayText }}</p>
        </div>
      </div>
    </transition>
    <!-- Receipt preview modal -->
    <transition name="fade">
      <div v-if="receiptModalVisible" class="modal-backdrop" @click.self="closeReceiptPreview">
        <div class="receipt-preview-modal">
          <div class="modal-header"><h3>Receipt Preview</h3></div>
          <div class="modal-body">
            <div class="preview-meta">
              <div><strong>Request ID:</strong> {{ receiptModalRequest?.id }}</div>
              <div><strong>Product:</strong> {{ receiptModalRequest?.product?.name || '(no product)' }}</div>
              <div><strong>Uploaded:</strong> {{ formatDate(receiptModalRequest?.receipt_uploaded_at) }}</div>
            </div>
            <div class="preview-image">
              <img :src="receiptModalPath" alt="receipt preview" />
            </div>
          </div>
          <div class="modal-footer" style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px">
            <button class="btn-secondary" @click="closeReceiptPreview">Close</button>
            <a :href="receiptModalPath" target="_blank" class="btn-primary">Open in new tab</a>
          </div>
        </div>
      </div>
    </transition>
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
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import FinancePanelContent from './finance/FinancePanelContent.vue'
import axios from 'axios'

// Logo image
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

const refreshInterval = ref(null)

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

// Header profile dropdown (match Logistics panel behavior)
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
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('triggerLogoutFromHeader failed', e) }
}

// Close dropdown when clicking outside
window.addEventListener('click', (e) => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})

// Helper function to safely extract array from response
const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && Array.isArray(response?.[key])) return response[key]
  if (key && Array.isArray(response?.data?.[key])) return response.data[key]
  if (key && response?.[key]?.data) return response[key].data
  if (key && response?.data?.[key]?.data) return response.data[key].data
  return []
}

const userProfile = ref({})
const dashboardTotals = ref({
  totalSales: '₱0',
  pendingApprovals: 0,
  revenue: '₱0'
})
// Branch budgets
const branches = ref([])
const branchesLoading = ref(true)
const editingBudgetId = ref(null)
const editBudgetValue = ref(null)
const financeReports = ref([])
const transactions = ref([])

// UI filter state (used by new layout controls)
const selectedRange = ref('all')

// Budget requests state
const budgetRequests = ref([])
const budgetLoading = ref(true)
const processingId = ref(null)
// Receipt submissions
const receiptSubmissions = ref([])
const receiptsLoading = ref(false)
const confirmingId = ref(null)
// Receipt preview modal state
const receiptModalVisible = ref(false)
const receiptModalPath = ref('')
const receiptModalRequest = ref(null)

// Computed: count of pending requests
const pendingBudgetCount = computed(() => {
  return budgetRequests.value.filter(r => r.status === 'Pending').length
})

// Handle profile update from layout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
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
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

// Fetch budget requests
async function fetchBudgetRequests() {
  budgetLoading.value = true

  try {
    const response = await axios.get('/api/manager/finance/budget/all', { withCredentials: true })
    if (response.data.ok) {
      budgetRequests.value = response.data.requests
    }
  } catch (err) {
    console.error('Error fetching budget requests:', err)
  } finally {
    budgetLoading.value = false
  }
}

// Fetch branches and budgets
async function fetchBranches() {
  branchesLoading.value = true
  try {
    const res = await axios.get('/api/manager/finance/branches', { withCredentials: true })
    if (res.data && res.data.ok) {
      branches.value = res.data.branches || []
    }
  } catch (err) {
    console.error('Error fetching branches:', err)
  } finally {
    branchesLoading.value = false
  }
}

function startEditBudget(id, current) {
  editingBudgetId.value = id
  editBudgetValue.value = Number(current || 0).toFixed(2)
}

function cancelEditBudget() {
  editingBudgetId.value = null
  editBudgetValue.value = null
}

async function saveBudget(id) {
  if (editingBudgetId.value !== id) return
  const val = parseFloat(editBudgetValue.value)
  if (Number.isNaN(val)) { alert('Invalid budget amount'); return }
  try {
    const res = await axios.put(`/api/manager/finance/branches/${id}/budget`, { budget: val }, { withCredentials: true })
    if (res.data && res.data.ok) {
      const idx = branches.value.findIndex(b => b.id === id)
      if (idx !== -1) branches.value[idx] = res.data.branch
      cancelEditBudget()
      alert('Budget updated')
    }
  } catch (err) {
    console.error('Failed to save budget:', err)
    alert(err.response?.data?.message || 'Failed to update budget')
  }
}

// Approve budget request
async function approveRequest(id) {
  if (processingId.value) return

  if (!(await window.swalConfirm('Are you sure you want to approve this budget request?'))) {
    return
  }

  processingId.value = id

  try {
    const response = await axios.put(`/api/manager/finance/budget/${id}/approve`, {}, { withCredentials: true })

    if (response.data.ok) {
      // Update the local request status
      const index = budgetRequests.value.findIndex(r => r.id === id)
      if (index !== -1) {
        budgetRequests.value[index].status = 'Approved'
        budgetRequests.value[index].processed_by = response.data.request.processed_by
        budgetRequests.value[index].date_processed = response.data.request.date_processed
      }
      alert('Budget request approved successfully!')
    }
  } catch (err) {
    console.error('Error approving request:', err)
    alert(err.response?.data?.message || 'Failed to approve budget request')
  } finally {
    processingId.value = null
  }
}

// Reject budget request
async function rejectRequest(id) {
  if (processingId.value) return

  if (!(await window.swalConfirm('Are you sure you want to reject this budget request?'))) {
    return
  }

  processingId.value = id

  try {
    const response = await axios.put(`/api/manager/finance/budget/${id}/reject`, {}, { withCredentials: true })

    if (response.data.ok) {
      // Update the local request status
      const index = budgetRequests.value.findIndex(r => r.id === id)
      if (index !== -1) {
        budgetRequests.value[index].status = 'Rejected'
        budgetRequests.value[index].processed_by = response.data.request.processed_by
        budgetRequests.value[index].date_processed = response.data.request.date_processed
      }
      alert('Budget request rejected.')
    }
  } catch (err) {
    console.error('Error rejecting request:', err)
    alert(err.response?.data?.message || 'Failed to reject budget request')
  } finally {
    processingId.value = null
  }
}

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function getStatusClass(status) {
  switch (status) {
    case 'Approved': return 'status-approved'
    case 'Rejected': return 'status-rejected'
    default: return 'status-pending'
  }
}

onMounted(() => {
  loadInitialData()

  // load receipt submissions for finance review
  loadReceiptSubmissions()

  // Auto-refresh every 30 seconds
  refreshInterval.value = setInterval(async () => {
    try {
      await refreshDashboard()
    } catch (e) {
      console.warn('Auto-refresh failed:', e)
    }
  }, 30000)
})

// Extract initial load into separate function
async function loadInitialData() {
  try {
    const [profileRes, dashRes, reportsRes, txRes] = await Promise.all([
      axios.get('/api/manager/finance/profile', { withCredentials: true }),
      axios.get('/api/manager/finance/dashboard', { params: { range: selectedRange.value }, withCredentials: true }),
      axios.get('/api/manager/finance/reports', { withCredentials: true }),
      axios.get('/api/manager/finance/transactions', { withCredentials: true })
    ])

    userProfile.value = profileRes.data.user
    dashboardTotals.value = {
      totalSales: dashRes.data.totalRevenue ? '₱' + Number(dashRes.data.totalRevenue).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      pendingApprovals: dashRes.data.pendingApprovals || 0,
      revenue: dashRes.data.netProfit ? '₱' + Number(dashRes.data.netProfit).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      totalOrders: dashRes.data.totalOrders || 0
    }
    financeReports.value = extractArray(reportsRes.data, 'reports')
    transactions.value = extractArray(txRes.data, 'transactions')
    await fetchBudgetRequests()
    await fetchBranches()
  } catch (err) {
    console.error('Error loading initial data:', err)
  }
}

async function loadReceiptSubmissions() {
  receiptsLoading.value = true
  try {
    const res = await axios.get('/api/procurement-requests/receipt-submissions', { withCredentials: true })
    if (res.data && res.data.ok) {
      receiptSubmissions.value = res.data.requests || []
    } else {
      receiptSubmissions.value = []
    }
  } catch (e) {
    console.error('Failed to load receipt submissions', e)
    receiptSubmissions.value = []
  } finally {
    receiptsLoading.value = false
  }
}

function storageUrl(path) {
  if (!path) return '#'
  // if stored under public/receipts, convert to storage URL
  if (typeof path !== 'string') return '#'
  if (path.startsWith('public/')) return '/storage/' + path.replace(/^public\//, '')
  if (path.startsWith('/receipts/')) return path
  if (path.startsWith('receipts/')) return '/' + path
  // already a public URL or full path
  return path
}

async function confirmReceipt(id) {
  if (await window.swalConfirm('Confirm this receipt and move request to On Delivery?')) {
    confirmingId.value = id
    try {
      const res = await axios.post(`/api/procurement-requests/${id}/confirm-receipt`, {}, { withCredentials: true })
      alert(res.data?.message || 'Receipt confirmed')
      await loadReceiptSubmissions()
      await refreshDashboard()
      // notify other open UIs (procurement manager) to refresh their lists
      try { window.dispatchEvent(new CustomEvent('receiptConfirmed', { detail: id })) } catch (e) { /* ignore */ }
    } catch (e) {
      console.error('Confirm receipt failed', e)
      alert(e.response?.data?.message || 'Failed to confirm receipt')
    } finally {
      confirmingId.value = null
    }
  }
}

function openReceiptPreview(r) {
  if (!r) return
  receiptModalRequest.value = r
  receiptModalPath.value = storageUrl(r.receipt_path)
  receiptModalVisible.value = true
}

function closeReceiptPreview() {
  receiptModalVisible.value = false
  receiptModalPath.value = ''
  receiptModalRequest.value = null
}

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})

// Refresh dashboard / re-fetch data for current filter (used by button + polling)
async function refreshDashboard() {
  try {
    const [dashRes, txRes] = await Promise.all([
      axios.get('/api/manager/finance/dashboard', { params: { range: selectedRange.value }, withCredentials: true }),
      axios.get('/api/manager/finance/transactions', { withCredentials: true }),
      fetchBudgetRequests(),
      fetchBranches()
    ])

    dashboardTotals.value = {
      totalSales: dashRes.data.totalRevenue ? '₱' + Number(dashRes.data.totalRevenue).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      pendingApprovals: dashRes.data.pendingApprovals || 0,
      revenue: dashRes.data.netProfit ? '₱' + Number(dashRes.data.netProfit).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      totalOrders: dashRes.data.totalOrders || 0
    }
    transactions.value = extractArray(txRes.data, 'transactions')
  } catch (err) {
    console.error('Error refreshing dashboard:', err)
  }
}

function onRangeChange() {
  refreshDashboard()
}

// Mark budget as given by finance (handed to procurement)
async function markBudgetGiven(id) {
  if (processingId.value) return
  if (!(await window.swalConfirm('Confirm you have handed the budget to procurement?'))) return
  processingId.value = id
  try {
    const response = await axios.put(`/api/manager/finance/budget/${id}/given`, {}, { withCredentials: true })
    if (response.data && response.data.ok) {
      // update local list
      const idx = budgetRequests.value.findIndex(r => r.id === id)
      if (idx !== -1) {
        // Use the returned budget_request payload to update the local item
        const br = response.data.budget_request || null
        if (br) {
          // If the backend could not persist the new enum value (DB enum mismatch),
          // treat a successful procurement_request update as 'Budget Given' for UX.
          if (br.status === 'Approved' && response.data.procurement_request) {
            budgetRequests.value[idx].status = 'Budget Given'
          } else {
            budgetRequests.value[idx].status = br.status || budgetRequests.value[idx].status
          }
          budgetRequests.value[idx].processed_by = br.processed_by || budgetRequests.value[idx].processed_by
          budgetRequests.value[idx].date_processed = br.date_processed || budgetRequests.value[idx].date_processed
        } else {
          // fallback: mark as Budget Given when no budget_request payload returned
          budgetRequests.value[idx].status = 'Budget Given'
          budgetRequests.value[idx].processed_by = response.data.procurement_request?.finance_user_id || budgetRequests.value[idx].processed_by
        }
      }
      alert('Budget marked as given. Procurement can now place orders.')
    } else {
      alert(response.data?.message || 'Failed to mark budget as given')
    }
  } catch (err) {
    console.error('Failed to mark budget given:', err)
    alert(err.response?.data?.message || 'Failed to mark budget as given')
  } finally {
    processingId.value = null
  }
}
</script>

<style scoped>
.manager-finance {
  background-color: #FBF7F4;
  padding: 30px;
  min-height: 100vh;
  color: #4b2a06;
}

.page-header {
  margin-bottom: 24px;
}

.btn-primary, .btn-success, .btn-secondary, .btn-info, .btn-danger {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  transition: all 0.18s ease;
}

.btn-primary {
  background: #ff7a18;
  color: #fff;
}

.btn-secondary {
  background: #fff;
  color: #4b2a06;
  border: 1px solid rgba(75,42,6,0.06);
  border-radius: 8px;
  padding: 8px 14px;
  font-weight: 700;
}

.btn-secondary:hover { background: #fff8f3 }

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 8px 16px;
  font-size: 0.9rem;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #4b2a06;
  margin: 0 0 8px 0;
}

.page-subtitle {
  color: #6B7280;
  margin: 0;
  font-size: 14px;
}

.filter-bar {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 24px;
  padding: 18px;
  background: #fff8f5;
  border-radius: 14px;
  border: 1px solid rgba(75,42,6,0.04);
  box-shadow: 0 8px 24px rgba(75,42,6,0.03);
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-group label {
  font-weight: 500;
  color: #374151;
}

.filter-group select {
  padding: 8px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  color: #111827;
  cursor: pointer;
}

.filter-group select:focus {
  outline: none;
  border-color: #ff7a18;
  box-shadow: 0 0 0 6px rgba(255,122,24,0.06);
}

.btn-refresh {
  background: #ff7a18;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.18s ease;
}

.btn-refresh:hover { background:#ff9f43 }

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #E5E7EB;
  border-top: 3px solid #0066FF;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.kpi-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap:20px; margin-bottom:32px }
.kpi-card { display:flex; align-items:center; gap:16px; padding:20px; background:#FFF6F1; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06); border:1px solid rgba(75,42,6,0.04) }
.kpi-card:hover { transform: translateY(-2px); box-shadow:0 8px 28px rgba(0,0,0,0.08) }
.kpi-card.highlight { border-left:6px solid #FF9F43; background:#FFF8F3 }
.kpi-icon { display:flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:12px; background:#FFF3EA; color:#7a3b00 }
.kpi-content { display:flex; flex-direction:column }
.kpi-label { font-size:13px; color:#7a5a44; margin-bottom:4px }
.kpi-value { font-size:22px; font-weight:700; color:#4b2a06 }

.section-title { font-size:18px; font-weight:700; color:#4b2a06; margin:0 0 16px 0 }

.branch-stats, .recent-transactions, .panel-section { background:white; padding:20px; border-radius:12px; border:1px solid #E5E7EB; box-shadow:0 4px 12px rgba(0,0,0,0.1); margin-bottom:24px }

.branch-table-container, .transactions-table-container, .table-container { overflow-x:auto }
.branch-table, .transactions-table, .data-table { width:100%; border-collapse:collapse }
.branch-table th, .branch-table td, .transactions-table th, .transactions-table td, .data-table th, .data-table td { padding:12px 16px; text-align:left }
.branch-table th, .transactions-table th, .data-table th { background:#EFF6FF; color:#1E3A8A; font-weight:600; padding:12px 16px; font-size:14px }
.branch-table td, .transactions-table td, .data-table td { color:#374151; font-size:14px; border-bottom:1px solid #E5E7EB }

.profit-positive { color:#2ecc71; font-weight:600 }
.profit-negative { color:#ff6b6b; font-weight:600 }

.status-badge { background:#FACC15; color:#1F2937; border-radius:6px; padding:4px 10px; font-size:12px; font-weight:500; text-transform:capitalize }

.action-buttons { display:flex; gap:8px }
.btn-approve { padding:6px 14px; background:#27ae60; color:white; border:none; border-radius:6px; font-size:13px; cursor:pointer }
.btn-approve:disabled { background:#95a5a6; cursor:not-allowed }
.btn-reject { padding:6px 14px; background:#e74c3c; color:white; border:none; border-radius:6px; font-size:13px; cursor:pointer }
.btn-reject:disabled { background:#95a5a6; cursor:not-allowed }

.empty-message { text-align:center; color:#999; font-style:italic }
.processed-info { font-size:13px; color:#666 }
.processed-info small { color:#999 }

.logout-confirm-backdrop { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000 }
.logout-confirm-box { background:white; padding:24px; border-radius:12px; text-align:center; max-width:400px }
.logout-confirm-box h3 { margin:0 0 8px 0; color:#333 }
.logout-confirm-box p { margin:0 0 20px 0; color:#666 }
.logout-actions { display:flex; gap:12px; justify-content:center }
.btn-cancel { padding:10px 24px; background:#6c757d; color:white; border:none; border-radius:8px; cursor:pointer }
.btn-confirm { padding:10px 24px; background:#dc3545; color:white; border:none; border-radius:8px; cursor:pointer }

.loading-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.95); display:flex; align-items:center; justify-content:center; z-index:1001 }
.logo-loading-box { text-align:center }
.logo-loading-img { width:120px; margin-bottom:16px }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s }
.fade-enter-from, .fade-leave-to { opacity:0 }

/* Receipt preview modal styles */
.receipt-preview-modal { background:#fff; padding:12px; border-radius:10px; width:min(90vw,900px); max-height:90vh; overflow:auto; box-shadow:0 18px 40px rgba(0,0,0,0.35); }
.receipt-preview-modal .modal-header h3 { margin:0 0 8px 0 }
.receipt-preview-modal .modal-body { display:grid; grid-template-columns: 1fr; gap:12px }
.preview-meta { color:#374151; font-size:14px; display:flex; gap:16px; flex-wrap:wrap }
.preview-image { display:flex; justify-content:center; align-items:center; padding:8px; background:#f8fafc; border-radius:8px }
.preview-image img { max-width:100%; height:auto; border-radius:6px; border:1px solid #e5e7eb }

/* Hide the parent layout header for this panel (we render header inside layout already) */
:deep(.admin-main-header) {
  display: none;
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

</style>

