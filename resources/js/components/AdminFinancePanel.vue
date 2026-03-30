<template>
  <div class="admin-finance-panel">
    <!-- Filter Bar -->
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

    <!-- KPI Cards -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading financial reports...</p>
    </div>

    <div v-else class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon revenue-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="Peso">
            <text x="12" y="16" text-anchor="middle" font-size="14" fill="currentColor" font-family="Segoe UI Symbol, Noto Sans Symbols, Arial Unicode MS">₱</text>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Total Sales</span>
          <span class="kpi-value">{{ dashboardData.totalSales }}</span>
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
          <span class="kpi-value">{{ dashboardData.totalOrders }}</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon expenses-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="3" y1="9" x2="21" y2="9"></line>
            <line x1="3" y1="15" x2="21" y2="15"></line>
          </svg>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">Total Expenses</span>
          <span class="kpi-value">{{ dashboardData.totalExpenses }}</span>
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
          <span class="kpi-value">{{ dashboardData.netProfit }}</span>
        </div>
      </div>
    </div>

    <!-- Financial Reports Section -->
    <div class="panel-section">
      <h2 class="section-title">Financial Report</h2>
      <p class="section-description">Monthly financial performance</p>
      <financial-panel-content :reports="financeReports" :transactions="transactions" />
    </div>

    <!-- Recent Transactions Section -->
    <div class="panel-section">
      <h2 class="section-title">Recent Transactions</h2>
      <p class="section-description">Latest completed orders</p>

      <div v-if="transactionsLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading transactions...</p>
      </div>

      <div v-else class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order Code</th>
              <th>Branch</th>
              <th>Cashier</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Paid</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tx in transactions" :key="tx.id">
              <td><strong>{{ tx.order_code }}</strong></td>
              <td>{{ tx.branch_name }}</td>
              <td>{{ tx.cashier_name }}</td>
              <td>{{ tx.customer }}</td>
              <td>₱{{ tx.total }}</td>
              <td>₱{{ tx.paid }}</td>
              <td>{{ tx.ordered_at }}</td>
            </tr>
            <tr v-if="transactions.length === 0">
              <td colspan="7" class="empty-message">No transactions found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FinancePanelContent from './finance/FinancePanelContent.vue'
import axios from 'axios'

const selectedRange = ref('all')
const isLoading = ref(true)
const transactionsLoading = ref(false)

const dashboardData = ref({
  totalSales: '₱0',
  totalOrders: 0,
  totalExpenses: '₱0',
  netProfit: '₱0'
})

const financeReports = ref([])
const transactions = ref([])

// Helper to extract array from response
const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && Array.isArray(response?.[key])) return response[key]
  if (key && Array.isArray(response?.data?.[key])) return response.data[key]
  return []
}

// Format currency
const formatCurrency = (amount) => {
  if (!amount) return '₱0'
  const num = parseFloat(amount)
  if (isNaN(num)) return '₱0'
  return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// Load dashboard data
async function loadDashboard() {
  isLoading.value = true
  try {
    const response = await axios.get('/api/admin/finance/dashboard', {
      params: { range: selectedRange.value },
      withCredentials: true
    })

    if (response.data && response.data.ok) {
      dashboardData.value = {
        totalSales: formatCurrency(response.data.totalRevenue),
        totalOrders: response.data.totalOrders || 0,
        totalExpenses: formatCurrency(response.data.totalExpenses),
        netProfit: formatCurrency(response.data.netProfit)
      }
    }
  } catch (err) {
    console.error('Error loading dashboard:', err)
  } finally {
    isLoading.value = false
  }
}

// Load reports
async function loadReports() {
  try {
    const response = await axios.get('/api/admin/finance/reports', { withCredentials: true })
    if (response.data && response.data.ok) {
      financeReports.value = extractArray(response.data, 'reports')
    }
  } catch (err) {
    console.error('Error loading reports:', err)
  }
}

// Load transactions
async function loadTransactions() {
  transactionsLoading.value = true
  try {
    const response = await axios.get('/api/admin/finance/transactions', { withCredentials: true })
    if (response.data && response.data.ok) {
      transactions.value = extractArray(response.data, 'transactions')
    }
  } catch (err) {
    console.error('Error loading transactions:', err)
  } finally {
    transactionsLoading.value = false
  }
}

// Refresh all data
async function refreshDashboard() {
  await Promise.all([loadDashboard(), loadReports(), loadTransactions()])
}

// Handle range change
function onRangeChange() {
  loadDashboard()
}

// Initial load
onMounted(() => {
  refreshDashboard()
})
</script>

<style scoped>
.admin-finance-panel {
  margin-bottom: 32px;
}

.filter-bar {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 24px;
  padding: 18px;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid rgba(75, 42, 6, 0.04);
  box-shadow: 0 8px 24px rgba(75, 42, 6, 0.03);
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-group label {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
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
  box-shadow: 0 0 0 6px rgba(255, 122, 24, 0.06);
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

.btn-refresh:hover {
  background: #ff9f43;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #E5E7EB;
  border-top: 3px solid #ff7a18;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: #FFF6F1;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(75, 42, 6, 0.04);
  transition: all 0.18s ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
}

.kpi-card.highlight {
  border-left: 6px solid #FF9F43;
  background: #FFF8F3;
}

.kpi-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 12px;
  background: #FFF3EA;
  color: #7a3b00;
  flex-shrink: 0;
}

.kpi-content {
  display: flex;
  flex-direction: column;
}

.kpi-label {
  font-size: 13px;
  color: #7a5a44;
  margin-bottom: 4px;
}

.kpi-value {
  font-size: 22px;
  font-weight: 700;
  color: #4b2a06;
}

.panel-section {
  background: white;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.section-title {
  font-size: 18px;
  font-weight: 700;
  color: #4b2a06;
  margin: 0 0 8px 0;
}

.section-description {
  font-size: 13px;
  color: #6B7280;
  margin: 0 0 16px 0;
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
}

.data-table th {
  background: #EFF6FF;
  color: #1E3A8A;
  font-weight: 600;
  font-size: 14px;
}

.data-table td {
  color: #374151;
  font-size: 14px;
  border-bottom: 1px solid #E5E7EB;
}

.data-table tbody tr:hover {
  background: #F9FAFB;
}

.empty-message {
  text-align: center;
  color: #999;
  font-style: italic;
  padding: 20px !important;
}
</style>
