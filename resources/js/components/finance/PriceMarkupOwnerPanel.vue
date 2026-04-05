<template>
  <div class="price-markup-owner-section">
    <div class="section-header">
      <h2>
        Price Markup Percentage Approvals
        <span v-if="pendingCount > 0" class="panel-badge">{{ pendingCount }}</span>
      </h2>
      <p>Final approval authority for price markup percentage changes</p>
      <button class="refresh-btn" @click="fetchPendingRequests" :disabled="isLoading">
        <span v-if="!isLoading">↻ Refresh</span>
        <span v-else class="btn-loading"><span class="spinner"></span> Refreshing...</span>
      </button>
    </div>

    <!-- Pending Approvals Tab -->
    <div class="tabs">
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'pending' }"
        @click="activeTab = 'pending'"
      >
        <span class="tab-label">Awaiting Approval</span>
        <span v-if="pendingCount > 0" class="badge">{{ pendingCount }}</span>
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'history' }"
        @click="activeTab = 'history'"
      >
        <span class="tab-label">Approval History</span>
      </button>
    </div>

    <!-- Tab: Pending Approvals -->
    <div v-show="activeTab === 'pending'" class="tab-content">
      <!-- Loading State -->
      <div v-if="isLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading pending approvals...</p>
      </div>

      <!-- No Pending Approvals -->
      <div v-else-if="pendingRequests.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>All pending approvals have been processed.</p>
      </div>

      <!-- Pending Requests List -->
      <div v-else class="requests-grid">
        <div v-for="request in pendingRequests" :key="request.id" class="approval-card">
          <div class="card-header">
            <div>
              <h3>{{ request.branch?.name }}</h3>
              <p>Requested by {{ request.requested_by?.full_name }}</p>
            </div>
            <span class="status-badge status-awaiting">Awaiting Your Decision</span>
          </div>

          <div class="card-body">
            <!-- Approval Timeline -->
            <div class="approval-timeline">
              <div class="timeline-item completed">
                <div class="timeline-marker">✓</div>
                <div class="timeline-content">
                  <strong>Requested by Finance Manager</strong>
                  <p>{{ formatDate(request.created_at) }}</p>
                </div>
              </div>

              <div class="timeline-connector"></div>

              <div class="timeline-item completed">
                <div class="timeline-marker">✓</div>
                <div class="timeline-content">
                  <strong>Approved by Main Finance</strong>
                  <p v-if="request.main_finance_approved_at">
                    {{ formatDate(request.main_finance_approved_at) }}
                  </p>
                  <p v-if="request.main_finance_notes" class="notes-text">
                    {{ request.main_finance_notes }}
                  </p>
                </div>
              </div>

              <div class="timeline-connector"></div>

              <div class="timeline-item pending">
                <div class="timeline-marker">⏳</div>
                <div class="timeline-content">
                  <strong>Awaiting Owner Final Approval</strong>
                  <p>Your decision required</p>
                </div>
              </div>
            </div>

            <!-- Requested Change -->
            <div class="change-summary">
              <div class="change-box">
                <div class="old-value">
                  <span class="label">Current</span>
                  <span class="value">{{ request.current_percentage }}%</span>
                  <span class="multiplier">×{{ (1 + request.current_percentage / 100).toFixed(2) }}</span>
                </div>
                <div class="arrow">→</div>
                <div class="new-value">
                  <span class="label">Proposed</span>
                  <span class="value">{{ request.requested_percentage }}%</span>
                  <span class="multiplier">×{{ (1 + request.requested_percentage / 100).toFixed(2) }}</span>
                </div>
              </div>
            </div>

            <!-- Reason -->
            <div v-if="request.reason" class="reason-box">
              <strong>Business Reason:</strong>
              <p>{{ request.reason }}</p>
            </div>

            <!-- Previous Notes -->
            <div v-if="request.main_finance_notes" class="notes-box">
              <strong>Main Finance Notes:</strong>
              <p>{{ request.main_finance_notes }}</p>
            </div>
          </div>

          <!-- Decision Section -->
          <div class="card-footer">
            <textarea
              v-model="requestActions[request.id].notes"
              placeholder="Add your notes for the record..."
              rows="3"
              :disabled="requestActions[request.id].isProcessing"
            ></textarea>

            <div class="action-buttons">
              <button
                class="btn btn-reject"
                @click="ownerApprove(request, false)"
                :disabled="requestActions[request.id].isProcessing"
              >
                <span v-if="requestActions[request.id].isProcessing" class="btn-loading">
                  <span class="spinner"></span> Processing...
                </span>
                <span v-else>Reject</span>
              </button>
              <button
                class="btn btn-approve"
                @click="ownerApprove(request, true)"
                :disabled="requestActions[request.id].isProcessing"
              >
                <span v-if="requestActions[request.id].isProcessing" class="btn-loading">
                  <span class="spinner"></span> Processing...
                </span>
                <span v-else>Approve & Activate</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab: History -->
    <div v-show="activeTab === 'history'" class="tab-content">
      <div v-if="isLoadingHistory" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading approval history...</p>
      </div>

      <div v-else-if="historyRequests.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        <p>No approval history available.</p>
      </div>

      <div v-else class="history-list">
        <div v-for="request in historyRequests" :key="request.id" class="history-item">
          <div class="history-header">
            <h4>{{ request.branch?.name }}</h4>
            <span :class="['history-badge', request.owner_approval === 'approved' ? 'approved' : 'rejected']">
              {{ request.owner_approval === 'approved' ? '✓ Approved' : '✗ Rejected' }}
            </span>
          </div>

          <div class="history-body">
            <div class="history-row">
              <span class="label">Change:</span>
              <span class="value">
                {{ request.current_percentage }}% →
                <strong>{{ request.requested_percentage }}%</strong>
              </span>
            </div>
            <div class="history-row">
              <span class="label">Requested by:</span>
              <span class="value">{{ request.requested_by?.full_name }}</span>
            </div>
            <div class="history-row">
              <span class="label">Activated:</span>
              <span class="value">{{ formatDate(request.activated_at) }}</span>
            </div>
            <div v-if="request.reason" class="history-row">
              <span class="label">Reason:</span>
              <span class="value">{{ request.reason }}</span>
            </div>
            <div v-if="request.owner_notes" class="history-row">
              <span class="label">Your Notes:</span>
              <span class="value">{{ request.owner_notes }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <transition name="fade">
      <div v-if="errorMessage" class="alert alert-error">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { showToast } from '../toastStore'

// State
const activeTab = ref('pending')
const pendingRequests = ref([])
const historyRequests = ref([])
const isLoading = ref(false)
const isLoadingHistory = ref(false)
const errorMessage = ref('')
const requestActions = reactive({})
const hasNotified = ref(false)

// Computed
const pendingCount = computed(() => pendingRequests.value.length)

// Methods
async function fetchPendingRequests() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const res = await axios.get('/api/price-markup/pending')

    if (res.data.ok) {
      // Filter only requests awaiting owner approval
      pendingRequests.value = res.data.requests.filter(
        r => r.main_finance_approval === 'approved' && r.owner_approval === 'pending'
      )

      // Initialize action state
      pendingRequests.value.forEach(req => {
        if (!requestActions[req.id]) {
          requestActions[req.id] = {
            notes: '',
            isProcessing: false
          }
        }
      })

      notifyIfPending()
    }
  } catch (error) {
    console.error('Error fetching pending requests:', error)
    errorMessage.value = 'Failed to load pending approvals.'
  } finally {
    isLoading.value = false
  }
}

