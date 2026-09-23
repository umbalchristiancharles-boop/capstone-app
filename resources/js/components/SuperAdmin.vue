<template>
  <div class="super-admin-shell" :class="{ 'sidebar-collapsed': sidebarCollapsed, 'super-admin-sidebar-resizing': sidebarResizing }" :style="{ '--super-admin-sidebar-width': `${sidebarWidth}px` }">
    <aside class="super-admin-sidebar" :aria-hidden="sidebarCollapsed" :style="{ width: `${sidebarWidth}px` }">
      <nav class="super-admin-sidebar__nav" aria-label="Super Admin modules">
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'staff' }" @click="openModule('staff')">Staff Management</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'hr' }" @click="openModule('hr')">HR Staff Management</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'kitchen' }" @click="openModule('kitchen')">Kitchen Staff Monitoring</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'finance' }" @click="openModule('finance')">Finance</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'cashier' }" @click="openModule('cashier')">Cashier</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'logistics' }" @click="openModule('logistics')">Logistics</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'supplier' }" @click="openModule('supplier')">Supplier Management</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'procurement' }" @click="openModule('procurement')">Procurement</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'branches' }" @click="openModule('branches')">Owner Add Branches</button>
      </nav>

      <div class="super-admin-sidebar__footer">
        <button class="super-admin-sidebar__account" @click="openInfoModal">Account Info</button>
        <button class="super-admin-sidebar__action super-admin-sidebar__action--primary" @click="showAnnouncement = true">Send Announcement</button>
        <button class="super-admin-sidebar__action" @click="showTerms = true">Update Terms</button>
        <button class="super-admin-sidebar__logout" @click.prevent="askLogout">Logout</button>
      </div>
      <button
        class="super-admin-sidebar__resize-handle"
        type="button"
        aria-label="Resize sidebar"
        title="Resize sidebar"
        @pointerdown="startSidebarResize"
      ></button>
    </aside>

    <div class="super-admin-main-panel">
      <header class="super-admin-topbar">
        <button class="super-admin-hamburger" :aria-label="sidebarCollapsed ? 'Show menu' : 'Hide menu'" @click="sidebarCollapsed = !sidebarCollapsed">☰</button>
        <div class="super-admin-topbar__spacer"></div>
        <div class="super-admin-user-pill">
          <span class="super-admin-user-pill__avatar">{{ userInitial }}</span>
          <span>{{ superAdminProfile.fullName || 'Super Admin' }}</span>
        </div>
      </header>

      <div class="admin-page">
      <section v-if="activeModule === 'dashboard'" class="admin-layout">
          <div class="page-header-top">
            <div>
              <h1>{{ panelTitle }}</h1>
              <p>{{ panelDescription }}</p>
              <p v-if="isLoadingDashboard && !isInitialMount" class="small-hint">Loading dashboard...</p>
              <p v-else-if="dashboardError" class="small-hint small-hint--error">{{ dashboardError }}</p>
            </div>
          </div>

          <aside class="admin-side super-admin-dashboard-side">
            <section class="panel-block">
              <div class="panel-header"><h2>Top Products (All Branches)</h2></div>
              <div class="panel-body panel-body--list">
                <div v-if="topProducts.length === 0" class="side-item"><span>No data for this range.</span></div>
                <div v-else v-for="prod in topProducts" :key="prod.id" class="side-item"><span>{{ prod.name }}</span><span class="side-value">{{ prod.orders }} orders</span></div>
              </div>
            </section>
            <section class="panel-block">
              <div class="panel-header"><h2>Low Stock Alerts</h2></div>
              <div class="panel-body panel-body--list">
                <div v-if="lowStockItems.length === 0" class="side-item side-item--alert"><span>All items above minimum stock.</span></div>
                <div v-else v-for="item in lowStockItems" :key="item.id" class="side-item side-item--alert"><span>{{ item.name }}</span><span class="side-value">{{ item.stock }}</span></div>
              </div>
            </section>
            <section class="panel-block">
              <div class="panel-header"><h2>Staff Attendance (All Branches)</h2></div>
              <div class="panel-body panel-body--table">
                <div class="table-header"><span>Staff</span><span>Branch</span><span>Status</span></div>
                <div v-if="adminAttendance.length === 0" class="table-row"><span>No records.</span><span></span><span></span></div>
                <div v-else v-for="att in adminAttendance.slice(0, 10)" :key="att.id" class="table-row"><span>{{ att.user_name }}</span><span>{{ att.branch_name || '-' }}</span><span><span class="badge" :class="{ 'badge--success': att.status === 'present', 'badge--warning': att.status === 'late', 'badge--info': att.status === 'absent' }">{{ att.status || '-' }}</span></span></div>
              </div>
            </section>
          </aside>

        <main class="admin-main">
          <div class="range-tabs">
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'today' }" @click="changeRange('today')">Today</button>
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'yesterday' }" @click="changeRange('yesterday')">Yesterday</button>
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisWeek' }" @click="changeRange('thisWeek')">This Week</button>
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisMonth' }" @click="changeRange('thisMonth')">This Month</button>
          </div>

          <section class="overview-grid">
            <div class="overview-card"><span class="overview-label">Total Orders:</span><span class="overview-value">&nbsp;{{ dashboardTotals.orders }}</span></div>
            <div class="overview-card"><span class="overview-label">Completed: </span><span class="overview-value">&nbsp;{{ dashboardTotals.completed }}</span></div>
            <div class="overview-card"><span class="overview-label">Total Sales:</span><span class="overview-value">&nbsp;{{ dashboardTotals.sales }}</span></div>
            <div class="overview-card"><span class="overview-label">Pending:</span><span class="overview-value">&nbsp;{{ dashboardTotals.pending }}</span></div>
          </section>

          <section class="panel-block">
            <div class="panel-header">
              <h2>System Overview by Branch</h2>
            </div>
            <div class="panel-body panel-body--table">
              <div class="table-header"><span>Branch</span><span>Orders</span><span>Sales</span><span>Staff</span><span>Status</span></div>
              <div v-if="branchStats.length === 0" class="table-row"><span>No branch data for this range.</span><span></span><span></span><span></span><span></span></div>
              <div v-else v-for="branch in branchStats" :key="branch.id" class="table-row">
                <span>{{ branch.name }}</span>
                <span>{{ branch.orders }}</span>
                <span>{{ branch.sales }}</span>
                <span>{{ branch.staff_count }}</span>
                <span>
                  <span class="badge" :class="branch.is_active ? 'badge--success' : 'badge--warning'">
                    {{ branch.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </span>
              </div>
            </div>
          </section>

          <section class="panel-block">
            <div class="panel-header">
              <h2>Recent System Activity</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div v-if="systemActivity.length === 0" class="queue-item"><div class="queue-title">No recent activity.</div></div>
              <div v-else v-for="activity in systemActivity" :key="activity.id" class="queue-item">
                <div>
                  <div class="queue-title">{{ activity.title }}</div>
                  <div class="queue-meta">{{ activity.description }}</div>
                </div>
                <span class="badge" :class="activity.badgeClass">{{ activity.status }}</span>
              </div>
            </div>
          </section>
        </main>

      </section>

      <section v-else class="super-admin-module-view">
        <button type="button" class="super-admin-module-back" @click="activeModule = 'dashboard'">Back to Dashboard</button>
        <component :is="activeModuleComponent" />
      </section>

      <!-- ANNOUNCEMENT MODAL -->
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

      <!-- TERMS MODAL -->
      <transition name="fade">
        <div v-if="showTerms" class="info-backdrop">
          <div class="info-modal">
            <h3>Update Terms & Agreement</h3>
            <p class="info-sub">Update the system-wide terms and conditions.</p>

            <div class="info-grid">
              <div class="info-row">
                <span class="info-label">Terms Content</span>
                <textarea v-model="termsText" class="info-input" rows="10" placeholder="Enter terms and agreement..."></textarea>
              </div>
            </div>

            <div v-if="termsError" class="info-error">{{ termsError }}</div>
            <div v-if="termsSuccess" class="info-success">{{ termsSuccess }}</div>

            <div class="info-actions">
              <button class="btn-outline" @click="showTerms = false">Cancel</button>
              <button class="btn-primary" @click="updateTerms" :disabled="isUpdatingTerms">
                {{ isUpdatingTerms ? 'Saving...' : 'Save Terms' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- INFO MODAL -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Super Admin Information</h3>
            <p class="info-sub">Personal details for this super admin account.</p>

            <div class="info-grid">
              <div class="info-row">
                <span class="info-label">Full name</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.fullName }}</span>
                <input v-else v-model="superAdminProfile.fullName" class="info-input" type="text" />
              </div>

              <div class="info-row">
                <span class="info-label">Role</span>
                <span class="info-value">{{ superAdminProfile.role }}</span>
              </div>

              <div class="info-row">
                <span class="info-label">Username</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.username }}</span>
                <input v-else v-model="superAdminProfile.username" class="info-input" type="text" />
              </div>

              <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.email }}</span>
                <input v-else v-model="superAdminProfile.email" class="info-input" type="email" />
              </div>

              <div class="info-row">
                <span class="info-label">Contact</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.contact }}</span>
                <input v-else v-model="superAdminProfile.contact" class="info-input" type="text" />
              </div>
            </div>

            <div v-if="profileError" class="info-error">{{ profileError }}</div>
            <div v-if="profileSuccess" class="info-success">{{ profileSuccess }}</div>

            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>
              <button class="btn-primary" @click="isEditingInfo ? saveProfile() : (isEditingInfo = true)" :disabled="isSavingProfile">
                {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- LOGOUT CONFIRM MODAL -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
          <div class="logout-confirm-box logout-confirm-dialog">
            <h3>Logout from Super Admin Panel?</h3>
            <p>This will end your current session for Chikin Tayo System.</p>
            <div class="logout-actions">
              <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
              <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, defineAsyncComponent } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import { showToast } from './toastStore'

