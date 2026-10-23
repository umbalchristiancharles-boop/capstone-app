<template>
  <section class="finance-confirmation">
    <div class="finance-confirmation__header">
      <div>
        <h2>
          New Branch Invoice & Budget Review
          <span v-if="pendingBranches.length > 0" class="panel-badge">{{ pendingBranches.length }}</span>
        </h2>
        <p>Review submitted cost documents and assign a branch budget before sending the request to Owner approval.</p>
      </div>
      <button class="refresh-btn" @click="loadPending" :disabled="loading">
        {{ loading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <div v-if="loading" class="muted">Loading pending requests...</div>
    <div v-else-if="error" class="alert-error">Warning: {{ error }}</div>
    <div v-else-if="pendingBranches.length === 0" class="empty-state">
      <p>No new branch invoices are awaiting review.</p>
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
              Assigned budget: <strong>{{ branch.budget ? formatCurrency(branch.budget) : 'Not assigned' }}</strong>
              <span class="dot">|</span>
              Effective budget: <strong>{{ formatCurrency(branch.budget || 0) }}</strong>
              <span class="dot">|</span>
              Available Main Branch budget: <strong>{{ formatCurrency(branch.available_main_branch_budget || 0) }}</strong>
              <span class="dot">|</span>
              Maximum branch budget after invoice: <strong>{{ formatCurrency(branch.max_assignable_budget || 0) }}</strong>
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
            <div class="value">
              {{ isCoordinateFallback(branch.address) ? 'Pinned location' : (branch.address || 'No address provided') }}
              <a v-if="hasCoordinates(branch)" :href="mapUrl(branch)" target="_blank" rel="noopener noreferrer">
                View on map
              </a>
            </div>
          </div>
          <div class="request-meta-grid request-details-grid">
            <div class="request-meta"><span>Store area</span><strong>{{ branch.square_meters || 'N/A' }} m²</strong></div>
            <div class="request-meta"><span>Geofence radius</span><strong>{{ branch.geofencing_radius || 'N/A' }} m</strong></div>
            <div class="request-meta"><span>Coordinates</span><strong>{{ branch.latitude ?? 'N/A' }}, {{ branch.longitude ?? 'N/A' }}</strong></div>
            <div class="request-meta"><span>Submitted invoice total</span><strong>{{ branch.total_investment == null ? 'N/A' : formatCurrency(branch.total_investment) }}</strong></div>
          </div>
          <div v-if="branch.cost_attachments?.permit_bills?.length || branch.cost_attachments?.construction_costs?.length || branch.cost_attachments?.equipment_costs?.length || branch.permit_bills?.length || branch.construction_costs?.length || branch.equipment_costs?.length" class="cost-details">
            <div v-if="branch.cost_attachments?.permit_bills?.length || branch.permit_bills?.length">
              <strong>Permit bills</strong>
              <a v-for="(file, index) in branch.cost_attachments?.permit_bills || []" :key="`permit-file-${index}`" :href="file.url" target="_blank" rel="noopener noreferrer">{{ file.name }}<template v-if="file.amount != null"> — {{ formatCurrency(file.amount) }}</template></a>
              <span v-for="(item, index) in branch.permit_bills || []" :key="`permit-${index}`">{{ item.type || 'Permit' }}: {{ formatCurrency(item.amount || 0) }}</span>
            </div>
            <div v-if="branch.cost_attachments?.construction_costs?.length || branch.construction_costs?.length">
              <strong>Construction costs</strong>
              <a v-for="(file, index) in branch.cost_attachments?.construction_costs || []" :key="`construction-file-${index}`" :href="file.url" target="_blank" rel="noopener noreferrer">{{ file.name }}<template v-if="file.amount != null"> — {{ formatCurrency(file.amount) }}</template></a>
              <span v-for="(item, index) in branch.construction_costs || []" :key="`construction-${index}`">{{ item.category || 'Construction' }}: {{ formatCurrency(item.amount || 0) }}</span>
            </div>
            <div v-if="branch.cost_attachments?.equipment_costs?.length || branch.equipment_costs?.length">
              <strong>Equipment costs</strong>
              <a v-for="(file, index) in branch.cost_attachments?.equipment_costs || []" :key="`equipment-file-${index}`" :href="file.url" target="_blank" rel="noopener noreferrer">{{ file.name }}<template v-if="file.amount != null"> — {{ formatCurrency(file.amount) }}</template></a>
              <span v-for="(item, index) in branch.equipment_costs || []" :key="`equipment-${index}`">{{ item.name || 'Equipment' }} ({{ item.quantity || 0 }}): {{ formatCurrency((item.quantity || 0) * (item.unit_cost || 0)) }}</span>
            </div>
          </div>
        </div>

        <div class="request-card__actions">
          <button
            class="btn-approve"
            :disabled="approvingId === branch.id || rejectingId === branch.id || Number(branch.max_assignable_budget) < 1"
            @click="approveBranch(branch)"
          >
            {{ approvingId === branch.id ? 'Saving...' : 'Confirm Invoice & Set Budget' }}
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
const rejectionReasonOptions = [
  'Incomplete or inaccurate branch information',
  'Insufficient budget or financial justification',
  'Location does not meet branch requirements',
  'Missing or invalid supporting documents',
  'Does not meet branch approval criteria',
  'Other',
]

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
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 2 }).format(amount)
  } catch (e) {
    return 'PHP ' + (amount || 0)
  }
}

