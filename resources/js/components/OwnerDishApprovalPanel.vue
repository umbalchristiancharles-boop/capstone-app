<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Dish Approval'"
    :panelDescription="'Review and approve new dishes from kitchen staff.'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    :showProfileColumn="false"
    @profile-updated="onProfileUpdated"
    @logout="confirmLogout"
  >
    <template #headerLeft>
      <button class="back-btn" @click="goBackToOwnerPanel" title="Back to Owner Panel">
        ← Back
      </button>
    </template>

    <template #main>
      <section class="panel-block">
        <div class="panel-header">
          <h2>
            Pending Dishes
            <span v-if="pendingDishes.length > 0" class="panel-badge">{{ pendingDishes.length }}</span>
          </h2>
          <button class="refresh-btn" @click="loadPendingDishes" :disabled="loading">
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
        </div>
        <div class="panel-body">
          <div v-if="loading" class="muted">Loading pending dishes...</div>
          <div v-else-if="error" class="alert-error">⚠️ {{ error }}</div>
          <div v-else-if="pendingDishes.length === 0" class="muted">No pending dishes.</div>
          <div v-else class="dishes-grid">
            <div v-for="dish in pendingDishes" :key="dish.id" class="dish-approval-card">
              <div class="card-header">
                <div class="dish-title-info">
                  <h3 class="dish-name">{{ dish.name }}</h3>
                  <p class="dish-meta">
                    Created by <strong>{{ dish.creator?.full_name || 'Unknown' }}</strong>
                    <br>
                    <small>{{ formatDate(dish.created_at) }}</small>
                  </p>
                </div>
                <div class="badge badge-pending">PENDING APPROVAL</div>
              </div>

              <div class="card-body">
                <div class="ingredients-section">
                  <h4>Ingredients</h4>
                  <div class="ingredients-list">
                    <div v-for="(ing, idx) in dish.ingredients" :key="idx" class="ingredient-item">
                      <div class="ingredient-name">{{ ing.name }}</div>
                      <div class="ingredient-details">
                        <span v-if="ing.per_serving" class="detail">{{ ing.per_serving }} {{ ing.unit || 'unit' }}/serving</span>
                        <span v-if="ing.product" class="detail product-badge">{{ ing.product.name }}</span>
                        <span v-else class="detail missing-badge">⚠️ No product assigned</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="approval-section">
                  <div class="approval-form">
                    <textarea
                      v-model="approvalNotes[dish.id]"
                      :placeholder="`Optional notes for approval of ${dish.name}...`"
                      class="notes-input"
                      rows="3"
                    ></textarea>

                    <div class="publish-controls">
                      <div v-if="userProfile.role && userProfile.role.toString().toUpperCase() === 'ADMIN'">
                        <label style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.6rem;">
                          <input type="checkbox" v-model="publishProducts[dish.id]" />
                          <strong>Publish products for this dish</strong>
                        </label>

                        <div v-if="publishProducts[dish.id]" style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;margin-bottom:0.6rem;">
                          <select v-model="perPackOrIndividual[dish.id]" style="padding:0.6rem;border:1px solid #d1d5db;border-radius:6px;">
                            <option value="individual">Sell as Individual</option>
                            <option value="per_pack">Sell as Pack</option>
                            <option value="both">Both</option>
                          </select>
                          <input type="text" v-model="packUnit[dish.id]" placeholder="Pack unit (e.g., pcs)" style="padding:0.6rem;border:1px solid #d1d5db;border-radius:6px;" />
                        </div>

                        <div v-if="publishProducts[dish.id] && (perPackOrIndividual[dish.id] === 'per_pack' || perPackOrIndividual[dish.id] === 'both')" style="margin-bottom:0.6rem;">
                          <input type="number" min="1" v-model.number="packQuantity[dish.id]" placeholder="Pack quantity (e.g., 6)" style="width:200px;padding:0.6rem;border:1px solid #d1d5db;border-radius:6px;" />
                        </div>
                      </div>

                      <div class="approval-actions">
                        <button
                          class="btn-approve"
                          @click="approveDish(dish.id)"
                          :disabled="approvingId === dish.id || rejectingId === dish.id"
                        >
                          {{ approvingId === dish.id ? '⏳ Approving...' : '✅ Approve' }}
                        </button>
                        <button
                          class="btn-reject"
                          @click="showRejectForm(dish.id)"
                          :disabled="approvingId === dish.id || rejectingId === dish.id"
                        >
                          {{ rejectingId === dish.id ? '⏳ Rejecting...' : '❌ Reject' }}
                        </button>
                      </div>
                    </div>

                    <div v-if="showRejectReason[dish.id]" class="reject-reason-form">
                      <textarea
                        v-model="rejectReason[dish.id]"
                        placeholder="Please provide a reason for rejection..."
                        class="reason-input"
                        rows="2"
                      ></textarea>
                      <div class="reject-actions">
                        <button
                          class="btn-outline"
                          @click="cancelReject(dish.id)"
                          :disabled="rejectingId === dish.id"
                        >
                          Cancel
                        </button>
                        <button
                          class="btn-reject"
                          @click="confirmReject(dish.id)"
                          :disabled="!rejectReason[dish.id] || rejectingId === dish.id"
                        >
                          {{ rejectingId === dish.id ? '⏳ Rejecting...' : 'Confirm Rejection' }}
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

      <!-- Approved Dishes Section -->
      <section class="panel-block">
        <div class="panel-header">
          <h2>Approved Dishes</h2>
          <button class="refresh-btn" @click="loadApprovedDishes" :disabled="loadingApproved">
            {{ loadingApproved ? 'Loading...' : 'Refresh' }}
          </button>
        </div>
        <div class="panel-body">
          <div v-if="loadingApproved" class="muted">Loading approved dishes...</div>
          <div v-else-if="approvedDishes.length === 0" class="muted">No approved dishes yet.</div>
          <div v-else class="approved-dishes-table">
            <table>
              <thead>
                <tr>
                  <th>Dish Name</th>
                  <th>Created by</th>
                  <th>Approved by</th>
                  <th>Approved On</th>
                  <th>Ingredients</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="dish in approvedDishes" :key="dish.id">
                  <td class="dish-name-cell">
                    <strong>{{ dish.name }}</strong>
                  </td>
                  <td>{{ dish.creator?.full_name || 'Unknown' }}</td>
                  <td>{{ dish.approver?.full_name || 'Unknown' }}</td>
                  <td><small>{{ formatDate(dish.approved_at) }}</small></td>
                  <td>
                    <div class="ingredients-count">{{ dish.ingredients?.length || 0 }} items</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

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

    <template #headerActions>
      <div ref="profileWrapper" class="header-profile-wrapper" style="position:relative;">
        <div
          class="header-profile-container"
          style="background:#fff;border:1px solid #eef2f5;border-radius:12px;padding:6px 10px;display:inline-flex;align-items:center;"
        >
          <button
            class="header-profile-btn"
            type="button"
            style="background: transparent; border: 0; cursor: pointer; display: flex; align-items: center; gap: 0.6rem; padding:0;"
            @click.stop="toggleProfileDropdown"
          >
            <div class="header-avatar" style="width:28px;height:28px;border-radius:50%;background:rgb(238,238,238);display:flex;align-items:center;justify-content:center;font-weight:600;">
              <div class="header-avatar-initials">{{ (userProfile.fullName || userProfile.full_name || userProfile.name || 'O').charAt(0) }}</div>
            </div>
            <div class="header-name" style="font-size:0.85rem;font-weight:700;color:#111827;white-space:nowrap;">
              {{ (userProfile.role || 'OWNER').toString().toUpperCase() }}
              <span v-if="userProfile.branch || userProfile.branch_name" style="font-weight:600;opacity:0.85"> - {{ (userProfile.branch || userProfile.branch_name).toString().toUpperCase() }}</span>
            </div>
          </button>
        </div>

        <div
          v-if="showProfileDropdown"
          class="header-profile-dropdown"
          style="position:absolute;right:0;top:46px;background:#fff;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);padding:8px;display:flex;flex-direction:column;gap:6px;min-width:140px;z-index:30"
        >
          <button class="dropdown-item" style="background:transparent;border:0;padding:8px;text-align:left;" @click.prevent="handleInfoClick">Info</button>
          <button class="dropdown-item" style="background:transparent;border:0;padding:8px;text-align:left;" @click.prevent="handleLogoutClick">Logout</button>
        </div>
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
const pendingDishes = ref([])
const approvedDishes = ref([])
const loading = ref(false)
const loadingApproved = ref(false)
const error = ref('')
const approvingId = ref(null)
const rejectingId = ref(null)
const showProfileDropdown = ref(false)

