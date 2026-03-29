<template>
  <OwnerPanelLayout
    :userProfile="staffProfile"
    :panelTitle="pageTitle"
    panelDescription="Manage your branch inventory"
    :enableProfileUpdate="true"
    :showProfileColumn="false"
    @logout="logout"
  >
    <template #headerActions>
      <div class="header-profile-wrapper" ref="headerProfileWrapper" style="margin: 0 0 12px;">
        <button class="header-profile-btn" @click.stop="toggleProfileMenu" type="button">
          <div class="header-avatar">
            <div v-if="staffProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url(' + staffProfile.avatarUrl + ')' }"></div>
            <div v-else class="header-avatar-initials">{{ staffProfile.fullName ? (staffProfile.fullName.charAt(0) || 'U') : 'U' }}</div>
          </div>
          <div class="header-name">{{ ((staffProfile.fullName || staffProfile.full_name) || ((staffProfile.role || 'STAFF') + (staffProfile.branch_name ? ' - ' + staffProfile.branch_name : (staffProfile.branch ? ' - ' + staffProfile.branch : '')) )).toUpperCase() }}</div>
        </button>
        <input id="staff-avatar-input" type="file" accept="image/*" @change="onAvatarChange" style="display: none" />

        <!-- Profile dropdown -->
        <div v-if="showProfileMenu" class="profile-dropdown" ref="profileDropdown" @click.stop>
          <button class="dropdown-item" @click="openInfoFromMenu">Info</button>
          <button class="dropdown-item" @click="openLogoutFromMenu">Logout</button>
        </div>
      </div>

    </template>

    <template #main>
      <!-- Top stats (moved under header) -->
      <div class="hr-stats-grid header-stats" style="margin: 12px 0 0 0; display:flex; gap:12px;">
        <div class="hr-stat-card hr-stat-card--total" style="flex:1; display:flex; gap:10px; align-items:center; padding:10px; border-radius:8px;"><div class="hr-stat-icon">…</div><div class="hr-stat-content"><span class="hr-stat-label">Total Products</span><div style="font-weight:800; color:#333">{{ totalProducts }}</div></div></div>
        <div class="hr-stat-card hr-stat-card--active" style="flex:1; display:flex; gap:10px; align-items:center; padding:10px; border-radius:8px;"><div class="hr-stat-icon">…</div><div class="hr-stat-content"><span class="hr-stat-label">Low Stock</span><div style="font-weight:800; color:#333">{{ lowStockCount }}</div></div></div>
        <div class="hr-stat-card hr-stat-card--leave" style="flex:1; display:flex; gap:10px; align-items:center; padding:10px; border-radius:8px;"><div class="hr-stat-icon">…</div><div class="hr-stat-content"><span class="hr-stat-label">Out of Stock</span><div style="font-weight:800; color:#333">{{ outOfStockCount }}</div></div></div>
      </div>

      <!-- Product list (center column) -->
      <ProductList
        ref="productListRef"
        :fetchUrl="fetchUrl"
        :products="internalProducts"
        compact
        @open-add="openAddProduct"
        @edit="handleEdit"
        @delete="deleteProduct"
        @adjust="openAdjustModal"
        @count="openCountModal"
      >
        <template #profile></template>
        <template #attendance></template>
        <template #stats>
          <template v-if="staffProfile.role !== 'STAFF'">
            <div class="hr-stats-grid">
              <div class="hr-stat-card hr-stat-card--total">
                <div class="hr-stat-icon"> … </div>
                <div class="hr-stat-content">
                  <span class="hr-stat-label">Total products</span>
                  <span class="hr-stat-value">{{ totalProducts }}</span>
                </div>
              </div>
            </div>
          </template>
        </template>
      </ProductList>
    </template>

    <template #side>
      <div class="attendance-card" style="margin-top:12px; background: #ffffff;">
        <div class="attendance-header">
          <span class="attendance-title">Attendance</span>
          <span :class="['attendance-status-badge', attendanceStatus.is_clocked_in ? 'status-on-duty' : 'status-off-duty']">
            {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
          </span>
        </div>
        <!-- rest of attendance card markup preserved -->
        <div class="attendance-times" v-if="attendanceStatus.clock_in_time || attendanceStatus.clock_out_time">
          <div class="time-row"><span class="time-label">Clock In:</span><span class="time-value">{{ attendanceStatus.clock_in_time || '-' }}</span></div>
          <div class="time-row"><span class="time-label">Clock Out:</span><span class="time-value">{{ attendanceStatus.clock_out_time || '-' }}</span></div>
          <div class="time-row" v-if="attendanceStatus.hours_worked > 0"><span class="time-label">Hours:</span><span class="time-value">{{ attendanceStatus.hours_worked }} hrs</span></div>
        </div>
        <div class="attendance-buttons">
          <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing" class="btn-clock-in">{{ isAttendanceProcessing ? '...' : 'Clock In' }}</button>
          <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">{{ isAttendanceProcessing ? '...' : 'Clock Out' }}</button>
        </div>
        <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction"><span class="restriction-icon">🔒</span><span>Cannot clock out before {{ scheduledTimeOut }}</span></div>
        <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
      </div>



      <!-- Announcements removed per request -->
      <div class="pending-box" style="margin-top:12px;">
        <h3>Pending Stock Confirmations</h3>
        <div v-if="pendingProcurements.length">
          <table class="pending-table"><thead><tr><th>ID</th><th>Product</th><th>Quantity</th><th>Uploaded</th><th></th></tr></thead><tbody><tr v-for="pp in pendingProcurements" :key="pp.id"><td>{{ pp.id }}</td><td>{{ pp.product_name || 'Unknown' }}</td><td>{{ pp.quantity }}</td><td>{{ formatDate(pp.created_at) }}</td><td><button class="btn-primary" @click.prevent="openProcurementConfirm(pp)">Confirm</button></td></tr></tbody></table>
        </div>
        <div v-else class="empty-text">No pending confirmations</div>
      </div>
    </template>
  </OwnerPanelLayout>

      <!-- COUNT / ADJUST / ADD MODALS -->
      <transition name="fade">
        <div v-if="showCountModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Stock Count - {{ activeProduct ? activeProduct.name : '' }}</h3>
            <p class="info-sub">Enter counted quantity for this product.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Counted Qty</span>
                <input v-model.number="countValue" class="info-input" type="number" min="0" />
              </div>
            </div>
            <div v-if="formError" class="info-error">{{ formError }}</div>
            <div v-if="formSuccess" class="info-success">{{ formSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="showCountModal = false">Cancel</button>
              <button class="btn-primary" @click="submitCount">Save Count</button>
            </div>
          </div>
        </div>
      </transition>

      <transition name="fade">
        <div v-if="showAdjustModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Adjust Stock - {{ activeProduct ? activeProduct.name : '' }}</h3>
            <p class="info-sub">Add or subtract quantity. Use negative number to subtract.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Delta</span>
                <input v-model.number="adjust.delta" class="info-input" type="number" step="1" />
              </div>
              <div class="info-row"><span class="info-label">Note</span>
                <input v-model="adjust.note" class="info-input" type="text" placeholder="Reason (optional)" />
              </div>
            </div>
            <div v-if="formError" class="info-error">{{ formError }}</div>
            <div v-if="formSuccess" class="info-success">{{ formSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="showAdjustModal = false">Cancel</button>
              <button class="btn-primary" @click="submitAdjust">Apply Adjustment</button>
            </div>
          </div>
        </div>
      </transition>

      <transition name="fade">
        <div v-if="showAddModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Add Product</h3>
            <p class="info-sub">Create a new product for your branch.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Name</span>
                <input v-model="newProduct.name" class="info-input" type="text" />
              </div>
              <div class="info-row"><span class="info-label">SKU (optional)</span>
                <input v-model="newProduct.sku" class="info-input" type="text" placeholder="Leave empty to auto-generate" />
              </div>
              <div class="info-row"><span class="info-label">Generated SKU Preview</span>
                <div class="info-value" style="display:flex;align-items:center;gap:8px;">
                  <span>{{ displaySku }}</span>
                  <button class="btn-outline" @click.prevent="regeneratePreview">Regenerate</button>
                </div>
              </div>
              <div class="info-row"><span class="info-label">Price</span>
                <input v-model.number="newProduct.price" class="info-input" type="number" step="0.01" />
              </div>
              <div class="info-row"><span class="info-label">Initial Stock</span>
                <input v-model.number="newProduct.stock" class="info-input" type="number" />
              </div>
            </div>
            <div v-if="formError" class="info-error">{{ formError }}</div>
            <div v-if="formSuccess" class="info-success">{{ formSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="showAddModal = false">Cancel</button>
              <button class="btn-primary" @click="submitAddProduct">Create Product</button>
            </div>
          </div>
        </div>
      </transition>

      <!-- INFO MODAL -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Staff Information</h3>
            <p class="info-sub">Personal details for this staff can be updated from this panel.</p>
            <div class="info-grid">
              <div class="info-row"><span class="info-label">Full name</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.fullName }}</span>
                <input v-else v-model="staffProfile.fullName" class="info-input" type="text" />
              </div>
              <div class="info-row"><span class="info-label">Role</span><span class="info-value">{{ staffProfile.role }}</span></div>
              <div class="info-row"><span class="info-label">Username</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.username }}</span>
                <input v-else v-model="staffProfile.username" class="info-input" type="text" placeholder="Enter username" />
              </div>
              <div class="info-row"><span class="info-label">Email</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.email }}</span>
                <input v-else v-model="staffProfile.email" class="info-input" type="email" />
              </div>
              <div class="info-row"><span class="info-label">Contact</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.contact }}</span>
                <input v-else v-model="staffProfile.contact" class="info-input" type="text" />
              </div>
              <!-- Password fields - only shown when editing -->
              <template v-if="isEditingInfo">
                <div class="info-row info-row--password">
                  <span class="info-label">New Password</span>
                  <input v-model="staffProfile.password" class="info-input" type="password" placeholder="Leave blank to keep current" />
                </div>
                <div class="info-row info-row--password">
                  <span class="info-label">Confirm Password</span>
                  <input v-model="staffProfile.password_confirmation" class="info-input" type="password" placeholder="Re-enter new password" />
                </div>
              </template>
            </div>
            <div v-if="profileError" class="info-error">{{ profileError }}</div>
            <div v-if="profileSuccess" class="info-success">{{ profileSuccess }}</div>
            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? ' Cancel' : 'Close' }}</button>
              <button class="btn-primary" @click="isEditingInfo ? saveStaffInfo() : (isEditingInfo = true)" :disabled="isSavingProfile">
                {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- LOGOUT CONFIRM MODAL -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="info-backdrop">
          <div class="info-modal">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to logout?</p>
            <div class="info-actions">
              <button class="btn-outline" @click="showLogoutConfirm = false">Cancel</button>
              <button class="btn-primary" @click="logout" :disabled="isLoggingOut">{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</button>
            </div>
          </div>
        </div>
      </transition>
</template>

<script setup>
import { ref, onMounted, watch, computed, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import ProductList from './ProductList.vue'
import OwnerPanelLayout from '../OwnerPanelLayout.vue'

const router = useRouter();

// Back button logic
const showBackButton = computed(() => {
  return new URLSearchParams(window.location.search).get('from') === 'custom-panel'
})

function goBack() {
  // Check if there's a from parameter
  const params = new URLSearchParams(window.location.search)
  if (params.get('from') === 'custom-panel') {
    router.push({ path: '/custom-panel' })
  } else {
    router.back()
  }
}

const staffProfile = ref({
  avatarUrl: '',
  fullName: '',
  role: '',
  username: '',
  email: '',
  contact: '',
  accountId: '',
  password: '',
  password_confirmation: ''
});
const isProfileLoading = ref(true);
const isEditingInfo = ref(false);
const isSavingProfile = ref(false);
const showInfoModal = ref(false);
const profileError = ref('');
const profileSuccess = ref('');
const showLogoutConfirm = ref(false);
const isLoggingOut = ref(false);

// Header/profile dropdown state
const showProfileMenu = ref(false);
const headerProfileWrapper = ref(null);
const profileDropdown = ref(null);

// Attendance state variables
const attendanceStatus = ref({
  is_clocked_in: false,
  clock_in_time: null,
  clock_out_time: null,
  hours_worked: 0
});
const isAttendanceProcessing = ref(false);
const attendanceMessage = ref('');
const attendanceMessageType = ref('');

// Attendance settings state
const attendanceSettings = ref({
  early_clockout_override: false,
  scheduled_time_out: '17:00:00'
});

// Computed property for scheduled time out display
const scheduledTimeOut = computed(() => {
  const time = attendanceSettings.value.scheduled_time_out || '17:00:00'
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour % 12 || 12
  return `${hour12}:${minutes} ${ampm}`
})

// Computed property to check if clock out is allowed
const canClockOut = computed(() => {
  // If not clocked in, can't clock out
  if (!attendanceStatus.value.is_clocked_in) return false

  // If override is enabled, allow clock out
  if (attendanceSettings.value.early_clockout_override) return true

  // Get current time
  const now = new Date()
  const currentHours = now.getHours()
  const currentMinutes = now.getMinutes()

  // Get scheduled time out
  const [scheduledHours, scheduledMinutes] = (attendanceSettings.value.scheduled_time_out || '17:00:00').split(':')

  // Compare times
  const currentTotalMinutes = currentHours * 60 + currentMinutes
  const scheduledTotalMinutes = parseInt(scheduledHours) * 60 + parseInt(scheduledMinutes)

  // Allow clock out if current time >= scheduled time
  return currentTotalMinutes >= scheduledTotalMinutes
})

// optional products prop (we will rely on ProductList fetch by default)
const props = defineProps({
  products: { type: Array, default: () => [] },
  fetchUrl: { type: String, default: '/api/staff/inventory/products' },
  pageTitle: { type: String, default: 'Staff Inventory' },
  isSuperAdmin: { type: Boolean, default: false }
});

// Internal products from parent (used when not fetching via API)
const internalProducts = ref(props.products || [])

// Watch for products prop changes
watch(() => props.products, (newProducts) => {
  internalProducts.value = newProducts || []
})

// Computed title
const pageTitle = computed(() => props.pageTitle)

// Compute API endpoints based on whether it's superadmin or not
const endpoints = computed(() => {
  if (props.isSuperAdmin) {
    return {
      products: '/api/superadmin/logistics/products',
      store: '/api/superadmin/logistics/products',
      update: (id) => `/api/superadmin/logistics/products/${id}`,
      destroy: (id) => `/api/superadmin/logistics/products/${id}`
    }
  }
  return {
    products: '/api/staff/inventory/products',
    store: '/api/staff/inventory/products',
    update: (id) => `/api/staff/inventory/products/${id}`,
    destroy: (id) => `/api/staff/inventory/products/${id}`
  }
})

// Also update the fetchUrl to use computed endpoints
// Include unpublished products for inventory management
const fetchUrl = computed(() => `${endpoints.value.products}?include_unpublished=1`)

// ref to the ProductList child so we can trigger refreshes
const productListRef = ref(null)

// Modals / forms
const showCountModal = ref(false);
const showAdjustModal = ref(false);
const showAddModal = ref(false);
const activeProduct = ref(null);
const countValue = ref(0);
const activeProcurementId = ref(null);
const adjust = ref({ delta: 0, note: '' });
const newProduct = ref({ name: '', price: 0, stock: 0, sku: '' });
const previewSku = ref('');
// small helper to force ProductList to refresh after server changes
function refreshList() {
  if (productListRef.value && typeof productListRef.value.fetchProducts === 'function') {
    return productListRef.value.fetchProducts().then(() => updateStats()).catch(() => {})
  }
}

function makePreviewSku(name) {
  let base = (name || '').toUpperCase().replace(/[^A-Z0-9]+/g, '').substring(0, 6)
  if (!base) base = 'PRD'
  const random = Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0,4).toUpperCase() || (Math.random()*1e6|0).toString(36).substring(0,4).toUpperCase()
  return `${base}-${random}`
}

function regeneratePreview() {
  previewSku.value = makePreviewSku(newProduct.value.name || '')
}

const displaySku = computed(() => {
  return newProduct.value.sku && newProduct.value.sku.trim() !== '' ? newProduct.value.sku : (previewSku.value || makePreviewSku(newProduct.value.name || ''))
})
const formError = ref('');
const formSuccess = ref('');
const isLoading = ref(false)

// controls state for header-area filters
const searchQuery = ref('')
const selectedStockFilter = ref('all')

// stats shown in sidebar
const totalProducts = ref(0)
const lowStockCount = ref(0)
const outOfStockCount = ref(0)

// Announcements state for staff panels
const announcements = ref([])
const loadingAnnouncements = ref(false)

async function fetchAnnouncements() {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    if (res.data) {
      // API may return { announcements: [...] } or data array directly
      if (Array.isArray(res.data)) announcements.value = res.data
      else if (Array.isArray(res.data.announcements)) announcements.value = res.data.announcements
      else if (Array.isArray(res.data.data)) announcements.value = res.data.data
      else announcements.value = []
    }
  } catch (e) {
    console.error('Failed to load announcements:', e)
    announcements.value = []
  } finally {
    loadingAnnouncements.value = false
  }
}

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleString() } catch (e) { return d }
}

