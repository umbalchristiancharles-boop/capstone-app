<template>
  <div class="staff-management-page">
    <!-- Back to Dashboard Button -->
    <button @click="router.push('/manager/hr')" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to HR Dashboard
    </button>

    <!-- Header -->
    <div class="staff-header">
      <h1 class="manager-hr-title">Staff Management</h1>
      <div class="header-actions">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search staff..."
          class="search-input"
        >
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
      <h3 class="manager-hr-total">Total Staff Members: {{ filteredStaff.length }}</h3>
    </div>
    <div v-if="!loading && staffList.length === 0" class="summary-card">
      <h3 class="manager-hr-total">Total Staff Members: 0</h3>
    </div>

    <!-- Staff Table -->
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

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// State
const loading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')

// Staff data
const staffList = ref([])

// Loading and error states
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

// Load staff data from Manager HR API (already filtered by branch)
async function loadStaff() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await axios.get('/api/manager/hr/staff', {
      withCredentials: true
    })

    if (res.data.ok) {
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

    if (res.data.ok) {
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

    if (res.data.ok) {
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

    if (res.data.ok) {
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

// On mounted
onMounted(async () => {
  await loadStaff()
})
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

.staff-header .manager-hr-title {
  color: #ffffff;
  font-weight: 700;
}

.summary-card .manager-hr-total {
  color: #ffffff;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
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

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #999;
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

.btn-primary {
  background: #ff9f43;
  color: #fff;
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
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

