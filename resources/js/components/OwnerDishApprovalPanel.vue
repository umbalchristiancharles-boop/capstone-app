<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Dish Approval'"
    :panelDescription="'Review and approve new dishes from kitchen staff.'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    :showProfileColumn="false"
    :showAnnouncements="false"
    :showAttendanceCard="false"
    :singleColumnLayout="true"
    :fullWidth="true"
    @profile-updated="onProfileUpdated"
    @logout="confirmLogout"
  >
    <template #headerActions>
      <div class="dish-approval-header-actions">
        <button class="back-to-dashboard-btn" @click="goBackToOwnerPanel" title="Back to Owner Panel">
          <svg class="back-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          <span>Back to Dashboard</span>
        </button>
      </div>
    </template>

    <template #main>
      <div class="dish-approval-page">
      <!-- Pending Product Requests Section -->
      <section class="panel-block">
        <div class="panel-header">
          <h2>
            Pending Product Requests
            <span v-if="pendingProductRequests.length > 0" class="panel-badge">{{ pendingProductRequests.length }}</span>
          </h2>
          <button class="refresh-btn" @click="loadPendingProductRequests" :disabled="loadingProductRequests">
            {{ loadingProductRequests ? 'Loading...' : 'Refresh' }}
          </button>
        </div>
        <div class="panel-body">
          <div v-if="loadingProductRequests" class="muted">Loading pending product requests...</div>
          <div v-else-if="productError" class="alert-error">⚠️ {{ productError }}</div>
          <div v-else-if="pendingProductRequests.length === 0" class="muted">No pending product requests.</div>
          <div v-else class="products-grid">
            <div v-for="prodReq in pendingProductRequests" :key="prodReq.id" class="product-request-card">
              <div class="card-header">
                <div class="product-title-info">
                  <h3 class="product-name">{{ prodReq.name }}</h3>
                  <p class="product-meta">
                    Requested by <strong>{{ prodReq.requester?.full_name || 'Unknown' }}</strong>
                    <br>
                    <small>{{ formatDate(prodReq.created_at) }}</small>
                  </p>
                </div>
                <div class="badge badge-pending">PENDING APPROVAL</div>
              </div>

              <div class="card-body">
                <div v-if="prodReq.description" class="description-section">
                  <h4>Description</h4>
                  <p class="description-text">{{ prodReq.description }}</p>
                </div>

                <div v-if="prodReq.unit" class="unit-section">
                  <h4>Unit of Measurement</h4>
                  <p class="unit-text">{{ prodReq.unit }}</p>
                </div>

                <div class="approval-section">
                  <div class="approval-form">
                    <textarea
                      v-model="approvalNotesProduct[prodReq.id]"
                      :placeholder="`Optional notes for approval of ${prodReq.name}...`"
                      class="notes-input"
                      rows="3"
                    ></textarea>

                    <div class="approval-actions">
                      <button
                        class="btn-approve"
                        @click="approveProductRequest(prodReq.id)"
                        :disabled="approvingProductId === prodReq.id || rejectingProductId === prodReq.id"
                      >
                        {{ approvingProductId === prodReq.id ? '⏳ Approving...' : '✅ Approve' }}
                      </button>
                      <button
                        class="btn-reject"
                        @click="showRejectFormProduct(prodReq.id)"
                        :disabled="approvingProductId === prodReq.id || rejectingProductId === prodReq.id"
                      >
                        {{ rejectingProductId === prodReq.id ? '⏳ Rejecting...' : '❌ Reject' }}
                      </button>
                    </div>

                    <div v-if="showRejectReasonProduct[prodReq.id]" class="reject-reason-form">
                      <textarea
                        v-model="rejectReasonProduct[prodReq.id]"
                        placeholder="Please provide a reason for rejection..."
                        class="reason-input"
                        rows="2"
                      ></textarea>
                      <div class="reject-actions">
                        <button
                          class="btn-outline"
                          @click="cancelRejectProduct(prodReq.id)"
                          :disabled="rejectingProductId === prodReq.id"
                        >
                          Cancel
                        </button>
                        <button
                          class="btn-reject"
                          @click="confirmRejectProduct(prodReq.id)"
                          :disabled="!rejectReasonProduct[prodReq.id] || rejectingProductId === prodReq.id"
                        >
                          {{ rejectingProductId === prodReq.id ? '⏳ Rejecting...' : 'Confirm Rejection' }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Approved Product Requests Section -->
      <section class="panel-block">
        <div class="panel-header">
          <h2>Approved Product Requests</h2>
          <button class="refresh-btn" @click="loadApprovedProductRequests" :disabled="loadingApprovedProducts">
            {{ loadingApprovedProducts ? 'Loading...' : 'Refresh' }}
          </button>
        </div>
        <div class="panel-body">
          <div v-if="loadingApprovedProducts" class="muted">Loading approved product requests...</div>
          <div v-else-if="approvedProductRequests.length === 0" class="muted">No approved product requests yet.</div>
          <div v-else class="approved-products-table">
            <table>
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Unit</th>
                  <th>Requested by</th>
                  <th>Approved by</th>
                  <th>Approved On</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="prodReq in approvedProductRequests" :key="prodReq.id">
                  <td class="product-name-cell">
                    <strong>{{ prodReq.name }}</strong>
                  </td>
                  <td>{{ prodReq.unit || 'N/A' }}</td>
                  <td>{{ prodReq.requester?.full_name || 'Unknown' }}</td>
                  <td>{{ prodReq.approver?.full_name || 'Unknown' }}</td>
                  <td><small>{{ formatDate(prodReq.approved_at) }}</small></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
      </div>
    </template>

  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'

const router = useRouter()
const userProfile = ref({})
// Product Request state
const pendingProductRequests = ref([])
const approvedProductRequests = ref([])
const loadingProductRequests = ref(false)
const loadingApprovedProducts = ref(false)
const productError = ref('')
const approvingProductId = ref(null)
const rejectingProductId = ref(null)
const hasNotified = ref(false)

const approvalNotesProduct = ref({})
const rejectReasonProduct = ref({})
const showRejectReasonProduct = ref({})

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function loadUserProfile() {
  axios.get('/api/me')
    .then(response => {
      userProfile.value = response.data.data || response.data
    })
    .catch(err => {
      console.error('Failed to load user profile:', err)
    })
}

function confirmLogout() {
  // Use the centralized swalConfirmLogout helper to show a single confirmation
  try {
    if (typeof window.swalConfirmLogout === 'function') {
      window.swalConfirmLogout({ useApi: true })
      return
    }
  } catch (e) {}

  // Fallback if helper missing
  if (window.confirm('Are you sure you want to logout?')) {
    axios.post('/logout')
      .then(() => {
        window.location.href = '/login'
      })
  }
}

function onProfileUpdated() {
  loadUserProfile()
}

function goBackToOwnerPanel() {
  router.push('/owner-panel')
}

// Product Request Functions
function loadPendingProductRequests() {
  loadingProductRequests.value = true
  productError.value = ''
  axios.get('/api/owner/product-requests/pending')
    .then(response => {
      pendingProductRequests.value = response.data.data || []
      notifyIfPending()
    })
    .catch(err => {
      console.error('Failed to load pending product requests:', err)
      productError.value = err.response?.data?.message || 'Failed to load pending product requests'
    })
    .finally(() => {
      loadingProductRequests.value = false
    })
}

function notifyIfPending() {
  if (hasNotified.value) return
  const totalPending = pendingProductRequests.value?.length || 0
  if (totalPending > 0) {
    showToast('You have pending approvals to review.', 'info')
    hasNotified.value = true
  }
}

function loadApprovedProductRequests() {
  loadingApprovedProducts.value = true
  axios.get('/api/owner/product-requests/approved')
    .then(response => {
      approvedProductRequests.value = response.data.data || []
    })
    .catch(err => {
      console.error('Failed to load approved product requests:', err)
    })
    .finally(() => {
      loadingApprovedProducts.value = false
    })
}

function approveProductRequest(prodReqId) {
  if (!window.confirm('Are you sure you want to approve this product request?')) {
    return
  }

  approvingProductId.value = prodReqId
  const notes = approvalNotesProduct.value[prodReqId] || ''

  axios.post(`/api/owner/product-requests/${prodReqId}/approve`, {
    notes: notes
  })
    .then(response => {
      // Remove from pending and add to approved
      pendingProductRequests.value = pendingProductRequests.value.filter(p => p.id !== prodReqId)
      approvedProductRequests.value.unshift(response.data)
      approvalNotesProduct.value[prodReqId] = ''
      showRejectReasonProduct.value[prodReqId] = false

      alert('✅ Product request approved successfully! Product is now available for procurement.')
    })
    .catch(err => {
      console.error('Failed to approve product request:', err)
      alert('❌ Error: ' + (err.response?.data?.error || 'Failed to approve product request'))
    })
    .finally(() => {
      approvingProductId.value = null
    })
}

function showRejectFormProduct(prodReqId) {
  showRejectReasonProduct.value[prodReqId] = true
}

function cancelRejectProduct(prodReqId) {
  showRejectReasonProduct.value[prodReqId] = false
  rejectReasonProduct.value[prodReqId] = ''
}

function confirmRejectProduct(prodReqId) {
  const reason = rejectReasonProduct.value[prodReqId] || ''
  if (!reason.trim()) {
    alert('Please provide a reason for rejection')
    return
  }

  if (!window.confirm('Are you sure you want to reject this product request?')) {
    return
  }

  rejectingProductId.value = prodReqId

  axios.post(`/api/owner/product-requests/${prodReqId}/reject`, {
    notes: reason
  })
    .then(response => {
      // Remove from pending
      pendingProductRequests.value = pendingProductRequests.value.filter(p => p.id !== prodReqId)
      rejectReasonProduct.value[prodReqId] = ''
      showRejectReasonProduct.value[prodReqId] = false

      alert('❌ Product request rejected. The logistics manager will be notified.')
    })
    .catch(err => {
      console.error('Failed to reject product request:', err)
      alert('Error: ' + (err.response?.data?.error || 'Failed to reject product request'))
    })
    .finally(() => {
      rejectingProductId.value = null
    })
}

onMounted(() => {
  loadUserProfile()
  loadPendingProductRequests()
  loadApprovedProductRequests()
})
</script>

<style scoped>
.dish-approval-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  width: 100%;
  max-width: 100%;
  padding: 0.15rem 0 1.5rem;
}