function isCoordinateFallback(address) {
  return /^Pinned location:\s*-?\d+(?:\.\d+)?,\s*-?\d+(?:\.\d+)?$/i.test(address || '')
}

function hasCoordinates(branch) {
  return Number.isFinite(Number(branch.latitude)) && Number.isFinite(Number(branch.longitude))
    && branch.latitude !== null && branch.longitude !== null
}

function mapUrl(branch) {
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${branch.latitude},${branch.longitude}`)}`
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
  const availableBudget = Number(branch.available_main_branch_budget) || 0
  const invoiceTotal = Number(branch.total_investment) || 0
  const maxAssignableBudget = Number(branch.max_assignable_budget) || 0
  if (maxAssignableBudget < 1) {
    error.value = 'The available Main Branch budget cannot cover the invoice and a branch budget.'
    return
  }

  const result = await Swal.fire({
    title: 'Confirm invoice and assign budget',
    html: `
      <p>Review the submitted invoice total${branch.total_investment == null ? '' : ` of <strong>${formatCurrency(branch.total_investment)}</strong>`}.</p>
      <div style="margin:16px 0;padding:14px 16px;border:1px solid #fed7aa;border-radius:10px;background:#fff7ed;text-align:left">
        <div style="font-size:13px;color:#7c2d12">Available Main Branch Finance budget</div>
        <strong style="display:block;margin-top:4px;font-size:22px;color:#9a3412">${formatCurrency(availableBudget)}</strong>
        <div style="margin-top:4px;font-size:12px;color:#7c2d12">After the invoice is reserved, the maximum branch budget is ${formatCurrency(maxAssignableBudget)}.</div>
      </div>
      <p>The invoice and branch budget are separate amounts; both are deducted from the Main Branch budget after Owner approval.</p>
      <div style="margin:14px 0 0;padding:12px 14px;border-radius:10px;background:#f3f4f6;text-align:left" aria-live="polite">
        <div style="font-size:13px;color:#4b5563">Combined amount (invoice + branch budget)</div>
        <strong id="branch-combined-total" style="display:block;margin-top:4px;font-size:20px;color:#1f2937">${formatCurrency(invoiceTotal)}</strong>
      </div>
    `,
    didOpen: () => {
      const popup = Swal.getPopup()
      const budgetInput = popup?.querySelector('.swal2-input')
      const combinedTotal = popup?.querySelector('#branch-combined-total')
      budgetInput?.addEventListener('input', () => {
        const enteredBudget = Number(budgetInput.value) || 0
        if (combinedTotal) {
          combinedTotal.textContent = formatCurrency(invoiceTotal + enteredBudget)
        }
      })
    },
    input: 'number',
    inputLabel: 'Branch budget (PHP)',
    inputPlaceholder: 'Enter the approved budget',
    inputAttributes: {
      min: 1,
      max: maxAssignableBudget,
      step: 1,
    },
    inputValue: '',
    inputValidator: value => {
      const budget = Number(value)
      if (!Number.isInteger(budget) || budget < 1 || budget > maxAssignableBudget) {
        return `Enter a whole-number budget between 1 and ${formatCurrency(maxAssignableBudget)}.`
      }
      return undefined
    },
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Confirm Invoice & Budget',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#16a34a',
  })

  if (!result.isConfirmed) return

  approvingId.value = branch.id
  try {
    const res = await axios.post(
      `/api/main-branch/finance/branch-requests/${branch.id}/approve`,
      { budget: Number(result.value) },
      { withCredentials: true }
    )
    if (res.data?.ok) {
      await loadPending()
    } else {
      error.value = res.data?.message || 'Failed to confirm the invoice and assign a budget.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to confirm the invoice and assign a budget.'
  } finally {
    approvingId.value = null
  }
}

async function rejectBranch(branch) {
  const result = await Swal.fire({
    title: 'Reject branch request?',
    text: `This will reject the budget allocation for ${branch.name}.`,
    html: `
      <label for="branch-rejection-reason" style="display:block;margin:0 0 8px;text-align:left;font-weight:600">Reason for rejection</label>
      <select id="branch-rejection-reason" class="swal2-select" style="display:flex;width:100%;margin:0 0 12px">
        <option value="">Choose a reason</option>
        ${rejectionReasonOptions.map(reason => `<option value="${reason}">${reason}</option>`).join('')}
      </select>
      <textarea id="branch-rejection-details" class="swal2-textarea" maxlength="1000" placeholder="Please explain the reason for rejection..." aria-label="Other rejection reason" style="display:none;margin:0;width:100%;box-sizing:border-box"></textarea>
    `,
    didOpen: () => {
      const popup = Swal.getPopup()
      const reasonSelect = popup?.querySelector('#branch-rejection-reason')
      const otherDetails = popup?.querySelector('#branch-rejection-details')
      reasonSelect?.addEventListener('change', () => {
        if (otherDetails) {
          otherDetails.style.display = reasonSelect.value === 'Other' ? 'block' : 'none'
          if (reasonSelect.value !== 'Other') otherDetails.value = ''
        }
      })
    },
    preConfirm: () => {
      const popup = Swal.getPopup()
      const reason = popup?.querySelector('#branch-rejection-reason')?.value
      const details = popup?.querySelector('#branch-rejection-details')?.value?.trim()
      if (!reason) {
        Swal.showValidationMessage('Choose a reason before rejecting this request.')
        return false
      }
      if (reason === 'Other' && !details) {
        Swal.showValidationMessage('Please explain the reason for rejection.')
        return false
      }
      return { reason, reason_details: reason === 'Other' ? details : null }
    },
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Reject',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#dc2626',
  })

  if (!result.isConfirmed) return

  rejectingId.value = branch.id
  try {
    const res = await axios.post(`/api/main-branch/finance/branch-requests/${branch.id}/reject`, result.value, { withCredentials: true })
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

.request-meta-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  margin-top: 12px;
}

.request-meta {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.request-meta span {
  color: #6b7280;
  font-size: 0.8rem;
}

.cost-details {
  display: grid;
  gap: 8px;
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.cost-details div {
  display: grid;
  gap: 3px;
}

.cost-details span {
  color: #4b5563;
  font-size: 0.85rem;
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
