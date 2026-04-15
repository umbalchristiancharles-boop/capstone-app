<template>
  <div class="staff-management-page">
    <button @click="back()" class="btn-secondary back-to-dashboard-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back to Super Admin
    </button>

    <div class="staff-header">
      <h1 class="owner-staff-title">Super Admin Staff Management</h1>
      <div class="header-actions">
        <input v-model="searchQuery" type="text" placeholder="Search staff..." class="search-input" />
        <select v-model="branchFilter" class="filter-select">
          <option value="">All Branches</option>
          <option v-for="b in branches" :key="b.id" :value="b.name">{{ b.name }}</option>
        </select>
        <button class="btn-primary" @click="refreshStaff">Refresh</button>
        <button class="btn-success" @click="openAddStaffModal">+ Add Staff</button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">Loading staff...</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <div v-if="!loading && groupedStaff.length > 0">
      <div v-for="group in groupedStaff" :key="group.branchName" class="branch-group">
        <div class="branch-header">
          <div class="branch-info">
            <h2 class="branch-title">{{ group.branchName }}{{ group.branchName !== 'Unassigned' ? ' Branch' : '' }}</h2>
            <span class="branch-count">{{ group.staff.length }} staff member{{ group.staff.length !== 1 ? 's' : '' }}</span>
          </div>
          <div v-if="group.defaultPassword" class="branch-password-display">
            <div class="password-card">
              <div class="password-label">Default Password:</div>
              <div class="password-value">
                <span class="password-text" :id="`password-${group.branchName}`">{{ group.defaultPassword }}</span>
                <button 
                  @click="copyToClipboard(`password-${group.branchName}`, group.defaultPassword)" 
                  class="btn-copy"
                  title="Copy to clipboard"
                >
                  📋
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="staff-table-wrapper">
          <table class="staff-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Branch</th>
                <th>Role</th>
                <th>Status</th>
                <th>Tasks</th>
                <th>Time Logs</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in group.staff" :key="m.id">
                <td>{{ m.full_name || m.username }}</td>
                <td>{{ m.branch_name || 'Unassigned' }}</td>
                <td>{{ displayRole(m.role) }}</td>
                <td>{{ m.status || (m.is_active ? (m.is_online ? 'On Duty' : 'Offline') : 'Inactive') }}</td>
                <td>{{ m.tasks || '-' }}</td>
                <td><button class="btn-sm" @click="viewTimeLogs(m)">View Logs</button></td>
                <td>
                  <button class="btn-sm btn-info" @click="editStaff(m)">Edit</button>
                  <button class="btn-sm btn-danger" @click="deleteStaff(m)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="!loading && groupedStaff.length === 0" class="empty-state">No staff found.</div>

    <OwnerStaffModal
      :show="showAddStaffModal"
      :staff="isEditingStaff ? staff.find(s => s.id === editingStaffId) : null"
      :isEdit="isEditingStaff"
      :isViewOnly="false"
      :branchName="editingStaffBranchName"
      :defaultPassword="editingStaffBranchDefaultPassword"
      :editingStaffId="editingStaffId"
      @close="showAddStaffModal = false"
      @success="onStaffModalSuccess"
      @resetPassword="onResetPasswordClick"
    ></OwnerStaffModal>

    <transition name="fade">
      <div v-if="showTimeLogs" class="modal-backdrop" @click.self="showTimeLogs = false">
        <div class="modal">
          <div class="modal-card">
            <div class="modal-header">
              <h2>Time Logs — {{ timeLogStaffName }}</h2>
              <button class="close-button" @click="showTimeLogs = false">×</button>
            </div>
            <div class="modal-body" style="padding:1rem;">
              <div v-if="timeLogsLoading">Loading logs...</div>
              <div v-if="timeLogs.length === 0 && !timeLogsLoading">No logs available.</div>
              <ul v-else>
                <li v-for="log in timeLogs" :key="log.id">{{ formatDateTime(log.time) }} — {{ log.type || log.note || 'log' }}</li>
              </ul>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="showTimeLogs = false">Close</button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import OwnerStaffModal from './OwnerStaffModal.vue'
import { useTheme } from '../composables/useTheme'

const router = useRouter()
const { initializeTheme } = useTheme()

const loading = ref(false)
const errorMessage = ref('')
const staff = ref([])
const branchData = ref({})  // Store branch-level data including passwords
const branches = ref([])
const searchQuery = ref('')
const branchFilter = ref('')