.panel-block {
  margin-bottom: 0;
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(255, 106, 61, 0.16);
  border-radius: 24px;
  box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
  overflow: hidden;
  backdrop-filter: blur(16px);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0;
  padding: 1.2rem 1.4rem 1rem;
  border-bottom: 1px solid rgba(255, 106, 61, 0.12);
  background: linear-gradient(90deg, rgba(255, 244, 235, 0.95), rgba(255, 255, 255, 0.98));
}

.panel-header h2 {
  position: relative;
  margin: 0;
  font-size: 1.35rem;
  color: #1f2937;
  letter-spacing: -0.02em;
}

.panel-badge {
  position: absolute;
  top: -8px;
  right: -18px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: linear-gradient(135deg, #fb923c, #ef4444);
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px rgba(239, 68, 68, 0.24);
}

.panel-body {
  padding: 1.2rem 1.4rem 1.4rem;
}

.dish-approval-header-actions {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 20;
  display: flex;
  justify-content: flex-end;
  align-items: flex-start;
  width: auto;
}

:deep(.admin-main-header) {
  position: relative;
  padding-bottom: 0.65rem;
}

:deep(.admin-main-header .header-left-slot) {
  display: none;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1rem;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(255, 106, 61, 0.12), rgba(251, 191, 36, 0.16));
  color: #c2410c;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.92rem;
  line-height: 1;
  box-shadow: none;
  border: 0;
  transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
}

