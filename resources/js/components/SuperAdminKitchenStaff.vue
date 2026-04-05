<template>
  <div class="staff-management-page">
    <button @click="router.push('/super-admin-panel')" class="btn-secondary back-to-dashboard-btn">
      ← Back to Super Admin
    </button>

    <div class="staff-header">
      <h1 class="owner-staff-title">Kitchen Staff Monitoring</h1>
      <div class="header-actions">
        <input v-model="searchQuery" type="text" placeholder="Search kitchen staff..." class="search-input" />

        <select v-model="branchFilter" class="filter-select">
          <option value="">All Branches</option>
          <option v-for="b in branches" :key="b.id" :value="b.name">{{ b.name }}</option>
        </select>

        <button @click="refreshAll" class="btn-primary">Refresh</button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <p>Loading kitchen staff...</p>
    </div>

    <div v-if="!loading && errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <div v-if="!loading && kitchenStaff.length === 0" class="empty-state">
      <p>No kitchen staff found.</p>
    </div>

    <div v-if="!loading && kitchenStaff.length > 0">
      <div class="summary-card">
        <h3 class="owner-staff-total">Kitchen Staff Members: {{ filteredStaff.length }}</h3>
      </div>

      <div class="staff-table-wrapper">
        <table class="staff-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Branch</th>
              <th>Role</th>
              <th>Status</th>
              <th>Current Tasks / Orders</th>
              <th>Time Logs (view-only)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="member in filteredStaff" :key="member.id">
              <td>
                <div class="staff-info">
                  <img v-if="member.avatar_url || member.avatarUrl" :src="member.avatar_url || member.avatarUrl" :alt="member.full_name || member.username" class="avatar" />
                  <strong>{{ member.full_name || member.fullName || member.username }}</strong>
                </div>
              </td>
              <td>{{ member.branch_name || member.branch || 'Unassigned' }}</td>
              <td>{{ displayRole(member.role) || (member.department || '-') }}</td>
              <td>
                <span :class="['badge', member.is_online ? 'badge-online' : 'badge-offline']">
                  {{ memberStatus(member) }}
                </span>
              </td>
              <td>
                <div v-if="tasksMap[member.id] && tasksMap[member.id].length">
                  <div v-for="t in tasksMap[member.id]" :key="t.id" style="margin-bottom:6px;">
                    <strong style="font-size:0.95rem">{{ t.title || t.order_code || t.id }}</strong>
                    <div style="font-size:0.85rem;color:#555">{{ t.meta || t.description || t.customer || '' }}</div>
                  </div>
                </div>
                <div v-else class="muted">—</div>
              </td>
              <td>
                <div v-if="timeLogsMap[member.id] && timeLogsMap[member.id].length">
                  <div v-for="log in timeLogsMap[member.id].slice(0,3)" :key="log.id" style="font-size:0.85rem;color:#444;margin-bottom:4px;">
                    <div>{{ formatTimeLog(log) }}</div>
                  </div>
                </div>
                <div v-else class="muted">No logs</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import { useTheme } from '../composables/useTheme'

const router = useRouter()
const { initializeTheme } = useTheme()
const loading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const kitchenStaff = ref([])
const branches = ref([])
const branchFilter = ref('')
const tasksMap = ref({})
const timeLogsMap = ref({})

let pollInterval = null

function isKitchenMember(m) {
  if (!m) return false
  const role = (m.role || m.role_name || '').toString().toLowerCase()
  const dept = (m.department || m.department_name || '').toString().toLowerCase()
  const title = (m.full_name || m.username || '').toString().toLowerCase()
  if (role.includes('kitchen') || dept.includes('kitchen')) return true
  // some staff entries may have role names like 'staff' but department kitchen
  return false
}

const filteredStaff = computed(() => {
  let list = kitchenStaff.value.slice()
  if (branchFilter.value) {
    const b = branchFilter.value.toString().toLowerCase()
    list = list.filter(m => ((m.branch_name || m.branch || '') + '').toString().toLowerCase() === b)
  }
  if (searchQuery.value && searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(m => (m.full_name || m.username || m.email || '').toString().toLowerCase().includes(q))
  }
  return list
})

function memberStatus(m) {
  if (!m) return '-'
  if (m.on_duty || m.is_on_duty) return 'On Duty'
  if (m.is_online) return 'Online'
  if (m.is_active === false || m.is_active === 0) return 'Inactive'
  return 'Off Duty'
}

function displayRole(r) {
  if (!r) return ''
  return (r || '').toString().replace(/_/g, ' ')
}

function formatTimeLog(log) {
  try {
    const t = log.time || log.created_at || log.timestamp || log.clock || ''
    const tag = log.type || log.action || ''
    return `${tag} • ${t}`
  } catch (e) { return '' }
}