const showAddStaffModal = ref(false)
const isEditingStaff = ref(false)
const editingStaffId = ref(null)
const editingStaffBranchName = ref('')
const editingStaffBranchDefaultPassword = ref('')

const showTimeLogs = ref(false)
const timeLogs = ref([])
const timeLogsLoading = ref(false)
const timeLogStaffName = ref('')

function back() { router.push('/super-admin-panel') }

const groupedStaff = computed(() => {
  const list = staff.value.slice()
  const filtered = list.filter(m => {
    const r = (m.role || '').toString().toUpperCase()
    return !r.includes('HR') && r !== 'HR'
  })
  const byBranch = branchFilter.value ? filtered.filter(m => (m.branch_name||'').toLowerCase() === branchFilter.value.toLowerCase()) : filtered
  const q = (searchQuery.value || '').toLowerCase()
  const searched = q ? byBranch.filter(m => (m.full_name||m.username||'').toLowerCase().includes(q)) : byBranch
  const groups = {}
  searched.forEach(m => {
    const bn = m.branch_name || 'Unassigned'
    if (!groups[bn]) groups[bn] = []
    groups[bn].push(m)
  })
  return Object.keys(groups).sort().map(k => ({
    branchName: k,
    staff: groups[k],
    defaultPassword: branchData.value[k]?.defaultPassword || null,
    defaultPasswordUpdatedAt: branchData.value[k]?.defaultPasswordUpdatedAt || null
  }))
})

function displayRole(r) {
  if (!r) return '-'
  return (r || '').toString().replace(/_/g, ' ')
}

async function loadStaff() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await axios.get('/api/admin/staff', { withCredentials: true })
    if (res.data && (res.data.staff || res.data.data || res.data.success)) {
      if (Array.isArray(res.data.staff)) {
        staff.value = res.data.staff
        branchData.value = {}
      } else if (Array.isArray(res.data.data)) {
        const list = []
        const branches = {}
        res.data.data.forEach(branch => {
          const branchName = branch.branch_name || ''
          branches[branchName] = {
            defaultPassword: branch.default_password,
            defaultPasswordUpdatedAt: branch.default_password_updated_at
          }
          if (branch.branch_manager) list.push({ ...branch.branch_manager, branch_name: branchName })
          if (Array.isArray(branch.hr)) branch.hr.forEach(h => list.push({ ...h, branch_name: branchName }))
          if (Array.isArray(branch.staff)) branch.staff.forEach(s => list.push({ ...s, branch_name: branchName }))
        })
        staff.value = list
        branchData.value = branches
      } else staff.value = []
    } else {
      errorMessage.value = 'Failed to load staff.'
    }
  } catch (e) {
    console.error(e)
    errorMessage.value = 'Error loading staff.'
  } finally {
    loading.value = false
  }
}

async function loadBranches() {
  try {
    const res = await axios.get('/api/admin/branches', { withCredentials: true })
    if (res.data && res.data.success && Array.isArray(res.data.data)) branches.value = res.data.data
  } catch (e) { console.warn('branches load failed', e) }
}

function refreshStaff() { loadStaff() }

function openAddStaffModal() {
  isEditingStaff.value = false
  editingStaffId.value = null
  showAddStaffModal.value = true
}

function editStaff(member) {
  isEditingStaff.value = true
  editingStaffId.value = member.id
  editingStaffBranchName.value = member.branch_name || ''
  editingStaffBranchDefaultPassword.value = branchData.value[member.branch_name]?.defaultPassword || ''
  
  // Fetch password if not available, then open modal
  if (!editingStaffBranchDefaultPassword.value && member.branch_id) {
    fetchBranchPassword(member.branch_id, () => {
      showAddStaffModal.value = true
    })
  } else {
    showAddStaffModal.value = true
  }
}

async function onStaffModalSuccess(payload) {
  try {
    if (payload && payload.form) {
      if (payload.isEdit && payload.staffId) {
        await axios.put(`/api/superadmin/staff/${payload.staffId}`, payload.form, { withCredentials: true })
        alert('Staff updated successfully')
      } else {
        // Create new staff via admin endpoint (SuperAdmin can create via admin endpoint)
        await axios.post('/api/admin/staff', payload.form, { withCredentials: true })
        alert('Staff created successfully')
      }
    }
  } catch (e) {
    console.error('Staff save error:', e)
    const errorMsg = e?.response?.data?.message || e?.response?.data?.errors?.full_name?.[0] || e?.message || 'Failed to save staff'
    alert(errorMsg)
  } finally {
    showAddStaffModal.value = false
    await loadStaff()
  }
}

