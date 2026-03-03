<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Staff Management'"
    :panelDescription="'Manage staff members, view details, and perform actions.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="showLogoutConfirm = true"
    @profile-updated="onProfileUpdated"
  >
    <template #profileFooter>
      <!-- Back to HR Dashboard Button -->
      <div class="admin-actions-row">
        <button class="staff-btn staff-btn--center" @click="goBackToHRPanel()">
          Back to HR Dashboard
        </button>
      </div>
    </template>
    <template #main>
      <!-- Staff Management Content - Matching Owner Staff Management UI -->
      <div class="staff-management-content">
        <!-- Header -->
        <div class="staff-header">
          <h1>Staff Management</h1>
          <div class="header-actions">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search staff..."
              class="search-input"
            />
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
        <div v-if="!loading && staffList.length > 0" class="summary-card">
          <h3>Total Staff Members: {{ filteredStaff.length }}</h3>
        </div>
        <div v-if="!loading && staffList.length === 0" class="summary-card">
          <h3>Total Staff Members: 0</h3>
        </div>

        <!-- Staff Table - Matching Owner Staff Management Table Structure -->
        <div v-if="!loading && filteredStaff.length > 0" class="staff-table-wrapper">
          <table class="staff-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
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
                <td>{{ member.department || '-' }}</td>
                <td>{{ displayRole(member.role) }}</td>
                <td>{{ member.email }}</td>
                <td>{{ member.phone_number || '-' }}</td>
                <td>
                  <span :class="['badge', member.is_active ? 'badge-active' : 'badge-inactive']">
                    {{ member.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
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
                  <button
                    @click="deleteStaff(member)"
                    class="btn-sm btn-danger"
                    title="Delete"
                  >
                    Delete
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

        <!-- Add/Edit Staff Modal -->
        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
          <div class="modal">
            <div class="modal-header">
              <h2>{{ isEditing ? 'Edit Staff Member' : 'Add New Staff Member' }}</h2>
              <button type="button" @click="closeModal" class="close-button">&times;</button>
            </div>
            <form @submit.prevent="submitStaffForm">
              <div class="modal-body">
                <!-- Username (Create only) -->
                <div class="form-group" v-if="!isEditing">
                  <label>Username *</label>
                  <input
                    v-model="formData.username"
                    type="text"
                    class="form-input"
                    placeholder="Enter username"
                    required
                  />
                </div>

                <!-- Email -->
                <div class="form-group">
                  <label>Email {{ !isEditing ? '*' : '' }}</label>
                  <input
                    v-model="formData.email"
                    type="email"
                    class="form-input"
                    placeholder="Enter email address"
                    :required="!isEditing"
                  />
                </div>

                <!-- Full Name -->
                <div class="form-group">
                  <label>Full Name *</label>
                  <input
                    v-model="formData.full_name"
                    type="text"
                    class="form-input"
                    placeholder="Enter full name"
                    required
                  />
                </div>

                <!-- Phone Number -->
                <div class="form-group">
                  <label>Phone Number</label>
                  <input
                    v-model="formData.phone_number"
                    type="tel"
                    class="form-input"
                    placeholder="Enter phone number"
                  />
                </div>

                <!-- Department -->
                <div class="form-group">
                  <label>Department</label>
                  <select v-model="formData.department" class="form-input">
                    <option value="">-- Select Department --</option>
                    <option value="Cashier">Cashier</option>
                    <option value="Kitchen">Kitchen</option>
                    <option value="Service">Service</option>
                    <option value="Delivery">Delivery</option>
                  </select>
                </div>

                <!-- Password (Create mode only) -->
                <div class="form-group" v-if="!isEditing">
                  <label>Password</label>
                  <input
                    v-model="formData.password"
                    type="password"
                    class="form-input"
                    placeholder="Leave blank for default password"
                  />
                  <small class="form-hint">Default: Chikintayo_123</small>
                </div>

                <!-- Password (Edit mode - optional) -->
                <div class="form-group" v-if="isEditing">
                  <label>New Password (leave blank to keep current)</label>
                  <input
                    v-model="formData.password"
                    type="password"
                    class="form-input"
                    placeholder="Enter new password"
                  />
                </div>

                <!-- Error Message -->
                <div v-if="formError" class="error-message">
                  {{ formError }}
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn btn-secondary" :disabled="isSubmitting">
                  Cancel
                </button>
                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                  {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Staff' : 'Add Staff') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </template>

    <template #side>
      <!-- Attendance Settings Section -->
      <section class="panel-block hr-settings-panel">
        <div class="panel-header">
          <h2>Attendance Settings</h2>
        </div>

        <!-- Early Clock-Out Override Toggle for HR only -->
        <div class="attendance-override-toggle" v-if="userProfile.role === 'HR'">
          <div class="toggle-label">
            <span class="toggle-title">Enable Early Clock-Out</span>
            <span class="toggle-desc">Allow staff to clock out before scheduled time</span>
          </div>
          <label class="toggle-switch">
            <input
              type="checkbox"
              v-model="earlyClockoutOverride"
              @change="toggleEarlyClockout"
              :disabled="isTogglingOverride"
            >
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="panel-body panel-body--list">
          <div class="side-item">
            <span>View and manage staff attendance records</span>
          </div>
        </div>
      </section>
    </template>
  </OwnerPanelLayout>

  <!-- LOGOUT CONFIRM -->
  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Manager Panel?</h3>
        <p>This will end your current session for Chikin Tayo Manager.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
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
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const router = useRouter()
const errorMessage = ref('')

// Logo image
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href

// Logout state
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

// User profile
const userProfile = ref({})

// Staff data
const staffList = ref([])

// Early clock-out override toggle
const earlyClockoutOverride = ref(false)
const isTogglingOverride = ref(false)

// Search
const searchQuery = ref('')

// Loading and error states
const loading = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const formError = ref('')
const editingStaffId = ref(null)

const formData = ref({
  username: '',
  email: '',
  full_name: '',
  phone_number: '',
  department: '',
  password: ''
})

// Computed: filtered staff with search
const filteredStaff = computed(() => {
  let filtered = staffList.value.slice()

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

// Load user profile
async function loadUserProfile() {
  try {
    const res = await axios.get('/api/manager/hr/profile', { withCredentials: true })
    userProfile.value = res.data.user
  } catch (err) {
    if (err.response && err.response.status === 401) {
      router.push('/login')
    }
  }
}

// Load staff data from Manager HR API (already filtered by branch)
async function loadStaff() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await axios.get('/api/manager/hr/staff', {
      withCredentials: true
    })

    if (res.data.success || res.data.ok) {
      staffList.value = res.data.staff || []
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

// Load attendance settings
async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      earlyClockoutOverride.value = res.data.data.early_clockout_override || false
    }
  } catch (e) {
    console.error('Failed to load attendance settings:', e)
  }
}

// Toggle early clock-out
async function toggleEarlyClockout() {
  isTogglingOverride.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.patch('/api/attendance/override', {
      early_clockout_override: earlyClockoutOverride.value
    }, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert(res.data.message || 'Settings updated successfully')
    } else {
      earlyClockoutOverride.value = !earlyClockoutOverride.value
      alert(res.data.message || 'Failed to update settings')
    }
  } catch (e) {
    earlyClockoutOverride.value = !earlyClockoutOverride.value
    alert(e.response?.data?.message || 'Error updating settings')
  } finally {
    isTogglingOverride.value = false
  }
}

// Refresh staff
function refreshStaff() {
  loadStaff()
}

// Reset form
function resetForm() {
  formData.value = {
    username: '',
    email: '',
    full_name: '',
    phone_number: '',
    department: '',
    password: ''
  }
  isEditing.value = false
  editingStaffId.value = null
  formError.value = ''
}

// Open add staff modal
function openAddStaffModal() {
  resetForm()
  showModal.value = true
}

// Edit staff
function editStaff(member) {
  isEditing.value = true
  editingStaffId.value = member.id
  formData.value = {
    username: member.username,
    email: member.email,
    full_name: member.full_name,
    phone_number: member.phone_number || '',
    department: member.department || '',
    password: ''
  }
  showModal.value = true
}

// Close modal
function closeModal() {
  showModal.value = false
  resetForm()
}

// Submit staff form
async function submitStaffForm() {
  formError.value = ''

  // Validation
  if (!formData.value.full_name || formData.value.full_name.trim() === '') {
    formError.value = 'Full name is required'
    return
  }

  if (!isEditing.value) {
    if (!formData.value.username || formData.value.username.trim() === '') {
      formError.value = 'Username is required'
      return
    }
    if (!formData.value.email || formData.value.email.trim() === '') {
      formError.value = 'Email is required'
      return
    }
  }

  isSubmitting.value = true

  try {
    let res

    if (isEditing.value) {
      // Update
      const payload = {
        fullName: formData.value.full_name,
        email: formData.value.email,
        phone: formData.value.phone_number,
        department: formData.value.department,
      }

      if (formData.value.password && formData.value.password.trim() !== '') {
        payload.password = formData.value.password
      }

      res = await axios.put(`/api/manager/hr/staff/${editingStaffId.value}`, payload, {
        withCredentials: true
      })
    } else {
      // Create
      res = await axios.post('/api/manager/hr/staff', {
        username: formData.value.username,
        email: formData.value.email,
        fullName: formData.value.full_name,
        phone: formData.value.phone_number,
        department: formData.value.department,
        password: formData.value.password,
      }, {
        withCredentials: true
      })
    }

    if (res.data.success || res.data.ok) {
      closeModal()
      loadStaff()
      alert(isEditing.value ? 'Staff updated successfully!' : 'Staff added successfully!')
    } else {
      formError.value = res.data.message || 'Failed to save staff'
    }
  } catch (error) {
    console.error('Submit error:', error)
    if (error.response?.data?.message) {
      formError.value = error.response.data.message
    } else {
      formError.value = 'Failed to save staff. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// Toggle staff status
async function toggleStatus(member) {
  try {
    const res = await axios.put(`/api/manager/hr/staff/${member.id}`, {
      isActive: !member.is_active,
    }, {
      withCredentials: true
    })

    if (res.data.success || res.data.ok) {
      loadStaff()
      alert(member.is_active ? 'Staff deactivated' : 'Staff activated')
    }
  } catch (error) {
    console.error('Toggle error:', error)
    alert('Failed to update staff status')
  }
}

// Delete staff
async function deleteStaff(member) {
  if (!confirm(`Are you sure you want to delete ${member.full_name || member.username}?`)) {
    return
  }

  try {
    const res = await axios.delete(`/api/manager/hr/staff/${member.id}`, {
      withCredentials: true
    })

    if (res.data.success || res.data.ok) {
      loadStaff()
      alert('Staff deleted successfully')
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('Failed to delete staff')
  }
}

// Display role
function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  return role.replace(/_/g, ' ')
}

// Handle profile update
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

// Navigate back to HR panel
function goBackToHRPanel() {
  router.push('/manager/hr')
}

// Cancel logout
function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

// Confirm logout
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
    try { window.location.replace('/') } catch (e) { /* ignore */ }
  }, 600)
}

// On mounted
onMounted(async () => {
  await loadUserProfile()
  await loadStaff()
  await loadAttendanceSettings()
})
</script>

<style scoped>
.staff-management-content {
  padding: 1rem;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  background: rgba(255,255,255,0.18);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.staff-header h1 {
  margin: 0;
  color: #333;
  font-size: 1.75rem;
  font-weight: 700;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.search-input {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  width: 200px;
}

.search-input:focus {
  outline: none;
  border-color: #FF9A4A;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1);
}

.staff-btn {
  display: inline-block;
  padding: 0.625rem 1.25rem;
  background: #ff9f43;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.staff-btn:hover {
  background: #fabd83;
}

.staff-btn--center {
  display: block;
  width: 100%;
  text-align: center;
}

.summary-card {
  background: rgba(255,255,255,0.18);
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.summary-card h3 {
  margin: 0;
  color: #333;
  font-size: 1rem;
}

.staff-table-wrapper {
  background: rgba(255,255,255,0.18);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
  padding: 0.75rem;
  text-align: left;
  font-weight: 600;
  color: #333;
  font-size: 0.85rem;
}

.staff-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #dee2e6;
  color: #333;
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
  gap: 0.5rem;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
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

.actions {
  display: flex;
  gap: 0.5rem;
}

.empty-state, .loading-state {
  text-align: center;
  padding: 2rem;
  background: rgba(255,255,255,0.18);
  border-radius: 8px;
  color: #333;
}

.alert {
  padding: 0.75rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.alert-danger {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Button Styles */
.btn-primary, .btn-success, .btn-secondary, .btn-info, .btn-danger {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s ease;
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
}

.btn-info:hover {
  background: #138496;
}

.btn-danger {
  background: #dc3545;
  color: #fff;
}

.btn-danger:hover {
  background: #c82333;
}

.btn-sm {
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
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
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 1.25rem;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #999;
  padding: 0;
  line-height: 1;
}

.close-button:hover {
  color: #333;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  gap: 0.75rem;
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
  padding: 0.625rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #FF9A4A;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1);
}

.form-hint {
  display: block;
  margin-top: 0.25rem;
  color: #666;
  font-size: 0.8rem;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  padding: 0.75rem;
  border-radius: 4px;
  font-size: 0.9rem;
  margin-top: 1rem;
}

.btn {
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Logout confirm dialog */
.logout-confirm-backdrop {
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

.logout-confirm-box {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
  max-width: 400px;
}

.logout-confirm-box h3 {
  margin: 0 0 0.5rem;
  color: #333;
}

.logout-confirm-box p {
  margin: 0 0 1.5rem;
  color: #666;
}

.logout-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-cancel {
  padding: 0.625rem 1.25rem;
  background: #6c757d;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-confirm {
  padding: 0.625rem 1.25rem;
  background: #dc3545;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

/* Loading overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.logo-loading-box {
  text-align: center;
}

.logo-loading-img {
  width: 120px;
  height: auto;
  margin-bottom: 1rem;
}

.logo-loading-box p {
  color: #666;
  font-size: 1rem;
}

/* Fade transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Side panel styles */
.panel-block {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 1rem;
}

.panel-header h2 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.attendance-override-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #eee;
}

.toggle-label {
  flex: 1;
}

.toggle-title {
  display: block;
  font-weight: 500;
  color: #333;
}

.toggle-desc {
  display: block;
  font-size: 0.8rem;
  color: #666;
}

.toggle-switch {
  position: relative;
  width: 48px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
  background-color: #ff9f43;
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

.panel-body--list {
  padding: 0.5rem 0;
}

.side-item {
  padding: 0.5rem 0;
  color: #666;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .staff-header {
    flex-direction: column;
    gap: 1rem;
  }

  .header-actions {
    width: 100%;
    flex-wrap: wrap;
  }

  .search-input {
    width: 100%;
  }

  .staff-table {
    font-size: 0.8rem;
  }

  .staff-table th,
  .staff-table td {
    padding: 0.5rem;
  }
}
</style>

