<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Logistics Manager Panel'"
    :panelDescription="'Monitor inventory levels and manage budget requests for your branch.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <!-- Inventory Section -->
      <div class="panel-section">
        <h2 class="section-title">Inventory Monitor</h2>
        <p class="section-description">Current stock levels for your branch (Read-only)</p>

        <div v-if="inventoryLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading inventory...</p>
        </div>

        <div v-else-if="inventoryError" class="error-container">
          <p class="error-message">{{ inventoryError }}</p>
          <button class="btn-retry" @click="fetchInventory">Retry</button>
        </div>

        <div v-else class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Product Name</th>
                <th>Stock Count</th>
                <th>Minimum Stock</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in inventory" :key="product.id">
                <td>{{ product.name }}</td>
                <td>{{ product.stock }}</td>
                <td>{{ product.min_stock }}</td>
                <td>
                  <span :class="['status-badge', product.status === 'OK' ? 'status-ok' : 'status-low']">
                    {{ product.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="inventory.length === 0">
                <td colspan="4" class="empty-message">No products found in your branch.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Budget Request Section -->
      <div class="panel-section">
        <h2 class="section-title">Budget Requests</h2>

        <!-- Create New Request Button -->
        <button v-if="!showRequestForm" class="btn-primary" @click="showRequestForm = true">
          + New Budget Request
        </button>

        <!-- Budget Request Form -->
        <div v-if="showRequestForm" class="form-container">
          <h3>Create New Budget Request</h3>
          <form @submit.prevent="submitBudgetRequest">
            <div class="form-group">
              <label>Purpose / Description</label>
              <textarea
                v-model="budgetForm.purpose"
                rows="3"
                placeholder="Describe the purpose of this budget request..."
                required
              ></textarea>
            </div>
            <div class="form-group">
              <label>Requested Amount (₱)</label>
              <input
                type="number"
                v-model="budgetForm.amount"
                min="1"
                step="0.01"
                placeholder="Enter amount"
                required
              />
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="cancelRequest">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="submitting">
                {{ submitting ? 'Submitting...' : 'Submit Request' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <!-- Budget Request History -->
        <div class="requests-list">
          <h3>Request History</h3>
          <div v-if="requestsLoading" class="loading-container small">
            <div class="loading-spinner"></div>
          </div>
          <div v-else class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date Requested</th>
                  <th>Purpose</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Processed By</th>
                  <th>Date Processed</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="req in budgetRequests" :key="req.id">
                  <td>{{ formatDate(req.date_requested) }}</td>
                  <td>{{ req.purpose }}</td>
                  <td>₱{{ req.requested_amount }}</td>
                  <td>
                    <span :class="['status-badge', getStatusClass(req.status)]">
                      {{ req.status }}
                    </span>
                  </td>
                  <td>{{ req.processed_by || '-' }}</td>
                  <td>{{ req.date_processed ? formatDate(req.date_processed) : '-' }}</td>
                </tr>
                <tr v-if="budgetRequests.length === 0">
                  <td colspan="6" class="empty-message">No budget requests yet.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Logistics Manager Panel?</h3>
        <p>This will end your current session for Chikin Tayo.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>

  <!-- FULLSCREEN LOADING OVERLAY -->
  <transition name="fade">
    <div v-if="showOverlay" class="loading-overlay">
      <div class="logo-loading-box">
        <img :src="logoImg" alt="Chikin Tayo" class="logo-loading-img" />
        <p>{{ overlayText }}</p>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

// Logo image
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

// User profile
const userProfile = ref({})

// Inventory state
const inventory = ref([])
const inventoryLoading = ref(true)
const inventoryError = ref(null)

// Budget request state
const budgetRequests = ref([])
const requestsLoading = ref(true)
const showRequestForm = ref(false)
const submitting = ref(false)
const successMessage = ref('')
const budgetForm = ref({
  purpose: '',
  amount: ''
})

// Handle profile update from layout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  overlayText.value = 'Logging out...'
  showOverlay.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/staff-landing') } catch (e) { /* ignore */ }
  }, 600)
}

// Fetch inventory (read-only)
async function fetchInventory() {
  inventoryLoading.value = true
  inventoryError.value = null

  try {
    const response = await axios.get('/api/manager/logistics/inventory', { withCredentials: true })
    if (response.data.ok) {
      inventory.value = response.data.products
    } else {
      inventoryError.value = response.data.message || 'Failed to load inventory'
    }
  } catch (err) {
    console.error('Error fetching inventory:', err)
    inventoryError.value = err.response?.data?.message || 'Failed to load inventory'
  } finally {
    inventoryLoading.value = false
  }
}

// Fetch budget requests
async function fetchBudgetRequests() {
  requestsLoading.value = true

  try {
    const response = await axios.get('/api/manager/logistics/budget/my-requests', { withCredentials: true })
    if (response.data.ok) {
      budgetRequests.value = response.data.requests
    }
  } catch (err) {
    console.error('Error fetching budget requests:', err)
  } finally {
    requestsLoading.value = false
  }
}

// Submit budget request
async function submitBudgetRequest() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = ''

  try {
    const response = await axios.post('/api/manager/logistics/budget/create', {
      purpose: budgetForm.value.purpose,
      requested_amount: budgetForm.value.amount
    }, { withCredentials: true })

    if (response.data.ok) {
      successMessage.value = 'Budget request submitted successfully!'
      budgetForm.value = { purpose: '', amount: '' }
      showRequestForm.value = false
      await fetchBudgetRequests()

      // Clear success message after 3 seconds
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    }
  } catch (err) {
    console.error('Error submitting budget request:', err)
    alert(err.response?.data?.message || 'Failed to submit budget request')
  } finally {
    submitting.value = false
  }
}

