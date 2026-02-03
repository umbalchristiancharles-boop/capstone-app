<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT: HR PROFILE COLUMN -->
        <aside class="admin-profile-column">
          <div v-if="!isProfileLoading" class="admin-card admin-card--stacked">
            <!-- PROFILE PICTURE + NAME + ROLE -->
            <div class="admin-card__header admin-card__header--stacked">
              <!-- clickable avatar -->
              <label class="admin-avatar admin-avatar--photo avatar-upload" for="hr-avatar-input">
                <img
                  v-if="hrProfile.avatarUrl"
                  :src="hrProfile.avatarUrl"
                  alt="Profile picture"
                  class="avatar-img"
                />
                <div v-else class="avatar-placeholder">
                  <span class="avatar-initials">HR</span>
                </div>
                <div class="avatar-overlay">
                  <span class="avatar-change-text">Change Photo</span>
                </div>
              </label>

              <div class="admin-header-text admin-header-text--center">
                <div class="admin-label">Account</div>
                <div class="admin-name">
                  {{ hrProfile.fullName || 'HR Administrator' }}
                </div>
                <div class="admin-role">
                  {{ hrProfile.role || 'HR DEPARTMENT' }}
                </div>
              </div>

              <!-- hidden file input -->
              <input
                id="hr-avatar-input"
                type="file"
                accept="image/*"
                @change="onAvatarChange"
                style="display: none"
              />
            </div>

            <!-- ACCOUNT ID + INFO BUTTON -->
            <div class="admin-card__body admin-card__body--stacked">
              <div class="admin-id-block admin-id-block--center">
                <span class="admin-id-label">HR I.D:</span>
                <span class="admin-id-value">
                  {{ hrProfile.accountId || 'HR-0001' }}
                </span>
              </div>

              <button
                class="admin-info-btn admin-info-btn--center"
                @click="openInfoModal"
              >
                Info
              </button>
            </div>

            <!-- METRICS + EXTRA + BUTTONS -->
            <div class="admin-card__footer admin-card__footer--stacked">
              <div class="admin-metrics-row">
                <div class="admin-metric">
                  <div class="metric-icon">👥</div>
                  <div class="metric-text">
                    <span class="metric-label">Total Employees</span>
                    <span class="metric-value">
                      {{ hrSummaryTotals.totalEmployees }}
                    </span>
                  </div>
                </div>

                <div class="admin-metric">
                  <div class="metric-icon">🏬</div>
                  <div class="metric-text">
                    <span class="metric-label">Covered Branches</span>
                    <span class="metric-value">
                      {{ hrSummaryTotals.totalBranches }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="owner-extra">
                <div class="owner-extra-row">
                  <span class="owner-label">Access Level:</span>
                  <span class="owner-value">HR Management</span>
                </div>
                <div class="owner-extra-row">
                  <span class="owner-label">Department:</span>
                  <span class="owner-value">
                    {{ hrProfile.department || 'Human Resources' }}
                  </span>
                </div>
              </div>

              <div class="admin-actions-row">
                <!-- Employee Management Button -->
                <button
                  class="staff-btn staff-btn--center"
                  @click="goToEmployeeManagement"
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

        <!-- MIDDLE: HR DASHBOARD -->
        <main class="admin-main">
          <header class="admin-main-header">
            <div class="admin-main-header-top">
              <div>
                <h1>{{ panelTitle }}</h1>
                <p>
                  {{ panelDescription }}
                </p>
                <p v-if="isLoadingDashboard" class="small-hint">
                  Loading HR dashboard…
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
                  :class="{ 'range-tab--active': activeRange === 'thisWeek' }"
                  @click="changeRange('thisWeek')"
                >
                  This Week
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
                  :class="{ 'range-tab--active': activeRange === 'thisYear' }"
                  @click="changeRange('thisYear')"
                >
                  This Year
                </button>
              </div>
            </div>
          </header>

          <!-- Overview cards (HR metrics) -->
          <section class="overview-grid">
            <div class="overview-card">
              <span class="overview-label">
                Employees<span v-if="activeRangeLabel"> ({{ activeRangeLabel }})</span>:
              </span>
              <span class="overview-value">
                {{ hrDashboardTotals.newEmployees }}
              </span>
            </div>
            <div class="overview-card">
              <span class="overview-label">Pending Leave Requests:</span>
              <span class="overview-value">
                {{ hrDashboardTotals.pendingLeaves }}
              </span>
            </div>
            <div class="overview-card">
              <span class="overview-label">Absent Today:</span>
              <span class="overview-value">
                {{ hrDashboardTotals.absentToday }}
              </span>
            </div>
            <div class="overview-card">
              <span class="overview-label">Open Positions:</span>
              <span class="overview-value">
                {{ hrDashboardTotals.openPositions }}
              </span>
            </div>
          </section>

          <!-- Recent hires table -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Recent Hires</h2>
              <button
                class="panel-action"
                @click="showAllHires = !showAllHires"
              >
                {{ showAllHires ? 'Show less' : 'View all' }}
              </button>
            </div>

            <div class="panel-body panel-body--table">
              <div class="table-header">
                <span>Employee</span>
                <span>Position</span>
                <span>Branch</span>
                <span>Hired</span>
              </div>

              <div
                v-if="recentHires.length === 0"
                class="table-row"
              >
                <span>No hires for this range.</span>
                <span></span>
                <span></span>
                <span></span>
              </div>

              <div
                v-else
                v-for="emp in visibleHires"
                :key="emp.id"
                class="table-row"
              >
                <span>{{ emp.name }}</span>
                <span>{{ emp.position }}</span>
                <span>{{ emp.branch }}</span>
                <span>{{ emp.hiredAt }}</span>
              </div>
            </div>
          </section>

          <!-- Attendance alerts -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Attendance Alerts</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="attendanceAlerts.length === 0"
                class="queue-item"
              >
                <div class="queue-title">No attendance issues detected.</div>
              </div>
              <div
                v-else
                v-for="item in attendanceAlerts"
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
          <!-- Leave requests -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Pending Leave Requests</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="pendingLeaves.length === 0"
                class="side-item"
              >
                <span>No pending leave requests.</span>
              </div>
              <div
                v-else
                v-for="leave in pendingLeaves"
                :key="leave.id"
                class="side-item"
              >
                <span>{{ leave.employee }}</span>
                <span class="side-value">{{ leave.range }}</span>
              </div>
            </div>
          </section>

          <!-- Open positions / applicants -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>Open Positions</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="openPositions.length === 0"
                class="side-item"
              >
                <span>No active job postings.</span>
              </div>
              <div
                v-else
                v-for="pos in openPositions"
                :key="pos.id"
                class="side-item"
              >
                <span>{{ pos.title }}</span>
                <span class="side-value">{{ pos.applicants }} applicants</span>
              </div>
            </div>
          </section>

          <!-- HR activity log -->
          <section class="panel-block">
            <div class="panel-header">
              <h2>HR Activity</h2>
            </div>
            <div class="panel-body panel-body--list">
              <div
                v-if="hrActivity.length === 0"
                class="activity-item"
              >
                <span class="activity-main">
                  No HR activity logged for this range.
                </span>
              </div>
              <div
                v-else
                v-for="log in hrActivity"
                :key="log.id"
                class="activity-item"
              >
                <span class="activity-main">
                  {{ log.message }}
                </span>
                <span class="activity-meta">{{ log.meta }}</span>
              </div>
            </div>
          </section>
        </aside>
      </section>

      <!-- Logout Confirmation Modal (admin style) -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
          <div class="logout-confirm-box">
            <h3>Logout from HR Panel?</h3>
            <p>This will end your current session for Chikin Tayo HR.</p>
            <div class="logout-actions">
              <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
              <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Info Modal (admin style) -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Profile Information</h3>
            <p class="info-sub">Personal details for this administrator can be updated from this panel.</p>

            <div class="info-grid">
              <div class="info-row">
                <span class="info-label">Full name</span>
                <span class="info-value" v-if="!isEditingInfo">{{ hrProfile.fullName }}</span>
                <input v-else v-model="hrProfile.fullName" class="info-input" type="text" />
              </div>

              <div class="info-row">
                <span class="info-label">Role</span>
                <span class="info-value">{{ hrProfile.role }}</span>
              </div>

              <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value" v-if="!isEditingInfo">{{ hrProfile.email }}</span>
                <input v-else v-model="hrProfile.email" class="info-input" type="email" />
              </div>

              <div class="info-row">
                <span class="info-label">Contact</span>
                <span class="info-value" v-if="!isEditingInfo">{{ hrProfile.contact }}</span>
                <input v-else v-model="hrProfile.contact" class="info-input" type="text" />
              </div>

              <div class="info-row">
                <span class="info-label">Department</span>
                <span class="info-value">{{ hrProfile.department }}</span>
              </div>
            </div>

            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>
              <button class="btn-primary" @click="isEditingInfo ? saveHrInfo() : (isEditingInfo = true)">{{ isEditingInfo ? 'Save changes' : 'Edit information' }}</button>
            </div>
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
const showAllHires = ref(false)
const isLoadingDashboard = ref(false)
const dashboardError = ref('')
const showInfoModal = ref(false)
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const isEditingInfo = ref(false)
const isInitialMount = ref(true)
const isProfileLoading = ref(true)

const panelTitle = computed(() => {
  return 'Chikin Tayo HR Panel'
})

const panelDescription = computed(() => {
  return 'Manage employees, attendance, and HR records across all branches.'
})

const hrDashboardTotals = ref({
  newEmployees: 0,
  pendingLeaves: 0,
  absentToday: 0,
  openPositions: 0
})

const hrSummaryTotals = ref({
  totalEmployees: 0,
  totalBranches: 0
})

const recentHires = ref([])
const attendanceAlerts = ref([])
const pendingLeaves = ref([])
const openPositions = ref([])
const hrActivity = ref([])

const hrProfile = ref({
  fullName: '',
  role: 'HR DEPARTMENT',
  email: '',
  contact: '',
  department: 'Human Resources',
  accountId: '',
  avatarUrl: ''
})

const visibleHires = computed(() => {
  if (!recentHires.value || recentHires.value.length === 0) return []
  return showAllHires.value ? recentHires.value : recentHires.value.slice(0, 3)
})

const activeRangeLabel = computed(() => {
  switch (activeRange.value) {
    case 'today': return 'Today'
    case 'thisWeek': return 'This Week'
    case 'thisMonth': return 'This Month'
    case 'thisYear': return 'This Year'
    default: return ''
  }
})

function normalizeUser(u) {
  if (!u) return { fullName: '', role: '', email: '', contact: '', department: 'Human Resources', accountId: '', avatarUrl: '' }
  return {
    fullName: u.fullName ?? u.full_name ?? '',
    role: u.role ?? '',
    email: u.email ?? '',
    contact: u.contact ?? u.phone_number ?? '',
    department: u.department ?? 'Human Resources',
    accountId: u.accountId ?? u.account_id ?? '',
    avatarUrl: u.avatarUrl ?? u.avatar_url ?? ''
  }
}

async function loadHrDashboard(range = 'today') {
  isLoadingDashboard.value = true
  dashboardError.value = ''

  hrDashboardTotals.value = {
    newEmployees: 0,
    pendingLeaves: 0,
    absentToday: 0,
    openPositions: 0
  }

  hrSummaryTotals.value = {
    totalEmployees: 0,
    totalBranches: 0
  }

  recentHires.value = []
  attendanceAlerts.value = []
  pendingLeaves.value = []
  openPositions.value = []
  hrActivity.value = []

  try {
    const [res, ownerRes] = await Promise.all([
      axios.get('/api/admin/dashboard', {
        params: { range },
        withCredentials: true,
      }),
      axios.get('/api/owner-dashboard', {
        withCredentials: true,
      })
    ])

    if (res.data) {
      const ownerSummary = ownerRes?.data?.summary

      if (ownerSummary) {
        const branchEmployees = ownerSummary.branchEmployees
        hrSummaryTotals.value = {
          totalEmployees: branchEmployees ?? ownerSummary.totalEmployees ?? 0,
          totalBranches: ownerSummary.totalBranches ?? 0
        }
      }

      if (res.data.success) {
        hrDashboardTotals.value = {
          newEmployees: res.data.stats?.newEmployees || 0,
          pendingLeaves: res.data.stats?.pendingLeaves || 0,
          absentToday: res.data.stats?.absentToday || 0,
          openPositions: res.data.stats?.openPositions || 0
        }

        recentHires.value = res.data.recentHires ?? []
        attendanceAlerts.value = res.data.attendanceAlerts ?? []
        pendingLeaves.value = res.data.pendingLeaves ?? []
        openPositions.value = res.data.openPositions ?? []
        hrActivity.value = res.data.hrActivity ?? []
      } else {
        // Admin dashboard response structure
        if (!ownerSummary) {
          hrSummaryTotals.value = {
            totalEmployees: res.data.staff_count || 0,
            totalBranches: res.data.branches_count || 0
          }
        }
      }
    }
  } catch (e) {
    dashboardError.value = 'Error loading HR dashboard.'
  } finally {
    isLoadingDashboard.value = false
  }
}

async function changeRange(range) {
  if (activeRange.value === range) return
  activeRange.value = range
  await loadHrDashboard(range)
}

function openInfoModal() {
  showInfoModal.value = true
  isEditingInfo.value = false

  // load actual profile from server if available
  try {
    axios.get('/api/owner-profile', { withCredentials: true })
      .then(res => {
        if (res.data && res.data.ok && res.data.user) {
          hrProfile.value = normalizeUser(res.data.user)
        } else {
          // fallback to current value or sensible defaults
          hrProfile.value = Object.assign({}, hrProfile.value, {
            fullName: hrProfile.value.fullName || 'HR Administrator',
            role: hrProfile.value.role || 'HR DEPARTMENT'
          })
        }
      })
      .catch(() => {
        hrProfile.value = Object.assign({}, hrProfile.value, {
          fullName: hrProfile.value.fullName || 'HR Administrator',
          role: hrProfile.value.role || 'HR DEPARTMENT'
        })
      })
  } catch (e) {
    hrProfile.value = Object.assign({}, hrProfile.value, {
      fullName: hrProfile.value.fullName || 'HR Administrator',
      role: hrProfile.value.role || 'HR DEPARTMENT'
    })
  }
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false
  } else {
    showInfoModal.value = false
  }
}

