  <template>
  <div class="staff-management-page">
    <!-- Back to Dashboard Button -->
    <button @click="$router.push('/admin-panel')" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Dashboard
    </button>

    <!-- Header & Filters Card -->
    <div class="staff-header-card">
      <div class="staff-header">
        <h1>Staff Management</h1>
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
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <p>Loading staff...</p>
    </div>

    <!-- Error State -->
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>

    <!-- Summary -->
    <div v-if="!isLoading && filteredStaff.length > 0" class="summary-card">
      <h3>Total Staff Members: {{ filteredStaff.length }}</h3>
    </div>
    <div v-if="!isLoading && filteredStaff.length === 0" class="summary-card">
      <h3>Total Staff Members: 0</h3>
    </div>

    <!-- Staff Table -->
    <div v-if="!isLoading && filteredStaff.length > 0" class="staff-table-wrapper">
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
                <div v-if="member.avatar_url" class="avatar-container">
                  <img
                    v-if="member.avatar_url"
                    :src="member.avatar_url"
                    :alt="member.full_name"
                    class="avatar"
                    @load="onImageLoad(member.id)"
                    @error="onImageLoad(member.id)"
                  >
                  <div
                    v-if="loadingImages[member.id] !== false"
                    class="absolute inset-0 w-8 h-8 rounded-full bg-gray-200 animate-pulse"
                  ></div>
                </div>
                <strong>{{ member.full_name || member.username }}</strong>
              </div>
            </td>
            <td>{{ displayRole(member.role) }}</td>
            <td>{{ member.department || '-' }}</td>
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
    <div v-if="!isLoading && filteredStaff.length === 0" class="empty-state">
      <p>No staff members found</p>
    </div>

    <!-- Add/Edit Staff Modal -->
    <transition name="fade">
      <div v-if="showAddStaffModal" class="modal-backdrop" @click="showAddStaffModal = false">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h2>{{ isEditingStaff ? 'Edit Staff Member' : 'Add New Staff Member' }}</h2>
            <button @click="showAddStaffModal = false" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <div v-if="!isEditingStaff" class="form-group">
              <label>Username:</label>
              <input
                v-model="newStaff.username"
                type="text"
                class="form-input"
                placeholder="Enter username"
              >
            </div>
            <div class="form-group">
              <label>Full Name:</label>
              <input
                v-model="newStaff.full_name"
                type="text"
                class="form-input"
                placeholder="Enter full name"
              >
            </div>
            <div class="form-group">
              <label>Email:</label>
              <input
                v-model="newStaff.email"
                type="email"
                class="form-input"
                placeholder="Enter email"
              >
            </div>
            <div class="form-group">
              <label>Phone Number:</label>
              <input
                v-model="newStaff.phone_number"
                type="text"
                class="form-input"
                placeholder="Enter phone number"
              >
            </div>
            <div v-if="false" class="form-group">
              <label>Role:</label>
              <select v-model="newStaff.role" class="form-input" required>
                <option value="OWNER">Owner</option>
              </select>
            </div>
            <div v-if="isEditingStaff" class="form-group">
              <label>Department:</label>
              <select v-model="newStaff.department" class="form-input">
                <option value="">-- Select Department (optional) --</option>
                <option value="HR">HR</option>
                <option value="FINANCE">Finance</option>
                <option value="INVENTORY">Inventory</option>
                <option value="LOGISTICS">Logistics</option>
                <option value="CASHIER">Cashier</option>
              </select>
            </div>
            <div v-if="!isEditingStaff" class="form-group">
              <label>Password <span style="font-weight:400; font-size: 0.85rem;">(Default: Chikintayo_123)</span></label>
              <div style="display:flex; gap:0.5rem; align-items:center;">
                <input
                  :value="defaultPasswordValue"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-input"
                  readonly
                  style="flex:1; background-color: #f3f4f6;"
                />
                <button type="button" class="password-toggle-btn" @click="showPassword = !showPassword" :title="showPassword ? 'Hide password' : 'Show password'">
                  <span v-if="showPassword">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                      <path stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.06 10.06 0 0 1 12 20c-5.05 0-9.29-3.81-10-8 .23-1.44.8-2.79 1.67-3.93M6.12 6.12A9.98 9.98 0 0 1 12 4c5.05 0 9.29 3.81 10 8-.23 1.44-.8 2.79-1.67 3.93M1 1l22 22M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-5.12"/>
                    </svg>
                  </span>
                  <span v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24">
                      <ellipse cx="12" cy="12" rx="10" ry="8" stroke="#666" stroke-width="2"/>
                      <circle cx="12" cy="12" r="3" stroke="#666" stroke-width="2"/>
                    </svg>
                  </span>
                </button>
              </div>
              <div class="small-hint" style="color:#6b7280;font-size:0.8rem; margin-top:0.25rem;">Default password is automatically set for new staff.</div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showAddStaffModal = false" class="btn-secondary">Cancel</button>
            <button v-if="isEditingStaff" @click="resetPassword" :disabled="isResetting" class="btn-warning">{{ isResetting ? 'Resetting...' : 'Reset Password' }}</button>
            <button @click="submitStaffForm" class="btn-primary">
              {{ isEditingStaff ? 'Update Staff' : 'Add Staff' }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
// Declare loadingImages as a ref to avoid undefined errors
const loadingImages = ref({})
import axios from 'axios'
import '../css/adminpanel.css'

// State
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const imageLoadingStates = ref({})

// Branches and filters
const branches = ref([])
const branchFilter = ref('')
const roleFilter = ref('')
const departmentFilter = ref('')

// Current user / role
const currentUserRole = ref(null)
const currentUserId = ref(null)
const isBranchManager = computed(() => (currentUserRole.value || '').toUpperCase() === 'BRANCH_MANAGER')

// Staff Data
const staff = ref([])

// Form State
const showAddStaffModal = ref(false)
const isEditingStaff = ref(false)
const showPassword = ref(false)
const newStaff = ref({
  username: '',
  email: '',
  full_name: '',
  phone_number: '',
  password: '',
  department: '',
  role: 'OWNER',
})
const editingStaffId = ref(null)
// removed createOwnerMode (owner creation not supported in this view)

// Computed
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

const defaultPasswordValue = 'Chikintayo_123'

const filteredStaff = computed(() => {
  let list = staff.value.slice()

  // branch filter (case-insensitive)
  if (branchFilter.value) {
    const selectedBranch = branchFilter.value.toLowerCase()
    list = list.filter(m => (m.branch_name || '').toString().toLowerCase() === selectedBranch)
  }

  // role filter
  if (roleFilter.value) {
    list = list.filter(m => (m.role || '') === roleFilter.value)
  } else {
    // default allow staff/manager/HR
    list = list.filter(member => {
      const r = (member.role || '').toUpperCase()
      return r.includes('STAFF') || r.includes('MANAGER') || r === 'HR' || r === 'BRANCH_MANAGER' || r === 'OWNER'
    })
  }

  // department filter
  if (departmentFilter.value) {
    list = list.filter(m => (m.department || '') === departmentFilter.value)
  }

  // search
  if (searchQuery.value && searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(member =>
      (member.full_name && member.full_name.toLowerCase().includes(q)) ||
      (member.username && member.username.toLowerCase().includes(q)) ||
      (member.email && member.email.toLowerCase().includes(q))
    )
  }

  // Initialize loading state for each participant
  list.forEach(member => {
    if (loadingImages.value[member.id] === undefined) {
      loadingImages.value[member.id] = true
      // Fallback: set to false after 1 second to handle cached images
      setTimeout(() => {
        if (loadingImages.value[member.id]) {
          loadingImages.value[member.id] = false
        }
      }, 1000)
    }
  })

  return list
})

// Methods
async function loadStaff() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const url = isBranchManager.value ? '/api/manager/staff' : '/api/admin/staff'
    const res = await axios.get(url, { withCredentials: true })

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

      // Fallback: ensure shimmer disappears for cached or fast-loading images
      setTimeout(() => {
        staff.value.forEach(member => {
          if (loadingImages.value[member.id]) {
            loadingImages.value[member.id] = false
          }
        })
      }, 500)
    } else {
      errorMessage.value = res.data.message || 'Failed to load staff'
    }
  } catch (error) {
    console.error('Staff load error:', error)
    errorMessage.value = 'Error loading staff. Please try again.'
  } finally {
    isLoading.value = false
  }
}