function onSearchInput() {
  if (productListRef.value && typeof productListRef.value.setQuery === 'function') {
    productListRef.value.setQuery(searchQuery.value)
  }
}

function onStockFilterChange() {
  if (productListRef.value && typeof productListRef.value.setStockFilter === 'function') {
    productListRef.value.setStockFilter(selectedStockFilter.value)
  }
}

async function updateStats() {
  if (productListRef.value && typeof productListRef.value.getStats === 'function') {
    const s = productListRef.value.getStats()
    totalProducts.value = s.total || 0
    lowStockCount.value = s.low || 0
    outOfStockCount.value = s.out || 0
  }
}

onMounted(async () => {
  isProfileLoading.value = true;
  try {
    const res = await axios.get('/api/me', { withCredentials: true });
    if (res.data && res.data.ok && res.data.user) {
      const u = res.data.user;
      staffProfile.value = {
        avatarUrl: u.avatar_url || '',
        fullName: u.full_name || u.fullName || '',
        role: u.role || '',
        username: u.username || '',
        email: u.email || '',
        contact: u.contact || '',
        accountId: u.account_id || '',
        branch_name: u.branch_name || (u.branch && (u.branch.name || u.branch.branch_name)) || '',
        password: '',
        password_confirmation: ''
      };
    }
  } catch (e) {
    profileError.value = 'Failed to load profile info.';
  } finally {
    isProfileLoading.value = false;
  }
  // Load attendance status and settings on mount
  loadAttendanceStatus()
  loadAttendanceSettings()
  // ProductList will handle fetching when given a fetchUrl; if a parent passed products prop, ProductList will display them.
  // initial stats update after mount
  setTimeout(() => updateStats(), 300)
  fetchAnnouncements()
  loadPendingProcurements()
  loadConfirmedProcurements()

  // click-away listener to close header profile dropdown
  function onDocClick(e) {
    try {
      if (!showProfileMenu.value) return
      const wrapper = headerProfileWrapper.value
      if (!wrapper) return
      if (!wrapper.contains(e.target)) {
        showProfileMenu.value = false
      }
    } catch (err) { /* ignore */ }
  }
  document.addEventListener('click', onDocClick)
  onUnmounted(() => document.removeEventListener('click', onDocClick))
});