const approvalNotes = ref({})
const rejectReason = ref({})
const showRejectReason = ref({})
// Publish controls per-dish
const publishProducts = ref({})
const perPackOrIndividual = ref({})
const packQuantity = ref({})
const packUnit = ref({})

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

function loadPendingDishes() {
  loading.value = true
  error.value = ''
  axios.get('/api/owner/dishes/pending')
    .then(response => {
      pendingDishes.value = response.data.data || []
      notifyIfPending()
    })
    .catch(err => {
      console.error('Failed to load pending dishes:', err)
      error.value = err.response?.data?.message || 'Failed to load pending dishes'
    })
    .finally(() => {
      loading.value = false
    })
}

function loadApprovedDishes() {
  loadingApproved.value = true
  axios.get('/api/owner/dishes/approved')
    .then(response => {
      approvedDishes.value = response.data.data || []
    })
    .catch(err => {
      console.error('Failed to load approved dishes:', err)
    })
    .finally(() => {
      loadingApproved.value = false
    })
}

function approveDish(dishId) {
  // Show SweetAlert confirmation
  try {
    window.swalConfirm('Are you sure you want to approve this dish?', 'Approve Dish').then(async (ok) => {
      if (!ok) return

      approvingId.value = dishId
      const notes = approvalNotes.value[dishId] || ''

      const payload = { notes: notes }
      if (userProfile.value.role && userProfile.value.role.toString().toUpperCase() === 'ADMIN') {
        payload.publish_products = publishProducts.value[dishId] || false
        payload.per_pack_or_individual = perPackOrIndividual.value[dishId] || null
        payload.pack_quantity = packQuantity.value[dishId] || null
        payload.pack_unit = packUnit.value[dishId] || null
      }

      axios.post(`/api/owner/dishes/${dishId}/approve`, payload)
        .then(response => {
          // Remove from pending and add to approved
          pendingDishes.value = pendingDishes.value.filter(d => d.id !== dishId)
          approvedDishes.value.unshift(response.data.data)
          approvalNotes.value[dishId] = ''
          showRejectReason.value[dishId] = false
          
          // Show success message
          try {
            window.swalAlert('✅ Dish approved successfully! Ingredients are now visible in logistics panel.', 'Success')
          } catch (e) {
            alert('✅ Dish approved successfully! Ingredients are now visible in logistics panel.')
          }
        })
        .catch(err => {
          console.error('Failed to approve dish:', err)
          try {
            window.swalAlert('❌ Error: ' + (err.response?.data?.message || 'Failed to approve dish'), 'Error')
          } catch (e) {
            alert('❌ Error: ' + (err.response?.data?.message || 'Failed to approve dish'))
          }
        })
        .finally(() => {
          approvingId.value = null
        })
    })
  } catch (e) {
    // Fallback to window.confirm if SweetAlert fails
    if (!window.confirm('Are you sure you want to approve this dish?')) {
      return
    }
    
    approvingId.value = dishId
    const notes = approvalNotes.value[dishId] || ''

    const payload = { notes: notes }
    if (userProfile.value.role && userProfile.value.role.toString().toUpperCase() === 'ADMIN') {
      payload.publish_products = publishProducts.value[dishId] || false
      payload.per_pack_or_individual = perPackOrIndividual.value[dishId] || null
      payload.pack_quantity = packQuantity.value[dishId] || null
      payload.pack_unit = packUnit.value[dishId] || null
    }

    axios.post(`/api/owner/dishes/${dishId}/approve`, payload)
      .then(response => {
        pendingDishes.value = pendingDishes.value.filter(d => d.id !== dishId)
        approvedDishes.value.unshift(response.data.data)
        approvalNotes.value[dishId] = ''
        showRejectReason.value[dishId] = false
        alert('✅ Dish approved successfully! Ingredients are now visible in logistics panel.')
      })
      .catch(err => {
        console.error('Failed to approve dish:', err)
        alert('❌ Error: ' + (err.response?.data?.message || 'Failed to approve dish'))
      })
      .finally(() => {
        approvingId.value = null
      })
  }
}