// Reset password from admin modal
const isResetting = ref(false)
async function resetPassword() {
  if (!isEditingStaff.value || !editingStaffId.value) return
  if (!confirm(`Reset password for "${newStaff.value.username || newStaff.value.full_name}" to default?`)) return
  isResetting.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true }).catch(() => {})
    const res = await axios.post(`/api/admin/staff/${editingStaffId.value}/reset-password`, {}, { withCredentials: true })
    if (res.data && res.data.success) {
      alert(res.data.message || 'Password reset successfully')
      if (res.data.defaultPassword) alert('Default password: ' + res.data.defaultPassword)
      await loadStaff()
      showAddStaffModal.value = false
    } else {
      alert(res.data?.message || 'Failed to reset password')
    }
  } catch (e) {
    console.error('Reset error:', e)
    alert(e.response?.data?.message || 'Failed to reset password')
  } finally {
    isResetting.value = false
  }
}

function refreshStaff() {
  loadStaff()
}

function useDefaultPassword() {
  newStaff.value.password = 'Chikintayo_123'
  showPassword.value = true
}

function resetForm() {
  newStaff.value = {
    username: '',
    email: '',
    full_name: '',
    phone_number: '',
    password: '',
    department: '',
    role: 'OWNER',
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
    role: member.role || 'OWNER',
  }
  showAddStaffModal.value = true
}

