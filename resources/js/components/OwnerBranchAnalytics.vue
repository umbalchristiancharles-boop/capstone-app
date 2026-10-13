<template>
  <section class="branch-analytics" aria-labelledby="branch-analytics-title">
    <div class="branch-analytics__overview">
      <header class="branch-analytics__header">
        <div>
          <p class="branch-analytics__eyebrow">Multi-branch performance</p>
          <h2 id="branch-analytics-title">Branch Analytics</h2>
        </div>
        <label class="branch-analytics__filter">
          <span>Period</span>
          <select v-model="range" @change="loadAnalytics">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="thisWeek">This week</option>
            <option value="thisMonth">This month</option>
            <option value="lastMonth">Last month</option>
            <option value="all">All time</option>
          </select>
        </label>
      </header>

      <p v-if="errorMessage" class="branch-analytics__notice branch-analytics__notice--error" role="alert">{{ errorMessage }}</p>
      <p v-else-if="loading" class="branch-analytics__notice" role="status">Loading branch analytics...</p>
      <div v-else class="branch-analytics__summary" aria-label="Combined branch totals">
        <article class="branch-analytics__stat branch-analytics__stat--sales">
          <span>Total sales</span><strong>{{ formatCurrency(totals.total_sales) }}</strong>
          <small>{{ formatNumber(totals.total_orders) }} orders across all statuses</small>
        </article>
        <article class="branch-analytics__stat">
          <span>Completed orders</span><strong>{{ formatNumber(totals.completed_orders) }}</strong>
          <small>{{ formatPercent(totals.completion_rate) }} completion rate</small>
        </article>
        <article class="branch-analytics__stat">
          <span>Expenses</span><strong>{{ formatCurrency(totals.total_expenses) }}</strong>
          <small>{{ formatNumber(totals.expense_count) }} approved expense entries</small>
        </article>
        <article class="branch-analytics__stat branch-analytics__stat--profit">
          <span>Net profit</span><strong>{{ formatCurrency(totals.net_profit) }}</strong>
          <small>{{ formatPercent(totals.profit_margin) }} profit margin</small>
        </article>
        <article class="branch-analytics__stat branch-analytics__stat--orders">
          <span>Average order value</span><strong>{{ formatCurrency(totals.average_order_value) }}</strong>
          <small>{{ formatNumber(totals.pending_orders) }} pending orders</small>
        </article>
        <article class="branch-analytics__stat branch-analytics__stat--refunds">
          <span>Refunds</span><strong>{{ formatCurrency(totals.total_refunds) }}</strong>
          <small>{{ formatPercent(totals.refund_rate) }} of all orders</small>
        </article>
      </div>
    </div>

    <template v-if="!loading && !errorMessage">
      <div class="branch-analytics__insights">
        <article class="branch-analytics__insight">
          <span class="branch-analytics__insight-label">Top sales branch</span>
          <strong>{{ highlights.top_sales_branch?.name || 'No sales yet' }}</strong>
          <small>{{ highlights.top_sales_branch ? formatCurrency(highlights.top_sales_branch.value) : 'Complete an order to see a leader' }}</small>
        </article>
        <article class="branch-analytics__insight">
          <span class="branch-analytics__insight-label">Top profit branch</span>
          <strong>{{ highlights.top_profit_branch?.name || 'No profit yet' }}</strong>
          <small>{{ highlights.top_profit_branch ? formatCurrency(highlights.top_profit_branch.value) : 'No profit recorded in this period' }}</small>
        </article>
      </div>

      <div class="branch-analytics__comparison">
        <div class="branch-analytics__section-heading">
          <div>
            <h3>Sales comparison</h3>
            <p>Completed sales by branch for the selected period.</p>
          </div>
          <span>{{ formatNumber(branches.length) }} branches</span>
        </div>
        <div v-if="branches.length" class="branch-analytics__bars" aria-label="Sales comparison by branch">
          <div v-for="branch in rankedBranches" :key="`bar-${branch.branch_id}`" class="branch-analytics__bar-row">
            <span class="branch-analytics__bar-label">{{ branch.branch_name }}</span>
            <div class="branch-analytics__bar-track">
              <span class="branch-analytics__bar-fill" :style="{ width: `${salesWidth(branch.total_sales)}%` }"></span>
            </div>
            <strong>{{ formatCurrency(branch.total_sales) }}</strong>
          </div>
        </div>
        <p v-else class="branch-analytics__empty">No branch sales to compare for this period.</p>
      </div>

      <div class="branch-analytics__table-wrap">
        <table class="branch-analytics__table">
          <thead>
            <tr>
              <th scope="col">Branch</th>
              <th scope="col">Status</th>
              <th scope="col">Sales</th>
              <th scope="col">Orders</th>
              <th scope="col">Avg. order</th>
              <th scope="col">Completion</th>
              <th scope="col">Expenses</th>
              <th scope="col">Refunds</th>
              <th scope="col">Margin</th>
              <th scope="col">Net profit</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="branch in branches" :key="branch.branch_id">
              <th scope="row">
                <span class="branch-analytics__branch-name">{{ branch.branch_name }}</span>
                <small v-if="branch.branch_code">{{ branch.branch_code }}</small>
              </th>
              <td><span class="branch-analytics__status" :class="branch.is_active ? 'is-active' : 'is-inactive'">{{ branch.is_active ? 'Active' : 'Inactive' }}</span></td>
              <td>{{ formatCurrency(branch.total_sales) }}</td>
              <td>
                <strong>{{ formatNumber(branch.completed_orders) }}</strong>
                <small class="branch-analytics__table-subtext">{{ formatNumber(branch.pending_orders) }} pending · {{ formatNumber(branch.cancelled_orders) }} cancelled</small>
              </td>
              <td>{{ formatCurrency(branch.average_order_value) }}</td>
              <td>{{ formatPercent(branch.completion_rate) }}</td>
              <td>{{ formatCurrency(branch.total_expenses) }}</td>
              <td>{{ formatCurrency(branch.total_refunds) }}</td>
              <td>{{ formatPercent(branch.profit_margin) }}</td>
              <td class="branch-analytics__profit">{{ formatCurrency(branch.net_profit) }}</td>
            </tr>
            <tr v-if="branches.length === 0">
              <td colspan="10" class="branch-analytics__empty">No non-main branches found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