async function loadBranches() {
  try {
    const res = await axios.get('/api/admin/branches', { withCredentials: true })
    if (res.data && res.data.success && Array.isArray(res.data.data)) branches.value = res.data.data
  } catch (e) { console.error('branches load failed', e) }
}

async function loadStaff() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await axios.get('/api/superadmin/all-staff', { withCredentials: true })
    if (res.data && res.data.ok) {
      // filter kitchen staff heuristically
      kitchenStaff.value = (res.data.staff || []).filter(s => isKitchenMember(s) || (s.department && (s.department+'').toString().toLowerCase().includes('kitchen')))
    } else {
      kitchenStaff.value = []
    }
  } catch (e) {
    console.error('Failed to load staff', e)
    errorMessage.value = 'Error loading staff.'
    kitchenStaff.value = []
  } finally {
    loading.value = false
  }
}

async function loadTasks() {
  // try a consolidated endpoint first; fallback silently if not available
  try {
    const res = await axios.get('/api/superadmin/staff-current-tasks', { withCredentials: true })
    if (res.data && res.data.ok && res.data.tasks) {
      tasksMap.value = res.data.tasks
      return
    }
  } catch (e) { /* ignore */ }

  // fallback: clear tasks map
  tasksMap.value = {}
}

async function loadTimeLogs() {
  try {
    const res = await axios.get('/api/superadmin/staff-time-logs', { withCredentials: true })
    if (res.data && res.data.ok && res.data.logs) {
      timeLogsMap.value = res.data.logs
      return
    }
  } catch (e) { /* ignore */ }
  timeLogsMap.value = {}
}

async function refreshAll() {
  await Promise.all([loadStaff(), loadTasks(), loadTimeLogs()])
}

onMounted(async () => {
  initializeTheme()
  await loadBranches()
  await refreshAll()
  // Poll every 10s for updates to approximate realtime
  pollInterval = setInterval(() => {
    refreshAll().catch(() => {})
  }, 10000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<style scoped>
/* Updated to match HR Staff Management visual style */
.staff-management-page {
  padding: 2rem;
  background: #f8fafc; /* neutral page background to match HR page */
  min-height: 100vh;
  font-family: 'Inter', 'Poppins', sans-serif;
  color: #1f2937;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  border-radius: 6px;
}

.staff-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: #ffffff;
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(15,23,42,0.06);
}

.owner-staff-title {
  margin: 0;
  font-size: 2.25rem;
  font-weight: 800;
  color: #2b2b2b;
}

.header-actions { display: flex; gap: 1rem; align-items: center }
.filter-select, .search-input {
  padding: 0.6rem 0.9rem;
  border: 1px solid #e6eef7;
  border-radius: 8px;
  background: #fff;
  font-size: 0.95rem;
}
.search-input { width: 300px }

.btn-primary {
  background: #0066FF;
  color: #fff;
  border: none;
  padding: 0.6rem 0.9rem;
  border-radius: 8px;
  cursor: pointer;
}
.btn-primary:hover { background: #0057e6 }

.btn-secondary {
  background: #64748B;
  color: #fff;
  border: none;
  padding: 0.5rem 0.85rem;
  border-radius: 8px;
  cursor: pointer;
}

.summary-card {
  background: #ffffff;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  margin-bottom: 1rem;
  box-shadow: 0 2px 6px rgba(15,23,42,0.04);
}
.owner-staff-total { margin: 0; color: #111827; font-weight: 700 }

.staff-table-wrapper {
  background: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(15,23,42,0.04);
}

.staff-table { width: 100%; border-collapse: collapse; font-size: 0.95rem }
.staff-table thead { background: #eff6ff }
.staff-table th { padding: 1rem; text-align: left; font-weight: 700; color: #0f172a }
.staff-table td { padding: 0.9rem 1rem; border-bottom: 1px solid #eef2f6; color: #111827 }
.staff-table tbody tr:hover { background: #fbfdff }

.staff-info { display: flex; align-items: center; gap: 0.75rem }
.avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover }

.muted { color: #6b7280 }
.badge { padding: 0.25rem 0.6rem; border-radius: 999px; font-weight: 600; font-size: 0.8rem }
.badge-online { background: #10b981; color: #fff }
.badge-offline { background: #6b7280; color: #fff }

.loading-state, .empty-state { text-align: center; padding: 2rem; background: #fff; border-radius: 8px }
.alert-danger { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px }

@media (max-width: 900px) {
  .search-input { width: 100% }
  .header-actions { flex-direction: column; align-items: stretch }
}

</style>
