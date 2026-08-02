<template>
  <div class="finance-content">
    <div class="panel-section">
      <h2 class="section-title">Financial Overview</h2>
      <div v-if="chartLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Building analytics...</p>
      </div>
      <div v-else>
        <div v-if="!hasChartData" class="empty-message">No finance reports available.</div>
        <div v-else class="charts-container">
          <div class="chart-wrapper">
            <h3 class="chart-title">Revenue vs Expenses</h3>
            <canvas ref="chartRef"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="panel-section">
      <h2 class="section-title">Recent Transactions</h2>
      <div v-if="transactionsLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading transactions...</p>
      </div>
      <div v-else class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Cashier</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Paid</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tx in transactions" :key="tx.id">
              <td>
                {{ tx.order_code }}
                <button class="details-btn" @click="toggle(tx.id)">{{ isOpen(tx.id) ? 'Hide' : 'Details' }}</button>
              </td>
              <td>
                <div style="font-size: 0.85em">
                  <div v-if="tx.branch_name">{{ tx.branch_name }}</div>
                  <small>{{ tx.cashier_name || 'N/A' }}</small>
                </div>
              </td>
              <td>{{ tx.customer }}</td>
              <td>₱{{ tx.total }}</td>
              <td>₱{{ tx.paid }}</td>
              <td><span :class="['status-badge', getStatusClass(tx.status)]">{{ tx.status }}</span></td>
              <td>{{ tx.ordered_at || 'N/A' }}</td>
            </tr>
            <template v-for="tx in transactions" :key="tx.id + '-details'">
              <tr v-if="isOpen(tx.id)" class="tx-details-row">
                <td colspan="7">
                  <div class="tx-details">
                    <div class="items">
                      <strong>Items:</strong>
                      <ul>
                        <li v-for="item in tx.items || []" :key="item.product_id">
                          {{ item.quantity }}x {{ item.product_name }} — ₱{{ Number(item.subtotal || 0).toFixed(2) }}
                        </li>
                      </ul>
                    </div>
                    <div class="breakdown">
                      <div>Subtotal: ₱{{ Number(tx.subtotal || 0).toFixed(2) }}</div>
                      <div>Discount ({{ tx.discount_type || 'none' }}): ₱{{ Number(tx.discount_amount || 0).toFixed(2) }}</div>
                      <div>VAT ({{ tx.vat_percent || 0 }}%): ₱{{ Number(tx.vat_amount || 0).toFixed(2) }}</div>
                      <div><strong>Total: ₱{{ tx.total }}</strong></div>
                      <div>Paid: ₱{{ tx.paid }}</div>
                      <div>Change: ₱{{ Number(tx.change || 0).toFixed(2) }}</div>
                      <div v-if="tx.approved_by">Approved by: {{ tx.approved_by }} at {{ tx.approved_at }}</div>
                      <div v-if="tx.status === 'cancelled'">
                        <div>Refunded by: {{ tx.cancelled_by || 'N/A' }} at {{ tx.cancelled_at || 'N/A' }}</div>
                        <div v-if="tx.refund_reason">Refund reason: {{ tx.refund_reason }}</div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="transactions.length === 0">
              <td colspan="7" class="empty-message">No recent transactions found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  reports: {
    type: Array,
    default: () => []
  },
  transactions: {
    type: Array,
    default: () => []
  },
  transactionsLoading: {
    type: Boolean,
    default: false
  },
  chartLoading: {
    type: Boolean,
    default: false
  }
})

const openIds = ref([])
const chartRef = ref(null)
const chartInstance = ref(null)
const chartLoading = computed(() => props.chartLoading)

const transactionsLoading = computed(() => props.transactionsLoading)

// Expose transactions as a computed for proper reactivity
const transactions = computed(() => props.transactions || [])

