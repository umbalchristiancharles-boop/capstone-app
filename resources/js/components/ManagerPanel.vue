<template>
  <div class="admin-page">
    <section class="admin-layout">
      <!-- LEFT:  MANAGER PROFILE COLUMN -->
      <aside class="admin-profile-column">
        <div class="admin-card admin-card--stacked">
          <!-- PROFILE PICTURE + NAME + ROLE -->
          <div class="admin-card__header admin-card__header--stacked">
            <!-- clickable avatar -->
            <label class="admin-avatar admin-avatar--photo avatar-upload" for="avatar-input">
              <img
                v-if="managerProfile.avatarUrl"
                :src="managerProfile.avatarUrl"
                alt="Profile picture"
                class="avatar-img"
              />
              <div v-else class="avatar-placeholder">
                <span class="avatar-initials">BM</span>
              </div>
              <div class="avatar-overlay">
                <span class="avatar-change-text">Change Photo</span>
              </div>
            </label>

            <div class="admin-header-text admin-header-text--center">
              <div class="admin-label">Account</div>
              <div class="admin-name">
                {{ managerProfile.fullName || 'Branch Manager' }}
              </div>
              <div class="admin-role">
                {{ managerProfile.role || 'BRANCH_MANAGER' }}
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
                &nbsp;{{ managerProfile.accountId || 'bm001' }}
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
              <div class="admin-metric">
                <div class="metric-icon">👥</div>
                <div class="metric-text">
                  <span class="metric-label">Branch Employees:</span>
                  <span class="metric-value">
                    &nbsp;{{ summaryTotals.totalEmployees }}
                  </span>
                </div>
              </div>

              <div class="admin-metric">
                <div class="metric-icon">👨‍🍳</div>
                <div class="metric-text">
                  <span class="metric-label">Active Staff:</span>
                  <span class="metric-value">
                    &nbsp;{{ summaryTotals.activeStaff }}
                  </span>
                </div>
              </div>
            </div>

            <div class="owner-extra">
              <div class="owner-extra-row">
                <span class="owner-label">Access Level:</span>
                <span class="owner-value">Branch Control</span>
              </div>
              <div class="owner-extra-row">
                <span class="owner-label">Assigned Branch:</span>
                <span class="owner-value">
                  {{ typeof managerProfile.branch === 'object' && managerProfile.branch.name ? managerProfile.branch.name : (managerProfile.branch || 'Not assigned') }}
                </span>
              </div>
            </div>

            <div class="admin-actions-row">
              <!-- Staff Management Button -->
              <button
                class="staff-btn staff-btn--center"
                @click="router.push('/manager/staff')"
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
              <h1>Branch Manager Dashboard</h1>
              <p>
                Monitor your branch orders, staff activity, and performance metrics.
              </p>

              <!-- ATTENDANCE STATUS + CLOCK IN/OUT -->
              <div class="attendance-status" style="margin: 15px 0; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                  <div>
                    <span style="font-weight: bold; color: white;">Status:</span>
                    <span style="color: #fff; margin-left: 10px;">
                      {{ attendanceStatus.clocked_in ? 'Clocked In' : 'Not Clocked In' }}
                    </span>
                    <span v-if="attendanceStatus.time_in" style="color: #ddd; margin-left: 10px;">
                      ({{ attendanceStatus.time_in }})
                    </span>
                  </div>
                  <button
                    v-if="!attendanceStatus.clocked_in"
                    @click="clockIn"
                    :disabled="isClockingInOut"
                    style="padding: 8px 16px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;"
                  >
                    {{ isClockingInOut ? 'Processing...' : 'Clock In' }}
                  </button>
                  <button
                    v-if="attendanceStatus.clocked_in && !attendanceStatus.clocked_out"
                    @click="clockOut"
                    :disabled="isClockingInOut"
                    style="padding: 8px 16px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;"
                  >
                    {{ isClockingInOut ? 'Processing...' : 'Clock Out' }}
                  </button>
                </div>
              </div>

              <p v-if="isLoadingDashboard" class="small-hint">
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
                :class="{ 'range-tab--active':  activeRange === 'thisWeek' }"
                @click="changeRange('thisWeek')"
              >
                This Week
              </button>
              <button
                class="range-tab"
                :class="{ 'range-tab--active':  activeRange === 'lastWeek' }"
                @click="changeRange('lastWeek')"
              >
                Last Week
              </button>
              <button
                class="range-tab"
                :class="{ 'range-tab--active':  activeRange === 'thisMonth' }"
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
              Orders<span v-if="activeRange === 'today'"> Today</span>:
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

        <!-- My Attendance History -->
        <section class="panel-block">
          <div class="panel-header">
            <h2>Attendance History</h2>
          </div>
          <div class="panel-body panel-body--table">
            <div class="table-header">
              <span>Date</span>
              <span>Time In</span>
              <span>Time Out</span>
              <span>Hours Worked</span>
              <span>Status</span>
            </div>

            <div
              v-if="attendanceHistory.length === 0"
              class="table-row"
            >
              <span>No attendance records.</span>
              <span></span>
              <span></span>
              <span></span>
              <span></span>
            </div>

            <div
              v-else
              v-for="att in attendanceHistory"
              :key="att.id"
              class="table-row"
            >
              <span>{{ att.date }}</span>
              <span>{{ att.time_in || '-' }}</span>
              <span>{{ att.time_out || '-' }}</span>
              <span>{{ att.hours_worked || '-' }}</span>
              <span>
                <span
                  class="badge"
                  :class="{
                    'badge--success': att.status === 'present',
                    'badge--warning': att.status === 'late',
                    'badge--info': att.status === 'absent'
                  }"
                >
                  {{ att.status }}
                </span>
              </span>
            </div>
          </div>
        </section>

        <!-- Attendance Monitoring Table (Branch Staff) -->
        <section class="panel-block">
          <div class="panel-header">
            <h2>Branch Staff Attendance</h2>
          </div>
          <div class="panel-body panel-body--table">
            <div class="table-header">
              <span>Staff Name</span>
              <span>Time In</span>
              <span>Time Out</span>
              <span>Hours Worked</span>
              <span>Status</span>
            </div>

            <div
              v-if="branchAttendance.length === 0"
              class="table-row"
            >
              <span>No attendance records for today.</span>
              <span></span>
              <span></span>
              <span></span>
              <span></span>
            </div>

            <div
              v-else
              v-for="att in branchAttendance"
              :key="att.id"
              class="table-row"
            >
              <span>{{ att.user_name }}</span>
              <span>{{ att.time_in || '-' }}</span>
              <span>{{ att.time_out || '-' }}</span>
              <span>{{ att.hours_worked ? (att.hours_worked / 60).toFixed(2) : '-' }}</span>
              <span>
                <span
                  class="badge"
                  :class="{
                    'badge--success': att.status === 'present',
                    'badge--warning': att.status === 'late',
                    'badge--info': att.status === 'absent'
                  }"
                >
                  {{ att.status }}
                </span>
              </span>
            </div>
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

          <!-- container na may fixed height + scroll -->
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
              <div class="queue-title">No items in production. </div>
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
              <span>All items above minimum stock. </span>
            </div>
            <div
              v-else
              v-for="item in lowStockItems"
              :key="item.id"
              class="side-item side-item--alert"
            >
              <span>{{ item.name }}</span>
              <span class="side-value">
                {{ item.qty }} {{ item.unit }} left
              </span>
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
              v-if="staffActivity.length === 0"
              class="activity-item"
            >
              <span class="activity-main">
                No staff activity logged for this range.
              </span>
            </div>
            <div
              v-else
              v-for="log in staffActivity"
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

        <!-- Calendar -->
        <section class="panel-block">
          <div class="panel-header">
            <h2>Calendar</h2>
          </div>
          <div class="panel-body panel-body--list">
            <div style="display: flex; gap: 8px; margin-bottom: 10px;">
              <select
                v-model.number="selectedMonth"
                @change="updateCalendarDate"
                style="flex: 1; padding: 6px 8px; border-radius: 6px; border: none;"
              >
                <option v-for="(m, idx) in monthOptions" :key="m" :value="idx">
                  {{ m }}
                </option>
              </select>
              <select
                v-model.number="selectedYear"
                @change="updateCalendarDate"
                style="width: 110px; padding: 6px 8px; border-radius: 6px; border: none;"
              >
                <option v-for="y in yearOptions" :key="y" :value="y">
                  {{ y }}
                </option>
              </select>
            </div>
            <div style="font-weight: 700; margin-bottom: 8px; color: #fff;">
              {{ monthLabel }}
            </div>
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 6px; color: rgba(255,255,255,0.8); font-size: 12px;">
              <div v-for="day in weekdays" :key="day" style="text-align: center; font-weight: 600;">
                {{ day }}
              </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
              <div
                v-for="(day, idx) in calendarDays"
                :key="idx"
                :style="{
                  textAlign: 'center',
                  padding: '6px 0',
                  borderRadius: '6px',
                  background: day && isToday(day) ? 'rgba(255,255,255,0.25)' : 'rgba(255,255,255,0.08)',
                  color: day ? '#fff' : 'transparent'
                }"
              >
                {{ day || '' }}
              </div>
            </div>
          </div>
        </section>
      </aside>
    </section>

    <!-- INFO MODAL -->
    <transition name="fade">
      <div v-if="showInfoModal" class="info-backdrop">
        <div class="info-modal">
          <h3>Manager Information</h3>
          <p class="info-sub">
            Personal details for this branch manager can be updated from this
            panel.
          </p>

          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">Full name</span>
              <span class="info-value" v-if="!isEditingInfo">
                {{ managerProfile.fullName }}
              </span>
              <input
                v-else
                v-model="managerProfile.fullName"
                class="info-input"
                type="text"
              />
            </div>

            <div class="info-row">
              <span class="info-label">Role</span>
              <span class="info-value">{{ managerProfile.role }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Email</span>
              <span class="info-value" v-if="!isEditingInfo">
                {{ managerProfile.email }}
              </span>
              <input
                v-else
                v-model="managerProfile.email"
                class="info-input"
                type="email"
              />
            </div>

            <div class="info-row">
              <span class="info-label">Contact</span>
              <span class="info-value" v-if="!isEditingInfo">
                {{ managerProfile.contact }}
              </span>
              <input
                v-else
                v-model="managerProfile.contact"
                class="info-input"
                type="text"
              />
            </div>

            <div class="info-row">
              <span class="info-label">Branch</span>
              <span class="info-value">
                {{ typeof managerProfile.branch === 'object' && managerProfile.branch.name ? managerProfile.branch.name : (managerProfile.branch || 'Not assigned') }}
              </span>
            </div>
          </div>

          <div class="info-actions">
            <button class="btn-outline" @click="handleInfoClose">
              {{ isEditingInfo ? 'Cancel' : 'Close' }}
            </button>
            <button
              class="btn-primary"
              @click="isEditingInfo ? saveManagerInfo() : (isEditingInfo = true)"
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
          <h3>Logout from Manager Dashboard?</h3>
          <p>This will end your current session for the Branch Manager Dashboard.</p>
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
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'

const router = useRouter()

// Setup CSRF token for axios
axios.defaults.withCredentials = true
axios.defaults.xsrfCookieName = 'XSRF-TOKEN'
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN'

const getCookie = (name) => {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) return parts.pop().split(';').shift()
  return null
}

