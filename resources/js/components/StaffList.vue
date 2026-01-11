<template>
  <div class="staff-management-container">
    <!-- Header Section -->
    <div class="staff-header">
      <h2 class="staff-title">Staff Management</h2>
      <button @click="openCreateModal" class="btn-create-staff">
        <span class="plus-icon">+</span>
        Create Staff Account
      </button>
    </div>

    <!-- Alert Messages -->
    <transition name="fade">
      <div v-if="alertMessage" :class="['alert', alertType]">
        {{ alertMessage }}
      </div>
    </transition>

    <!-- Staff Table -->
    <div class="staff-table-wrapper">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Branch</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="staff in staffList" :key="staff.id" class="staff-row">
            <td class="td-username">{{ staff.username }}</td>
            <td>{{ staff.full_name }}</td>
            <td>{{ staff.email }}</td>
            <td>{{ staff.branch_name || 'N/A' }}</td>
            <td>{{ staff.phone_number || 'N/A' }}</td>
            <td>
              <span :class="['status-badge', staff.is_active ? 'active' :  'inactive']">
                {{ staff.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="td-actions">
              <button @click="editStaff(staff)" class="btn-action btn-edit">
                Edit
              </button>
              <button @click="confirmDelete(staff.id, staff. username)" class="btn-action btn-delete">
                Delete
              </button>
            </td>
          </tr>
          <tr v-if="staffList.length === 0">
            <td colspan="7" class="no-data">No staff accounts found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <StaffModal
      :show="showModal"
      :staff="selectedStaff"
      :isEdit="!! selectedStaff"
      @close="closeModal"
      @success="handleSaved"
    />
  </div>
</template>

<script>
import StaffModal from './StaffModal.vue';

export default {
  name: 'StaffList',
  components:  {
    StaffModal
  },
  data() {
    return {
      staffList: [],
      branches: [],
      showModal: false,
      selectedStaff:  null,
      alertMessage: '',
      alertType: 'success',
    };
  },
  mounted() {
    this.fetchStaff();
    this.fetchBranches();
  },
  methods: {
    async fetchStaff() {
      try {
        const response = await fetch('/api/admin/staff');
        const data = await response. json();
        if (data.ok) {
          this.staffList = data.staff;
        }
      } catch (error) {
        this.showAlert('Failed to load staff list', 'error');
      }
    },

    async fetchBranches() {
      try {
        const response = await fetch('/api/admin/branches');
        const data = await response.json();
        if (data.ok) {
          this.branches = data.branches;
        }
      } catch (error) {
        console.error('Failed to load branches');
      }
    },

    openCreateModal() {
      this.selectedStaff = null;
      this.showModal = true;
    },

    editStaff(staff) {
      this.selectedStaff = { ...staff };
      this.showModal = true;
    },

   async confirmDelete(id, username) {
  if (!confirm(`Are you sure you want to delete "${username}"?`)) {
    return;
  }

  try {
    const response = await fetch(`/api/admin/staff/${id}`, {
      method: 'DELETE',
      headers:  {
        'Content-Type':  'application/json',
        'Accept': 'application/json'
      }
    });

    const data = await response.json();

    if (response.ok) {
      this.showAlert(data.message || 'Staff account deleted successfully!', 'success');
      this.fetchStaff();
    } else {
      this.showAlert(data.message || 'Failed to delete staff account', 'error');
    }
  } catch (error) {
    console.error('Delete error:', error);
    this.showAlert('Failed to delete staff account', 'error');
  }
}

    ,closeModal() {
      this.showModal = false;
      this.selectedStaff = null;
    },

    handleSaved() {
      this.closeModal();
      this.fetchStaff();
      this.showAlert('Staff account saved successfully!', 'success');
    },

    showAlert(message, type) {
      this.alertMessage = message;
      this.alertType = type;
      setTimeout(() => {
        this.alertMessage = '';
      }, 3000);
    }
  }
};
</script>

<style scoped>
.staff-management-container {
  background:  linear-gradient(135deg, #ff9a56 0%, #ff7e5f 100%);
  padding: 2rem;
  border-radius: 20px;
  min-height: 80vh;
  box-shadow: 0 10px 40px rgba(255, 126, 95, 0.3);
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.staff-title {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-create-staff {
  background: rgba(255, 255, 255, 0.95);
  color: #ff7e5f;
  border:  none;
  padding: 0.875rem 1.75rem;
  border-radius:  12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-create-staff:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.plus-icon {
  font-size: 1.25rem;
  font-weight:  bold;
}

.alert {
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.alert.success {
  background: #10b981;
  color:  white;
}

.alert.error {
  background: #ef4444;
  color: white;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.staff-table-wrapper {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
}

.staff-table thead {
  background: linear-gradient(135deg, #ff9a56, #ff8c5f);
}

.staff-table thead th {
  color: white;
  padding: 1.25rem 1rem;
  text-align: left;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.875rem;
  letter-spacing: 0.5px;
}

.staff-row {
  transition: background 0.2s;
}

.staff-row:hover {
  background:  rgba(255, 154, 86, 0.08);
}

.staff-table tbody td {
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
}

.td-username {
  font-weight: 600;
  color: #ff7e5f;
}

.status-badge {
  padding: 0.375rem 0.875rem;
  border-radius:  20px;
  font-size: 0.8125rem;
  font-weight:  700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.active {
  background: #d1fae5;
  color:  #065f46;
}

.status-badge.inactive {
  background: #fee2e2;
  color:  #991b1b;
}

.td-actions {
  display:  flex;
  gap: 0.5rem;
}

.btn-action {
  padding: 0.5rem 1rem;
  border:  none;
  border-radius:  8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
}

.btn-edit {
  background: #3b82f6;
  color:  white;
}

.btn-edit:hover {
  background:  #2563eb;
  transform: translateY(-1px);
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-delete:hover {
  background: #dc2626;
  transform:  translateY(-1px);
}

.no-data {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
  font-weight: 600;
}
</style>