function toggleProfileMenu() {
  showProfileMenu.value = !showProfileMenu.value
}

function openInfoFromMenu() {
  showProfileMenu.value = false
  openInfoModal()
}

function openLogoutFromMenu() {
  showProfileMenu.value = false
  showLogoutConfirm.value = true
}

async function loadPendingProcurements() {
  try {
    const res = await axios.get('/api/staff/inventory/pending-procurements', { withCredentials: true })
    pendingProcurements.value = res.data || []
  } catch (e) {
    pendingProcurements.value = []
  }
}

const confirmedProcurements = ref([])

async function loadConfirmedProcurements() {
  try {
    const res = await axios.get('/api/staff/inventory/confirmed-procurements', { withCredentials: true })
    confirmedProcurements.value = res.data || []
  } catch (e) {
    confirmedProcurements.value = []
  }
}

const pendingProcurements = ref([])

// Note: ProductList can fetch products itself via `fetchUrl`. Parent mutating actions will call `refreshList()` after success.

function formatCurrency(v) {
  if (v === null || v === undefined) return '-';
  return Number(v).toLocaleString(undefined, { style: 'currency', currency: 'PHP' });
}

function openCountModal(prod) {
  activeProduct.value = prod;
  countValue.value = prod.stock || 0;
  formError.value = '';
  formSuccess.value = '';
  activeProcurementId.value = null;
  showCountModal.value = true;
}

