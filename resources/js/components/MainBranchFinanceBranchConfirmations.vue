<template>
  <section class="finance-confirmation">
    <div class="finance-confirmation__header">
      <div>
        <h2>
          Branch Budget Confirmations
          <span v-if="pendingBranches.length > 0" class="panel-badge">{{ pendingBranches.length }}</span>
        </h2>
        <p>Confirm budget allocation for new branches before sending to owner approval.</p>
      </div>
      <button class="refresh-btn" @click="loadPending" :disabled="loading">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <div v-if="loading" class="muted">Loading pending requests...</div>
    <div v-else-if="error" class="alert-error">Warning: {{ error }}</div>
    <div v-else-if="pendingBranches.length === 0" class="empty-state">
      <p>No pending budget confirmations.</p>
    </div>

    <div v-else class="request-grid">
      <article v-for="branch in pendingBranches" :key="branch.id" class="request-card">
        <div class="request-card__top">
          <div>
            <div class="request-title">
              <h3>{{ branch.name }}</h3>
              <span class="badge badge-pending">Pending Finance</span>
            </div>
            <p class="request-meta">
              Code: <strong>{{ branch.code }}</strong>
              <span class="dot">|</span>
              Budget: <strong>{{ formatCurrency(branch.budget || 0) }}</strong>
            </p>
            <p class="request-meta">
              Requested by:
              <strong>{{ branch.requested_by?.full_name || branch.requested_by?.username || 'Unknown' }}</strong>
              <span class="dot">|</span>
              {{ formatDate(branch.created_at) }}
            </p>
          </div>
          <div class="request-id">#{{ branch.id }}</div>
        </div>

        <div class="request-card__body">
          <div class="address-block">
            <div class="label">Address</div>
            <div class="value">{{ branch.address || 'No address provided' }}</div>
          </div>
        </div>

        <div class="request-card__actions">
          <button
            class="btn-approve"
            :disabled="approvingId === branch.id || rejectingId === branch.id"
            @click="approveBranch(branch)"
          >
            {{ approvingId === branch.id ? 'Confirming...' : 'Confirm Budget' }}
          </button>
          <button
            class="btn-reject"
            :disabled="approvingId === branch.id || rejectingId === branch.id"
            @click="rejectBranch(branch)"
          >
            {{ rejectingId === branch.id ? 'Rejecting...' : 'Reject' }}
          </button>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { showToast } from './toastStore'

const pendingBranches = ref([])
const loading = ref(false)
const error = ref('')
const approvingId = ref(null)
const rejectingId = ref(null)
const hasNotified = ref(false)

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatCurrency(amount) {
  try {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(amount)
  } catch (e) {
    return 'PHP ' + (amount || 0)
  }
}

async function loadPending() {
  loading.value = true
  error.value = ''
  try {
    const res = await axios.get('/api/main-branch/finance/branch-requests', { withCredentials: true })
    if (res.data?.ok) {
      pendingBranches.value = res.data.branches || []
      notifyIfPending()
    } else {
      error.value = res.data?.message || 'Failed to load pending confirmations.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load pending confirmations.'
  } finally {
    loading.value = false
  }
}

function notifyIfPending() {
  if (hasNotified.value) return
  if ((pendingBranches.value || []).length > 0) {
    showToast('You have pending budget confirmations.', 'info')
    hasNotified.value = true
  }
}

async function approveBranch(branch) {
  const result = await Swal.fire({
    title: 'Confirm budget allocation?',
    text: `This will forward the request to owner approval.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Confirm',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#16a34a',
  })

  if (!result.isConfirmed) return

  approvingId.value = branch.id
  try {
    const res = await axios.post(`/api/main-branch/finance/branch-requests/${branch.id}/approve`, {}, { withCredentials: true })
    if (res.data?.ok) {
      await loadPending()
    } else {
      error.value = res.data?.message || 'Failed to confirm budget.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to confirm budget.'
  } finally {
    approvingId.value = null
  }
}

async function rejectBranch(branch) {
  const result = await Swal.fire({
    title: 'Reject branch request?',
    text: `This will reject the budget allocation for ${branch.name}.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Reject',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#dc2626',
  })

  if (!result.isConfirmed) return

  rejectingId.value = branch.id
  try {
    const res = await axios.post(`/api/main-branch/finance/branch-requests/${branch.id}/reject`, {}, { withCredentials: true })
    if (res.data?.ok) {
      await loadPending()
    } else {
      error.value = res.data?.message || 'Failed to reject request.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to reject request.'
  } finally {
    rejectingId.value = null
  }
}

onMounted(loadPending)
</script>

<style scoped>
.finance-confirmation {
  background: white;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  margin-bottom: 24px;
}

.finance-confirmation__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.finance-confirmation__header h2 {
  margin: 0 0 4px;
  font-size: 1.1rem;
  color: #1F2937;
  font-weight: 700;
  position: relative;
}

.panel-badge {
  position: absolute;
  top: -8px;
  right: -18px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(239,68,68,0.35);
}

.finance-confirmation__header p {
  margin: 0;
  color: #6B7280;
  font-size: 0.9rem;
}

.refresh-btn {
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  background: #F3F4F6;
  color: #374151;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.2s ease;
}

.refresh-btn:hover:not(:disabled) {
  background: #E5E7EB;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.muted {
  color: #6B7280;
  padding: 12px 0;
  font-size: 0.9rem;
}

.empty-state {
  padding: 16px 0;
  color: #9CA3AF;
  text-align: center;
  font-style: italic;
}

.alert-error {
  background: #FEE2E2;
  border: 1px solid #FECACA;
  color: #991B1B;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.request-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 14px;
}

.request-card {
  background: #F9FAFB;
  border-radius: 8px;
  padding: 14px;
  border: 1px solid #E5E7EB;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.request-card__top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.request-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.request-title h3 {
  margin: 0;
  font-size: 1rem;
  color: #1F2937;
  font-weight: 600;
}

.request-meta {
  margin: 6px 0 0;
  color: #6B7280;
  font-size: 0.85rem;
}

.dot {
  margin: 0 6px;
}

.request-id {
  font-weight: 600;
  color: #9CA3AF;
  font-size: 0.85rem;
}

.request-card__body {
  margin: 8px 0;
}

.address-block .label {
  font-size: 0.8rem;
  color: #6B7280;
  font-weight: 600;
  margin-bottom: 4px;
}

.address-block .value {
  color: #374151;
  font-size: 0.9rem;
}

.request-card__actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.btn-approve,
.btn-reject {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-approve {
  background: #27ae60;
  color: white;
}

.btn-approve:hover:not(:disabled) {
  background: #229954;
}

.btn-approve:disabled {
  background: #95a5a6;
  cursor: not-allowed;
}

.btn-reject {
  background: #e74c3c;
  color: white;
}

.btn-reject:hover:not(:disabled) {
  background: #c0392b;
}

.btn-reject:disabled {
  background: #95a5a6;
  cursor: not-allowed;
}

.badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-pending {
  background: #FEF3C7;
  color: #92400E;
}
</style>