const router = useRouter()
const route = useRoute()

// Profile state
const superAdminProfile = ref({
   username: '',
  role: 'SUPER_ADMIN',
 fullName: '',
  email: '',
  contact: '',
  accountId: '',
  avatarUrl: ''
})
const isProfileLoading = ref(true)
const isEditingInfo = ref(false)
const isSavingProfile = ref(false)
const profileError = ref('')
const profileSuccess = ref('')

// Modals
const showInfoModal = ref(false)
const showLogoutConfirm = ref(false)
const showAnnouncement = ref(false)
const showTerms = ref(false)
const isLoggingOut = ref(false)
const sidebarCollapsed = ref(false)
const sidebarWidth = ref(156)
const sidebarResizing = ref(false)
const activeModule = ref('dashboard')

const moduleComponents = {
  staff: defineAsyncComponent(() => import('./SuperAdminStaffManagement.vue')),
  hr: defineAsyncComponent(() => import('./HRStaffManagement.vue')),
  kitchen: defineAsyncComponent(() => import('./SuperAdminKitchenStaff.vue')),
  finance: defineAsyncComponent(() => import('./SuperAdminFinance.vue')),
  cashier: defineAsyncComponent(() => import('./Cashier.vue')),
  logistics: defineAsyncComponent(() => import('./SuperAdminLogisticsPanel.vue')),
  supplier: defineAsyncComponent(() => import('./SuperAdminSupplier.vue')),
  procurement: defineAsyncComponent(() => import('./SuperAdminProcurement.vue')),
  branches: defineAsyncComponent(() => import('./OwnerAddBranches.vue')),
}

