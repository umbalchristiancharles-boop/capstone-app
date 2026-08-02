<template>
  <div class="main-branch-page">
    <section class="panel-layout">
      <!-- Left profile column removed for Main Branch layout -->

      <main class="main-col">
        <header class="panel-header">
          <h1>Main Branch Logistics Dashboard</h1>
          <p>HQ inventory and fulfillment control center for Main Branch operations.</p>
        </header>

        <section class="overview-grid">
          <article class="overview-card"><span class="k">Active Products</span><strong>{{ metrics.active_products }}</strong></article>
          <article class="overview-card"><span class="k">Low Stock</span><strong>{{ metrics.low_stock }}</strong></article>
          <article class="overview-card" :class="{ 'stat-alert': pendingDeliveriesCount > 0 }">
            <span class="k">Pending Deliveries</span>
            <strong>{{ metrics.pending_deliveries }}</strong>
            <span v-if="pendingDeliveriesCount > 0" class="panel-badge">{{ pendingDeliveriesCount }}</span>
          </article>
          <article class="overview-card"><span class="k">Suppliers</span><strong>{{ metrics.suppliers }}</strong></article>
        </section>


        <section class="panel-section">
          <h2 class="section-title">Inventory Monitor</h2>
          <p class="section-description">Current stock levels across branches (read-only)</p>

          <div class="branch-filter-row" style="margin-bottom:12px">
            <label style="font-weight:600;color:#4b2a06;margin-right:8px">Branch</label>
            <select v-model="selectedBranch" style="min-width:220px;padding:8px;border-radius:8px;border:1px solid #ddd">
              <option value="" disabled>Select branch...</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <div v-if="branchesLoading" style="margin-left:8px" class="loading-spinner"></div>
            <div v-if="branchesError" style="margin-left:8px;color:#dc3545">{{ branchesError }}</div>
          </div>

          <div v-if="inventoryLoading" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Loading inventory...</p>
          </div>

          <div v-else-if="inventoryError" class="error-container">
            <p class="error-message">{{ inventoryError }}</p>
            <button class="btn-retry" @click="fetchInventory">Retry</button>
          </div>

          <div v-else class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Stock Count</th>
                  <th>Minimum Stock</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in inventory" :key="product.id">
                  <td>{{ product.name }} <small v-if="product.branch_name">({{ product.branch_name }})</small></td>
                  <td>{{ product.real_stock ?? product.stock }}</td>
                  <td>{{ product.min_stock }}</td>
                  <td>
                    <span :class="['status-badge', product.status === 'OK' ? 'status-ok' : 'status-low']">{{ product.status }}</span>
                  </td>
                  <td>
                    <span class="muted-note">View-only</span>
                  </td>
                </tr>
                <tr v-if="inventory.length === 0">
                  <td colspan="5" class="empty-message">No products found.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <section class="panel-section">
            <h2 class="section-title">
              Product Requests (Logistics Approval)
              <span v-if="pendingProductRequestsCount > 0" class="panel-badge">{{ pendingProductRequestsCount }}</span>
            </h2>
            <p class="section-description">New product requests awaiting logistics approval from Main Branch.</p>

            <div v-if="prLoading" class="loading-container small">
              <div class="loading-spinner"></div>
              <p>Loading product requests...</p>
            </div>

            <div v-else-if="prError" class="error-container">
              <p class="error-message">{{ prError }}</p>
              <button class="btn-retry" @click="fetchPendingProductRequests">Retry</button>
            </div>

            <div v-else class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Product Name</th>
                    <th>Requested By</th>
                    <th>Branch</th>
                    <th>Requested</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in pendingProductRequests" :key="r.id">
                    <td>{{ r.name }}</td>
                    <td>{{ r.requester?.full_name || r.requester?.username || '(user)' }}</td>
                    <td>{{ r.branch?.name || '(branch)' }}</td>
                    <td>{{ formatDate(r.created_at) }}</td>
                    <td>
                      <button class="action-btn" @click="approvePendingRequest(r.id)">Approve</button>
                      <button class="link-btn" style="margin-left:8px;background:#ef4444" @click="rejectPendingRequest(r.id)">Reject</button>
                    </td>
                  </tr>
                  <tr v-if="pendingProductRequests.length === 0">
                    <td colspan="5" class="empty-message">No product requests awaiting logistics approval.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section class="panel-section">
            <h2 class="section-title">Suppliers</h2>
            <p class="section-description">Suppliers available for the selected branch (read-only)</p>

            <div v-if="suppliersLoading" class="loading-container small">
              <div class="loading-spinner"></div>
              <p>Loading suppliers...</p>
            </div>

            <div v-else-if="suppliersError" class="error-container">
              <p class="error-message">{{ suppliersError }}</p>
              <button class="btn-retry" @click="fetchSuppliers">Retry</button>
            </div>

            <div v-else class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Supplier Name</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="s in suppliers" :key="s.id">
                    <td>{{ s.name || s.full_name || s.username || '(no name)' }}</td>
                    <td>
                      <div v-if="s.email">{{ s.email }}</div>
                      <div v-else-if="s.phone">{{ s.phone }}</div>
                      <div v-else class="muted-note">(no contact)</div>
                    </td>
                    <td>
                      <span :class="['status-badge', (s.is_active || s.active) ? 'status-ok' : 'status-low']">
                        {{ (s.is_active || s.active) ? 'ACTIVE' : (s.status || 'INACTIVE') }}
                      </span>
                    </td>
                    <td><span class="muted-note">View-only</span></td>
                  </tr>
                  <tr v-if="suppliers.length === 0">
                    <td colspan="4" class="empty-message">No suppliers found for this branch.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

        </section>
      </main>

      <aside class="side-col">
        <div class="header-actions-side">
          <div class="header-profile-wrapper" style="margin:0 0 12px;">
            <button class="header-profile-btn" type="button" @click.stop="toggleProfileDropdown">
              <div class="header-avatar">
                <div v-if="profile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url(' + profile.avatarUrl + ')' }"></div>
                <div v-else class="header-avatar-initials">{{ (profile.full_name || profile.fullName || 'M').charAt(0) }}</div>
              </div>
              <div class="header-name">{{ ((profile.branch_name || profile.branch || '') ? (profile.branch_name || profile.branch) + ' - ' : '') + ((profile.role || profile.position || 'MANAGER')).toString().toUpperCase() }}</div>
            </button>
            <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
              <button class="dropdown-item" @click="openProfileInfo">Info</button>
              <button class="dropdown-item" @click="askLogout">Logout</button>
            </div>
          </div>

          <section class="panel-block announcements-panel">
            <div class="panel-header announcements-header">
              <h2>Announcements</h2>
            </div>
            <div class="panel-body">
              <div v-if="loadingAnnouncements" style="text-align:center; padding:12px; color:#9ca3af;">Loading...</div>
              <div v-else-if="announcements.length === 0" style="text-align:center; padding:12px; color:#9ca3af;">No announcements</div>
              <ul v-else class="announcement-list">
                <li v-for="a in announcements" :key="a.id" class="announcement-item">
                  <div class="announcement-title">{{ a.title }}</div>
                  <div class="announcement-meta">{{ new Date(a.created_at).toLocaleString() }} • {{ a.target || 'all' }}</div>
                  <div class="announcement-message">{{ a.message }}</div>
                </li>
              </ul>
            </div>
          </section>

          <div class="attendance-card" style="margin-top:12px; background:#ffffff;">
            <div class="attendance-header" style="margin-top:12px;">
              <span class="attendance-title">Attendance</span>
              <span :class="['attendance-status-badge', attendanceStatus.is_clocked_in ? 'status-on-duty' : 'status-off-duty']">
                {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
              </span>
            </div>
            <div class="attendance-buttons">
              <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockInGeofencing || locationLoading" class="btn-clock-in">
                {{ (isAttendanceProcessing || locationLoading) ? '...' : 'Clock In' }}
              </button>
              <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut || !canClockInGeofencing || locationLoading" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">
                {{ (isAttendanceProcessing || locationLoading) ? '...' : 'Clock Out' }}
              </button>
            </div>
            <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction"><span class="restriction-icon">🔒</span><span>Cannot clock out before {{ scheduledTimeOut }}</span></div>
            <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
          </div>
        </div>
      </aside>
    </section>

    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from Main Branch Logistics Panel?</h3>
          <p>This will end your current session for Chikin Tayo.</p>
          <div class="logout-actions">
            <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
            <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { showToast } from './toastStore'

const router = useRouter()
const profile = ref({})
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const metrics = ref({ active_products: 0, low_stock: 0, pending_deliveries: 0, suppliers: 0 })
// Inventory / procurement state (read-only on Main Branch)
const inventory = ref([])
const inventoryLoading = ref(false)
const inventoryError = ref('')

const procurementRequests = ref([])
const procRequestsLoading = ref(false)

// Product requests pending logistics approval
const pendingProductRequests = ref([])
const prLoading = ref(false)
const prError = ref('')

// Suppliers for selected branch (read-only)
const suppliers = ref([])
const suppliersLoading = ref(false)
const suppliersError = ref('')
const announcements = ref([])
const loadingAnnouncements = ref(false)
const attendanceStatus = ref({ is_clocked_in: false, clock_in_time: null, clock_out_time: null, hours_worked: 0 })
const isAttendanceProcessing = ref(false)
const attendanceMessage = ref('')
const attendanceMessageType = ref('')
const attendanceSettings = ref({ early_clockout_override: false, scheduled_time_out: '17:00:00' })
const userLocation = ref(null)
const locationLoading = ref(false)
const locationError = ref('')
const canClockInGeofencing = ref(true)
const geofencingMessage = ref('')
const notificationCounts = ref({ logistics: 0 })
const hasNotified = ref(false)
const pendingDeliveriesCount = computed(() => {
  const apiPending = Number(notificationCounts.value?.logistics || 0)
  const metricsPending = Number(metrics.value?.pending_deliveries || 0)
  return Math.max(apiPending, metricsPending, 0)
})
const pendingProductRequestsCount = computed(() => (pendingProductRequests.value || []).length)
const logisticsAlertCount = computed(() => Math.max(pendingDeliveriesCount.value, pendingProductRequestsCount.value, 0))

// Header profile dropdown state (compact header in profile column)
const profileDropdownVisible = ref(false)
function toggleProfileDropdown() { profileDropdownVisible.value = !profileDropdownVisible.value }
function openProfileInfo() { profileDropdownVisible.value = false; try { showToast('Profile info') } catch(e) {} }

// Branch selector for Main Branch HQ users
const branches = ref([])
const selectedBranch = ref(null)
const branchesLoading = ref(false)
const branchesError = ref('')

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    window.location.replace('/staff-landing')
  }, 350)
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

