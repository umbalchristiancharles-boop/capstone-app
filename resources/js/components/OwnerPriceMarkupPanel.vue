<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Price Markup Approvals'"
    :panelDescription="'Review and approve price markup percentage changes for all branches'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    :showProfileColumn="false"
    :showAnnouncements="false"
    :ownerTwoColumnLayout="true"
    @profile-updated="onProfileUpdated"
    @logout="confirmLogout"
  >
    <template #headerLeft>
      <button class="back-btn" @click="goBackToOwnerPanel" title="Back to Owner Panel">
        ← Back
      </button>
    </template>

    <template #main>
      <div class="centered-wrapper">
        <section class="panel-block">
        <div class="panel-header">
          <h2>
            Pending Price Markup Approvals
            <span v-if="pendingRequests.length > 0" class="panel-badge">{{ pendingRequests.length }}</span>
          </h2>
          <button class="refresh-btn" @click="loadPendingRequests" :disabled="loading">
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
        </div>

        <div v-if="loading" class="muted">Loading pending approvals...</div>
        <div v-else-if="error" class="alert-error">⚠️ {{ error }}</div>
        <div v-else-if="pendingRequests.length === 0" class="muted">
          <p>No pending price markup requests awaiting your approval.</p>
          <p style="font-size: 0.9em; opacity: 0.7;">Only requests approved by the Main Finance Manager will appear here.</p>
        </div>

        <div v-else class="requests-list">
          <div v-for="req in pendingRequests" :key="req.id" class="request-card">
            <div class="card-header">
              <div class="request-info">
                <div class="request-title">
                  <h3>Branch: {{ req.branch?.name || 'Unknown' }}</h3>
                  <p class="request-meta">
                    Requested by <strong>{{ req.requested_by?.full_name || 'Unknown' }}</strong>
                    <br>
                    <small>{{ formatDate(req.created_at) }}</small>
                  </p>
                </div>
                <div class="change-summary">
                  <div class="change-from-to">
                    <span class="percentage">{{ req.current_percentage }}%</span>
                    <span class="arrow">→</span>
                    <span class="percentage highlight">{{ req.requested_percentage }}%</span>
                  </div>
                  <div class="multiplier-info">
                    ×{{ (req.current_percentage / 100 + 1).toFixed(2) }} → ×{{ (req.requested_percentage / 100 + 1).toFixed(2) }}
                  </div>
                </div>
              </div>
              <div class="badge badge-pending">FINAL APPROVAL NEEDED</div>
            </div>

            <div class="card-body">
              <div v-if="req.reason" class="reason-section">
                <h4>Reason for Change</h4>
                <p>{{ req.reason }}</p>
              </div>

              <div class="approval-timeline">
                <h4>Approval Timeline</h4>
                <div class="timeline">
                  <div class="timeline-item completed">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <strong>Finance Manager Requested</strong>
                      <span class="timeline-date">{{ formatDate(req.created_at) }}</span>
                      <span class="timeline-user">by {{ req.requested_by?.full_name }}</span>
                    </div>
                  </div>

                  <div class="timeline-item completed">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <strong>Main Finance Manager Approved</strong>
                      <span class="timeline-date">{{ formatDate(req.main_finance_approved_at) }}</span>
                      <span class="timeline-user">by {{ req.main_finance_approved_by?.full_name }}</span>
                      <div v-if="req.main_finance_notes" class="timeline-notes">
                        Notes: {{ req.main_finance_notes }}
                      </div>
                    </div>
                  </div>

                  <div class="timeline-item pending">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <strong>Waiting for Owner Final Approval</strong>
                      <span class="timeline-date">Your decision needed</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="approval-form">
                <div class="form-group">
                  <label>Final Approval Notes (Optional)</label>
                  <textarea
                    v-model="approvalNotes[req.id]"
                    placeholder="Add any final comments or approval notes..."
                    rows="3"
                    class="notes-input"
                  ></textarea>
                </div>

                <div class="approval-actions">
                  <button
                    class="btn-approve"
                    @click="approveRequest(req.id)"
                    :disabled="approvingId === req.id || rejectingId === req.id"
                  >
                    {{ approvingId === req.id ? '⏳ Processing...' : '✅ Approve & Activate' }}
                  </button>
                  <button
                    class="btn-reject"
                    @click="showRejectForm(req.id)"
                    :disabled="approvingId === req.id || rejectingId === req.id"
                  >
                    {{ rejectingId === req.id ? '⏳ Processing...' : '❌ Reject' }}
                  </button>
                </div>

                <div v-if="showRejectReason[req.id]" class="reject-reason-form">
                  <label>Rejection Reason (Required)</label>
                  <textarea
                    v-model="rejectReason[req.id]"
                    placeholder="Please provide a reason for rejection..."
                    rows="2"
                    class="reason-input"
                  ></textarea>
                  <div class="reject-actions">
                    <button
                      class="btn-outline"
                      @click="cancelReject(req.id)"
                      :disabled="rejectingId === req.id"
                    >
                      Cancel
                    </button>
                    <button
                      class="btn-reject-confirm"
                      @click="rejectRequest(req.id)"
                      :disabled="!rejectReason[req.id] || rejectingId === req.id"
                    >
                      {{ rejectingId === req.id ? 'Processing...' : 'Confirm Rejection' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        </section>
      </div>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { showToast } from './toastStore'

const router = useRouter()
const userProfile = ref({})
const pendingRequests = ref([])
const approvalNotes = ref({})
const rejectReason = ref({})
const showRejectReason = ref({})
const approvingId = ref(null)
const rejectingId = ref(null)
const loading = ref(false)
const error = ref('')
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

async function loadProfile() {
  try {
    const res = await axios.get('/api/me', { withCredentials: true })
    if (res.data?.ok) {
      userProfile.value = res.data.user || {}
    }
  } catch (e) {
    console.error('Failed to load profile:', e)
  }
}

async function loadPendingRequests() {
  loading.value = true
  error.value = ''
  try {
    const res = await axios.get('/api/price-markup/pending', { withCredentials: true })
    if (res.data?.ok) {
      // Filter to only show requests that have main finance approval but no owner approval yet
      pendingRequests.value = (res.data.requests || []).filter(
        req => req.main_finance_approval === 'approved' && req.owner_approval === 'pending'
      )
      notifyIfPending()
    }
  } catch (e) {
    error.value = 'Failed to load pending requests'
    console.error('Error loading requests:', e)
  } finally {
    loading.value = false
  }
}

function notifyIfPending() {
  if (hasNotified.value) return
  if ((pendingRequests.value || []).length > 0) {
    showToast('You have pending price markup approvals.', 'info')
    hasNotified.value = true
  }
}

function showRejectForm(requestId) {
  showRejectReason.value[requestId] = true
  rejectReason.value[requestId] = ''
}

function cancelReject(requestId) {
  showRejectReason.value[requestId] = false
  rejectReason.value[requestId] = ''
}

async function approveRequest(requestId) {
  approvingId.value = requestId
  try {
    const response = await axios.post(
      `/api/price-markup/${requestId}/owner-approve`,
      {
        approved: true,
        notes: approvalNotes.value[requestId] || null,
      },
      { withCredentials: true }
    )

    if (response.data?.ok) {
      // Show success toast
      if (window.swalAlert) {
        window.swalAlert('✅ Approved!', response.data.message, 'success')
      }
      await loadPendingRequests()
      approvalNotes.value[requestId] = ''
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to approve request'
    if (window.swalAlert) {
      window.swalAlert('⚠️ Error', msg, 'error')
    }
    console.error('Approval failed:', error)
  } finally {
    approvingId.value = null
  }
}

async function rejectRequest(requestId) {
  if (!rejectReason.value[requestId]) {
    if (window.swalAlert) {
      window.swalAlert('⚠️ Required', 'Please provide a rejection reason', 'warning')
    }
    return
  }

  rejectingId.value = requestId
  try {
    const response = await axios.post(
      `/api/price-markup/${requestId}/owner-approve`,
      {
        approved: false,
        notes: rejectReason.value[requestId],
      },
      { withCredentials: true }
    )

    if (response.data?.ok) {
      if (window.swalAlert) {
        window.swalAlert('✅ Rejected', 'Request rejected successfully', 'success')
      }
      await loadPendingRequests()
      rejectReason.value[requestId] = ''
      showRejectReason.value[requestId] = false
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to reject request'
    if (window.swalAlert) {
      window.swalAlert('⚠️ Error', msg, 'error')
    }
    console.error('Rejection failed:', error)
  } finally {
    rejectingId.value = null
  }
}

function goBackToOwnerPanel() {
  router.push('/owner-panel')
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

async function confirmLogout() {
  try {
    const ok = await (window.swalConfirm
      ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout')
      : Promise.resolve(false))
    if (ok) {
      await axios.post('/api/logout', {}, { withCredentials: true })
      try {
        localStorage.clear()
        sessionStorage.clear()
      } catch (e) {}
      setTimeout(() => {
        window.location.replace('/staff-landing')
      }, 350)
    }
  } catch (e) {
    console.error('Logout failed:', e)
  }
}

onMounted(async () => {
  await loadProfile()
  await loadPendingRequests()
})
</script>

<style scoped>
.centered-wrapper {
  max-width: 1000px;
  margin: 0 auto;
  width: 100%;
}

.panel-block {
  background: linear-gradient(135deg, #ffffff 0%, #faf5f1 100%);
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 8px 32px rgba(255, 107, 28, 0.12), 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 2px solid rgba(255, 107, 28, 0.1);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
  padding-bottom: 20px;
  border-bottom: 2px solid rgba(255, 107, 28, 0.15);
}

.panel-header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: var(--orange);
  letter-spacing: -0.5px;
  position: relative;
}

.panel-badge { position:absolute; top:-8px; right:-18px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }

.refresh-btn {
  padding: 10px 20px;
  background: var(--orange);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(255, 107, 28, 0.3);
  font-size: 14px;
}

.refresh-btn:hover:not(:disabled) {
  background: #ff8a48;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 107, 28, 0.4);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.back-btn {
  padding: 10px 14px;
  background: var(--orange);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(255, 107, 28, 0.2);
}

.back-btn:hover {
  background: #ff8a48;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 107, 28, 0.3);
}

.muted {
  color: #6b7280;
  padding: 48px 30px;
  text-align: center;
  font-size: 15px;
  background: linear-gradient(to bottom, #ffffff 0%, #faf5f1 100%);
  border-radius: 12px;
  border: 2px dashed rgba(255, 107, 28, 0.1);
}

.alert-error {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(239, 68, 68, 0.04) 100%);
  border: 2px solid rgba(239, 68, 68, 0.2);
  color: #dc2626;
  padding: 14px 18px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: 500;
}

.requests-list {
  display: grid;
  gap: 16px;
}

.request-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(to bottom, #ffffff 0%, #fef9f6 100%);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
}

.request-card:hover {
  border-color: var(--orange);
  box-shadow: 0 12px 32px rgba(255, 107, 28, 0.2);
  transform: translateY(-4px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  padding: 18px;
  background: linear-gradient(135deg, #ffffff 0%, rgba(255, 107, 28, 0.02) 100%);
  border-bottom: 2px solid rgba(255, 107, 28, 0.1);
}

.request-info {
  flex: 1;
  display: grid;
  grid-template-columns: 1fr auto;
  position: relative;
  gap: 16px;
  align-items: start;
}

.panel-badge { position:absolute; top:-8px; right:-18px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }

.request-title {
  margin: 0;
}

.request-title h3 {
  margin: 0 0 6px;
  font-size: 18px;
  font-weight: 700;
  color: var(--orange);
  letter-spacing: -0.3px;
}

.request-meta {
  margin: 0;
  font-size: 12px;
  color: #6b7280;
}

.change-summary {
  text-align: right;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.change-from-to {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.percentage {
  font-size: 20px;
  color: #6b7280;
  font-weight: 600;
}

.percentage.highlight {
  color: var(--orange);
  font-size: 24px;
  font-weight: 700;
}

.arrow {
  color: #d1d5db;
}

.multiplier-info {
  font-size: 12px;
  color: #6b7280;
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-pending {
  background: var(--orange);
  color: white;
  box-shadow: 0 4px 12px rgba(255, 107, 28, 0.3);
}

.card-body {
  padding: 16px;
}

.reason-section {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.reason-section h4 {
  margin: 0 0 8px;
  font-size: 13px;
  text-transform: uppercase;
  color: #6b7280;
  letter-spacing: 0.5px;
}

.reason-section p {
  margin: 0;
  color: #374151;
  font-size: 14px;
}

.approval-timeline {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.approval-timeline h4 {
  margin: 0 0 12px;
  font-size: 13px;
  text-transform: uppercase;
  color: #6b7280;
  letter-spacing: 0.5px;
}

.timeline {
  position: relative;
  padding-left: 32px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 7px;
  top: 20px;
  bottom: 0;
  width: 2px;
  background: #e5e7eb;
}

.timeline-item {
  margin-bottom: 16px;
  position: relative;
}

.timeline-marker {
  position: absolute;
  left: -29px;
  top: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #e5e7eb;
  border: 2px solid white;
}

.timeline-item.completed .timeline-marker {
  background: #22c55e;
}

.timeline-item.pending .timeline-marker {
  background: var(--orange);
}

.timeline-content strong {
  display: block;
  font-size: 13px;
  color: var(--text-dark);
  margin-bottom: 4px;
}

.timeline-date {
  display: block;
  font-size: 12px;
  color: #6b7280;
}

.timeline-user {
  display: block;
  font-size: 12px;
  color: #9ca3af;
}

.timeline-notes {
  margin-top: 6px;
  padding: 8px;
  background: #f3f4f6;
  border-radius: 4px;
  font-size: 12px;
  color: #374151;
  font-style: italic;
}

.approval-form {
  display: grid;
  gap: 14px;
}

.form-group label {
  display: block;
  font-weight: 700;
  font-size: 14px;
  color: var(--orange);
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.notes-input,
.reason-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: inherit;
  font-size: 14px;
  resize: vertical;
}

.notes-input:focus,
.reason-input:focus {
  outline: none;
  border-color: var(--orange);
  box-shadow: 0 0 0 3px rgba(255, 107, 28, 0.08);
}

.approval-actions {
  display: flex;
  gap: 10px;
}

.btn-approve,
.btn-reject {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.btn-approve {
  background: #22c55e;
  color: white;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.btn-approve:hover:not(:disabled) {
  background: #16a34a;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
}

.btn-reject {
  background: #ef4444;
  color: white;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-reject:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

.btn-approve:disabled,
.btn-reject:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.reject-reason-form {
  background: #fef3f2;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 6px;
  padding: 12px;
  display: grid;
  gap: 10px;
}

.reject-reason-form label {
  display: block;
  font-weight: 600;
  font-size: 13px;
  color: #dc2626;
  margin-bottom: 4px;
}

.reject-actions {
  display: flex;
  gap: 10px;
}

.btn-outline {
  padding: 8px 14px;
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #f9fafb;
}

.btn-reject-confirm {
  padding: 8px 14px;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-reject-confirm:hover:not(:disabled) {
  background: #991b1b;
}

.btn-reject-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    gap: 12px;
  }

  .request-info {
    grid-template-columns: 1fr;
  }

  .change-summary {
    text-align: left;
  }

  .approval-actions {
    flex-direction: column;
  }

  .btn-approve,
  .btn-reject {
    width: 100%;
  }
}

.centered-wrapper {
  padding: 0 12px;
}
</style>
