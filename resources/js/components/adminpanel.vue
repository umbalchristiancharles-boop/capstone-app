<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <div class="admin-topbar">
          <div class="admin-topbar-title">
            <div class="admin-topbar-heading">{{ panelTitle }}</div>
            <p class="admin-topbar-sub">{{ panelDescription }}</p>
            <p v-if="isLoadingDashboard && !isInitialMount" class="admin-topbar-hint">
              Loading dashboard…
            </p>
            <p v-else-if="dashboardError" class="admin-topbar-hint admin-topbar-hint--error">
              {{ dashboardError }}
            </p>
          </div>
          <div class="admin-topbar-actions">
            <div class="header-profile-wrapper" @click.stop>
              <button class="header-profile-btn" @click="toggleProfileDropdown">
                <div class="header-avatar">
                  <div v-if="ownerProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+ownerProfile.avatarUrl+')' }"></div>
                  <div v-else class="header-avatar-initials">{{ (ownerProfile.fullName || 'L').charAt(0).toUpperCase() }}</div>
                </div>
                <div class="header-name">{{ ((ownerProfile.role || 'ADMIN') + (typeof ownerProfile.branch === 'object' && ownerProfile.branch?.name ? ' - ' + ownerProfile.branch.name : (ownerProfile.branch ? ' - ' + ownerProfile.branch : ''))) }}</div>
              </button>
              <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
                <button class="dropdown-item" @click="openInfoModal">Info</button>
                <button
                  v-if="(ownerProfile.role || '').toString().toUpperCase() !== 'STAFF'"
                  class="dropdown-item"
                  @click="goToStaffManagement"
                >
                  Staff Management
                </button>
                <button class="dropdown-item" @click="askLogout">Logout</button>
              </div>
            </div>
          </div>
        </div>
        <!-- LEFT: Announcements column -->
        <aside class="admin-left">
          <section class="panel-block announcements-panel">
            <div class="panel-header"><h2>Announcements</h2></div>
            <div class="panel-body panel-body--list">
              <div v-if="loadingAnnouncements">Loading...</div>
              <div v-else-if="announcements.length === 0">No announcements</div>
              <ul v-else class="announcement-list">
                <li v-for="a in announcements" :key="a.id" class="announcement-item">
                  <div class="announcement-title">{{ a.title }}</div>
                  <div class="announcement-meta">{{ new Date(a.created_at).toLocaleString() }} • {{ a.target }}</div>
                  <div class="announcement-message">{{ a.message }}</div>
                </li>
              </ul>
            </div>
          </section>
        </aside>

        <!-- MIDDLE: MAIN DASHBOARD -->
        <main class="admin-main">

          <!-- Date range tabs (moved out of header) -->
          <div class="range-tabs">
            <button
              class="range-tab"
              :class="{ 'range-tab--active': activeRange === 'today' }"
              @click="changeRange('today')"
            >
              Today
            </button>
            <button
              class="range-tab"
              :class="{ 'range-tab--active': activeRange === 'yesterday' }"
              @click="changeRange('yesterday')"
            >
              Yesterday
            </button>
            <button
              class="range-tab"
              :class="{ 'range-tab--active': activeRange === 'thisWeek' }"
              @click="changeRange('thisWeek')"
            >
              This Week
            </button>
            <button
              class="range-tab"
              :class="{ 'range-tab--active': activeRange === 'lastWeek' }"
              @click="changeRange('lastWeek')"
            >
              Last Week
            </button>
            <button
              class="range-tab"
              :class="{ 'range-tab--active': activeRange === 'thisMonth' }"
              @click="changeRange('thisMonth')"
            >
              This Month
            </button>
            <button
              class="range-tab"
              :class="{ 'range-tab--active': activeRange === 'lastMonth' }"
              @click="changeRange('lastMonth')"
            >
              Last Month
            </button>
          </div>

          <!-- Overview cards - Operational Metrics -->
          <section class="overview-grid">
            <div class="overview-card">
              <span class="overview-label">
                Orders:
              </span>
              <span class="overview-value">
                &nbsp;{{ dashboardTotals.orders }}
              </span>
            </div>
            <div class="overview-card">
              <span class="overview-label">Completed Orders: </span>
              <span class="overview-value">
                &nbsp;{{ dashboardTotals.completed }}
              </span>
            </div>
            <div class="overview-card">
              <span class="overview-label">Sales:</span>
              <span class="overview-value">
                &nbsp;{{ dashboardTotals.sales }}
              </span>
            </div>
            <div class="overview-card">
              <span class="overview-label">Pending Orders:</span>
              <span class="overview-value">
                &nbsp;{{ dashboardTotals.pending }}
              </span>
            </div>
          </section>

          <!-- Request New Product (Admin) -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Request New Product</h2>
              <button class="panel-action" @click="showProductRequestForm = true">+ Request New Product</button>
            </div>
            <div class="panel-body panel-body--list">
              <p>Request new products to be added to inventory. Requests will require owner/main branch logistics approval.</p>
            </div>
          </section>

          <!-- Financial Metrics (moved to Attendance column) -->

          <!-- Orders table -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Orders</h2>
              <button
                class="panel-action"
                @click="showAllOrders = !showAllOrders"
              >
                {{ showAllOrders ? 'Show less' : 'View all' }}
              </button>
            </div>

            <div class="panel-body panel-body--table">
              <div class="table-header">
                <span>Order #</span>
                <span>Customer</span>
                <span>Status</span>
                <span>Total</span>
              </div>

              <div
                v-if="recentOrders.length === 0"
                class="table-row"
              >
                <span>No recent orders for this range.</span>
                <span></span>
                <span></span>
                <span></span>
              </div>

              <div
                v-else
                v-for="order in visibleOrders"
                :key="order.id"
                class="table-row"
              >
                <span>{{ order.code }}</span>
                <span>{{ order.customer }}</span>
                <span>
                  <span
                    class="badge"
                    :class="{
                      'badge--success': order.status === 'completed',
                      'badge--warning': order.status === 'in_kitchen',
                      'badge--info': order.status === 'pending'
                    }"
                  >
                    {{ order.statusLabel }}
                  </span>
                </span>
                <span>{{ order.total }}</span>
              </div>
            </div>
          </section>

          <!-- Production queue -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Production Queue</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="productionQueue.length === 0"
                class="queue-item"
              >
                <div class="queue-title">No items in production.</div>
              </div>
              <div
                v-else
                v-for="item in productionQueue"
                :key="item.id"
                class="queue-item"
              >
                <div>
                  <div class="queue-title">{{ item.title }}</div>
                  <div class="queue-meta">{{ item.meta }}</div>
                </div>
                <span class="badge" :class="item.badgeClass">
                  {{ item.badgeLabel }}
                </span>
              </div>
            </div>
          </section>






        </main>
        <!-- RIGHT: SIDE PANELS -->
        <aside class="admin-side">
          <!-- Top products -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Top Products</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="topProducts.length === 0"
                class="side-item"
              >
                <span>No data for this range.</span>
              </div>
              <div
                v-else
                v-for="prod in topProducts"
                :key="prod.id"
                class="side-item"
              >
                <span>{{ prod.name }}</span>
                <span class="side-value">{{ prod.orders }} orders</span>
              </div>
            </div>
          </section>

          <!-- Low stock -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Low Stock Items</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="lowStockItems.length === 0"
                class="side-item side-item--alert"
              >
                <span>All items above minimum stock.</span>
              </div>
              <div
                v-else
                v-for="item in lowStockItems"
                :key="item.id"
                class="side-item side-item--alert"
              >
                <span>{{ item.name }}</span>
                <span class="side-value">{{ item.stock }}</span>
              </div>
            </div>
          </section>

          <!-- Staff activity -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Staff Activity</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="adminStaffActivity.length === 0"
                class="side-item"
              >
                <span>No recent staff activity.</span>
              </div>
              <div
                v-else
                v-for="act in adminStaffActivity"
                :key="act.name"
                class="side-item"
              >
                <div>
                  <div class="activity-title">{{ act.name }}</div>
                  <div class="activity-meta">{{ act.role }} - {{ act.branch }}</div>
                </div>
                <span class="activity-time">{{ act.last_active }}</span>
              </div>
            </div>
          </section>



            <!-- Attendance Monitoring (Admin) -->
            <section class="panel-block">
              <div class="panel-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <h2>Attendance Monitoring</h2>
                <div style="display:flex; gap:8px; align-items:center;">
                  <select v-model="selectedBranchId" @change="loadAdminAttendance(activeRange)" style="padding:6px; border-radius:6px;">
                    <option :value="null">All branches</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                  </select>
                  <button class="panel-action" @click="loadAdminAttendance(activeRange)">Refresh</button>
                </div>
              </div>

            <!-- Early Clock-Out Override Toggle for Owner/HR only -->
            <div class="attendance-override-toggle" v-if="ownerProfile.role === 'OWNER' || ownerProfile.role === 'HR'">
              <div class="toggle-label">
                <span class="toggle-title">Enable Early Clock-Out</span>
                <span class="toggle-desc">Allow staff to clock out before scheduled time</span>
              </div>
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="earlyClockoutOverride"
                  @change="toggleEarlyClockout"
                  :disabled="isTogglingOverride"
                >
                <span class="toggle-slider"></span>
              </label>
            </div>

              <div class="panel-body panel-body--table">
                <div class="table-header">
                  <span>Staff Name</span>
                  <span>Branch</span>
                  <span>Time In</span>
                  <span>Time Out</span>
                  <span>Hours</span>
                  <span>Status</span>
                </div>

                <div v-if="adminAttendance.length === 0" class="table-row">
                  <span>No attendance records for this range.</span>
                  <span></span><span></span><span></span><span></span><span></span>
                </div>

                <div v-else v-for="att in adminAttendance" :key="att.id" class="table-row">
                  <span>{{ att.user_name }}</span>
                  <span>{{ att.branch_name || '-' }}</span>
                  <span>{{ att.time_in || '-' }}</span>
                  <span>{{ att.time_out || '-' }}</span>
                  <span>{{ att.hours_worked || '-' }}</span>
                  <span>
                    <span class="badge" :class="{
                      'badge--success': att.status === 'present',
                      'badge--warning': att.status === 'late',
                      'badge--info': att.status === 'absent'
                    }">{{ att.status || '-' }}</span>
                  </span>
                </div>
              </div>
            </section>



        </aside>
      </section>

      <!-- ANNOUNCEMENT MODAL (Owner) -->
      <transition name="fade">
        <div v-if="showAnnouncement" class="info-backdrop" @click.self="closeAnnouncementModal">
          <div class="info-modal announcement-modal">
            <div class="modal-header-custom">
              <h3>📢 Send Announcement</h3>
              <button class="modal-close-btn" @click="closeAnnouncementModal">✕</button>
            </div>

            <div class="modal-body-custom">
              <!-- Title Field -->
              <div class="form-group-custom">
                <label class="info-label">Title</label>
                <input
                  v-model="announcementTitle"
                  class="info-input"
                  type="text"
                  placeholder="Enter announcement title"
                  @keyup.enter="sendAnnouncement"
                />
              </div>

              <!-- Message Field -->
              <div class="form-group-custom">
                <label class="info-label">Message</label>
                <textarea
                  v-model="announcementText"
                  class="info-input"
                  rows="5"
                  placeholder="Write your announcement message..."
                ></textarea>
              </div>

              <!-- Target Selection -->
              <div class="form-group-custom">
                <label class="info-label">Send To</label>
                <select v-model="announcementTarget" class="info-input">
                  <option value="all">👥 All Branches (Everyone)</option>
                  <option value="staff">👨‍🍳 All Staff</option>
                  <option value="managers">👔 Managers Only</option>
                </select>
              </div>

              <!-- Error/Success Messages -->
              <div v-if="announcementError" class="alert-message alert-error">
                ⚠️ {{ announcementError }}
              </div>
              <div v-if="announcementSuccess" class="alert-message alert-success">
                ✅ {{ announcementSuccess }}
              </div>
            </div>

            <div class="modal-footer-custom">
              <button
                class="btn-outline"
                @click="closeAnnouncementModal"
                :disabled="isSendingAnnouncement"
              >
                Cancel
              </button>
              <button
                class="btn-primary"
                @click="sendAnnouncement"
                :disabled="isSendingAnnouncement"
              >
                {{ isSendingAnnouncement ? 'Sending...' : 'Send Announcement' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- INFO MODAL -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Owner Information</h3>
            <p class="info-sub">
              Personal details for this administrator can be updated from this
              panel.
            </p>

            <div class="info-grid">
              <div class="info-row">
                <span class="info-label">Full name</span>
                <span class="info-value" v-if="!isEditingInfo">
                  {{ ownerProfile.fullName }}
                </span>
                <input
                  v-else
                  v-model="ownerProfile.fullName"
                  class="info-input"
                  type="text"
                />
              </div>

              <div class="info-row">
                <span class="info-label">Role</span>
                <span class="info-value">{{ ownerProfile.role }}</span>
              </div>

              <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value" v-if="!isEditingInfo">
                  {{ ownerProfile.email }}
                </span>
                <input
                  v-else
                  v-model="ownerProfile.email"
                  class="info-input"
                  type="email"
                />
              </div>

              <div class="info-row">
                <span class="info-label">Contact</span>
                <span class="info-value" v-if="!isEditingInfo">
                  {{ ownerProfile.contact }}
                </span>
                <input
                  v-else
                  v-model="ownerProfile.contact"
                  class="info-input"
                  type="text"
                />
              </div>

              <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value">
                  {{ typeof ownerProfile.branch === 'object' && ownerProfile.branch.name ? ownerProfile.branch.name : (ownerProfile.branch || 'Not assigned') }}
                </span>
              </div>
            </div>

            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">
                {{ isEditingInfo ? 'Cancel' : 'Close' }}
              </button>
              <button
                class="btn-primary"
                @click="isEditingInfo ? saveOwnerInfo() : (isEditingInfo = true)"
              >
                {{ isEditingInfo ? 'Save changes' : 'Edit information' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Product Request Modal -->
      <transition name="fade">
        <div v-if="showProductRequestForm" class="modal-backdrop" @click.self="cancelProductRequest">
          <div class="modal">
            <h3>Request New Product</h3>
            <p class="modal-sub">Request new products to be added to inventory. Requires owner/main approval.</p>
            <form @submit.prevent="submitProductRequest">
              <div class="form-group">
                <label>Product Name*</label>
                <input v-model="productRequestForm.name" type="text" placeholder="e.g., Organic Chicken Breast" required />
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea v-model="productRequestForm.description" rows="3" placeholder="Optional details"></textarea>
              </div>
              <div class="form-group">
                <label>Unit of Measurement</label>
                <select v-model="productRequestForm.unit">
                  <option value="">-- Select unit (optional) --</option>
                  <option value="pcs">Pieces (pcs)</option>
                  <option value="g">Grams (g)</option>
                  <option value="kg">Kilograms (kg)</option>
                  <option value="ml">Milliliters (ml)</option>
                  <option value="l">Liters (l)</option>
                  <option value="pack">Pack</option>
                  <option value="box">Box</option>
                </select>
              </div>
              <div class="form-actions" style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="btn-secondary" @click="cancelProductRequest">Cancel</button>
                <button type="submit" class="btn-primary">Submit Request</button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- LOGOUT CONFIRM MODAL -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
          <div class="logout-confirm-box">
            <h3>Logout from Admin Panel?</h3>
            <p>This will end your current session for Chikin Tayo Admin.</p>
            <div class="logout-actions">
              <button
                class="btn-cancel"
                @click="cancelLogout"
                :disabled="isLoggingOut"
              >
                Cancel
              </button>
              <button
                class="btn-confirm"
                @click="confirmLogout"
                :disabled="isLoggingOut"
              >
                Yes, logout
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- FULLSCREEN LOADING OVERLAY -->
      <LoadingOverlay :show="showOverlay" :text="overlayText" :logo-src="logoImg" />
    </div>
  </div>
</template>

<script setup>
import { createApp, h, ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import LoadingOverlay from './LoadingOverlay.vue'

import { showToast } from './toastStore'

const router = useRouter()
const activeRange = ref('today')

const dashboardTotals = ref({
  orders: 0,
  completed: 0,
  sales: '₱0',
  pending: 0,
})



// Initialize summaryTotals with default value (will be updated after profile loads)
const summaryTotals = ref({ totalBranches: 0, totalEmployees: 0 })

const productionQueue = ref([])
const topProducts = ref([])
const lowStockItems = ref([])
const staffActivity = ref([])
const adminStaffActivity = ref([])
const announcements = ref([])
const loadingAnnouncements = ref(false)
const branches = ref([])
const selectedBranchId = ref(null)
const adminAttendance = ref([])
const recentOrders = ref([])
const showAllOrders = ref(false)

const visibleOrders = computed(() => {
  if (!recentOrders.value || recentOrders.value.length === 0) return []
  return showAllOrders.value ? recentOrders.value : recentOrders.value.slice(0, 3)
})

const isLoadingDashboard = ref(false)
const dashboardError = ref('')
const isInitialMount = ref(true)  // Hide loading message on initial mount
const isProfileLoading = ref(true)  // Hide profile until fetched

const showInfoModal = ref(false)
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
// Product request modal state (Admin)
const showProductRequestForm = ref(false)
const productRequestForm = ref({ name: '', description: '', unit: '' })
const productRequestSubmitting = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

const ownerProfile = ref({
  fullName: '',
  role: 'Owner',
  email: '',
  contact: '',
  branch: '',
  accountId: '',
  avatarUrl: '',
})



const isEditingInfo = ref(false)

// Header profile dropdown state (moved from side column)
const profileDropdownVisible = ref(false)

function toggleProfileDropdown() {
  profileDropdownVisible.value = !profileDropdownVisible.value
}

function onDocumentClick() {
  try { if (profileDropdownVisible.value) profileDropdownVisible.value = false } catch (e) {}
}

// Early clock-out override toggle
const earlyClockoutOverride = ref(false)
const isTogglingOverride = ref(false)

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      earlyClockoutOverride.value = res.data.data.early_clockout_override || false
    }
  } catch (e) {
    console.error('Failed to load attendance settings:', e)
  }
}

async function toggleEarlyClockout() {
  isTogglingOverride.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.patch('/api/attendance/override', {
      early_clockout_override: earlyClockoutOverride.value
    }, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert(res.data.message || 'Settings updated successfully')
    } else {
      earlyClockoutOverride.value = !earlyClockoutOverride.value
      alert(res.data.message || 'Failed to update settings')
    }
  } catch (e) {
    earlyClockoutOverride.value = !earlyClockoutOverride.value
    alert(e.response?.data?.message || 'Error updating settings')
  } finally {
    isTogglingOverride.value = false
  }
}

const brandTitle = computed(() => 'Chikin Tayo')
const panelText = computed(() => {
  const role = ownerProfile.value.role || 'OWNER'
  if (role === 'BRANCH_MANAGER') return 'Branch Manager Panel'
  return 'Admin Panel'
})
const panelTitle = computed(() => `${brandTitle.value} ${panelText.value}`)

const panelDescription = computed(() => {
  const role = ownerProfile.value.role || 'OWNER'
  if (role === 'BRANCH_MANAGER') return 'Monitor your branch orders, staff, and activity.'
  return 'Monitor branches, orders, and staff activity from a single dashboard.'
})

function normalizeUser(u) {
  if (!u) return { fullName: '', role: '', email: '', contact: '', branch: '', accountId: '', avatarUrl: '' }
  return {
    fullName: u.fullName ?? u.full_name ?? '',
    role: u.role ?? '',
    email: u.email ?? '',
    contact: u.contact ?? u.phone_number ?? '',
    branch: u.branch ?? (u.branch_name ?? '') ,
    accountId: u.accountId ?? (u.account_id ?? ''),
    avatarUrl: u.avatarUrl ?? (u.avatar_url ?? ''),
  }
}

async function loadBranches() {
  try {
    const res = await axios.get('/api/admin/branches', { withCredentials: true })
    if (res.data) {
      branches.value = res.data || []
    }
  } catch (e) {
    console.error('Error loading branches:', e)
    branches.value = []
  }
}

// Fetch announcements visible to the current user
async function fetchAnnouncements() {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    if (res.data && res.data.ok) announcements.value = res.data.announcements || []
  } catch (e) {
    // ignore non-critical errors
  } finally {
    loadingAnnouncements.value = false
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

async function submitProductRequest() {
  productRequestSubmitting.value = true
  try {
    await ensureCsrf()
    const payload = { name: productRequestForm.value.name, description: productRequestForm.value.description || null, unit: productRequestForm.value.unit || null }
    const res = await axios.post('/api/product-requests', payload, { withCredentials: true })
    showToast('Product request submitted for approval', 'success')
    showProductRequestForm.value = false
    productRequestForm.value = { name: '', description: '', unit: '' }
  } catch (e) {
    const msg = e.response?.data?.error || e.response?.data?.message || e.message || 'Failed to submit product request'
    showToast(msg, 'error')
  } finally {
    productRequestSubmitting.value = false
  }
}

function cancelProductRequest() {
  showProductRequestForm.value = false
  productRequestForm.value = { name: '', description: '', unit: '' }
}

async function loadAdminAttendance(range = 'today') {
  try {
    const params = { range }
    if (selectedBranchId.value) params.branch_id = selectedBranchId.value
    const res = await axios.get('/api/admin/attendance', { params, withCredentials: true })
    if (res.data && res.data.ok) {
      adminAttendance.value = res.data.data || []
    } else {
      adminAttendance.value = []
    }
  } catch (e) {
    console.error('Error loading admin attendance:', e)
    adminAttendance.value = []
  }
}

async function loadDashboard(range) {
  isLoadingDashboard.value = true
  dashboardError.value = ''

  // Clear all data while loading
  dashboardTotals.value = {
    orders: 0,
    completed: 0,
    sales: '₱0',
    pending: 0,
  }
  if (ownerProfile.value.role !== 'STAFF') {
    summaryTotals.value = {
      totalBranches: 0,
      totalEmployees: 0,
    }
  }
  recentOrders.value = []
  productionQueue.value = []
  topProducts.value = []
  lowStockItems.value = []
  staffActivity.value = []
  adminStaffActivity.value = []

  try {
    // Determine which endpoint to use based on user role
    const userRole = ownerProfile.value.role
    let endpoint = '/api/admin/dashboard'

    if (userRole === 'BRANCH_MANAGER') {
      endpoint = '/api/manager/dashboard'
    }

    // Fetch dashboard data with date range parameter
    const res = await axios.get(endpoint, {
      params: { range },
      withCredentials: true,
    })

    if (res.data) {
      if (userRole === 'BRANCH_MANAGER' && res.data.success) {
        // Manager dashboard response structure
        summaryTotals.value = {
          totalBranches: 1,
          totalEmployees: res.data.summary?.totalEmployees || 0,
        }

        dashboardTotals.value = {
          orders: res.data.stats?.orders || 0,
          completed: res.data.stats?.completed || 0,
          sales: res.data.stats?.sales || '₱0',
          pending: res.data.stats?.pending || 0,
        }

        recentOrders.value = res.data.recentOrders || []
        productionQueue.value = res.data.productionQueue || []
        staffActivity.value = res.data.staffActivity || []
      } else {
        // Admin dashboard response structure - map all new fields from enhanced API
        if (userRole !== 'STAFF') {
          summaryTotals.value = {
            totalBranches: res.data.branches_count || 0,
            totalEmployees: res.data.staff_count || 0,
          }
        }

        // Map all dashboard totals from the enhanced API
        dashboardTotals.value = {
          orders: res.data.orders ?? res.data.orders_count ?? 0,
          completed: res.data.completed ?? 0,
          sales: res.data.sales ?? '₱0',
          pending: res.data.pending ?? 0,
        }

        // Map recent orders
        recentOrders.value = res.data.recent_orders || []

        // Map production queue
        productionQueue.value = res.data.production_queue || []

        // Map top products
        topProducts.value = res.data.top_products || []

        // Map low stock items
        lowStockItems.value = res.data.low_stock_items || []

        // Map staff activity
        adminStaffActivity.value = res.data.recent_activity || res.data.recentActivity || []

        // Update branches if provided
        if (res.data.branches && res.data.branches.length > 0) {
          branches.value = res.data.branches
        }
      }
    }
  } catch (e) {
    // If 401, user session expired - redirect to login
    if (e.response?.status === 401) {
      router.push('/staff-landing')
      return
    }
    dashboardError.value = 'Error loading dashboard.'
    console.error('Dashboard error:', e)
  } finally {
    isLoadingDashboard.value = false
    // Remove any temporary global overlay created by previous route
    clearTemporaryOverlay()

    // Hide global page blur (if using the pageBlur helper)
    try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}

    // Also ensure local overlay flag is cleared
    try { showOverlay.value = false } catch (e) {}
  }
}

async function changeRange(range) {
  if (activeRange.value === range) return
  activeRange.value = range
  try {
    await loadDashboard(range)
  } catch (err) {
    console.error('Error changing range:', err)
    await loadDashboard(range)
  }
  // reload admin attendance for new range
  try { await loadAdminAttendance(range) } catch (e) {}
}



async function openInfoModal() {
  showInfoModal.value = true
  isEditingInfo.value = false

  try {
    const res = await axios.get('/api/owner-profile', {
      withCredentials: true,
    })
    if (res.data.ok) {
      ownerProfile.value = normalizeUser(res.data.user)
    }
  } catch (e) {}
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false
  } else {
    showInfoModal.value = false
  }
}

