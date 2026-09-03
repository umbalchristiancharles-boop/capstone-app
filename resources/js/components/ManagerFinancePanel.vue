<template>
  <div class="manager-finance finance-manager-layout" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
    <aside class="finance-sidebar" :aria-hidden="sidebarCollapsed">
      <nav class="finance-sidebar__nav">
        <button class="finance-sidebar__item" :class="{ active: selectedSection === 'overview' }" @click="selectedSection = 'overview'">
          <span class="finance-sidebar__label">Financial Overview</span>
        </button>
        <button class="finance-sidebar__item" :class="{ active: selectedSection === 'markup' }" @click="selectedSection = 'markup'">
          <span class="finance-sidebar__label">Price Markup</span>
        </button>
        <button class="finance-sidebar__item" :class="{ active: selectedSection === 'approvals' }" @click="selectedSection = 'approvals'">
          <span class="finance-sidebar__label">Approvals</span>
        </button>
        <button class="finance-sidebar__item" :class="{ active: selectedSection === 'transactions' }" @click="selectedSection = 'transactions'">
          <span class="finance-sidebar__label">Transactions</span>
        </button>
        <button class="finance-sidebar__item" :class="{ active: selectedSection === 'attendance' }" @click="selectedSection = 'attendance'">
          <span class="finance-sidebar__label">Attendance</span>
        </button>
      </nav>

      <div class="finance-sidebar__footer">
        <button class="finance-sidebar__account-info" @click="showAccountInfoModal = true" aria-label="Account Info">
          <span class="finance-sidebar__account-text">Account Info</span>
        </button>
        <button class="finance-sidebar__logout" @click="askLogout" aria-label="Logout">
          <span class="finance-sidebar__logout-text">Logout</span>
        </button>
      </div>
    </aside>

    <div class="finance-main-panel">
      <header class="finance-main-header">
        <button
          class="finance-hamburger"
          :aria-label="sidebarCollapsed ? 'Show menu' : 'Hide menu'"
          :aria-expanded="(!sidebarCollapsed).toString()"
          @click="sidebarCollapsed = !sidebarCollapsed"
        >☰</button>
        <div class="finance-header__spacer"></div>
        <div class="finance-header__actions">
          <div class="finance-user-pill" @click.stop>
            <div class="finance-user-pill__avatar">{{ userInitial }}</div>
            <span>{{ userProfile?.fullName || userProfile?.full_name || 'Manager' }}</span>
          </div>
        </div>
      </header>

      <main class="finance-dashboard-body">
        <section class="finance-feature-header">
          <div>
            <p class="finance-eyebrow">Finance dashboard</p>
            <h2 class="finance-title">{{ getSectionTitle }}</h2>
          </div>
          <button class="finance-refresh-button" @click="refreshDashboard" :disabled="budgetLoading">
            {{ budgetLoading ? 'Loading...' : 'Refresh' }}
          </button>
        </section>

        <Transition name="finance-section" mode="out-in">
        <div :key="selectedSection" class="finance-section-view">
        <div v-if="selectedSection === 'attendance'" class="finance-main-attendance-card">
          <section class="attendance-card">
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

            <div v-if="locationError" class="geofencing-status geofencing-error">
              <span class="status-icon">⚠️</span>
              <span>{{ locationError }}</span>
            </div>
            <div v-else-if="userLocation && canClockInGeofencing" class="geofencing-status geofencing-success">
              <span class="status-icon">✓</span>
              <span>Location verified</span>
            </div>
            <div v-else-if="!canClockInGeofencing && geofencingMessage" class="geofencing-status geofencing-error">
              <span class="status-icon">🔒</span>
              <span>{{ geofencingMessage }}</span>
            </div>

            <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction">
              <span class="restriction-icon">LOCK</span>
              <span>Cannot clock out before {{ scheduledTimeOut }}</span>
            </div>

            <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
          </section>
        </div>

        <!-- FINANCIAL OVERVIEW SECTION -->
        <template v-if="selectedSection === 'overview'">
          <div class="filter-bar">
            <div class="filter-group">
              <label>Branch:</label>
              <select v-model="selectedBranchId" @change="onBranchChange">
                <option value="">All Branches</option>
                <option v-for="branch in availableBranches" :key="branch.id" :value="branch.id">
                  {{ branch.name }}
                </option>
              </select>
            </div>
            <div class="filter-group filter-group--range">
              <label>Date Range:</label>
              <select v-model="selectedRange" @change="onRangeChange" class="range-select">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="thisWeek">This Week</option>
                <option value="thisMonth">This Month</option>
                <option value="lastMonth">Last Month</option>
                <option value="custom">Custom Range</option>
                <option value="all">All Time</option>
              </select>
              <div v-if="selectedRange === 'custom'" class="custom-date-range">
                <input type="date" v-model="customStartDate" @change="onCustomDateChange" :max="customEndDate || today" class="date-input" />
                <span class="date-separator">to</span>
                <input type="date" v-model="customEndDate" @change="onCustomDateChange" :min="customStartDate" :max="today" class="date-input" />
              </div>
            </div>
          </div>

          <div v-if="budgetLoading" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Loading data...</p>
          </div>

          <div v-else class="finance-overview-content">
            <div class="kpi-grid">
              <div class="kpi-card">
                <div class="kpi-icon revenue-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="Peso">
                    <text x="12" y="16" text-anchor="middle" font-size="14" fill="currentColor" font-family="Segoe UI Symbol, Noto Sans Symbols, Arial Unicode MS">₱</text>
                  </svg>
                </div>
                <div class="kpi-content">
                  <span class="kpi-label">Total Sales</span>
                  <span class="kpi-value">{{ dashboardTotals.totalSales || '₱0' }}</span>
                </div>
              </div>

              <div class="kpi-card">
                <div class="kpi-icon orders-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                  </svg>
                </div>
                <div class="kpi-content">
                  <span class="kpi-label">Total Orders</span>
                  <span class="kpi-value">{{ dashboardTotals.totalOrders ?? (transactions.length || 0) }}</span>
                </div>
              </div>

              <div class="kpi-card" :class="{ 'stat-alert': financePendingCount > 0 }">
                <div class="kpi-icon expenses-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                  </svg>
                </div>
                <div class="kpi-content">
                  <span class="kpi-label">Pending Approvals</span>
                  <span class="kpi-value">{{ pendingBudgetCount }}</span>
                </div>
                <span v-if="financePendingCount > 0" class="panel-badge">{{ financePendingCount }}</span>
              </div>

              <div class="kpi-card highlight">
                <div class="kpi-icon profit-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="20" x2="12" y2="10"></line>
                    <line x1="18" y1="20" x2="18" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="16"></line>
                  </svg>
                </div>
                <div class="kpi-content">
                  <span class="kpi-label">Net Revenue</span>
                  <span class="kpi-value">{{ dashboardTotals.revenue || '₱0' }}</span>
                </div>
              </div>
            </div>

            <section class="finance-card finance-card--overview">
              <PriceMarkupManagerPanel
                v-if="userProfile && userProfile.branch_id"
                :branchId="userProfile.branch_id"
                :isMainBranchFinance="props.isMainBranchFinance || isMainBranchFinanceManager"
                :showOverviewCharts="true"
                :showMarkupSection="false"
              />
            </section>
          </div>
        </template>

        <!-- PRICE MARKUP SECTION -->
        <template v-if="selectedSection === 'markup'">
          <section class="finance-card finance-card--section-full">
            <PriceMarkupManagerPanel
              v-if="userProfile && userProfile.branch_id"
              :branchId="userProfile.branch_id"
              :isMainBranchFinance="props.isMainBranchFinance || isMainBranchFinanceManager"
            />
          </section>
        </template>

        <!-- BUDGET APPROVALS SECTION -->
        <template v-if="selectedSection === 'approvals'">
          <section class="finance-card finance-card--section-full">
            <div class="finance-table-header">
              <h3>Budget Request Approvals</h3>
            </div>
            <div v-if="budgetLoading" class="loading-container">
              <div class="loading-spinner"></div>
              <p>Loading budget requests...</p>
            </div>
            <div v-else class="table-container">
              <table class="branch-table data-table">
                <thead>
                  <tr>
                    <th>Date Requested</th>
                    <th>Requester</th>
                    <th>Purpose</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="req in budgetRequests" :key="req.id">
                    <td>{{ formatDate(req.date_requested) }}</td>
                    <td>{{ req.requester_name }}</td>
                    <td>{{ req.purpose }}</td>
                    <td>₱{{ req.requested_amount }}</td>
                    <td>
                      <span :class="['status-badge', getStatusClass(req.status)]">{{ req.status }}</span>
                    </td>
                    <td>
                      <div v-if="req.status === 'Pending'" class="action-buttons">
                        <button class="btn-approve" @click="approveRequest(req.id)" :disabled="processingId === req.id">{{ processingId === req.id ? 'Processing...' : 'Approve' }}</button>
                        <button class="btn-reject" @click="rejectRequest(req.id)" :disabled="processingId === req.id">{{ processingId === req.id ? 'Processing...' : 'Reject' }}</button>
                      </div>
                      <div v-else-if="req.status === 'Approved' && req.purpose && /Procurement Request #\d+/i.test(req.purpose)">
                        <button class="btn-approve" @click="markBudgetGiven(req.id)" :disabled="processingId === req.id">{{ processingId === req.id ? 'Processing...' : 'Budget Given' }}</button>
                      </div>
                      <span v-else class="processed-info">{{ req.status }} by {{ req.processed_by || 'Unknown' }}<br><small>{{ formatDate(req.date_processed) }}</small></span>
                    </td>
                  </tr>
                  <tr v-if="budgetRequests.length === 0">
                    <td colspan="6" class="empty-message">No budget requests found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </template>

        <!-- RECENT TRANSACTIONS SECTION -->
        <template v-if="selectedSection === 'transactions'">
          <section class="finance-card finance-card--section-full">
            <finance-panel-content :reports="financeReports" :transactions="transactions" />
          </section>
        </template>
        </div>
        </Transition>
      </main>
    </div>

    <transition name="fade">
      <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
        <div class="logout-confirm-box">
          <h3>Logout from Finance Manager Panel?</h3>
          <p>This will end your current session for Chikin Tayo.</p>
          <div class="logout-actions">
            <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
            <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showOverlay" class="loading-overlay">
        <div class="logo-loading-box">
          <img :src="logoImg" alt="Chikin Tayo" class="logo-loading-img" />
          <p>{{ overlayText }}</p>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="receiptModalVisible" class="modal-backdrop" @click.self="closeReceiptPreview">
        <div class="receipt-preview-modal">
          <div class="modal-header"><h3>Receipt Preview</h3></div>
          <div class="modal-body">
            <div class="preview-meta">
              <div><strong>Request ID:</strong> {{ receiptModalRequest?.id }}</div>
              <div><strong>Product:</strong> {{ receiptModalRequest?.product?.name || '(no product)' }}</div>
              <div><strong>Uploaded:</strong> {{ formatDate(receiptModalRequest?.receipt_uploaded_at) }}</div>
            </div>
            <div class="preview-image">
              <img :src="receiptModalPath" alt="receipt preview" />
            </div>
          </div>
          <div class="modal-footer" style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px">
            <button class="btn-secondary" @click="closeReceiptPreview">Close</button>
            <a :href="receiptModalPath" target="_blank" class="btn-primary">Open in new tab</a>
          </div>
        </div>
      </div>
    </transition>

    <!-- Account Info Modal -->
    <transition name="fade">
      <div v-if="showAccountInfoModal" class="account-info-backdrop" @click.self="showAccountInfoModal = false">
        <div class="account-info-modal">
          <div class="account-info-header">
            <h3>Account Information</h3>
            <button class="account-info-close" @click="showAccountInfoModal = false">✕</button>
          </div>
          <div class="account-info-body">
            <div class="info-section">
              <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ userProfile?.fullName || userProfile?.full_name || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ userProfile?.email || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Role:</span>
                <span class="info-value">{{ userProfile?.role || userProfile?.position || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Department:</span>
                <span class="info-value">{{ userProfile?.department || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Branch:</span>
                <span class="info-value">{{ userProfile?.branch_name || userProfile?.branch || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ userProfile?.phone || userProfile?.contact || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Position:</span>
                <span class="info-value">{{ userProfile?.position || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Start Date:</span>
                <span class="info-value">{{ formatDate(userProfile?.hire_date || userProfile?.created_at) }}</span>
              </div>
              <div class="info-row" v-if="userProfile?.employment_status">
                <span class="info-label">Employment Status:</span>
                <span class="info-value">{{ userProfile?.employment_status }}</span>
              </div>
              <div class="info-row" v-if="userProfile?.address">
                <span class="info-label">Address:</span>
                <span class="info-value">{{ userProfile?.address }}</span>
              </div>
            </div>
          </div>
          <div class="account-info-footer">
            <button class="btn-close" @click="showAccountInfoModal = false">Close</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Face Capture Modal -->
    <div v-if="showFaceCapture" class="face-capture-modal">
      <div class="face-capture-content">
        <h3>Take a Photo for Clock In</h3>
        <p class="face-capture-instruction">Please position your face in the frame and click capture</p>

        <div class="camera-container">
          <video ref="video" autoplay playsinline></video>
          <canvas ref="canvas" style="display: none;"></canvas>
          <div v-if="cameraError" class="camera-error">
            {{ cameraError }}
          </div>
        </div>

        <div class="face-capture-buttons">
          <button @click="capturePhoto" :disabled="isCapturing" class="btn-capture">
            <span v-if="!isCapturing">📸 Capture Photo</span>
            <span v-else>Capturing...</span>
          </button>
          <button @click="cancelFaceCapture" class="btn-cancel">Cancel</button>
        </div>

        <div v-if="capturedImage" class="captured-preview">
          <img :src="capturedImage" alt="Captured face" />
          <p class="preview-label">Photo captured!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted, watch } from 'vue'
import FinancePanelContent from './finance/FinancePanelContent.vue'
import PriceMarkupManagerPanel from './finance/PriceMarkupManagerPanel.vue'
import MainBranchFinanceBranchConfirmations from './MainBranchFinanceBranchConfirmations.vue'
import axios from 'axios'
import { Chart } from 'vue-chartjs'
import { showToast } from './toastStore'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Colors
} from 'chart.js'

// Define props from wrapper components
const props = defineProps({
  isMainBranchFinance: {
    type: Boolean,
    default: false
  }
})

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Colors
)

// Logo image
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

const refreshInterval = ref(null)
const notificationCounts = ref({ finance: 0 })
const hasNotified = ref(false)

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

const showAccountInfoModal = ref(false)

  const attendanceStatus = ref({
    is_clocked_in: false,
    clock_in_time: null,
    clock_out_time: null,
    hours_worked: 0
  })
  const isAttendanceProcessing = ref(false)
  const attendanceMessage = ref('')
  const attendanceMessageType = ref('')
  const attendanceSettings = ref({
    early_clockout_override: false,
    scheduled_time_out: '17:00:00'
  })

  // Dashboard section navigation
  const selectedSection = ref('overview')
  const sidebarCollapsed = ref(false)

  // Face capture state
  const showFaceCapture = ref(false)
  const capturedImage = ref(null)
  const cameraStream = ref(null)
  const cameraError = ref('')
  const isCapturing = ref(false)

// Geofencing state
const userLocation = ref(null)
const locationLoading = ref(false)
const locationError = ref('')
const canClockInGeofencing = ref(true)
const geofencingMessage = ref('')

const userInitial = computed(() => {
  const name = userProfile.value?.fullName || userProfile.value?.full_name || 'U'
  return name.charAt(0).toUpperCase()
})

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

// Helper function to safely extract array from response
const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && Array.isArray(response?.[key])) return response[key]
  if (key && Array.isArray(response?.data?.[key])) return response.data[key]
  if (key && response?.[key]?.data) return response[key].data
  if (key && response?.data?.[key]?.data) return response.data[key].data
  return []
}

const userProfile = ref({})
const dashboardTotals = ref({
  totalSales: '₱0',
  pendingApprovals: 0,
  revenue: '₱0'
})
// Branch budgets
const branches = ref([])
const branchesLoading = ref(true)
const editingBudgetId = ref(null)
const editBudgetValue = ref(null)
const financeReports = ref([])
const transactions = ref([])

// Chart data computed from financeReports
const managerChartData = computed(() => {
  if (!financeReports.value || financeReports.value.length === 0) return { labels: [], datasets: [] }
  const rpt = financeReports.value[0].data || {}
  const labels = rpt.months || []
  return {
    labels,
    datasets: [
      {
        label: 'Revenue',
        data: rpt.income || [],
        borderColor: '#10B981',
        backgroundColor: 'rgba(16,185,129,0.08)',
        tension: 0.25,
        fill: true,
        pointRadius: 3
      },
      {
        label: 'Expenses',
        data: rpt.expenses || [],
        borderColor: '#F59E0B',
        backgroundColor: 'rgba(245,158,11,0.08)',
        tension: 0.25,
        fill: true,
        pointRadius: 3
      },
      {
        label: 'Net Profit',
        data: rpt.netProfit || [],
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59,130,246,0.08)',
        tension: 0.25,
        fill: false,
        pointRadius: 3
      }
    ]
  }
})

const managerChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } },
  interaction: { mode: 'nearest', axis: 'x', intersect: false },
  scales: { x: { display: true }, y: { display: true, beginAtZero: true } }
}