const activeModuleComponent = computed(() => moduleComponents[activeModule.value] || null)
let lightModeObserver = null

function startSidebarResize(event) {
  if (sidebarCollapsed.value) return

  event.preventDefault()
  sidebarResizing.value = true
  const startX = event.clientX
  const startWidth = sidebarWidth.value

  const resize = (moveEvent) => {
    sidebarWidth.value = Math.min(320, Math.max(120, startWidth + moveEvent.clientX - startX))
  }

  const stopResize = () => {
    sidebarResizing.value = false
    document.removeEventListener('pointermove', resize)
    document.removeEventListener('pointerup', stopResize)
  }

  document.addEventListener('pointermove', resize)
  document.addEventListener('pointerup', stopResize)
}

// Announcement
const announcementTitle = ref('')
const announcementText = ref('')
const announcementTarget = ref('all')
const announcementError = ref('')
const announcementSuccess = ref('')
const isSendingAnnouncement = ref(false)

// Close announcement modal and reset state
function closeAnnouncementModal() {
  showAnnouncement.value = false
  // Reset form fields
  announcementTitle.value = ''
  announcementText.value = ''
  announcementTarget.value = 'all'
  announcementError.value = ''
  announcementSuccess.value = ''
}

// Terms
const termsText = ref('')
const termsError = ref('')
const termsSuccess = ref('')
const isUpdatingTerms = ref(false)