async function saveOwnerInfo() {
  try {
    const payload = {
      fullName: ownerProfile.value.fullName,
      email: ownerProfile.value.email,
      contact: ownerProfile.value.contact,
    }

    const res = await axios.put('/api/owner-profile', payload, {
      withCredentials: true,
    })

    if (res.data.ok) {
      isEditingInfo.value = false
    }
  } catch (e) {}
}

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  // Confirm before changing profile picture
  if (!(await window.swalConfirm('Are you sure you want to change your profile picture?'))) return

  try {
    // Get CSRF cookie first
    try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
    await new Promise(resolve => setTimeout(resolve, 100))

    // Get CSRF token from cookie
    function getCookie(name) {
      const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'))
      return m ? m[2] : null
    }
    const xsrf = getCookie('XSRF-TOKEN')

    // Prepare form data
    const formData = new FormData()
    formData.append('avatar', file)
    if (xsrf) {
      try {
        formData.append('_token', decodeURIComponent(xsrf))
      } catch (_) {
        formData.append('_token', xsrf)
      }
    }

    // Set CSRF token in header
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

    // Upload
    const res = await axios.post('/api/upload-avatar', formData, config)

    if (res.data && res.data.ok) {
      // Update profile with new avatar URL
      ownerProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
      alert('Profile picture updated successfully!')
    }
  } catch (e) {
    console.error('Avatar upload failed:', e)
    alert('Failed to upload profile picture. Please try again.')
  }
}