async function deleteStaff(member) {
  if (!confirm('Delete this staff member? This action cannot be undone.')) return
  try {
    const res = await axios.delete(`/api/admin/staff/${member.id}`, { withCredentials: true })
    if (res.data && (res.data.success || res.data.ok)) {
      alert('Deleted')
      await loadStaff()
    } else {
      alert(res.data?.message || 'Delete failed')
    }
  } catch (e) {
    console.error(e)
    alert('Delete failed')
  }
}

async function viewTimeLogs(member) {
  timeLogsLoading.value = true
  timeLogStaffName.value = member.full_name || member.username
  showTimeLogs.value = true
  try {
    const res = await axios.get(`/api/staff/${member.id}/timelogs`, { withCredentials: true })
    if (res.data && Array.isArray(res.data.data)) timeLogs.value = res.data.data
    else timeLogs.value = []
  } catch (e) {
    console.warn('timelogs load failed', e)
    timeLogs.value = []
  } finally {
    timeLogsLoading.value = false
  }
}

function formatDateTime(s) {
  try { return new Date(s).toLocaleString() } catch (e) { return s }
}

function copyToClipboard(elementId, text) {
  navigator.clipboard.writeText(text).then(() => {
    alert('Password copied to clipboard!')
  }).catch(err => {
    console.error('Failed to copy:', err)
    alert('Failed to copy to clipboard')
  })
}

async function fetchBranchPassword(branchId, callback) {
  try {
    const res = await axios.get(`/api/admin/staff/branch/default-password`, { withCredentials: true })
    if (res.data.success && res.data.data.defaultPassword) {
      editingStaffBranchDefaultPassword.value = res.data.data.defaultPassword
      console.log('Loaded default password:', editingStaffBranchDefaultPassword.value)
    }
  } catch (e) {
    console.warn('Failed to fetch branch password:', e)
  } finally {
    if (callback) callback()
  }
}

async function onResetPasswordClick(staffId) {
  // Call the reset password endpoint
  try {
    const res = await axios.post(`/api/admin/staff/${staffId}/reset-password`, {}, { withCredentials: true })
    
    if (res.data.success) {
      alert('Password reset to default successfully!')
      loadStaff()
    } else {
      alert(res.data.message || 'Failed to reset password')
    }
  } catch (error) {
    console.error('Reset password error:', error)
    alert('Error resetting password: ' + (error.response?.data?.message || error.message))
  }
}

onMounted(async () => {
  initializeTheme()
  await loadBranches()
  await loadStaff()
})
</script>

<style>
/* styles copied from HRStaffManagement.vue for exact match */
.staff-management-page {
  padding: 30px;
  background-color: #F8FAFC;
  min-height: 100vh;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.staff-management-page h1,
.staff-management-page h2 {
  font-family: 'Inter', 'Poppins', sans-serif;
  font-weight: 800;
  color: var(--text-dark);
}

.admin-label, .metric-label, .overview-label, .branch-count {
  color: #64748B !important;
}

.avatar-change-text {
  color: var(--text-dark) !important;
}

.btn-primary {
  background: #0066FF !important;
  color: white !important;
}

.btn-primary:hover {
  background: #3B82F6 !important;
}

.btn-secondary, .btn-outline {
  background: #64748B !important;
  color: white !important;
}

.btn-secondary:hover, .btn-outline:hover {
  background: #525c6a !important;
}

.staff-header h1 {
  margin: 0;
  font-size: 2.5rem;
  font-weight: 700;
  letter-spacing: -1px;
}

.owner-staff-title {
  font-family: 'Inter', 'Poppins', sans-serif !important;
  font-weight: 800 !important;
  letter-spacing: -0.5px !important;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
}

.search-input {
  padding: 0.75rem 1rem;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 0.9rem;
  width: 280px;
}

.search-input:focus {
  outline: none;
  border-color: #0066FF;
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
}

.btn-primary, .btn-success, .btn-secondary, .btn-info, .btn-danger {
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  transition: background 0.3s ease;
}

.btn-primary {
  background: #0066FF;
  color: white;
}

.btn-primary:hover {
  background: #3B82F6;
}

.btn-success {
  background: #10B981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-info {
  background: #3B82F6;
  color: white;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
  border-radius: 6px;
}

.btn-info:hover {
  background: #2563EB;
}

.btn-danger {
  background: #EF4444;
  color: white;
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
  border-radius: 6px;
}

.btn-danger:hover {
  background: #DC2626;
}

.btn-sm {
  padding: 0.35rem 0.7rem;
  font-size: 0.8rem;
}

/* space between adjacent small buttons (Edit / Delete) */
.staff-table td .btn-sm + .btn-sm {
  margin-left: 0.6rem;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 8px 16px;
  font-size: 0.9rem;
  font-weight: 600;
  border-radius: 8px;
}

.back-icon {
  flex-shrink: 0;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  margin-bottom: 2rem;
}

.summary-card h3 {
  margin: 0;
  color: #222;
}

.owner-staff-total {
  color: #ffffff !important;
}

.branch-group {
  margin-bottom: 2rem;
}

.branch-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1rem 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border-left: 4px solid #0066FF;
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
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  overflow: hidden;
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
}