.back-to-dashboard-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(255, 106, 61, 0.16);
  opacity: 0.95;
}

.back-icon {
  flex-shrink: 0;
}

.refresh-btn {
  padding: 0.6rem 1rem;
  background: linear-gradient(135deg, #ff6a3d, #f59e0b);
  color: white;
  border: none;
  border-radius: 999px;
  cursor: pointer;
  font-weight: 700;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
  box-shadow: 0 10px 20px rgba(255, 106, 61, 0.18);
}

.refresh-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 12px 24px rgba(255, 106, 61, 0.24);
}

.refresh-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.muted {
  color: #64748b;
  text-align: center;
  padding: 2rem 1rem;
  font-size: 0.95rem;
}

.alert-error {
  background: linear-gradient(90deg, rgba(254, 242, 242, 0.95), rgba(255, 247, 247, 0.95));
  color: #b91c1c;
  padding: 1rem 1.1rem;
  border-radius: 14px;
  margin-bottom: 1rem;
  border: 1px solid rgba(248, 113, 113, 0.24);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1rem;
}

.product-request-card {
  background: linear-gradient(180deg, #fffdfb 0%, #ffffff 100%);
  border: 1px solid rgba(251, 146, 60, 0.18);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-request-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.2rem 1.2rem 1rem;
  background: linear-gradient(90deg, rgba(255, 247, 237, 0.95), rgba(255, 250, 244, 0.95));
  border-bottom: 1px solid rgba(255, 106, 61, 0.12);
}

.product-title-info {
  flex: 1;
}

.product-name {
  margin: 0 0 0.45rem 0;
  font-size: 1.15rem;
  color: #111827;
}

.product-meta {
  margin: 0;
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.4;
}

.badge {
  display: inline-block;
  padding: 0.42rem 0.75rem;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 800;
  white-space: nowrap;
}

.badge-pending {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  color: #92400e;
  box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.12);
}

