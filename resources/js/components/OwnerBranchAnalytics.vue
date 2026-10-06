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
        </article>
        <article class="branch-analytics__stat">
          <span>Completed orders</span><strong>{{ formatNumber(totals.total_orders) }}</strong>
        </article>
        <article class="branch-analytics__stat">
          <span>Expenses</span><strong>{{ formatCurrency(totals.total_expenses) }}</strong>
        </article>
        <article class="branch-analytics__stat branch-analytics__stat--profit">
          <span>Net profit</span><strong>{{ formatCurrency(totals.net_profit) }}</strong>
        </article>
      </div>
    </div>

    <template v-if="!loading && !errorMessage">
      <div class="branch-analytics__table-wrap">
        <table class="branch-analytics__table">
          <thead>
            <tr>
              <th scope="col">Branch</th>
              <th scope="col">Status</th>
              <th scope="col">Sales</th>
              <th scope="col">Orders</th>
              <th scope="col">Expenses</th>
              <th scope="col">Refunds</th>
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
              <td>{{ formatNumber(branch.total_orders) }}</td>
              <td>{{ formatCurrency(branch.total_expenses) }}</td>
              <td>{{ formatCurrency(branch.total_refunds) }}</td>
              <td class="branch-analytics__profit">{{ formatCurrency(branch.net_profit) }}</td>
            </tr>
            <tr v-if="branches.length === 0">
              <td colspan="7" class="branch-analytics__empty">No non-main branches found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const range = ref('thisMonth')
const loading = ref(false)
const errorMessage = ref('')
const branches = ref([])
const totals = ref({ total_sales: 0, total_orders: 0, total_expenses: 0, total_refunds: 0, net_profit: 0 })

function formatCurrency(value) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(value || 0))
}

function formatNumber(value) {
  return new Intl.NumberFormat('en-PH').format(Number(value || 0))
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
.branch-analytics__stat span { color: #667386; font-size: 12px; font-weight: 600; }
.branch-analytics__stat strong { color: #1d3048; font-size: 20px; }
.branch-analytics__table-wrap { overflow-x: auto; border: 1px solid #e4e7eb; border-radius: 12px; background: #fff; }
.branch-analytics__table { width: 100%; border-collapse: collapse; white-space: nowrap; }
.branch-analytics__table th, .branch-analytics__table td { padding: 13px 14px; border-bottom: 1px solid #edf0f2; text-align: right; font-size: 13px; }
.branch-analytics__table thead th { background: #f5f7f8; color: #667386; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.branch-analytics__table th:first-child, .branch-analytics__table td:first-child { text-align: left; }
.branch-analytics__table tbody tr:last-child > * { border-bottom: 0; }
.branch-analytics__branch-name { display: block; color: #1d3048; font-weight: 700; }
.branch-analytics__table small { display: block; margin-top: 3px; color: #7b8795; font-size: 11px; }
.branch-analytics__status { display: inline-block; padding: 4px 7px; border-radius: 4px; font-size: 11px; font-weight: 700; }
.branch-analytics__status.is-active { background: #e7f4eb; color: #267447; }
.branch-analytics__status.is-inactive { background: #f0f1f2; color: #68717a; }
.branch-analytics__profit { color: #246b45; font-weight: 700; }
.branch-analytics__empty, .branch-analytics__notice { padding: 20px; color: #667386; text-align: center; }
.branch-analytics__notice--error { color: #a33125; }
@media (max-width: 760px) {
  .branch-analytics__overview { padding: 18px; }
  .branch-analytics__header { align-items: start; }
  .branch-analytics__summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 420px) {
  .branch-analytics__header { flex-direction: column; }
  .branch-analytics__filter { width: 100%; }
}
</style>