// Available branches computed property - show if user can see multiple branches
const availableBranches = computed(() => {
  return branches.value
})

// Check if user can view multiple branches (OWNER, SUPER_ADMIN, or Main Branch user)
const canViewMultipleBranches = computed(() => {
  const userRole = (userProfile.value?.role || '').toUpperCase()
  const branchName = (userProfile.value?.branch_name || userProfile.value?.branch || '').toUpperCase()
  const isBranchOwner = ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'].includes(userRole)
  const isMainBranchUser = branchName.includes('MAIN')

  return isBranchOwner || isMainBranchUser
})

// Check if user is assigned to Main Branch
const isMainBranchFinanceManager = computed(() => {
  const branchName = (userProfile.value?.branch_name || userProfile.value?.branch || '').toUpperCase()
  const result = branchName.includes('MAIN')
  console.log('[ManagerFinancePanel] isMainBranchFinanceManager check:', {
    branchName,
    result,
    userProfile: userProfile.value
  })
  return result
})

// Get title based on selected section
const getSectionTitle = computed(() => {
  const titles = {
    overview: 'Financial Overview',
    markup: 'Current Price Markup',
    approvals: 'Budget Request Approvals',
    transactions: 'Recent Transactions',
    attendance: 'Attendance'
  }
  return titles[selectedSection.value] || 'Finance Overview'
})