const setupCsrfToken = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const xsrf = getCookie('XSRF-TOKEN')
    if (xsrf) {
      const decoded = decodeURIComponent(xsrf)
      axios.defaults.headers.common['X-XSRF-TOKEN'] = decoded
      axios.defaults.headers.common['X-CSRF-TOKEN'] = decoded
      return
    }
  } catch (e) {
    // fallback below
  }

  try {
    const res = await axios.get('/api/csrf-token')
    if (res.data && res.data.token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = res.data.token
      axios.defaults.headers.common['X-XSRF-TOKEN'] = res.data.token
    }
  } catch (e) {
    console.error('Failed to fetch CSRF token:', e)
  }
}

const activeRange = ref('today')

const calendarDate = ref(new Date())
const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const monthOptions = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]
const selectedMonth = ref(calendarDate.value.getMonth())
const selectedYear = ref(calendarDate.value.getFullYear())
const yearOptions = computed(() => {
  const years = []
  for (let i = 1926; i <= 2126; i++) {
    years.push(i)
  }
  return years
})
const updateCalendarDate = () => {
  calendarDate.value = new Date(selectedYear.value, selectedMonth.value, 1)
}
const monthLabel = computed(() => {
  return calendarDate.value.toLocaleString('en-US', { month: 'long', year: 'numeric' })
})
const calendarDays = computed(() => {
  const year = calendarDate.value.getFullYear()
  const month = calendarDate.value.getMonth()
  const firstDay = new Date(year, month, 1).getDay()
  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const cells = []
  for (let i = 0; i < firstDay; i++) cells.push(null)
  for (let d = 1; d <= daysInMonth; d++) cells.push(d)
  while (cells.length % 7 !== 0) cells.push(null)
  return cells
})
const isToday = (day) => {
  if (!day) return false
  const today = new Date()
  return (
    day === today.getDate() &&
    calendarDate.value.getMonth() === today.getMonth() &&
    calendarDate.value.getFullYear() === today.getFullYear()
  )
}