.staff-table th {
  background: #EFF6FF;
  color: #1E3A8A;
  font-weight: 600;
  padding: 1rem;
  text-align: left;
  font-size: 0.9rem;
}

.staff-table td {
  border-bottom: 1px solid #E5E7EB;
  padding: 1rem;
  color: #374151;
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
  background: #FACC15;
  color: #1F2937;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-online {
  background: #10B981;
  color: white;
}

.badge-offline {
  background: #6B7280;
  color: white;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.empty-state, .loading-state {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 12px;
  color: #6B7280;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.alert {
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.alert-danger {
  background: #FEF2F2;
  color: #DC2626;
  border: 1px solid #FECACA;
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

/* ===== DARK MODE SUPPORT ===== */
.dark-mode .staff-management-page {
  background-color: #1a1a1a !important;
  color: #e5e7eb !important;
}

.dark-mode .staff-header {
  background: #2d2d2d !important;
  border-bottom: 1px solid #3f3f3f;
}

.dark-mode .owner-staff-title {
  color: #ffffff !important;
}

.dark-mode .search-input,
.dark-mode .filter-select {
  background: #1f1f1f !important;
  color: #e5e7eb !important;
  border: 1px solid #404040 !important;
}

.dark-mode .search-input::placeholder {
  color: #9ca3af !important;
}

.dark-mode .search-input:focus,
.dark-mode .filter-select:focus {
  background: #2a2a2a !important;
  border-color: #ff8a50 !important;
  box-shadow: 0 0 0 3px rgba(255, 138, 80, 0.1) !important;
}

.dark-mode .summary-card {
  background: #2d2d2d !important;
  color: #e5e7eb !important;
  border: 1px solid #3f3f3f !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
}

.dark-mode .summary-card h3 {
  color: #ffffff !important;
}

.dark-mode .branch-group {
  background: #252525 !important;
  border: 1px solid #3f3f3f !important;
  border-radius: 8px;
  padding: 0;
}

.dark-mode .branch-header {
  background: #2a2a2a !important;
  border-bottom: 1px solid #404040 !important;
  border-left: 4px solid #ff8a50 !important;
  color: #e5e7eb;
  box-shadow: none !important;
}

.dark-mode .branch-title {
  color: #ffffff !important;
}

.dark-mode .branch-count {
  color: #d1d5db !important;
  background: rgba(255, 138, 80, 0.1) !important;
}

.dark-mode .staff-table-wrapper {
  background: #1f1f1f !important;
  border: 1px solid #3f3f3f !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
}

.dark-mode .staff-table {
  background: #1f1f1f !important;
}

.dark-mode .staff-table th {
  background: #262626 !important;
  color: #ffffff !important;
  border-bottom: 2px solid #404040 !important;
}

.dark-mode .staff-table td {
  color: #d1d5db !important;
  border-bottom: 1px solid #3f3f3f !important;
}

.dark-mode .staff-table tbody tr:hover {
  background: #262626 !important;
}

.dark-mode .staff-table tbody tr.inactive {
  opacity: 0.6;
  background: #1f1f1f !important;
}

.dark-mode .staff-info strong {
  color: #ffffff !important;
}

.dark-mode .avatar {
  border: 1px solid #404040 !important;
}

.dark-mode .badge {
  background: #3f3f3f !important;
  color: #e5e7eb !important;
}

.dark-mode .badge-online {
  background: rgba(34, 197, 94, 0.2) !important;
  color: #4ade80 !important;
}

.dark-mode .badge-offline {
  background: rgba(107, 114, 128, 0.2) !important;
  color: #d1d5db !important;
}

.dark-mode .empty-state,
.dark-mode .loading-state {
  background: #2d2d2d !important;
  color: #9ca3af !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
}

.dark-mode .alert {
  background: #2a2a2a !important;
  color: #e5e7eb !important;
}

.dark-mode .alert-danger {
  background: rgba(239, 68, 68, 0.1) !important;
  color: #fca5a5 !important;
  border: 1px solid #7f1d1d !important;
}

.dark-mode .back-to-dashboard-btn {
  background: #4b5563 !important;
  color: #ffffff !important;
  border: 1px solid #5a6580 !important;
}

.dark-mode .back-to-dashboard-btn:hover {
  background: #5a6580 !important;
  border-color: #ff8a50 !important;
}

.dark-mode .btn-primary {
  background: #0ea5e9 !important;
  color: white !important;
}

.dark-mode .btn-primary:hover {
  background: #0284c7 !important;
}

.dark-mode .btn-success {
  background: #10b981 !important;
  color: white !important;
}

.dark-mode .btn-success:hover {
  background: #059669 !important;
}

.dark-mode .btn-secondary {
  background: #4b5563 !important;
  color: #ffffff !important;
  border: 1px solid #5a6580 !important;
}

.dark-mode .btn-secondary:hover {
  background: #5a6580 !important;
  border-color: #ff8a50 !important;
}

.dark-mode .btn-info {
  background: #0ea5e9 !important;
  color: white !important;
}

.dark-mode .btn-info:hover {
  background: #0284c7 !important;
}

.dark-mode .btn-danger {
  background: #ef4444 !important;
  color: white !important;
}

.dark-mode .btn-danger:hover {
  background: #dc2626 !important;
}

.dark-mode .btn-sm {
  background: #2a2a2a !important;
  color: #e5e7eb !important;
  border: 1px solid #404040 !important;
}

.dark-mode .btn-sm:hover {
  background: #3a3a3a !important;
  border-color: #ff8a50 !important;
  color: #ffffff !important;
}

/* ===== TABLE STYLING ===== */
.staff-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.staff-table th {
  background: #EFF6FF;
  color: #1E3A8A;
  font-weight: 600;
  padding: 1rem;
  text-align: left;
  font-size: 0.9rem;
  border-right: 1px solid #E5E7EB;
}

.staff-table th:last-child {
  border-right: none;
}

.staff-table td {
  border-bottom: 1px solid #E5E7EB;
  border-right: 1px solid #E5E7EB;
  padding: 1rem;
  color: #374151;
}

.staff-table td:last-child {
  border-right: none;
}

.staff-table tbody tr:hover {
  background: rgba(0, 102, 255, 0.05);
}

.staff-table tbody tr.inactive {
  opacity: 0.7;
  background: #f8f9fa;
}

/* ===== DARK MODE TABLE STYLING ===== */
.dark-mode .staff-table {
  background: #1f1f1f;
  color: #e5e7eb;
}

.dark-mode .staff-table th {
  background: #262626 !important;
  color: #ffffff !important;
  border-right: 1px solid #404040 !important;
  border-bottom: 2px solid #404040 !important;
}

.dark-mode .staff-table th:last-child {
  border-right: none;
}

.dark-mode .staff-table td {
  color: #d1d5db !important;
  border-bottom: 1px solid #3f3f3f !important;
  border-right: 1px solid #3f3f3f !important;
}

.dark-mode .staff-table td:last-child {
  border-right: none;
}

.dark-mode .staff-table tbody tr {
  background: #1f1f1f;
}

.dark-mode .staff-table tbody tr:hover {
  background: rgba(255, 138, 80, 0.05) !important;
}

.dark-mode .staff-table tbody tr.inactive {
  opacity: 0.6;
  background: #262626;
}

/* Branch Password Display Styles */
.branch-info {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.branch-password-display {
  margin-left: auto;
}

.password-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  padding: 0.75rem 1.25rem;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.password-label {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.password-value {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.password-text {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  padding: 0.5rem 0.75rem;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.3px;
  user-select: all;
}

.btn-copy {
  background: rgba(255, 255, 255, 0.25);
  border: none;
  color: #ffffff;
  padding: 0.35rem 0.6rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.btn-copy:hover {
  background: rgba(255, 255, 255, 0.4);
  transform: scale(1.05);
}

.btn-copy:active {
  transform: scale(0.98);
}

.dark-mode .password-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

  .branch-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .branch-password-display {
    width: 100%;
    margin-left: 0;
  }
}
</style>