// UI filter state (used by new layout controls)
const selectedRange = ref('all')
const selectedBranchId = ref('')
const customStartDate = ref('')
const customEndDate = ref('')
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Budget requests state
const budgetRequests = ref([])
const budgetLoading = ref(true)
const processingId = ref(null)
// Receipt submissions
const receiptSubmissions = ref([])
const receiptsLoading = ref(false)
const confirmingId = ref(null)
// Receipt preview modal state
const receiptModalVisible = ref(false)
const receiptModalPath = ref('')
const receiptModalRequest = ref(null)

// Computed: count of pending requests
const pendingBudgetCount = computed(() => {
  return budgetRequests.value.filter(r => (r.status || '').toLowerCase() === 'pending').length
})
const financePendingCount = computed(() => {
  const apiPending = Number(notificationCounts.value?.finance || 0)
  const localPending = Number(pendingBudgetCount.value || 0)
  const receiptsPending = (receiptSubmissions.value || []).length
  return Math.max(apiPending, localPending, receiptsPending, 0)
})

watch(financePendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending budget approvals.', 'info')
    hasNotified.value = true
  }
})

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      notificationCounts.value = { finance: Number(res.data.counts?.finance || 0) }
    }
  } catch (e) {
    notificationCounts.value = { finance: 0 }
  }
}