const dashboardTotals = ref({
  orders: 0,
  completed: 0,
  sales: '₱0',
  pending: 0,
})

const summaryTotals = ref({
  totalEmployees: 0,
  activeStaff: 0,
})

const productionQueue = ref([])
const topProducts = ref([])
const lowStockItems = ref([])
const staffActivity = ref([])
const recentOrders = ref([])
const showAllOrders = ref(false)
const branchAttendance = ref([])
const attendanceHistory = ref([])
const attendanceStatus = ref({
  clocked_in: false,
  clocked_out: false,
  time_in: null,
  time_out: null,
  status: null
})
const isClockingInOut = ref(false)

const visibleOrders = computed(() => {
  if (!recentOrders.value || recentOrders.value.length === 0) {
    return []
  }

  if (showAllOrders.value) {
    return recentOrders.value
  }

  return recentOrders.value.slice(0, 3)
})

const isLoadingDashboard = ref(false)
const dashboardError = ref('')

const showInfoModal = ref(false)
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

const managerProfile = ref({
  fullName: '',
  role: 'BRANCH_MANAGER',
  email: '',
  contact: '',
  branch: '',
  accountId: '',
  avatarUrl: '',
})

function normalizeUser(u) {
  if (!u) return {
    fullName: '', role: '', email: '', contact: '', branch: '', accountId: '', avatarUrl: ''
  }
  return {
    fullName: u.fullName ?? u.full_name ?? '',
    role: u.role ?? '',
    email: u.email ?? '',
    contact: u.contact ?? u.phone_number ?? '',
    branch: u.branch ?? (u.branch_name ?? ''),
    accountId: u.accountId ?? (u.account_id ?? ''),
    avatarUrl: u.avatarUrl ?? (u.avatar_url ?? ''),
  }
}