async function loadProfile() {
  try {
    // Prefer manager logistics profile which includes capability flags
    try {
      const r2 = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
      if (r2.data?.ok) profile.value = r2.data.user || {}
    } catch (e) {
      const res = await axios.get('/api/me', { withCredentials: true })
      if (res.data?.ok) profile.value = res.data.user || {}
    }
  } catch (e) {}
}

async function loadMetrics() {
  try {
    const res = await axios.get('/api/manager/logistics/dashboard', { withCredentials: true })
    const d = res.data || {}
    metrics.value = {
      active_products: d.total_products ?? d.products ?? 0,
      low_stock: d.low_stock_count ?? d.low_stock ?? 0,
      pending_deliveries: d.pending_deliveries ?? 0,
      suppliers: d.total_suppliers ?? d.suppliers ?? 0,
    }
  } catch (e) {}
}

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      notificationCounts.value = { logistics: Number(res.data.counts?.logistics || 0) }
    }
  } catch (e) {
    notificationCounts.value = { logistics: 0 }
  }
}

watch(logisticsAlertCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending logistics operations.', 'info')
    hasNotified.value = true
  }
})

async function fetchInventory() {
  inventoryLoading.value = true
  inventoryError.value = ''
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    const res = await axios.get('/api/manager/logistics/inventory', { params, withCredentials: true })
    const raw = res.data?.data ?? res.data ?? []
    inventory.value = Array.isArray(raw) ? raw : []
  } catch (e) {
    inventoryError.value = 'Failed to load inventory'
    inventory.value = []
  } finally {
    inventoryLoading.value = false
  }
}