// Handle profile update from layout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  overlayText.value = 'Logging out...'
  showOverlay.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => {
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    try { window.location.replace('/staff-landing') } catch (e) { /* ignore */ }
  }, 600)
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

// Fetch budget requests
async function fetchBudgetRequests() {
  budgetLoading.value = true

  try {
    const response = await axios.get('/api/manager/finance/budget/all', { withCredentials: true })
    if (response.data.ok) {
      budgetRequests.value = response.data.requests
    }
  } catch (err) {
    console.error('Error fetching budget requests:', err)
  } finally {
    budgetLoading.value = false
  }
}

// Fetch branches and budgets
async function fetchBranches() {
  branchesLoading.value = true
  try {
    const res = await axios.get('/api/manager/finance/branches', { withCredentials: true })
    console.log('Branches API Response:', res.data)
    if (res.data && res.data.ok) {
      branches.value = res.data.branches || []
      console.log('Loaded branches:', branches.value)
    }
  } catch (err) {
    console.error('Error fetching branches:', err)
  } finally {
    branchesLoading.value = false
  }
}

function startEditBudget(id, current) {
  editingBudgetId.value = id
  editBudgetValue.value = Number(current || 0).toFixed(2)
}

function cancelEditBudget() {
  editingBudgetId.value = null
  editBudgetValue.value = null
}

