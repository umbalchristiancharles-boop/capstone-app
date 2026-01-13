<template>
  <div class="admin-page">
    <section class="admin-layout">
      <!-- LEFT: STAFF PROFILE COLUMN -->
      <aside class="admin-profile-column">
        <div class="admin-card admin-card--stacked">
          <!-- PROFILE PICTURE + NAME + ROLE -->
          <div class="admin-card__header admin-card__header--stacked">
            <!-- clickable avatar -->
            <label class="admin-avatar admin-avatar--photo avatar-upload" for="avatar-input">
              <img
                v-if="staffProfile.avatarUrl"
                :src="staffProfile.avatarUrl"
                alt="Profile picture"
                class="avatar-img"
              />
              <div v-else class="avatar-placeholder">
                <span class="avatar-initials">ST</span>
              </div>
              <div class="avatar-overlay">
                <span class="avatar-change-text">Change Photo</span>
              </div>
            </label>

            <div class="admin-header-text admin-header-text--center">
              <div class="admin-label">Account</div>
              <div class="admin-name">
                {{ staffProfile.fullName || 'Staff Member' }}
              </div>
              <div class="admin-role">
                {{ staffProfile.role || 'STAFF' }}
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
                &nbsp;{{ staffProfile.accountId || 'st001' }}
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
            <div class="owner-extra">
              <div class="owner-extra-row">
                <span class="owner-label">Access Level:</span>
                <span class="owner-value">Staff</span>
              </div>
              <div class="owner-extra-row">
                <span class="owner-label">Branch:</span>
                <span class="owner-value">
                  {{ typeof staffProfile.branch === 'object' && staffProfile.branch.name ? staffProfile.branch.name : (staffProfile.branch || 'Not assigned') }}
                </span>
              </div>
            </div>

            <div class="admin-actions-row">
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
              <h1>Staff Dashboard</h1>
              <p>
                View your personal orders, schedule, and performance metrics.
              </p>
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
            <span class="overview-label">Completed: </span>
            <span class="overview-value">
              &nbsp;{{ dashboardTotals.completed }}
            </span>
          </div>
          <div class="overview-card">
            <span class="overview-label">Pending:</span>
            <span class="overview-value">
              &nbsp;{{ dashboardTotals.pending }}
            </span>
          </div>
        </section>

        <!-- Orders table -->
        <section class="panel-block">
          <div class="panel-header">
            <h2>My Orders</h2>
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
              <span>No orders for this range.</span>
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
            <h2>My Tasks</h2>
          </div>
          <div class="panel-body panel-body--list">
            <div
              v-if="productionQueue.length === 0"
              class="queue-item"
            >
              <div class="queue-title">No tasks assigned. </div>
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
        <!-- Performance stats -->
        <section class="panel-block">
          <div class="panel-header">
            <h2>Performance</h2>
          </div>
          <div class="panel-body panel-body--list">
            <div class="side-item">
              <span>Completed Orders:</span>
              <span class="side-value">{{ summaryTotals.completedOrders }}</span>
            </div>
            <div class="side-item">
              <span>Avg Rating:</span>
              <span class="side-value">{{ summaryTotals.avgRating || 'N/A' }}</span>
            </div>
            <div class="side-item">
              <span>Hours Worked:</span>
              <span class="side-value">{{ summaryTotals.hoursWorked }}</span>
            </div>
          </div>
        </section>

        <!-- Announcements -->
        <section class="panel-block">
          <div class="panel-header">
            <h2>Announcements</h2>
          </div>
          <div class="panel-body panel-body--list">
            <div
              v-if="announcements.length === 0"
              class="side-item"
            >
              <span>No announcements.</span>
            </div>
            <div
              v-else
              v-for="ann in announcements"
              :key="ann.id"
              class="side-item"
            >
              <span>{{ ann.title }}</span>
              <span class="side-meta">{{ ann.date }}</span>
            </div>
          </div>
        </section>
      </aside>
    </section>

    <!-- INFO MODAL -->
    <transition name="fade">
      <div v-if="showInfoModal" class="info-backdrop">
        <div class="info-modal">
          <h3>Staff Information</h3>
          <p class="info-sub">
            Your personal details are displayed below.
          </p>

          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">Full name</span>
              <span class="info-value" v-if="!isEditingInfo">
                {{ staffProfile.fullName }}
              </span>
              <input
                v-else
                v-model="staffProfile.fullName"
                class="info-input"
                type="text"
              />
            </div>

            <div class="info-row">
              <span class="info-label">Role</span>
              <span class="info-value">{{ staffProfile.role }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Email</span>
              <span class="info-value" v-if="!isEditingInfo">
                {{ staffProfile.email }}
              </span>
              <input
                v-else
                v-model="staffProfile.email"
                class="info-input"
                type="email"
              />
            </div>

            <div class="info-row">
              <span class="info-label">Contact</span>
              <span class="info-value" v-if="!isEditingInfo">
                {{ staffProfile.contact }}
              </span>
              <input
                v-else
                v-model="staffProfile.contact"
                class="info-input"
                type="text"
              />
            </div>

            <div class="info-row">
              <span class="info-label">Branch</span>
              <span class="info-value">{{ staffProfile.branch }}</span>
            </div>
          </div>

          <div class="info-actions">
            <button class="btn-outline" @click="handleInfoClose">
              {{ isEditingInfo ? 'Cancel' : 'Close' }}
            </button>
            <button
              class="btn-primary"
              @click="isEditingInfo ? saveStaffInfo() : (isEditingInfo = true)"
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
          <h3>Logout from Staff Dashboard?</h3>
          <p>This will end your current session for the Staff Dashboard.</p>
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
const activeRange = ref('today')

const dashboardTotals = ref({
  orders: 0,
  completed: 0,
  pending: 0,
})

const summaryTotals = ref({
  completedOrders: 0,
  avgRating: 4.8,
  hoursWorked: 0,
})

const productionQueue = ref([])
const recentOrders = ref([])
const showAllOrders = ref(false)
const announcements = ref([])

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

const staffProfile = ref({
  fullName: '',
  role: 'STAFF',
  email: '',
  contact: '',
  branch: '',
  accountId: '',
  avatarUrl: '',
})

const isEditingInfo = ref(false)

async function loadDashboard(range) {
  isLoadingDashboard.value = true
  dashboardError.value = ''

  try {
    const res = await axios.get('/api/owner-dashboard', {
      params: { range },
      withCredentials: true,
    })

    if (res.data.ok) {
      dashboardTotals.value = res.data.totals || dashboardTotals.value
      summaryTotals.value = res.data.summary || summaryTotals.value
      recentOrders.value = res.data.recentOrders || []
      productionQueue.value = res.data.productionQueue || []
      announcements.value = res.data.announcements || []
    } else {
      dashboardError.value =
        res.data.message || 'Unable to load dashboard.'
    }
  } catch (e) {
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

  try {
    const res = await axios.get('/api/owner-profile', {
      withCredentials: true,
    })
    if (res.data.ok) {
      staffProfile.value = res.data.user
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

async function saveStaffInfo() {
  try {
    const payload = {
      fullName: staffProfile.value.fullName,
      email: staffProfile.value.email,
      contact: staffProfile.value.contact,
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

  const formData = new FormData()
  formData.append('avatar', file)

  const res = await axios.post('/api/upload-avatar', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
    withCredentials: true,
  })

  staffProfile.value.avatarUrl = res.data.avatarUrl
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true

  try {
    await axios.post(
      '/api/logout',
      {},
      { withCredentials: true }
    )
  } catch (e) {
    // client-side logout pa rin
  }

  overlayText.value = 'Logging you out...'
  setTimeout(() => {
    showOverlay.value = true
    setTimeout(() => {
      router.push('/admin-login')
    }, 600)
  }, 400)

  showLogoutConfirm.value = false
  isLoggingOut.value = false
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

onMounted(() => {
  loadDashboard(activeRange.value)
  axios
    .get('/api/owner-profile', { withCredentials: true })
    .then(res => {
      if (res.data.ok) {
        staffProfile.value = res.data.user
      }
    })
    .catch(() => {})
})
</script>
