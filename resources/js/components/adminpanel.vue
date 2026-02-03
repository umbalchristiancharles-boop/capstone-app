<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT:  ADMIN PROFILE COLUMN -->
        <aside class="admin-profile-column">
          <div v-if="!isProfileLoading" class="admin-card admin-card--stacked">
            <!-- PROFILE PICTURE + NAME + ROLE -->
            <div class="admin-card__header admin-card__header--stacked">
              <!-- clickable avatar -->
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
                  {{ ownerProfile.fullName || 'System Administrato' }}
                </div>
                <div class="admin-role">
                  {{ ownerProfile.role || 'OWNER' }}
                </div>
              </div>

              <!-- hidden file input -->
              <input
                id="avatar-input"
                type="file"
                accept="image/*"
                @change="onAvatarChange"
                style="display: none"
              />
            </div>

            <!-- ACCOUNT ID + INFO BUTTON + QR -->
            <div class="admin-card__body admin-card__body--stacked">
              <div class="admin-id-block admin-id-block--center">
                <span class="admin-id-label">Account I.D: </span>
                <span class="admin-id-value">
                  &nbsp;{{ ownerProfile.accountId || 'kk0001' }}
                </span>
              </div>

              <button
                class="admin-info-btn admin-info-btn--center"
                @click="openInfoModal"
              >
                Info
              </button>

              <div class="admin-qr-block admin-qr-block--center">
                <div class="qr-placeholder">QR</div>
              </div>
            </div>

            <!-- METRICS + EXTRA + CENTERED LOGOUT -->
            <div class="admin-card__footer admin-card__footer--stacked">
              <div class="admin-metrics-row">
                <div v-if="ownerProfile.role !== 'BRANCH_MANAGER'" class="admin-metric">
                  <div class="metric-icon">👥</div>
                  <div class="metric-text">
                    <span class="metric-label">Total Branches: </span>
                    <span class="metric-value">
                      &nbsp;{{ summaryTotals.totalBranches }}
                    </span>
                  </div>
                </div>

                <div class="admin-metric">
                  <div class="metric-icon">👨‍🍳</div>
                  <div class="metric-text">
                    <span class="metric-label">Total Employees:</span>
                    <span class="metric-value">
                      &nbsp;{{ summaryTotals.totalEmployees }}
                    </span>
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
                  <span class="owner-value">
                    {{ typeof ownerProfile.branch === 'object' && ownerProfile.branch.name ? ownerProfile.branch.name : (ownerProfile.branch || 'Chikin Tayo – QC Main') }}
                  </span>
                </div>
              </div>

              <div class="admin-actions-row">
                <!-- Staff Management Button -->
                <button
                  class="staff-btn staff-btn--center"
                  @click="goToStaffManagement"
                >
                  👥 Staff Management
                </button>

                <!-- Logout Button -->
                <button
                  class="logout-btn logout-btn--center"
                  @click="showLogoutConfirm = true"
                >
                  Logout
                </button>
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
                <p v-if="isLoadingDashboard && !isInitialMount" class="small-hint">
                  Loading dashboard…
                </p>
                <p v-else-if="dashboardError" class="small-hint small-hint--error">
                  {{ dashboardError }}
                </p>
              </div>

              <!-- Date range tabs -->
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
            </div>
          </header>

          <!-- Overview cards -->
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
        </aside>
      </section>

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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'

const router = useRouter()
const activeRange = ref('today')

const dashboardTotals = ref({
  orders: 0,
  completed: 0,
  sales: '₱0',
  pending: 0,
})

const summaryTotals = ref({
  totalBranches: 0,
  totalEmployees: 0,
})

const productionQueue = ref([])
const topProducts = ref([])
const lowStockItems = ref([])
const staffActivity = ref([])
const adminStaffActivity = ref([])
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