async function saveBudget(id) {
  if (editingBudgetId.value !== id) return
  const val = parseFloat(editBudgetValue.value)
  if (Number.isNaN(val)) { alert('Invalid budget amount'); return }
  try {
    const res = await axios.put(`/api/manager/finance/branches/${id}/budget`, { budget: val }, { withCredentials: true })
    if (res.data && res.data.ok) {
      const idx = branches.value.findIndex(b => b.id === id)
      if (idx !== -1) branches.value[idx] = res.data.branch
      cancelEditBudget()
      alert('Budget updated')
    }
  } catch (err) {
    console.error('Failed to save budget:', err)
    alert(err.response?.data?.message || 'Failed to update budget')
  }
}

// Approve budget request
async function approveRequest(id) {
  if (processingId.value) return

  if (!(await window.swalConfirm('Are you sure you want to approve this budget request?'))) {
    return
  }

  processingId.value = id

  try {
    const response = await axios.put(`/api/manager/finance/budget/${id}/approve`, {}, { withCredentials: true })

    if (response.data.ok) {
      // Update the local request status
      const index = budgetRequests.value.findIndex(r => r.id === id)
      if (index !== -1) {
        budgetRequests.value[index].status = 'Approved'
        budgetRequests.value[index].processed_by = response.data.request.processed_by
        budgetRequests.value[index].date_processed = response.data.request.date_processed
      }
      alert('Budget request approved successfully!')
    }
  } catch (err) {
    console.error('Error approving request:', err)
    alert(err.response?.data?.message || 'Failed to approve budget request')
  } finally {
    processingId.value = null
  }
}