async function submitCount() {
  if (!activeProduct.value) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    if (activeProcurementId.value) {
      // Confirm stock for a procurement request (staff flow)
      const res = await axios.post(`/api/staff/inventory/procurements/${activeProcurementId.value}/confirm-stock`, { counted_stock: Number(countValue.value) }, { withCredentials: true })
      await refreshList()
      await loadPendingProcurements()
      await loadConfirmedProcurements()
      formSuccess.value = res.data?.message || 'Stock confirmed successfully.'
      showCountModal.value = false
      activeProcurementId.value = null
    } else {
      const payload = { stock: Number(countValue.value) };
      const res = await axios.put(endpoints.value.update(activeProduct.value.id), payload, { withCredentials: true });
      // refresh the list from server
      refreshList()
      formSuccess.value = 'Stock updated successfully.';
      showCountModal.value = false;
    }
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to update stock.';
  }
}

function openAdjustModal(prod) {
  activeProduct.value = prod;
  adjust.value = { delta: 0, note: '' };
  formError.value = '';
  formSuccess.value = '';
  showAdjustModal.value = true;
}

function handleEdit(prod) {
  // open the Add/Edit modal prefilled for editing
  newProduct.value = { id: prod.id, name: prod.name, price: prod.price, stock: prod.stock, sku: prod.sku }
  formError.value = '';
  formSuccess.value = '';
  showAddModal.value = true;
}