// Dashboard
const activeRange = ref('today')
const isLoadingDashboard = ref(false)
const isInitialMount = ref(true)
const dashboardError = ref('')
const dashboardTotals = ref({ orders: 0, completed: 0, sales: '₱0', pending: 0 })
const summaryTotals = ref({ totalBranches: 0, totalEmployees: 0, totalAdmins: 0 })
const branchStats = ref([])
const systemActivity = ref([])
const topProducts = ref([])
const lowStockItems = ref([])
const adminAttendance = ref([])
const pendingCounts = ref({
  procurement: 0,
  logistics: 0,
  supplier: 0,
  finance: 0,
  kitchen: 0,
  cashier: 0,
})
const hasNotified = ref(false)

const panelTitle = computed(() => 'Chikin Tayo Super Admin Panel')
const panelDescription = computed(() => 'Full system access - manage all modules, branches, and system settings.')
const userInitial = computed(() => (superAdminProfile.value.fullName || superAdminProfile.value.role || 'S').charAt(0).toUpperCase())

function enforceSuperAdminLightMode() {
  try {
    document.documentElement.classList.remove('dark-mode')
    document.body.classList.remove('dark-mode')
    document.documentElement.classList.add('light-mode')
    document.body.classList.add('light-mode')
    document.documentElement.removeAttribute('data-superadmin-theme')
    document.body.removeAttribute('data-superadmin-theme')
  } catch (e) {}
}

function startLightModeGuard() {
  enforceSuperAdminLightMode()
  lightModeObserver = new MutationObserver(() => {
    if (document.documentElement.classList.contains('dark-mode') || document.body.classList.contains('dark-mode')) {
      enforceSuperAdminLightMode()
    }
  })
  lightModeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-superadmin-theme'] })
  lightModeObserver.observe(document.body, { attributes: true, attributeFilter: ['class', 'data-superadmin-theme'] })
}

function isDashboardSection(section) {
  if (section === 'staff') return route.path.includes('/staff-management')
  if (section === 'hr') return route.path.includes('/hr')
  return false
}

function normalizeUser(u) {
  if (!u) return { fullName: '', username: '', role: '', email: '', contact: '', accountId: '', avatarUrl: '' }
  return {
    fullName: u.fullName ?? u.full_name ?? '',
    username: u.username ?? '',
    role: u.role ?? '',
    email: u.email ?? '',
    contact: u.contact ?? u.phone_number ?? '',
    accountId: u.accountId ?? u.account_id ?? '',
    avatarUrl: u.avatarUrl ?? u.avatar_url ?? '',
  }
}

async function loadProfile() {
  try {
    const res = await axios.get('/api/superadmin-profile', { withCredentials: true })
    if (res.data && res.data.ok && res.data.user) {
      superAdminProfile.value = normalizeUser(res.data.user)
    }
    isProfileLoading.value = false
  } catch (e) {
    if (e.response?.status === 401) { router.push('/staff-landing'); return }
    isProfileLoading.value = false
  }
}