async function fetchProcRequests() {
  procRequestsLoading.value = true
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    // include completed requests for branch-wide HQ view
    if (selectedBranch.value) params.include_completed = 1
    const res = await axios.get('/api/procurement-requests', { params, withCredentials: true })
    console.debug('MainBranchLogisticsPanel.fetchProcRequests params:', params, 'res.data:', res.data)
    const data = res.data?.data ?? res.data ?? []
    procurementRequests.value = Array.isArray(data) ? data : []
  } catch (e) {
    procurementRequests.value = []
  } finally {
    procRequestsLoading.value = false
  }
}

async function fetchBranches() {
  branchesLoading.value = true
  branchesError.value = ''
  try {
    const res = await axios.get('/api/manager/logistics/branches', { withCredentials: true })
    const data = res.data?.data ?? res.data ?? []
    branches.value = Array.isArray(data) ? data : []
    if (!selectedBranch.value) {
      if (branches.value.length > 0) selectedBranch.value = branches.value[0].id
    }
  } catch (e) {
    console.error('Failed to load branches', e)
    branches.value = []
    branchesError.value = 'Failed to load branches'
  } finally {
    branchesLoading.value = false
  }
}

function formatPrice(n) {
  const num = Number(n || 0)
  if (Number.isNaN(num)) return '₱0.00'
  return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleString() } catch (e) { return d }
}