// Reject budget request
async function rejectRequest(id) {
  if (processingId.value) return

  if (!(await window.swalConfirm('Are you sure you want to reject this budget request?'))) {
    return
  }

  processingId.value = id

  try {
    const response = await axios.put(`/api/manager/finance/budget/${id}/reject`, {}, { withCredentials: true })

    if (response.data.ok) {
      // Update the local request status
      const index = budgetRequests.value.findIndex(r => r.id === id)
      if (index !== -1) {
        budgetRequests.value[index].status = 'Rejected'
        budgetRequests.value[index].processed_by = response.data.request.processed_by
        budgetRequests.value[index].date_processed = response.data.request.date_processed
      }
      alert('Budget request rejected.')
    }
  } catch (err) {
    console.error('Error rejecting request:', err)
    alert(err.response?.data?.message || 'Failed to reject budget request')
  } finally {
    processingId.value = null
  }
}

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function getStatusClass(status) {
  switch (status) {
    case 'Approved': return 'status-approved'
    case 'Rejected': return 'status-rejected'
    default: return 'status-pending'
  }
}

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
    console.error('Failed to load attendance status:', e)
  }
}

// Geofencing methods
const getUserLocation = async () => {
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
    attendanceSettings.value = { early_clockout_override: false, scheduled_time_out: '17:00:00' }
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

  // Show face capture modal first
  showFaceCapture.value = true
  capturedImage.value = null
  cameraError.value = ''

  // Initialize camera
  await startCamera()
}

// Face capture methods
async function startCamera() {
  try {
    // Request camera access
    const stream = await navigator.mediaDevices.getUserMedia({
      video: {
        width: { ideal: 640 },
        height: { ideal: 480 },
        facingMode: 'user' // Front camera
      }
    })

    cameraStream.value = stream
    cameraError.value = ''

    // Set video source
    const video = document.querySelector('.face-capture-modal video')
    if (video) {
      video.srcObject = stream
    }
  } catch (error) {
    console.error('Camera access error:', error)
    cameraError.value = 'Unable to access camera. Please grant camera permission.'
    stopCamera()
  }
}

function stopCamera() {
  if (cameraStream.value) {
    cameraStream.value.getTracks().forEach(track => track.stop())
    cameraStream.value = null
  }
}

function capturePhoto() {
  const video = document.querySelector('.face-capture-modal video')
  const canvas = document.querySelector('.face-capture-modal canvas')

  if (!video || !canvas) {
    attendanceMessage.value = 'Camera not ready. Please try again.'
    attendanceMessageType.value = 'error'
    return
  }

  isCapturing.value = true

  try {
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight

    // Draw video frame to canvas
    const context = canvas.getContext('2d')
    context.drawImage(video, 0, 0, canvas.width, canvas.height)

    // Convert to base64 image
    capturedImage.value = canvas.toDataURL('image/jpeg', 0.8)

    // Stop camera after capture
    stopCamera()

    // Proceed with clock in after a short delay
    setTimeout(() => {
      showFaceCapture.value = false
      proceedWithClockIn()
    }, 1500)

  } catch (error) {
    console.error('Capture error:', error)
    attendanceMessage.value = 'Failed to capture photo. Please try again.'
    attendanceMessageType.value = 'error'
    isCapturing.value = false
  }
}