function openAddStaffModal() {
  resetForm()
  showAddStaffModal.value = true
}

// owner creation removed; use openAddStaffModal for new staff

async function submitStaffForm() {
  // Validation
  if (!newStaff.value.full_name || !newStaff.value.email) {
    alert('Please fill in all required fields')
    return
  }

  if (!isEditingStaff.value && !newStaff.value.username) {
    alert('Username is required for new staff')
    return
  }
  if (!newStaff.value.role) {
    alert('Please select a role')
    return
  }

  // Fix: department must be null if not set or if role is OWNER
  let departmentToSend = newStaff.value.department;
  if (!departmentToSend || departmentToSend === '' || newStaff.value.role === 'OWNER') {
    departmentToSend = null;
  }

  try {
    let res
    const baseUrl = isBranchManager.value ? '/api/manager/staff' : '/api/admin/staff'

    if (isEditingStaff.value) {
      // Update (manager/admin endpoints)
      res = await axios.put(`${baseUrl}/${editingStaffId.value}`, {
        full_name: newStaff.value.full_name,
        email: newStaff.value.email,
        phone_number: newStaff.value.phone_number,
        department: departmentToSend,
        role: newStaff.value.role,
      }, {
        withCredentials: true
      })
    } else {
      // Create staff with default password
      res = await axios.post(baseUrl, {
        username: newStaff.value.username,
        email: newStaff.value.email,
        full_name: newStaff.value.full_name,
        phone_number: newStaff.value.phone_number,
        password: defaultPasswordValue,
        role: newStaff.value.role,
        department: departmentToSend
      }, {
        withCredentials: true
      })
    }

    if (res.data.success) {
      showAddStaffModal.value = false
      resetForm()
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
    const baseUrl = isBranchManager.value ? '/api/manager/staff' : '/api/admin/staff'
    const res = await axios.put(`${baseUrl}/${member.id}`, {
      is_active: !member.is_active,
    }, { withCredentials: true })

    if (res.data.success) {
      loadStaff()
      alert(member.is_active ? 'Staff deactivated' : 'Staff activated')
    }
  } catch (error) {
    console.error('Toggle error:', error)
    alert('Failed to update staff status')
  }
}

// owner profile loading removed from this view

async function setCurrentUserRole() {
  try {
    const res = await axios.get('/api/me', { withCredentials: true })
    if (res.data?.ok && res.data.user) {
      currentUserRole.value = res.data.user.role
      currentUserId.value = res.data.user.id
    }
  } catch (e) {
    // ignore - default to admin style
    console.warn('Could not determine current user role', e)
  }
}

onMounted(() => {
  ;(async () => {
    await setCurrentUserRole()
    await loadStaff()
    await loadBranches()
  })()

  // Fallback: force all image loading states to false after 3 seconds to prevent stuck shimmers
  setTimeout(() => {
    Object.keys(loadingImages.value).forEach(key => {
      loadingImages.value[key] = false
    })
  }, 3000)
})

// owner/profile & logout helpers removed from this view

function onImageLoad(memberId) {
  loadingImages.value[memberId] = false
  // Fallback: ensure shimmer disappears after a short delay
  setTimeout(() => {
    if (loadingImages.value[memberId]) {
      loadingImages.value[memberId] = false
    }
  }, 2000) // 2 seconds timeout
}

function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  if (role === 'OWNER') return 'Owner'
  // fallback: prettify underscores
  return role.replace(/_/g, ' ')
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
</script>

<style scoped>
@import url("../css/adminpanel.css");

.staff-management-page {
  padding: 2rem;
  background: #f5f5f5;
  min-height: 100vh;
  margin-top: 40px; /* Prevent overlap with sidebar/dashboard */
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
@import url("../js/css/adminpanel.css");
.staff-management-page {
  min-height: 100vh;
  background: #ff6b1c;
  padding: 24px;
  color: #fff4e6;
}

.staff-header-card {
  background: rgba(255,255,255,0.18);
  -webkit-backdrop-filter: blur(22px);
  backdrop-filter: blur(22px);
  border-radius: 32px;
  border: 1px solid rgba(255,255,255,0.35);
  margin-bottom: 2rem;
  padding: 28px 26px;
}

.staff-header h1 {
  color: #000;
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 0;
}

.header-actions input,
.header-actions select {
  background: rgba(255,255,255,0.7);
  border: 1px solid #ffd36b;
  color: #4b2a06;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  margin-right: 0.5rem;
  font-size: 1rem;
}

.header-actions button {
  background: linear-gradient(135deg, #ff9a4a, #ff6b1c);
  color: #fff4e6;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  padding: 0.5rem 1.5rem;
  margin-left: 0.5rem;
  box-shadow: 0 4px 12px rgba(255, 107, 28, 0.15);
  transition: all 0.2s;
}
.header-actions button:hover {
  background: linear-gradient(135deg, #ff6b1c, #ff9a4a);
  color: #fff;
}

.summary-card {
  background: rgba(255,255,255,0.18);
  border-radius: 18px;
  padding: 1.2rem 2rem;
  margin-bottom: 1.5rem;
  color: #fff4e6;
  font-weight: 600;
  font-size: 1.2rem;
}

.staff-table-wrapper {
  background: rgba(255,255,255,0.18);
  border-radius: 18px;
  box-shadow: 0 2px 8px rgba(255, 107, 28, 0.08);
  overflow: hidden;
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
  color: #4b2a06;
  background: transparent;
}
.staff-table thead {
  background: linear-gradient(90deg, #ffe8a3 60%, #ffd36b 100%);
}
.staff-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 700;
  color: #4b2a06;
  font-size: 1rem;
  letter-spacing: 0.04em;
}
.staff-table td {
  padding: 1rem;
  border-bottom: 1px solid #ffd36b;
}
.staff-table tbody tr:hover {
  background: rgba(255, 232, 163, 0.18);
}
.staff-table tbody tr.inactive {
  opacity: 0.7;
  background: rgba(255, 232, 163, 0.18);
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
  border: 2px solid #ffd36b;
}
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
}
.badge-active {
  background: #ffe8a3;
  color: #4b2a06;
  border: 1px solid #ffd36b;
}
.badge-inactive {
  background: #ffd6d6;
  color: #b23c3c;
  border: 1px solid #ffb3b3;
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
  background: rgba(255,255,255,0.18);
  border-radius: 18px;
  color: #fff4e6;
  font-weight: 600;
}
.alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  background: #ffd6d6;
  color: #b23c3c;
  border: 1px solid #ffb3b3;
}
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
  background: #fff4e6;
  border-radius: 18px;
  width: 95%;
  max-width: 900px;
  box-shadow: 0 4px 16px rgba(255, 107, 28, 0.12);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #ffd36b;
}
.modal-header h2 {
  margin: 0;
  color: #ff6b1c;
  font-weight: 800;
}
.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #ff6b1c;
}
.close-btn:hover {
  color: #b23c3c;
}
.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
  align-items: start;
}
.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #ffd36b;
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
  color: #ff6b1c;
  font-weight: 700;
  font-size: 0.95rem;
}
.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ffd36b;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  background: rgba(255,255,255,0.7);
  color: #4b2a06;
}
.form-input:focus {
  outline: none;
  border-color: #ff6b1c;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.12);
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

.modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
  align-items: start;
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
  color: #333;
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

.password-toggle-btn {
  background: #f3f4f6;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 0.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  min-width: 40px;
}

.password-toggle-btn:hover {
  background: #e5e7eb;
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