const range = ref('thisMonth')
const loading = ref(false)
const errorMessage = ref('')
const branches = ref([])
const totals = ref({
  total_sales: 0, total_orders: 0, completed_orders: 0, pending_orders: 0, cancelled_orders: 0,
  total_expenses: 0, expense_count: 0, total_refunds: 0, average_order_value: 0,
  completion_rate: 0, refund_rate: 0, net_profit: 0, profit_margin: 0,
})
const highlights = ref({ top_sales_branch: null, top_profit_branch: null })

const rankedBranches = computed(() => [...branches.value].sort((a, b) => Number(b.total_sales || 0) - Number(a.total_sales || 0)))

function formatCurrency(value) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(value || 0))
}

function formatNumber(value) {
  return new Intl.NumberFormat('en-PH').format(Number(value || 0))
}

function formatPercent(value) {
  return `${Number(value || 0).toFixed(1)}%`
}

function salesWidth(value) {
  const highestSales = Math.max(...branches.value.map((branch) => Number(branch.total_sales || 0)), 0)
  return highestSales > 0 ? Math.max((Number(value || 0) / highestSales) * 100, 2) : 0
}

async function loadAnalytics() {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.get('/api/owner/branch-analytics', {
      params: { range: range.value },
      withCredentials: true,
    })
    branches.value = response.data?.branches || []
    totals.value = response.data?.totals || totals.value
    highlights.value = response.data?.highlights || highlights.value
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || 'Unable to load branch analytics.'
  } finally {
    loading.value = false
  }
}

onMounted(loadAnalytics)
</script>

