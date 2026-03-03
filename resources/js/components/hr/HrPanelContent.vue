<template>
  <section class="hr-panel-content">
    <!-- Header with Actions -->
    <div class="staff-header">
      <h2>Staff Management</h2>
      <div class="hr-header-actions">
        <div class="hr-search-wrapper">
          <svg class="hr-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search staff..."
            class="hr-search-input"
          />
        </div>
        <button @click="refreshStaff" class="hr-btn hr-btn--refresh">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
          Refresh
        </button>
        <button @click="openAddStaffModal()" class="hr-btn hr-btn--add">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          + Add Staff
        </button>
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

    <!-- Staff Table -->
    <div v-if="!loading && filteredStaff.length > 0" class="staff-table-wrapper">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Position</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in filteredStaff" :key="member.id" :class="{ 'inactive': !member.is_active }">
            <td>
              <div class="staff-info">
                <strong>{{ member.full_name || member.username }}</strong>
              </div>
            </td>
            <td>{{ member.department || '-' }}</td>
            <td>{{ displayRole(member.role) }}</td>
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
      <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
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
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  staffList: {
    type: Array,
    default: () => []
  },
  hrReports: {
    type: Array,
    default: () => []
  }
})

// Emit events for parent refresh
const emit = defineEmits(['refresh'])

// State
const loading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const staffList = ref([])
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

// Methods
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

function refreshStaff() {
  loadStaff()
  emit('refresh')
}

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

function openAddStaffModal() {
  resetForm()
  showModal.value = true
}

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

function closeModal() {
  showModal.value = false
  resetForm()
}

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
      emit('refresh')
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

async function toggleStatus(member) {
  try {
    const res = await axios.put(`/api/manager/hr/staff/${member.id}`, {
      isActive: !member.is_active,
    }, {
      withCredentials: true
    })

    if (res.data.ok) {
      loadStaff()
      emit('refresh')
      alert(member.is_active ? 'Staff deactivated' : 'Staff activated')
    }
  } catch (error) {
    console.error('Toggle error:', error)
    alert('Failed to update staff status')
  }
}

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
      emit('refresh')
      alert('Staff deleted successfully')
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('Failed to delete staff')
  }
}

function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  return role.replace(/_/g, ' ')
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleDateString()
  } catch {
    return dateStr
  }
}

// Initialize - load staff from API since parent passes props but we need to manage our own state
onMounted(() => {
  loadStaff()
})

// Watch for prop changes
import { watch } from 'vue'
watch(() => props.staffList, (newVal) => {
  if (newVal && Array.isArray(newVal)) {
    staffList.value = newVal
  }
}, { immediate: true })
</script>

<style scoped>
.hr-panel-content {
  padding: 1rem;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.staff-header h2 {
  margin: 0;
  color: #333;
  font-size: 1.5rem;
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
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.btn-danger {
  background: #dc3545;
  color: #fff;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.btn-sm {
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.summary-card {
  background: rgba(255,255,255,0.18);
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.summary-card h3 {
  margin: 0;
  color: #333;
  font-size: 1rem;
}

.staff-table-wrapper {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
}

.staff-table thead {
  background: #f8f9fa;
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
  background: #f8f9fa;
}

.staff-table tbody tr.inactive {
  opacity: 0.7;
  background: #f8f9fa;
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
  background: white;
  border-radius: 8px;
  color: #666;
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