// Auto-upload pending avatar after reload (admin panel)
onMounted(async () => {
  // Mark initial mount complete first (before loading dashboard)
  isInitialMount.value = false

  // Reset profile to avoid showing stale data
  ownerProfile.value = {
    fullName: '',
    role: 'Owner',
    email: '',
    contact: '',
    branch: '',
    accountId: '',
    avatarUrl: '',
  }

  // Fetch profile
  try {
    const res = await axios.get('/api/owner-profile', { withCredentials: true })
    if (res.data && res.data.ok && res.data.user) {
      ownerProfile.value = normalizeUser(res.data.user)
    }
    isProfileLoading.value = false
  } catch (e) {
    // If 401, user session expired - redirect to login
    if (e.response?.status === 401) {
      router.push('/staff-landing')
      return
    }
    isProfileLoading.value = false
  }

  // Now load dashboard after initial mount flag is set
  try {
    await loadDashboard(activeRange.value)
  } catch (err) {
    console.error('Dashboard load failed:', err)
  }



  // Remove loading overlay after content is loaded
  clearTemporaryOverlay()

  // Hide global page blur if present
  try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
})

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true

  try {
    // Use server GET logout endpoint to avoid CSRF token issues (server redirects to landing)
    // Clear client storage then navigate to server logout which will invalidate session
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    window.location.replace('/logout')
  } catch (e) {}

  // Show CHIKIN TAYO overlay + page blur, then navigate via router
  overlayText.value = 'Logging out...'
  try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}
  showOverlay.value = true

  // close the confirm modal immediately
  showLogoutConfirm.value = false

  // wait a short moment for the overlay to appear, then use SPA navigation
    setTimeout(() => {
      // Clear any client-side state to prevent SPA from showing protected pages
      try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}

      // Use full-page navigation so the browser requests the server (which has invalidated session)
      // and receives no-cache headers. Use replace to avoid adding a new history entry.
      try {
        window.location.replace('/staff-landing')
      } catch (e) {
        // fallback to router navigation if replace fails
        router.push('/staff-landing').catch(() => {})
      }
    }, 600)
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Admin.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