.card-body {
  padding: 1.1rem 1.2rem 1.2rem;
}

.description-section,
.unit-section {
  margin-bottom: 0.95rem;
}

.description-section h4,
.unit-section h4 {
  margin: 0 0 0.4rem 0;
  font-size: 0.8rem;
  font-weight: 700;
  color: #f97316;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.description-text,
.unit-text {
  margin: 0;
  color: #374151;
  line-height: 1.55;
}

.approval-section {
  border-top: 1px solid rgba(255, 106, 61, 0.12);
  padding-top: 0.95rem;
}

.approval-form {
  display: grid;
  gap: 0.9rem;
}

.notes-input,
.reason-input {
  width: 100%;
  padding: 0.8rem 0.9rem;
  border: 1px solid #f4c78f;
  border-radius: 12px;
  background: #fffaf4;
  font-family: inherit;
  font-size: 0.92rem;
  resize: vertical;
  color: #111827;
}

.notes-input:focus,
.reason-input:focus {
  outline: none;
  border-color: #ff8a3d;
  box-shadow: 0 0 0 3px rgba(255, 106, 61, 0.12);
}

.approval-actions,
.reject-actions {
  display: flex;
  gap: 0.7rem;
}

.btn-approve,
.btn-reject,
.btn-outline {
  flex: 1;
  padding: 0.75rem 0.9rem;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
  font-size: 0.9rem;
}

.btn-approve {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-approve:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 16px rgba(5, 150, 105, 0.2);
}

.btn-reject {
  background: linear-gradient(135deg, #f43f5e, #dc2626);
  color: white;
}

.btn-reject:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 16px rgba(220, 38, 38, 0.2);
}

.btn-outline {
  background: white;
  border: 1px solid rgba(251, 146, 60, 0.24);
  color: #4b5563;
}

.btn-outline:hover:not(:disabled) {
  background: #fff7ed;
}

.btn-approve:disabled,
.btn-reject:disabled,
.btn-outline:disabled {
  opacity: 0.67;
  cursor: not-allowed;
}

.reject-reason-form {
  display: grid;
  gap: 0.8rem;
  padding: 0.95rem;
  background: linear-gradient(90deg, rgba(255, 247, 237, 0.95), rgba(254, 242, 242, 0.95));
  border: 1px solid rgba(251, 146, 60, 0.18);
  border-radius: 16px;
  margin-top: 0.6rem;
}

.approved-products-table {
  overflow-x: auto;
  border: 1px solid rgba(251, 146, 60, 0.14);
  border-radius: 18px;
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
}

thead {
  background: linear-gradient(90deg, rgba(255, 247, 237, 0.95), rgba(255, 250, 244, 0.95));
  border-bottom: 1px solid rgba(255, 106, 61, 0.12);
}

th {
  padding: 0.95rem 1rem;
  text-align: left;
  font-weight: 700;
  color: #4b5563;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

tbody tr {
  border-bottom: 1px solid #f3f4f6;
  transition: background 0.2s ease;
}

tbody tr:hover {
  background: #fffaf4;
}

td {
  padding: 0.95rem 1rem;
  color: #111827;
}

.product-name-cell {
  font-weight: 700;
  color: #111827;
}

small {
  font-size: 0.8rem;
  color: #6b7280;
}

@media (max-width: 768px) {
  .panel-header {
    padding: 1rem 1rem 0.9rem;
  }

  .panel-body {
    padding: 1rem;
  }

  .products-grid {
    grid-template-columns: 1fr;
  }

  .approval-actions,
  .reject-actions {
    flex-direction: column;
  }
}
</style>