const isEditingInfo = ref(false)

async function loadDashboard(range) {
  isLoadingDashboard.value = true
  dashboardError.value = ''

  try {
    const res = await axios.get('/api/manager/dashboard', {
      params: { range },
      withCredentials: true,
    })

    if (res.data.success) {
      // Update dashboard totals
      dashboardTotals.value = res.data.stats || dashboardTotals.value

      // Update summary totals
      summaryTotals.value = res.data.summary || summaryTotals.value

      // Update other data
      recentOrders.value = res.data.recentOrders || []
      productionQueue.value = res.data.productionQueue || []
      topProducts.value = res.data.topProducts || []
      lowStockItems.value = res.data.lowStockItems || []
      staffActivity.value = res.data.staffActivity || []

      // Update manager profile with branch info
      if (res.data.branch) {
        managerProfile.value.branch = res.data.branch.name
      }
    } else {
      dashboardError.value = res.data.message || 'Unable to load dashboard.'
    }
  } catch (e) {
    console.error('Dashboard load error:', e)
    dashboardError.value = 'Error loading dashboard.'
  } finally {
    isLoadingDashboard.value = false
  }
}

async function loadAttendanceStatus() {
  try {
    const res = await axios.get('/api/staff/attendance/status', {
      withCredentials: true,
    })

    if (res.data.ok) {
      attendanceStatus.value = {
        clocked_in: res.data.clocked_in,
        clocked_out: res.data.clocked_out,
        time_in: res.data.time_in,
        time_out: res.data.time_out,
        status: res.data.status
      }
    }
  } catch (e) {
    console.error('Error loading attendance status:', e)
  }
}

async function loadAttendanceHistory(range = 'today') {
  try {
    const res = await axios.get('/api/staff/attendance/history', {
      params: { range },
      withCredentials: true,
    })

    if (res.data.ok) {
      attendanceHistory.value = res.data.data || []
    }
  } catch (e) {
    console.error('Error loading attendance history:', e)
    attendanceHistory.value = []
  }
}

