<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT: HR PROFILE COLUMN -->
        <aside class="admin-profile-column">
          <div class="admin-card admin-card--stacked">
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

              <div class="admin-qr-block admin-qr-block--center">
                <div class="qr-placeholder">QR</div>
              </div>
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
                  👥 Employee Management
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
                <h1>Chikin Tayo HR Panel</h1>
                <p>
                  Manage employees, attendance, and HR records across all branches.
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

  try {
    hrDashboardTotals.value = {
      newEmployees: 5,
      pendingLeaves: 2,
      absentToday: 3,
      openPositions: 2
    }

    hrSummaryTotals.value = {
      totalEmployees: 45,
      totalBranches: 8
    }

    recentHires.value = [
      { id: 1, name: 'Juan Dela Cruz', position: 'Cashier', branch: 'Dasmariñas, Cavite', hiredAt: 'Feb 1' },
      { id: 2, name: 'Maria Santos', position: 'Cashier', branch: 'General Trias, Cavite', hiredAt: 'Jan 10' },
      { id: 3, name: 'Pedro Reyes', position: 'Rider', branch: 'Manila Branch', hiredAt: 'Jan 3' },
      { id: 4, name: 'Ana Cruz', position: 'Shift Leader', branch: 'Dasmariñas, Cavite', hiredAt: 'Dec 28' }
    ]

    attendanceAlerts.value = [
      {
        id: 1,
        title: 'Multiple late arrivals',
        meta: '3 lates this week - Dasmariñas, Cavite',
        badgeClass: 'badge--warning',
        badgeLabel: 'ATTENTION'
      },
      {
        id: 2,
        title: 'No time-out recorded',
        meta: '1 staff - General Trias, Cavite',
        badgeClass: 'badge--info',
        badgeLabel: 'CHECK'
      }
    ]

    pendingLeaves.value = [
      { id: 1, employee: 'Maria Santos', range: 'Jan 30 - Jan 31' },
      { id: 2, employee: 'Mark Test', range: 'Feb 2 - Feb 3' }
    ]

    openPositions.value = [
      { id: 1, title: 'Cook - Dasmariñas', applicants: 4 },
      { id: 2, title: 'Cashier - Manila', applicants: 2 }
    ]

    hrActivity.value = [
      { id: 1, message: 'New hire added: Juan Dela Cruz', meta: '5 mins ago' },
      { id: 2, message: 'Leave request approved: Maria Santos', meta: '30 mins ago' },
      { id: 3, message: 'Job posting updated: Cashier - Manila', meta: '1 hour ago' }
    ]
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
  router.push('/hr/employees')
}

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return

  if (!window.confirm('Are you sure you want to change your profile picture?')) return

  const reader = new FileReader()
  const dataUrl = await new Promise((resolve, reject) => {
    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.onload = () => resolve(reader.result)
    reader.readAsDataURL(file)
  })

  sessionStorage.setItem('pendingAvatar', JSON.stringify({
    dataUrl,
    filename: file.name,
    panel: 'hr'
  }))
  window.location.reload()
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
  // load dashboard (don't await so profile fetch can run immediately)
  loadHrDashboard()

  // fetch profile early so the left column shows the logged-in account
  try {
    axios.get('/api/owner-profile', { withCredentials: true })
      .then(res => {
        if (res.data && res.data.ok && res.data.user) {
          hrProfile.value = normalizeUser(res.data.user)
        }
      })
      .catch(err => {
        // If 401, user session expired - redirect to login
        if (err.response?.status === 401) {
          router.push('/admin-login')
        }
      })
  } catch (e) {}

  // If user chose an avatar before reload, perform pending upload (non-blocking)
  ;(async () => {
    try {
      const pendingRaw = sessionStorage.getItem('pendingAvatar')
      if (!pendingRaw) return
      const pending = JSON.parse(pendingRaw)
      if (!pending || pending.panel !== 'hr') return

      function dataURLtoBlob(dataurl) {
        const arr = dataurl.split(',')
        const mime = arr[0].match(/:(.*?);/)[1]
        const bstr = atob(arr[1])
        let n = bstr.length
        const u8arr = new Uint8Array(n)
        while (n--) { u8arr[n] = bstr.charCodeAt(n) }
        return new Blob([u8arr], { type: mime })
      }

      const blob = dataURLtoBlob(pending.dataUrl)
      const file = new File([blob], pending.filename, { type: blob.type })
      const formData = new FormData()
      formData.append('avatar', file)

      try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
      await new Promise(resolve => setTimeout(resolve, 50))

      try {
        function getCookie(name) { const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return m ? m[2] : null }
        const xsrf = getCookie('XSRF-TOKEN')
        if (xsrf) {
          try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (_) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
          try { formData.append('_token', decodeURIComponent(xsrf)) } catch (_) { formData.append('_token', xsrf) }
        }
      } catch (e) {}

      try { console.debug('AUTO UPLOAD CSRF: document.cookie=', document.cookie) } catch (_) {}
      try { console.debug('AUTO UPLOAD CSRF: axios.defaults.headers.common["X-XSRF-TOKEN"]=', axios.defaults.headers.common['X-XSRF-TOKEN']) } catch (_) {}

      try {
        const res = await axios.post('/api/upload-avatar', formData, { headers: { 'Content-Type': 'multipart/form-data' }, withCredentials: true })
        try {
          const profileRes = await axios.get('/api/owner-profile', { withCredentials: true })
          if (profileRes.data.ok && profileRes.data.user) {
            hrProfile.value = normalizeUser(profileRes.data.user)
          } else {
            let url = res.data.avatarUrl
            url = url.replace(/\?t=\d+$/, '')
            hrProfile.value.avatarUrl = url + '?t=' + Date.now()
          }
        } catch (e) {
          let url = res.data.avatarUrl
          url = url.replace(/\?t=\d+$/, '')
          hrProfile.value.avatarUrl = url + '?t=' + Date.now()
        }

        sessionStorage.removeItem('pendingAvatar')
      } catch (e) {
        console.error('Auto-upload failed:', e)
      }
    } catch (e) {
      console.error('Auto-upload setup failed:', e)
    }
  })()
})
</script>