function getProcStatusClass(status) {
  switch ((status || '').toLowerCase()) {
    case 'completed': return 'status-approved'
    case 'approved': return 'status-approved'
    case 'pending': return 'status-pending'
    default: return 'status-pending'
  }
}

function formatProcStatus(status, budgetApproved) {
  if (budgetApproved) return 'BUDGET APPROVED'
  return (status || '').toUpperCase()
}

onMounted(async () => {
  await loadProfile()
  await loadMetrics()
  await loadPanelNotifications()
  await fetchBranches()

  watch(selectedBranch, async () => {
    await Promise.all([fetchInventory(), fetchProcRequests(), fetchSuppliers()])
  })

  await Promise.all([fetchInventory().catch(()=>{}), fetchProcRequests().catch(()=>{}), fetchSuppliers().catch(()=>{}), fetchAnnouncements().catch(()=>{})])
  if (!hideAttendanceCard.value) {
    await Promise.all([loadAttendanceStatus().catch(()=>{}), loadAttendanceSettings().catch(()=>{}), getUserLocation().catch(()=>{})])
  }
  // fetch pending product requests for logistics approval
  await fetchPendingProductRequests().catch(()=>{})
})

async function fetchAnnouncements() {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    const data = res.data?.announcements ?? res.data?.data ?? res.data ?? []
    announcements.value = Array.isArray(data) ? data : []
  } catch (e) {
    announcements.value = []
  } finally {
    loadingAnnouncements.value = false
  }
}

const scheduledTimeOut = computed(() => {
  const time = attendanceSettings.value.scheduled_time_out || '17:00:00'
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour % 12 || 12
  return `${hour12}:${minutes} ${ampm}`
})