// Announcement modal state (OWNER)
const showAnnouncement = ref(false)
const announcementTitle = ref('')
const announcementText = ref('')
const announcementTarget = ref('all')
const announcementError = ref('')
const announcementSuccess = ref('')
const isSendingAnnouncement = ref(false)

function clearTemporaryOverlay() {
  try {
    const overlayHost = window.__chikin_temp_overlay
    if (!overlayHost) return
    const overlayApp = overlayHost.__loadingOverlayApp
    if (overlayApp && typeof overlayApp.unmount === 'function') {
      overlayApp.unmount()
    }
    overlayHost.remove()
    window.__chikin_temp_overlay = null
  } catch (e) {}
}

function mountTemporaryOverlay(message) {
  try {
    if (window.__chikin_temp_overlay) return false

    const overlayHost = document.createElement('div')
    const overlayApp = createApp({
      render() {
        return h(LoadingOverlay, {
          show: true,
          text: message,
          logoSrc: logoImg,
        })
      },
    })

    overlayApp.mount(overlayHost)
    overlayHost.__loadingOverlayApp = overlayApp
    document.body.appendChild(overlayHost)
    window.__chikin_temp_overlay = overlayHost
    return true
  } catch (e) {
    return false
  }
}

function closeAnnouncementModal() {
  showAnnouncement.value = false
  announcementTitle.value = ''
  announcementText.value = ''
  announcementTarget.value = 'all'
  announcementError.value = ''
  announcementSuccess.value = ''
}