async function loadDashboard(range) {
  isLoadingDashboard.value = true
  dashboardError.value = ''
  dashboardTotals.value = { orders: 0, completed: 0, sales: '₱0', pending: 0 }
  summaryTotals.value = { totalBranches: 0, totalEmployees: 0, totalAdmins: 0 }
  branchStats.value = []
  systemActivity.value = []
  topProducts.value = []
  lowStockItems.value = []
  adminAttendance.value = []

  try {
    const res = await axios.get('/api/superadmin/dashboard', { params: { range }, withCredentials: true })
    if (res.data) {
      summaryTotals.value = {
        totalBranches: res.data.totalBranches || res.data.branches_count || 0,
        totalEmployees: res.data.totalEmployees || res.data.staff_count || 0,
        totalAdmins: res.data.totalAdmins || 0
      }
      dashboardTotals.value = {
        orders: res.data.orders || 0,
        completed: res.data.completed || 0,
        sales: res.data.sales || '₱0',
        pending: res.data.pending || 0
      }
      branchStats.value = res.data.branchStats || []
      systemActivity.value = res.data.systemActivity || []
      topProducts.value = res.data.topProducts || []
      lowStockItems.value = res.data.lowStockItems || []
      adminAttendance.value = res.data.attendance || []
    }
  } catch (e) {
    if (e.response?.status === 401) { router.push('/staff-landing'); return }
    dashboardError.value = 'Error loading dashboard.'
  } finally {
    isLoadingDashboard.value = false
  }
}

async function changeRange(range) {
  if (activeRange.value === range) return
  activeRange.value = range
  await loadDashboard(range)
}

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      pendingCounts.value = { ...pendingCounts.value, ...(res.data.counts || {}) }
      const total = Object.values(pendingCounts.value).reduce((sum, v) => sum + Number(v || 0), 0)
      if (!hasNotified.value && total > 0) {
        showToast('You have pending items across modules.', 'info')
        hasNotified.value = true
      }
    }
  } catch (e) {
    // Non-blocking
  }
}

async function openInfoModal() {
  showInfoModal.value = true
  isEditingInfo.value = false
  profileError.value = ''
  profileSuccess.value = ''
  try {
    const res = await axios.get('/api/superadmin-profile', { withCredentials: true })
    if (res.data.ok) superAdminProfile.value = normalizeUser(res.data.user)
  } catch (e) {}
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false
    profileError.value = ''
    profileSuccess.value = ''
  } else {
    showInfoModal.value = false
  }
}

async function saveProfile() {
  isSavingProfile.value = true
  profileError.value = ''
  profileSuccess.value = ''
  try {
    const payload = {
      fullName: superAdminProfile.value.fullName,
      username: superAdminProfile.value.username,
      email: superAdminProfile.value.email,
      contact: superAdminProfile.value.contact
    }
    const res = await axios.put('/api/superadmin-profile', payload, { withCredentials: true })
    if (res.data.ok) {
      isEditingInfo.value = false
      profileSuccess.value = 'Profile updated successfully.'
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
  if (!(await window.swalConfirm('Are you sure you want to change your profile picture?'))) return

  try {
    // Get CSRF cookie first
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

    // Wait a bit for cookie to be set
    await new Promise(resolve => setTimeout(resolve, 200))

    // Get CSRF token from cookie
    function getCookie(name) {
      const value = `; ${document.cookie}`
      const parts = value.split(`; ${name}=`)
      if (parts.length === 2) {
        return parts.pop().split(';').shift()
      }
      return null
    }
    const xsrfToken = getCookie('XSRF-TOKEN')

    // Prepare form data
    const formData = new FormData()
    formData.append('avatar', file)

    // Upload avatar to superadmin endpoint
    const res = await axios.post('/api/superadmin/avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-XSRF-TOKEN': xsrfToken || '',
      },
      withCredentials: true,
    })

    if (res.data && res.data.ok) {
      // Update local avatar immediately
      superAdminProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()

      // Optionally refresh profile from server to ensure consistency
      try {
        const profileRes = await axios.get('/api/superadmin-profile', { withCredentials: true })
        if (profileRes.data && profileRes.data.ok && profileRes.data.user) {
          superAdminProfile.value.avatarUrl = profileRes.data.user.avatarUrl
        }
      } catch (profileError) {
        console.log('Profile refresh skipped, using direct upload response')
      }

      alert('Profile picture updated successfully!')
    } else {
      alert(res.data.message || 'Failed to upload profile picture.')
    }
  } catch (e) {
    console.error('Avatar upload failed:', e)
    const errorMessage = e?.response?.data?.message || e?.message || 'Failed to upload profile picture. Please try again.'
    alert(errorMessage)
  }
}

function openModule(name) {
  if (moduleComponents[name]) activeModule.value = name
}

function ownerAddBranches() {
  activeModule.value = 'branches'
}

