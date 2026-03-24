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
                  <span :class="['badge', member.is_online ? 'badge-online' : 'badge-offline']">
                    {{ member.is_online ? 'Online' : 'Offline' }}
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
      @close="showAddStaffModal = false"
      @success="onStaffModalSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import StaffModal from './StaffModal.vue'

const router = useRouter()

function onStaffModalSuccess() {
  showAddStaffModal.value = false
  resetForm()
  loadStaff()
}

// State
const loading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')

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

// Methods
async function loadStaff() {
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
/* styles copied unchanged from original component */
.staff-management-page {
  padding: 2rem;
  background: linear-gradient(180deg, #FF9A4A 0%, #FF6A3D 100%);
  min-height: 100vh;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: rgba(255,255,255,0.18);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

h1, h2 {
  color: var(--text-dark) !important;
  font-weight: 800 !important;
  font-family: 'Inter', 'Poppins', sans-serif !important;
  letter-spacing: -0.5px !important;
  margin-bottom: 8px !important;
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
  padding: 0.45rem 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: #fff;
  font-size: 0.9rem;
}

.search-input {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  width: 250px;
}

.search-input:focus {
  outline: none;
  border-color: #FF9A4A;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1);
}

.btn-primary, .btn-success, .btn-secondary, .btn-info, .btn-danger {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.btn-success {
  background: #28a745;
  color: #fff;
}

.btn-success:hover {
  background: #218838;
}

.btn-success {
  background: #28a745;
  color: #fff;
}

.btn-success:hover {
  background: #218838;
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-info {
  background: #17a2b8;
  color: #fff;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.btn-info:hover {
  background: #138496;
}

.btn-danger {
  background: #dc3545;
  color: #fff;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.btn-danger:hover {
  background: #c82333;
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
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}

.back-icon {
  flex-shrink: 0;
}

.summary-card {
  background: rgba(255,255,255,0.18);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
  background: rgba(255, 255, 255, 0.22);
  border-radius: 8px;
  border-left: 4px solid #FF9A4A;
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
  background: rgba(255,255,255,0.18);
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
}

.staff-table thead {
  background: rgba(255,255,255,0.22);
  border-bottom: 2px solid #dee2e6;
}

.staff-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #222;
  font-size: 0.9rem;
}

.staff-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  color: #222;
}

.staff-table tbody tr:hover {
  background: rgba(255,255,255,0.14);
}

.staff-table tbody tr.inactive {
  opacity: 0.7;
  background: #f8f9fa;
}

.staff-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-online {
  background: #28a745;
  color: #ffffff;
}

.badge-offline {
  background: #6c757d;
  color: #ffffff;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.empty-state, .loading-state {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
  color: #444;
}

.alert {
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.alert-danger {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
  .staff-header {
    flex-direction: column;
    gap: 1rem;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .search-input {
    width: 100%;
  }

  .staff-table {
    font-size: 0.85rem;
  }

  .staff-table th,
  .staff-table td {
    padding: 0.75rem 0.5rem;
  }
}
</style>