async function clockIn() {
  isClockingInOut.value = true
  try {
    await setupCsrfToken()
    const res = await axios.post('/api/staff/clock-in', {}, {
      withCredentials: true
    })
    if (res.data.ok) {
      attendanceStatus.value.clocked_in = true
      attendanceStatus.value.time_in = res.data.time_in
      attendanceStatus.value.status = res.data.status
      alert('Clocked in successfully!')
      await loadAttendanceHistory(activeRange.value)
    } else {
      alert(res.data.message || 'Clock in failed')
    }
  } catch (e) {
    alert('Error clocking in: ' + (e.response?.data?.message || e.message))
  } finally {
    isClockingInOut.value = false
  }
}

async function clockOut() {
  isClockingInOut.value = true
  try {
    await setupCsrfToken()
    const res = await axios.post('/api/staff/clock-out', {}, {
      withCredentials: true
    })
    if (res.data.ok) {
      attendanceStatus.value.clocked_out = true
      attendanceStatus.value.time_out = res.data.time_out
      alert(`Clocked out successfully! Hours worked: ${res.data.hours_worked}h`)
      await loadAttendanceHistory(activeRange.value)
    } else {
      alert(res.data.message || 'Clock out failed')
    }
  } catch (e) {
    alert('Error clocking out: ' + (e.response?.data?.message || e.message))
  } finally {
    isClockingInOut.value = false
  }
}

async function loadBranchAttendance(range = 'today') {
  try {
    const res = await axios.get('/api/staff/attendance/branch', {
      params: { range },
      withCredentials: true,
    })

    if (res.data.success) {
      branchAttendance.value = res.data.data || []
    }
  } catch (e) {
    console.error('Error loading branch attendance:', e)
    branchAttendance.value = []
  }
}

async function changeRange(range) {
  if (activeRange.value === range) return
  activeRange.value = range
  await loadDashboard(range)
  await loadAttendanceHistory(range)
  await loadBranchAttendance(range)
}

async function openInfoModal() {
  showInfoModal.value = true
  isEditingInfo.value = false

  try {
    const res = await axios.get('/api/owner-profile', {
      withCredentials: true,
    })
    if (res.data.ok) {
      managerProfile.value = normalizeUser(res.data.user)
    }
  } catch (e) {
    // optional
  }
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false
  } else {
    showInfoModal.value = false
  }
}

async function saveManagerInfo() {
  try {
    const payload = {
      fullName: managerProfile.value.fullName,
      email: managerProfile.value.email,
      contact: managerProfile.value.contact,
    }

    const res = await axios.put('/api/owner-profile', payload, {
      withCredentials: true,
    })

    if (res.data.ok) {
      isEditingInfo.value = false
    }
  } catch (e) {
    // optional
  }
}

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  // Confirm before changing profile picture
  if (!window.confirm('Are you sure you want to change your profile picture?')) return

  // Save chosen file to sessionStorage as dataURL so we can reload first and then upload
  try {
    const reader = new FileReader()
    const dataUrl = await new Promise((resolve, reject) => {
      reader.onerror = () => reject(new Error('Failed to read file'))
      reader.onload = () => resolve(reader.result)
      reader.readAsDataURL(file)
    })
    sessionStorage.setItem('pendingAvatar', JSON.stringify({ dataUrl, filename: file.name, panel: 'manager' }))
    // reload so other panels get fresh state; upload will continue after reload
    try { window.location.reload() } catch (_) { }
    return
  } catch (e) {
    // fallback to direct upload if reading fails
  }

  const formData = new FormData()
  formData.append('avatar', file)

  // Ensure fresh CSRF cookie before stateful POST
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
  } catch (e) {}

  // small delay to allow browser to set the XSRF cookie before reading it
  await new Promise(resolve => setTimeout(resolve, 50))

  // Ensure axios X-XSRF-TOKEN header is set from cookie (helps multipart requests)
  try {
    function getCookie(name) { const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return m ? m[2] : null }
    const xsrf = getCookie('XSRF-TOKEN')
    if (xsrf) {
      try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (_) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
    }
  } catch (e) {}

  // DEBUG: dump cookie + header right before posting
  try { console.debug('DEBUG CSRF: document.cookie=', document.cookie) } catch (_) {}
  try { console.debug('DEBUG CSRF: axios.defaults.headers.common["X-XSRF-TOKEN"]=', axios.defaults.headers.common['X-XSRF-TOKEN']) } catch (_) {}

  // Also append CSRF token to FormData to avoid header/cookie race on multipart uploads
  try {
    if (typeof xsrf !== 'undefined' && xsrf) {
      try { formData.append('_token', decodeURIComponent(xsrf)) } catch (_) { formData.append('_token', xsrf) }
    }
  } catch (e) {}

  let res
  try {
    res = await axios.post('/api/upload-avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      withCredentials: true,
    })
  } catch (err) {
    if (err.response && err.response.status === 419) {
      try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
      res = await axios.post('/api/upload-avatar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        withCredentials: true,
      })
    } else {
      throw err
    }
  }

  // Fetch latest profile to ensure avatarUrl is up-to-date
  try {
    const profileRes = await axios.get('/api/owner-profile', { withCredentials: true })
    if (profileRes.data.ok && profileRes.data.user) {
      managerProfile.value.avatarUrl = (profileRes.data.user.avatarUrl || res.data.avatarUrl) + '?t=' + Date.now()
    } else {
      managerProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
    }
  } catch (e) {
    managerProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
  }

  // After successful change, reload so all panels reflect the new profile
  try { window.location.reload() } catch (_) {}
}