async function saveHrInfo() {
  const payload = {
    fullName: hrProfile.value.fullName,
    email: hrProfile.value.email,
    contact: hrProfile.value.contact,
  }

  async function doPut() {
    return axios.put('/api/owner-profile', payload, { withCredentials: true })
  }

  try {
    try {
      const res = await doPut()
      if (res.data && res.data.ok) {
        if (res.data.user) hrProfile.value = normalizeUser(res.data.user)
        isEditingInfo.value = false
        showInfoModal.value = false
        return
      }
    } catch (err) {
      // if CSRF/token expired (Laravel 419) attempt to refresh CSRF cookie and retry
      const status = err && err.response && err.response.status
      if (status === 419) {
        try {
          await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

          // try to set X-XSRF-TOKEN header from cookie if present
          try {
            const m = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'))
            const xsrf = m ? m[2] : null
            if (xsrf) {
              try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (_) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
            }
          } catch (_) {}

          const retry = await doPut()
          if (retry.data && retry.data.ok) {
            if (retry.data.user) hrProfile.value = normalizeUser(retry.data.user)
            isEditingInfo.value = false
            showInfoModal.value = false
            return
          }
        } catch (e2) {
          console.error('Retry after CSRF cookie failed:', e2)
        }
      }
      throw err
    }

    // fallback close modal if response not ok
    isEditingInfo.value = false
    showInfoModal.value = false
  } catch (e) {
    console.error('Failed to save profile info:', e)
  }
}

