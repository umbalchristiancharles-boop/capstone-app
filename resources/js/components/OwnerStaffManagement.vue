<template>
  <div class="staff-management-page">
    <!-- Back to Dashboard Button -->
    <button @click="router.push('/owner-panel')" class="btn-secondary back-to-dashboard-btn">
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

    <!-- Staff Table -->
    <div v-if="!loading && filteredStaff.length > 0" class="staff-table-wrapper">
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
          <tr v-for="member in filteredStaff" :key="member.id" :class="{ 'inactive': !member.is_active }">
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
            <td>{{ member.created_at }}</td>
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

    <!-- Empty State -->
    <div v-if="!loading && filteredStaff.length === 0" class="empty-state">
      <p>No staff members found</p>
    </div>

    <!-- Add/Edit Staff Modal (Modular) -->
    <OwnerStaffModal
      :show="showAddStaffModal"
      :staff="isEditingStaff ? staff.find(s => s.id === editingStaffId) : null"
      :isEdit="isEditingStaff"
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
const newStaff = ref({
  username: '',
  email: '',
  full_name: '',
  phone_number: '',
  password: '',
  department: '',
})
const editingStaffId = ref(null)

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
    // default: include STAFF and MANAGER variants
    filtered = filtered.filter(member => {
      const r = (member.role || '').toUpperCase()
      return r.includes('STAFF') || r.includes('MANAGER') || r === 'HR' || r === 'BRANCH_MANAGER' || r === 'OWNER'
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

// Methods
async function loadCurrentOwner() {
  try {
    const res = await axios.get('/api/owner-profile', {
      withCredentials: true
    })

    if (res.data && res.data.ok && res.data.user) {
      // Get the owner ID - could be accountId or id
      currentOwnerId.value = res.data.user.accountId || res.data.user.account_id || res.data.user.id
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
  isEditingStaff.value = true
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
  resetForm()
  showAddStaffModal.value = true
}

async function submitStaffForm() {
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
</script>

<style scoped>
.staff-management-page {
  padding: 2rem;
  background: linear-gradient(180deg, #ff8c42 0%, #ff6b1c 100%);
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

.staff-header h1 {
  margin: 0;
  color: #000;
  font-size: 2.5rem;
  font-weight: 700;
  letter-spacing: -1px;
}

/* Override h1 color for owner-staff-title */
.owner-staff-title {
  color: #ffffff !important;
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

.btn-primary {
  background: #ff9f43;
  color: #fff;
}

.btn-primary:hover {
  background: #fabd83;
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

/* Override h3 color for owner-staff-total */
.owner-staff-total {
  color: #ffffff !important;
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

.badge-active {
  background: #d4edda;
  color: #155724;
}

.badge-inactive {
  background: #f8d7da;
  color: #721c24;
}

/* Online/Offline status badges */
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

/* Modal Styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-header h2 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #999;
}

.close-btn:hover {
  color: #333;
}

.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #222;
  font-weight: 500;
  font-size: 0.9rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: #FF9A4A;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
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