function cancelFaceCapture() {
  stopCamera()
  showFaceCapture.value = false
  capturedImage.value = null
  cameraError.value = ''
  isCapturing.value = false
}

async function proceedWithClockIn() {
  if (isAttendanceProcessing.value) return

  // Check if face was captured
  if (!capturedImage.value) {
    attendanceMessage.value = 'Face photo is required to clock in'
    attendanceMessageType.value = 'warning'
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
    return
  }

  isAttendanceProcessing.value = true
  attendanceMessage.value = ''

  try {
    const res = await axios.post('/api/manager/clock-in', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude,
      face_image: capturedImage.value
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
    isCapturing.value = false
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

onMounted(() => {
  loadInitialData()
  loadPanelNotifications()

  // load receipt submissions for finance review
  loadReceiptSubmissions()

  // Auto-refresh every 30 seconds
  refreshInterval.value = setInterval(async () => {
    try {
      await refreshDashboard()
    } catch (e) {
      console.warn('Auto-refresh failed:', e)
    }
  }, 30000)

  loadAttendanceStatus()
  loadAttendanceSettings()
  getUserLocation()

  // Refresh location every 5 minutes
  setInterval(getUserLocation, 300000)
})

// Extract initial load into separate function
async function loadInitialData() {
  try {
    const params = { range: selectedRange.value }
    if (selectedBranchId.value) {
      params.branch_id = selectedBranchId.value
    }
    // Add custom date range parameters if applicable
    if (selectedRange.value === 'custom' && customStartDate.value && customEndDate.value) {
      params.start_date = customStartDate.value
      params.end_date = customEndDate.value
    }

    const [profileRes, dashRes, reportsRes, txRes] = await Promise.all([
      axios.get('/api/manager/finance/profile', { withCredentials: true }),
      axios.get('/api/manager/finance/dashboard', { params, withCredentials: true }),
      axios.get('/api/manager/finance/reports', { params, withCredentials: true }),
      axios.get('/api/manager/finance/transactions', { withCredentials: true })
    ])

    userProfile.value = profileRes.data.user
    console.log('User Profile:', userProfile.value)
    console.log('User Branch ID:', userProfile.value?.branch_id)
    dashboardTotals.value = {
      totalSales: dashRes.data.totalRevenue ? '₱' + Number(dashRes.data.totalRevenue).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      pendingApprovals: dashRes.data.pendingApprovals || 0,
      revenue: dashRes.data.netProfit ? '₱' + Number(dashRes.data.netProfit).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      totalOrders: dashRes.data.totalOrders || 0
    }

    console.log('Reports API Response:', reportsRes.data)
    financeReports.value = extractArray(reportsRes.data, 'reports')
    console.log('Extracted financeReports:', financeReports.value)

    transactions.value = extractArray(txRes.data, 'transactions')
    await fetchBudgetRequests()
    await fetchBranches()
  } catch (err) {
    console.error('Error loading initial data:', err)
  }
}

async function loadReceiptSubmissions() {
  receiptsLoading.value = true
  try {
    const res = await axios.get('/api/procurement-requests/receipt-submissions', { withCredentials: true })
    if (res.data && res.data.ok) {
      receiptSubmissions.value = res.data.requests || []
    } else {
      receiptSubmissions.value = []
    }
  } catch (e) {
    console.error('Failed to load receipt submissions', e)
    receiptSubmissions.value = []
  } finally {
    receiptsLoading.value = false
  }
}

function storageUrl(path) {
  if (!path) return '#'
  // if stored under public/receipts, convert to storage URL
  if (typeof path !== 'string') return '#'
  if (path.startsWith('public/')) return '/storage/' + path.replace(/^public\//, '')
  if (path.startsWith('/receipts/')) return path
  if (path.startsWith('receipts/')) return '/' + path
  // already a public URL or full path
  return path
}

async function confirmReceipt(id) {
  if (await window.swalConfirm('Confirm this receipt and move request to On Delivery?')) {
    confirmingId.value = id
    try {
      const res = await axios.post(`/api/procurement-requests/${id}/confirm-receipt`, {}, { withCredentials: true })
      alert(res.data?.message || 'Receipt confirmed')
      await loadReceiptSubmissions()
      await refreshDashboard()
      // notify other open UIs (procurement manager) to refresh their lists
      try { window.dispatchEvent(new CustomEvent('receiptConfirmed', { detail: id })) } catch (e) { /* ignore */ }
    } catch (e) {
      console.error('Confirm receipt failed', e)
      alert(e.response?.data?.message || 'Failed to confirm receipt')
    } finally {
      confirmingId.value = null
    }
  }
}

function openReceiptPreview(r) {
  if (!r) return
  receiptModalRequest.value = r
  receiptModalPath.value = storageUrl(r.receipt_path)
  receiptModalVisible.value = true
}

function closeReceiptPreview() {
  receiptModalVisible.value = false
  receiptModalPath.value = ''
  receiptModalRequest.value = null
}

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})

// Refresh dashboard / re-fetch data for current filter (used by button + polling)
async function refreshDashboard() {
  try {
    const params = { range: selectedRange.value }
    if (selectedBranchId.value) {
      params.branch_id = selectedBranchId.value
    }
    // Add custom date range parameters if applicable
    if (selectedRange.value === 'custom' && customStartDate.value && customEndDate.value) {
      params.start_date = customStartDate.value
      params.end_date = customEndDate.value
    }

    const [dashRes, txRes, reportsRes] = await Promise.all([
      axios.get('/api/manager/finance/dashboard', { params, withCredentials: true }),
      axios.get('/api/manager/finance/transactions', { withCredentials: true }),
      axios.get('/api/manager/finance/reports', { params, withCredentials: true }),
      fetchBudgetRequests(),
      fetchBranches()
    ])

    dashboardTotals.value = {
      totalSales: dashRes.data.totalRevenue ? '₱' + Number(dashRes.data.totalRevenue).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      pendingApprovals: dashRes.data.pendingApprovals || 0,
      revenue: dashRes.data.netProfit ? '₱' + Number(dashRes.data.netProfit).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0',
      totalOrders: dashRes.data.totalOrders || 0
    }
    transactions.value = extractArray(txRes.data, 'transactions')

    console.log('Refresh - Reports API Response:', reportsRes.data)
    financeReports.value = extractArray(reportsRes.data, 'reports')
    console.log('Refresh - Extracted financeReports:', financeReports.value)
  } catch (err) {
    console.error('Error refreshing dashboard:', err)
  }
}

function onRangeChange() {
  refreshDashboard()
}

function onBranchChange() {
  refreshDashboard()
}

function onCustomDateChange() {
  if (customStartDate.value && customEndDate.value) {
    selectedRange.value = 'custom'
    refreshDashboard()
  }
}

// Mark budget as given by finance (handed to procurement)
async function markBudgetGiven(id) {
  if (processingId.value) return
  if (!(await window.swalConfirm('Confirm you have handed the budget to procurement?'))) return
  processingId.value = id
  try {
    const response = await axios.put(`/api/manager/finance/budget/${id}/given`, {}, { withCredentials: true })
    if (response.data && response.data.ok) {
      // update local list
      const idx = budgetRequests.value.findIndex(r => r.id === id)
      if (idx !== -1) {
        // Use the returned budget_request payload to update the local item
        const br = response.data.budget_request || null
        if (br) {
          // If the backend could not persist the new enum value (DB enum mismatch),
          // treat a successful procurement_request update as 'Budget Given' for UX.
          if (br.status === 'Approved' && response.data.procurement_request) {
            budgetRequests.value[idx].status = 'Budget Given'
          } else {
            budgetRequests.value[idx].status = br.status || budgetRequests.value[idx].status
          }
          budgetRequests.value[idx].processed_by = br.processed_by || budgetRequests.value[idx].processed_by
          budgetRequests.value[idx].date_processed = br.date_processed || budgetRequests.value[idx].date_processed
        } else {
          // fallback: mark as Budget Given when no budget_request payload returned
          budgetRequests.value[idx].status = 'Budget Given'
          budgetRequests.value[idx].processed_by = response.data.procurement_request?.finance_user_id || budgetRequests.value[idx].processed_by
        }
      }
      alert('Budget marked as given. Procurement can now place orders.')
    } else {
      alert(response.data?.message || 'Failed to mark budget as given')
    }
  } catch (err) {
    console.error('Failed to mark budget given:', err)
    alert(err.response?.data?.message || 'Failed to mark budget as given')
  } finally {
    processingId.value = null
  }
}
</script>

<style scoped src="./ManagerFinancePanel.css"></style>

