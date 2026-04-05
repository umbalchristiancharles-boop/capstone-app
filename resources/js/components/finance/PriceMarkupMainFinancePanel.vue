<template>
  <div class="price-markup-main-finance-section">
    <div class="section-header">
      <h2>
        Price Markup Percentage Requests
        <span v-if="pendingRequests.length > 0" class="panel-badge">{{ pendingRequests.length }}</span>
      </h2>
      <p>Review and approve/reject percentage change requests from branch finance managers</p>
      <button class="refresh-btn" @click="fetchPendingRequests" :disabled="isLoading">
        <span v-if="!isLoading">↻ Refresh</span>
        <span v-else class="btn-loading"><span class="spinner"></span> Refreshing...</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading pending requests...</p>
    </div>

    <!-- No Pending Requests -->
    <div v-else-if="pendingRequests.length === 0" class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
      </svg>
      <p>No pending requests at this time.</p>
    </div>

    <!-- Pending Requests List -->
    <div v-else class="requests-grid">
      <div v-for="request in pendingRequests" :key="request.id" class="request-card">
        <div class="card-header">
          <div class="request-title">
            <h3>From: {{ request.requested_by?.full_name || 'Unknown' }}</h3>
            <p class="branch-name">{{ request.branch?.name }}</p>
          </div>
          <span :class="['status-badge', getStatusClass(request)]">
            {{ getRequestStatus(request) }}
          </span>
        </div>

        <div class="card-body">
          <div class="change-display">
            <div class="percentage-change">
              <span class="current">{{ request.current_percentage }}%</span>
              <span class="arrow">→</span>
              <span class="requested">{{ request.requested_percentage }}%</span>
            </div>
            <div class="multiplier-change">
              <span class="current-mult">×{{ (1 + request.current_percentage / 100).toFixed(2) }}</span>
              <span class="arrow">→</span>
              <span class="requested-mult">×{{ (1 + request.requested_percentage / 100).toFixed(2) }}</span>
            </div>
          </div>

          <div v-if="request.reason" class="reason-section">
            <strong>Reason:</strong>
            <p>{{ request.reason }}</p>
          </div>

          <div class="request-meta">
            <div class="meta-item">
              <span class="label">Requested:</span>
              <span>{{ formatDate(request.created_at) }}</span>
            </div>
            <div class="meta-item">
              <span class="label">Requested by:</span>
              <span>{{ request.requested_by?.username }}</span>
            </div>
          </div>
        </div>

        <!-- Main Finance Approval Section -->
        <div v-if="request.main_finance_approval === 'pending'" class="card-actions">
          <div class="approval-form">
            <textarea
              v-model="requestActions[request.id].notes"
              placeholder="Add notes for approval or rejection..."
              rows="3"
              :disabled="requestActions[request.id].isProcessing"
            ></textarea>
            <div class="action-buttons">
              <button
                class="btn btn-reject"
                @click="approveOrReject(request, false)"
                :disabled="requestActions[request.id].isProcessing"
              >
                <span v-if="requestActions[request.id].isProcessing" class="btn-loading">
                  <span class="spinner"></span> Processing...
                </span>
                <span v-else>Reject</span>
              </button>
              <button
                class="btn btn-approve"
                @click="approveOrReject(request, true)"
                :disabled="requestActions[request.id].isProcessing"
              >
                <span v-if="requestActions[request.id].isProcessing" class="btn-loading">
                  <span class="spinner"></span> Processing...
                </span>
                <span v-else>Approve</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Already Approved by Finance -->
        <div v-else class="card-status">
          <div :class="['status-info', request.main_finance_approval]">
            <strong>Finance Manager Decision:</strong>
            <p v-if="request.main_finance_approval === 'approved'" class="text-success">
              ✓ Approved by {{ request.main_finance_approver?.full_name || 'Finance Manager' }}
              <br>
              <span class="text-time">{{ formatDate(request.main_finance_approved_at) }}</span>
            </p>
            <p v-else-if="request.main_finance_approval === 'rejected'" class="text-danger">
              ✗ Rejected by {{ request.main_finance_approver?.full_name || 'Finance Manager' }}
              <br>
              <span class="text-time">{{ formatDate(request.main_finance_approved_at) }}</span>
            </p>
            <p v-if="request.main_finance_notes" class="notes">
              <strong>Notes:</strong> {{ request.main_finance_notes }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <transition name="fade">
      <div v-if="errorMessage" class="alert alert-error">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 5.917A2.083 2.083 0 0 0 15.917 3H4.083A2.083 2.083 0 0 0 2 5.917v8.166A2.083 2.083 0 0 0 4.083 16h11.834A2.083 2.083 0 0 0 18 13.917V5.917zM9.5 7.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zM8 10.5a1 1 0 1 1 2 0v2a1 1 0 1 1-2 0v-2z" clip-rule="evenodd" />
        </svg>
        <div>
          <strong>Error</strong>
          <p>{{ errorMessage }}</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { showToast } from '../toastStore'

const props = defineProps({
  branchId: {
    type: Number,
    default: null
  }
})

// State
const pendingRequests = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const requestActions = reactive({})
const hasNotified = ref(false)

// Methods
async function fetchPendingRequests() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const url = props.branchId
      ? `/api/price-markup/pending/${props.branchId}`
      : '/api/price-markup/pending'

    console.log('[PriceMarkupMainFinance] Fetching pending requests from:', url)
    const res = await axios.get(url)

    console.log('[PriceMarkupMainFinance] API Response:', res.data)

    if (res.data.ok) {
      pendingRequests.value = res.data.requests
      console.log('[PriceMarkupMainFinance] Loaded', pendingRequests.value.length, 'pending requests')

      // Initialize action state for each request
      res.data.requests.forEach(req => {
        if (!requestActions[req.id]) {
          requestActions[req.id] = {
            notes: '',
            isProcessing: false
          }
        }
      })

      notifyIfPending()
    } else {
      errorMessage.value = res.data?.message || 'Failed to load pending requests'
      console.warn('[PriceMarkupMainFinance] API returned ok=false:', res.data)
    }
  } catch (error) {
    console.error('[PriceMarkupMainFinance] Error fetching pending requests:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to load pending requests. Please try again.'
  } finally {
    isLoading.value = false
  }
}