async function sendAnnouncement() {
  if (!announcementTitle.value.trim() || !announcementText.value.trim()) {
    announcementError.value = 'Please enter a title and message.'
    return
  }
  isSendingAnnouncement.value = true
  announcementError.value = ''
  announcementSuccess.value = ''
  try {
    // ensure CSRF cookie
    try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}

    const res = await axios.post('/api/superadmin/announce', {
      title: announcementTitle.value,
      message: announcementText.value,
      target: announcementTarget.value
    }, { withCredentials: true })

    if (res.data && res.data.ok) {
      announcementSuccess.value = 'Announcement sent successfully!'
      fetchAnnouncements()
      setTimeout(() => closeAnnouncementModal(), 1200)
    } else {
      announcementError.value = res.data?.message || 'Failed to send announcement.'
    }
  } catch (e) {
    announcementError.value = e?.response?.data?.message || e?.message || 'Failed to send announcement.'
  } finally {
    isSendingAnnouncement.value = false
  }
}

// Only define goToStaffManagement for non-STAFF roles
function goToStaffManagement() {
  if (ownerProfile.value.role === 'STAFF') return
  try {
    if (!mountTemporaryOverlay('Opening Staff Management...')) return
    try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}
    setTimeout(() => {
      try {
        window.location.href = '/staff-management'
      } catch (e) {
        try { router.push('/staff-management') } catch (err) {}
      }
    }, 220)
  } catch (e) {
    try { router.push('/staff-management') } catch (err) {}
  }
}