async function submitAdjust() {
  if (!activeProduct.value) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    const newStock = Number(activeProduct.value.stock) + Number(adjust.value.delta);
    const payload = { stock: newStock };
    const res = await axios.put(endpoints.value.update(activeProduct.value.id), payload, { withCredentials: true });
    refreshList()
    formSuccess.value = 'Stock adjusted successfully.';
    showAdjustModal.value = false;
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to adjust stock.';
  }
}

function openAddProduct() {
  newProduct.value = { name: '', price: 0, stock: 0, sku: '' };
  formError.value = '';
  formSuccess.value = '';
  showAddModal.value = true;
  // prepare preview SKU
  previewSku.value = makePreviewSku('')
}

function openProcurementConfirm(proc) {
  // Try to find product entry in current list
  const prod = (internalProducts.value || []).find(p => p.id === proc.product_id) || { id: proc.product_id, name: proc.product_name, stock: 0 };
  activeProduct.value = prod;
  // Suggest counted input as the delivered quantity (we will increment stock by this)
  countValue.value = Number(proc.quantity || 0);
  formError.value = '';
  formSuccess.value = '';
  activeProcurementId.value = proc.id;
  showCountModal.value = true;
}

async function submitAddProduct() {
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    // If user didn't provide SKU, send the preview so server and UI match
    const payload = { ...newProduct.value };
    if (!payload.sku || payload.sku.trim() === '') payload.sku = previewSku.value || makePreviewSku(payload.name || '')
    let res
    if (payload.id) {
      // update existing product
      res = await axios.put(endpoints.value.update(payload.id), payload, { withCredentials: true })
    } else {
      res = await axios.post(endpoints.value.store, payload, { withCredentials: true });
    }
    if (res.data && (res.data.product || res.data.ok)) {
      // refresh the list so ProductList reflects the change
      refreshList()
      formSuccess.value = payload.id ? 'Product updated.' : 'Product added.';
      showAddModal.value = false;
    }
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to create product.';
  }
}

