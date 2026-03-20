<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Finance Manager Panel'"
    :panelDescription="'View financial reports, manage budget requests, and analyze revenue for your branch.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <!-- Overview Cards -->
      <div class="overview-grid">
        <div class="overview-card">
          <span class="overview-label">Total Sales:</span>
          <span class="overview-value">{{ dashboardTotals.totalSales }}</span>
        </div>
        <div class="overview-card">
          <span class="overview-label">Pending Approvals:</span>
          <span class="overview-value">{{ pendingBudgetCount }}</span>
        </div>
        <div class="overview-card">
          <span class="overview-label">Revenue:</span>
          <span class="overview-value">{{ dashboardTotals.revenue }}</span>
        </div>
      </div>

      <!-- Budget Requests Section -->
      <div class="panel-section">
        <h2 class="section-title">Budget Request Approvals</h2>
        <p class="section-description">Review and approve/reject budget requests from Logistics Managers</p>

        <div v-if="budgetLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading budget requests...</p>
        </div>

        <div v-else class="table-container">
          <table class="data-table">
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
                  <span :class="['status-badge', getStatusClass(req.status)]">
                    {{ req.status }}
                  </span>
                </td>
                <td>
                  <div v-if="req.status === 'Pending'" class="action-buttons">
                    <button
                      class="btn-approve"
                      @click="approveRequest(req.id)"
                      :disabled="processingId === req.id"
                    >
                      {{ processingId === req.id ? 'Processing...' : 'Approve' }}
                    </button>
                    <button
                      class="btn-reject"
                      @click="rejectRequest(req.id)"
                      :disabled="processingId === req.id"
                    >
                      {{ processingId === req.id ? 'Processing...' : 'Reject' }}
                    </button>
                  </div>
                  <div v-else-if="req.status === 'Approved' && req.purpose && /Procurement Request #\d+/i.test(req.purpose)">
                    <button
                      class="btn-approve"
                      @click="markBudgetGiven(req.id)"
                      :disabled="processingId === req.id"
                    >
                      {{ processingId === req.id ? 'Processing...' : 'Budget Given' }}
                    </button>
                    <span style="margin-left:8px; color:#666">Approved</span>
                  </div>
                  <span v-else class="processed-info">
                    {{ req.status }} by {{ req.processed_by || 'Unknown' }}
                    <br>
                    <small>{{ formatDate(req.date_processed) }}</small>
                  </span>
                </td>
              </tr>
              <tr v-if="budgetRequests.length === 0">
                <td colspan="6" class="empty-message">No budget requests found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Finance Reports Section -->
      <finance-panel-content :reports="financeReports" :transactions="transactions" />
    </template>
  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
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
import { ref, onMounted, computed } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import FinancePanelContent from './finance/FinancePanelContent.vue'
import axios from 'axios'

// Logo image
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

// Helper function to safely extract array from response
const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && response?.[key]?.data) return response[key].data
  return []
}

const userProfile = ref({})
const dashboardTotals = ref({
  totalSales: '₱0',
  pendingApprovals: 0,
  revenue: '₱0'
})
const financeReports = ref([])
const transactions = ref([])

// Budget requests state
const budgetRequests = ref([])
const budgetLoading = ref(true)
const processingId = ref(null)

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

// Approve budget request
async function approveRequest(id) {
  if (processingId.value) return

  if (!confirm('Are you sure you want to approve this budget request?')) {
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

  if (!confirm('Are you sure you want to reject this budget request?')) {
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

onMounted(async () => {
  try {
    // Fetch user profile
    const res = await axios.get('/api/manager/finance/profile', { withCredentials: true })
    userProfile.value = res.data.user

    // Fetch dashboard data
    const dash = await axios.get('/api/manager/finance/dashboard', { withCredentials: true })
    dashboardTotals.value = {
      totalSales: dash.data.totalRevenue ? '₱' + Number(dash.data.totalRevenue).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      pendingApprovals: dash.data.pendingApprovals || 0,
      revenue: dash.data.netProfit ? '₱' + Number(dash.data.netProfit).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0'
    }

    // Fetch finance reports and transactions
    const reports = await axios.get('/api/manager/finance/reports', { withCredentials: true })
    financeReports.value = extractArray(reports.data, 'reports')
    const tx = await axios.get('/api/manager/finance/transactions', { withCredentials: true })
    transactions.value = extractArray(tx.data, 'transactions')

    // Fetch budget requests
    await fetchBudgetRequests()
  } catch (err) {
    console.error('Error loading data:', err)
  }
})

// Mark budget as given by finance (handed to procurement)
async function markBudgetGiven(id) {
  if (processingId.value) return
  if (!confirm('Confirm you have handed the budget to procurement?')) return
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
          budgetRequests.value[idx].status = br.status || budgetRequests.value[idx].status
          budgetRequests.value[idx].processed_by = br.processed_by || budgetRequests.value[idx].processed_by
          budgetRequests.value[idx].date_processed = br.date_processed || budgetRequests.value[idx].date_processed
        } else {
          // fallback: mark as Budget Given
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
.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.overview-card {
  background: rgba(255, 255, 255, 0.95);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.overview-label {
  display: block;
  font-size: 13px;
  color: #666;
  margin-bottom: 4px;
}

.overview-value {
  font-size: 24px;
  font-weight: 700;
  color: #4b2a06;
}

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

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-approve {
  padding: 6px 14px;
  background: #27ae60;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-approve:hover:not(:disabled) {
  background: #219a52;
}

.btn-approve:disabled {
  background: #95a5a6;
  cursor: not-allowed;
}

.btn-reject {
  padding: 6px 14px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-reject:hover:not(:disabled) {
  background: #c0392b;
}

.btn-reject:disabled {
  background: #95a5a6;
  cursor: not-allowed;
}

.processed-info {
  font-size: 13px;
  color: #666;
}

.processed-info small {
  color: #999;
}

/* Logout Confirm */
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