function goToEmployeeManagement() {
  router.push('/admin/staff-management')
}

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (!window.confirm('Are you sure you want to change your profile picture?')) return

  try {
    const formData = new FormData()
    formData.append('avatar', file)

    try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
    await new Promise(resolve => setTimeout(resolve, 50))

    try {
      function getCookie(name) { const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return m ? m[2] : null }
        const xsrf = getCookie('XSRF-TOKEN')
      if (xsrf) {
        try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (_) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
      }
    } catch (e) {}

      // Fallback: fetch CSRF token explicitly if cookie is missing
      try {
        function getCookie(name) { const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return m ? m[2] : null }
        const xsrf = getCookie('XSRF-TOKEN')
        if (!xsrf) {
          const tokenRes = await axios.get('/api/csrf-token', { withCredentials: true })
          const token = tokenRes?.data?.token
          if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token
            formData.append('_token', token)
          }
        }
      } catch (e) {}

    const res = await axios.post('/api/upload-avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      withCredentials: true
    })

    if (res.data && res.data.ok) {
      hrProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
      alert('Profile picture updated successfully!')
    }
  } catch (e) {
    console.error('Avatar upload failed:', e)
    alert('Failed to upload profile picture. Please try again.')
  }
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true

  try {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    // use server logout to invalidate session cookie
    window.location.replace('/logout')
  } catch (e) {
    console.error('Logout failed:', e)
    try { window.location.href = '/login' } catch (_) {}
  }
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

// Auto-upload pending avatar after reload
onMounted(async () => {
  // Mark initial mount complete first
  isInitialMount.value = false

  // Reset profile to avoid showing stale data
  hrProfile.value = {
    fullName: '',
    role: 'HR',
    email: '',
    contact: '',
    department: 'Human Resources',
    accountId: '',
    avatarUrl: ''
  }

  // Fetch profile
  try {
    const res = await axios.get('/api/owner-profile', { withCredentials: true })
    if (res.data && res.data.ok && res.data.user) {
      hrProfile.value = normalizeUser(res.data.user)
    }
    isProfileLoading.value = false
  } catch (e) {
    if (e.response?.status === 401) {
      router.push('/admin-login')
      return
    }
    isProfileLoading.value = false
  }

  // Load dashboard
  loadHrDashboard(activeRange.value)

  // Non-blocking avatar upload logic (removed pending avatar)
  ;(async () => {
    // Placeholder for future async operations
    return
  })()
})
</script>