const panelTitle = computed(() => {
  const role = ownerProfile.value.role || 'OWNER'
  if (role === 'BRANCH_MANAGER') return 'Chikin Tayo Branch Manager Panel'
  return 'Chikin Tayo Admin Panel'
})

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
  summaryTotals.value = {
    totalBranches: 0,
    totalEmployees: 0,
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

    // Fetch dashboard data
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
        // Admin dashboard response structure
        summaryTotals.value = {
          totalBranches: res.data.branches_count || 0,
          totalEmployees: res.data.staff_count || 0,
        }

        dashboardTotals.value = {
          orders: res.data.orders_count || 0,
          completed: 0,
          sales: '₱0',
          pending: 0,
        }

        adminStaffActivity.value = res.data.recent_activity || []
      }
    }
  } catch (e) {
    // If 401, user session expired - redirect to login
    if (e.response?.status === 401) {
      router.push('/admin-login')
      return
    }
    dashboardError.value = 'Error loading dashboard.'
    console.error('Dashboard error:', e)
  } finally {
    isLoadingDashboard.value = false
    // Remove any temporary global overlay created by previous route
    try {
      if (window.__chikin_temp_overlay) {
        window.__chikin_temp_overlay.remove()
        window.__chikin_temp_overlay = null
      }
    } catch (e) {}

    // Hide global page blur (if using the pageBlur helper)
    try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}

    // Also ensure local overlay flag is cleared
    try { showOverlay.value = false } catch (e) {}
  }
}

async function changeRange(range) {
  if (activeRange.value === range) return
  activeRange.value = range
  await loadDashboard(range)
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
  if (!window.confirm('Are you sure you want to change your profile picture?')) return

  try {
    // Prepare form data
    const formData = new FormData()
    formData.append('avatar', file)

    // Get CSRF cookie
    try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
    await new Promise(resolve => setTimeout(resolve, 50))

    // Set CSRF token
    try {
      function getCookie(name) { const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return m ? m[2] : null }
      const xsrf = getCookie('XSRF-TOKEN')
      if (xsrf) {
        try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (_) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
      }
    } catch (e) {}

    // Upload directly without reload
    const res = await axios.post('/api/upload-avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      withCredentials: true
    })

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
      router.push('/admin-login')
      return
    }
    isProfileLoading.value = false
  }

  // Now load dashboard after initial mount flag is set
  loadDashboard(activeRange.value)
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
        window.location.replace('/')
      } catch (e) {
        // fallback to router navigation if replace fails
        router.push('/').catch(() => {})
      }
    }, 600)
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

function goToStaffManagement() {
  // create a temporary overlay DOM node so it persists across route change
  try {
    if (window.__chikin_temp_overlay) return
    const overlay = document.createElement('div')
    overlay.className = 'loading-overlay __chikin_temp_overlay'
    overlay.style.zIndex = '9999'
    overlay.style.backdropFilter = 'blur(8px)'
    overlay.style.webkitBackdropFilter = 'blur(8px)'
    overlay.innerHTML = `
      <div class="logo-loading-box">
        <img src="${logoImg}" alt="Chikin Tayo" class="logo-loading-img" />
        <p>Opening Staff Management...</p>
      </div>
    `
    document.body.appendChild(overlay)
    window.__chikin_temp_overlay = overlay
    // show global page blur so the background is blurred while overlay is visible
    try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}

    // give overlay a short moment to render, then navigate
    setTimeout(() => {
      router.push('/admin/staff-management').catch(() => {
        // navigation failed; leave cleanup to whoever created/handles the overlay
      })
    }, 220)
  } catch (e) {
    // fallback navigation if DOM manipulation fails
    try { router.push('/admin/staff-management') } catch (err) {}
  }
}

onMounted(() => {
  loadDashboard(activeRange.value)
  axios
    .get('/api/owner-profile', { withCredentials: true })
    .then(res => {
      if (res.data.ok) {
        ownerProfile.value = normalizeUser(res.data.user)
      }
    })
    .catch(() => {})
})
</script>