async function deleteProduct(prod) {
  if (!(await window.swalConfirm('Delete product "' + prod.name + '"? This cannot be undone.'))) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { alert('Unable to refresh CSRF token. Please reload or login.'); return }
  try {
    await axios.delete(endpoints.value.destroy(prod.id), { withCredentials: true });
    refreshList()
  } catch (e) {
    alert((e.response && e.response.data && e.response.data.message) || 'Failed to delete product');
  }
}

// Ensure a fresh CSRF cookie/header is present before mutating requests
async function ensureCsrf() {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    const match = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'));
    const token = match ? decodeURIComponent(match[2]) : null;
    if (token) axios.defaults.headers.common['X-XSRF-TOKEN'] = token;
    return true;
  } catch (e) {
    return false;
  }
}

function openInfoModal() {
  showInfoModal.value = true;
  isEditingInfo.value = false;
  profileError.value = '';
  profileSuccess.value = '';
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false;
    profileError.value = '';
    profileSuccess.value = '';
  } else {
    showInfoModal.value = false;
  }
}

async function saveStaffInfo() {
  isSavingProfile.value = true;
  profileError.value = '';
  profileSuccess.value = '';
  // TODO: Replace with real API call
  setTimeout(() => {
    isSavingProfile.value = false;
    isEditingInfo.value = false;
    profileSuccess.value = 'Profile updated!';
  }, 1000);
}

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (!(await window.swalConfirm('Are you sure you want to change your profile picture?'))) return

  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    await new Promise(resolve => setTimeout(resolve, 100))

    function getCookie(name) {
      const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'))
      return m ? m[2] : null
    }

    const xsrf = getCookie('XSRF-TOKEN')
    const formData = new FormData()
    formData.append('avatar', file)

    if (xsrf) {
      try {
        formData.append('_token', decodeURIComponent(xsrf))
      } catch (_) {
        formData.append('_token', xsrf)
      }
    }

    const config = {
      headers: { 'Content-Type': 'multipart/form-data' },
      withCredentials: true
    }

    if (xsrf) {
      try {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrf)
      } catch (_) {
        config.headers['X-XSRF-TOKEN'] = xsrf
      }
    }

    const endpoint = '/api/staff/inventory/avatar'
    const res = await axios.post(endpoint, formData, config)

    if (res.data && res.data.ok) {
      staffProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
      alert('Profile picture updated successfully!')
    }
  } catch (e) {
    console.error('Avatar upload failed:', e)
    alert(e.response?.data?.message || 'Failed to upload profile picture. Please try again.')
  }
}

async function logout() {
  if (isLoggingOut.value) return;
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (!ok) return
    isLoggingOut.value = true;
    try { await axios.post('/api/logout', {}, { withCredentials: true }) } catch (e) {}
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    // Optional: show overlay (if you have one)
    showLogoutConfirm.value = false;
    setTimeout(() => {
      try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
      try { window.location.replace('/staff-landing') } catch (e) { router.push('/staff-landing').catch(() => {}) }
    }, 600);
  } catch (e) { console.error('logout failed', e) }
}

// Attendance functions
async function loadAttendanceStatus() {
  try {
    const res = await axios.get('/api/staff/attendance/status', { withCredentials: true })
    if (res.data && res.data.ok) {
      attendanceStatus.value = {
        is_clocked_in: res.data.status?.is_clocked_in || false,
        clock_in_time: res.data.status?.clock_in_time || null,
        clock_out_time: res.data.status?.clock_out_time || null,
        hours_worked: res.data.status?.hours_worked || 0
      }
    }
  } catch (e) {
    console.error('Failed to load attendance status:', e)
  }
}

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      attendanceSettings.value = {
        early_clockout_override: res.data.data.early_clockout_override || false,
        scheduled_time_out: res.data.data.scheduled_time_out || '17:00:00'
      }
    }
  } catch (e) {
    console.error('Failed to load attendance settings:', e)
    attendanceSettings.value = {
      early_clockout_override: false,
      scheduled_time_out: '17:00:00'
    }
  }
}

async function performClockIn() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''

  try {
    const res = await axios.post('/api/staff/clock-in', {}, { withCredentials: true })
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked in successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking in'
    attendanceMessageType.value = 'error'
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

async function performClockOut() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''

  try {
    const res = await axios.post('/api/staff/clock-out', {}, { withCredentials: true })
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked out successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking out'
    attendanceMessageType.value = 'error'
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}
</script>

<style scoped>
.inventory-table th, .inventory-table td {
  padding: 0.55rem 0.7rem;
  font-size: 0.92rem;
  vertical-align: middle;
}
.inventory-table thead th {
  font-size: 0.95rem;
}
.prod-thumb {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid rgba(255,211,107,0.6);
  background: #fff4e6;
  display: inline-block;
}
.prod-thumb--placeholder {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #4b2a06;
  font-weight: 700;
  background: rgba(255,232,163,0.9);
}
.sku-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(255,211,107,0.7);
  color: #4b2a06;
  font-weight: 700;
  font-size: 0.85rem;
}
.prod-name { font-size: 0.98rem; }
.staff-table td.actions { white-space: nowrap; }

