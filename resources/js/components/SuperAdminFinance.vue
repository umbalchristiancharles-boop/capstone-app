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
const selectedRange = ref('all')
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
      dashboard.value = response.data.dashboard || {}
      // tolerate both snake_case and camelCase keys for transactions
      recentTransactions.value = response.data.recent_transactions || response.data.recentTransactions || []

      // Validate dashboard data - ensure no negative values except refunds
      if (dashboard.value.total_revenue < 0) {
        console.warn('Warning: Total revenue is negative, this may indicate data inconsistency')
      }
      if (dashboard.value.total_net_profit === undefined) {
        dashboard.value.total_net_profit = dashboard.value.total_revenue - (dashboard.value.total_expenses || 0) - (dashboard.value.total_refunds || 0)
      }

      // Fetch branch stats with the same filters
      const branchResponse = await axios.get('/api/superadmin/finance/branches', {
        params,
        withCredentials: true
      })

      if (branchResponse.data.ok) {
        // Normalize branches to { id, name } shape expected by the select control
        const raw = branchResponse.data.branches || []
        branchStats.value = raw
        branches.value = raw.map(b => {
          // support both shapes: { id, name } and { branch_id, branch_name }
          if (b.id && b.name) return { id: b.id, name: b.name }
          if (b.branch_id && b.branch_name) return { id: b.branch_id, name: b.branch_name }
          // fallback: try common keys
          return { id: b.branch_id || b.id || null, name: b.branch_name || b.name || b.code || 'Unknown' }
        }).filter(b => b.id !== null)

        // Validate branch stats - warn if totals don't match
        if (branchStats.value.length > 0) {
          const branchSum = branchStats.value.reduce((sum, b) => sum + (b.total_sales || 0), 0)
          if (Math.abs(branchSum - (dashboard.value.total_revenue || 0)) > 0.01) {
            console.warn('Warning: Branch totals do not match dashboard total revenue')
          }
        }
      }
    } else {
      error.value = response.data.message || 'Failed to load finance data'
    }
  } catch (err) {
    console.error('Error fetching finance dashboard:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load finance data'
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

<style>
.superadmin-finance {
  background: #e7d9cf;
  padding: 30px;
  min-height: 100vh;
  color: #3d2a1f;
  font-family: Poppins, Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
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
  background: #1f2937;
  color: #fff;
}

.btn-primary:hover {
  background: #374151;
}

.btn-secondary {
  background: #64748b;
  color: #ffffff;
  border: 1px solid #64748b;
  border-radius: 8px;
  padding: 8px 16px;
  font-weight: 600;
  transition: background 0.3s ease;
}

.btn-secondary:hover {
  background: #475569;
}

/* Back to Dashboard Button */
.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 8px 16px;
  font-size: 0.9rem;
}

.back-icon {
  flex-shrink: 0;
}

h1, h2 {
  color: #3d2a1f !important;
  font-weight: 800 !important;
  font-family: 'Inter', 'Poppins', sans-serif !important;
  letter-spacing: -0.5px !important;
  margin-bottom: 8px !important;
}

.admin-label, .metric-label, .overview-label, .branch-count, .kpi-label, .section-title {
  color: rgba(66, 33, 11, 0.9) !important;
}

.avatar-change-text {
  color: #c25a12 !important;
}

.btn-primary {
  background: #1f2937 !important;
  color: white !important;
}

.btn-primary:hover {
  background: #374151 !important;
}

.btn-secondary, .btn-outline {
  background: #64748b;
  color: #ffffff;
  border: 1px solid #64748b;
}

.btn-secondary:hover, .btn-outline:hover {
  background: #475569;
}

.page-title {
  font-size: 26px;
  line-height: 1.1;
  font-weight: 800;
  color: #3d2a1f;
  margin: 0 0 8px 0;
}

.page-subtitle {
  color: #64748b;
  margin: 0;
  font-size: 14px;
}

.filter-bar {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 24px;
  padding: 20px;
  background: #fffaf5;
  border-radius: 12px;
  border: 1px solid #f1e5d8;
  box-shadow: 0 4px 14px #eadfd5;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-group label {
  font-weight: 500;
  color: #3d2a1f;
}

.filter-group select {
  padding: 8px 12px;
  border: 1px solid #d8c8bc;
  border-radius: 8px;
  font-size: 14px;
  background: #ffffff;
  color: #3d2a1f;
  cursor: pointer;
}

.filter-group select:focus {
  outline: none;
  border-color: #c25a12;
  box-shadow: 0 0 0 3px #f8dfcc;
}

.btn-refresh {
  background: #1f2937;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-refresh:hover {
  background: #374151;
}

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: #fffaf5;
  border-radius: 12px;
  border: 1px solid #f1e5d8;
  box-shadow: 0 4px 14px #eadfd5;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f1e5d8;
  border-top: 3px solid #c25a12;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  color: var(--alert);
  margin-bottom: 16px;
}

.btn-retry {
  background: #b42318;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-retry:hover {
  background: #912018;
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
  background: #fffaf5;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(16,24,40,0.04);
  border: 1px solid #f1e5d8;
  transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px #eadfd5;
}

.kpi-card.highlight {
  border-left: 4px solid #f59e0b;
  background: #fffaf5;
}

.kpi-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #f1f5f9;
  color: #3d2a1f;
}



.kpi-content {
  display: flex;
  flex-direction: column;
}

.kpi-label {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 4px;
}

.kpi-value {
  font-size: 22px;
  font-weight: 700;
  color: #3d2a1f;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #3d2a1f;
  margin: 0 0 16px 0;
}

.branch-stats,
.recent-transactions {
  background: #fffaf5;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #f1e5d8;
  box-shadow: 0 4px 14px #eadfd5;
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
}

.branch-table th,
.transactions-table th {
  background: #fff4e8;
  color: #3d2a1f;
  font-weight: 600;
  padding: 12px 16px;
  font-size: 14px;
}

.branch-table td,
.transactions-table td {
  color: #3d2a1f;
  font-size: 14px;
  border-bottom: 1px solid #eadfd5;
}

.profit-positive {
  color: #0f766e;
  font-weight: 600;
}

.profit-negative {
  color: #b42318;
  font-weight: 600;
}

.status-badge {
  background: #fef3c7;
  color: #92400e;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

</style>

