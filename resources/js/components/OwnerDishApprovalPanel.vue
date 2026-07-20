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
.panel-block {
  margin-bottom: 2rem;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}

.panel-header h2 { position: relative; }
.panel-badge { position:absolute; top:-8px; right:-18px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }

.panel-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #111827;
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
}

:deep(.admin-main-header .header-left-slot) {
  display: none;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 10px;
  background: transparent;
  color: #ff6a3d;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.92rem;
  line-height: 1;
  box-shadow: none;
  border: 0;
  transition: transform 0.18s ease, opacity 0.18s ease;
}

.back-to-dashboard-btn:hover {
  transform: translateY(-1px);
  opacity: 0.82;
}

.back-to-dashboard-btn:active {
  transform: translateY(0);
}

.back-icon {
  flex-shrink: 0;
}

.refresh-btn {
  padding: 0.5rem 1rem;
  background: #ff6a3d;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.refresh-btn:hover:not(:disabled) {
  background: #ff5522;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.muted {
  color: #9ca3af;
  text-align: center;
  padding: 2rem;
  font-size: 0.95rem;
}

.alert-error {
  background: #fee2e2;
  color: #dc2626;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.dishes-grid {
  display: grid;
  gap: 1.5rem;
}

.dish-approval-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.2s;
}

.dish-approval-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.dish-title-info {
  flex: 1;
}

.dish-name {
  margin: 0 0 0.5rem 0;
  font-size: 1.3rem;
  color: #111827;
}

.dish-meta {
  margin: 0;
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.4;
}

.badge {
  display: inline-block;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
}

.badge-pending {
  background: #fef08a;
  color: #854d0e;
}

.card-body {
  padding: 1.5rem;
}

.ingredients-section {
  margin-bottom: 1.5rem;
}

.ingredients-section h4 {
  margin: 0 0 0.8rem 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.ingredients-list {
  display: grid;
  gap: 0.8rem;
}

.ingredient-item {
  padding: 0.8rem;
  background: #f3f4f6;
  border-radius: 6px;
  border-left: 3px solid #ff6a3d;
}

.ingredient-name {
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.3rem;
}

.ingredient-details {
  display: flex;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.detail {
  font-size: 0.85rem;
  color: #6b7280;
}

.product-badge {
  display: inline-block;
  background: #dbeafe;
  color: #1e40af;
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-weight: 600;
}

.missing-badge {
  display: inline-block;
  background: #fee2e2;
  color: #dc2626;
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-weight: 600;
}

.approval-section {
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
}

.approval-form {
  display: grid;
  gap: 1rem;
}

.notes-input,
.reason-input {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.9rem;
  resize: vertical;
}

.notes-input:focus,
.reason-input:focus {
  outline: none;
  border-color: #ff6a3d;
  box-shadow: 0 0 0 3px rgba(255, 106, 61, 0.1);
}

.approval-actions {
  display: flex;
  gap: 1rem;
}

.btn-approve,
.btn-reject,
.btn-outline {
  flex: 1;
  padding: 0.8rem 1.2rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.btn-approve {
  background: #10b981;
  color: white;
}

.btn-approve:hover:not(:disabled) {
  background: #059669;
}

.btn-reject {
  background: #ef4444;
  color: white;
}

.btn-reject:hover:not(:disabled) {
  background: #dc2626;
}

.btn-outline {
  background: white;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-outline:hover:not(:disabled) {
  background: #f3f4f6;
}

.btn-approve:disabled,
.btn-reject:disabled,
.btn-outline:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.reject-reason-form {
  display: grid;
  gap: 0.8rem;
  padding: 1rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  margin-top: 1rem;
}

.reject-actions {
  display: flex;
  gap: 0.8rem;
}

.reject-actions button {
  flex: 1;
}

/* Approved Dishes Table */
.approved-dishes-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

th {
  padding: 1rem;
  text-align: left;
  font-weight: 700;
  color: #374151;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.2s;
}

tbody tr:hover {
  background: #f9fafb;
}

td {
  padding: 1rem;
  color: #111827;
}

.dish-name-cell {
  font-weight: 600;
  color: #111827;
}

.ingredients-count {
  display: inline-block;
  background: #dbeafe;
  color: #1e40af;
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 600;
}

small {
  font-size: 0.8rem;
  color: #6b7280;
}
</style>