function notifyIfPending() {
  if (hasNotified.value) return
  if ((pendingRequests.value || []).length > 0) {
    showToast('You have pending price markup approvals.', 'info')
    hasNotified.value = true
  }
}

async function fetchHistory() {
  isLoadingHistory.value = true

  try {
    const res = await axios.get(`/api/price-markup/history/0`)

    if (res.data.ok) {
      historyRequests.value = res.data.history
    }
  } catch (error) {
    console.error('Error fetching history:', error)
  } finally {
    isLoadingHistory.value = false
  }
}

async function ownerApprove(request, approved) {
  const action = requestActions[request.id]
  action.isProcessing = true
  errorMessage.value = ''

  try {
    const res = await axios.post(
      `/api/price-markup/${request.id}/owner-approve`,
      {
        approved: approved,
        notes: action.notes
      }
    )

    if (res.data.ok) {
      // Refresh data
      await fetchPendingRequests()
      await fetchHistory()
      action.notes = ''

      if (window.showToast) {
        window.showToast(res.data.message, 'success')
      }
    }
  } catch (error) {
    console.error('Error processing owner approval:', error)
    errorMessage.value =
      error.response?.data?.message ||
      'Failed to process your decision.'
  } finally {
    action.isProcessing = false
  }
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
  fetchPendingRequests()
  fetchHistory()

  const interval = setInterval(() => {
    fetchPendingRequests()
  }, 30000)

  onUnmounted(() => clearInterval(interval))
})
</script>

