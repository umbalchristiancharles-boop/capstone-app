<template>
  <div class="staff-management-page">
    <!-- Header -->
    <div class="staff-header">
      <h1>Staff Management</h1>
      <div class="header-actions">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search staff..." 
          class="search-input"
        >
        <button @click="refreshStaff" class="btn-primary">Refresh</button>
        <button @click="showAddStaffModal = true" class="btn-success">+ Add Staff</button>
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
    <div v-if="!isLoading && staff" class="summary-card">
      <h3>Total Staff Members: {{ staff.length }}</h3>
    </div>

    <!-- Staff Table -->
    <div v-if="!isLoading && filteredStaff.length > 0" class="staff-table-wrapper">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Name</th>
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
            <td>{{ member.username }}</td>
            <td>{{ member.email }}</td>
            <td>{{ member.phone_number || '-' }}</td>
            <td>
              <span :class="['badge', member.is_active ? 'badge-active' : 'badge-inactive']">
                {{ member.is_active ? 'Active' : 'Inactive' }}
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
            <div v-if="!isEditingStaff" class="form-group">
              <label>Password:</label>
              <input 
                v-model="newStaff.password"
                type="password"
                class="form-input"
                placeholder="Enter password (min 8 characters)"
              >
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showAddStaffModal = false" class="btn-secondary">Cancel</button>
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
import axios from 'axios'
import '../css/adminpanel.css'

// State
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')

// Staff Data
const staff = ref([])

// Form State
const showAddStaffModal = ref(false)
const isEditingStaff = ref(false)
const newStaff = ref({
  username: '',
  email: '',
  full_name: '',
  phone_number: '',
  password: '',
})
const editingStaffId = ref(null)

// Computed
const filteredStaff = computed(() => {
  if (!searchQuery.value.trim()) return staff.value

  return staff.value.filter(member =>
    member.full_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    member.username.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    member.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
async function loadStaff() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const res = await axios.get('/api/manager/staff', {
      withCredentials: true
    })

    if (res.data.success) {
      staff.value = res.data.staff
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
  }
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
      res = await axios.put(`/api/manager/staff/${editingStaffId.value}`, {
        full_name: newStaff.value.full_name,
        email: newStaff.value.email,
        phone_number: newStaff.value.phone_number,
      }, {
        withCredentials: true
      })
    } else {
      // Create
      res = await axios.post('/api/manager/staff', {
        username: newStaff.value.username,
        email: newStaff.value.email,
        full_name: newStaff.value.full_name,
        phone_number: newStaff.value.phone_number,
        password: newStaff.value.password,
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
    const res = await axios.put(`/api/manager/staff/${member.id}`, {
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

onMounted(() => {
  loadStaff()
})
</script>

<style scoped>
.staff-management-page {
  padding: 2rem;
  background: #f5f5f5;
  min-height: 100vh;
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

.staff-header h1 {
  margin: 0;
  color: #333;
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
  background: #FF9A4A;
  color: white;
}

.btn-primary:hover {
  background: #FF6A3D;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-info {
  background: #17a2b8;
  color: white;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

.btn-info:hover {
  background: #138496;
}

.btn-danger {
  background: #dc3545;
  color: white;
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

.summary-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

.summary-card h3 {
  margin: 0;
  color: #333;
}

.staff-table-wrapper {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
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
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #333;
  font-size: 0.9rem;
}

.staff-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.staff-table tbody tr:hover {
  background: #f8f9fa;
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
  color: #666;
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