const normalizedReports = computed(() => {
  const rows = []

  for (const r of (props.reports || [])) {
    // Support report payloads shaped like { data: { months: [], income: [], expenses: [], netProfit: [] } }.
    if (r?.data && Array.isArray(r.data.months)) {
      const months = r.data.months || []
      const income = r.data.income || r.data.revenue || []
      const expenses = r.data.expenses || []
      const netProfit = r.data.netProfit || r.data.profit || []

      months.forEach((month, idx) => {
        const revenue = toNumber(income[idx])
        const expense = toNumber(expenses[idx])
        const profit = toNumber(netProfit[idx] ?? (revenue - expense))
        rows.push({
          label: month || `Entry ${idx + 1}`,
          revenue,
          expenses: expense,
          profit
        })
      })

      continue
    }

    const label = r?.month || r?.label || r?.period || r?.date || `Entry ${rows.length + 1}`
    const revenue = toNumber(r?.revenue || r?.totalRevenue || r?.total_sales || r?.sales)
    const expenses = toNumber(r?.expenses || r?.totalExpenses || r?.total_expenses)
    const profit = toNumber(r?.netProfit || r?.profit || (revenue - expenses))
    rows.push({ label, revenue, expenses, profit })
  }

  return rows
})

const hasChartData = computed(() => normalizedReports.value.some(r => r.revenue || r.expenses || r.profit))

function getStatusClass(status) {
  switch (status?.toLowerCase()) {
    case 'completed': return 'status-approved'
    case 'pending': return 'status-pending'
    case 'cancelled': return 'status-rejected'
    default: return 'status-pending'
  }
}

function toggle(id) {
  const i = openIds.value.indexOf(id)
  if (i === -1) openIds.value.push(id)
  else openIds.value.splice(i, 1)
}

function isOpen(id) {
  return openIds.value.includes(id)
}

function toNumber(val) {
  const n = Number(val)
  return isNaN(n) ? 0 : n
}

let isDestroying = false

function renderChart() {
  // Skip rendering if component is being destroyed or ref is null
  if (isDestroying) return
  if (!chartRef.value) return

  // Ensure canvas is actually in the DOM and visible
  if (!chartRef.value.isConnected) return
  if (chartRef.value.offsetParent === null && chartRef.value.style.display !== 'none') return

  if (chartInstance.value) {
    chartInstance.value.destroy()
    chartInstance.value = null
  }
  if (!hasChartData.value) return

  const labels = normalizedReports.value.map(r => r.label)
  const revenue = normalizedReports.value.map(r => r.revenue)
  const expenses = normalizedReports.value.map(r => r.expenses)
  const profit = normalizedReports.value.map(r => r.profit)

  // Guard against a missing or detached canvas context which causes Chart.js internals to fail
  if (!chartRef.value || typeof chartRef.value.getContext !== 'function') return
  const ctx = chartRef.value.getContext('2d')
  if (!ctx) {
    // Canvas context couldn't be obtained (element detached or not ready). Skip rendering.
    return
  }

  // Additional guard: ensure canvas is still attached to DOM
  if (!chartRef.value.isConnected) {
    return
  }

  try {
    chartInstance.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Revenue', data: revenue, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.12)', tension: 0.25, fill: true, pointRadius: 5, pointHoverRadius: 7, pointBackgroundColor: '#22c55e' },
        { label: 'Expenses', data: expenses, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.12)', tension: 0.25, fill: true, pointRadius: 5, pointHoverRadius: 7, pointBackgroundColor: '#ef4444' },
        { label: 'Profit', data: profit, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.12)', tension: 0.25, fill: false, pointRadius: 5, pointHoverRadius: 7, pointBackgroundColor: '#6366f1' }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: { display: true, position: 'top' },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: '#ddd',
          borderWidth: 1,
          padding: 12,
          displayColors: true,
          callbacks: {
            title: (tooltipItems) => {
              return tooltipItems[0]?.label || 'Data'
            },
            label: (context) => {
              const label = context.dataset.label || ''
              const value = context.parsed.y || 0
              return `${label}: ₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
            },
            afterLabel: (context) => {
              if (context.datasetIndex === 2) {
                const revenue = context.chart.data.datasets[0].data[context.dataIndex] || 0
                const expenses = context.chart.data.datasets[1].data[context.dataIndex] || 0
                const profit = revenue - expenses
                return `Total: ₱${Number(profit).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
              }
              return ''
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: (v) => `₱${Number(v).toLocaleString('en-PH')}` }
        }
      }
    }
  })
  } catch (e) {
    // Prevent Chart.js drawing errors from bubbling to global handlers
    // Log to console for debugging
    console.error('Chart rendering failed:', e)
  }
}