<style scoped>
.price-markup-owner-section {
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

.tabs {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid #E5E7EB;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  color: #6B7280;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.tab-btn:hover {
  color: #1F2937;
}

.tab-btn.active {
  color: #059669;
  border-bottom-color: #059669;
}

.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  padding: 0 6px;
  background: #EF4444;
  color: white;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

.tab-content {
  animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
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
  gap: 20px;
}

.approval-card {
  background: white;
  border: 2px solid #E5E7EB;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding: 20px;
  background: linear-gradient(135deg, #F0FDF4 0%, #F3E8FF 100%);
  border-bottom: 1px solid #E5E7EB;
}

.card-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1F2937;
  margin: 0 0 4px 0;
}

.card-header p {
  font-size: 13px;
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

.status-awaiting {
  background: #FEF08A;
  color: #92400E;
}

.card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.approval-timeline {
  position: relative;
  padding-left: 40px;
}

.timeline-item {
  position: relative;
  margin-bottom: 20px;
  display: flex;
  gap: 12px;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -30px;
  top: 30px;
  width: 2px;
  height: 20px;
  background: #E5E7EB;
}

.timeline-item.completed::before {
  background: #10B981;
}

.timeline-marker {
  position: absolute;
  left: -40px;
  top: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #E5E7EB;
  border-radius: 50%;
  font-size: 14px;
  font-weight: 700;
  color: #6B7280;
}

.timeline-item.completed .timeline-marker {
  background: #10B981;
  color: white;
}

.timeline-item.pending .timeline-marker {
  background: #FEF08A;
  color: #92400E;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.timeline-content strong {
  display: block;
  font-size: 14px;
  color: #1F2937;
  margin-bottom: 4px;
}

.timeline-content p {
  margin: 0;
  font-size: 12px;
  color: #6B7280;
}

.notes-text {
  color: #3B82F6;
  font-style: italic;
}

.timeline-connector {
  height: 1px;
  background: #E5E7EB;
  margin: 10px 0;
}

.change-summary {
  background: #F0FDF4;
  border: 2px solid #86EFAC;
  border-radius: 8px;
  padding: 16px;
}

.change-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.old-value,
.new-value {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex: 1;
  padding: 12px;
  background: white;
  border-radius: 6px;
}

.old-value .label,
.new-value .label {
  font-size: 12px;
  font-weight: 600;
  color: #6B7280;
}

.old-value .value,
.new-value .value {
  font-size: 28px;
  font-weight: 700;
  color: #1F2937;
}

.old-value .multiplier,
.new-value .multiplier {
  font-size: 12px;
  color: #059669;
  font-weight: 600;
}

.new-value {
  background: #ECFDF5;
}

.arrow {
  font-size: 24px;
  color: #6B7280;
  font-weight: 700;
}

.reason-box,
.notes-box {
  border-left: 3px solid #3B82F6;
  padding: 12px 16px;
  background: #EFF6FF;
  border-radius: 6px;
}

.reason-box strong,
.notes-box strong {
  display: block;
  color: #1E40AF;
  margin-bottom: 6px;
  font-size: 13px;
}

.reason-box p,
.notes-box p {
  margin: 0;
  color: #1F2937;
  font-size: 14px;
}

.card-footer {
  padding: 20px;
  background: #F9FAFB;
  border-top: 1px solid #E5E7EB;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.card-footer textarea {
  padding: 10px;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  font-size: 13px;
  font-family: inherit;
  resize: vertical;
}

.card-footer textarea:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.action-buttons {
  display: flex;
  gap: 12px;
}

.btn {
  flex: 1;
  padding: 12px 16px;
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
}

.btn-reject:hover:not(:disabled) {
  background: #FEE2E2;
  border: 1px solid #FCA5A5;
}

.btn-approve {
  background: #10B981;
  color: white;
}

.btn-approve:hover:not(:disabled) {
  background: #059669;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.history-list {
  display: grid;
  gap: 16px;
}

.history-item {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  overflow: hidden;
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}

.history-header h4 {
  font-size: 15px;
  font-weight: 700;
  color: #1F2937;
  margin: 0;
}

.history-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.history-badge.approved {
  background: #DCFCE7;
  color: #15803D;
}

.history-badge.rejected {
  background: #FEE2E2;
  color: #991B1B;
}

.history-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.history-row {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.history-row .label {
  font-weight: 600;
  color: #6B7280;
  min-width: 100px;
  font-size: 13px;
}

.history-row .value {
  color: #1F2937;
  font-size: 13px;
}

.alert {
  padding: 16px;
  border-radius: 8px;
  display: flex;
  gap: 12px;
  margin-top: 20px;
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
