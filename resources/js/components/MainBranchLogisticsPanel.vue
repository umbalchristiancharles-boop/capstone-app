<template>
  <div class="main-branch-page" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
    <aside class="logistics-sidebar">
      <nav class="logistics-sidebar__nav" aria-label="Logistics navigation">
        <a class="logistics-sidebar__item" :class="{ active: selectedSection === 'overview' }" href="#logistics-overview" @click.prevent="selectedSection = 'overview'">Logistics Overview</a>
        <a class="logistics-sidebar__item" :class="{ active: selectedSection === 'inventory' }" href="#inventory-monitor" @click.prevent="selectedSection = 'inventory'">Inventory Monitor</a>
        <a class="logistics-sidebar__item" :class="{ active: selectedSection === 'product-requests' }" href="#product-requests" @click.prevent="selectedSection = 'product-requests'">Product Requests</a>
        <a class="logistics-sidebar__item" :class="{ active: selectedSection === 'suppliers' }" href="#suppliers" @click.prevent="selectedSection = 'suppliers'">Suppliers</a>
        <a class="logistics-sidebar__item" :class="{ active: selectedSection === 'attendance' }" href="#attendance" @click.prevent="selectedSection = 'attendance'">Attendance</a>
      </nav>
      <div class="logistics-sidebar__footer">
        <button class="logistics-sidebar__account" type="button" @click="openProfileInfo">Account Info</button>
        <button class="logistics-sidebar__logout" type="button" @click="askLogout">Logout</button>
      </div>
    </aside>

    <div class="logistics-workspace">
      <header class="logistics-topbar">
        <button class="logistics-menu-button" type="button" :aria-label="sidebarCollapsed ? 'Show logistics menu' : 'Hide logistics menu'" :aria-expanded="(!sidebarCollapsed).toString()" @click="sidebarCollapsed = !sidebarCollapsed">☰</button>
        <div class="logistics-topbar__spacer"></div>
        <div class="header-profile-wrapper">
          <div class="header-profile-btn" aria-label="Current account">
            <div class="header-avatar">
              <div v-if="profile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url(' + profile.avatarUrl + ')' }"></div>
              <div v-else class="header-avatar-initials">{{ (profile.full_name || profile.fullName || 'M').charAt(0) }}</div>
            </div>
            <div class="header-name">Logistics - {{ profile.branch_name || profile.branch || 'Main Branch' }}</div>
          </div>
        </div>
      </header>

      <section class="panel-layout">
      <!-- Left profile column removed for Main Branch layout -->

      <main class="main-col">
        <header id="logistics-overview" class="panel-header logistics-feature-header">
          <div>
            <p class="logistics-eyebrow">Logistics dashboard</p>
            <h1>Main Branch Logistics Dashboard</h1>
          </div>
          <button class="logistics-refresh-button" type="button" @click="refreshDashboard" :disabled="isRefreshing">
            {{ isRefreshing ? 'Loading...' : 'Refresh' }}
          </button>
        </header>

        <Transition name="logistics-section" mode="out-in">
        <div :key="selectedSection" class="logistics-section-view">
        <div v-if="selectedSection === 'attendance'" class="logistics-attendance-view">
          <section id="attendance" class="attendance-card logistics-attendance-card">
            <div class="attendance-header">
              <span class="attendance-title">Attendance</span>
              <span :class="['attendance-status-badge', attendanceStatus.is_clocked_in ? 'status-on-duty' : 'status-off-duty']">
                {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
              </span>
            </div>

            <div class="attendance-times" v-if="attendanceStatus.clock_in_time || attendanceStatus.clock_out_time">
              <div class="time-row"><span class="time-label">Clock In:</span><span class="time-value">{{ attendanceStatus.clock_in_time || '-' }}</span></div>
              <div class="time-row"><span class="time-label">Clock Out:</span><span class="time-value">{{ attendanceStatus.clock_out_time || '-' }}</span></div>
              <div class="time-row" v-if="attendanceStatus.hours_worked > 0"><span class="time-label">Hours:</span><span class="time-value">{{ attendanceStatus.hours_worked }} hrs</span></div>
            </div>

            <div class="attendance-buttons">
              <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockInGeofencing || locationLoading" class="btn-clock-in">
                {{ (isAttendanceProcessing || locationLoading) ? '...' : 'Clock In' }}
              </button>
              <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut || !canClockInGeofencing || locationLoading" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">
                {{ (isAttendanceProcessing || locationLoading) ? '...' : 'Clock Out' }}
              </button>
            </div>

            <div v-if="locationError" class="geofencing-status geofencing-error"><span class="status-icon">!</span><span>{{ locationError }}</span></div>
            <div v-else-if="userLocation && canClockInGeofencing" class="geofencing-status geofencing-success"><span class="status-icon">✓</span><span>Location verified</span></div>
            <div v-else-if="!canClockInGeofencing && geofencingMessage" class="geofencing-status geofencing-error"><span class="status-icon">LOCK</span><span>{{ geofencingMessage }}</span></div>

            <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction"><span class="restriction-icon">LOCK</span><span>Cannot clock out before {{ scheduledTimeOut }}</span></div>
            <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
          </section>
        </div>
        <section v-if="selectedSection === 'overview'" class="overview-grid">
          <article class="overview-card"><span class="k">Active Products</span><strong>{{ metrics.active_products }}</strong></article>
          <article class="overview-card"><span class="k">Low Stock</span><strong>{{ metrics.low_stock }}</strong></article>
          <article class="overview-card" :class="{ 'stat-alert': pendingDeliveriesCount > 0 }">
            <span class="k">Pending Deliveries</span>
            <strong>{{ metrics.pending_deliveries }}</strong>
            <span v-if="pendingDeliveriesCount > 0" class="panel-badge">{{ pendingDeliveriesCount }}</span>
          </article>
          <article class="overview-card"><span class="k">Suppliers</span><strong>{{ metrics.suppliers }}</strong></article>
        </section>


        <section v-if="selectedSection === 'overview' || selectedSection === 'inventory'" id="inventory-monitor" class="panel-section">
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
        </section>

          <section v-if="selectedSection === 'overview' || selectedSection === 'product-requests'" id="product-requests" class="panel-section">
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
                      <button class="link-btn" @click="toggleProductRequestDetails(r.id)">
                        {{ expandedProductRequestId === r.id ? 'Hide details' : 'View details' }}
                      </button>
                      <button class="action-btn" @click="approvePendingRequest(r.id)">Approve</button>
                      <button class="link-btn" style="margin-left:8px;background:#ef4444" @click="rejectPendingRequest(r.id)">Reject</button>
                    </td>
                  </tr>
                  <template v-for="r in pendingProductRequests" :key="`${r.id}-details`">
                    <tr v-if="expandedProductRequestId === r.id" class="product-request-details-row">
                      <td colspan="5">
                        <div class="product-request-details">
                          <div class="product-request-detail"><strong>Category</strong><span>{{ r.category || 'Not provided' }}</span></div>
                          <div class="product-request-detail"><strong>Brand</strong><span>{{ r.brand || 'Not provided' }}</span></div>
                          <div class="product-request-detail product-request-detail--wide"><strong>Description</strong><span>{{ r.description || 'Not provided' }}</span></div>
                          <div class="product-request-detail product-request-detail--wide"><strong>Reason</strong><span>{{ r.reason || 'Not provided' }}</span></div>
                          <div class="product-request-detail"><strong>Target audience</strong><span>{{ r.target_audience || 'Not provided' }}</span></div>
                          <div class="product-request-detail"><strong>Storage requirements</strong><span>{{ r.storage_requirements || 'Not provided' }}</span></div>
                          <div class="product-request-detail"><strong>Product type</strong><span>{{ r.is_perishable ? 'Perishable' : 'Non-perishable' }}</span></div>
                          <div class="product-request-detail"><strong>Unit</strong><span>{{ r.unit || 'Not provided' }}</span></div>
                          <div class="product-request-detail"><strong>Selected supplier</strong><span>{{ r.procurement_request?.supplier?.full_name || r.procurement_request?.supplier?.username || 'Not selected' }}</span></div>
                          <div class="product-request-detail"><strong>Supplier price</strong><span>{{ r.supplier_price ? formatPrice(r.supplier_price) : 'Pending supplier quote' }}</span></div>
                          <div class="product-request-detail"><strong>Branch markup</strong><span>{{ r.markup_percentage }}%</span></div>
                          <div class="product-request-detail"><strong>Expected selling price</strong><span>{{ r.expected_selling_price ? formatPrice(r.expected_selling_price) : 'Pending supplier quote' }}</span></div>
                          <div class="product-request-detail"><strong>Expected profit</strong><span>{{ r.expected_profit ? formatPrice(r.expected_profit) : 'Pending supplier quote' }}</span></div>
                        </div>
                      </td>
                    </tr>
                  </template>
                  <tr v-if="pendingProductRequests.length === 0">
                    <td colspan="5" class="empty-message">No product requests awaiting logistics approval.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

        <section v-if="selectedSection === 'overview' || selectedSection === 'suppliers'" id="suppliers" class="panel-section">
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
        </div>
        </Transition>
      </main>

      </section>
    </div>

    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box logout-confirm-dialog">
          <h3>Logout from Main Branch Logistics Panel?</h3>
          <p>This will end your current session for Chikin Tayo.</p>
          <div class="logout-actions">
            <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
            <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showAccountInfoModal" class="logistics-account-backdrop" @click.self="showAccountInfoModal = false">
        <div class="logistics-account-modal">
          <div class="logistics-account-header">
            <h3>Account Information</h3>
            <button class="logistics-account-close" type="button" aria-label="Close account information" @click="showAccountInfoModal = false">✕</button>
          </div>
          <div class="logistics-account-body">
            <div class="logistics-account-row"><span>Name:</span><strong>{{ profile.fullName || profile.full_name || 'N/A' }}</strong></div>
            <div class="logistics-account-row"><span>Email:</span><strong>{{ profile.email || 'N/A' }}</strong></div>
            <div class="logistics-account-row"><span>Role:</span><strong>{{ profile.role || profile.position || 'N/A' }}</strong></div>
            <div class="logistics-account-row"><span>Department:</span><strong>{{ profile.department || 'N/A' }}</strong></div>
            <div class="logistics-account-row"><span>Branch:</span><strong>{{ profile.branch_name || profile.branch || 'N/A' }}</strong></div>
            <div class="logistics-account-row"><span>Phone:</span><strong>{{ profile.phone || profile.contact || 'N/A' }}</strong></div>
            <div class="logistics-account-row"><span>Position:</span><strong>{{ profile.position || 'N/A' }}</strong></div>
            <div class="logistics-account-row"><span>Start Date:</span><strong>{{ formatDate(profile.hire_date || profile.created_at) || 'N/A' }}</strong></div>
          </div>
          <div class="logistics-account-footer">
            <button type="button" @click="showAccountInfoModal = false">Close</button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showLogoutOverlay" class="logistics-loading-overlay">
        <div class="logistics-logo-loading-box">
          <img :src="logoImg" alt="Chikin Tayo" class="logistics-logo-loading-img" />
          <p>Logging out...</p>
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

const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

const router = useRouter()
const profile = ref({})
const selectedSection = ref('overview')
const sidebarCollapsed = ref(false)
const showAccountInfoModal = ref(false)
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showLogoutOverlay = ref(false)
const metrics = ref({ active_products: 0, low_stock: 0, pending_deliveries: 0, suppliers: 0 })
// Inventory / procurement state (read-only on Main Branch)
const inventory = ref([])
const inventoryLoading = ref(false)
const inventoryError = ref('')

const procurementRequests = ref([])
const procRequestsLoading = ref(false)

// Product requests pending logistics approval
const pendingProductRequests = ref([])
const expandedProductRequestId = ref(null)
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

function toggleProductRequestDetails(id) {
  expandedProductRequestId.value = expandedProductRequestId.value === id ? null : id
}

// Header profile dropdown state (compact header in profile column)
function openProfileInfo() { showAccountInfoModal.value = true }

// Branch selector for Main Branch HQ users
const branches = ref([])
const selectedBranch = ref(null)
const branchesLoading = ref(false)
const branchesError = ref('')
const isRefreshing = ref(false)

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  showLogoutOverlay.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    window.location.replace('/admin-login')
  }, 600)
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

