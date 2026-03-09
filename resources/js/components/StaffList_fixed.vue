<template>
  <div class="staff-management-page">
    <!-- Back to Dashboard Button -->
    <button @click="goToAdminPanel" class="btn-secondary back-to-dashboard-btn">
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
        <button @click="openCreateModal()" class="btn-success">+ Add Staff</button>
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

    <!-- Alert Messages -->
    <transition name="fade">
      <div v-if="alertMessage" :class="['alert', alertType]">
        {{ alertMessage }}
      </div>
    </transition>

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
                    @click="confirmDelete(member.id, member.username)"
                    class="btn-sm btn-danger"
                    title="Delete"
                    :disabled="deletingIds.includes(member.id)"
                  >
                    {{ deletingIds.includes(member.id) ? 'Deleting...' : 'Delete' }}
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
      :show="showModal"
      :staff="selectedStaff"
      :isEdit="!!selectedStaff"
      :branchManagerMode="isBranchManager"
      :branchForManager="managerBranchId"
      @close="closeModal"
      @success="handleSaved"
    />
  </div>
</template>

<script>
import axios from 'axios'
import StaffModal from './StaffModal.vue'

export default {
  name: 'StaffList',
  components: {
    StaffModal,
  },
  data() {
    return {
      // Staff data
      staff: [],
      branches: [],
      loading: false,
      errorMessage: '',
      searchQuery: '',
      
      // Filters
      branchFilter: '',
      roleFilter: '',
      departmentFilter: '',
      
      // Modal
      showModal: false,
      selectedStaff: null,
      
      // User info
      isBranchManager: false,
      managerBranchId: null,
      currentUserId: null,
      currentUserRole: null,
      
      // Alert
      alertMessage: '',
      alertType: 'success',
      
      // Deleting
      deletingIds: [],
    }
  },
  computed: {
    defaultRoles() {
      return [
        'Manager HR', 'Manager Finance', 'Manager Inventory', 'Manager Logistics',
        'Staff Cashier', 'Staff Finance', 'Staff Inventory'
      ]
    },
    availableRoles() {
      const set = new Set(this.defaultRoles)
      this.staff.forEach(m => { if (m.role) set.add(m.role) })
      return Array.from(set).sort()
    },
    availableDepartments() {
      const set = new Set()
      this.staff.forEach(m => { if (m.department) set.add(m.department) })
      return Array.from(set).sort()
    },
    filteredStaff() {
      let filtered = this.staff.slice()

      // Role filter
      if (this.roleFilter) {
        filtered = filtered.filter(m => (m.role || '').toString() === this.roleFilter)
      } else {
        // default: include STAFF and MANAGER variants (exclude OWNER)
        filtered = filtered.filter(member => {
          const r = (member.role || '').toUpperCase()
          return r.includes('STAFF') || r.includes('MANAGER') || r === 'HR' || r === 'BRANCH_MANAGER'
        })
      }

      // Branch filter
      if (this.branchFilter) {
        const selectedBranch = this.branchFilter.toLowerCase()
        filtered = filtered.filter(m => (m.branch_name || '').toString().toLowerCase() === selectedBranch)
      }

      // Department filter
      if (this.departmentFilter) {
        filtered = filtered.filter(m => (m.department || '').toString() === this.departmentFilter)
      }

      // Search query
      if (this.searchQuery && this.searchQuery.trim()) {
        const q = this.searchQuery.toLowerCase()
        filtered = filtered.filter(member =>
          (member.full_name && member.full_name.toLowerCase().includes(q)) ||
          (member.username && member.username.toLowerCase().includes(q)) ||
          (member.email && member.email.toLowerCase().includes(q))
        )
      }

      return filtered
    },
    groupedStaff() {
      const groups = {}

      this.filteredStaff.forEach(member => {
        const branchName = member.branch_name || 'Unassigned'
        if (!groups[branchName]) {
          groups[branchName] = []
        }
        groups[branchName].push(member)
      })

      // Sort staff within each branch by role priority
      Object.keys(groups).forEach(branch => {
        groups[branch].sort((a, b) => {
          const priorityA = this.getRolePriority(a.role)
          const priorityB = this.getRolePriority(b.role)
          return priorityA - priorityB
        })
      })

      // Get all branch names sorted alphabetically
      const sortedBranchNames = Object.keys(groups).sort()

      return sortedBranchNames
        .filter(branchName => branchName.toLowerCase() !== 'owners')
        .map(branchName => ({
          branchName,
          staff: groups[branchName]
        }))
    }
  },
  async mounted() {
    await this.setCurrentUserRole()
    await this.ensureCsrf()
    await this.fetchStaff()
  },
  methods: {
    getRolePriority(role) {
      const rolePriority = {
        'OWNER': 1,
        'MANAGER_HR': 2,
        'MANAGER_FINANCE': 3,
        'MANAGER_INVENTORY': 4,
        'MANAGER_LOGISTICS': 5,
        'MANAGER': 6,
        'BRANCH_MANAGER': 6,
        'STAFF': 7,
        'STAFF_CASHIER': 8,
        'STAFF_FINANCE': 9,
        'STAFF_INVENTORY': 10
      }
      const upperRole = (role || '').toUpperCase()
      return rolePriority[upperRole] || 999
    },
    
    async setCurrentUserRole() {
      try {
        const res = await axios.get('/api/me', { withCredentials: true })
        if (res.data?.ok && res.data.user) {
          this.currentUserRole = res.data.user.role
          this.isBranchManager = res.data.user.role === 'BRANCH_MANAGER'
          this.currentUserId = res.data.user.id
          this.managerBranchId = res.data.user.branch_id || (res.data.user.branch && res.data.user.branch.id) || null
        }
      } catch (e) {
        console.warn('Could not determine current user role', e)
      }
    },
    
    async ensureCsrf() {
      try {
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        if (metaToken) axios.defaults.headers.common['X-CSRF-TOKEN'] = metaToken
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true }).catch(() => {})
        function getCookie(name) { const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return match ? match[2] : null }
        const xsrf = getCookie('XSRF-TOKEN')
        if (xsrf) {
          try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrf) } 
          catch (e) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf }
        }
      } catch (e) { console.warn('Failed to refresh CSRF/XSRF tokens', e) }
    },
    
    async fetchStaff() {
      this.loading = true
      this.errorMessage = ''
      this.alertMessage = ''

      try {
        const res = await axios.get('/api/admin/staff')

        if (res.status === 401 || res.data?.status === 401) {
          this.showAlert('Not authenticated. Please login again.', 'error')
          setTimeout(() => {
            window.location.href = '/login'
          }, 2000)
          return
        }

        const data = res.data

        if (data.success) {
          // Transform data to match expected format
          const list = []
          if (Array.isArray(data.data)) {
            data.data.forEach(branch => {
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
          }
          this.staff = list
          
          // Load branches for filter
          await this.fetchBranches()
        } else {
          this.errorMessage = data.message || 'Failed to load staff data'
        }
      } catch (error) {
        this.errorMessage = 'Failed to load staff data'
        console.error('Staff fetch error:', error)
      } finally {
        this.loading = false
        // Clean up overlay
        try {
          if (window.__chikin_temp_overlay) {
            window.__chikin_temp_overlay.remove()
            window.__chikin_temp_overlay = null
          }
        } catch (e) {}
        try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
      }
    },
    
    async fetchBranches() {
      try {
        const res = await axios.get('/api/admin/branches', { withCredentials: true })
        if (res.data && res.data.success && Array.isArray(res.data.data)) {
          this.branches = res.data.data
        }
      } catch (e) {
        console.error('Failed loading branches', e)
      }
    },
    
    refreshStaff() {
      this.fetchStaff()
    },
    
    openCreateModal() {
      this.selectedStaff = null
      this.showModal = true
    },
    
    editStaff(staff) {
      this.selectedStaff = Object.assign({}, staff)
      this.showModal = true
    },
    
    closeModal() {
      this.showModal = false
      this.selectedStaff = null
    },
    
    handleSaved() {
      this.closeModal()
      this.fetchStaff()
      this.showAlert('Account saved successfully!', 'success')
    },
    
    async confirmDelete(id, username) {
      if (!confirm(`Are you sure you want to delete "${username}"?`)) {
        return
      }

      this.deletingIds.push(id)

      try {
        const res = await axios.delete(`/api/admin/staff/${id}`)
        const data = res.data

        if (res.status === 200 && data.success) {
          this.showAlert(data.message || 'Account deleted successfully!', 'success')
          this.fetchStaff()
        } else {
          this.showAlert(data.message || 'Failed to delete account', 'error')
        }
      } catch (error) {
        this.showAlert(error.response?.data?.message || 'Failed to delete account', 'error')
      } finally {
        this.deletingIds = this.deletingIds.filter(delId => delId !== id)
      }
    },
    
    showAlert(message, type) {
      this.alertMessage = message
      this.alertType = type
      setTimeout(() => {
        this.alertMessage = ''
      }, 5000)
    },
    
    goToAdminPanel() {
      try {
        if (window.__chikin_temp_overlay) return
        const overlay = document.createElement('div')
        overlay.className = 'loading-overlay __chikin_temp_overlay'
        overlay.style.zIndex = '9999'
        overlay.style.backdropFilter = 'blur(8px)'
        overlay.style.webkitBackdropFilter = 'blur(8px)'
        const logo = new URL('../assets/chikinlogo.png', import.meta.url).href
        overlay.innerHTML = `
          <div class="logo-loading-box">
            <img src="${logo}" alt="Chikin Tayo" class="logo-loading-img" />
            <p>Loading dashboard...</p>
          </div>
        `
        document.body.appendChild(overlay)
        window.__chikin_temp_overlay = overlay
        try { if (window.pageBlur && typeof window.pageBlur.show === 'function') window.pageBlur.show() } catch (e) {}

        const dashboardRoute = this.currentUserRole === 'HR'
          ? '/hr-panel'
          : (this.currentUserRole === 'BRANCH_MANAGER' ? '/manager-panel' : '/admin-panel')

        this.$router.push(dashboardRoute).catch(() => {
          try {
            if (window.__chikin_temp_overlay) {
              window.__chikin_temp_overlay.remove()
              window.__chikin_temp_overlay = null
            }
          } catch (e) {}
        })
      } catch (e) {
        const dashboardRoute = this.currentUserRole === 'HR'
          ? '/hr-panel'
          : (this.currentUserRole === 'BRANCH_MANAGER' ? '/manager-panel' : '/admin-panel')
        this.$router.push(dashboardRoute)
      }
    },
    
    displayRole(r) {
      const role = (r || '').toString().toUpperCase()
      if (role === 'BRANCH_MANAGER') return 'Manager'
      if (role === 'STAFF') return 'Staff'
      if (role === 'HR') return 'HR'
      if (role === 'OWNER') return 'Owner'
      return role.replace(/_/g, ' ')
    },
    
    formatDate(dateString) {
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
  },
  watch: {
    $route(to, from) {
      if (to.path === '/staff-management') {
        this.fetchStaff()
      }
    }
  }
}
</script>

<style scoped>
/* Main Container - Orange Gradient */
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
  flex-wrap: wrap;
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

/* Branch Group Styles */
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

.alert.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
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