function notifyIfPending() {
  if (hasNotified.value) return
  if ((pendingRequests.value || []).length > 0) {
    showToast('You have pending price markup requests to review.', 'info')
    hasNotified.value = true
  }
}

async function approveOrReject(request, approved) {
  const action = requestActions[request.id]
  action.isProcessing = true
  errorMessage.value = ''

  try {
    const res = await axios.post(
      `/api/price-markup/${request.id}/main-finance-approve`,
      {
        approved: approved,
        notes: action.notes
      }
    )

    if (res.data.ok) {
      // Remove the approved/rejected request and refresh
      await fetchPendingRequests()
      action.notes = ''

      // Show success via toast if available
      if (window.showToast) {
        window.showToast(res.data.message, 'success')
      }
    }
  } catch (error) {
    console.error('Error processing approval:', error)
    errorMessage.value =
      error.response?.data?.message ||
      'Failed to process approval. Please try again.'
  } finally {
    action.isProcessing = false
  }
}

function getStatusClass(request) {
  if (request.main_finance_approval === 'approved') {
    if (request.owner_approval === 'pending') {
      return 'status-pending-owner'
    }
    if (request.owner_approval === 'approved') {
      return 'status-approved'
    }
    if (request.owner_approval === 'rejected') {
      return 'status-rejected'
    }
  }
  if (request.main_finance_approval === 'rejected') {
    return 'status-rejected'
  }
  return 'status-pending'
}

function getRequestStatus(request) {
  if (request.main_finance_approval === 'pending') {
    return 'Awaiting Your Decision'
  }
  if (request.main_finance_approval === 'approved') {
    if (request.owner_approval === 'pending') {
      return 'Awaiting Owner Approval'
    }
    if (request.owner_approval === 'approved') {
      return 'Approved & Activated'
    }
    if (request.owner_approval === 'rejected') {
      return 'Rejected by Owner'
    }
  }
  if (request.main_finance_approval === 'rejected') {
    return 'Rejected by Finance'
  }
  return 'Processing'
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  console.log('[PriceMarkupMainFinance] Component mounted, branchId prop:', props.branchId)
  fetchPendingRequests()

  // Auto-refresh every 30 seconds
  const interval = setInterval(() => {
    fetchPendingRequests()
  }, 30000)

  onUnmounted(() => clearInterval(interval))
})
</script>

<style scoped>
.price-markup-main-finance-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding-bottom: 16px;
  border-bottom: 2px solid #E5E7EB;
}

.section-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: #1F2937;
  margin: 0;
  position: relative;
}

.panel-badge { position:absolute; top:-8px; right:-18px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }

.section-header p {
  color: #6B7280;
  font-size: 14px;
  margin: 4px 0 0 0;
}

.refresh-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  background: #F3F4F6;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
  white-space: nowrap;
}