function cancelRequest() {
  showRequestForm.value = false
  budgetForm.value = { purpose: '', amount: '' }
}

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function getStatusClass(status) {
  switch (status) {
    case 'Approved': return 'status-approved'
    case 'Rejected': return 'status-rejected'
    default: return 'status-pending'
  }
}

onMounted(async () => {
  try {
    // Fetch user profile
    const profileRes = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
    userProfile.value = profileRes.data.user

    // Fetch inventory and budget requests in parallel
    await Promise.all([
      fetchInventory(),
      fetchBudgetRequests()
    ])
  } catch (err) {
    console.error('Error loading data:', err)
  }
})
</script>

<style scoped>
.panel-section {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #4b2a06;
  margin: 0 0 8px 0;
}

.section-description {
  font-size: 14px;
  color: #666;
  margin: 0 0 16px 0;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.loading-container.small {
  padding: 20px;
}

.loading-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid rgba(255, 159, 67, 0.3);
  border-top: 3px solid #ff9f43;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  background: #fff5f5;
  border-radius: 8px;
}

.error-message {
  color: #dc3545;
  margin-bottom: 12px;
}

.btn-retry {
  padding: 8px 16px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.btn-retry:hover {
  background: #c82333;
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
  border-bottom: 1px solid #eee;
}

.data-table th {
  background: #fff4e6;
  font-weight: 600;
  color: #5a2c0a;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.data-table td {
  color: #333;
  font-size: 14px;
}

.empty-message {
  text-align: center;
  color: #999;
  font-style: italic;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-ok {
  background: rgba(46, 204, 113, 0.15);
  color: #27ae60;
}

.status-low {
  background: rgba(231, 76, 60, 0.15);
  color: #e74c3c;
}

.status-approved {
  background: rgba(46, 204, 113, 0.15);
  color: #27ae60;
}

.status-rejected {
  background: rgba(231, 76, 60, 0.15);
  color: #e74c3c;
}

.status-pending {
  background: rgba(241, 196, 15, 0.15);
  color: #f39c12;
}

/* Form Styles */
.form-container {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  border: 1px solid #e9ecef;
}

.form-container h3 {
  margin: 0 0 16px 0;
  color: #4b2a06;
  font-size: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-weight: 500;
  color: #333;
  margin-bottom: 6px;
}

.form-group textarea,
.form-group input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
}

.form-group textarea:focus,
.form-group input:focus {
  outline: none;
  border-color: #ff9f43;
  box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.15);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-primary {
  padding: 10px 20px;
  background: #ff9f43;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #ffb366;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 10px 20px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #5a6268;
}

.success-message {
  background: #d4edda;
  color: #155724;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  border: 1px solid #c3e6cb;
}

.requests-list h3 {
  margin: 24px 0 16px 0;
  color: #4b2a06;
  font-size: 16px;
}

/* Logout Confirm */
.logout-confirm-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.logout-confirm-box {
  background: white;
  padding: 24px;
  border-radius: 12px;
  text-align: center;
  max-width: 400px;
}

.logout-confirm-box h3 {
  margin: 0 0 8px 0;
  color: #333;
}

.logout-confirm-box p {
  margin: 0 0 20px 0;
  color: #666;
}

.logout-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.btn-cancel {
  padding: 10px 24px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.btn-confirm {
  padding: 10px 24px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
}

.logo-loading-box {
  text-align: center;
}

.logo-loading-img {
  width: 120px;
  margin-bottom: 16px;
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

