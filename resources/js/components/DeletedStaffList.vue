<template>
  <div class="deleted-staff-container">
    <!-- Header -->
    <div class="staff-header">
      <h2 class="staff-title">🗑️ Deleted Staff History</h2>
      <a href="/admin/staff-management" class="btn-back-link">
        ← Back to Staff Management
      </a>
    </div>

    <!-- Alert Messages -->
    <transition name="fade">
      <div v-if="alertMessage" :class="['alert', alertType]">
        {{ alertMessage }}
      </div>
    </transition>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="loading-text">Loading deleted accounts...</div>
    </div>

    <!-- Deleted Staff Table -->
    <div v-else class="staff-table-wrapper">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Branch</th>
            <th>Deleted At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in deletedUsers" :key="user. id" class="staff-row">
            <td class="td-username">{{ user.username }}</td>
            <td>{{ user.full_name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span :class="['role-badge', user.role === 'BRANCH_MANAGER' ? 'manager-badge' : 'staff-badge']">
                {{ user.role === 'BRANCH_MANAGER' ? 'Manager' : 'Staff' }}
              </span>
            </td>
            <td>{{ user.branch?. name || 'N/A' }}</td>
            <td>{{ formatDate(user.deleted_at) }}</td>
            <td class="td-actions">
              <button
                @click="restoreAccount(user.id, user.username)"
                class="btn-action btn-restore"
                :disabled="restoringIds.includes(user.id)"
              >
                {{ restoringIds.includes(user.id) ? '⏳' : '🔄 Restore' }}
              </button>
              <button
                v-if="isOwner"
                @click="permanentDelete(user.id, user.username)"
                class="btn-action btn-permanent-delete"
                :disabled="deletingIds.includes(user.id)"
              >
                {{ deletingIds.includes(user.id) ? '⏳' : '🗑️ Delete Forever' }}
              </button>
            </td>
          </tr>
          <tr v-if="deletedUsers.length === 0">
            <td colspan="7" class="no-data">No deleted accounts found</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'DeletedStaffList',
  data() {
    return {
      deletedUsers: [],
      loading: false,
      alertMessage:  '',
      alertType: 'success',
      restoringIds:  [],
      deletingIds: [],
      isOwner: false
    }
  },
  mounted() {
    this.checkUserRole()
    this.fetchDeletedStaff()
  },
  methods: {
    checkUserRole() {
      // You can get this from auth user data
      // For now, assume we pass it from backend
      this.isOwner = window.userRole === 'OWNER'
    },

    async fetchDeletedStaff() {
      this.loading = true
      try {
        const res = await axios.get('/api/admin/deleted-staff')
        if (res.data.success) {
          this.deletedUsers = res.data.data
        }
      } catch (e) {
        this.showAlert('Failed to load deleted accounts', 'error')
      } finally {
        this. loading = false
      }
    },

    async restoreAccount(id, username) {
      if (! confirm(`Restore account "${username}"?`)) return

      this.restoringIds.push(id)
      try {
        const res = await axios.post(`/api/admin/deleted-staff/${id}/restore`)
        if (res.data.success) {
          this.showAlert('Account restored successfully! ', 'success')
          this.fetchDeletedStaff()
        }
      } catch (e) {
        this.showAlert('Failed to restore account', 'error')
      } finally {
        this.restoringIds = this.restoringIds. filter(rid => rid !== id)
      }
    },

    async permanentDelete(id, username) {
      if (!confirm(`⚠️ PERMANENTLY DELETE "${username}"?  This cannot be undone! `)) return

      this.deletingIds.push(id)
      try {
        const res = await axios.delete(`/api/admin/deleted-staff/${id}/force`)
        if (res.data.success) {
          this.showAlert('Account permanently deleted', 'success')
          this.fetchDeletedStaff()
        }
      } catch (e) {
        this.showAlert('Failed to delete account', 'error')
      } finally {
        this.deletingIds = this.deletingIds.filter(did => did !== id)
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    showAlert(message, type) {
      this.alertMessage = message
      this.alertType = type
      setTimeout(() => {
        this.alertMessage = ''
      }, 5000)
    }
  }
}
</script>

<style scoped>
/* Reuse same styles from StaffList. vue */
.deleted-staff-container {
  background: linear-gradient(135deg, #ff9a56 0%, #ff7e5f 100%);
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

.btn-back-link {
  background: rgba(255, 255, 255, 0.95);
  color: #ff7e5f;
  padding: 0.875rem 1.75rem;
  border-radius: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-back-link:hover {
  background: white;
  transform: translateY(-2px);
}

.alert {
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-bottom:  1.5rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.alert.success { background: #10b981; color: white; }
.alert.error { background: #ef4444; color: white; }

.loading-container {
  text-align: center;
  padding: 3rem;
}

.loading-text {
  color: white;
  font-size: 1.25rem;
  font-weight: 600;
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

.staff-row:hover {
  background: rgba(255, 154, 86, 0.08);
}

.staff-table tbody td {
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
}

.td-username {
  font-weight: 600;
  color:  #ff7e5f;
}

.role-badge {
  padding: 0.375rem 0.875rem;
  border-radius:  20px;
  font-size:  0.8125rem;
  font-weight:  700;
  text-transform: uppercase;
}

.manager-badge { background: #ffecd1; color: #ff7e5f; }
.staff-badge { background: #e0e7ff; color: #6366f1; }

.td-actions {
  display: flex;
  gap: 0.5rem;
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

.btn-restore {
  background: #10b981;
  color: white;
}

.btn-restore:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
}

.btn-permanent-delete {
  background: #ef4444;
  color: white;
}

.btn-permanent-delete:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

.btn-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.no-data {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
  font-weight: 600;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