function goToDishApproval() {
  if (ownerProfile.value.role !== 'OWNER') return
  try {
    if (!mountTemporaryOverlay('Opening Dish Approval...')) return
    try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}
    setTimeout(() => {
      try {
        window.location.href = '/owner/dish-approval'
      } catch (e) {
        try { router.push('/owner/dish-approval') } catch (err) {}
      }
    }, 220)
  } catch (e) {
    try { router.push('/owner/dish-approval') } catch (err) {}
  }
}

function goToPriceMarkupApprovals() {
  if (ownerProfile.value.role !== 'OWNER') return
  try {
    if (!mountTemporaryOverlay('Opening Price Markup Approvals...')) return
    try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}
    setTimeout(() => {
      try {
        window.location.href = '/owner/price-markup-approvals'
      } catch (e) {
        try { router.push('/owner/price-markup-approvals') } catch (err) {}
      }
    }, 220)
  } catch (e) {
    try { router.push('/owner/price-markup-approvals') } catch (err) {}
  }
}

function goToAddBranches() {
  if (ownerProfile.value.role !== 'ADMIN') return
  try {
    router.push('/main-branch/branches')
  } catch (e) {
    window.location.href = '/main-branch/branches'
  }
}

  onMounted(() => {
    loadDashboard(activeRange.value)
    // load branches + attendance overview for admin
    loadBranches()
    loadAdminAttendance(activeRange.value)
    loadAttendanceSettings()
    axios
      .get('/api/owner-profile', { withCredentials: true })
      .then(res => {
        if (res.data.ok) {
          ownerProfile.value = normalizeUser(res.data.user)
        }
      })
      .catch(() => {})

    // Close profile dropdown when clicking outside
    try { window.addEventListener('click', onDocumentClick) } catch (e) {}

    // load announcements for side panel
    fetchAnnouncements()
  })

  onUnmounted(() => {
    try { window.removeEventListener('click', onDocumentClick) } catch (e) {}
  })

