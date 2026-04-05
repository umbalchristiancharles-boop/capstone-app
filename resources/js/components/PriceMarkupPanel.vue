<template>
  <div class="price-markup-section">
    <!-- Current Markup Display -->
    <div class="markup-display">
      <div class="current-markup">
        <span class="label">Current Price Markup</span>
        <div class="value-box">
          {{ currentPercentage }}%
          <span class="multiplier">(×{{ currentMultiplier }})</span>
        </div>
      </div>
      <p class="description">All dishes are sold at cost price × {{ currentMultiplier }}</p>
    </div>

    <!-- Request New Percentage (Finance Manager Only) -->
    <div v-if="canRequest && !showRequestForm" class="action-buttons">
      <button class="btn-request" @click="showRequestForm = true">
        📊 Request Percentage Change
      </button>
    </div>

    <!-- Request Form -->
    <div v-if="canRequest && showRequestForm" class="request-form">
      <h4>Request New Markup Percentage</h4>
      
      <div class="form-group">
        <label>New Percentage (%)</label>
        <div class="input-with-preview">
          <input
            v-model.number="newPercentage"
            type="number"
            min="1"
            max="100"
            step="0.5"
            placeholder="Enter percentage"
            @input="calculatePreview"
          />
          <div v-if="previewMultiplier" class="preview">
            Preview: ×{{ previewMultiplier }}
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Reason (Optional)</label>
        <textarea
          v-model="requestReason"
          placeholder="Why are you requesting this change?"
          rows="3"
        ></textarea>
      </div>

      <div v-if="requestError" class="alert alert-error">
        ⚠️ {{ requestError }}
      </div>

      <div class="form-actions">
        <button class="btn-cancel" @click="cancelRequest" :disabled="isSubmitting">
          Cancel
        </button>
        <button class="btn-submit" @click="submitRequest" :disabled="isSubmitting || !isValidRequest">
          {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
        </button>
      </div>
    </div>

    <!-- Display Success Message -->
    <div v-if="requestSuccess" class="alert alert-success">
      ✅ {{ requestSuccess }}
    </div>

    <!-- Pending Requests List (For Main Finance and Owner) -->
    <div v-if="(canApproveAsMainFinance || canApproveAsOwner) && pendingRequests.length > 0" class="pending-requests">
      <h4>
        Pending Approval Requests
        <span v-if="pendingRequests.length > 0" class="panel-badge">{{ pendingRequests.length }}</span>
      </h4>
      
      <div v-for="req in pendingRequests" :key="req.id" class="request-card">
        <div class="request-header">
          <div class="request-info">
            <span class="badge" :class="getStatusBadgeClass(req)">
              {{ formatStatus(req) }}
            </span>
            <span class="timestamp">{{ formatDate(req.created_at) }}</span>
          </div>
          <div class="change-highlight">
            {{ req.current_percentage }}% → {{ req.requested_percentage }}%
            <span class="multiplier">(×{{ req.current_percentage / 100 + 1 }} → ×{{ req.requested_percentage / 100 + 1 }})</span>
          </div>
        </div>

        <div v-if="req.reason" class="request-reason">
          <strong>Reason:</strong> {{ req.reason }}
        </div>

        <div class="request-requested-by">
          <strong>Requested by:</strong> {{ req.requested_by?.full_name || 'Unknown' }} ({{ formatDate(req.created_at) }})
        </div>

        <!-- Main Finance Approval Section -->
        <div v-if="canApproveAsMainFinance && req.main_finance_approval === 'pending'" class="approval-section main-finance">
          <h5>Main Finance Manager Review</h5>
          <div class="approval-form">
            <textarea
              v-model="approvalNotes[req.id]"
              placeholder="Add approval notes (optional)"
              rows="2"
            ></textarea>
            <div class="approval-buttons">
              <button class="btn-approve" @click="approveAsMainFinance(req)" :disabled="isApproving[req.id]">
                {{ isApproving[req.id] ? 'Processing...' : '✓ Approve' }}
              </button>
              <button class="btn-reject" @click="rejectAsMainFinance(req)" :disabled="isApproving[req.id]">
                {{ isApproving[req.id] ? 'Processing...' : '✕ Reject' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Main Finance Approval Display -->
        <div v-else-if="req.main_finance_approval === 'approved'" class="approval-status approved">
          <strong>✓ Approved by Main Finance:</strong> 
          {{ req.main_finance_approved_by?.full_name || 'System' }} on {{ formatDate(req.main_finance_approved_at) }}
          <div v-if="req.main_finance_notes" class="approval-notes">
            Notes: {{ req.main_finance_notes }}
          </div>
        </div>
        <div v-else-if="req.main_finance_approval === 'rejected'" class="approval-status rejected">
          <strong>✕ Rejected by Main Finance:</strong> 
          {{ req.main_finance_approved_by?.full_name || 'System' }} on {{ formatDate(req.main_finance_approved_at) }}
          <div v-if="req.main_finance_notes" class="approval-notes">
            Reason: {{ req.main_finance_notes }}
          </div>
        </div>

        <!-- Owner Approval Section -->
        <div v-if="canApproveAsOwner && req.main_finance_approval === 'approved' && req.owner_approval === 'pending'" class="approval-section owner">
          <h5>Owner Final Approval</h5>
          <div class="approval-form">
            <textarea
              v-model="ownerApprovalNotes[req.id]"
              placeholder="Add final approval notes (optional)"
              rows="2"
            ></textarea>
            <div class="approval-buttons">
              <button class="btn-approve" @click="approveAsOwner(req)" :disabled="isApproving[req.id]">
                {{ isApproving[req.id] ? 'Processing...' : '✓ Approve & Activate' }}
              </button>
              <button class="btn-reject" @click="rejectAsOwner(req)" :disabled="isApproving[req.id]">
                {{ isApproving[req.id] ? 'Processing...' : '✕ Reject' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Owner Approval Display -->
        <div v-else-if="canApproveAsOwner && req.owner_approval === 'approved'" class="approval-status approved activated">
          <strong>✓ Approved by Owner - ACTIVATED:</strong> 
          {{ req.owner_approved_by?.full_name || 'System' }} on {{ formatDate(req.owner_approved_at) }}
          <div v-if="req.owner_notes" class="approval-notes">
            Notes: {{ req.owner_notes }}
          </div>
        </div>
        <div v-else-if="canApproveAsOwner && req.owner_approval === 'rejected'" class="approval-status rejected">
          <strong>✕ Rejected by Owner:</strong> 
          {{ req.owner_approved_by?.full_name || 'System' }} on {{ formatDate(req.owner_approved_at) }}
          <div v-if="req.owner_notes" class="approval-notes">
            Reason: {{ req.owner_notes }}
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="(canApproveAsMainFinance || canApproveAsOwner) && pendingRequests.length === 0 && !loading" class="empty-state">
      <p>No pending markup percentage requests</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { showToast } from './toastStore'

const props = defineProps({
  branchId: { type: Number, default: null },
  userRole: { type: String, default: '' },
  isMainBranchUser: { type: Boolean, default: false },
})

// State
const currentPercentage = ref(20)
const currentMultiplier = computed(() => (1 + currentPercentage.value / 100).toFixed(2))
const newPercentage = ref(null)
const previewMultiplier = computed(() => newPercentage.value ? (1 + newPercentage.value / 100).toFixed(2) : null)
const requestReason = ref('')
const showRequestForm = ref(false)
const isSubmitting = ref(false)
const requestError = ref('')
const requestSuccess = ref('')
const pendingRequests = ref([])
const approvalNotes = ref({})
const ownerApprovalNotes = ref({})
const isApproving = ref({})
const loading = ref(false)
const hasNotified = ref(false)

// Permissions
const canRequest = computed(() => {
  const role = props.userRole.toUpperCase()
  return (
    role === 'FINANCE_MANAGER' ||
    role === 'MANAGER' ||
    role === 'OWNER' ||
    role === 'SUPER_ADMIN' ||
    role === 'SUPERADMIN'
  )
})

const canApproveAsMainFinance = computed(() => {
  const role = props.userRole.toUpperCase()
  return props.isMainBranchUser && (role === 'FINANCE_MANAGER' || role === 'MANAGER' || role === 'OWNER' || role === 'SUPERADMIN' || role === 'SUPER_ADMIN')
})

const canApproveAsOwner = computed(() => {
  const role = props.userRole.toUpperCase()
  return role === 'OWNER' || role === 'SUPER_ADMIN' || role === 'SUPERADMIN'
})

const isValidRequest = computed(() => {
  return newPercentage.value !== null && newPercentage.value > 0 && newPercentage.value <= 100 && newPercentage.value !== currentPercentage.value
})

// Methods
function calculatePreview() {
  // Trigger reactivity
  previewMultiplier.value
}

function cancelRequest() {
  showRequestForm.value = false
  newPercentage.value = null
  requestReason.value = ''
  requestError.value = ''
}

async function submitRequest() {
  if (!isValidRequest.value) return

  isSubmitting.value = true
  requestError.value = ''
  requestSuccess.value = ''

  try {
    const response = await axios.post('/api/price-markup/request', {
      branch_id: props.branchId,
      requested_percentage: newPercentage.value,
      reason: requestReason.value || null,
    }, { withCredentials: true })

    if (response.data.ok) {
      requestSuccess.value = 'Request submitted successfully. Awaiting Main Finance Manager approval.'
      showRequestForm.value = false
      newPercentage.value = null
      requestReason.value = ''
      await loadPendingRequests()
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to submit request'
    requestError.value = msg
  } finally {
    isSubmitting.value = false
  }
}

async function approveAsMainFinance(request) {
  isApproving.value[request.id] = true
  try {
    const response = await axios.post(`/api/price-markup/${request.id}/main-finance-approve`, {
      approved: true,
      notes: approvalNotes.value[request.id] || null,
    }, { withCredentials: true })

    if (response.data.ok) {
      await loadPendingRequests()
      approvalNotes.value[request.id] = ''
    }
  } catch (error) {
    console.error('Approval failed:', error)
  } finally {
    isApproving.value[request.id] = false
  }
}

async function rejectAsMainFinance(request) {
  isApproving.value[request.id] = true
  try {
    const response = await axios.post(`/api/price-markup/${request.id}/main-finance-approve`, {
      approved: false,
      notes: approvalNotes.value[request.id] || null,
    }, { withCredentials: true })

    if (response.data.ok) {
      await loadPendingRequests()
      approvalNotes.value[request.id] = ''
    }
  } catch (error) {
    console.error('Rejection failed:', error)
  } finally {
    isApproving.value[request.id] = false
  }
}

async function approveAsOwner(request) {
  isApproving.value[request.id] = true
  try {
    const response = await axios.post(`/api/price-markup/${request.id}/owner-approve`, {
      approved: true,
      notes: ownerApprovalNotes.value[request.id] || null,
    }, { withCredentials: true })

    if (response.data.ok) {
      currentPercentage.value = request.requested_percentage
      await loadPendingRequests()
      ownerApprovalNotes.value[request.id] = ''
    }
  } catch (error) {
    console.error('Approval failed:', error)
  } finally {
    isApproving.value[request.id] = false
  }
}

async function rejectAsOwner(request) {
  isApproving.value[request.id] = true
  try {
    const response = await axios.post(`/api/price-markup/${request.id}/owner-approve`, {
      approved: false,
      notes: ownerApprovalNotes.value[request.id] || null,
    }, { withCredentials: true })

    if (response.data.ok) {
      await loadPendingRequests()
      ownerApprovalNotes.value[request.id] = ''
    }
  } catch (error) {
    console.error('Rejection failed:', error)
  } finally {
    isApproving.value[request.id] = false
  }
}

async function loadCurrentPercentage() {
  try {
    const response = await axios.get(`/api/price-markup/current/${props.branchId || ''}`, { withCredentials: true })
    if (response.data.ok) {
      currentPercentage.value = response.data.current_percentage
    }
  } catch (error) {
    console.error('Failed to load current percentage:', error)
  }
}

async function loadPendingRequests() {
  try {
    // Main branch user should see all requests (pass no branchId to backend)
    const branchParam = props.isMainBranchUser ? '' : (props.branchId || '');
    const response = await axios.get(`/api/price-markup/pending/${branchParam}`, { withCredentials: true })
    if (response.data.ok) {
      pendingRequests.value = response.data.requests || []
      notifyIfPending()
    }
  } catch (error) {
    console.error('Failed to load pending requests:', error)
  }
}

function notifyIfPending() {
  if (hasNotified.value) return
  if ((pendingRequests.value || []).length > 0) {
    showToast('You have pending price markup requests.', 'info')
    hasNotified.value = true
  }
}

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

function formatStatus(req) {
  if (req.owner_approval === 'approved') return 'ACTIVATED'
  if (req.main_finance_approval === 'approved') return 'Awaiting Owner'
  if (req.main_finance_approval === 'rejected' || req.owner_approval === 'rejected') return 'REJECTED'
  return 'Pending Finance Review'
}

function getStatusBadgeClass(req) {
  if (req.owner_approval === 'approved') return 'badge-activated'
  if (req.owner_approval === 'rejected' || req.main_finance_approval === 'rejected') return 'badge-rejected'
  if (req.main_finance_approval === 'approved') return 'badge-approved'
  return 'badge-pending'
}

onMounted(async () => {
  loading.value = true
  await loadCurrentPercentage()
  if (canApproveAsMainFinance.value || canApproveAsOwner.value) {
    await loadPendingRequests()
  }
  loading.value = false
})
</script>

<style scoped>
.price-markup-section {
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 14px rgba(16, 24, 40, 0.04);
  border: 1px solid #eef2f7;
  margin-bottom: 20px;
}

.markup-display {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
  padding: 16px;
  background: linear-gradient(135deg, #fff5f0 0%, #fff9f7 100%);
  border-radius: 8px;
  border: 1px solid #ffe4d6;
}

.current-markup {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.current-markup .label {
  font-weight: 600;
  color: #6b7280;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.value-box {
  display: flex;
  align-items: baseline;
  gap: 8px;
  font-size: 28px;
  font-weight: 700;
  color: var(--orange);
}

.multiplier {
  font-size: 16px;
  font-weight: 500;
  color: #6b7280;
}

.description {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
  font-style: italic;
}

.action-buttons {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.btn-request {
  flex: 1;
  padding: 10px 16px;
  background: linear-gradient(135deg, var(--orange), #ff8c42);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(255, 107, 28, 0.15);
}

.btn-request:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 107, 28, 0.25);
}

.request-form {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  margin-bottom: 20px;
}

.request-form h4 {
  margin: 0 0 16px;
  font-size: 16px;
  color: var(--text-dark);
}

.form-group {
  margin-bottom: 14px;
}

.form-group label {
  display: block;
  font-weight: 600;
  font-size: 13px;
  color: #374151;
  margin-bottom: 6px;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: inherit;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--orange);
  box-shadow: 0 0 0 3px rgba(255, 107, 28, 0.08);
}

.input-with-preview {
  display: flex;
  gap: 12px;
  align-items: center;
}

.input-with-preview input {
  flex: 1;
}

.preview {
  padding: 8px 12px;
  background: var(--orange);
  color: white;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  white-space: nowrap;
}

.alert {
  padding: 12px 14px;
  border-radius: 6px;
  margin-bottom: 14px;
  font-size: 13px;
}

.alert-error {
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #dc2626;
}

.alert-success {
  background: rgba(34, 197, 94, 0.08);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #16a34a;
  margin-bottom: 20px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.btn-cancel,
.btn-submit {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-cancel {
  background: #e5e7eb;
  color: #374151;
}

.btn-cancel:hover {
  background: #d1d5db;
}

.btn-submit {
  background: var(--orange);
  color: white;
  box-shadow: 0 4px 12px rgba(255, 107, 28, 0.15);
}

.btn-submit:hover:not(:disabled) {
  background: #ff6a3d;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.pending-requests {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 2px solid #f3f4f6;
}

.pending-requests h4 { position: relative; }
.panel-badge { position:absolute; top:-8px; right:-18px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }

.pending-requests h4 {
  margin: 0 0 16px;
  font-size: 16px;
  color: var(--text-dark);
}

.request-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 14px;
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 12px;
}

.request-info {
  display: flex;
  gap: 8px;
  align-items: center;
}

.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-pending {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

.badge-approved {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.badge-activated {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
}

.badge-rejected {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.timestamp {
  font-size: 12px;
  color: #9ca3af;
}

.change-highlight {
  display: flex;
  flex-direction: column;
  gap: 4px;
  text-align: right;
  font-weight: 600;
  color: var(--orange);
}

.change-highlight .multiplier {
  font-size: 12px;
  color: #6b7280;
}

.request-reason,
.request-requested-by {
  font-size: 13px;
  color: #374151;
  margin-bottom: 8px;
}

.request-reason strong,
.request-requested-by strong {
  color: #1f2937;
  margin-right: 4px;
}

.approval-section {
  margin-top: 14px;
  padding: 12px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
}

.approval-section h5 {
  margin: 0 0 12px;
  font-size: 13px;
  text-transform: uppercase;
  color: #6b7280;
  letter-spacing: 0.5px;
}

.approval-section.main-finance {
  border-left: 3px solid #3b82f6;
  background: rgba(59, 130, 246, 0.02);
}

.approval-section.owner {
  border-left: 3px solid var(--orange);
  background: rgba(255, 107, 28, 0.02);
}

.approval-form textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: inherit;
  font-size: 13px;
  margin-bottom: 10px;
  resize: vertical;
}

.approval-buttons {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn-approve,
.btn-reject {
  padding: 6px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 12px;
  transition: all 0.2s;
}

.btn-approve {
  background: #22c55e;
  color: white;
  box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
}

.btn-approve:hover:not(:disabled) {
  background: #16a34a;
  transform: translateY(-1px);
}

.btn-reject {
  background: #ef4444;
  color: white;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
}

.btn-reject:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

.btn-approve:disabled,
.btn-reject:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.approval-status {
  margin-top: 12px;
  padding: 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
}

.approval-status.approved {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
  border: 1px solid rgba(34, 197, 94, 0.2);
}

.approval-status.rejected {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.approval-status.activated {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.12) 0%, rgba(34, 197, 94, 0.08) 100%);
  border: 2px solid #22c55e;
  font-weight: 600;
}

.approval-notes {
  margin-top: 6px;
  font-size: 12px;
  opacity: 0.9;
}

.empty-state {
  padding: 30px;
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  gap: 12px;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #e5e7eb;
  border-top: 3px solid var(--orange);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .price-markup-section {
    padding: 16px;
  }

  .request-header {
    flex-direction: column;
    gap: 8px;
  }

  .change-highlight {
    text-align: left;
  }

  .approval-buttons {
    flex-direction: column;
  }

  .btn-approve,
  .btn-reject {
    width: 100%;
  }
}
</style>