const canClockOut = computed(() => {
  if (!attendanceStatus.value.is_clocked_in) return false
  if (attendanceSettings.value.early_clockout_override) return true
  const now = new Date()
  const currentTotalMinutes = now.getHours() * 60 + now.getMinutes()
  const [scheduledHours, scheduledMinutes] = (attendanceSettings.value.scheduled_time_out || '17:00:00').split(':')
  const scheduledTotalMinutes = parseInt(scheduledHours) * 60 + parseInt(scheduledMinutes)
  return currentTotalMinutes >= scheduledTotalMinutes
})

const hideAttendanceCard = computed(() => {
  try {
    return new URLSearchParams(window.location.search).get('from') === 'custom-panel'
  } catch (e) {
    return false
  }
})

async function loadAttendanceStatus() {
  try {
    const res = await axios.get('/api/manager/attendance/status', { withCredentials: true })
    if (res.data && res.data.success) {
      attendanceStatus.value = {
        is_clocked_in: !!res.data.clocked_in,
        clock_in_time: res.data.time_in || res.data.status?.clock_in_time || null,
        clock_out_time: res.data.time_out || res.data.status?.clock_out_time || null,
        hours_worked: res.data.status?.hours_worked || 0
      }
    }
  } catch (e) {
    // ignore
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
    // ignore
  }
}

async function getUserLocation() {
  locationLoading.value = true
  locationError.value = ''
  canClockInGeofencing.value = true
  geofencingMessage.value = ''

  if (!navigator.geolocation) {
    locationError.value = 'Geolocation is not supported by your browser'
    canClockInGeofencing.value = false
    locationLoading.value = false
    return
  }

  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      })
    })

    userLocation.value = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude
    }
  } catch (error) {
    console.error('Error getting location:', error)
    locationError.value = 'Unable to retrieve your location. Please enable location services.'
    canClockInGeofencing.value = false
    userLocation.value = null
  } finally {
    locationLoading.value = false
  }
}

