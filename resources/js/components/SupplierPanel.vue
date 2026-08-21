<template>
  <OwnerPanelLayout ref="ownerLayout"
    :userProfile="userProfile"
    :panelTitle="'Supplier Panel'"
    :panelDescription="'Manage suppliers, view deliveries, and monitor supplier performance.'"
    :showProfileColumn="false"
    :showAttendanceCard="false"
    :showHeader="false"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    :ownerTwoColumnLayout="true"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="supplier-page">
        <header class="supplier-hero">
          <div>
            <span class="supplier-hero__eyebrow">Supplier dashboard</span>
            <h2 class="supplier-hero__title">Supplier overview</h2>
            <p class="supplier-hero__subtitle">Manage suppliers, view deliveries, and monitor supplier performance.</p>
          </div>
          <button class="supplier-hero__action" type="button" @click="loadOrders" :disabled="ordersLoading">
            {{ ordersLoading ? 'Loading...' : 'Refresh Orders' }}
          </button>
        </header>

      <div class="panel-content">
        <div class="hr-stats-grid">
          <div class="hr-stat-card hr-stat-card--total">
            <div class="hr-stat-icon">📦</div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Active Deliveries</span>
              <span class="hr-stat-value">{{ dashboardTotals.activeDeliveries }}</span>
            </div>
          </div>
          <div class="hr-stat-card hr-stat-card--active" :class="{ 'stat-alert': supplierPendingCount > 0 }">
            <div class="hr-stat-icon">🕒</div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Pending Orders</span>
              <span class="hr-stat-value">{{ dashboardTotals.pendingOrders }}</span>
            </div>
            <span v-if="supplierPendingCount > 0" class="panel-badge">{{ supplierPendingCount }}</span>
          </div>
          <div class="hr-stat-card hr-stat-card--leave">
            <div class="hr-stat-icon">🏷️</div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Total Suppliers</span>
              <span class="hr-stat-value">{{ dashboardTotals.totalSuppliers }}</span>
            </div>
          </div>
        </div>

      <!-- Orders Section (merged) -->
      <div class="panel-section">
        <h2 class="section-title">Your Orders</h2>
        <div v-if="ordersLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading orders...</p>
        </div>
        <div v-else class="requests-container">
          <div class="requests-scroll table-container">
            <table class="data-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Branch</th>
                <th>Qty</th>
                <th>Variance</th>
                <th>Total</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id">
                <td>{{ order.product?.name }}</td>
                <td>{{ order.branch?.name || order.branch_id }}</td>
                <td>{{ order.quantity }}</td>
                <td>{{ formatVariance(order.procurementRequest?.variance_quantity) }}</td>
                <td>{{ formatPrice(order.product?.price * order.quantity) }}</td>
                <td>{{ order.expires_at ? formatDate(order.expires_at) : 'N/A' }}</td>
                <td>
                  <span :class="['status-badge', getStatusClass(order.status)]">
                    {{ order.status }}
                  </span>
                  <div v-if="order.procurementRequest?.variance_quantity" class="alert-badge">
                    Variance reported
                  </div>
                </td>
                <td class="action-cell">
                  <template v-if="order.status === 'pending'">
                    <div v-if="canSubmitProduct(order)">
                      <button class="btn-primary btn-small" @click="openSupplierSubmitModal(order)">Product available</button>
                    </div>
                    <div v-else>
                      <div v-if="canCompleteTransaction(order)">
                        <button class="btn-primary btn-small" @click="completeTransaction(order.id)">Transaction complete</button>
                      </div>
                      <div v-else-if="order.product && order.product.id">
                        <button class="btn-disabled btn-small" disabled>Product submitted</button>
                        <div class="muted small-text" v-if="order.product.created_at" style="margin-top:4px">Submitted: {{ formatDate(order.product.created_at) }}</div>
                      </div>
                      <div v-else>
                        <button class="btn-disabled btn-small" disabled>Waiting for procurement order</button>
                      </div>
                    </div>
                  </template>
                  <template v-else-if="order.status === 'fulfilled'">
                    <button class="btn-disabled btn-small" disabled>Completed</button>
                  </template>
                  <template v-else-if="order.status === 'on_delivery'">
                    <button class="btn-disabled btn-small" disabled>On delivery</button>
                  </template>
                  <template v-else-if="order.status === 'cancelled'">
                    <button class="btn-muted btn-small" disabled>Cancelled</button>
                  </template>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="8" class="empty-message">No orders yet.</td>
              </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>

      <logistics-panel-content :deliveries="deliveries" :suppliers="suppliers" @product-added="onProductAdded" />

          <section class="supplier-products">
            <h2>Your Products</h2>
            <div v-if="loadingProducts">Loading products...</div>
            <div v-else-if="!products.length">No products yet.</div>
            <div v-else class="product-grid">
              <div v-for="p in products" :key="p.id" class="product-card">
                <div class="product-card-header">
                  <div class="product-name">{{ p.name }}</div>
                  <button class="btn-edit" @click="openEditProductModal(p)" title="Edit product">✎</button>
                </div>
                <div class="product-category" v-if="p.category">{{ p.category }}</div>
                <div class="product-type" v-if="p.per_pack_or_individual" :class="'type-' + p.per_pack_or_individual">
                  {{ formatPricingType(p.per_pack_or_individual) }}
                </div>
            <div class="product-meta">
              <div class="product-price">{{ formatPrice(p.price) }}</div>
              <div class="product-stock">Real stock: {{ p.real_stock ?? p.stock ?? 0 }}</div>
              <!-- Expiry is now tracked at order level, not product level -->
            </div>
              </div>
            </div>
              </section>
            </div>
            </div>
            </template>

            <template #sideTop>
              <div class="header-actions-top">
                <div class="header-profile-wrapper" @click.stop>
                  <button class="header-profile-btn" @click="toggleProfileDropdown">
                    <div class="header-avatar">
                      <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
                      <div v-else class="header-avatar-initials">{{ (userProfile.fullName || userProfile.full_name || 'S').charAt(0) }}</div>
                    </div>
                    <div class="header-name">{{ ((userProfile.fullName || userProfile.full_name) ? (userProfile.fullName || userProfile.full_name).toUpperCase() : 'SUPPLIER') }} - {{ (userProfile.branch_name || userProfile.branch || userProfile.branch_id || 'Branch') }}</div>
                  </button>
                  <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
                    <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
                    <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
                  </div>
                </div>
              </div>
            </template>


  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
  <!-- RECEIPT MODAL -->
  <transition name="fade">
    <div v-if="showReceiptModal" class="modal-backdrop">
      <div class="receipt-box receipt-box-enhanced">
        <div class="receipt-header">
          <div class="receipt-logo">
            <img :src="logoImg" alt="Chikin Tayo" class="receipt-logo-img" />
          </div>
          <div class="receipt-title-section">
            <h3 class="receipt-title">Transaction Receipt</h3>
            <p class="receipt-subtitle">Official Delivery Confirmation</p>
          </div>
        </div>

        <div class="receipt-body">
          <!-- Order Information Section -->
          <div class="receipt-section">
            <div class="receipt-section-header">
              <span class="section-icon">📋</span>
              <h4>Order Information</h4>
            </div>
            <div class="receipt-details-grid">
              <div class="receipt-detail-item">
                <span class="detail-label">Order ID</span>
                <span class="detail-value">{{ receiptData.id }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Order Date</span>
                <span class="detail-value">{{ formatDate(receiptData.created_at || receiptData.createdAt) }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                  <span :class="['status-badge', getStatusClass(receiptData.status)]">{{ receiptData.status }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Product Details Section -->
          <div class="receipt-section">
            <div class="receipt-section-header">
              <span class="section-icon">📦</span>
              <h4>Product Details</h4>
            </div>
            <div class="receipt-details-grid">
              <div class="receipt-detail-item full-width">
                <span class="detail-label">Product Name</span>
                <span class="detail-value detail-value-bold">{{ receiptData.product?.name }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Category</span>
                <span class="detail-value">{{ receiptData.product?.category || 'N/A' }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Pricing Type</span>
                <span class="detail-value">{{ formatPricingType(receiptData.product?.per_pack_or_individual) }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Unit Price</span>
                <span class="detail-value detail-value-price">{{ formatPrice(receiptData.product?.price) }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Quantity</span>
                <span class="detail-value detail-value-bold">{{ receiptData.quantity }} units</span>
              </div>
            </div>
          </div>

          <!-- Delivery Information Section -->
          <div class="receipt-section">
            <div class="receipt-section-header">
              <span class="section-icon">🚚</span>
              <h4>Delivery Information</h4>
            </div>
            <div class="receipt-details-grid">
              <div class="receipt-detail-item">
                <span class="detail-label">Branch</span>
                <span class="detail-value">{{ receiptData.branch?.name || receiptData.branch_id }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Variance</span>
                <span class="detail-value">{{ formatVariance(receiptData.procurementRequest?.variance_quantity) }}</span>
              </div>
            </div>
          </div>

          <!-- Financial Summary Section -->
          <div class="receipt-section receipt-section-highlight">
            <div class="receipt-section-header">
              <span class="section-icon">💰</span>
              <h4>Financial Summary</h4>
            </div>
            <div class="receipt-financial-summary">
              <div class="receipt-financial-row">
                <span class="financial-label">Unit Price</span>
                <span class="financial-value">{{ formatPrice(receiptData.product?.price) }}</span>
              </div>
              <div class="receipt-financial-row">
                <span class="financial-label">Quantity</span>
                <span class="financial-value">× {{ receiptData.quantity }}</span>
              </div>
              <div class="receipt-divider"></div>
              <div class="receipt-financial-row receipt-total-row">
                <span class="financial-label">Total Amount</span>
                <span class="financial-value financial-total">{{ formatPrice(receiptData.product?.price * receiptData.quantity) }}</span>
              </div>
            </div>
          </div>

          <!-- Procurement Information Section -->
          <div v-if="receiptData.procurementRequest" class="receipt-section">
            <div class="receipt-section-header">
              <span class="section-icon">📊</span>
              <h4>Procurement Details</h4>
            </div>
            <div class="receipt-details-grid">
              <div class="receipt-detail-item">
                <span class="detail-label">Procurement Request ID</span>
                <span class="detail-value">#{{ receiptData.procurementRequest.id }}</span>
              </div>
              <div class="receipt-detail-item">
                <span class="detail-label">Procurement Status</span>
                <span class="detail-value">{{ formatProcurementStatus(receiptData.procurementRequest.status) }}</span>
              </div>
            </div>
          </div>

          <!-- Estimated Delivery Section -->
          <div class="receipt-section receipt-section-delivery">
            <div class="receipt-section-header">
              <span class="section-icon">⏰</span>
              <h4>Estimated Delivery</h4>
            </div>
            <div class="delivery-input-group">
              <div class="form-group">
                <label>Estimated Delivery Date & Time</label>
                <input
                  v-model="estimatedDeliveryDateTime"
                  type="datetime-local"
                  class="delivery-input"
                  :min="getCurrentDateTimeLocal()"
                />
                <span class="form-hint">Please provide your estimated delivery schedule</span>
              </div>
              <button
                class="btn-confirm-delivery"
                @click="confirmEstimatedDelivery"
                :disabled="!estimatedDeliveryDateTime || savingEstimatedDelivery"
              >
                {{ savingEstimatedDelivery ? 'Saving...' : 'Confirm Delivery Schedule' }}
              </button>
            </div>
          </div>
        </div>

        <div class="receipt-footer">
          <div class="receipt-footer-info">
            <p class="receipt-print-info">Receipt generated on {{ formatDate(new Date().toISOString()) }}</p>
          </div>
          <div class="receipt-actions">
            <button class="btn-secondary" @click="closeReceipt">Close</button>
            <button class="btn-primary" @click="printReceipt">Print Receipt</button>
          </div>
        </div>
      </div>
    </div>
  </transition>

  <!-- Submit product modal for supplier to add product and price -->
  <transition name="fade">
    <div v-if="supplierSubmitModalVisible" class="modal-backdrop" @click.self="closeSupplierSubmitModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>Product Request - Add Product</h3>
          </div>
          <div class="modal-body">
            <div class="form-group full-span">
              <label>Product Name</label>
              <input v-model="submitForm.name" type="text" placeholder="Product name" readonly />
            </div>
            <div class="form-group">
              <label>Category (e.g., Beverage, Meat, Vegetable, Condiment)</label>
              <select v-model="submitForm.category">
                <option value="">Select a category</option>
                <option value="Beverage">Beverage</option>
                <option value="Meat">Meat</option>
                <option value="Vegetable">Vegetable</option>
                <option value="Grain">Grain</option>
                <option value="Condiment">Condiment</option>
                <option value="Dairy">Dairy</option>
                <option value="Egg">Egg</option>
                <option value="Spice">Spice</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="form-group full-span">
              <label>Pricing Type</label>
              <div class="pricing-type-options">
                <div class="option-group">
                  <input
                    type="radio"
                    id="submit-type-individual"
                    value="individual"
                    v-model="submitForm.per_pack_or_individual"
                  />
                  <label for="submit-type-individual" class="option-label">
                    <span class="option-badge type-individual">Individual</span>
                    <span class="option-desc">Sold by individual units</span>
                  </label>
                </div>
                <div class="option-group">
                  <input
                    type="radio"
                    id="submit-type-per_pack"
                    value="per_pack"
                    v-model="submitForm.per_pack_or_individual"
                  />
                  <label for="submit-type-per_pack" class="option-label">
                    <span class="option-badge type-per_pack">Per Pack</span>
                    <span class="option-desc">Sold in packs only</span>
                  </label>
                </div>
                <div class="option-group">
                  <input
                    type="radio"
                    id="submit-type-both"
                    value="both"
                    v-model="submitForm.per_pack_or_individual"
                  />
                  <label for="submit-type-both" class="option-label">
                    <span class="option-badge type-both">Both Options</span>
                    <span class="option-desc">Can be sold both ways</span>
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Unit Price (PHP)</label>
              <input v-model.number="submitForm.price" type="number" min="0.01" step="0.01" placeholder="0.00" />
            </div>
            <div class="form-group">
              <label>Date Product Made</label>
              <input v-model="submitForm.date_made" type="date" :max="todayDate" />
            </div>
            <div v-if="submitForm.per_pack_or_individual === 'per_pack'" class="form-group">
              <label>Pack details</label>
              <div style="display:flex;gap:8px">
                <input v-model.number="submitForm.pack_quantity" type="number" min="0" step="0.01" placeholder="Quantity (e.g. 6 or 250)" />
                <select v-model="submitForm.pack_unit">
                  <option value="">Unit</option>
                  <option value="pcs">pcs</option>
                  <option value="g">g</option>
                  <option value="kg">kg</option>
                </select>
              </div>
              <div class="muted" style="margin-top:6px">If selling per pack, enter how many pieces or how many grams are in a pack.</div>
            </div>
            <div v-if="submitError" class="error-msg">{{ submitError }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeSupplierSubmitModal">Cancel</button>
            <button class="btn-primary" @click="submitProductForm" :disabled="submitSubmitting">{{ submitSubmitting ? 'Submitting...' : 'Submit Product' }}</button>
          </div>
        </div>
      </div>
    </div>
  </transition>


  <!-- EDIT PRODUCT MODAL - Edit one field at a time -->
  <transition name="fade">
    <div v-if="editProductModalVisible" class="modal-backdrop" @click.self="closeEditProductModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>Edit Product: {{ editForm.name }}</h3>
          </div>
          <div class="modal-body">
            <!-- Field selector -->
            <div class="field-selector">
              <button
                v-for="field in editFields"
                :key="field.id"
                class="field-btn"
                :class="{ active: editFieldType === field.id }"
                @click="selectEditField(field.id)"
              >
                {{ field.label }}
              </button>
            </div>

            <!-- Category field -->
            <div v-if="editFieldType === 'category'" class="edit-field-section">
              <label>Edit Category</label>
              <select v-model="editForm.category" class="full-width-input">
                <option value="">Select a category</option>
                <option value="Beverage">Beverage</option>
                <option value="Meat">Meat</option>
                <option value="Vegetable">Vegetable</option>
                <option value="Grain">Grain</option>
                <option value="Condiment">Condiment</option>
                <option value="Dairy">Dairy</option>
                <option value="Egg">Egg</option>
                <option value="Spice">Spice</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- Pricing Type field -->
            <div v-if="editFieldType === 'pricing'" class="edit-field-section">
              <label>Edit Pricing Type</label>
              <div class="pricing-type-options">
                <div class="option-group">
                  <input
                    type="radio"
                    id="edit-type-individual"
                    value="individual"
                    v-model="editForm.per_pack_or_individual"
                  />
                  <label for="edit-type-individual" class="option-label">
                    <span class="option-badge type-individual">Individual</span>
                    <span class="option-desc">Sold by individual units</span>
                  </label>
                </div>
                <div class="option-group">
                  <input
                    type="radio"
                    id="edit-type-per_pack"
                    value="per_pack"
                    v-model="editForm.per_pack_or_individual"
                  />
                  <label for="edit-type-per_pack" class="option-label">
                    <span class="option-badge type-per_pack">Per Pack</span>
                    <span class="option-desc">Sold in packs only</span>
                  </label>
                </div>
                <div class="option-group">
                  <input
                    type="radio"
                    id="edit-type-both"
                    value="both"
                    v-model="editForm.per_pack_or_individual"
                  />
                  <label for="edit-type-both" class="option-label">
                    <span class="option-badge type-both">Both Options</span>
                    <span class="option-desc">Can be sold both ways</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Price field -->
            <div v-if="editFieldType === 'price'" class="edit-field-section">
              <label>Edit Unit Price (PHP)</label>
              <input v-model.number="editForm.price" type="number" step="0.01" placeholder="0.00" class="full-width-input" />
            </div>

            <div v-if="editError" class="error-msg">{{ editError }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeEditProductModal">Cancel</button>
            <button class="btn-primary" @click="saveProductChanges" :disabled="editSubmitting || !editFieldType">{{ editSubmitting ? 'Saving...' : 'Save Changes' }}</button>
          </div>
        </div>
      </div>
    </div>
  </transition>

  <!-- FULLSCREEN LOADING OVERLAY -->
  <LoadingOverlay :show="showOverlay" :text="overlayText" :logo-src="logoImg" />
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import LogisticsPanelContent from './logistics/LogisticsPanelContent.vue'
import LoadingOverlay from './LoadingOverlay.vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { showToast } from './toastStore'

const userProfile = ref({})
const dashboardTotals = ref({ totalSuppliers: 0, activeDeliveries: 0, pendingOrders: 0 })
const deliveries = ref([])
const products = ref([])
const loadingProducts = ref(false)
const orders = ref([])
const ordersLoading = ref(false)
const suppliers = ref([])
const notificationCounts = ref({ supplier: 0 })
const hasNotified = ref(false)
const supplierPendingCount = computed(() => {
  const apiPending = Number(notificationCounts.value?.supplier || 0)
  const dashboardPending = Number(dashboardTotals.value?.pendingOrders || 0)
  const localPending = (orders.value || []).filter(o => {
    const status = (o.status || '').toLowerCase()
    if (!status) return false
    return !['fulfilled', 'completed', 'cancelled'].includes(status)
  }).length
  return Math.max(apiPending, dashboardPending, localPending, 0)
})

// UI / modal state
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
// Receipt modal state
const showReceiptModal = ref(false)
const receiptData = ref({})
const estimatedDeliveryDateTime = ref('')
const deliveryScheduleConfirmed = ref(false)
const savingEstimatedDelivery = ref(false)
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href
// Supplier submit modal state
const supplierSubmitModalVisible = ref(false)
const submitForm = ref({ name: '', price: null, category: '', per_pack_or_individual: '', date_made: '', pack_quantity: null, pack_unit: '' })
const todayDate = new Date().toLocaleDateString('en-CA')
const submitSubmitting = ref(false)
const submitError = ref('')
const currentSubmitOrderId = ref(null)
const lastOrderCheck = ref(new Date().toISOString())

// Edit product modal state
const editProductModalVisible = ref(false)
const editFieldType = ref(null) // 'category', 'pricing', 'price', 'expiration'
const editFields = [
  { id: 'category', label: 'Category' },
  { id: 'pricing', label: 'Pricing Type' },
  { id: 'price', label: 'Price' }
]
const editForm = ref({ id: null, name: '', price: null, category: '', per_pack_or_individual: '', expires_at: '' })
const editSubmitting = ref(false)
const editError = ref('')

function selectEditField(fieldId) {
  editFieldType.value = fieldId
  editError.value = ''
}

// Header/profile dropdown state and owner layout ref
const profileDropdownVisible = ref(false)
const ownerLayout = ref(null)

function toggleProfileDropdown() { profileDropdownVisible.value = !profileDropdownVisible.value }
function closeProfileDropdown() { profileDropdownVisible.value = false }

function openInfoFromHeader() {
  closeProfileDropdown()
  try {
    if (ownerLayout.value && typeof ownerLayout.value.openInfoModal === 'function') {
      ownerLayout.value.openInfoModal()
      return
    }
  } catch (e) {}
  try { window.dispatchEvent(new Event('open-owner-info')); return } catch (e) {}
  const infoBtn = document.querySelector('.admin-info-btn')
  if (infoBtn) infoBtn.click()
}

function triggerLogoutFromHeader() {
  closeProfileDropdown()
  askLogout()
}

// Close dropdown when clicking outside
window.addEventListener('click', (e) => { try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {} })

onMounted(async () => {
  try {
    // Try /api/me then /api/profile then manager profile as last resort
    let res = null
    try {
      res = await axios.get('/api/me', { withCredentials: true })
    } catch (e) {
      try {
        res = await axios.get('/api/profile', { withCredentials: true })
      } catch (e2) {
        try {
          res = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
        } catch (e3) {
          res = null
        }
      }
    }

    if (res && res.data) {
      // Debug: log raw profile response to help diagnose missing fields
      try { console.debug('profile response', res.data) } catch (e) {}

      const raw = res.data.user || res.data || {}

      // Normalize user profile fields to what OwnerPanelLayout expects
      const normalized = {
        id: raw.id,
        username: raw.username || raw.user_name || raw.user || null,
        fullName: raw.fullName || raw.full_name || raw.name || raw.username || null,
        full_name: raw.fullName || raw.full_name || raw.name || raw.username || null,
        role: (raw.role || raw.user_role || raw.type || '') ? String(raw.role || raw.user_role || raw.type) : null,
        email: raw.email || null,
        contact: raw.contact || raw.phone_number || raw.phone || null,
        branch_id: raw.branch_id || raw.branch || null,
        accountId: raw.accountId || raw.account_id || (raw.id ? 'kk' + String(raw.id).padStart(5, '0') : null),
        avatarUrl: (raw.avatarUrl || raw.avatar_url) ? (raw.avatarUrl || raw.avatar_url) : null,
      }

      userProfile.value = normalized
    }
  } catch (e) {}

  try {
    // Only request manager/logistics dashboard if user has a manager/admin role
    const roleUpper = (userProfile.value.role || '').toString().toUpperCase()
    const managerRoles = ['MANAGER', 'MANAGER_HR', 'OWNER', 'ADMIN', 'SUPER_ADMIN']
    if (managerRoles.includes(roleUpper)) {
      const dash = await axios.get('/api/manager/logistics/dashboard', { withCredentials: true })
      if (dash && dash.data && typeof dash.data === 'object') dashboardTotals.value = {
        totalSuppliers: dash.data.totalSuppliers || dash.data.total_suppliers || 0,
        activeDeliveries: dash.data.activeDeliveries || dash.data.active_deliveries || 0,
        pendingOrders: dash.data.pendingOrders || dash.data.pending_orders || 0
      }
    }
  } catch (e) {}

  try {
    // Only load suppliers/deliveries when manager/admin role
    const roleUpper = (userProfile.value.role || '').toString().toUpperCase()
    const managerRoles = ['MANAGER', 'MANAGER_HR', 'OWNER', 'ADMIN', 'SUPER_ADMIN']
    if (managerRoles.includes(roleUpper)) {
        // suppliers list removed from supplier panel UI

      try {
        const dres = await axios.get('/api/logistics/deliveries', { withCredentials: true })
        if (dres && dres.data) {
          if (Array.isArray(dres.data)) deliveries.value = dres.data
          else if (Array.isArray(dres.data.data)) deliveries.value = dres.data.data
          else deliveries.value = []
        }
      } catch (e) { console.warn('Failed to load deliveries', e) }
    }
  } catch (e) { console.warn('Failed to determine role for loading logistics data', e) }

  // load supplier orders for supplier user
  try {
    await loadOrders()
    // initialize lastOrderCheck to latest returned order timestamp (created or updated)
    try {
      const maxTs = orders.value.reduce((max, o) => {
        const candidates = [o.updated_at || o.updatedAt, o.created_at || o.createdAt].filter(Boolean)
        const latest = candidates.reduce((m, t) => (t && new Date(t) > new Date(m) ? t : m), max)
        return latest
      }, lastOrderCheck.value)
      lastOrderCheck.value = maxTs || lastOrderCheck.value
    } catch (e) {}
  } catch (e) { console.warn('Failed to load supplier orders', e) }

  // Load products for the current user's branch (show supplier products)
  try {
    if (userProfile.value && (userProfile.value.branch_id || userProfile.value.id)) {
      console.debug('Loading products for user', userProfile.value)
      await loadProducts()
    }
  } catch (e) { console.warn('Failed to load supplier products', e) }

  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      notificationCounts.value = { supplier: Number(res.data.counts?.supplier || 0) }
    }
  } catch (e) {
    notificationCounts.value = { supplier: 0 }
  }
})

watch(supplierPendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending supplier orders.', 'info')
    hasNotified.value = true
  }
})

async function loadProducts() {
  loadingProducts.value = true
  try {
    const pres = await axios.get('/api/staff/inventory/products', { withCredentials: true })
    if (pres && pres.data) {
      if (Array.isArray(pres.data)) products.value = pres.data
      else if (Array.isArray(pres.data.data)) products.value = pres.data.data
      else products.value = []
    }
  } catch (e) {
    console.warn('Failed to load products', e)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

// Poll for new real (non-broadcast) supplier orders and notify supplier
let _ordersPollTimer = null

function normalizeSupplierOrder(order) {
  if (!order || typeof order !== 'object') return order
  return {
    ...order,
    procurementRequest: order.procurementRequest || order.procurement_request || null,
    branch: order.branch || null,
    product: order.product || null,
  }
}

function startOrdersPolling() {
  stopOrdersPolling()
  _ordersPollTimer = setInterval(async () => {
    try {
      const res = await axios.get('/api/supplier-orders', { withCredentials: true })
      const rawList = res.data.data || res.data || []
      const list = Array.isArray(rawList) ? rawList.map(normalizeSupplierOrder) : []
      // find any non-broadcast orders created or updated after lastOrderCheck
      const newReal = list.filter(o => {
        const created = o.created_at || o.createdAt
        const updated = o.updated_at || o.updatedAt
        const createdNew = created && new Date(created) > new Date(lastOrderCheck.value)
        const updatedNew = updated && new Date(updated) > new Date(lastOrderCheck.value)
        return !o.is_broadcast && (createdNew || updatedNew)
      })
      if (newReal && newReal.length > 0) {
        showToast('New order placed for your products', 'info')
        // update orders list and last check (consider both created_at and updated_at)
        orders.value = list
        const maxTs = list.reduce((max, o) => {
          const candidates = [o.created_at || o.createdAt, o.updated_at || o.updatedAt].filter(Boolean)
          const latest = candidates.reduce((m, t) => (t && new Date(t) > new Date(m) ? t : m), max)
          return latest
        }, lastOrderCheck.value)
        lastOrderCheck.value = maxTs
      }
    } catch (e) {
      // ignore polling errors
    }
  }, 15000)
}

function stopOrdersPolling() {
  if (_ordersPollTimer) {
    clearInterval(_ordersPollTimer)
    _ordersPollTimer = null
  }
}

// Orders for supplier
async function loadOrders() {
  ordersLoading.value = true
  try {
    const res = await axios.get('/api/supplier-orders', { withCredentials: true })
    const rawList = res.data.data || res.data || []
    orders.value = Array.isArray(rawList) ? rawList.map(normalizeSupplierOrder) : []
    dashboardTotals.value.pendingOrders = orders.value.filter(o => o.status === 'pending').length
    // fulfilled count could be used elsewhere
  } catch (e) {
    console.error('Failed to load orders', e)
  } finally {
    ordersLoading.value = false
  }
}

async function fulfillOrder(id) {
  // kept for backward compatibility but unused in the new flow
  return
}

async function completeTransaction(id) {
  try {
    // Ask supplier to provide expiry at transaction complete (required)
    const result = await Swal.fire({
      title: 'Complete Transaction?',
      html: `
        <div style="text-align:left; margin-top:10px;">
          <div style="font-weight:700; margin-bottom:6px;">Expiration date (required)</div>
          <input id="supplier-expiry-input" type="datetime-local" style="width:100%; padding:10px; border-radius:8px; border:1px solid #d1d5db;" />
          <div style="color:#6b7280; font-size:12px; margin-top:6px;">This expiry is stored per supplier order, not per product.</div>
        </div>
      `,
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Yes, Complete',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      preConfirm: () => {
        const el = document.getElementById('supplier-expiry-input')
        const v = el ? el.value : ''
        if (!v) {
          Swal.showValidationMessage('Expiry date is required')
          return false
        }
        // Convert datetime-local value (YYYY-MM-DDTHH:mm) to what backend expects
        return v
      }
    })

    if (!result.isConfirmed) return

    // Mark the supplier order as on_delivery and store expiry
    const expiresAt = result.value
    const res = await axios.put(
      `/api/supplier-orders/${id}/status`,
      { status: 'on_delivery', expires_at: expiresAt },
      { withCredentials: true }
    )

    if (res && res.data) {
      receiptData.value = normalizeSupplierOrder(res.data)
      estimatedDeliveryDateTime.value = receiptData.value.estimated_delivery_datetime || ''
      deliveryScheduleConfirmed.value = Boolean(estimatedDeliveryDateTime.value)
      showReceiptModal.value = true
    }

    try {
      await loadOrders()
    } catch (loadErr) {
      console.error('Failed to reload orders', loadErr)
    }
  } catch (e) {
    console.error('completeTransaction failed', e)
    showToast('Failed to complete transaction', 'error')
  }
}


function openSupplierSubmitModal(order) {
  // Prefill product name if procurement request provides it
  submitError.value = ''
  submitForm.value = { name: '', price: null, category: '', per_pack_or_individual: '', date_made: '', pack_quantity: null, pack_unit: '' }
  currentSubmitOrderId.value = null
  if (!order) return
  currentSubmitOrderId.value = order.id
  // Try to prefill from procurementRequest or product name
  const suggested = order.procurementRequest?.product?.name || order.product?.name || ''
  submitForm.value.name = suggested
  supplierSubmitModalVisible.value = true
}

function closeSupplierSubmitModal() {
  if (submitSubmitting.value) return
  supplierSubmitModalVisible.value = false
  submitError.value = ''
  submitForm.value = { name: '', price: null, category: '', per_pack_or_individual: '', date_made: '', pack_quantity: null, pack_unit: '' }
  currentSubmitOrderId.value = null
}

function openEditProductModal(product) {
  if (!product) return
  editError.value = ''
  editFieldType.value = 'price' // Default to editing price
  editForm.value = {
    id: product.id,
    name: product.name,
    price: product.price,
    category: product.category || '',
    per_pack_or_individual: product.per_pack_or_individual || '',
    expires_at: product.expires_at ? formatDateTimeLocal(product.expires_at) : ''
  }
  editProductModalVisible.value = true
}

function closeEditProductModal() {
  if (editSubmitting.value) return
  editProductModalVisible.value = false
  editError.value = ''
  editFieldType.value = null
  editForm.value = { id: null, name: '', price: null, category: '', per_pack_or_individual: '', expires_at: '' }
}

async function saveProductChanges() {
  if (!editForm.value.id) return
  if (!editFieldType.value) { editError.value = 'Please select a field to edit'; return }

  // Validate only the selected field
  if (editFieldType.value === 'category' && !editForm.value.category) {
    editError.value = 'Category is required'; return
  }
  if (editFieldType.value === 'pricing' && !editForm.value.per_pack_or_individual) {
    editError.value = 'Pricing type is required'; return
  }
  if (editFieldType.value === 'price' && (!editForm.value.price || editForm.value.price <= 0)) {
    editError.value = 'Price is required and must be greater than 0'; return
  }

  editSubmitting.value = true
  editError.value = ''
  try {
    // Build payload with only the selected field
    const payload = {}
    if (editFieldType.value === 'category') payload.category = editForm.value.category
    if (editFieldType.value === 'pricing') payload.per_pack_or_individual = editForm.value.per_pack_or_individual
    if (editFieldType.value === 'price') payload.price = editForm.value.price

    const res = await axios.put(`/api/staff/inventory/products/${editForm.value.id}`, payload, { withCredentials: true })
    if (res && res.data) {
      showToast('Product updated successfully', 'success')
      await loadProducts()
      closeEditProductModal()
    } else {
      const msg = res.data?.error || res.data?.message || 'Failed to update product'
      editError.value = msg
      showToast(msg, 'error')
    }
  } catch (e) {
    console.error('saveProductChanges failed', e)
    const msg = e.response?.data?.error || e.response?.data?.message || 'Failed to update product'
    editError.value = msg
    showToast(msg, 'error')
  } finally {
    editSubmitting.value = false
  }
}

async function submitProductForm() {
  if (!currentSubmitOrderId.value) return
  if (!submitForm.value.name) { await Swal.fire({ icon: 'error', title: 'Validation', text: 'Product name is required' }); return }
  if (!submitForm.value.category) { await Swal.fire({ icon: 'error', title: 'Validation', text: 'Category is required' }); return }
  if (!submitForm.value.per_pack_or_individual) { await Swal.fire({ icon: 'error', title: 'Validation', text: 'Pricing type is required' }); return }
  // If per-pack, require pack quantity and unit
  if (submitForm.value.per_pack_or_individual === 'per_pack') {
    if (submitForm.value.pack_quantity === null || submitForm.value.pack_quantity === undefined || Number(submitForm.value.pack_quantity) <= 0) { await Swal.fire({ icon: 'error', title: 'Validation', text: 'Pack quantity is required and must be greater than 0' }); return }
    if (!submitForm.value.pack_unit) { await Swal.fire({ icon: 'error', title: 'Validation', text: 'Pack unit is required' }); return }
  }
  if (submitForm.value.price === null || submitForm.value.price === undefined) { await Swal.fire({ icon: 'error', title: 'Validation', text: 'Price is required' }); return }
  // Ensure price is greater than zero
  if (Number(submitForm.value.price) <= 0) { await Swal.fire({ icon: 'error', title: 'Validation', text: 'Price must be greater than 0' }); return }
  submitSubmitting.value = true
  submitError.value = ''
  try {
    const payload = {
      name: submitForm.value.name,
      price: submitForm.value.price,
      category: submitForm.value.category,
      per_pack_or_individual: submitForm.value.per_pack_or_individual,
      pack_quantity: submitForm.value.pack_quantity,
      pack_unit: submitForm.value.pack_unit,
      date_made: submitForm.value.date_made || null
    }
    const res = await axios.post(`/api/supplier-orders/${currentSubmitOrderId.value}/submit-product`, payload, { withCredentials: true })
    if (res && res.data && res.data.ok) {
      showToast('Product submitted and linked to order', 'success')
      await loadOrders()
      await loadProducts()
      closeSupplierSubmitModal()
    } else {
      const msg = res.data?.error || res.data?.message || 'Failed to submit product'
      submitError.value = msg
      showToast(msg, 'error')
    }
  } catch (e) {
    console.error('submitProductForm failed', e)
    const msg = e.response?.data?.error || e.response?.data?.message || 'Failed to submit product'
    submitError.value = msg
    showToast(msg, 'error')
  } finally {
    submitSubmitting.value = false
  }
}

function closeReceipt() {
  if (!deliveryScheduleConfirmed.value) {
    showToast('Please confirm the estimated delivery schedule before closing', 'error')
    return
  }
  showReceiptModal.value = false
  receiptData.value = {}
  estimatedDeliveryDateTime.value = ''
  deliveryScheduleConfirmed.value = false
}

function printReceipt() {
  try {
    const orderDate = receiptData.value.created_at || receiptData.value.createdAt || new Date().toISOString()
    const deliveryDate = estimatedDeliveryDateTime.value ? new Date(estimatedDeliveryDateTime.value).toLocaleString() : 'Not specified'

    const html = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Transaction Receipt - Order #${receiptData.value.id}</title>
        <style>
          * { margin: 0; padding: 0; box-sizing: border-box; }
          body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            color: #1f2937;
            max-width: 800px;
            margin: 0 auto;
          }
          .receipt-container {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 30px;
            background: white;
          }
          .receipt-header {
            text-align: center;
            border-bottom: 3px solid #0b6e3a;
            padding-bottom: 20px;
            margin-bottom: 25px;
          }
          .receipt-header h1 {
            color: #0b6e3a;
            font-size: 28px;
            margin-bottom: 5px;
          }
          .receipt-header p {
            color: #6b7280;
            font-size: 14px;
          }
          .section {
            margin-bottom: 25px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #7c3aed;
          }
          .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
          }
          .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
          }
          .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
          }
          .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
          }
          .detail-value {
            color: #111827;
            font-size: 14px;
            font-weight: 500;
          }
          .financial-summary {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #0b6e3a;
          }
          .financial-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
          }
          .financial-total {
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #0b6e3a;
          }
          .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
          }
          .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
          }
          .status-approved { background: #d1fae5; color: #065f46; }
          .status-pending { background: #fef3c7; color: #92400e; }
          .status-on-delivery { background: #dbeafe; color: #1e40af; }
          .status-rejected { background: #fee2e2; color: #991b1c; }
        </style>
      </head>
      <body>
        <div class="receipt-container">
          <div class="receipt-header">
            <h1>🏪 Chikin Tayo</h1>
            <p>Transaction Receipt - Official Delivery Confirmation</p>
          </div>

          <div class="section">
            <div class="section-title">📋 Order Information</div>
            <div class="details-grid">
              <div class="detail-row">
                <span class="detail-label">Order ID:</span>
                <span class="detail-value">#${receiptData.value.id || ''}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Order Date:</span>
                <span class="detail-value">${formatDate(orderDate)}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                  <span class="status-badge ${getStatusClass(receiptData.value.status).replace('status-', 'status-')}">${receiptData.value.status || ''}</span>
                </span>
              </div>
            </div>
          </div>

          <div class="section">
            <div class="section-title">📦 Product Details</div>
            <div class="details-grid">
              <div class="detail-row" style="grid-column: 1 / -1;">
                <span class="detail-label">Product Name:</span>
                <span class="detail-value" style="font-weight: 700;">${receiptData.value.product?.name || ''}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Category:</span>
                <span class="detail-value">${receiptData.value.product?.category || 'N/A'}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Pricing Type:</span>
                <span class="detail-value">${formatPricingType(receiptData.value.product?.per_pack_or_individual)}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Unit Price:</span>
                <span class="detail-value">${formatPrice(receiptData.value.product?.price)}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Quantity:</span>
                <span class="detail-value" style="font-weight: 700;">${receiptData.value.quantity || 0} units</span>
              </div>
            </div>
          </div>

          <div class="section">
            <div class="section-title">🚚 Delivery Information</div>
            <div class="details-grid">
              <div class="detail-row">
                <span class="detail-label">Branch:</span>
                <span class="detail-value">${receiptData.value.branch?.name || receiptData.value.branch_id || ''}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Variance:</span>
                <span class="detail-value">${formatVariance(receiptData.value.procurementRequest?.variance_quantity)}</span>
              </div>
            </div>
          </div>

          <div class="section">
            <div class="section-title">💰 Financial Summary</div>
            <div class="financial-summary">
              <div class="financial-row">
                <span class="detail-label">Unit Price:</span>
                <span class="detail-value">${formatPrice(receiptData.value.product?.price)}</span>
              </div>
              <div class="financial-row">
                <span class="detail-label">Quantity:</span>
                <span class="detail-value">× ${receiptData.value.quantity || 0}</span>
              </div>
              <div class="financial-row financial-total">
                <span class="detail-label" style="font-size: 18px;">Total Amount:</span>
                <span class="detail-value" style="color: #0b6e3a; font-size: 22px;">${formatPrice(receiptData.value.product?.price * receiptData.value.quantity)}</span>
              </div>
            </div>
          </div>

          ${receiptData.value.procurementRequest ? `
          <div class="section">
            <div class="section-title">📊 Procurement Details</div>
            <div class="details-grid">
              <div class="detail-row">
                <span class="detail-label">Procurement Request ID:</span>
                <span class="detail-value">#${receiptData.value.procurementRequest.id}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Procurement Status:</span>
                <span class="detail-value">${formatProcurementStatus(receiptData.value.procurementRequest.status)}</span>
              </div>
            </div>
          </div>
          ` : ''}

          <div class="section" style="border-left-color: #f59e0b;">
            <div class="section-title">⏰ Estimated Delivery</div>
            <div class="detail-row">
              <span class="detail-label">Estimated Delivery Date & Time:</span>
              <span class="detail-value" style="font-weight: 600;">${deliveryDate}</span>
            </div>
          </div>

          <div class="footer">
            <p><strong>Receipt generated on ${formatDate(new Date().toISOString())}</strong></p>
            <p style="margin-top: 5px;">Thank you for your business!</p>
          </div>
        </div>
      </body>
      </html>
    `

    const w = window.open('', '_blank', 'width=800,height=600')
    if (!w) return alert('Unable to open print window. Please allow popups for this site.')
    w.document.write(html)
    w.document.close()
    w.focus()
    setTimeout(() => {
      w.print()
      w.close()
    }, 250)
  } catch (e) {
    console.warn('Print failed', e)
    showToast('Failed to print receipt', 'error')
  }
}

function getStatusClass(status) {
  switch (status) {
    case 'fulfilled': return 'status-approved'
    case 'cancelled': return 'status-rejected'
    case 'on_delivery': return 'status-on-delivery'
    default: return 'status-pending'
  }
}

function canSubmitProduct(order) {
  // Allow supplier to submit a product when:
  // - There's no linked product yet
  // - OR the linked product is not from this supplier
  // - OR the linked product has no positive price
  try {
    const myId = userProfile.value?.id
    const prod = order.product
    if (!prod) return true
    // If product has no price or price <= 0 -> supplier should supply price
    const price = Number(prod.price || 0)
    if (isNaN(price) || price <= 0) return true
    // If product.supplier_id is different from the current supplier (order.supplier_id), allow submission
    // order.supplier_id should equal current user's id for supplier orders
    if (prod.supplier_id && Number(prod.supplier_id) !== Number(order.supplier_id)) return true
    // Otherwise product already provided by this supplier with price > 0 -> no need to submit
    return false
  } catch (e) {
    return true
  }
}

function canCompleteTransaction(order) {
  try {
    if (!order || order.status !== 'pending') return false
    if (!order.product || !order.product.id) return false

    // Only show "Transaction complete" for actual placed orders (is_broadcast = false)
    // NOT for initial submission broadcasts (is_broadcast = true)
    // This ensures only the selected supplier sees the complete button
    // MySQL returns boolean columns as 0/1 integers, while some API responses
    // may serialize them as true/false. Treat both false representations alike.
    if (!(order.is_broadcast === false || Number(order.is_broadcast) === 0)) return false

    // Supplier can complete transaction only after procurement/finance flow has
    // reached order-ready or delivery states for the linked request.
    const reqStatus = order.procurementRequest?.status || ''
    const allowed = ['pending_order_to_supplier', 'delivery_pending', 'ongoing_delivery', 'on_delivery']
    return allowed.includes(reqStatus)
  } catch (e) {
    return false
  }
}

function formatPrice(val) {
  if (val === null || val === undefined) return '₱0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '₱0.00'
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatVariance(val) {
  if (val === null || val === undefined || val === 0) return '-'
  const n = Number(val)
  if (Number.isNaN(n)) return '-'
  return n > 0 ? `+${n}` : String(n)
}

function formatDate(dt) {
  try {
    const d = new Date(dt)
    if (isNaN(d.getTime())) return ''
    return d.toLocaleString()
  } catch (e) {
    return ''
  }
}

function formatDateTimeLocal(dt) {
  try {
    if (!dt) return ''
    const d = new Date(dt)
    if (isNaN(d.getTime())) return ''
    // Format for datetime-local input: YYYY-MM-DDTHH:mm
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${year}-${month}-${day}T${hours}:${minutes}`
  } catch (e) {
    return ''
  }
}

function formatPricingType(type) {
  const typeMap = {
    'individual': 'Individual',
    'per_pack': 'Per Pack',
    'both': 'Both (Individual & Per Pack)'
  }
  return typeMap[type] || type
}

function getCurrentDateTimeLocal() {
  try {
    const now = new Date()
    const year = now.getFullYear()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')
    const hours = String(now.getHours()).padStart(2, '0')
    const minutes = String(now.getMinutes()).padStart(2, '0')
    return `${year}-${month}-${day}T${hours}:${minutes}`
  } catch (e) {
    return ''
  }
}

function formatProcurementStatus(status) {
  if (!status) return 'N/A'
  const statusMap = {
    'pending_order_to_supplier': 'Pending Order to Supplier',
    'delivery_pending': 'Delivery Pending',
    'ongoing_delivery': 'Ongoing Delivery',
    'on_delivery': 'On Delivery',
    'delivered': 'Delivered',
    'completed': 'Completed',
    'cancelled': 'Cancelled'
  }
  return statusMap[status] || status
}

function onProductAdded(newProduct) {
  // If we already have products loaded, add the new one at top; otherwise try reloading
  try {
    if (products.value && Array.isArray(products.value)) {
      products.value.unshift(newProduct)
    } else {
      loadProducts()
    }
  } catch (e) {
    loadProducts()
  }
}

async function confirmEstimatedDelivery() {
  if (!estimatedDeliveryDateTime.value || !receiptData.value?.id) return

  savingEstimatedDelivery.value = true
  try {
    const res = await axios.put(
      `/api/supplier-orders/${receiptData.value.id}/estimated-delivery`,
      { estimated_delivery_datetime: estimatedDeliveryDateTime.value },
      { withCredentials: true }
    )

    if (res && res.data) {
      showToast('Delivery schedule confirmed successfully', 'success')
      // Update receipt data with the new estimated delivery
      receiptData.value = { ...receiptData.value, estimated_delivery_datetime: estimatedDeliveryDateTime.value }
      deliveryScheduleConfirmed.value = true
    }
  } catch (e) {
    console.error('confirmEstimatedDelivery failed', e)
    const msg = e.response?.data?.error || e.response?.data?.message || 'Failed to save delivery schedule'
    showToast(msg, 'error')
  } finally {
    savingEstimatedDelivery.value = false
  }
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

async function askLogout() {
  try {
    const result = await Swal.fire({
      title: 'Logout from Supplier Panel?',
      text: 'This will end your current session for Chikin Tayo.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, logout',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d'
    })
    if (result.isConfirmed) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

function onProfileUpdated(newData) {
  Object.assign(userProfile.value, newData)
}
</script>

<style scoped>
@import '../css/adminpanel.css';
/* Supplier panel product grid */
.overview-grid { display:flex; gap:0.75rem; margin-bottom:0.75rem }
.overview-card { background:#fff; border-radius:10px; padding:0.75rem 1rem; box-shadow:0 6px 18px rgba(15,23,42,0.04); border:1px solid #eef2f6; display:flex; gap:0.5rem; align-items:center }
.overview-label { color:#6b7280; font-weight:600 }
.overview-value { font-weight:700; color:#111827; margin-left:6px }
.hr-stat-card { position: relative; }

.supplier-products {
  width: 100%;
  min-width: 0;
  margin-top: 1rem;
  padding: 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(16, 24, 40, 0.06);
  box-sizing: border-box;
}
.product-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap:0.75rem; margin-top:0.5rem }
.product-card { background:#fff; border-radius:10px; padding:0.75rem; box-shadow:0 8px 24px rgba(15,23,42,0.06); border:1px solid #eef2f6 }
.product-name { font-weight:700; color:#0f172a; font-size:0.95rem }
.product-category { color:#7c3aed; font-size:0.8rem; font-weight:600; margin-top:4px; text-transform:capitalize }
.product-type { display:inline-block; font-size:0.75rem; font-weight:600; padding:3px 8px; border-radius:6px; margin-top:4px; text-transform:capitalize }
.product-type.type-individual { background:#dbeafe; color:#1e40af }
.product-type.type-per_pack { background:#d1fae5; color:#065f46 }
.product-type.type-both { background:#fef3c7; color:#92400e }
.product-meta { display:flex; flex-direction:column; gap:6px; margin-top:8px }
.product-price { color:#0b6e3a; font-weight:700; font-size:0.95rem }
.product-expiry { color:#7c2d12; font-size:0.8rem; display:flex; gap:4px }
.expiry-label { font-weight:600 }
.product-stock { color:#6b7280 }
.product-card-header { display:flex; justify-content:space-between; align-items:flex-start; gap:8px }
.btn-edit { background:none; border:none; color:#7c3aed; cursor:pointer; font-size:1.2rem; padding:0 }
.btn-edit:hover { opacity:.7 }

/* Pricing type options */
.pricing-type-options { display:flex; flex-direction:column; gap:10px; margin-top:6px; padding:8px; background:#f9fafb; border-radius:8px; border:1px solid #e5e7eb }
.option-group { display:flex; align-items:flex-start; gap:12px; cursor:pointer; padding:8px; border-radius:6px; transition:background 0.2s }
.option-group:hover { background:#f3f4f6 }
.option-group input[type="radio"] { margin-top:5px; cursor:pointer; accent-color:#7c3aed }
.option-label { display:flex; flex-direction:column; gap:4px; cursor:pointer; flex:1 }
.option-badge { display:inline-block; padding:4px 10px; border-radius:6px; font-size:0.85rem; font-weight:600; width:fit-content }
.option-desc { font-size:0.8rem; color:#6b7280 }

/* Receipt modal styles */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:1200 }
.modal { position:relative; max-height:90vh; overflow-y:auto }
.modal-card { background:#fff; border-radius:12px; box-shadow:0 18px 54px rgba(15,23,42,0.12); min-width:500px; max-width:600px; overflow:hidden }
.modal-header { background:#f9fafb; padding:1.25rem 1.5rem; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center }
.modal-header h3 { margin:0; font-size:1.1rem; font-weight:700; color:#111827 }
.modal-body { padding:1.5rem; max-height:calc(90vh - 180px); overflow-y:auto; display:flex; flex-direction:column; gap:1rem }
.modal-footer { padding:1rem 1.5rem; border-top:1px solid #e5e7eb; display:flex; gap:0.75rem; justify-content:flex-end; background:#f9fafb }
.form-group { display:flex; flex-direction:column; gap:6px }
.form-group.full-span { grid-column:1/-1 }
.form-group label { font-weight:600; color:#374151; font-size:0.9rem }
.form-group input, .form-group select { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:0.95rem }
.form-group input:focus, .form-group select:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.1) }
.error-msg { background:#fee2e2; color:#dc2626; padding:10px 12px; border-radius:8px; font-size:0.9rem; margin-top:8px }

/* Enhanced Receipt Styles */
.receipt-box-enhanced {
  width: 600px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
  padding: 0;
}

.receipt-header {
  background: linear-gradient(135deg, #0b6e3a 0%, #065f46 100%);
  color: white;
  padding: 20px 25px;
  display: flex;
  align-items: center;
  gap: 15px;
  border-radius: 10px 10px 0 0;
}

.receipt-logo {
  width: 60px;
  height: 60px;
  background: white;
  border-radius: 8px;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.receipt-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.receipt-title-section {
  flex: 1;
}

.receipt-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.receipt-subtitle {
  margin: 4px 0 0 0;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.9);
}

.receipt-body {
  padding: 20px 25px;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.receipt-section {
  background: #f9fafb;
  border-radius: 8px;
  padding: 15px;
  border-left: 4px solid #7c3aed;
}

.receipt-section-highlight {
  background: #f0fdf4;
  border-left-color: #0b6e3a;
}

.receipt-section-delivery {
  background: #fffbeb;
  border-left-color: #f59e0b;
}

.receipt-section-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
}

.section-icon {
  font-size: 1.2rem;
}

.receipt-section-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
}

.receipt-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.receipt-detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.receipt-detail-item.full-width {
  grid-column: 1 / -1;
}

.detail-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 0.95rem;
  color: #111827;
  font-weight: 500;
}

.detail-value-bold {
  font-weight: 700;
  color: #0f172a;
}

.detail-value-price {
  color: #0b6e3a;
  font-weight: 600;
  font-size: 1rem;
}

.receipt-financial-summary {
  background: white;
  border-radius: 6px;
  padding: 12px;
  border: 2px solid #0b6e3a;
}

.receipt-financial-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  font-size: 0.95rem;
}

.financial-label {
  color: #374151;
  font-weight: 500;
}

.financial-value {
  color: #111827;
  font-weight: 600;
}

.receipt-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 8px 0;
}

.receipt-total-row {
  padding-top: 10px;
}

.financial-total {
  font-size: 1.3rem;
  color: #0b6e3a;
  font-weight: 700;
}

.receipt-footer {
  padding: 15px 25px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  border-radius: 0 0 10px 10px;
}

.receipt-footer-info {
  text-align: center;
  margin-bottom: 12px;
}

.receipt-print-info {
  font-size: 0.8rem;
  color: #6b7280;
  font-style: italic;
}

.receipt-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.receipt-actions .btn-secondary {
  background:#f3f4f6;
  border:1px solid #e5e7eb;
  padding:8px 16px;
  border-radius:6px;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.receipt-actions .btn-secondary:hover {
  background: #e5e7eb;
}

.receipt-actions .btn-primary {
  background:#0b6e3a;
  color:#fff;
  padding:8px 16px;
  border-radius:6px;
  border:none;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.receipt-actions .btn-primary:hover {
  background: #065f46;
}

/* Delivery Input Styles */
.delivery-input-group {
  margin-top: 8px;
}

.delivery-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: 'Arial', sans-serif;
  transition: all 0.2s;
}

.delivery-input:focus {
  outline: none;
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.form-hint {
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 4px;
  font-style: italic;
}

@media (max-width: 640px) {
  .receipt-box-enhanced {
    width: 100%;
  }

  .receipt-details-grid {
    grid-template-columns: 1fr;
  }

  .receipt-header {
    flex-direction: column;
    text-align: center;
  }
}

/* Field selector for edit modal */
.field-selector { display:flex; flex-wrap:wrap; gap:0.5rem; padding:0.5rem 0; border-bottom:1px solid #e5e7eb; margin-bottom:1rem }
.field-btn { padding:8px 12px; border-radius:6px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:500; cursor:pointer; transition:all 0.2s }
.field-btn:hover { border-color:#7c3aed; color:#7c3aed }
.field-btn.active { background:#7c3aed; color:#fff; border-color:#7c3aed }

/* Edit field section */
.edit-field-section { display:flex; flex-direction:column; gap:0.75rem }
.edit-field-section label { font-weight:600; color:#374151; font-size:0.95rem }
.full-width-input { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:0.95rem; width:100% }
.full-width-input:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.1) }


.receipt-box { background:#fff; padding:1rem 1.25rem; border-radius:10px; width:420px; box-shadow:0 12px 36px rgba(15,23,42,0.18); border:1px solid #eef2f6 }
.receipt-box h3 { margin:0 0 0.6rem 0 }
.receipt-body p { margin:6px 0 }
.receipt-actions { display:flex; justify-content:flex-end; gap:0.5rem; margin-top:0.75rem }
.receipt-actions .btn-secondary { background:#f3f4f6; border:1px solid #e5e7eb; padding:6px 10px; border-radius:6px }
.receipt-actions .btn-primary { background:#0b6e3a; color:#fff; padding:6px 10px; border-radius:6px; border:none }

/* Layout refinements for SupplierPanel */
.supplier-page {
  width: 100%;
  min-width: 0;
}

.supplier-hero {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 18px 16px;
  margin-bottom: 20px;
  background: linear-gradient(135deg, #fffaf5 0%, #ffffff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.05);
}

.supplier-hero__eyebrow {
  display: inline-block;
  margin-bottom: 6px;
  color: #c25a12;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.supplier-hero__title {
  margin: 0;
  color: #1f2937;
  font-size: 26px;
  line-height: 1.1;
}

.supplier-hero__subtitle {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
  max-width: 560px;
}

.supplier-hero__action {
  flex-shrink: 0;
  margin-top: 2px;
  padding: 8px 14px;
  background: #4b5563;
  color: #ffffff;
  border: 0;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(75, 85, 99, 0.1);
}

.supplier-hero__action:hover:not(:disabled) { background: #374151; }
.supplier-hero__action:disabled { opacity: 0.6; cursor: not-allowed; }

.panel-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  overflow: visible;
}

.panel-content > * {
  width: 100%;
  max-width: 100%;
  min-width: 0;
  box-sizing: border-box;
}

.panel-section {
  width: 100%;
  min-width: 0;
  margin: 0;
  padding: 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(16, 24, 40, 0.06);
  box-sizing: border-box;
}

:deep(.logistics-panel) {
  width: 100%;
  min-width: 0;
  padding: 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(16, 24, 40, 0.06);
  box-sizing: border-box;
}

:deep(.admin-layout.no-profile-column) .admin-main {
  overflow: visible;
}

:deep(.admin-layout.no-profile-column) .admin-main > * {
  min-width: 0;
  max-width: 100%;
}

.table-container { overflow-x:auto; background:transparent; border-radius:8px }
.data-table { width:100%; border-collapse:separate; border-spacing:0; min-width:720px }
.data-table th, .data-table td { padding:10px 12px; border-bottom:1px solid #eef2f6; vertical-align:middle }
.data-table thead th { background:transparent; color:#374151; font-weight:700; text-align:left }
.action-cell { min-width:200px; white-space:nowrap; text-align:right }
.status-badge { display:inline-block; padding:4px 8px; border-radius:8px; font-size:0.9rem }
.alert-badge { margin-top:6px; display:inline-block; padding:3px 8px; border-radius:999px; font-size:0.75rem; font-weight:600; background:#fff1f2; color:#be123c }
.btn-small { padding:6px 8px; font-size:0.85rem }

.product-grid { gap:0.75rem }
.product-card { display:flex; flex-direction:column; justify-content:space-between }

.panel-badge { position:absolute; top:-8px; right:-8px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }
.stat-alert { border:1px solid #fecaca; box-shadow:0 0 0 2px rgba(239,68,68,0.12) }

@media (max-width: 900px) {
  .supplier-hero { flex-direction: column; gap: 12px; }
  .supplier-hero__action { width: 100%; }
  .overview-grid { flex-direction:column }
  .data-table { min-width:600px }
  .product-grid { grid-template-columns: repeat(auto-fill,minmax(160px,1fr)) }
}

@media (max-width: 480px) {
  .data-table { min-width:480px }
  .action-cell { min-width:160px }
}

/* Scroll container similar to ProcurementManagerPanel */
.requests-container { background:transparent; border-radius:8px }
.requests-scroll { max-height:360px; overflow:auto; padding-right:6px }
.requests-scroll .data-table { min-width:640px }

/* When profile column is hidden, lay out main + side like other manager panels */
:deep(.admin-layout.no-profile-column) {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
  column-gap: 24px;
  row-gap: 0;
  align-items: start;
}

:deep(.admin-layout.no-profile-column) .admin-main {
  width: 100%;
  min-width: 0;
}

:deep(.admin-layout.no-profile-column) .admin-side {
  width: 100%;
  min-width: 0;
  padding-top: 0;
  box-sizing: border-box;
}

:deep(.admin-layout.no-profile-column > .admin-side > .header-actions-top) {
  transform: translateY(-8px);
}

/* Compact Supplier profile pill matching the reference layout. */
:deep(.admin-card.admin-card--stacked) {
  align-self: flex-end;
  width: auto;
  max-width: 100%;
  min-width: 0;
  padding: 8px 12px;
  border-radius: 12px;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  text-align: left;
}

:deep(.admin-card.admin-card--stacked .admin-card__header--stacked) {
  margin: 0;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

:deep(.admin-card.admin-card--stacked .admin-avatar) {
  width: 36px;
  height: 36px;
  flex: 0 0 36px;
}

:deep(.admin-card.admin-card--stacked .admin-header-text) {
  min-width: 0;
  text-align: left;
  white-space: nowrap;
}

:deep(.admin-card.admin-card--stacked .admin-label),
:deep(.admin-card.admin-card--stacked .admin-role) {
  display: none;
}

:deep(.admin-card.admin-card--stacked .admin-name) {
  display: block;
  max-width: 220px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
}

:deep(.admin-card.admin-card--stacked .admin-card__body--stacked),
:deep(.admin-card.admin-card--stacked .admin-card__footer--stacked) {
  display: none;
}

/* Keep the Supplier announcements card lower in the side column. */
.announcements-panel {
  max-width: 100%;
  box-sizing: border-box;
  margin-top: 0 !important;
}

/* Keep the Supplier profile control in the main column's top-right corner. */
:deep(.admin-page .admin-main-header) {
  position: relative;
}

:deep(.admin-page .admin-main-header-top > .header-actions-top) {
  position: absolute;
  top: 0;
  right: 8px;
  margin: 0;
  z-index: 2;
}

:deep(.admin-page .admin-main-header-top > .header-actions-top > .header-actions-top) {
  position: static;
  max-width: 100%;
  margin: 0;
}

:deep(.admin-page .admin-main-header-top > .header-actions-top .header-profile-wrapper) {
  max-width: 260px;
  overflow: hidden;
}

:deep(.admin-page .admin-main-header-top > .header-actions-top .header-profile-btn) {
  max-width: 100%;
  min-width: 0;
}

:deep(.admin-page .admin-main-header-top > .header-actions-top .header-name) {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

</style>