function goToSuperAdminStaff() {
  activeModule.value = 'staff'
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
    const res = await axios.post('/api/superadmin/announce', {
      title: announcementTitle.value,
      message: announcementText.value,
      target: announcementTarget.value
    }, { withCredentials: true })
    if (res.data.ok) {
      announcementSuccess.value = 'Announcement sent successfully!'
      setTimeout(() => {
        showAnnouncement.value = false
        announcementTitle.value = ''
        announcementText.value = ''
        announcementTarget.value = 'all'
      }, 1500)
    } else {
      announcementError.value = res.data.message || 'Failed to send announcement.'
    }
  } catch (e) {
    announcementError.value = e?.response?.data?.message || 'Failed to send announcement.'
  } finally {
    isSendingAnnouncement.value = false
  }
}

async function updateTerms() {
  if (!termsText.value.trim()) {
    termsError.value = 'Please enter terms content.'
    return
  }
  isUpdatingTerms.value = true
  termsError.value = ''
  termsSuccess.value = ''
  try {
    const res = await axios.post('/api/superadmin/terms', {
      content: termsText.value
    }, { withCredentials: true })
    if (res.data.ok) {
      termsSuccess.value = 'Terms updated successfully!'
      setTimeout(() => {
        showTerms.value = false
      }, 1500)
    } else {
      termsError.value = res.data.message || 'Failed to update terms.'
    }
  } catch (e) {
    termsError.value = e?.response?.data?.message || 'Failed to update terms.'
  } finally {
    isUpdatingTerms.value = false
  }
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    localStorage.clear()
    sessionStorage.clear()
    window.location.replace('/logout')
  } catch (e) {
    localStorage.clear()
    sessionStorage.clear()
    window.location.replace('/admin-login')
  }
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

onMounted(async () => {
  startLightModeGuard()
  isInitialMount.value = false
  superAdminProfile.value = { fullName: '', role: 'SUPER_ADMIN', email: '', contact: '', accountId: '', avatarUrl: '' }
  await loadProfile()
  await loadDashboard(activeRange.value)
  await loadPanelNotifications()
})

onBeforeUnmount(() => {
  lightModeObserver?.disconnect()
  lightModeObserver = null
})

// Reload dashboard whenever we navigate to this route so external changes (like added branches)
// are reflected immediately without requiring a manual refresh.
watch(() => route.path, (p) => {
  try {
    if (p === '/super-admin-panel') {
      loadDashboard(activeRange.value)
    }
  } catch (e) {}
})
</script>

<style scoped>
.super-admin-shell {
  --super-admin-sidebar-width: 156px;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  min-height: 100vh;
  background: #f1e5dc;
  color: #42210b;
}

.super-admin-sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 400;
  display: flex;
  width: var(--super-admin-sidebar-width);
  min-height: 100vh;
  overflow-x: hidden;
  overflow-y: auto;
  padding: 1.5rem 1rem 1rem;
  box-sizing: border-box;
  flex-direction: column;
  background: #f3e9e1;
  border-right: 1px solid #d8c8bc;
  transition: transform 220ms ease, opacity 220ms ease;
}

.super-admin-sidebar__resize-handle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 30;
  width: 10px;
  padding: 0;
  border: 0;
  background: transparent;
  cursor: col-resize;
}

.super-admin-sidebar__resize-handle::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 4px;
  width: 2px;
  height: 42px;
  border-radius: 999px;
  background: rgba(143, 79, 47, 0.38);
  transform: translateY(-50%);
  transition: height 160ms ease, background-color 160ms ease;
}

.super-admin-sidebar__resize-handle:hover::after,
.super-admin-sidebar__resize-handle:focus-visible::after {
  height: 64px;
  background: #8f4f2f;
}

.super-admin-sidebar-resizing .super-admin-sidebar,
.super-admin-sidebar-resizing .super-admin-main-panel,
.super-admin-sidebar-resizing .super-admin-topbar {
  transition: none !important;
}

.super-admin-sidebar__nav,
.super-admin-sidebar__footer {
  display: flex;
  width: 100%;
  min-width: 0;
  overflow-x: hidden;
  flex-direction: column;
  gap: 0.75rem;
}