async function performClockIn() {
  if (isAttendanceProcessing.value) return

  if (!userLocation.value) {
    attendanceMessage.value = 'Please enable location services to clock in'
    attendanceMessageType.value = 'warning'
    await getUserLocation()
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
    return
  }

  isAttendanceProcessing.value = true
  attendanceMessage.value = ''
  try {
    const res = await axios.post('/api/manager/clock-in', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude
    }, { withCredentials: true })

    if (res.data && res.data.success) {
      attendanceMessage.value = 'Clocked in successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else if (res.data.geofencing_error) {
      attendanceMessage.value = res.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = res.data.message
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.geofencing_error) {
      attendanceMessage.value = e.response.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = e.response.data.message
    } else {
      attendanceMessage.value = e.response?.data?.message || 'Error clocking in'
      attendanceMessageType.value = 'error'
    }
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

async function performClockOut() {
  if (isAttendanceProcessing.value) return

  if (!userLocation.value) {
    attendanceMessage.value = 'Please enable location services to clock out'
    attendanceMessageType.value = 'warning'
    await getUserLocation()
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
    return
  }

  isAttendanceProcessing.value = true
  attendanceMessage.value = ''
  try {
    const res = await axios.post('/api/manager/clock-out', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude
    }, { withCredentials: true })

    if (res.data && res.data.success) {
      attendanceMessage.value = 'Clocked out successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else if (res.data.geofencing_error) {
      attendanceMessage.value = res.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = res.data.message
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.geofencing_error) {
      attendanceMessage.value = e.response.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = e.response.data.message
    } else {
      attendanceMessage.value = e.response?.data?.message || 'Error clocking out'
      attendanceMessageType.value = 'error'
    }
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

async function fetchSuppliers() {
  suppliersLoading.value = true
  suppliersError.value = ''
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    const res = await axios.get('/api/manager/logistics/suppliers', { params, withCredentials: true })
    // Controller returns { ok: true, suppliers: [...] }
    const data = res.data?.suppliers ?? res.data?.data ?? res.data ?? []
    suppliers.value = Array.isArray(data) ? data : []
  } catch (e) {
    suppliers.value = []
    suppliersError.value = 'Failed to load suppliers'
  } finally {
    suppliersLoading.value = false
  }
}

async function fetchPendingProductRequests() {
  prLoading.value = true
  prError.value = ''
  try {
    const params = {}
    if (selectedBranch.value) params.branch_id = selectedBranch.value
    const res = await axios.get('/api/product-requests/pending/logistics', { params, withCredentials: true })
    const data = res.data?.data ?? res.data ?? []
    pendingProductRequests.value = Array.isArray(data) ? data : (data?.data ?? [])
  } catch (e) {
    prError.value = 'Failed to load product requests'
    pendingProductRequests.value = []
  } finally {
    prLoading.value = false
  }
}

async function approvePendingRequest(id) {
  try {
    const notes = window.prompt('Optional notes for approval (leave blank to skip):', '')
    if (notes === null) return // cancelled
    const res = await axios.post(`/api/product-requests/${id}/approve-logistics`, { notes }, { withCredentials: true })
    // remove from list
    pendingProductRequests.value = pendingProductRequests.value.filter(r => r.id !== id)
    try { if (window.showToast) window.showToast('Request approved', 'success') } catch(e) { alert('Request approved') }
  } catch (e) {
    console.error('approvePendingRequest failed', e)
    try { if (window.showToast) window.showToast('Failed to approve request', 'error') } catch(e) { alert('Failed to approve request') }
  }
}

async function rejectPendingRequest(id) {
  try {
    const notes = window.prompt('Provide rejection reason (required):', '')
    if (notes === null) return // cancelled
    if (!notes || notes.trim().length === 0) { alert('Rejection requires a reason'); return }
    const res = await axios.post(`/api/product-requests/${id}/reject-logistics`, { notes }, { withCredentials: true })
    pendingProductRequests.value = pendingProductRequests.value.filter(r => r.id !== id)
    try { if (window.showToast) window.showToast('Request rejected', 'success') } catch(e) { alert('Request rejected') }
  } catch (e) {
    console.error('rejectPendingRequest failed', e)
    try { if (window.showToast) window.showToast('Failed to reject request', 'error') } catch(e) { alert('Failed to reject request') }
  }
}

// watch branch selection to refresh pending requests
watch(selectedBranch, async () => {
  await fetchPendingProductRequests().catch(()=>{})
})
</script>

<style scoped>
/* Match MainBranchAdminPanel color scheme, typography and spacing */
.main-branch-page {
  min-height: 100vh;
  padding: 28px;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  color: rgba(17,24,39,0.95);
  font-size: 15px;
}

.panel-layout { display: grid; grid-template-columns: 1fr 260px; gap: 20px; align-items: start; }
.profile-card, .panel-block, .overview-card, .panel-header { background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 4px 14px rgba(16,24,40,0.04); border: 1px solid #eef2f7; }

.profile-head { display: flex; gap: 14px; align-items: center; }
.avatar { width: 56px; height: 56px; border-radius: 50%; background: #111827; color: #fff; display: grid; place-items: center; font-weight: 700; font-size: 18px; }
.label { font-size: 12px; color: #6b7280; }
.profile-meta { margin: 12px 0; display: grid; gap: 6px; font-size: 14px; color: rgba(66,33,11,0.85); }

.action-btn, .link-btn { border: 0; border-radius: 10px; background: #2563eb; color: #fff; cursor: pointer; box-shadow: 0 8px 24px rgba(37,99,235,0.08); padding: 10px 14px; font-weight: 600; }
.action-btn:hover, .link-btn:hover { filter: brightness(0.98); }
.profile-card .action-btn { display: block; width: 100%; margin-top: 12px; }
.side-col .panel-block .link-btn { display: block; width: 100%; text-align: left; padding: 8px 12px; margin-bottom: 10px; background: linear-gradient(180deg, #2563eb, #e05818); box-shadow: 0 8px 20px rgba(224,88,24,0.08); }

.main-col { display: grid; gap: 18px; }
.panel-header h1 { margin: 0 0 6px; font-size: 34px; letter-spacing: -0.5px; color: rgba(17,24,39,0.95); }
.panel-header p { margin: 0; color: rgba(66,33,11,0.7); max-width: 54ch; }

.overview-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.overview-card { position: relative; display: flex; flex-direction: column; gap: 8px; padding: 16px; }
.overview-card .k { color: rgba(66,33,11,0.7); font-size: 13px; }
.overview-card strong { font-size: 24px; color: rgba(17,24,39,0.85); }
.panel-badge { position:absolute; top:-8px; right:-8px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }
.stat-alert { border:1px solid #fecaca; box-shadow:0 0 0 2px rgba(239,68,68,0.12) }

.side-col { display: grid; gap: 14px; align-content: start; }
.panel-block ul { margin: 0; padding-left: 18px; }
.panel-block li { margin: 8px 0; color: rgba(66,33,11,0.85); }

.logout-btn { border: 0; border-radius: 999px; padding: 8px 12px; background: var(--alert); color: #fff; cursor: pointer; margin-top: 8px; box-shadow: 0 6px 18px rgba(239,68,68,0.08); }
.logout-btn:hover { filter: brightness(0.98); }

.logout-confirm-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.logout-confirm-box { width: min(92vw, 420px); background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 12px 40px rgba(16,24,40,0.12); }
.logout-confirm-box h3 { margin: 0 0 8px; font-size: 18px; }
.logout-confirm-box p { margin: 0 0 14px; color: #64748b; }
.logout-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-cancel, .btn-confirm { border: 0; border-radius: 999px; padding: 6px 14px; font-size: 0.88rem; cursor: pointer; }
.btn-cancel { background: rgba(16,24,40,0.04); color: rgba(17,24,39,0.9); }
.btn-confirm { background: var(--alert); color: #ffffff; }

.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.profile-col, .side-col { position: sticky; top: 16px; }

@media (max-width: 1100px) {
  .panel-layout { grid-template-columns: 1fr; }
  .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

/* Table and status styles (preserve logistics look) */
.table-container { overflow-x: auto; margin-top: 8px; }
.data-table { width: 100%; border-collapse: collapse; background: transparent; }
.data-table th, .data-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.06); }
.panel-section { padding: 6px 0 0; }
.section-title { font-size: 20px; margin: 0 0 6px; color: rgba(17,24,39,0.9); }
.section-description { margin: 0 0 12px; color: rgba(66,33,11,0.65); }

.branch-filter-row select { padding: 8px 10px; border-radius: 8px; border: 1px solid #e6eaf0; background: #fff; color: rgba(17,24,39,0.9); }

.overview-card { min-height: 72px; }
.overview-card strong { font-size: 22px; }

.profile-card { padding: 16px; }
.profile-card .avatar { font-size: 16px; width: 52px; height: 52px; }

.data-table th { background: rgba(255,244,230,0.6); }

.data-table th { background: rgba(255,244,230,0.6); font-weight: 600; color: #5a2c0a; font-size: 13px; text-transform: uppercase; letter-spacing: 0.4px; }
.data-table td.amount { text-align: right; white-space: nowrap; font-weight: 600; }
.product-name { white-space: normal; word-break: break-word; max-width: 420px; }
.empty-message { text-align: center; color: #999; font-style: italic; }

.status-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
.status-ok { background: rgba(46, 204, 113, 0.12); color: #27ae60; }
.status-low { background: rgba(231, 76, 60, 0.12); color: #e74c3c; }
.status-approved { background: rgba(46, 204, 113, 0.12); color: #27ae60; }
.status-pending { background: rgba(241, 196, 15, 0.12); color: #f39c12; }
</style>
