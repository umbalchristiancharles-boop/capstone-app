<template>
  <div class="staff-management-page">
    <!-- Back to Dashboard Button -->
    <button @click="router.push('/owner-panel')" class="back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Dashboard
    </button>

    <!-- Header -->
    <div class="staff-header">
      <h1 class="owner-staff-title">Staff Management</h1>
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

    <!-- Add/Edit Staff Modal (Modular) -->
    <OwnerStaffModal
      :show="showAddStaffModal"
      :staff="isEditingStaff ? staff.find(s => s.id === editingStaffId) : null"
      :isEdit="isEditingStaff"
      :isViewOnly="isViewOnly"
      @close="showAddStaffModal = false"
      @success="onStaffModalSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import OwnerStaffModal from './OwnerStaffModal.vue'

function onStaffModalSuccess() {
  showAddStaffModal.value = false
  resetForm()
  loadStaff()
}

const router = useRouter()
const currentRoute = useRoute()

// Watch for route changes to reload staff when navigating to this page
watch(() => currentRoute.path, (newPath) => {
  if (newPath === '/owner/staff-management' || newPath === '/admin/staff-management') {
    loadStaff()
    loadBranches()
  }
})

// State
const loading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')

// Current Owner Data
const currentOwnerId = ref(null)
// Current logged-in user's role (e.g. OWNER, BRANCH_MANAGER, STAFF)
const currentUserRole = ref('')

// Whether the current logged-in user can perform edits in this view
const canEdit = computed(() => {
  const r = (currentUserRole.value || '').toString().toUpperCase()
  // Deny edits by default until we know the user's role.
  if (!r) return false
  return r !== 'OWNER'
})

// Staff Data
const staff = ref([])
// Branches and filters
const branches = ref([])
const branchFilter = ref('')
const roleFilter = ref('')
const departmentFilter = ref('')

// Form State
const showAddStaffModal = ref(false)
const isEditingStaff = ref(false)
const isViewOnly = ref(false)
const newStaff = ref({
  username: '',
  email: '',
  full_name: '',
  phone_number: '',
  password: '',
  department: '',
})
const editingStaffId = ref(null)

// Role hierarchy - lower number = higher priority (Owner at very top)
const rolePriority = {
  'Owner': 1,
  'Manager HR': 2,
  'Manager Finance': 3,
  'Manager Inventory': 4,
  'Manager Logistics': 5,
  'Manager': 6,   // Any branch manager
  'Staff': 7,
  'Staff Cashier': 8,
  'Staff Finance': 9,
  'Staff Inventory': 10
}

// Map database role values to display role for priority sorting
function mapRoleToDisplayRole(role) {
  if (!role) return null
  const upperRole = role.toUpperCase()

  // Map database values to display role values
  if (upperRole === 'OWNER') return 'Owner'
  if (upperRole === 'MANAGER_HR') return 'Manager HR'
  if (upperRole === 'MANAGER_FINANCE') return 'Manager Finance'
  if (upperRole === 'MANAGER_INVENTORY') return 'Manager Inventory'
  if (upperRole === 'MANAGER_LOGISTICS') return 'Manager Logistics'
  if (upperRole === 'BRANCH_MANAGER') return 'Manager'
  if (upperRole === 'MANAGER') return 'Manager' // Handle plain MANAGER role
  if (upperRole === 'HR') return 'Staff'
  if (upperRole === 'STAFF_CASHIER') return 'Staff Cashier'
  if (upperRole === 'STAFF_FINANCE') return 'Staff Finance'
  if (upperRole === 'STAFF_INVENTORY') return 'Staff Inventory'
  if (upperRole === 'STAFF_LOGISTICS') return 'Staff'
  if (upperRole === 'STAFF') return 'Staff'

  // Fallback: if role contains MANAGER (any variant), map to Manager
  if (upperRole.includes('MANAGER')) return 'Manager'
  // Fallback: if role contains STAFF (any variant), map to Staff
  if (upperRole.includes('STAFF')) return 'Staff'

  return null
}

// Get role priority for sorting (lower = higher priority, Owner = 1)
function getRolePriority(role) {
  if (!role) return 999 // Unknown roles go to the bottom
  const displayRole = mapRoleToDisplayRole(role)
  if (!displayRole) return 999
  return rolePriority[displayRole] ?? 999
}

// Computed: available roles and departments for filter dropdowns
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