.super-admin-sidebar__nav {
  overflow-y: auto;
}

.super-admin-sidebar__footer {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #d8c8bc;
}

.super-admin-sidebar__item,
.super-admin-sidebar__account,
.super-admin-sidebar__action,
.super-admin-sidebar__logout {
  position: relative;
  width: 100%;
  box-sizing: border-box;
  min-width: 0;
  min-height: 40px;
  padding: 0.65rem 0.55rem;
  border-radius: 10px;
  font-size: 0.76rem;
  font-weight: 700;
  line-height: 1.25;
  cursor: pointer;
}

.super-admin-sidebar__item {
  border: 1px solid transparent;
  background: transparent;
  color: #3d2a1f;
  text-align: left;
}

.super-admin-sidebar__item:hover,
.super-admin-sidebar__item.active {
  background: rgba(255, 255, 255, 0.85);
  border-color: rgba(148, 163, 184, 0.3);
  box-shadow: 0 8px 18px rgba(66, 33, 11, 0.08);
}

.super-admin-sidebar__account {
  border: 1px solid rgba(59, 130, 246, 0.25);
  background: rgba(59, 130, 246, 0.12);
  color: #30445a;
}

.super-admin-sidebar__action {
  border: 0;
  background: #64748b;
  color: #fff;
}

.super-admin-sidebar__action--primary {
  background: #ff5c1a;
}

.super-admin-sidebar__logout {
  border: 1px solid rgba(138, 113, 95, 0.25);
  background: rgba(255, 159, 67, 0.12);
  color: #a23d32;
}

.super-admin-main-panel {
  min-height: 100vh;
  margin-left: var(--super-admin-sidebar-width);
}

.super-admin-topbar {
  position: fixed;
  top: 0;
  right: 0;
  left: var(--super-admin-sidebar-width);
  z-index: 300;
  display: flex;
  min-height: 66px;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  box-sizing: border-box;
  background: #eee2d9;
  border-bottom: 1px solid #d8c8bc;
}

.super-admin-hamburger {
  display: grid;
  width: 34px;
  height: 34px;
  padding: 0;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.7);
  color: #334155;
  cursor: pointer;
}

.super-admin-topbar__spacer { flex: 1; }

.super-admin-user-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.45rem 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.58);
  color: #1f2937;
  font-size: 0.82rem;
  font-weight: 600;
}

.super-admin-user-pill__avatar {
  display: grid;
  width: 26px;
  height: 26px;
  place-items: center;
  border-radius: 50%;
  background: #f7b97a;
  color: #1f2937;
  font-size: 0.72rem;
  font-weight: 800;
}

.super-admin-main-panel > .admin-page {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
  min-height: 100vh;
  padding-top: 66px;
  box-sizing: border-box;
}

.super-admin-module-view {
  width: 100%;
  min-width: 0;
  min-height: calc(100vh - 66px);
  padding: 1rem;
  box-sizing: border-box;
  overflow-x: hidden;
  background: #f1e5dc;
}

.super-admin-module-view > :deep(*) {
  max-width: 100%;
  min-width: 0;
}

.super-admin-module-back {
  display: inline-flex;
  align-items: center;
  margin-bottom: 0.75rem;
  padding: 0.55rem 0.9rem;
  border: 1px solid #cbd5df;
  border-radius: 8px;
  color: #334155;
  background: #ffffff;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.super-admin-module-back:hover {
  background: #f8fafc;
}

.super-admin-main-panel :deep(.admin-layout) {
  display: block;
  width: 100%;
  max-width: none !important;
  min-height: calc(100vh - 66px);
  padding: 1rem;
  box-sizing: border-box;
}

.super-admin-main-panel :deep(.admin-layout > .admin-main),
.super-admin-main-panel :deep(.admin-layout > .admin-side),
.super-admin-main-panel :deep(.admin-layout > .admin-left),
.super-admin-main-panel :deep(.admin-layout > .page-header-top) {
  grid-column: auto !important;
  width: 100% !important;
  max-width: none !important;
  min-width: 0;
}

.super-admin-main-panel :deep(.admin-main) {
  width: 100%;
  max-width: none !important;
  min-width: 0;
  margin: 0;
  overflow-x: hidden !important;
}

