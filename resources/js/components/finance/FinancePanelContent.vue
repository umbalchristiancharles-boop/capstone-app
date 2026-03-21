<template>
  <div class="finance-content">
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
              <td>{{ tx.customer }}</td>
              <td>₱{{ tx.total }}</td>
              <td>₱{{ tx.paid }}</td>
              <td><span :class="['status-badge', getStatusClass(tx.status)]">{{ tx.status }}</span></td>
              <td>{{ tx.ordered_at || 'N/A' }}</td>
            </tr>
            <tr v-for="tx in transactions" :key="tx.id + '-details'" v-if="isOpen(tx.id)" class="tx-details-row">
              <td colspan="6">
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
                  </div>
                </div>
              </td>
            </tr>
            <tr v-if="transactions.length === 0">
              <td colspan="6" class="empty-message">No recent transactions found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Financial Reports (placeholder - expand when reports data available) -->
    <div class="panel-section">
      <h2 class="section-title">Financial Reports</h2>
      <p v-if="!reports || reports.length === 0" class="empty-message">No financial reports available. Reports will appear here when generated.</p>
      <div v-else class="reports-grid">
        <div v-for="report in reports" :key="report.id" class="report-card">
          <h3>{{ report.title }}</h3>
          <p>{{ report.summary || 'No summary available' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  reports: {
    type: Array,
    default: () => []
  },
  transactions: {
    type: Array,
    default: () => []
  }
})

const openIds = ref([])

const transactionsLoading = computed(() => props.transactions.length === 0 && !props.reports.length) // simple loading proxy

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
</script>

<style scoped>
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
  border-top: 3px solid #0066FF;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.report-card {
  padding: 20px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  background: #F9FAFB;
}

.report-card h3 {
  margin: 0 0 8px 0;
  color: #1F2937;
  font-size: 16px;
}
</style>