async function refreshDashboard() {
  if (isRefreshing.value) return
  isRefreshing.value = true
  try {
    await Promise.all([
      loadMetrics(),
      fetchInventory(),
      fetchPendingProductRequests(),
      fetchSuppliers()
    ])
  } finally {
    isRefreshing.value = false
  }
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
.product-request-details-row td { background: rgba(255, 248, 240, 0.72); }
.product-request-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 20px; padding: 6px 4px; }
.product-request-detail { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.product-request-detail strong { color: #5a2c0a; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; }
.product-request-detail span { color: #3f3f46; overflow-wrap: anywhere; }
.product-request-detail--wide { grid-column: 1 / -1; }

.status-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
.status-ok { background: rgba(46, 204, 113, 0.12); color: #27ae60; }
.status-low { background: rgba(231, 76, 60, 0.12); color: #e74c3c; }
.status-approved { background: rgba(46, 204, 113, 0.12); color: #27ae60; }
.status-pending { background: rgba(241, 196, 15, 0.12); color: #f39c12; }

@media (max-width: 700px) {
  .product-request-details { grid-template-columns: 1fr; }
  .product-request-detail--wide { grid-column: auto; }
}

/* Finance manager visual language, scoped to this panel only. */
.main-branch-page {
  display: grid;
  grid-template-columns: 120px minmax(0, 1fr);
  gap: 0;
  padding: 0;
  background: #efe5dc;
  color: #172a3d;
  overflow-x: hidden;
}

.logistics-sidebar {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 1.5rem 1rem 1rem;
  gap: 1.2rem;
  background: #f2e9e1;
  border-right: 1px solid rgba(97, 72, 51, 0.1);
}

.logistics-sidebar__nav,
.logistics-sidebar__footer { display: grid; gap: 0.75rem; }
.logistics-sidebar__item,
.logistics-sidebar__account,
.logistics-sidebar__logout {
  display: block;
  width: 100%;
  padding: 10px 9px;
  border: 1px solid transparent;
  border-radius: 9px;
  background: transparent;
  color: #29384a;
  font-family: inherit;
  font-size: 0.78rem;
  font-weight: 600;
  text-align: left;
  text-decoration: none;
  cursor: pointer;
  transition: background-color .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
}
.logistics-sidebar__item:hover,
.logistics-sidebar__account:hover,
.logistics-sidebar__logout:hover { transform: translateX(2px); background: rgba(255, 255, 255, .72); }
.logistics-sidebar__item.active {
  background: #fffaf5;
  border-color: #f1b986;
  box-shadow: 0 5px 12px rgba(113, 77, 41, .08);
  color: #111827;
}
.logistics-sidebar__account,
.logistics-sidebar__logout { border-radius: 12px; text-align: center; }
.logistics-sidebar__account { background: #f5fbff; border-color: #c9e0eb; color: #30445a; }
.logistics-sidebar__logout { background: #fff8f5; border-color: #f1c2b2; color: #a23d32; }

.logistics-workspace { min-width: 0; }
.logistics-topbar {
  min-height: 68px;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 0 20px;
  background: rgba(255, 250, 246, .92);
  border-bottom: 1px solid rgba(111, 78, 49, .1);
  box-shadow: 0 8px 24px rgba(83, 57, 37, .14);
  position: relative;
  z-index: 4;
}
.logistics-menu-button {
  width: 28px;
  height: 28px;
  border: 1px solid #d9e0e5;
  border-radius: 8px;
  background: #f7fafb;
  color: #506273;
  cursor: pointer;
  transition: background-color .2s ease, transform 260ms cubic-bezier(.22, 1, .36, 1), box-shadow .2s ease;
}
.logistics-menu-button:hover { background: #fff; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(36, 52, 71, .12); }
.main-branch-page.sidebar-collapsed .logistics-menu-button { transform: rotate(180deg); }
.main-branch-page.sidebar-collapsed .logistics-menu-button:hover { transform: rotate(180deg) translateY(-1px); }
.logistics-topbar__spacer { flex: 1; }
.header-profile-wrapper { position: relative; }
.header-profile-btn {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.45rem 0.75rem;
  border: 1px solid #eadbd0;
  border-radius: 999px;
  background: #fffaf7;
  color: #26354a;
  font-family: inherit;
  font-size: 0.82rem;
  font-weight: 600;
  line-height: 1.2;
  box-shadow: 0 4px 12px rgba(83, 57, 37, .07);
}
.header-avatar { width: 26px; height: 26px; border-radius: 50%; background: #f4d8b9; display: grid; place-items: center; overflow: hidden; }
.header-avatar-initials { color: #8d4d28; font-size: 11px; font-weight: 800; }
.header-avatar-img { width: 100%; height: 100%; background-position: center; background-size: cover; }
.header-name { white-space: nowrap; }

.panel-layout { grid-template-columns: minmax(0, 1fr) 200px; gap: 18px; padding: 28px 20px 40px; }
.main-col { gap: 18px; }
.panel-header { padding: 0 2px 8px; background: transparent; border: 0; box-shadow: none; }
.panel-header h1 { font-size: clamp(29px, 3vw, 39px); letter-spacing: -1px; color: #172a3d; animation: logistics-title-in .55s ease both; }
.panel-header p { color: #886f60; }
.overview-card, .panel-block, .attendance-card { border: 1px solid rgba(227, 209, 194, .8); box-shadow: 0 7px 18px rgba(97, 69, 45, .08); }
.overview-card { background: #fffaf7; transition: transform .2s ease, box-shadow .2s ease; }
.overview-card:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(97, 69, 45, .13); }
.overview-card .k, .section-description { color: #987b69; }
.overview-card strong { color: #26354a; }
.panel-section { scroll-margin-top: 86px; }
.main-col > .panel-section {
  padding: 20px;
  border: 1px solid rgba(226, 232, 240, .9);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 8px 20px rgba(83, 57, 37, .1);
}
.main-col > .panel-section + .panel-section { margin-top: 2px; }
.section-title { color: #172a3d; }
.side-col { top: 86px; }
.panel-block { background: #fffaf7; }
.data-table th { background: #fbf1e9; color: #81502e; }
.data-table td { border-bottom-color: rgba(141, 111, 89, .14); }
.data-table tbody tr { transition: background-color .18s ease; }
.data-table tbody tr:hover { background: rgba(255, 246, 238, .8); }
.action-btn, .link-btn { background: #e8752d; box-shadow: 0 7px 16px rgba(232, 117, 45, .2); transition: transform .2s ease, filter .2s ease; }
.action-btn:hover, .link-btn:hover { filter: brightness(1.04); transform: translateY(-1px); }
.status-ok { background: #d9f1e6; color: #168254; }
.status-low { background: #f8dddd; color: #d55b58; }
.loading-container, .error-container { animation: logistics-fade-up .35s ease both; }
.main-col > .overview-grid { animation: logistics-fade-up .45s .08s ease both; }
.main-col > .panel-section { animation: logistics-fade-up .45s .14s ease both; }

@keyframes logistics-title-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
@keyframes logistics-fade-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 1100px) {
  .main-branch-page { grid-template-columns: 92px minmax(0, 1fr); }
  .panel-layout { grid-template-columns: 1fr; }
  .side-col { position: static; grid-row: auto; }
}
@media (max-width: 620px) {
  .main-branch-page { display: block; }
  .logistics-sidebar { min-height: auto; padding: 10px; }
  .logistics-sidebar__nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .logistics-sidebar__footer { display: none; }
  .logistics-sidebar__item { text-align: center; }
  .logistics-topbar { padding: 0 12px; }
  .header-name { max-width: 155px; overflow: hidden; text-overflow: ellipsis; }
  .panel-layout { padding: 22px 12px 30px; }
  .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

/* Keep the navigation and its account actions anchored while the dashboard scrolls. */
.logistics-sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  width: 120px;
  min-height: 0;
  box-sizing: border-box;
  overflow-y: auto;
  z-index: 10;
}
.main-branch-page { display: block; }
.logistics-workspace { margin-left: 120px; }

@media (max-width: 1100px) {
  .logistics-sidebar { width: 92px; }
  .logistics-workspace { margin-left: 92px; }
}
@media (max-width: 620px) {
  .logistics-sidebar {
    position: static;
    width: auto;
    min-height: auto;
    overflow: visible;
  }
  .logistics-workspace { margin-left: 0; }
}

/* Match the Finance Manager rail width and let logistics use the full workspace. */
.logistics-sidebar { width: 156px; }
.logistics-sidebar__item,
.logistics-sidebar__account,
.logistics-sidebar__logout {
  padding: 0.7rem 0.75rem;
  font-size: 0.78rem;
  font-weight: 600;
}
.logistics-sidebar__footer {
  width: 100%;
  padding-top: 1rem;
  border-top: 1px solid rgba(138, 113, 95, .16);
}
.logistics-workspace { margin-left: 156px; }
.panel-layout { grid-template-columns: minmax(0, 1fr); }

@media (max-width: 768px) {
  .logistics-sidebar {
    position: static;
    width: auto;
    min-height: auto;
    padding: 12px;
    overflow: visible;
  }
  .logistics-workspace { margin-left: 0; }
  .logistics-sidebar__nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .logistics-sidebar__footer { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .panel-layout { padding: 22px 14px 32px; }
  .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 460px) {
  .logistics-sidebar__nav,
  .logistics-sidebar__footer,
  .overview-grid { grid-template-columns: 1fr; }

.logistics-sidebar {
  transition: width 260ms cubic-bezier(.22, 1, .36, 1), padding 260ms cubic-bezier(.22, 1, .36, 1), border-color 260ms ease, opacity 180ms ease;
}
.logistics-workspace { transition: margin-left 260ms cubic-bezier(.22, 1, .36, 1); }
.logistics-topbar { transition: left 260ms cubic-bezier(.22, 1, .36, 1); }
.main-branch-page.sidebar-collapsed .logistics-sidebar {
  width: 0;
  padding-right: 0;
  padding-left: 0;
  border-right-color: transparent;
  opacity: 0;
  overflow: hidden;
  pointer-events: none;
}
.main-branch-page.sidebar-collapsed .logistics-workspace { margin-left: 0; }
.main-branch-page.sidebar-collapsed .logistics-topbar { left: 0; }

.logistics-section-enter-active,
.logistics-section-leave-active {
  transition: opacity 220ms ease, transform 220ms cubic-bezier(.22, 1, .36, 1);
}
.logistics-section-enter-from { opacity: 0; transform: translateY(10px); }
.logistics-section-leave-to { opacity: 0; transform: translateY(-6px); }
  .logistics-topbar { min-height: 60px; padding: 0 12px; }
  .header-name { max-width: 145px; }
  .panel-header h1 { font-size: 29px; }
  .data-table th, .data-table td { padding: 10px 8px; font-size: 12px; }
}

.logistics-feature-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 0.2rem 0.2rem 0.6rem;
}
.logistics-feature-header .logistics-eyebrow {
  margin: 0 0 0.2rem;
  color: #b66a3e;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.68rem;
  font-weight: 700;
}
.logistics-feature-header h1 {
  margin: 0;
  color: #1f2937;
  font-size: clamp(2rem, 2vw, 2.2rem);
  font-weight: 800;
  letter-spacing: 0;
}
.logistics-feature-header > div > p:last-child {
  margin: 0.35rem 0 0;
  color: #886f60;
  max-width: 54ch;
}
.logistics-refresh-button {
  border: 1px solid #243447;
  background: #243447;
  color: #fff;
  border-radius: 12px;
  padding: 0.72rem 1rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 8px 18px rgba(36, 52, 71, .15);
  transition: background-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.logistics-refresh-button:hover:not(:disabled) {
  background: #172536;
  border-color: #172536;
  box-shadow: 0 10px 22px rgba(36, 52, 71, .2);
  transform: translateY(-1px);
}
.logistics-refresh-button:disabled { cursor: wait; opacity: .72; }

@media (max-width: 460px) {
  .logistics-feature-header { align-items: flex-start; flex-direction: column; }
  .logistics-refresh-button { align-self: stretch; }
}

/* Match the Finance Manager canvas and keep the topbar outside page scrolling. */
.main-branch-page { background: #e7d9cf; }
.logistics-sidebar { background: rgba(255, 255, 255, .42); border-right-color: rgba(115, 93, 84, .18); }
.logistics-workspace { padding-top: 68px; }
.logistics-topbar {
  position: fixed;
  top: 0;
  right: 0;
  left: 156px;
  min-height: 68px;
  box-sizing: border-box;
  background: linear-gradient(180deg, #e7d9cf 0%, #eee5df 100%);
  border-bottom-color: rgba(148, 163, 184, .18);
  box-shadow: 0 10px 18px rgba(15, 23, 42, .12);
  z-index: 20;
}

@media (max-width: 768px) {
  .logistics-workspace { padding-top: 60px; }
  .logistics-topbar { left: 0; min-height: 60px; }
}

/* Keep the burger behavior active outside the mobile media query as well. */
.logistics-sidebar {
  transition: width 260ms cubic-bezier(.22, 1, .36, 1), padding 260ms cubic-bezier(.22, 1, .36, 1), border-color 260ms ease, opacity 180ms ease;
}
.logistics-workspace { transition: margin-left 260ms cubic-bezier(.22, 1, .36, 1); }
.logistics-topbar { transition: left 260ms cubic-bezier(.22, 1, .36, 1); }
.main-branch-page.sidebar-collapsed .logistics-sidebar {
  width: 0;
  padding-right: 0;
  padding-left: 0;
  border-right-color: transparent;
  opacity: 0;
  overflow: hidden;
  pointer-events: none;
}
.main-branch-page.sidebar-collapsed .logistics-workspace { margin-left: 0; }
.main-branch-page.sidebar-collapsed .logistics-topbar { left: 0; }

/* The animated view wrapper sits between main-col and each selected section. */
.logistics-section-view > .panel-section {
  padding: 20px;
  border: 1px solid rgba(226, 232, 240, .9);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 8px 20px rgba(83, 57, 37, .1);
}
.logistics-section-view > .panel-section + .panel-section { margin-top: 18px; }

.logistics-attendance-view { width: min(100%, 760px); margin: 0 auto 1.25rem; }
.logistics-attendance-card {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  margin: 0;
  padding: 0.85rem 0.95rem;
  border: 1px solid rgba(219, 188, 160, .45);
  border-radius: 14px;
  background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(255, 249, 242, .95));
  box-shadow: 0 14px 34px rgba(16, 24, 40, .06);
}
.logistics-attendance-card .attendance-header { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
.logistics-attendance-card .attendance-title { color: #3d2a1f; font-size: .98rem; font-weight: 800; }
.logistics-attendance-card .attendance-status-badge { display: inline-flex; align-items: center; justify-content: center; padding: .35rem .75rem; border-radius: 999px; font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
.logistics-attendance-card .status-on-duty { background: rgba(34, 197, 94, .14); color: #15803d; }
.logistics-attendance-card .status-off-duty { background: rgba(239, 68, 68, .12); color: #b91c1c; }
.logistics-attendance-card .attendance-times { display: grid; gap: .45rem; font-size: .88rem; }
.logistics-attendance-card .time-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.logistics-attendance-card .time-label { color: #7c6758; }
.logistics-attendance-card .time-value { color: #3d2a1f; font-weight: 700; }
.logistics-attendance-card .attendance-buttons { display: flex; gap: .6rem; flex-wrap: wrap; }
.logistics-attendance-card .btn-clock-in,
.logistics-attendance-card .btn-clock-out { flex: 1 1 8rem; padding: .65rem .85rem; border: 1px solid #243447; border-radius: 10px; color: #fff; font-weight: 700; cursor: pointer; }
.logistics-attendance-card .btn-clock-in { background: #243447; }
.logistics-attendance-card .btn-clock-out { border-color: #b96b63; background: #b96b63; }
.logistics-attendance-card .btn-clock-in:disabled,
.logistics-attendance-card .btn-clock-out:disabled { opacity: .7; cursor: not-allowed; }
.logistics-attendance-card .geofencing-status { display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .72rem .85rem; border-radius: 8px; font-size: .8rem; }
.logistics-attendance-card .geofencing-success { border: 1px solid rgba(34, 197, 94, .3); background: rgba(34, 197, 94, .12); color: #15803d; }
.logistics-attendance-card .geofencing-error { border: 1px solid rgba(239, 68, 68, .3); background: rgba(239, 68, 68, .12); color: #b91c1c; }

/* Finance Manager-style animated view switch. */
.logistics-section-enter-active,
.logistics-section-leave-active {
  transition: opacity 220ms ease, transform 220ms cubic-bezier(.22, 1, .36, 1);
}
.logistics-section-enter-from { opacity: 0; transform: translateY(10px); }
.logistics-section-leave-to { opacity: 0; transform: translateY(-6px); }

.logistics-account-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(0, 0, 0, .42);
}
.logistics-account-modal {
  width: min(90vw, 520px);
  max-height: 80vh;
  overflow: hidden;
  border: 1px solid rgba(219, 188, 160, .45);
  border-radius: 20px;
  background: linear-gradient(180deg, #ffffff 0%, #fef8f3 100%);
  box-shadow: 0 28px 72px rgba(15, 23, 42, .18);
}
.logistics-account-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(219, 188, 160, .35);
  background: linear-gradient(180deg, rgba(255, 255, 255, .6), rgba(255, 248, 241, .4));
}
.logistics-account-header h3 { margin: 0; color: #3d2a1f; font-size: 1.15rem; font-weight: 800; }
.logistics-account-close {
  border: 0;
  background: transparent;
  color: #a6785e;
  font-size: 1.5rem;
  cursor: pointer;
}
.logistics-account-body { flex: 1; overflow-y: auto; padding: 0.75rem 1.5rem; }
.logistics-account-row {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  padding: 0.65rem 0;
  border-bottom: 1px solid rgba(219, 188, 160, .24);
  color: #977b6b;
  font-size: 0.9rem;
}
.logistics-account-row strong { color: #3d2a1f; text-align: right; font-size: 0.95rem; font-weight: 700; word-break: break-word; }
.logistics-account-footer { display: flex; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid rgba(219, 188, 160, .3); }
.logistics-account-footer button {
  border: 1px solid rgba(100, 116, 139, .3);
  border-radius: 999px;
  padding: 6px 14px;
  background: #64748b;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: none;
  transition: background-color .16s ease, transform .16s ease;
}
.logistics-account-footer button:hover { background: #525c6a; transform: translateY(-1px); }

.logistics-loading-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, .35);
  backdrop-filter: blur(4px);
  z-index: 501;
}
.logistics-logo-loading-box {
  min-width: 168px;
  display: block;
  padding: 12px 18px 14px;
  border-radius: 12px;
  background: rgba(255, 255, 255, .95);
  box-shadow: 0 20px 50px rgba(0, 0, 0, .18);
  text-align: center;
}
.logistics-logo-loading-img {
  width: 80px;
  height: auto;
  display: block;
  margin: 0 auto 8px;
  animation: logistics-logo-bounce .8s ease-in-out infinite;
}
.logistics-logo-loading-box p { margin: 0; color: #6b6b6b; font-size: .9rem; font-weight: 500; }
@keyframes logistics-logo-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}
</style>