.refresh-btn:hover:not(:disabled) {
  background: #E5E7EB;
}

.refresh-btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-loading {
  display: flex;
  align-items: center;
  gap: 6px;
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(0, 0, 0, 0.1);
  border-top-color: #1F2937;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  gap: 16px;
  color: #6B7280;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #E5E7EB;
  border-top-color: #059669;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  gap: 16px;
  color: #6B7280;
  background: #F9FAFB;
  border-radius: 12px;
  border: 2px dashed #D1D5DB;
}

.empty-state svg {
  width: 64px;
  height: 64px;
  opacity: 0.5;
}

.empty-state p {
  font-size: 16px;
  margin: 0;
}

.requests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
}

@media (max-width: 768px) {
  .requests-grid {
    grid-template-columns: 1fr;
  }
}

.request-card {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}

.request-card:hover {
  border-color: #D1D5DB;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}

.request-title h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1F2937;
  margin: 0 0 4px 0;
}

.branch-name {
  font-size: 12px;
  color: #6B7280;
  margin: 0;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.status-pending {
  background: #FEF08A;
  color: #92400E;
}

.status-pending-owner {
  background: #E0E7FF;
  color: #3730A3;
}

.status-approved {
  background: #DCFCE7;
  color: #15803D;
}

.status-rejected {
  background: #FEE2E2;
  color: #991B1B;
}

.card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.change-display {
  background: #F0FDF4;
  border: 1px solid #86EFAC;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.percentage-change,
.multiplier-change {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.current {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 60px;
  padding: 8px 12px;
  background: #EF4444;
  color: white;
  border-radius: 6px;
  font-weight: 700;
  font-size: 16px;
}

.requested {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 60px;
  padding: 8px 12px;
  background: #10B981;
  color: white;
  border-radius: 6px;
  font-weight: 700;
  font-size: 16px;
}

.current-mult,
.requested-mult {
  font-weight: 600;
}

.arrow {
  color: #6B7280;
  font-weight: 700;
}

.reason-section {
  border-left: 3px solid #3B82F6;
  padding: 12px 16px;
  background: #EFF6FF;
}

.reason-section strong {
  color: #1E40AF;
  font-size: 13px;
  display: block;
  margin-bottom: 4px;
}

.reason-section p {
  margin: 0;
  color: #1F2937;
  font-size: 14px;
}

.request-meta {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  padding-top: 12px;
  border-top: 1px solid #E5E7EB;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-item .label {
  font-size: 12px;
  color: #6B7280;
  font-weight: 600;
}

.meta-item span {
  font-size: 13px;
  color: #1F2937;
}

.card-actions,
.card-status {
  padding: 16px;
  background: #F9FAFB;
  border-top: 1px solid #E5E7EB;
}

.approval-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.approval-form textarea {
  padding: 10px;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  font-size: 13px;
  font-family: inherit;
  resize: vertical;
  transition: border-color 0.2s;
}

.approval-form textarea:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.approval-form textarea:disabled {
  background: #F3F4F6;
  color: #9CA3AF;
  cursor: not-allowed;
}

.action-buttons {
  display: flex;
  gap: 12px;
}

.btn {
  flex: 1;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-reject {
  background: #FEE2E2;
  color: #991B1B;
  border: 1px solid #FECACA;
}

.btn-reject:hover:not(:disabled) {
  background: #FECACA;
}

.btn-approve {
  background: #DCFCE7;
  color: #15803D;
  border: 1px solid #86EFAC;
}

.btn-approve:hover:not(:disabled) {
  background: #BBF7D0;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.status-info {
  padding: 12px;
  border-radius: 6px;
}

.status-info.approved {
  background: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}

.status-info.rejected {
  background: #FEF2F2;
  color: #991B1B;
  border: 1px solid #FECACA;
}

.status-info strong {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
}

.status-info p {
  margin: 0;
  font-size: 14px;
}

.text-success {
  color: #059669;
}

.text-danger {
  color: #DC2626;
}

.text-time {
  font-size: 12px;
  opacity: 0.8;
}

.notes {
  margin-top: 8px !important;
  padding-top: 8px;
  border-top: 1px solid currentColor;
  opacity: 0.9;
}

.notes strong {
  display: block;
  margin-bottom: 4px;
}

.alert {
  padding: 16px;
  border-radius: 8px;
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}

.alert-error {
  background: #FEE2E2;
  border: 1px solid #FECACA;
  color: #991B1B;
}

.alert svg {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.alert strong {
  display: block;
  margin-bottom: 4px;
}

.alert p {
  margin: 0;
  font-size: 14px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
