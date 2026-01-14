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

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="loading-text">Loading staff data...</div>
    </div>

    <!-- Branches List -->
    <div v-else class="branches-container">
      <!-- Each Branch Section -->
      <div v-for="branch in branches" :key="branch.branch_id" class="branch-section">
        <!-- Branch Header -->
        <div class="branch-header">
          <h3 class="branch-title">
            {{ branch.branch_name }}
            <span class="branch-code">({{ branch.branch_code }})</span>
          </h3>
        </div>

        <!-- Branch Content Card -->
        <div class="branch-content-card">
          <!-- Branch Manager Section -->
          <div v-if="branch.branch_manager" class="manager-section">
            <div class="section-header manager-header">
              <h4 class="section-title">Branch Manager</h4>
            </div>
            <div class="manager-card">
              <div class="user-info-grid">
                <div class="info-item">
                  <span class="info-label">Username</span>
                  <span class="info-value">{{ branch. branch_manager.username }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Full Name</span>
                  <span class="info-value">{{ branch.branch_manager.full_name }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Email</span>
                  <span class="info-value">{{ branch.branch_manager.email }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Phone</span>
                  <span class="info-value">{{ branch.branch_manager.phone_number || 'N/A' }}</span>
                </div>
              </div>
              <div class="user-actions">
                <button @click="editStaff(branch. branch_manager)" class="btn-action btn-edit">
                  Edit
                </button>
                <button
                  @click="confirmDelete(branch.branch_manager.id, branch.branch_manager.username)"
                  class="btn-action btn-delete"
                  :disabled="deletingIds.includes(branch.branch_manager.id)"
                >
                  {{ deletingIds. includes(branch.branch_manager. id) ? '⏳' : 'Delete' }}
                </button>
              </div>
            </div>
          </div>

          <!-- No Manager Notice -->
          <div v-else class="no-manager-notice">
            <p>No Branch Manager assigned</p>
          </div>

          <!-- Staff Section -->
          <div class="staff-section">
            <div class="section-header staff-header-bar">
              <h4 class="section-title">
                Staff Members
                <span class="staff-count">({{ branch.staff.length }})</span>
              </h4>
            </div>

            <!-- Staff List -->
            <div v-if="branch.staff. length > 0" class="staff-list">
              <div
                v-for="staff in branch.staff"
                :key="staff.id"
                class="staff-card"
              >
                <div class="user-info-grid">
                  <div class="info-item">
                    <span class="info-label">Username</span>
                    <span class="info-value">{{ staff.username }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value">{{ staff. full_name }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ staff.email }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Phone</span>
                    <span class="info-value">{{ staff.phone_number || 'N/A' }}</span>
                  </div>
                </div>
                <div class="user-actions">
                  <button @click="editStaff(staff)" class="btn-action btn-edit">
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(staff.id, staff.username)"
                    class="btn-action btn-delete"
                    :disabled="deletingIds.includes(staff.id)"
                  >
                    {{ deletingIds.includes(staff.id) ? '⏳' : 'Delete' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- No Staff Notice -->
            <div v-else class="no-staff-notice">
              <p>No staff members assigned to this branch</p>
            </div>
          </div>
        </div>
      </div>

      <!-- No Branches Message -->
      <div v-if="branches.length === 0" class="no-data-card">
        <p>No branches with staff or managers found</p>
      </div>
    </div>

    <!-- Modal -->
    <StaffModal
      :show="showModal"
      :staff="selectedStaff"
      :isEdit="!!selectedStaff"
      @close="closeModal"
      @success="handleSaved"
    />
  </div>
</template>

<script>
import axios from 'axios'
import StaffModal from './StaffModal.vue';

export default {
  name: 'StaffList',
  components: {
    StaffModal
  },
  data() {
    return {
      branches: [],
      loading: false,
      showModal: false,
      selectedStaff: null,
      alertMessage: '',
      alertType: 'success',
      deletingIds: [],
    };
  },
  mounted() {
    this.fetchStaff();
  },
  methods: {
    async fetchStaff() {
      this.loading = true;
      this.alertMessage = '';

      try {
        console.log('Fetching staff grouped by branch...');
        const res = await axios.get('/api/admin/staff');

        if (res.status === 401 || res.data?.status === 401) {
          this.showAlert('Not authenticated. Please login again.', 'error');
          setTimeout(() => {
            window.location.href = '/login';
          }, 2000);
          return;
        }

        const data = res.data;
        console.log('API Response:', data);

        if (data.success) {
          this.branches = data.data;
          console.log('Branches loaded:', this.branches.length);
        } else {
          this.showAlert(data.message || 'Failed to load staff data', 'error');
        }
      } catch (error) {
        console.error('Error fetching staff:', error);
        this.showAlert('Failed to load staff data', 'error');
      } finally {
        this.loading = false;
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

      this.deletingIds.push(id);

      try {
        const res = await axios.delete(`/api/admin/staff/${id}`);
        const data = res.data;

        if (res.status === 200 && data.success) {
          this.showAlert(
            data.message || 'Account deleted successfully!',
            'success'
          );
          this.fetchStaff();
        } else {
          this.showAlert(
            data.message || 'Failed to delete account',
            'error'
          );
        }
      } catch (error) {
        console.error('Delete error:', error);
        this.showAlert(
          error.response?.data?.message || 'Failed to delete account',
          'error'
        );
      } finally {
        this.deletingIds = this.deletingIds.filter(delId => delId !== id);
      }
    },

    closeModal() {
      this.showModal = false;
      this.selectedStaff = null;
    },

    handleSaved() {
      this.closeModal();
      this.fetchStaff();
      this.showAlert('Account saved successfully!', 'success');
    },

    showAlert(message, type) {
      this.alertMessage = message;
      this.alertType = type;
      setTimeout(() => {
        this.alertMessage = '';
      }, 5000);
    }
  }
};
</script>


<style scoped>
/* Main Container */
.staff-management-container {
  background: linear-gradient(135deg, #ff9a56 0%, #ff7e5f 100%);
  padding: 2rem;
  border-radius: 20px;
  min-height: 80vh;
  box-shadow:  0 10px 40px rgba(255, 126, 95, 0.3);
}

/* Header */
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
  border: none;
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
  font-weight: bold;
}

/* Alerts */
.alert {
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.alert.success {
  background: #10b981;
  color: white;
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

/* Loading */
.loading-container {
  text-align: center;
  padding: 3rem;
}

.loading-text {
  color: white;
  font-size: 1.25rem;
  font-weight:  600;
}

/* Branches Container */
.branches-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Branch Section */
.branch-section {
  margin-bottom: 1.5rem;
}

.branch-header {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  padding: 1rem 1.5rem;
  border-radius: 12px 12px 0 0;
  border-bottom: 4px solid rgba(255, 126, 95, 0.8);
}

.branch-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin:  0;
}

.branch-code {
  font-size: 0.875rem;
  font-weight: 400;
  opacity: 0.9;
}

/* Branch Content Card */
.branch-content-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 0 0 16px 16px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

/* Section Headers */
.section-header {
  padding: 0.875rem 1.5rem;
}

.manager-header {
  background: linear-gradient(135deg, #ffecd1 0%, #ffd8a8 100%);
  border-bottom: 2px solid #ff9a56;
}

.staff-header-bar {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.section-title {
  font-size: 1rem;
  font-weight: 700;
  color: #ff7e5f;
  margin:  0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.staff-count {
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Manager Card */
.manager-card {
  padding: 1.5rem;
  background: rgba(255, 154, 86, 0.05);
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Staff List */
.staff-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.5rem;
}

.staff-card {
  padding: 1.25rem;
  background: #f9fafb;
  border-radius: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.2s;
}

.staff-card:hover {
  background: #f3f4f6;
  transform: translateX(4px);
}

/* User Info Grid */
.user-info-grid {
  display:  grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  flex:  1;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  font-size:  0.75rem;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size:  0.9375rem;
  font-weight: 600;
  color: #1f2937;
}

/* User Actions */
.user-actions {
  display: flex;
  gap: 0.5rem;
  margin-left: 1rem;
}

.btn-action {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
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

.btn-delete:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

.btn-delete:disabled {
  background: #fca5a5;
  cursor:  not-allowed;
  opacity: 0.8;
}

/* No Data Messages */
.no-manager-notice,
.no-staff-notice {
  padding: 2rem;
  text-align:  center;
  color: #9ca3af;
  font-weight: 600;
}

.no-data-card {
  background: rgba(255, 255, 255, 0.95);
  padding: 3rem;
  border-radius:  16px;
  text-align:  center;
  box-shadow:  0 8px 32px rgba(0, 0, 0, 0.12);
}

.no-data-card p {
  color: #6b7280;
  font-size: 1.125rem;
  font-weight: 600;
  margin:  0;
}
.staff-header-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.btn-deleted-history {
  background: #111827;
  color: #f9fafb;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.btn-deleted-history:hover {
  background: #020617;
  transform: translateY(-2px);
}

</style>