.pl-page { padding: 16px; background: radial-gradient(circle at center, #FFFFFF 0%, #FCFCFC 40%, #EFEFEF 100%); min-height: 100vh; width: 100vw; }
.pl-container { width: 100%; max-width: 1200px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; border: 1px solid #F0E9E0; padding: 20px; box-shadow: 0 8px 24px rgba(16,24,40,0.06); box-sizing: border-box; display: grid; grid-template-columns: 180px 1fr 300px; gap: 20px; align-items: start }

/* root columns inside the container */
.pl-root { display: flex; gap: 20px; align-items: flex-start }
.pl-left-panel { width: 280px; flex: 0 0 280px }
.pl-right-column { flex: 1 1 auto; }

/* make the right column content stand out as a white card */
.pl-right-column .pl-header,
.pl-right-column .pl-main,
.pl-right-column .pl-table-wrap { background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 8px 28px rgba(0,0,0,0.06); }

.pl-header { display:flex; justify-content:space-between; align-items:center; gap:12px }
.pl-title { margin:0; font-size:1.05rem; color:#2c2c2c }
.pl-sub { margin:0; color:#6b6b6b; font-size:0.9rem }
.pl-actions { display:flex; gap:12px; align-items:center }
.pl-filters select, .pl-search input { border-radius:8px; border:1px solid rgba(0,0,0,0.06); padding:8px }
.pl-page-header { background: transparent; padding: 4px 0 }
.pl-container > .pl-page-header { grid-column: 1 / -1 }

/* Make ProductList span the first two columns (left + center) so there's no empty gutter */
.pl-container > ProductList { grid-column: 2 / 3 }
.pl-container > .pl-right-column { grid-column: 3 / 4 }
.pl-h1 { margin:0; font-size:1.4rem; color:#2c2c2c }
.pl-lead { margin:0; color:#6b6b6b }
.pl-controls { display:flex; justify-content:space-between; gap:12px; align-items:center }
.pl-controls-left { flex:1 }
.pl-controls-right { display:flex; gap:8px; align-items:center }
.pl-search { width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(0,0,0,0.06); background:#ffffff }
.pl-stats { display:flex; flex-direction:column; gap:12px }
.stat-card { background: #ffffff; padding:12px; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
.stat-title { color:#6b6b6b; font-size:0.85rem }
.stat-value { font-weight:800; font-size:1.25rem; color:#333333 }
.pl-main { min-width:0 }

/* compact ProductList overrides when embedded */
ProductList[compact] { width:100% }

/* Profile card styles (restored owner look) */
.profile-card { background: #ffffff; border-radius: 14px; padding: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); display:flex; flex-direction:column; gap:12px }
.profile-avatar { display:flex; justify-content:center }
.avatar-circle { width:72px; height:72px; border-radius:50%; background: linear-gradient(180deg,#ff9a4b,#ff7043); color:white; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.25rem; border:4px solid rgba(255,255,255,0.9) }
.profile-info { text-align:center }
.profile-role { font-size:0.75rem; color:#8a4b1a; font-weight:700 }
.profile-name { font-size:1.05rem; font-weight:800; color:#7a2b00 }
.profile-sub { font-size:0.8rem; color:#a65a2a }
.profile-box { background: #ffffff; padding:12px; border-radius:10px; display:flex; flex-direction:column; gap:8px; align-items:center }
.account-id { color:#333333; font-weight:700 }
.btn-info-small { padding:6px 12px; border-radius:999px; background: #f0f0f0; border:none; color:#333333 }
.qr-placeholder { width:84px; height:84px; border-radius:8px; border:2px dashed rgba(255,211,107,0.6); display:flex; align-items:center; justify-content:center; color:#7a2b00 }
.profile-actions { display:flex; flex-direction:column; gap:10px }
.small-stats { display:flex; justify-content:space-between; gap:12px }
.small-stat-title { font-size:0.75rem; color:#8a4b1a }
.small-stat-val { font-weight:800; color:#7a2b00 }

/* Avatar upload styles */
.avatar-upload { cursor: pointer; position: relative; display: inline-block; }
.avatar-img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,244,230,0.9); }
.avatar-overlay { position: absolute; top: 0; left: 0; width: 72px; height: 72px; border-radius: 50%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
.avatar-upload:hover .avatar-overlay { opacity: 1; }
.avatar-change-text { color: white; font-size: 0.6rem; font-weight: 500; text-transform: uppercase; text-align: center; }

/* Attendance Card Styles */
.attendance-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

/* Pending/History centered section */
.pending-center { display:flex; gap:20px; justify-content:center; align-items:flex-start; margin:12px 0; }
.pending-box, .history-box { background: rgba(255,255,255,0.9); padding:12px; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); width:48%; }
.pending-box h3, .history-box h3 { margin:0 0 8px; color:#7a2b00 }
.pending-table, .history-table { width:100%; border-collapse:collapse }
.pending-table th, .pending-table td, .history-table th, .history-table td { padding:8px; border-bottom:1px solid rgba(0,0,0,0.06); text-align:left }
.pending-table thead th, .history-table thead th { font-weight:700; font-size:0.9rem }
.pending-table tbody tr:last-child td, .history-table tbody tr:last-child td { border-bottom:none }
.history-box { max-height:360px; overflow:auto }

/* Pending confirmations box styling */
.pending-box {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-sizing: border-box;
  width: 100%;
  max-width: 360px;
  min-height: 120px;
  margin: 0 auto;
}
.pending-box h3 { margin: 0 0 10px; color: #7a2b00; font-size: 1.05rem; font-weight:700 }
.pending-box .empty-text { color: #6b6b6b; font-size:1rem; padding: 0 }
.pending-table { width:100%; border-collapse:collapse; margin-top:8px }
.pending-table th { font-size:0.85rem; color:#6b6b6b; padding:8px 6px; text-align:left }
.pending-table td { font-size:0.95rem; padding:8px 6px; border-bottom:1px solid rgba(0,0,0,0.04) }
.pending-table button.btn-primary { padding:6px 10px; font-size:0.88rem }

/* Inventory Summary Cards - below Attendance card */
.inventory-summary {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 10px;
}

@media (max-width: 600px) {
  .inventory-summary {
    grid-template-columns: 1fr;
  }
}

.attendance-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.attendance-title {
  font-weight: 700;
  color: #333333;
  font-size: 0.9rem;
}

.attendance-status-badge {
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
}

.status-on-duty {
  background: #d4edda;
  color: #155724;
}

.status-off-duty {
  background: #f8d7da;
  color: #721c24;
}

.attendance-times {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
}

.time-row {
  display: flex;
  justify-content: space-between;
}

.time-label {
  color: #8a4b1a;
}

.time-value {
  font-weight: 600;
  color: #7a2b00;
}

.attendance-buttons {
  display: flex;
  gap: 8px;
}

.btn-clock-in,
.btn-clock-out {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-clock-in {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.btn-clock-in:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-clock-in:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-clock-out {
  background: linear-gradient(135deg, #dc3545, #ff6b6b);
  color: white;
}

.btn-clock-out:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.btn-clock-out:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-disabled {
  background: #999 !important;
  cursor: not-allowed !important;
}

.clockout-restriction {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 6px;
  color: #856404;
  font-size: 0.7rem;
}

.restriction-icon {
  font-size: 1rem;
}

.attendance-message {
  padding: 8px;
  border-radius: 4px;
  text-align: center;
  font-size: 0.75rem;
  font-weight: 500;
}

.attendance-message.success {
  background: #d4edda;
  color: #155724;
}

.attendance-message.error {
  background: #f8d7da;
  color: #721c24;
}

/* Modal styles */
.info-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.info-modal {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.info-modal h3 {
  margin: 0 0 8px;
  color: #333333;
}

.info-sub {
  margin: 0 0 16px;
  color: #6b6b6b;
  font-size: 0.9rem;
}

.info-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-label {
  font-weight: 600;
  color: #7a2b00;
}

.info-value {
  color: #8a4b1a;
}

.info-input {
  padding: 8px 12px;
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 6px;
  width: 200px;
}

.info-error {
  color: #dc3545;
  background: #f8d7da;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.info-success {
  color: #155724;
  background: #d4edda;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.info-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn-outline {
  padding: 8px 16px;
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 6px;
  background: transparent;
  color: #333333;
  cursor: pointer;
}

.btn-primary {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  background: linear-gradient(180deg,#ff8a4b,#ff7043);
  color: white;
  cursor: pointer;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Header profile dropdown */
.header-profile-wrapper { position: relative; display: inline-block }
.profile-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(2,6,23,0.12);
  padding: 6px;
  z-index: 1200;
  min-width: 140px;
}
.profile-dropdown .dropdown-item {
  display: block;
  width: 100%;
  padding: 8px 12px;
  text-align: left;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 6px;
}
.profile-dropdown .dropdown-item:hover { background: #f5f5f5 }

/* header profile button (match Manager panels) - ensure styles apply inside OwnerPanelLayout slot */
:deep(.header-actions-top .header-profile-btn) { border: 1px solid rgba(0,0,0,0.08); background: #fff; padding: 6px 10px; border-radius: 8px; display:flex; gap:8px; align-items:center }
:deep(.header-actions-top .header-avatar) { width:36px; height:36px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f3f4f6; margin-right:8px }
:deep(.header-actions-top .header-avatar-img) { width:100%; height:100%; background-size:cover; background-position:center }
:deep(.header-actions-top .header-avatar-initials) { font-weight:700; color:#374151 }
:deep(.header-actions-top .header-name) { font-size: 0.8rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 320px }
</style>
