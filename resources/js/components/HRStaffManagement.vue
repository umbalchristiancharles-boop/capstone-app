<template>
  <div class="staff-management-page">
    <!-- Back to Dashboard Button -->
    <button @click="router.push('/super-admin-panel')" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Super Admin
    </button>

    <!-- Header -->
    <div class="staff-header">
      <h1 class="owner-staff-title">HR Staff Management</h1>
      <div class="header-actions">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search staff..."
          class="search-input"
        >
        <select v-model="branchFilter" class="filter-select">
          <option value="">All Branches</option>
          <option v-for="b in branches" :key="b.id" :value="b.name">{{ b.name }}</option>
        </select>

        <select v-model="roleFilter" class="filter-select">
          <option value="">All Roles</option>
          <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
        </select>

        <select v-model="departmentFilter" class="filter-select">
          <option value="">All Departments</option>
          <option v-for="d in availableDepartments" :key="d" :value="d">{{ d }}</option>
        </select>
        <button @click="refreshStaff" class="btn-primary">Refresh</button>
        <button @click="openAddStaffModal()" class="btn-success">+ Add Staff</button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Loading staff...</p>
    </div>

    <!-- Error State -->
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>

    <!-- Summary -->
    <div v-if="!loading && filteredStaff.length > 0" class="summary-card">
      <h3 class="owner-staff-total">Total Staff Members: {{ filteredStaff.length }}</h3>
    </div>
    <div v-if="!loading && filteredStaff.length === 0" class="summary-card">
      <h3 class="owner-staff-total">Total Staff Members: 0</h3>
    </div>

    <!-- Staff Tables Grouped by Branch -->
    <div v-if="!loading && groupedStaff.length > 0">
      <div v-for="group in groupedStaff" :key="group.branchName" class="branch-group">
        <!-- Branch Header -->
        <div class="branch-header">
          <h2 class="branch-title">{{ group.branchName }}{{ group.branchName !== 'Unassigned' ? ' Branch' : '' }}</h2>
          <span class="branch-count">{{ group.staff.length }} staff member{{ group.staff.length !== 1 ? 's' : '' }}</span>
        </div>

        <!-- Branch Staff Table -->
        <div class="staff-table-wrapper">
          <table class="staff-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Department</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="member in group.staff" :key="member.id" :class="{ 'inactive': !member.is_active }">
                <td>
                  <div class="staff-info">
                    <img v-if="member.avatar_url" :src="member.avatar_url" :alt="member.full_name" class="avatar">
                    <strong>{{ member.full_name || member.username }}</strong>
                  </div>
                </td>
                <td>{{ displayRole(member.role) }}</td>
                <td>{{ (member.department || '-') }}</td>
                <td>{{ member.username }}</td>
                <td>{{ member.email }}</td>
                <td>{{ member.phone_number || '-' }}</td>
                <td>
                  <span :class="['badge', statusBadgeClass(getMemberStatus(member))]">
                    {{ getMemberStatus(member) }}
                  </span>
                </td>
                <td>{{ formatDate(member.created_at) }}</td>
                <td class="actions">
                  <button
                    @click="editStaff(member)"
                    class="btn-sm btn-info"
                    title="Edit"
                  >
                    Edit
                  </button>
                  <button
                    @click="toggleStatus(member)"
                    :class="['btn-sm', member.is_active ? 'btn-danger' : 'btn-success']"
                    :title="member.is_active ? 'Deactivate' : 'Activate'"
                  >
                    {{ member.is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && filteredStaff.length === 0" class="empty-state">
      <p>No staff members found</p>
    </div>

    <!-- Add/Edit Staff Modal -->
    <StaffModal
      :show="showAddStaffModal"
      :staff="isEditingStaff ? staff.find(s => s.id === editingStaffId) : null"
      :isEdit="isEditingStaff"
      :preSelectedBranchId="currentBranchId"
      @close="showAddStaffModal = false"
      @success="onStaffModalSuccess"
    />

    <!-- Position Open Requests Section -->
    <div class="position-requests-section" style="display: block;">
      <div class="section-header">
        <h2 class="section-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
          Open Position Requests
          <span v-if="positionRequestsPendingCount > 0" style="background: #ffc107; color: #000; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold; margin-left: 8px;">{{ positionRequestsPendingCount }}</span>
        </h2>
        <div class="section-actions">
          <button @click="loadPositionRequests" class="btn-secondary" :disabled="loadingPositionRequests">
            {{ loadingPositionRequests ? 'Loading...' : 'Refresh' }}
          </button>
        </div>
      </div>

      <div v-if="loadingPositionRequests" class="loading-state">
        <p>Loading requests...</p>
      </div>

      <div v-else-if="positionRequests.length === 0" class="empty-state">
        <p>No position requests found.</p>
      </div>

      <div v-else class="requests-list">
        <div v-for="req in positionRequests" :key="req.id" class="request-card" :class="'request-card--' + req.status.toLowerCase()">
          <div class="request-card__header">
            <div class="request-card__position">{{ req.position?.name || 'Unknown Position' }}</div>
            <span class="badge" :class="statusBadgeClass(req.status)">{{ req.status }}</span>
          </div>

          <div class="request-card__body">
            <div class="request-card__info">
              <span class="label">Branch:</span>
              <span class="value">{{ req.branch?.name || 'Main HR' }}</span>
            </div>
            <div class="request-card__info">
              <span class="label">Quantity:</span>
              <span class="value">{{ req.quantity }}</span>
            </div>
            <div class="request-card__info">
              <span class="label">Requested by:</span>
              <span class="value">{{ req.requested_by?.full_name || req.requested_by?.username || 'Unknown' }}</span>
            </div>
            <div class="request-card__info">
              <span class="label">Date:</span>
              <span class="value">{{ formatDate(req.created_at) }}</span>
            </div>
            <div v-if="req.notes" class="request-card__notes">
              <span class="label">Notes:</span>
              <p>{{ req.notes }}</p>
            </div>
            <div v-if="req.rejection_reason" class="request-card__notes request-card__notes--rejection">
              <span class="label">Rejection reason:</span>
              <p>{{ req.rejection_reason }}</p>
            </div>
          </div>

          <div v-if="req.status === 'Pending'" class="request-card__actions">
            <button @click="approveRequest(req)" class="btn-success btn-sm" :disabled="processingRequestId === req.id">
              {{ processingRequestId === req.id ? 'Processing...' : 'Approve' }}
            </button>
            <button @click="openRejectModal(req)" class="btn-danger btn-sm" :disabled="processingRequestId === req.id">
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Reason Modal -->
    <div v-if="showRejectModal" class="modal-backdrop" @click.self="closeRejectModal">
      <div class="modal">
        <div class="modal-header">
          <h2>Reject Request</h2>
          <button class="close-button" @click="closeRejectModal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Reason for rejection (optional)</label>
            <textarea
              v-model="rejectReason"
              class="form-input"
              rows="3"
              placeholder="Enter reason..."
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="closeRejectModal">Cancel</button>
          <button
            class="btn-danger"
            @click="confirmReject"
            :disabled="processingRequestId === rejectingRequest?.id"
          >
            {{ processingRequestId === rejectingRequest?.id ? 'Processing...' : 'Reject Request' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import StaffModal from './StaffModal.vue'
import { useTheme } from '../composables/useTheme'

const router = useRouter()
const { initializeTheme } = useTheme()

function onStaffModalSuccess() {
  showAddStaffModal.value = false
  resetForm()
  loadStaff()
}

// State
const loading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')

// Current user's branch id (used to restrict branch selection for HR)
const currentBranchId = ref(null)

// Staff Data
const staff = ref([])

// Position Open Requests State
const positionRequests = ref([])
const loadingPositionRequests = ref(false)
const processingRequestId = ref(null)
const showRejectModal = ref(false)
const rejectingRequest = ref(null)
const rejectReason = ref('')

const positionRequestsPendingCount = computed(() => {
  return positionRequests.value.filter(r => r.status === 'Pending').length
})
// Branches and filters
const branches = ref([])
const branchFilter = ref('')
const roleFilter = ref('')
const departmentFilter = ref('')

// Form State
const showAddStaffModal = ref(false)
const isEditingStaff = ref(false)
const editingStaffId = ref(null)

// Role hierarchy - lower number = higher priority
const rolePriority = {
  'Owner': 1,
  'Manager HR': 2,
  'Manager Finance': 3,
  'Manager Inventory': 4,
  'Manager Logistics': 5,
  'Manager': 6,
  'Staff': 7,
  'Staff Cashier': 8,
  'Staff Finance': 9,
  'Staff Inventory': 10
}

function mapRoleToDisplayRole(role) {
  if (!role) return null
  const upperRole = role.toUpperCase()

  if (upperRole === 'OWNER') return 'Owner'
  if (upperRole === 'MANAGER_HR') return 'Manager HR'
  if (upperRole === 'MANAGER_FINANCE') return 'Manager Finance'
  if (upperRole === 'MANAGER_INVENTORY') return 'Manager Inventory'
  if (upperRole === 'MANAGER_LOGISTICS') return 'Manager Logistics'
  if (upperRole === 'BRANCH_MANAGER') return 'Manager'
  if (upperRole === 'MANAGER') return 'Manager'
  if (upperRole === 'HR') return 'Staff'
  if (upperRole === 'STAFF_CASHIER') return 'Staff Cashier'
  if (upperRole === 'STAFF_FINANCE') return 'Staff Finance'
  if (upperRole === 'STAFF_INVENTORY') return 'Staff Inventory'
  if (upperRole === 'STAFF_LOGISTICS') return 'Staff'
  if (upperRole === 'STAFF') return 'Staff'

  if (upperRole.includes('MANAGER')) return 'Manager'
  if (upperRole.includes('STAFF')) return 'Staff'

  return null
}

function getRolePriority(role) {
  if (!role) return 999
  const displayRole = mapRoleToDisplayRole(role)
  if (!displayRole) return 999
  return rolePriority[displayRole] ?? 999
}

// Computed: available roles and departments
const defaultRoles = [
  'Manager HR', 'Manager Finance', 'Manager Inventory', 'Manager Logistics',
  'Staff Cashier', 'Staff Finance', 'Staff Inventory'
]

const availableRoles = computed(() => {
  const set = new Set(defaultRoles)
  staff.value.forEach(m => { if (m.role) set.add(m.role) })
  return Array.from(set).sort()
})

const availableDepartments = computed(() => {
  const set = new Set()
  staff.value.forEach(m => { if (m.department) set.add(m.department) })
  return Array.from(set).sort()
})

// Computed: filtered staff
const filteredStaff = computed(() => {
  let filtered = staff.value.slice()

  // Role filter
  if (roleFilter.value) {
    filtered = filtered.filter(m => (m.role || '').toString() === roleFilter.value)
  } else {
    filtered = filtered.filter(member => {
      const r = (member.role || '').toUpperCase()
      return r.includes('STAFF') || r.includes('MANAGER') || r === 'HR' || r === 'BRANCH_MANAGER'
    })
  }

  // Branch filter
  if (branchFilter.value) {
    const selectedBranch = branchFilter.value.toLowerCase()
    filtered = filtered.filter(m => (m.branch_name || '').toString().toLowerCase() === selectedBranch)
  }

  // Department filter
  if (departmentFilter.value) {
    filtered = filtered.filter(m => (m.department || '').toString() === departmentFilter.value)
  }

  // Search query
  if (searchQuery.value && searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(member =>
      (member.full_name && member.full_name.toLowerCase().includes(q)) ||
      (member.username && member.username.toLowerCase().includes(q)) ||
      (member.email && member.email.toLowerCase().includes(q))
    )
  }

  return filtered
})

// Computed: group staff by branch
const groupedStaff = computed(() => {
  const groups = {}

  filteredStaff.value.forEach(member => {
    const branchName = member.branch_name || 'Unassigned'
    if (!groups[branchName]) {
      groups[branchName] = []
    }
    groups[branchName].push(member)
  })

  // Sort staff within each branch by role priority
  Object.keys(groups).forEach(branch => {
    groups[branch].sort((a, b) => {
      const priorityA = getRolePriority(a.role)
      const priorityB = getRolePriority(b.role)
      return priorityA - priorityB
    })
  })

  const sortedBranchNames = Object.keys(groups).sort()

  return sortedBranchNames
    .filter(branchName => branchName.toLowerCase() !== 'owners')
    .map(branchName => ({
      branchName,
      staff: groups[branchName]
    }))
})

// Helper: derive human-friendly status for a member
function getMemberStatus(member) {
  if (!member) return '-'

  const raw = (member.status || '').toString().trim()
  const isActive = !!member.is_active
  const isOnline = !!member.is_online

  if (raw) {
    const s = raw.toLowerCase()
    // map common backend values to a normalized display value
    if (s === 'present' || s === 'on duty' || s === 'onduty' || s === 'working' || s === 'working now') {
      const role = (member.role || '').toString().toUpperCase()
      if (role.includes('MANAGER') || role === 'HR') return 'Working'
      return 'On Duty'
    }
    if (s === 'offline' || s === 'off' || s === 'absent') return 'Offline'
    if (s === 'inactive') return 'Inactive'

    // fallback: return a capitalized version of the raw status
    return raw.charAt(0).toUpperCase() + raw.slice(1)
  }

  if (!isActive) return 'Inactive'
  return isOnline ? 'On Duty' : 'Offline'
}

function statusBadgeClass(status) {
  if (!status) return 'badge-offline'
  const s = status.toString().toLowerCase()
  if (s === 'on duty' || s === 'working') return 'badge-online'
  if (s === 'offline') return 'badge-offline'
  if (s === 'inactive') return 'badge-inactive'
  return 'badge-offline'
}

// Methods
async function loadStaff(retryCount = 0) {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await axios.get('/api/superadmin/all-staff', {
      withCredentials: true
    })

    if (res.data && res.data.ok) {
      staff.value = res.data.staff || []
    } else {
      errorMessage.value = res.data?.message || 'Failed to load staff'
    }
  } catch (error) {
    console.error('Staff load error:', error)

    // Handle auth errors gracefully
    if (error.response?.status === 401 || error.response?.status === 403) {
      errorMessage.value = 'Session expired. Redirecting to login...'
      setTimeout(() => {
        localStorage.removeItem('user')
        router.push('/staff-landing')
      }, 1500)
      return
    }

    if (retryCount < 1) {
      console.log(`[HRStaffManagement] Retrying loadStaff (attempt ${retryCount + 2})`)
      await new Promise(resolve => setTimeout(resolve, 1000))
      return loadStaff(retryCount + 1)
    }

    errorMessage.value = 'Error loading staff. Please refresh the page.'
  } finally {
    loading.value = false
  }
}

async function loadBranches() {
  try {
    const res = await axios.get('/api/admin/branches', { withCredentials: true })
    if (res.data && res.data.success && Array.isArray(res.data.data)) {
      branches.value = res.data.data
    }
  } catch (e) {
    console.error('Failed loading branches', e)
  }
}

// Position Requests Methods
async function loadPositionRequests() {
  loadingPositionRequests.value = true
  try {
    console.log('[HRStaffManagement] Loading position requests, CSRF token available:', document.cookie.includes('XSRF'))
    const res = await axios.get('/api/hr/positions/requests/pending', { withCredentials: true })
    console.log('[HRStaffManagement] Position requests response:', res.data)
    if (res.data && res.data.ok) {
      positionRequests.value = res.data.requests || []
    } else {
      positionRequests.value = []
    }
  } catch (e) {
    console.error('[HRStaffManagement] Failed loading position requests:', e.response || e.message || e)
    positionRequests.value = []
  } finally {
    loadingPositionRequests.value = false
  }
}

async function approveRequest(req) {
  processingRequestId.value = req.id
  try {
    const res = await axios.post(`/api/hr/positions/requests/${req.id}/approve`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Request approved successfully.')
      await loadPositionRequests()
    } else {
      alert(res.data?.message || 'Failed to approve request.')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to approve request.')
  } finally {
    processingRequestId.value = null
  }
}

function openRejectModal(req) {
  rejectingRequest.value = req
  rejectReason.value = ''
  showRejectModal.value = true
}

function closeRejectModal() {
  showRejectModal.value = false
  rejectingRequest.value = null
  rejectReason.value = ''
}

async function confirmReject() {
  if (!rejectingRequest.value) return
  processingRequestId.value = rejectingRequest.value.id
  try {
    const res = await axios.post(`/api/hr/positions/requests/${rejectingRequest.value.id}/reject`, {
      reason: rejectReason.value
    }, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Request rejected.')
      closeRejectModal()
      await loadPositionRequests()
    } else {
      alert(res.data?.message || 'Failed to reject request.')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to reject request.')
  } finally {
    processingRequestId.value = null
  }
}

function refreshStaff() {
  loadStaff()
}

function resetForm() {
  isEditingStaff.value = false
  editingStaffId.value = null
}

function editStaff(member) {
  isEditingStaff.value = true
  editingStaffId.value = member.id
  showAddStaffModal.value = true
}

function openAddStaffModal() {
  resetForm()
  showAddStaffModal.value = true
}

async function toggleStatus(member) {
  try {
    const res = await axios.put(`/api/admin/staff/${member.id}`, {
      is_active: !member.is_active,
    }, {
      withCredentials: true
    })

    if (res.data.success) {
      loadStaff()
      alert(member.is_active ? 'Staff deactivated' : 'Staff activated')
    }
  } catch (error) {
    console.error('Toggle error:', error)
    alert('Failed to update staff status')
  }
}

onMounted(async () => {
  initializeTheme()
  // Force page reload effect for HR Staff Management (user request)
  if (sessionStorage.getItem('forceHrReload') === '1') {
    console.log('[HRStaffManagement] Force reload flag detected - full refresh complete')
    sessionStorage.removeItem('forceHrReload')
    // Trigger immediate data load after hard reload
  }

  // Pre-flight CSRF refresh to prevent 419 errors on initial load
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    console.log('[HRStaffManagement] CSRF refreshed successfully')
  } catch (e) {
    console.warn('[HRStaffManagement] CSRF refresh failed, proceeding anyway:', e)
  }

  // Read current user's branch id from localStorage if available so the modal
  // can restrict branch selection when HR creates staff.
  try {
    const stored = JSON.parse(localStorage.getItem('user') || 'null') || null
    if (stored && stored.branch_id) currentBranchId.value = stored.branch_id
  } catch (e) {
    currentBranchId.value = null
  }

  await loadBranches()
  await loadStaff()
  await loadPositionRequests()
})

function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  if (role === 'OWNER') return 'Owner'
  return role.replace(/_/g, ' ')
}

function formatDate(dateString) {
  if (dateString === null || dateString === undefined) return '-'
  const normalizedDate = String(dateString).trim()
  if (!normalizedDate) return '-'

  const date = new Date(normalizedDate)
  if (isNaN(date.getTime())) return '-'
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const year = date.getFullYear()
  return `${month}-${day}-${year}`
}
</script>

<style>
/* styles copied unchanged from original component */
.staff-management-page {
  padding: 30px;
  background-color: #F8FAFC;
  min-height: 100vh;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.staff-management-page h1,
.staff-management-page h2 {
  font-family: 'Inter', 'Poppins', sans-serif;
  font-weight: 800;
  color: var(--text-dark);
}

.admin-label, .metric-label, .overview-label, .branch-count {
  color: #64748B !important;
}

.avatar-change-text {
  color: var(--text-dark) !important;
}

.btn-primary {
  background: #0066FF !important;
  color: white !important;
}

.btn-primary:hover {
  background: #0057e6 !important;
}

.btn-secondary, .btn-outline {
  background: #64748B !important;
  color: white !important;
}

.btn-secondary:hover, .btn-outline:hover {
  background: #525c6a !important;
}

.staff-header h1 {
  margin: 0;
  font-size: 2.5rem;
  font-weight: 700;
  letter-spacing: -1px;
}

.owner-staff-title {
  font-family: 'Inter', 'Poppins', sans-serif !important;
  font-weight: 800 !important;
  letter-spacing: -0.5px !important;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
}

.search-input {
  padding: 0.75rem 1rem;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 0.9rem;
  width: 280px;
}

.search-input:focus {
  outline: none;
  border-color: #0066FF;
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
}

.btn-primary, .btn-success, .btn-secondary, .btn-info, .btn-danger {
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  transition: background 0.3s ease;
}

.btn-primary {
  background: #0066FF;
  color: white;
}

.btn-primary:hover {
  background: #3B82F6;
}

.btn-success {
  background: #10B981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-info {
  background: #3B82F6;
  color: white;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
  border-radius: 6px;
}

.btn-info:hover {
  background: #2563EB;
}

.btn-danger {
  background: #EF4444;
  color: white;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
  border-radius: 6px;
}

.btn-danger:hover {
  background: #DC2626;
}

.btn-sm {
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 8px 16px;
  font-size: 0.9rem;
  font-weight: 600;
  border-radius: 8px;
}

.back-icon {
  flex-shrink: 0;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  margin-bottom: 2rem;
}

.summary-card h3 {
  margin: 0;
  color: #222;
}

.owner-staff-total {
  color: #ffffff !important;
}

.branch-group {
  margin-bottom: 2rem;
}

.branch-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1rem 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border-left: 4px solid #0066FF;
}

.branch-title {
  margin: 0;
  color: #222;
  font-size: 1.4rem;
  font-weight: 600;
}

.branch-count {
  color: #555;
  font-size: 0.9rem;
  background: rgba(255, 255, 255, 0.4);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
}

.staff-table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  overflow: hidden;
}

/* ===== DARK MODE SUPPORT ===== */
.dark-mode .staff-management-page {
  background-color: #1a1a1a !important;
  color: #e5e7eb !important;
}

.dark-mode .staff-header {
  background: #2d2d2d !important;
  border-bottom: 1px solid #3f3f3f;
}

.dark-mode .owner-staff-title {
  color: #ffffff !important;
}

.dark-mode .search-input,
.dark-mode .filter-select {
  background: #1f1f1f !important;
  color: #e5e7eb !important;
  border: 1px solid #404040 !important;
}

.dark-mode .search-input::placeholder {
  color: #9ca3af !important;
}

.dark-mode .search-input:focus,
.dark-mode .filter-select:focus {
  background: #2a2a2a !important;
  border-color: #ff8a50 !important;
  box-shadow: 0 0 0 3px rgba(255, 138, 80, 0.1) !important;
}

.dark-mode .summary-card {
  background: #2d2d2d !important;
  color: #e5e7eb !important;
  border: 1px solid #3f3f3f !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
}

.dark-mode .summary-card h3 {
  color: #ffffff !important;
}

.dark-mode .branch-group {
  background: #252525 !important;
  border: 1px solid #3f3f3f !important;
  border-radius: 8px;
}

.dark-mode .branch-header {
  background: #2a2a2a !important;
  border-bottom: 1px solid #404040 !important;
  border-left: 4px solid #ff8a50 !important;
  color: #e5e7eb;
  box-shadow: none !important;
}

.dark-mode .branch-title {
  color: #ffffff !important;
}

.dark-mode .branch-count {
  color: #d1d5db !important;
  background: rgba(255, 138, 80, 0.1) !important;
}

.dark-mode .staff-table-wrapper {
  background: #1f1f1f !important;
  border: 1px solid #3f3f3f !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
}

.dark-mode .staff-table {
  background: #1f1f1f !important;
}

.dark-mode .staff-table th {
  background: #262626 !important;
  color: #ffffff !important;
  border-bottom: 2px solid #404040 !important;
}

.dark-mode .staff-table td {
  color: #d1d5db !important;
  border-bottom: 1px solid #3f3f3f !important;
}

.dark-mode .staff-table tbody tr:hover {
  background: #262626 !important;
}

.dark-mode .staff-table tbody tr.inactive {
  opacity: 0.6;
  background: #1f1f1f !important;
}

.dark-mode .staff-info strong {
  color: #ffffff !important;
}

.dark-mode .avatar {
  border: 1px solid #404040 !important;
}

.dark-mode .badge {
  background: #3f3f3f !important;
  color: #e5e7eb !important;
}

.dark-mode .badge-online {
  background: rgba(34, 197, 94, 0.2) !important;
  color: #4ade80 !important;
}

.dark-mode .badge-offline {
  background: rgba(107, 114, 128, 0.2) !important;
  color: #d1d5db !important;
}

.dark-mode .badge-inactive {
  background: rgba(107, 114, 128, 0.08) !important;
  color: #9ca3af !important;
}

.dark-mode .empty-state,
.dark-mode .loading-state {
  background: #2d2d2d !important;
  color: #9ca3af !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
}

.dark-mode .alert {
  background: #2a2a2a !important;
  color: #e5e7eb !important;
}

.dark-mode .alert-danger {
  background: rgba(239, 68, 68, 0.1) !important;
  color: #fca5a5 !important;
  border: 1px solid #7f1d1d !important;
}

.badge-inactive { background: #f1f5f9; color: #64748b; }


.dark-mode .back-to-dashboard-btn {
  background: #4b5563 !important;
  color: #ffffff !important;
  border: 1px solid #5a6580 !important;
}

.dark-mode .back-to-dashboard-btn:hover {
  background: #5a6580 !important;
  border-color: #ff8a50 !important;
}

.dark-mode .btn-primary {
  background: #0ea5e9 !important;
  color: white !important;
}

.dark-mode .btn-primary:hover {
  background: #0284c7 !important;
}

.dark-mode .btn-success {
  background: #10b981 !important;
  color: white !important;
}

.dark-mode .btn-success:hover {
  background: #059669 !important;
}

.dark-mode .btn-secondary {
  background: #4b5563 !important;
  color: #ffffff !important;
  border: 1px solid #5a6580 !important;
}

.dark-mode .btn-secondary:hover {
  background: #5a6580 !important;
  border-color: #ff8a50 !important;
}

.dark-mode .btn-info {
  background: #0ea5e9 !important;
  color: white !important;
}

.dark-mode .btn-info:hover {
  background: #0284c7 !important;
}

.dark-mode .btn-danger {
  background: #ef4444 !important;
  color: white !important;
}

.dark-mode .btn-danger:hover {
  background: #dc2626 !important;
}

.dark-mode .btn-sm {
  background: #2a2a2a !important;
  color: #e5e7eb !important;
  border: 1px solid #404040 !important;
}

.dark-mode .btn-sm:hover {
  background: #3a3a3a !important;
  border-color: #ff8a50 !important;
  color: #ffffff !important;
}

/* ===== TABLE STYLING ===== */
.staff-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.staff-table th {
  background: #EFF6FF;
  color: #1E3A8A;
  font-weight: 600;
  padding: 1rem;
  text-align: left;
  font-size: 0.9rem;
  border-right: 1px solid #E5E7EB;
}

.staff-table th:last-child {
  border-right: none;
}

.staff-table td {
  border-bottom: 1px solid #E5E7EB;
  border-right: 1px solid #E5E7EB;
  padding: 1rem;
  color: #374151;
}

.staff-table td:last-child {
  border-right: none;
}

.staff-table tbody tr:hover {
  background: rgba(0, 102, 255, 0.05);
}

.staff-table tbody tr.inactive {
  opacity: 0.7;
  background: #f8f9fa;
}

/* ===== DARK MODE TABLE STYLING ===== */
.dark-mode .staff-table {
  background: #1f1f1f;
  color: #e5e7eb;
}

.dark-mode .staff-table th {
  background: #262626 !important;
  color: #ffffff !important;
  border-right: 1px solid #404040 !important;
  border-bottom: 2px solid #404040 !important;
}

.dark-mode .staff-table th:last-child {
  border-right: none;
}

.dark-mode .staff-table td {
  color: #d1d5db !important;
  border-bottom: 1px solid #3f3f3f !important;
  border-right: 1px solid #3f3f3f !important;
}

.dark-mode .staff-table td:last-child {
  border-right: none;
}

.dark-mode .staff-table tbody tr {
  background: #1f1f1f;
}

.dark-mode .staff-table tbody tr:hover {
  background: rgba(255, 138, 80, 0.05) !important;
}

.dark-mode .staff-table tbody tr.inactive {
  opacity: 0.6;
  background: #262626;
}

/* Position Requests Section */
.position-requests-section {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  margin-top: 2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
  margin-top: 2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.25rem;
  margin: 0;
}

.section-actions {
  display: flex;
  gap: 0.5rem;
}

.requests-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.request-card {
  background: #fafafa;
  border-radius: 8px;
  border: 1px solid #eee;
  padding: 1rem;
}

.request-card--approved {
  border-left: 4px solid #28a745;
}

.request-card--rejected {
  border-left: 4px solid #dc3545;
}

.request-card--pending {
  border-left: 4px solid #ffc107;
}

.request-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.request-card__position {
  font-weight: 600;
  font-size: 1.1rem;
  color: #333;
}

.request-card__body {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.request-card__info {
  display: flex;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.request-card__info .label {
  color: #666;
  min-width: 100px;
}

.request-card__info .value {
  color: #333;
  font-weight: 500;
}

.request-card__notes {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #eee;
}

.request-card__notes .label {
  display: block;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.request-card__notes p {
  margin: 0;
  font-size: 0.9rem;
  color: #333;
}

.request-card__notes--rejection p {
  color: #dc3545;
}

.request-card__actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.btn-sm {
  padding: 0.35rem 0.75rem;
  font-size: 0.85rem;
}
</style>
