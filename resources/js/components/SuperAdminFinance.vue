<template>
  <div class="superadmin-finance">
    <!-- Back to Dashboard Button -->
    <button @click="router.push('/super-admin-panel')" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Super Admin
    </button>

    <!-- Header -->
    <div class="page-header">
      <h1 class="page-title">Finance Dashboard</h1>
      <p class="page-subtitle">Monitor financial performance across all branches</p>
    </div>

    <!-- Date Range Filter -->
    <div class="filter-bar">
      <div class="filter-group">
        <label>Date Range:</label>
        <select v-model="selectedRange" @change="fetchDashboard">
          <option value="today">Today</option>
          <option value="yesterday">Yesterday</option>
          <option value="thisWeek">This Week</option>
          <option value="thisMonth">This Month</option>
          <option value="lastMonth">Last Month</option>
          <option value="all">All Time</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Branch:</label>
        <select v-model="selectedBranch" @change="fetchDashboard">
          <option :value="null">All Branches</option>
          <option v-for="branch in branches" :key="branch.id" :value="branch.id">
            {{ branch.name }}
          </option>
        </select>
      </div>
      <button class="btn-refresh" @click="fetchDashboard">Refresh</button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading finance data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <p class="error-message">{{ error }}</p>
      <button class="btn-retry" @click="fetchDashboard">Retry</button>
    </div>

    <!-- Finance KPIs -->
    <div v-else class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon revenue-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Total Revenue</span>
          <span class="kpi-value">{{ formatCurrency(dashboard.total_revenue) }}</span>
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
          <span class="kpi-value">{{ dashboard.total_orders }}</span>
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
          <span class="kpi-label">Total Expenses</span>
          <span class="kpi-value">{{ formatCurrency(dashboard.total_expenses) }}</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon refunds-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="1 4 1 10 7 10"></polyline>
            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Total Refunds</span>
          <span class="kpi-value">{{ formatCurrency(dashboard.total_refunds) }}</span>
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
          <span class="kpi-label">Net Profit</span>
          <span class="kpi-value">{{ formatCurrency(dashboard.total_net_profit) }}</span>
        </div>
      </div>
    </div>

    <!-- Branch Stats -->
    <div class="branch-stats" v-if="!loading && !error && branchStats.length > 0">
      <h2 class="section-title">Branch Performance</h2>
      <div class="branch-table-container">
        <table class="branch-table">
          <thead>
            <tr>
              <th>Branch</th>
              <th>Total Sales</th>
              <th>Total Orders</th>
              <th>Total Refunds</th>
              <th>Net Profit</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="branch in branchStats" :key="branch.branch_id">
              <td>{{ branch.branch_name }}</td>
              <td>{{ formatCurrency(branch.total_sales) }}</td>
              <td>{{ branch.total_orders }}</td>
              <td>{{ formatCurrency(branch.total_refunds) }}</td>
              <td :class="branch.net_profit >= 0 ? 'profit-positive' : 'profit-negative'">
                {{ formatCurrency(branch.net_profit) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="recent-transactions" v-if="!loading && !error && recentTransactions.length > 0">
      <h2 class="section-title">Recent Transactions</h2>
      <div class="transactions-table-container">
        <table class="transactions-table">
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Branch</th>
              <th>Order Code</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tx in recentTransactions" :key="tx.transaction_id">
              <td>{{ tx.transaction_id }}</td>
              <td>{{ tx.branch_name }}</td>
              <td>{{ tx.order_code }}</td>
              <td>{{ formatCurrency(tx.amount) }}</td>
              <td>
                <span :class="['status-badge', 'status-' + tx.status]">{{ tx.status }}</span>
              </td>
              <td>{{ formatDate(tx.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

// Router
const router = useRouter()

// State
const loading = ref(true)
const error = ref(null)
const selectedRange = ref('today')
const selectedBranch = ref(null)
const branches = ref([])
const dashboard = ref({
  total_revenue: 0,
  total_orders: 0,
  total_expenses: 0,
  total_refunds: 0,
  total_net_profit: 0,
  total_branches: 0,
  currency: 'PHP',
  date_range: 'today'
})
const branchStats = ref([])
const recentTransactions = ref([])

// Methods
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '₱0.00'
  return '₱' + parseFloat(value).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchDashboard = async () => {
  loading.value = true
  error.value = null

  try {
    // Build params with both range and branch
    const params = { range: selectedRange.value }
    if (selectedBranch.value) {
      params.branch_id = selectedBranch.value
    }

    const response = await axios.get('/api/superadmin/finance/dashboard', {
      params,
      withCredentials: true
    })

    if (response.data.ok) {
      dashboard.value = response.data.dashboard
      recentTransactions.value = response.data.recent_transactions || []

      // Fetch branch stats with the same filters
      const branchResponse = await axios.get('/api/superadmin/finance/branches', {
        params,
        withCredentials: true
      })

      if (branchResponse.data.ok) {
        branchStats.value = branchResponse.data.branches || []
      }
    }
  } catch (err) {
    console.error('Error fetching finance dashboard:', err)
    error.value = err.response?.data?.message || 'Failed to load finance data'
  } finally {
    loading.value = false
  }
}

const fetchBranches = async () => {
  try {
    const response = await axios.get('/api/superadmin/branches', {
      withCredentials: true
    })
    if (response.data && response.data.ok && response.data.branches) {
      branches.value = response.data.branches
    }
  } catch (err) {
    console.error('Error fetching branches:', err)
    // Fallback: try the finance branches endpoint
    try {
      const fallbackRes = await axios.get('/api/superadmin/finance/branches', {
        params: { range: 'all' },
        withCredentials: true
      })
      if (fallbackRes.data && fallbackRes.data.ok && fallbackRes.data.branches) {
        branches.value = fallbackRes.data.branches
      }
    } catch (fallbackErr) {
      console.error('Fallback also failed:', fallbackErr)
    }
  }
}

// Lifecycle
onMounted(async () => {
  await fetchBranches()
  await fetchDashboard()
})
</script>

<style scoped>
.superadmin-finance {
  padding: 24px;
  background: linear-gradient(135deg, #FF9A4A 0%, #FF6A3D 100%);
  min-height: 100vh;
}

.page-header {
  margin-bottom: 24px;
}

/* Button Styles */
.btn-primary, .btn-success, .btn-secondary, .btn-info, .btn-danger {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #ff9f43;
  color: #fff;
}

.btn-primary:hover {
  background: #fabd83;
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background: #5a6268;
}

/* Back to Dashboard Button */
.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}

.back-icon {
  flex-shrink: 0;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #fff4e6;
  margin: 0 0 8px 0;
}

.page-subtitle {
  color: rgba(255, 244, 230, 0.88);
  margin: 0;
  font-size: 14px;
}

.filter-bar {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 24px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.18);
  -webkit-backdrop-filter: blur(12px);
  backdrop-filter: blur(12px);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.25);
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-group label {
  font-weight: 500;
  color: #fff4e6;
}

.filter-group select {
  padding: 8px 12px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  font-size: 14px;
  background: rgba(255, 255, 255, 0.9);
  color: #5a2c0a;
  cursor: pointer;
}

.filter-group select:focus {
  outline: none;
  border-color: #ff9f43;
  box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.25);
}

.btn-refresh {
  padding: 8px 16px;
  background: #ff9f43;
  color: #5a2c0a;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
}

.btn-refresh:hover {
  background: #ffb366;
  transform: translateY(-1px);
}

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: rgba(255, 255, 255, 0.18);
  -webkit-backdrop-filter: blur(12px);
  backdrop-filter: blur(12px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.25);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top: 3px solid #ff9f43;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  color: #ffcccc;
  margin-bottom: 16px;
}

.btn-retry {
  padding: 8px 20px;
  background: #ff6b6b;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
}

.btn-retry:hover {
  background: #ff5252;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.kpi-card.highlight {
  background: linear-gradient(135deg, #ff9f43 0%, #ff6b1c 100%);
}

.kpi-card.highlight .kpi-label,
.kpi-card.highlight .kpi-value {
  color: white;
}

.kpi-card.highlight .kpi-icon {
  background: rgba(255, 255, 255, 0.25);
  color: white;
}

.kpi-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  color: white;
}

.revenue-icon {
  background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
}

.orders-icon {
  background: linear-gradient(135deg, #ff9f43 0%, #ff6b1c 100%);
}

.expenses-icon {
  background: linear-gradient(135deg, #ffc107 0%, #ffcd39 100%);
}

.refunds-icon {
  background: linear-gradient(135deg, #dc3545 0%, #e4606d 100%);
}

.profit-icon {
  background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
}

.kpi-content {
  display: flex;
  flex-direction: column;
}

.kpi-label {
  font-size: 13px;
  color: #5a2c0a;
  margin-bottom: 4px;
}

.kpi-value {
  font-size: 22px;
  font-weight: 700;
  color: #4b2a06;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #fff4e6;
  margin: 0 0 16px 0;
}

.branch-stats,
.recent-transactions {
  background: rgba(255, 255, 255, 0.18);
  -webkit-backdrop-filter: blur(12px);
  backdrop-filter: blur(12px);
  padding: 24px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  margin-bottom: 24px;
}

.branch-table-container,
.transactions-table-container {
  overflow-x: auto;
}

.branch-table,
.transactions-table {
  width: 100%;
  border-collapse: collapse;
}

.branch-table th,
.branch-table td,
.transactions-table th,
.transactions-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.branch-table th,
.transactions-table th {
  background: rgba(255, 159, 67, 0.2);
  font-weight: 600;
  color: #5a2c0a;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.branch-table td,
.transactions-table td {
  color: #fff4e6;
  font-size: 14px;
}

.profit-positive {
  color: #2ecc71;
  font-weight: 600;
}

.profit-negative {
  color: #ff6b6b;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-completed {
  background: rgba(46, 204, 113, 0.2);
  color: #2ecc71;
}

.status-pending {
  background: rgba(241, 196, 15, 0.2);
  color: #f1c40f;
}

.status-cancelled {
  background: rgba(231, 76, 60, 0.2);
  color: #e74c3c;
}

.status-in_kitchen {
  background: rgba(52, 152, 219, 0.2);
  color: #3498db;
}
</style>