function clearTempOverlay() {
  try {
    if (window.__chikin_temp_overlay) {
      window.__chikin_temp_overlay.remove()
      window.__chikin_temp_overlay = null
    }
  } catch (e) {}
  try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
}

// If we reloaded after choosing an avatar, perform the pending upload for this panel
onMounted(async () => {
  try {
    const pendingRaw = sessionStorage.getItem('pendingAvatar')
    if (!pendingRaw) return
    const pending = JSON.parse(pendingRaw)
    if (!pending || pending.panel !== 'manager') return

    // convert dataURL back to blob
    function dataURLtoBlob(dataurl) {
      const arr = dataurl.split(',')
      const mime = arr[0].match(/:(.*?);/)[1]
      const bstr = atob(arr[1])
      let n = bstr.length
      const u8arr = new Uint8Array(n)
      while (n--) {
        u8arr[n] = bstr.charCodeAt(n)
      }
      return new Blob([u8arr], { type: mime })
    }

    const blob = dataURLtoBlob(pending.dataUrl)
    const file = new File([blob], pending.filename, { type: blob.type })
    const formData = new FormData()
    formData.append('avatar', file)

    // Ensure fresh CSRF cookie before stateful POST
    try { await axios.get('/sanctum/csrf-cookie', { withCredentials: true }) } catch (e) {}
    await new Promise(resolve => setTimeout(resolve, 50))

    // Ensure axios X-XSRF-TOKEN header
    try {
      function getCookie(name) { const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return m ? m[2] : null }
      const xsrf = getCookie('XSRF-TOKEN')
      if (xsrf) {
        try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } catch (_) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
        try { formData.append('_token', decodeURIComponent(xsrf)) } catch (_) { formData.append('_token', xsrf) }
      }
    } catch (e) {}

    // DEBUG
    try { console.debug('AUTO UPLOAD CSRF: document.cookie=', document.cookie) } catch (_) {}
    try { console.debug('AUTO UPLOAD CSRF: axios.defaults.headers.common["X-XSRF-TOKEN"]=', axios.defaults.headers.common['X-XSRF-TOKEN']) } catch (_) {}

    const res = await axios.post('/api/upload-avatar', formData, { headers: { 'Content-Type': 'multipart/form-data' }, withCredentials: true })
    try {
      const profileRes = await axios.get('/api/owner-profile', { withCredentials: true })
      if (profileRes.data.ok && profileRes.data.user) {
        managerProfile.value.avatarUrl = (profileRes.data.user.avatarUrl || res.data.avatarUrl) + '?t=' + Date.now()
      } else {
        managerProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
      }
    } catch (e) {
      managerProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
    }

    sessionStorage.removeItem('pendingAvatar')
  } catch (e) {
    // ignore
  }
})

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true

  try {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    window.location.replace('/logout')
  } catch (e) {
    // client-side logout pa rin
  }

  // Clear client-side state and perform full-page navigation so server enforces session state
  try {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    window.location.replace('/')
  } finally {
    showLogoutConfirm.value = false
    isLoggingOut.value = false
  }
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

onMounted(async () => {
  clearTempOverlay()
  await setupCsrfToken()
  loadDashboard(activeRange.value)
  loadAttendanceStatus()
  loadAttendanceHistory(activeRange.value)
  loadBranchAttendance(activeRange.value)
  axios
    .get('/api/owner-profile', { withCredentials: true })
    .then(res => {
      if (res.data.ok) {
        managerProfile.value = normalizeUser(res.data.user)
      }
    })
    .catch(() => {})
  clearTempOverlay()
})
</script>