function showRejectForm(dishId) {
  showRejectReason.value[dishId] = true
}

function cancelReject(dishId) {
  showRejectReason.value[dishId] = false
  rejectReason.value[dishId] = ''
}

function confirmReject(dishId) {
  const reason = rejectReason.value[dishId] || ''
  if (!reason.trim()) {
    try {
      window.swalAlert('Please provide a reason for rejection', 'Invalid Input')
    } catch (e) {
      alert('Please provide a reason for rejection')
    }
    return
  }

  // Show SweetAlert confirmation
  try {
    window.swalConfirm('Are you sure you want to reject this dish?', 'Reject Dish').then(async (ok) => {
      if (!ok) return

      rejectingId.value = dishId

      axios.post(`/api/owner/dishes/${dishId}/reject`, {
        reason: reason
      })
        .then(response => {
          // Remove from pending
          pendingDishes.value = pendingDishes.value.filter(d => d.id !== dishId)
          rejectReason.value[dishId] = ''
          showRejectReason.value[dishId] = false
          
          try {
            window.swalAlert('❌ Dish rejected. The kitchen staff will be notified.', 'Dish Rejected')
          } catch (e) {
            alert('❌ Dish rejected. The kitchen staff will be notified.')
          }
        })
        .catch(err => {
          console.error('Failed to reject dish:', err)
          try {
            window.swalAlert('Error: ' + (err.response?.data?.message || 'Failed to reject dish'), 'Error')
          } catch (e) {
            alert('Error: ' + (err.response?.data?.message || 'Failed to reject dish'))
          }
        })
        .finally(() => {
          rejectingId.value = null
        })
    })
  } catch (e) {
    // Fallback to window.confirm if SweetAlert fails
    if (!window.confirm('Are you sure you want to reject this dish?')) {
      return
    }

    rejectingId.value = dishId

    axios.post(`/api/owner/dishes/${dishId}/reject`, {
      reason: reason
    })
      .then(response => {
        pendingDishes.value = pendingDishes.value.filter(d => d.id !== dishId)
        rejectReason.value[dishId] = ''
        showRejectReason.value[dishId] = false
        alert('❌ Dish rejected. The kitchen staff will be notified.')
      })
      .catch(err => {
        console.error('Failed to reject dish:', err)
        alert('Error: ' + (err.response?.data?.message || 'Failed to reject dish'))
      })
      .finally(() => {
        rejectingId.value = null
      })
  }
}

function toggleProfileDropdown() {
  showProfileDropdown.value = !showProfileDropdown.value
}

function handleInfoClick() {
  showProfileDropdown.value = false
  // Redirect to profile page or show modal
  window.location.href = '/profile'
}

function handleLogoutClick() {
  confirmLogout()
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
  const totalPending = (pendingDishes.value?.length || 0) + (pendingProductRequests.value?.length || 0)
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
  loadPendingDishes()
  loadApprovedDishes()
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

.back-btn {
  padding: 0.4rem 0.8rem;
  background: transparent;
  color: #ff6a3d;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: opacity 0.2s;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  position: relative;
  z-index: 40;
}

.back-btn:hover {
  opacity: 0.8;
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
