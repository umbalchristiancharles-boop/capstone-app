<template>
        <!-- Force Password Change Modal -->
        <ForcePasswordChangeModal
          v-if="showForceModal"
          :show="showForceModal"
          :username="ownerProfile.username"
          :defaultPassword="''"
          @close="showForceModal = false"
        />
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT:  OWNER PROFILE COLUMN (same layout as admin) -->
        <aside class="admin-profile-column">
          <div v-if="!isProfileLoading" class="admin-card admin-card--stacked">
            <!-- PROFILE PICTURE + NAME + ROLE -->
            <div class="admin-card__header admin-card__header--stacked">
              <label class="admin-avatar admin-avatar--photo avatar-upload" for="avatar-input">
                <img
                  v-if="ownerProfile.avatarUrl"
                  :src="ownerProfile.avatarUrl"
                  alt="Profile picture"
                  class="avatar-img"
                />
                <div v-else class="avatar-placeholder">
                  <span class="avatar-initials">CT</span>
                </div>
                <div class="avatar-overlay">
                  <span class="avatar-change-text">Change Photo</span>
                </div>
              </label>

              <div class="admin-header-text admin-admin-header-text--center">
                <div class="admin-label">Account</div>
                <div class="admin-name">
                  {{ ownerProfile.fullName || 'Owner' }}
                </div>
                <div class="admin-role">
                  {{ ownerProfile.role || 'OWNER' }}
                </div>
              </div>

              <input
                id="avatar-input"
                type="file"
                accept="image/*"
                @change="onAvatarChange"
                style="display: none"
              />
            </div>

            <div class="admin-card__body admin-card__body--stacked">
              <div class="admin-id-block admin-id-block--center">
                <span class="admin-id-label">Account I.D: </span>
                <span class="admin-id-value">&nbsp;{{ ownerProfile.accountId || 'ow0001' }}</span>
              </div>

              <button class="admin-info-btn admin-info-btn--center" @click="openInfoModal">Info</button>

              <div class="admin-qr-block admin-qr-block--center">
                <div class="qr-placeholder">QR</div>
              </div>
            </div>

              <div class="admin-card__footer admin-card__footer--stacked">
                <div class="admin-metrics-row">
                  <div class="admin-metric">
                    <div class="metric-icon">👥</div>
                    <div class="metric-text">
                      <span class="metric-label">Total Branches: </span>
                      <span class="metric-value">&nbsp;{{ summaryTotals.totalBranches }}</span>
                    </div>
                  </div>

                  <div class="admin-metric">
                    <div class="metric-icon">👨‍🍳</div>
                    <div class="metric-text">
                      <span class="metric-label">Total Employees:</span>
                      <span class="metric-value">&nbsp;{{ summaryTotals.totalEmployees }}</span>
                    </div>
                  </div>
                </div>

                <div class="owner-extra">
                  <div class="owner-extra-row">
                    <span class="owner-label">Access Level:</span>
                    <span class="owner-value">Full control</span>
                  </div>
                  <div class="owner-extra-row">
                    <span class="owner-label">Assigned Branch:</span>
                    <span class="owner-value">{{ typeof ownerProfile.branch === 'object' && ownerProfile.branch.name ? ownerProfile.branch.name : (ownerProfile.branch || 'Chikin Tayo – Main') }}</span>
                  </div>
                </div>

                <!-- Staff Management button moved here -->
                <div class="admin-actions-row">
                  <!-- Staff Management button: visible for OWNER only -->
                  <button
                    class="staff-btn staff-btn--center"
                    v-if="ownerProfile.role === 'OWNER'"
                    @click="router.push('/owner/staff-management')"
                  >
                    Staff Management
                  </button>
                  <button class="logout-btn logout-btn--center" @click="showLogoutConfirm = true">Logout</button>
                </div>
              </div>
          </div>
        </aside>

        <!-- MIDDLE: MAIN DASHBOARD -->
        <main class="admin-main">
          <header class="admin-main-header">
            <div class="admin-main-header-top">
              <div>
                <h1>{{ panelTitle }}</h1>
                <p>{{ panelDescription }}</p>
                <!-- Staff Management button moved to left column above logout -->
                <p v-if="isLoadingDashboard && !isInitialMount" class="small-hint">Loading dashboard…</p>
                <p v-else-if="dashboardError" class="small-hint small-hint--error">{{ dashboardError }}</p>
              </div>

              <div class="range-tabs">
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'today' }" @click="changeRange('today')">Today</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'yesterday' }" @click="changeRange('yesterday')">Yesterday</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisWeek' }" @click="changeRange('thisWeek')">This Week</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'lastWeek' }" @click="changeRange('lastWeek')">Last Week</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisMonth' }" @click="changeRange('thisMonth')">This Month</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'lastMonth' }" @click="changeRange('lastMonth')">Last Month</button>
              </div>
            </div>
          </header>

          <section class="overview-grid">
            <div class="overview-card"><span class="overview-label">Orders:</span><span class="overview-value">&nbsp;{{ dashboardTotals.orders }}</span></div>
            <div class="overview-card"><span class="overview-label">Completed Orders: </span><span class="overview-value">&nbsp;{{ dashboardTotals.completed }}</span></div>
            <div class="overview-card"><span class="overview-label">Sales:</span><span class="overview-value">&nbsp;{{ dashboardTotals.sales }}</span></div>
            <div class="overview-card"><span class="overview-label">Pending Orders:</span><span class="overview-value">&nbsp;{{ dashboardTotals.pending }}</span></div>
          </section>

          <section class="panel-block">
            <div class="panel-header">
              <h2>Orders</h2>
              <button class="panel-action" @click="showAllOrders = !showAllOrders">{{ showAllOrders ? 'Show less' : 'View all' }}</button>
            </div>

            <div class="panel-body panel-body--table">
              <div class="table-header"><span>Order #</span><span>Customer</span><span>Status</span><span>Total</span></div>

              <div v-if="recentOrders.length === 0" class="table-row"><span>No recent orders for this range.</span><span></span><span></span><span></span></div>

              <div v-else v-for="order in visibleOrders" :key="order.id" class="table-row">
                <span>{{ order.code }}</span>
                <span>{{ order.customer }}</span>
                <span>
                  <span class="badge" :class="{
                    'badge--success': order.status === 'completed',
                    'badge--warning': order.status === 'in_kitchen',
                    'badge--info': order.status === 'pending'
                  }">{{ order.statusLabel }}</span>
                </span>
                <span>{{ order.total }}</span>
              </div>
            </div>
          </section>

          <section class="panel-block">
            <div class="panel-header"><h2>Production Queue</h2></div>
            <div class="panel-body panel-body--list">
              <div v-if="productionQueue.length === 0" class="queue-item"><div class="queue-title">No items in production.</div></div>
              <div v-else v-for="item in productionQueue" :key="item.id" class="queue-item">
                <div>
                  <div class="queue-title">{{ item.title }}</div>
                  <div class="queue-meta">{{ item.meta }}</div>
                </div>
                <span class="badge" :class="item.badgeClass">{{ item.badgeLabel }}</span>
              </div>
            </div>
          </section>
        </main>

        <!-- RIGHT: SIDE PANELS (same as admin) -->
        <aside class="admin-side">
          <section class="panel-block">
            <div class="panel-header"><h2>Top Products</h2></div>
            <div class="panel-body panel-body--list">
              <div v-if="topProducts.length === 0" class="side-item"><span>No data for this range.</span></div>
              <div v-else v-for="prod in topProducts" :key="prod.id" class="side-item"><span>{{ prod.name }}</span><span class="side-value">{{ prod.orders }} orders</span></div>
            </div>
          </section>

          <section class="panel-block">
            <div class="panel-header"><h2>Low Stock Items</h2></div>
            <div class="panel-body panel-body--list">
              <div v-if="lowStockItems.length === 0" class="side-item side-item--alert"><span>All items above minimum stock.</span></div>
              <div v-else v-for="item in lowStockItems" :key="item.id" class="side-item side-item--alert"><span>{{ item.name }}</span><span class="side-value">{{ item.stock }}</span></div>
            </div>
          </section>

          <section class="panel-block">
            <div class="panel-header"><h2>Staff Activity</h2></div>
            <div class="panel-body panel-body--list">
              <div v-if="adminStaffActivity.length === 0" class="side-item"><span>No recent staff activity.</span></div>
              <div v-else v-for="act in adminStaffActivity" :key="act.name" class="side-item">
                <div>
                  <div class="activity-title">{{ act.name }}</div>
                  <div class="activity-meta">{{ act.role }} - {{ act.branch }}</div>
                </div>
                <span class="activity-time">{{ act.last_active }}</span>
              </div>
            </div>
          </section>

          <section class="panel-block">
            <div class="panel-header" style="display:flex; align-items:center; justify-content:space-between; gap:12px;"><h2>Attendance Monitoring</h2>
              <div style="display:flex; gap:8px; align-items:center;">
                <select v-model="selectedBranchId" @change="loadAdminAttendance(activeRange)" style="padding:6px; border-radius:6px;">
                  <option :value="null">All branches</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                <button class="panel-action" @click="loadAdminAttendance(activeRange)">Refresh</button>
              </div>
            </div>
            <div class="panel-body panel-body--table">
              <div class="table-header"><span>Staff Name</span><span>Branch</span><span>Time In</span><span>Time Out</span><span>Hours</span><span>Status</span></div>
              <div v-if="adminAttendance.length === 0" class="table-row"><span>No attendance records for this range.</span><span></span><span></span><span></span><span></span><span></span></div>
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

      <!-- INFO MODAL -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Owner Information</h3>
            <p class="info-sub">Personal details for this owner can be updated from this panel.</p>

            <div class="info-grid">
              <div class="info-row"><span class="info-label">Full name</span><span class="info-value" v-if="!isEditingInfo">{{ ownerProfile.fullName }}</span>
                <input v-else v-model="ownerProfile.fullName" class="info-input" type="text" />
              </div>

              <div class="info-row"><span class="info-label">Role</span><span class="info-value">{{ ownerProfile.role }}</span></div>

              <div class="info-row"><span class="info-label">Username</span><span class="info-value" v-if="!isEditingInfo">{{ ownerProfile.username }}</span>
                <input v-else v-model="ownerProfile.username" class="info-input" type="text" placeholder="Enter username" />
              </div>

              <div class="info-row"><span class="info-label">Email</span><span class="info-value" v-if="!isEditingInfo">{{ ownerProfile.email }}</span>
                <input v-else v-model="ownerProfile.email" class="info-input" type="email" />
              </div>

              <div class="info-row"><span class="info-label">Contact</span><span class="info-value" v-if="!isEditingInfo">{{ ownerProfile.contact }}</span>
                <input v-else v-model="ownerProfile.contact" class="info-input" type="text" />
              </div>

              <div class="info-row"><span class="info-label">Branch</span><span class="info-value">{{ typeof ownerProfile.branch === 'object' && ownerProfile.branch.name ? ownerProfile.branch.name : (ownerProfile.branch || 'Not assigned') }}</span></div>

              <!-- Password fields - only shown when editing -->
              <template v-if="isEditingInfo">
                <div class="info-row info-row--password">
                  <span class="info-label">New Password</span>
                  <input v-model="ownerProfile.password" class="info-input" type="password" placeholder="Leave blank to keep current" />
                </div>

                <div class="info-row info-row--password">
                  <span class="info-label">Confirm Password</span>
                  <input v-model="ownerProfile.password_confirmation" class="info-input" type="password" placeholder="Re-enter new password" />
                </div>
              </template>
            </div>

            <!-- Error message display -->
            <div v-if="profileError" class="info-error">
              {{ profileError }}
            </div>

            <!-- Success message display -->
            <div v-if="profileSuccess" class="info-success">
              {{ profileSuccess }}
            </div>

            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>
              <button class="btn-primary" @click="isEditingInfo ? saveOwnerInfo() : (isEditingInfo = true)" :disabled="isSavingProfile">
                {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- LOGOUT CONFIRM MODAL -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
          <div class="logout-confirm-box">
            <h3>Logout from Owner Panel?</h3>
            <p>This will end your current session for Chikin Tayo Owner.</p>
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
    </div>
  </div>
</template>

<script setup>
import ForcePasswordChangeModal from './ForcePasswordChangeModal.vue'
const showForceModal = ref(false)
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
// Add missing refs for properties used in template
const profileError = ref('')
const profileSuccess = ref('')
const isSavingProfile = ref(false)

const router = useRouter()
const activeRange = ref('today')

const dashboardTotals = ref({ orders: 0, completed: 0, sales: '₱0', pending: 0 })
const summaryTotals = ref({ totalBranches: 0, totalEmployees: 0 })

const productionQueue = ref([])
const topProducts = ref([])
const lowStockItems = ref([])
const staffActivity = ref([])
const adminStaffActivity = ref([])
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
const isInitialMount = ref(true)
const isProfileLoading = ref(true)

const showInfoModal = ref(false)
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

const ownerProfile = ref({ fullName: '', username: '', role: 'OWNER', email: '', contact: '', branch: '', accountId: '', avatarUrl: '' })
const isEditingInfo = ref(false)

const panelTitle = computed(() => 'Chikin Tayo Owner Panel')
const panelDescription = computed(() => 'Monitor branches, orders, and staff activity as Owner.')

function normalizeUser(u) {
  if (!u) return { fullName: '', username: '', role: '', email: '', contact: '', branch: '', accountId: '', avatarUrl: '' }
  return {
    fullName: u.fullName ?? u.full_name ?? '',
    username: u.username ?? '',
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
    if (res.data) branches.value = res.data || []
  } catch (e) { branches.value = [] }
}

async function loadAdminAttendance(range = 'today') {
  try {
    const params = { range }
    if (selectedBranchId.value) params.branch_id = selectedBranchId.value
    const res = await axios.get('/api/admin/attendance', { params, withCredentials: true })
    if (res.data && res.data.ok) adminAttendance.value = res.data.data || []
    else adminAttendance.value = []
  } catch (e) { adminAttendance.value = [] }
}

async function loadDashboard(range) {
  isLoadingDashboard.value = true
  dashboardError.value = ''
  dashboardTotals.value = { orders: 0, completed: 0, sales: '₱0', pending: 0 }
  summaryTotals.value = { totalBranches: 0, totalEmployees: 0 }
  recentOrders.value = []
  productionQueue.value = []
  topProducts.value = []
  lowStockItems.value = []
  staffActivity.value = []
  adminStaffActivity.value = []

  try {
    const res = await axios.get('/api/admin/dashboard', { params: { range }, withCredentials: true })
    if (res.data) {
      summaryTotals.value = { totalBranches: res.data.branches_count || 0, totalEmployees: res.data.staff_count || 0 }
      dashboardTotals.value = { orders: res.data.orders_count || 0, completed: 0, sales: '₱0', pending: 0 }
      adminStaffActivity.value = res.data.recent_activity || []
    }
  } catch (e) {
    if (e.response?.status === 401) { router.push('/admin-login'); return }
    dashboardError.value = 'Error loading dashboard.'
  } finally {
    isLoadingDashboard.value = false
    try { if (window.__chikin_temp_overlay) { window.__chikin_temp_overlay.remove(); window.__chikin_temp_overlay = null } } catch (e) {}
    try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
    try { showOverlay.value = false } catch (e) {}
  }
}

async function changeRange(range) {
  if (activeRange.value === range) return
  activeRange.value = range
  await loadDashboard(range)
  try { await loadAdminAttendance(range) } catch (e) {}
}

async function openInfoModal() {
  showInfoModal.value = true
  isEditingInfo.value = false
  try {
    const res = await axios.get('/api/owner-profile', { withCredentials: true })
    if (res.data.ok) ownerProfile.value = normalizeUser(res.data.user)
  } catch (e) {}
}

function handleInfoClose() { if (isEditingInfo.value) isEditingInfo.value = false; else showInfoModal.value = false }

async function saveOwnerInfo() {
  isSavingProfile.value = true
  profileError.value = ''
  profileSuccess.value = ''
  try {
    const payload = {
      fullName: ownerProfile.value.fullName,
      username: ownerProfile.value.username,
      email: ownerProfile.value.email,
      contact: ownerProfile.value.contact
    }
    if (ownerProfile.value.password) {
      payload.password = ownerProfile.value.password
      payload.password_confirmation = ownerProfile.value.password_confirmation
    }
    const res = await axios.put('/api/owner-profile', payload, { withCredentials: true })
    if (res.data.ok) {
      isEditingInfo.value = false
      profileSuccess.value = 'Profile updated successfully.'
      // Optionally clear password fields
      ownerProfile.value.password = ''
      ownerProfile.value.password_confirmation = ''
    } else {
      profileError.value = res.data.message || 'Failed to update profile.'
    }
  } catch (e) {
    profileError.value = e?.response?.data?.message || 'Failed to update profile.'
  } finally {
    isSavingProfile.value = false
  }
}

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (!window.confirm('Are you sure you want to change your profile picture?')) return
  try {
    try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
    await new Promise(resolve => setTimeout(resolve, 100))
    function getCookie(name) { const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return m ? m[2] : null }
    const xsrf = getCookie('XSRF-TOKEN')
    const formData = new FormData(); formData.append('avatar', file)
    if (xsrf) { try { formData.append('_token', decodeURIComponent(xsrf)) } catch (_) { formData.append('_token', xsrf) } }
    const config = { headers: { 'Content-Type': 'multipart/form-data' }, withCredentials: true }
    if (xsrf) { try { config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (_) { config.headers['X-XSRF-TOKEN'] = xsrf } }
    const res = await axios.post('/api/upload-avatar', formData, config)
    if (res.data && res.data.ok) { ownerProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now(); alert('Profile picture updated successfully!') }
  } catch (e) { console.error('Avatar upload failed:', e); alert('Failed to upload profile picture. Please try again.') }
}

onMounted(async () => {
  isInitialMount.value = false
  ownerProfile.value = { fullName: '', role: 'OWNER', email: '', contact: '', branch: '', accountId: '', avatarUrl: '' }
  try {
    const res = await axios.get('/api/owner-profile', { withCredentials: true });
    if (res.data && res.data.ok && res.data.user) {
      ownerProfile.value = normalizeUser(res.data.user);
      // Show password update modal if must_change_password is true
      if (res.data.user.must_change_password) {
        showForceModal.value = true;
      }
    }
    isProfileLoading.value = false;
  } catch (e) {
    if (e.response?.status === 401) { router.push('/admin-login'); return }
    isProfileLoading.value = false;
  }
  await loadDashboard(activeRange.value)
  try { if (window.__chikin_temp_overlay) { window.__chikin_temp_overlay.remove(); window.__chikin_temp_overlay = null } } catch (e) {}
  try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
})

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try { try { localStorage.clear(); sessionStorage.clear(); } catch (e) {} window.location.replace('/logout') } catch (e) {}
  overlayText.value = 'Logging out...'
  try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}
  showOverlay.value = true
  showLogoutConfirm.value = false
  setTimeout(() => { try { localStorage.clear(); sessionStorage.clear(); } catch (e) {} try { window.location.replace('/') } catch (e) { router.push('/').catch(() => {}) } }, 600)
}

function cancelLogout() { if (isLoggingOut.value) return; showLogoutConfirm.value = false }

// Staff management navigation removed from Owner panel (owners should not manage staff here)

onMounted(() => {
  loadDashboard(activeRange.value)
  loadBranches()
  loadAdminAttendance(activeRange.value)
  axios.get('/api/owner-profile', { withCredentials: true }).then(res => { if (res.data.ok) ownerProfile.value = normalizeUser(res.data.user) }).catch(() => {})
})
</script>