.super-admin-main-panel .page-header-top {
  width: 100%;
  margin: 0 0 1rem;
  padding: 1rem 0;
  background: transparent;
  border: 0;
  box-shadow: none;
}

.super-admin-dashboard-side {
  display: grid;
  width: 100%;
  min-width: 0;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.super-admin-dashboard-side .panel-block { margin: 0; }

.sidebar-collapsed .super-admin-sidebar {
  transform: translateX(-100%);
  opacity: 0;
  pointer-events: none;
}

.sidebar-collapsed .super-admin-sidebar__resize-handle { display: none; }

.sidebar-collapsed .super-admin-main-panel { margin-left: 0; }

.sidebar-collapsed .super-admin-topbar { left: 0; }

@media (max-width: 900px) {
  .super-admin-shell { --super-admin-sidebar-width: 156px; }
  .super-admin-sidebar { width: var(--super-admin-sidebar-width); }
  .super-admin-main-panel { margin-left: var(--super-admin-sidebar-width); }
  .super-admin-topbar { left: var(--super-admin-sidebar-width); }
  .super-admin-dashboard-side { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 640px) {
  .super-admin-shell { --super-admin-sidebar-width: 156px; }
  .super-admin-sidebar { width: var(--super-admin-sidebar-width); }
  .super-admin-main-panel { margin-left: 0; }
  .super-admin-topbar { left: 0; }
  .super-admin-shell:not(.sidebar-collapsed) .super-admin-sidebar { transform: translateX(0); }
  .super-admin-main-panel > .admin-page { padding-top: 66px; }
  .super-admin-main-panel :deep(.admin-layout) { padding: 0.75rem; }
  .super-admin-dashboard-side { grid-template-columns: 1fr; }
}

.primary-action-btn {
  background: linear-gradient(135deg, #2b8aef, #1a6ed8);
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  margin-top: 0.5rem;
  transition: all 0.2s;
}
.primary-action-btn:hover {
  background: linear-gradient(135deg, #1a6ed8, #1557b0);
}

.secondary-action-btn {
  background: #6c757d;
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  margin-top: 0.5rem;
  transition: all 0.2s;
}
.secondary-action-btn:hover {
  background: #5a6268;
}

.staff-btn--center {
  position: relative;
  width: 100%;
  margin-bottom: 0.5rem;
}

.panel-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
}

.info-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}
.info-input:focus {
  outline: none;
  border-color: #2b8aef;
}

textarea.info-input {
  resize: vertical;
  min-height: 80px;
}

.admin-main-header h1 {
  color: var(--text-dark);
  font-weight: 800;
  font-family: 'Inter', 'Poppins', sans-serif;
  letter-spacing: -0.5px;
  margin-bottom: 8px;
}

/* Header at the top of layout - spans all columns */
.page-header-top {
  grid-column: 1 / -1;
  width: 100%;
  padding: 12px 0;
  background: white;
  border-bottom: 1px solid #F0E9E0;
  box-shadow: 0 2px 4px rgba(16,24,40,0.04);
  margin-bottom: 4px;
  transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

.page-header-top div {
  padding: 0;
}

.page-header-top h1 {
  color: var(--text-dark);
  font-weight: 800;
  font-family: 'Inter', 'Poppins', sans-serif;
  letter-spacing: -0.5px;
  margin: 0 0 8px 0;
  font-size: 1.5rem;
  word-break: break-word;
  transition: color 0.3s ease;
}

.page-header-top p {
  font-size: 0.9rem;
  color: rgba(66,33,11,0.6);
  margin: 4px 0;
  transition: color 0.3s ease;
}

.page-header-top .small-hint {
  font-size: 0.8rem;
  margin-top: 4px;
  transition: color 0.3s ease;
}

.page-header-top .small-hint--error {
  color: #dc2626;
}

@media (max-width: 479px) {
  .page-header-top h1 {
    font-size: 1.2rem;
  }
}

/* Reduce top spacing for side panels so left side panels move up
   and maximize available space at the top on wide screens. */
@media (min-width: 1024px) {
  .admin-side {
    margin-top: 8px !important;
    position: static !important;
    top: auto !important;
  }
}
</style>