// Computed: filtered staff with branch/role/department/search
const filteredStaff = computed(() => {
  let filtered = staff.value.slice()

  // Exclude owner account from list
  if (currentOwnerId.value) {
    filtered = filtered.filter(member => member.id !== currentOwnerId.value)
  }

  // Role filter: if set, match exact role
  if (roleFilter.value) {
    filtered = filtered.filter(m => (m.role || '').toString() === roleFilter.value)
  } else {
    // default: include STAFF and MANAGER variants (exclude OWNER)
    filtered = filtered.filter(member => {
      const r = (member.role || '').toUpperCase()
      return r.includes('STAFF') || r.includes('MANAGER') || r === 'HR' || r === 'BRANCH_MANAGER'
    })
  }

  // Branch filter: match by branch_name (case-insensitive)
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

// Computed: group staff by branch with role hierarchy sorting
// Note: The logged-in Owner is already excluded in filteredStaff
const groupedStaff = computed(() => {
  const groups = {}

  filteredStaff.value.forEach(member => {
    const branchName = member.branch_name || 'Unassigned'
    if (!groups[branchName]) {
      groups[branchName] = []
    }
    groups[branchName].push(member)
  })

  // Sort staff within each branch by role priority (lowest number first = highest priority)
  // Managers (priority 1-6) will appear before Staff (priority 7-10)
  Object.keys(groups).forEach(branch => {
    groups[branch].sort((a, b) => {
      // Get priorities - lower number = higher priority
      const priorityA = getRolePriority(a.role)
      const priorityB = getRolePriority(b.role)

      // Sort by priority ascending (lower number first)
      return priorityA - priorityB
    })
  })

  // Get all branch names sorted alphabetically
  const sortedBranchNames = Object.keys(groups).sort()

  // Return as array of { branchName, staff: [] } sorted by branch name
  // Exclude the "Owners" branch entirely
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
  if (member.status) return member.status
  return member.is_active ? (member.is_online ? 'On Duty' : 'Offline') : 'Inactive'
}

function statusBadgeClass(status) {
  if (!status) return 'badge-offline'
  if (status === 'On Duty') return 'badge-online'
  if (status === 'Offline') return 'badge-offline'
  return 'badge-inactive'
}

// Methods
async function loadCurrentOwner() {
  try {
    const res = await axios.get('/api/owner-profile', {
      withCredentials: true
    })

    if (res.data && res.data.ok && res.data.user) {
      // Get the owner ID - could be accountId or id
      currentOwnerId.value = res.data.user.accountId || res.data.user.account_id || res.data.user.id
      // Set current user's role when available so we can guard edit actions
      currentUserRole.value = (res.data.user.role || res.data.user.account_role || '').toString()
    }
  } catch (error) {
    console.error('Error loading owner profile:', error)
    // Continue without filtering if we can't get owner profile
  }
}

async function loadStaff() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await axios.get('/api/admin/staff', {
      withCredentials: true
    })

    if (res.data.success) {
      // Response may come in two shapes:
      // - { success: true, staff: [...] } (manager API)
      // - { success: true, data: [ { branch_name, branch_manager, staff: [...], hr: [...] }, ... ] } (admin API)
      if (Array.isArray(res.data.staff)) {
        staff.value = res.data.staff
      } else if (Array.isArray(res.data.data)) {
        const list = []
        res.data.data.forEach(branch => {
          const branchName = branch.branch_name || ''
          if (branch.branch_manager) {
            list.push({ ...branch.branch_manager, branch_name: branchName })
          }
          if (Array.isArray(branch.hr)) {
            branch.hr.forEach(h => list.push({ ...h, branch_name: branchName }))
          }
          if (Array.isArray(branch.staff)) {
            branch.staff.forEach(s => list.push({ ...s, branch_name: branchName }))
          }
        })
        staff.value = list
      } else {
        staff.value = []
      }
    } else {
      errorMessage.value = res.data.message || 'Failed to load staff'
    }
  } catch (error) {
    console.error('Staff load error:', error)
    errorMessage.value = 'Error loading staff. Please try again.'
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

function refreshStaff() {
  loadStaff()
}

function resetForm() {
  newStaff.value = {
    username: '',
    email: '',
    full_name: '',
    phone_number: '',
    password: '',
    department: '',
  }
  isEditingStaff.value = false
  editingStaffId.value = null
}

function editStaff(member) {
  if (!canEdit.value) {
    alert('You do not have permission to edit staff from this account.')
    return
  }

  isEditingStaff.value = true
  isViewOnly.value = false
  editingStaffId.value = member.id
  newStaff.value = {
    username: member.username,
    email: member.email,
    full_name: member.full_name,
    phone_number: member.phone_number,
    password: '',
    department: member.department || '',
  }
  showAddStaffModal.value = true
}

function viewStaff(member) {
  isEditingStaff.value = true
  isViewOnly.value = true
  editingStaffId.value = member.id
  newStaff.value = {
    username: member.username,
    email: member.email,
    full_name: member.full_name,
    phone_number: member.phone_number,
    password: '',
    department: member.department || '',
  }
  showAddStaffModal.value = true
}

function openAddStaffModal() {
  if (!canEdit.value) {
    alert('You do not have permission to add staff from this account.')
    return
  }

  resetForm()
  showAddStaffModal.value = true
}

async function submitStaffForm() {
  if (!canEdit.value) {
    alert('You do not have permission to save changes from this account.')
    return
  }
  // Validation
  if (!newStaff.value.full_name || !newStaff.value.email) {
    alert('Please fill in all required fields')
    return
  }

  if (!isEditingStaff.value && (!newStaff.value.username || !newStaff.value.password)) {
    alert('Username and password are required for new staff')
    return
  }

  try {
    let res

    if (isEditingStaff.value) {
      // Update
      res = await axios.put(`/api/admin/staff/${editingStaffId.value}`, {
        full_name: newStaff.value.full_name,
        email: newStaff.value.email,
        phone_number: newStaff.value.phone_number,
        department: newStaff.value.department,
      }, {
        withCredentials: true
      })
    } else {
      // Create
      res = await axios.post('/api/admin/staff', {
        username: newStaff.value.username,
        email: newStaff.value.email,
        full_name: newStaff.value.full_name,
        phone_number: newStaff.value.phone_number,
        password: newStaff.value.password,
        department: newStaff.value.department,
      }, {
        withCredentials: true
      })
    }

    if (res.data.success) {
      showAddStaffModal.value = false
      resetForm()
      // Optimistically add new staff to the list if not present
      if (!isEditingStaff.value && res.data.staff) {
        // If API returns the created staff object
        const exists = staff.value.some(s => s.id === res.data.staff.id)
        if (!exists) {
          staff.value.push(res.data.staff)
        }
      }
      loadStaff()
      alert(isEditingStaff.value ? 'Staff updated successfully!' : 'Staff added successfully!')
    }
  } catch (error) {
    console.error('Submit error:', error)
    alert('Failed to save staff: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

async function toggleStatus(member) {
  if (!canEdit.value) {
    alert('You do not have permission to change staff status from this account.')
    return
  }
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
  await loadCurrentOwner()
  await loadBranches()
  await loadStaff()
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

<style scoped>
.staff-management-page {
  padding: 1.5rem;
  background: #f8fafc;
  min-height: 100vh;
  color: #1f2937;
}

.staff-management-page::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top left, rgba(255,255,255,0.55), transparent 28%),
              radial-gradient(circle at bottom right, rgba(255,255,255,0.4), transparent 30%);
  pointer-events: none;
}
.staff-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.75rem;
  background: rgba(255, 255, 255, 0.9);
  padding: 1.5rem;
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.45);
  box-shadow: 0 28px 80px rgba(15, 23, 42, 0.08);
  position: relative;
  z-index: 1;
}

.staff-header h1 {
  margin: 0;
  font-size: 2.15rem;
  font-weight: 800;
  line-height: 1.05;
  color: #1f2937;
}

.owner-staff-title {
  color: #1f2937;
}

.header-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.85rem;
  align-items: center;
  width: 100%;
}

.filter-select,
.search-input {
  min-width: 180px;
  padding: 0.75rem 1rem;
  border-radius: 16px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: #fff;
  color: #0f172a;
  font-size: 0.95rem;
  box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
}

.search-input {
  flex: 1 1 250px;
  max-width: 320px;
}

.filter-select {
  flex: 1 1 170px;
  max-width: 220px;
}

.search-input:focus,
.filter-select:focus {
  outline: none;
  border-color: rgba(251, 146, 60, 0.75);
  box-shadow: 0 0 0 4px rgba(255, 154, 74, 0.15);
}

.btn-primary {
  padding: 0.85rem 1.25rem;
  border-radius: 999px;
  border: none;
  font-weight: 700;
  background: linear-gradient(135deg, #ff6a3d, #f59e0b);
  color: #ffffff;
  box-shadow: 0 14px 30px rgba(255, 106, 61, 0.18);
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 18px 38px rgba(255, 106, 61, 0.22);
}

.btn-secondary,
.btn-outline {
  padding: 0.75rem 1rem;
  border-radius: 999px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: rgba(255, 255, 255, 0.84);
  color: #334155;
}

.btn-secondary:hover,
.btn-outline:hover {
  background: #ffffff;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1rem;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(255, 106, 61, 0.12), rgba(251, 191, 36, 0.16));
  color: #c2410c;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.92rem;
  line-height: 1;
  box-shadow: none;
  border: 0;
  transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
}

.back-to-dashboard-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(255, 106, 61, 0.16);
  opacity: 0.95;
}

.back-to-dashboard-btn:active {
  transform: translateY(0);
}

.summary-card,
.branch-header,
.staff-table-wrapper,
.empty-state,
.loading-state {
  background: rgba(255, 255, 255, 0.92);
  border-radius: 22px;
  border: 1px solid rgba(255, 106, 61, 0.14);
}

.summary-card {
  padding: 1.5rem 1.6rem;
  margin-bottom: 1.75rem;
  box-shadow: 0 20px 48px rgba(15, 23, 42, 0.06);
}

.summary-card h3 {
  margin: 0;
  color: #1f2937;
  font-size: 1.2rem;
}

.branch-group {
  margin-bottom: 1.5rem;
}

.branch-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.25rem;
  margin-bottom: 0.9rem;
  border-left: 5px solid #ff6a3d;
}

.branch-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.branch-count {
  color: #475569;
  font-size: 0.9rem;
  background: rgba(255, 152, 35, 0.12);
  padding: 0.4rem 0.85rem;
  border-radius: 999px;
}

.staff-table-wrapper {
  overflow: hidden;
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1000px;
}

.staff-table thead {
  background: #fff7ed;
}

.staff-table th {
  padding: 1rem 1.15rem;
  text-align: left;
  font-weight: 700;
  color: #334155;
  font-size: 0.88rem;
  letter-spacing: 0.01em;
}

.staff-table td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  color: #334155;
  font-size: 0.94rem;
}