watch(normalizedReports, () => {
  // Only render if canvas is available and component is not being destroyed
  if (!isDestroying && chartRef.value && chartRef.value.isConnected) {
    renderChart()
  }
})

onMounted(() => {
  renderChart()
})

onUnmounted(() => {
  isDestroying = true
  if (chartInstance.value) {
    try {
      chartInstance.value.destroy()
    } catch (e) {
      // Ignore errors during cleanup
    }
    chartInstance.value = null
  }
  chartRef.value = null
})
</script>

<style scoped>
:global(.dark-mode) .finance-content,
:global(.dark-mode) .panel-section,
:global(.dark-mode) .chart-wrapper,
:global(.dark-mode) .tx-details-row,
:global(.dark-mode) .data-table th,
:global(.dark-mode) .data-table td,
:global(.dark-mode) .loading-container,
:global(.dark-mode) .table-container {
  background: linear-gradient(180deg, rgba(17, 24, 39, 0.98), rgba(15, 23, 42, 0.98));
  border-color: rgba(148, 163, 184, 0.18);
  color: #f8fafc;
}

:global(.dark-mode) .section-title,
:global(.dark-mode) .chart-title,
:global(.dark-mode) .data-table th,
:global(.dark-mode) .data-table td,
:global(.dark-mode) .details-btn,
:global(.dark-mode) .empty-message,
:global(.dark-mode) .loading-container p,
:global(.dark-mode) .breakdown,
:global(.dark-mode) .tx-details,
:global(.dark-mode) .items,
:global(.dark-mode) .items li,
:global(.dark-mode) .chart-legend small {
  color: #f8fafc;
}

:global(.dark-mode) .panel-section .section-title,
:global(.dark-mode) .panel-section .chart-title {
  color: #f8fafc;
}

:global(.dark-mode) .data-table th {
  background: linear-gradient(180deg, #1e293b 0%, #162032 100%);
}

:global(.dark-mode) .chart-wrapper {
  border-color: rgba(148, 163, 184, 0.18);
}

:global(.dark-mode) .data-table td,
:global(.dark-mode) .data-table th {
  border-bottom-color: rgba(148, 163, 184, 0.18);
}

:global(.dark-mode) .status-badge.status-approved {
  background: rgba(16, 185, 129, 0.16);
  color: #a7f3d0;
}

:global(.dark-mode) .status-badge.status-pending {
  background: rgba(245, 158, 11, 0.18);
  color: #fde68a;
}

:global(.dark-mode) .status-badge.status-rejected {
  background: rgba(248, 113, 113, 0.18);
  color: #fecaca;
}

:global(.dark-mode) .details-btn {
  background: #2563eb;
}

.finance-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.panel-section {
  background: white;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #1F2937;
  margin: 0 0 16px 0;
}

.chart-title {
  font-size: 16px;
  font-weight: 600;
  color: #1F2937;
  margin: 0 0 12px 0;
  text-align: center;
}

.charts-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 24px;
}

.chart-wrapper {
  background: #F8FAFC;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  min-height: 320px;
}

.chart-wrapper canvas {
  height: 300px !important;
  max-height: 350px;
}

.chart-legend {
  text-align: center;
  margin-top: 16px;
  padding: 12px;
  background: #F1F5F9;
  border-radius: 8px;
}

.chart-legend small {
  color: #64748B;
  font-size: 14px;
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
  border-bottom: 1px solid #E5E7EB;
}

.data-table th {
  background: #F9FAFB;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-approved {
  background: #D1FAE5;
  color: #065F46;
}

.status-pending {
  background: #FEF3C7;
  color: #92400E;
}

.status-rejected {
  background: #FEE2E2;
  color: #991B1B;
}

.details-btn {
  margin-left: 8px;
  padding: 4px 8px;
  background: #3B82F6;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
}

.details-btn:hover {
  background: #2563EB;
}

.tx-details-row {
  background: #F8FAFC;
}

.tx-details {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.items ul {
  margin: 8px 0;
  padding-left: 20px;
}

.breakdown {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 14px;
}

.empty-message {
  text-align: center;
  color: #9CA3AF;
  font-style: italic;
  padding: 40px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #E5E7EB;
  border-top: 3px solid #3B82F6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .charts-container {
    grid-template-columns: 1fr;
  }

  .chart-wrapper canvas {
    height: 250px !important;
  }
}
</style>