</script>

<style scoped>
.primary-action-btn {
  background: linear-gradient(135deg, #2b8aef, #1a6ed8);
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  margin-bottom: 0.5rem;
  transition: all 0.2s;
}
.primary-action-btn:hover {
  background: linear-gradient(135deg, #1a6ed8, #1557b0);
}

.modal-header-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e9ecef;
}

.modal-body-custom {
  margin-bottom: 1.5rem;
}

.form-group-custom {
  margin-bottom: 1.25rem;
}

.form-group-custom .info-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #495057;
  font-size: 0.95rem;
}

.modal-footer-custom {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}

.modal-footer-custom .btn-outline,
.modal-footer-custom .btn-primary {
  flex: 1;
  min-width: 100px;
}

@media (max-width: 640px) {
  .modal-footer-custom {
    flex-direction: column;
  }
}

h1, h2 {
  color: var(--text-dark) !important;
  font-weight: 800 !important;
  font-family: 'Inter', 'Poppins', sans-serif !important;
  letter-spacing: -0.5px !important;
  margin-bottom: 8px !important;
}

.admin-label, .metric-label, .overview-label, .branch-count {
  color: #64748B !important;
}



.avatar-change-text {
  color: var(--text-dark) !important;
}

.btn-primary {
  background: #0066FF !important;
  color: white !important;
}

.btn-primary:hover {
  background: #0057e6 !important;
}

.btn-secondary, .btn-outline {
  background: #64748B !important;
  color: white !important;
}

.btn-secondary:hover, .btn-outline:hover {
  background: #525c6a !important;
}

.brand-text {
  color: var(--text-dark);
  font-weight: 800;
}