<style scoped>
.branch-analytics { display: grid; gap: 16px; color: #26354a; }
.branch-analytics__overview { position: relative; overflow: hidden; padding: 22px; border: 1px solid #ffe4cc; border-radius: 22px; background: linear-gradient(135deg, #ffffff 0%, #fff8f3 60%, #fff1e6 100%); box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.05), 0 24px 60px -18px rgba(15, 23, 42, 0.18); }
.branch-analytics__overview::before { content: ''; position: absolute; inset: 0 0 auto; height: 3px; background: linear-gradient(90deg, #f97316, #fb923c, #fbbf24); }
.branch-analytics__header { display: flex; justify-content: space-between; align-items: end; gap: 20px; margin-bottom: 18px; }
.branch-analytics__eyebrow { margin: 0 0 5px; color: #a94b22; font-size: 12px; font-weight: 700; text-transform: uppercase; }
.branch-analytics h2 { margin: 0; color: #172a43; font-size: 25px; }
.branch-analytics__filter { display: grid; gap: 5px; min-width: 150px; color: #526176; font-size: 12px; font-weight: 700; }
.branch-analytics__filter select { min-height: 40px; padding: 0 32px 0 11px; border: 1px solid #e7d9cf; border-radius: 10px; background: #fff; color: #26354a; font: inherit; }
.branch-analytics__summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.branch-analytics__stat { display: grid; gap: 8px; padding: 15px; border: 1px solid #f0e4da; border-top: 3px solid #527d83; border-radius: 12px; background: rgba(255, 255, 255, 0.88); }
.branch-analytics__stat--sales { border-top-color: #da6b36; }
.branch-analytics__stat--profit { border-top-color: #4c8a65; }
.branch-analytics__stat--orders { border-top-color: #6d72b8; }
.branch-analytics__stat--refunds { border-top-color: #c87575; }
.branch-analytics__stat span { color: #667386; font-size: 12px; font-weight: 600; }
.branch-analytics__stat strong { color: #1d3048; font-size: 20px; }
.branch-analytics__stat small { color: #7b8795; font-size: 11px; }
.branch-analytics__insights { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.branch-analytics__insight { display: grid; gap: 5px; padding: 16px 18px; border: 1px solid #e7e9ed; border-radius: 12px; background: #fff; }
.branch-analytics__insight-label { color: #7b8795; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.branch-analytics__insight strong { overflow: hidden; color: #1d3048; font-size: 16px; text-overflow: ellipsis; white-space: nowrap; }
.branch-analytics__insight small { color: #527d83; font-size: 12px; font-weight: 700; }
.branch-analytics__comparison { padding: 18px; border: 1px solid #e4e7eb; border-radius: 12px; background: #fff; }
.branch-analytics__section-heading { display: flex; align-items: start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.branch-analytics__section-heading h3 { margin: 0; color: #1d3048; font-size: 16px; }
.branch-analytics__section-heading p { margin: 4px 0 0; color: #7b8795; font-size: 12px; }
.branch-analytics__section-heading > span { color: #7b8795; font-size: 12px; font-weight: 700; }
.branch-analytics__bars { display: grid; gap: 13px; }
.branch-analytics__bar-row { display: grid; grid-template-columns: minmax(120px, 0.7fr) minmax(120px, 2fr) minmax(100px, 0.65fr); align-items: center; gap: 12px; }
.branch-analytics__bar-label { overflow: hidden; color: #526176; font-size: 12px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
.branch-analytics__bar-track { height: 9px; overflow: hidden; border-radius: 999px; background: #f1f3f5; }
.branch-analytics__bar-fill { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #f97316, #fbbf24); }
.branch-analytics__bar-row strong { color: #1d3048; font-size: 12px; text-align: right; }
.branch-analytics__table-wrap { overflow-x: auto; border: 1px solid #e4e7eb; border-radius: 12px; background: #fff; }
.branch-analytics__table { width: 100%; border-collapse: collapse; white-space: nowrap; }
.branch-analytics__table th, .branch-analytics__table td { padding: 13px 14px; border-bottom: 1px solid #edf0f2; text-align: right; font-size: 13px; }
.branch-analytics__table thead th { background: #f5f7f8; color: #667386; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.branch-analytics__table th:first-child, .branch-analytics__table td:first-child { text-align: left; }
.branch-analytics__table tbody tr:last-child > * { border-bottom: 0; }
.branch-analytics__branch-name { display: block; color: #1d3048; font-weight: 700; }
.branch-analytics__table small { display: block; margin-top: 3px; color: #7b8795; font-size: 11px; }
.branch-analytics__table-subtext { white-space: nowrap; }
.branch-analytics__status { display: inline-block; padding: 4px 7px; border-radius: 4px; font-size: 11px; font-weight: 700; }
.branch-analytics__status.is-active { background: #e7f4eb; color: #267447; }
.branch-analytics__status.is-inactive { background: #f0f1f2; color: #68717a; }
.branch-analytics__profit { color: #246b45; font-weight: 700; }
.branch-analytics__empty, .branch-analytics__notice { padding: 20px; color: #667386; text-align: center; }
.branch-analytics__notice--error { color: #a33125; }
@media (max-width: 760px) {
  .branch-analytics__overview { padding: 18px; }
  .branch-analytics__header { align-items: start; }
  .branch-analytics__summary, .branch-analytics__insights { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .branch-analytics__bar-row { grid-template-columns: minmax(100px, 0.8fr) minmax(90px, 1.5fr) minmax(90px, 0.7fr); }
}
@media (max-width: 420px) {
  .branch-analytics__header { flex-direction: column; }
  .branch-analytics__filter { width: 100%; }
  .branch-analytics__summary, .branch-analytics__insights { grid-template-columns: 1fr; }
  .branch-analytics__section-heading { flex-direction: column; }
}
</style>