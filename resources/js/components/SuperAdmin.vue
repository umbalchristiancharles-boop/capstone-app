<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT: SIDE PANELS (Super Admin only) -->
        <aside class="admin-side">
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
              <div v-else v-for="att in adminAttendance.slice(0, 10)" :key="att.id" class="table-row">
                <span>{{ att.user_name }}</span>
                <span>{{ att.branch_name || '-' }}</span>
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

        <!-- MIDDLE: MAIN DASHBOARD -->
        <main class="admin-main">
          <header class="admin-main-header">
            <div class="admin-main-header-top">
              <div>
                <h1>{{ panelTitle }}</h1>
                <p>{{ panelDescription }}</p>
                <p v-if="isLoadingDashboard && !isInitialMount" class="small-hint">Loading dashboard…</p>
                <p v-else-if="dashboardError" class="small-hint small-hint--error">{{ dashboardError }}</p>
              </div>

              <div class="range-tabs">
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'today' }" @click="changeRange('today')">Today</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'yesterday' }" @click="changeRange('yesterday')">Yesterday</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisWeek' }" @click="changeRange('thisWeek')">This Week</button>
                <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisMonth' }" @click="changeRange('thisMonth')">This Month</button>
              </div>
            </div>
          </header>

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

        <!-- RIGHT: SUPER ADMIN PROFILE COLUMN -->
        <aside class="admin-profile-column">
          <div v-if="!isProfileLoading" class="admin-card admin-card--stacked">
            <!-- PROFILE PICTURE + NAME + ROLE -->
            <div class="admin-card__header admin-card__header--stacked">
              <label class="admin-avatar admin-avatar--photo avatar-upload" for="avatar-input">
                <img
                  v-if="superAdminProfile.avatarUrl"
                  :src="superAdminProfile.avatarUrl"
                  alt="Profile picture"
                  class="avatar-img"
                />
                <div v-else class="avatar-placeholder">
                  <span class="avatar-initials">SA</span>
                </div>
                <div class="avatar-overlay">
                  <span class="avatar-change-text">Change Photo</span>
                </div>
              </label>

              <div class="admin-header-text admin-admin-header-text--center">
                <div class="admin-label">Account</div>
                <div class="admin-name">
                  {{ superAdminProfile.fullName || 'Super Admin' }}
                </div>
                <div class="admin-role">
                  {{ superAdminProfile.role || 'SUPER_ADMIN' }}
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
                <span class="admin-id-value">&nbsp;{{ superAdminProfile.accountId || 'sa0001' }}</span>
              </div>

              <button class="admin-info-btn admin-info-btn--center" @click="openInfoModal">Info</button>
            </div>

            <div class="admin-card__footer admin-card__footer--stacked">
              <div class="admin-metrics-row">
                <div class="admin-metric">
                  <div class="metric-icon">🏢</div>
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

                <div class="admin-metric">
                  <div class="metric-icon">👤</div>
                  <div class="metric-text">
                    <span class="metric-label">Total Admins:</span>
                    <span class="metric-value">&nbsp;{{ summaryTotals.totalAdmins }}</span>
                  </div>
                </div>
              </div>

              <div class="owner-extra">
                <div class="owner-extra-row">
                  <span class="owner-label">Access Level:</span>
                  <span class="owner-value">System Wide</span>
                </div>
                <div class="owner-extra-row">
                  <span class="owner-label">System Status:</span>
                  <span class="owner-value" style="color: #28a745;">Active</span>
                </div>
              </div>

              <!-- Module Navigation -->
              <div class="admin-actions-row">
                <button class="staff-btn staff-btn--center" @click="openModule('hr')"> HR Staff Management</button>
                <button class="staff-btn staff-btn--center" @click="openModule('finance')">Finance</button>
                <button class="staff-btn staff-btn--center" @click="openModule('cashier')">Cashier</button>
              </div>

              <div class="admin-actions-row">
                <button class="staff-btn staff-btn--center" @click="openModule('logistics')">Logistics</button>
              </div>

              <div class="admin-actions-row">
                <button class="staff-btn staff-btn--center" @click="openModule('supplier')">Supplier Management</button>
              </div>

              <div class="admin-actions-row">
                <button class="staff-btn staff-btn--center" @click="openModule('procurement')">Procurement</button>
              </div>

              <div class="admin-actions-row">
                <button class="staff-btn staff-btn--center" @click="ownerAddBranches">OwnerAddBranches</button>
              </div>
              <div class="admin-actions-row">
                <button class="primary-action-btn" @click="showAnnouncement = true">Send Announcement</button>
                <button class="secondary-action-btn" @click="showTerms = true">📄 Update Terms</button>
              </div>

              <div class="admin-actions-row">
                <button class="logout-btn logout-btn--center" @click.prevent="askLogout">Logout</button>
              </div>
            </div>
          </div>
        </aside>
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
          <div class="logout-confirm-box">
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
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'

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

const panelTitle = computed(() => 'Chikin Tayo Super Admin Panel')
const panelDescription = computed(() => 'Full system access - manage all modules, branches, and system settings.')

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
  switch (name) {
    case 'hr':
      sessionStorage.setItem('forceHrReload', '1')
      window.location.href = '/super-admin/hr'
      return
    case 'finance': return router.push('/super-admin/finance')
    case 'cashier': return router.push('/super-admin/cashier')
    case 'logistics': return router.push('/super-admin/logistics')
    case 'supplier': return router.push('/super-admin/supplier')
    case 'procurement': return router.push('/super-admin/procurement')
    default: return
  }
}

function ownerAddBranches() {
  // Navigate to the owner add branches page (route should exist or be implemented separately)
  // Navigate to the main-branch branches page but include a query marker
  // so the branches page can return to the Super Admin panel when needed.
  // Use dedicated super-admin route so the branches page can detect Super Admin
  router.push({ path: '/super-admin/branches', query: { from: 'superadmin' } })
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
    window.location.replace('/staff-landing')
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
  isInitialMount.value = false
  superAdminProfile.value = { fullName: '', role: 'SUPER_ADMIN', email: '', contact: '', accountId: '', avatarUrl: '' }
  await loadProfile()
  await loadDashboard(activeRange.value)
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
  width: 100%;
  margin-bottom: 0.5rem;
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
</style>