.panel-text {
  color: #64748B;
  font-weight: 800;
}

.admin-main-header h1 {
  font-family: 'Inter', 'Poppins', sans-serif;
  letter-spacing: -0.5px;
  margin-bottom: 8px;
}

/* Default: left-align the main header and its tabs for Admin Panel */
.admin-main-header {
  padding: 18px 24px;
  position: relative;
}
.admin-main-header .admin-main-header-top {
  display: block;
  position: relative;
  min-height: 100px;
  margin: 0;
}
.admin-main-header .admin-main-header-top > div:first-child {
  text-align: left;
  max-width: 800px;
}
  .admin-main-header .admin-main-header-top .header-actions-top {
  position: absolute;
  top: -10px;
  right: 16px;
  display: flex;
  align-items: center;
  flex-shrink: 0;
  padding: 0;
  z-index: 100;
}
.admin-main-header .admin-main-header-top .range-tabs {
  display: flex;
  justify-content: flex-start;
  gap: 12px;
  margin-top: 16px;
  position: absolute;
  bottom: 0;
  left: 0;
}

/* Header profile button styling */
.header-profile-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border: none;
  background: white;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-profile-btn:hover {
  background: #F9FAFB;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.header-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #FF9A4A, #FF6A3D);
  color: white;
  font-weight: 600;
  font-size: 14px;
  flex-shrink: 0;
}

.header-avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
}

.header-avatar-initials {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #FF9A4A, #FF6A3D);
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.header-name {
  font-size: 13px;
  font-weight: 600;
  color: #1F2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}

.header-profile-wrapper {
  position: relative;
}

.header-profile-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  margin-top: 4px;
  min-width: 160px;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 10px 16px;
  border: none;
  background: transparent;
  text-align: left;
  cursor: pointer;
  font-size: 13px;
  color: #374151;
  transition: all 0.2s;
}

.dropdown-item:first-child {
  border-radius: 6px 6px 0 0;
}

.dropdown-item:last-child {
  border-radius: 0 0 6px 6px;
}

.dropdown-item:hover {
  background: #F3F4F6;
}

.panel-body--center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.panel-body--center > div {
  width: 100%;
  max-width: 1100px;
}

/* Large-screen adjustments: ensure center column layout and center the range-tabs */
@media (min-width: 1024px) {
  /* Force main column to occupy center grid slot */
  .admin-main { grid-column: 2; }

  /* Range tabs: keep them left-aligned inside the center column */
  .admin-main-header-top .range-tabs {
    position: absolute;
    bottom: 0;
    left: 0;
    width: auto;
    display: flex;
    justify-content: flex-start; /* left-align the tabs */
    margin-top: 0;
    gap: 12px;
  }

  .range-tab { min-width: 90px; }

  /* Ensure side panels (Top Products, Low Stock, Staff Activity, Announcements)
     are visually positioned in the right column. */
  .admin-side { grid-column: 3; }
  .admin-profile-column { grid-column: 3; }

  /* Keep right side panels visually pinned to the top-right on large screens */
  .admin-side {
    align-self: start;
  }

  /* Make the first panel in the right column (Top Products) sticky to the viewport */
  .admin-side .panel:first-child {
    position: sticky;
    top: 18px;
    z-index: 105;
  }
  /* Style for header profile when rendered in the right column */
  .admin-side .header-actions-top {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 12px;
    /* Keep profile inside the right column so it scrolls with the page */
    position: relative;
    top: 0;
    right: 0;
    z-index: 1100;
    background: transparent;
  }

  .admin-side .header-actions-top .header-profile-wrapper {
    position: relative;
  }

  /* Strong rule to ensure profile stays fixed and doesn't scroll */
  .header-actions-side {
    position: static !important;
    top: auto !important;
    right: auto !important;
    z-index: auto !important;
  }

  /* Pin the main header to the top-left on large screens */
  .admin-main-header {
    position: fixed;
    top: 12px;
    left: 16px;
    width: 320px;
    z-index: 1200;
    background: transparent;
    padding: 8px 12px;
    border-radius: 8px;
  }

}

/* Left-align header when viewing as SUPERADMIN only */
.admin-main-header--left {
  padding: 18px 24px;
  position: relative;
}
.admin-main-header--left .admin-main-header-top {
  display: block !important;
  position: relative !important;
  min-height: 100px !important;
  margin: 0 !important;
}
.admin-main-header--left .admin-main-header-top > div:first-child {
  text-align: left !important;
  max-width: 900px !important;
  margin-bottom: 0 !important;
}
.admin-main-header--left .admin-main-header-top .header-actions-top {
  position: fixed !important;
  top: -10px !important;
  right: 16px !important;
  display: flex !important;
  align-items: center !important;
  flex-shrink: 0 !important;
  padding: 0 !important;
  z-index: 100 !important;
}
.admin-main-header--left .admin-main-header-top .range-tabs {
  position: absolute !important;
  bottom: 0 !important;
  left: 0 !important;
  width: auto !important;
  display: flex !important;
  justify-content: flex-start !important;
  gap: 8px !important;
  margin-top: 0 !important;
}
.admin-main-header--left .admin-main-header-top .range-tabs .range-tab {
  min-width: 80px;
}

</style>