.staff-table tbody tr {
  transition: background 0.2s ease;
}

.staff-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.8);
}

.staff-table tbody tr.inactive {
  opacity: 0.82;
  background: #f8fafc;
}

.staff-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 999px;
  object-fit: cover;
  border: 1px solid rgba(148, 163, 184, 0.35);
}

.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.32rem 0.75rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  line-height: 1;
}

.badge-online {
  background: #d1fae5;
  color: #065f46;
}

.badge-offline {
  background: #e2e8f0;
  color: #334155;
}

.badge-inactive {
  background: #fee2e2;
  color: #b91c1c;
}

.empty-state,
.loading-state {
  text-align: center;
  padding: 2.5rem;
  color: #475569;
  border: 1px solid rgba(255, 255, 255, 0.45);
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
}

.alert {
  padding: 1rem 1.15rem;
  border-radius: 14px;
  margin-bottom: 1rem;
  background: rgba(254, 226, 226, 0.96);
  color: #991b1b;
  border: 1px solid rgba(248, 113, 113, 0.35);
}

.modal,
.modal-body,
.modal-footer,
.modal-header,
.form-group,
.form-input,
.close-btn {
  box-sizing: border-box;
}

.modal {
  background: #ffffff;
  border-radius: 24px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 22px 62px rgba(15, 23, 42, 0.18);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 1rem;
  border-bottom: 1px solid rgba(226, 232, 240, 0.9);
}

.modal-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 1.25rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.35rem;
  cursor: pointer;
  color: #94a3b8;
}

.close-btn:hover {
  color: #475569;
}

.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 1rem 1.5rem 1.5rem;
  border-top: 1px solid rgba(226, 232, 240, 0.9);
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  justify-content: flex-end;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #334155;
  font-weight: 600;
  font-size: 0.92rem;
}

.form-input {
  width: 100%;
  padding: 0.85rem 1rem;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 16px;
  font-size: 0.95rem;
  font-family: inherit;
  color: #0f172a;
  background: #f8fafc;
}

.form-input:focus {
  outline: none;
  border-color: rgba(251, 146, 60, 0.75);
  box-shadow: 0 0 0 4px rgba(255, 154, 74, 0.12);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 1024px) {
  .staff-table {
    min-width: 0;
  }
}

@media (max-width: 768px) {
  .staff-header {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .search-input,
  .filter-select {
    width: 100%;
    max-width: 100%;
  }

  .staff-table th,
  .staff-table td {
    padding: 0.85rem 0.75rem;
  }
}
</style>
