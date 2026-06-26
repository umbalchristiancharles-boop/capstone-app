<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Manager HR Panel'"
    :panelDescription="'Manage staff, view HR reports, and monitor staff status.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #profileFooter>
      <div class="admin-actions-row">
        <button class="staff-btn staff-btn--center" @click="goToStaffManagement()">
          Staff Management
        </button>
      </div>
    </template>
    <template #main>
      <!-- HR Positions Management -->
      <!-- POSITIONS REQUEST MODAL -->
      <transition name="fade">
        <div v-if="showPositionsModal" class="positions-modal-backdrop" @click.self="closePositionsModal">
          <div class="positions-modal">
            <div class="positions-modal__header">
              <div>
                <h3>Request Open Positions</h3>
                <p class="muted">Select a position, then set quantity and notes.</p>
              </div>
              <button class="modal-close" @click="closePositionsModal" aria-label="Close">✕</button>
            </div>

            <div class="positions-modal__body">
              <div v-if="positionsLoading" class="loading-box">Loading positions...</div>
              <div v-else-if="positions.length === 0" class="empty-box">No active positions found.</div>

              <div v-else class="positions-list">
                <div v-for="p in positions" :key="p.id" class="position-row">
                  <div class="position-row__meta">
                    <div class="position-row__name">{{ p.name }}</div>
                    <div class="position-row__dept">{{ p.department || '—' }}</div>
                  </div>

                  <div class="position-row__inputs">
                    <label class="field">
                      <span class="field-label">Quantity</span>
                      <input
                        type="number"
                        min="1"
                        class="field-input"
                        v-model.number="requestQuantities[p.id]"
                        :placeholder="'1'"
                      />
                    </label>

                    <label class="field">
                      <span class="field-label">Notes</span>
                      <textarea
                        class="field-textarea"
                        rows="2"
                        v-model.trim="requestNotes[p.id]"
                        placeholder="Optional"
                      ></textarea>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="positions-modal__footer">
              <button class="btn-secondary" @click="closePositionsModal" :disabled="submittingPositions">Cancel</button>
              <button
                class="btn-primary"
                @click="submitPositionsRequests"
                :disabled="submittingPositions || positionsLoading"
              >
                {{ submittingPositions ? 'Submitting...' : 'Submit Request(s)' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- HR Positions Management -->
      <div class="positions-top-actions">
        <button class="panel-action panel-action--primary" @click="openPositionsModal" :disabled="positionsLoading">
          {{ positionsLoading ? 'Loading...' : 'Request Open Positions' }}
        </button>
      </div>


      <!-- Bento-style Stats Cards -->
      <div class="manager-hr-main-wrapper">
        <div class="hr-stats-grid">
        <div class="hr-stat-card hr-stat-card--total">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Total Staff</span>
            <span class="hr-stat-value">{{ dashboardTotals.totalStaff }}</span>
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--active">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Active Staff</span>
            <span class="hr-stat-value">{{ dashboardTotals.activeStaff }}</span>
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--leave">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">On Leave</span>
            <span class="hr-stat-value">{{ dashboardTotals.onLeave }}</span>
          </div>
        </div>
        </div>
      </div>

      <section class="panel-block hr-attendance-panel">
        <div class="panel-header hr-attendance-header">
          <h2>
            Attendance Monitoring
            <span v-if="hrAlertCount > 0" class="panel-badge">{{ hrAlertCount }}</span>
          </h2>
          <div class="hr-attendance-actions">
            <select v-model="attendanceRange" @change="loadHrAttendance(attendanceRange)" class="hr-attendance-select">
              <option value="today">Today</option>
              <option value="thisWeek">This Week</option>
              <option value="thisMonth">This Month</option>
            </select>
            <button class="panel-action" @click="loadHrAttendance(attendanceRange)">Refresh</button>
          </div>
        </div>

        <div class="panel-body panel-body--table">
          <div class="table-header">
            <span>Staff Name</span>
            <span>Branch</span>
            <span>Time In</span>
            <span>Time Out</span>
            <span>Hours</span>
            <span>Status</span>
          </div>

          <div v-if="isLoadingAttendance" class="table-row">
            <span>Loading attendance...</span>
            <span></span><span></span><span></span><span></span><span></span>
          </div>

          <div v-else-if="hrAttendance.length === 0" class="table-row">
            <span>No attendance records for this range.</span>
            <span></span><span></span><span></span><span></span><span></span>
          </div>

          <div v-else v-for="att in hrAttendance" :key="att.id" class="table-row">
            <span>{{ att.user_name }}</span>
            <span>{{ att.branch_name || '-' }}</span>
            <span>{{ att.time_in || '-' }}</span>
            <span>{{ att.time_out || '-' }}</span>
            <span>{{ att.hours_worked || '-' }}</span>
            <span>
              <span class="badge" :class="attendanceStatusClass(att.status)">{{ att.status || '-' }}</span>
            </span>
          </div>
        </div>
      </section>
    </template>

    <template #side>
      <section class="panel-block hr-settings-panel">
        <div class="panel-header"><h2>Attendance Settings</h2></div>
        <div class="attendance-override-toggle" v-if="userProfile.role === 'HR'">
          <div class="toggle-label">
            <span class="toggle-title">Enable Early Clock-Out</span>
            <span class="toggle-desc">Allow staff to clock out before scheduled time</span>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" v-model="earlyClockoutOverride" @change="toggleEarlyClockout" :disabled="isTogglingOverride">
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div class="panel-body panel-body--list">
          <div class="side-item"><span>View and manage staff attendance records</span></div>
        </div>
      </section>
    </template>
  </OwnerPanelLayout>



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
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'

// HR Positions Modal state
const showPositionsModal = ref(false)
const positions = ref([])
const positionsLoading = ref(false)
const submittingPositions = ref(false)
const requestQuantities = ref({})
const requestNotes = ref({})


const router = useRouter()
const errorMessage = ref('')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')

const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && response?.[key]?.data) return response[key].data
  return []
}

const userProfile = ref({})
const dashboardTotals = ref({ totalStaff: 0, activeStaff: 0, onLeave: 0 })
const staffList = ref([])
const hrReports = ref([])

const showStaffManagement = ref(false)
const searchQuery = ref('')
const loading = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const formError = ref('')
const editingStaffId = ref(null)
const hrAttendance = ref([])
const attendanceRange = ref('today')
const isLoadingAttendance = ref(false)
const hasNotified = ref(false)
const hrAlertCount = computed(() => {
  return (hrAttendance.value || []).filter(a => (a.status || '').toLowerCase() !== 'present').length
})

watch(hrAlertCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have attendance alerts to review.', 'info')
    hasNotified.value = true
  }
})

const formData = ref({ username: '', email: '', full_name: '', phone_number: '', department: '', password: '' })

const filteredStaff = computed(() => {
  let filtered = staffList.value.slice()
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

const earlyClockoutOverride = ref(false)
const isTogglingOverride = ref(false)

function toggleStaffManagement() {
  showStaffManagement.value = !showStaffManagement.value
}

function goToStaffManagement() {
  window.location.href = '/manager/hr/staff-management'
}

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      earlyClockoutOverride.value = res.data.data.early_clockout_override || false
    }
  } catch (e) { console.error('Failed to load attendance settings:', e) }
}

async function toggleEarlyClockout() {
  isTogglingOverride.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.patch('/api/attendance/override', { early_clockout_override: earlyClockoutOverride.value }, { withCredentials: true })
    if (res.data && res.data.ok) { alert(res.data.message || 'Settings updated successfully') }
    else { earlyClockoutOverride.value = !earlyClockoutOverride.value; alert(res.data.message || 'Failed to update settings') }
  } catch (e) { earlyClockoutOverride.value = !earlyClockoutOverride.value; alert(e.response?.data?.message || 'Error updating settings') }
  finally { isTogglingOverride.value = false }
}

async function refreshAllData() {
  errorMessage.value = ''
  try { const dash = await axios.get('/api/manager/hr/dashboard', { withCredentials: true }); dashboardTotals.value = dash.data } catch (err) { errorMessage.value = 'Failed to load dashboard data.' }
  try { const staff = await axios.get('/api/manager/hr/staff', { withCredentials: true }); staffList.value = extractArray(staff.data, 'staffList') } catch (err) { staffList.value = []; errorMessage.value = 'Failed to load staff list.' }
  try { const reports = await axios.get('/api/manager/hr/reports', { withCredentials: true }); hrReports.value = extractArray(reports.data, 'hrReports') } catch (err) { hrReports.value = []; errorMessage.value = 'Failed to load HR reports.' }
  loadHrAttendance(attendanceRange.value)
}

async function loadHrAttendance(range = 'today') {
  isLoadingAttendance.value = true
  try {
    const res = await axios.get('/api/manager/hr/attendance', {
      params: { range },
      withCredentials: true
    })
    if (res.data && res.data.ok) {
      hrAttendance.value = res.data.data || []
    } else {
      hrAttendance.value = []
    }
  } catch (e) {
    console.error('Error loading HR attendance:', e)
    hrAttendance.value = []
  } finally {
    isLoadingAttendance.value = false
  }
}

async function loadStaff() {
  loading.value = true; errorMessage.value = ''
  try {
    const res = await axios.get('/api/manager/hr/staff', { withCredentials: true })
    if (res.data.ok) { staffList.value = res.data.staff || [] }
    else { errorMessage.value = res.data.message || 'Failed to load staff' }
  } catch (error) { errorMessage.value = 'Error loading staff. Please try again.' }
  finally { loading.value = false }
}

function refreshStaff() { loadStaff() }

function resetForm() {
  formData.value = { username: '', email: '', full_name: '', phone_number: '', department: '', password: '' }
  isEditing.value = false; editingStaffId.value = null; formError.value = ''
}

function openAddStaffModal() { resetForm(); showModal.value = true }

function editStaff(member) {
  isEditing.value = true; editingStaffId.value = member.id
  formData.value = { username: member.username, email: member.email, full_name: member.full_name, phone_number: member.phone_number || '', department: member.department || '', password: '' }
  showModal.value = true
}

function closeModal() { showModal.value = false; resetForm() }

async function submitStaffForm() {
  formError.value = ''
  if (!formData.value.full_name || formData.value.full_name.trim() === '') { formError.value = 'Full name is required'; return }
  if (!isEditing.value) {
    if (!formData.value.username || formData.value.username.trim() === '') { formError.value = 'Username is required'; return }

  }
  isSubmitting.value = true
  try {
    let res
    if (isEditing.value) {
      const payload = { fullName: formData.value.full_name, email: formData.value.email, phone: formData.value.phone_number, department: formData.value.department }
      if (formData.value.password && formData.value.password.trim() !== '') payload.password = formData.value.password
      res = await axios.put(`/api/manager/hr/staff/${editingStaffId.value}`, payload, { withCredentials: true })
    } else {
      res = await axios.post('/api/manager/hr/staff', { username: formData.value.username, email: formData.value.email, fullName: formData.value.full_name, phone: formData.value.phone_number, department: formData.value.department, password: formData.value.password }, { withCredentials: true })
    }
    if (res.data.ok) { closeModal(); loadStaff(); alert(isEditing.value ? 'Staff updated successfully!' : 'Staff added successfully!') }
    else { formError.value = res.data.message || 'Failed to save staff' }
  } catch (error) { formError.value = error.response?.data?.message || 'Failed to save staff. Please try again.' }
  finally { isSubmitting.value = false }
}

async function toggleStatus(member) {
  try {
    const res = await axios.put(`/api/manager/hr/staff/${member.id}`, { isActive: !member.is_active }, { withCredentials: true })
    if (res.data.ok) { loadStaff(); alert(member.is_active ? 'Staff deactivated' : 'Staff activated') }
  } catch (error) { alert('Failed to update staff status') }
}

async function deleteStaff(member) {
  if (!(await window.swalConfirm(`Are you sure you want to delete ${member.full_name || member.username}?`))) return
  try {
    const res = await axios.delete(`/api/manager/hr/staff/${member.id}`, { withCredentials: true })
    if (res.data.ok) { loadStaff(); alert('Staff deleted successfully') }
  } catch (error) { alert('Failed to delete staff') }
}

function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  return role.replace(/_/g, ' ')
}

function attendanceStatusClass(status) {
  const s = (status || '').toString().toLowerCase()
  if (s === 'present') return 'badge--success'
  if (s === 'late') return 'badge--warning'
  if (s === 'absent') return 'badge--info'
  if (s === 'on_duty') return 'badge--success'
  if (s === 'completed') return 'badge--success'
  return 'badge--info'
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/hr/profile', { withCredentials: true })
    userProfile.value = res.data.user
  } catch (err) { if (err.response && err.response.status === 401) { router.push('/staff-landing'); return } }
  await refreshAllData()
  loadAttendanceSettings()
})

function onProfileUpdated(updatedProfile) { userProfile.value = { ...userProfile.value, ...updatedProfile } }
function cancelLogout() { if (isLoggingOut.value) return; showLogoutConfirm.value = false }

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true; overlayText.value = 'Logging out...'; showOverlay.value = true
  try { await axios.post('/api/logout', {}, { withCredentials: true }) } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => { try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}; try { window.location.replace('/staff-landing') } catch (e) {} }, 600)
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

async function closePositionsModal() {
  showPositionsModal.value = false
}

async function openPositionsModal() {
  showPositionsModal.value = true
  positionsLoading.value = true
  try {
    const res = await axios.get('/api/hr/positions', { withCredentials: true })
    positions.value = res.data?.positions || []

    const quantities = {}
    const notes = {}
    ;(positions.value || []).forEach(p => {
      quantities[p.id] = requestQuantities.value[p.id] || 0
      notes[p.id] = requestNotes.value[p.id] || ''
    })
    requestQuantities.value = quantities
    requestNotes.value = notes
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to load positions')
    positions.value = []
  } finally {
    positionsLoading.value = false
  }
}

async function submitPositionsRequests() {
  if (!Array.isArray(positions.value) || positions.value.length === 0) return

  const payloads = positions.value
    .map(p => {
      const q = Number(requestQuantities.value?.[p.id] || 0)
      const notes = requestNotes.value?.[p.id] || null
      return { position_id: p.id, quantity: q, notes }
    })
    .filter(x => x.quantity && x.quantity >= 1)

  if (payloads.length === 0) {
    alert('Please enter quantity (min 1) for at least one position.')
    return
  }

  submittingPositions.value = true
  let lastResponse = null
  try {
    for (const item of payloads) {
      const res = await axios.post('/api/hr/positions/requests', item, { withCredentials: true })
      lastResponse = res
      if (!res.data?.ok) throw new Error(res.data?.message || 'Request failed')
    }

    alert(lastResponse?.data?.message || 'Position request(s) submitted. Waiting for main HR approval.')
    await closePositionsModal()
  } catch (err) {
    console.error('Position request error:', err)
    alert(err.response?.data?.message || err.message || 'Failed to submit position request(s).')
  } finally {
    submittingPositions.value = false
  }
}

defineExpose({ refreshAllData, onProfileUpdated })
</script>


<style scoped>
.panel-badge {
  position: absolute;
  top: -8px;
  right: -16px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
}

.hr-attendance-header h2 {
  position: relative;
  display: inline-block;
}
.hr-panel-content { padding: 1rem; }
/* HR stats grid: keep cards in a responsive row and avoid overlapping other panels */
.hr-stats-grid {
  display: flex;
  gap: 1rem;
  align-items: stretch;
  flex-wrap: wrap;
  margin: 0 0 1.25rem 0;
}
.hr-stat-card {
  background: white;
  border-radius: 8px;
  padding: 0.85rem 1rem;
  box-shadow: 0 2px 6px rgba(16,24,40,0.04);
  display: flex;
  gap: 0.75rem;
  align-items: center;
  min-width: 180px;
  flex: 0 0 200px;
}
.hr-stat-icon { width:48px; height:48px; display:flex; align-items:center; justify-content:center; border-radius:8px; background: #fff5ee; color: #ff9f43; }
.hr-stat-content { display:flex; flex-direction:column; }
.hr-stat-label { font-size:0.85rem; color:#666; }
.hr-stat-value { font-size:1.35rem; font-weight:700; color:#333; }

/* Ensure main content area in this component sits above any sticky side panels */
.manager-hr-main-wrapper { position: relative; z-index: 1; }
.staff-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.staff-header h2 { margin: 0; color: #333; font-size: 1.5rem; }
.hr-header-actions { display: flex; gap: 0.75rem; align-items: center; }
.hr-search-wrapper { position: relative; display: flex; align-items: center; }
.hr-search-icon { position: absolute; left: 10px; color: #666; }
.hr-search-input { padding: 0.5rem 1rem 0.5rem 2.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; width: 200px; }
.hr-btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; transition: all 0.2s ease; }
.hr-btn--refresh { background: #6c757d; color: #fff; }
.hr-btn--refresh:hover { background: #5a6268; }
.hr-btn--add { background: #ff9f43; color: #fff; }
.hr-btn--add:hover { background: #fabd83; }
.staff-btn { display: inline-block; padding: 0.625rem 1.25rem; background: #ff9f43; color: #fff; border: none; border-radius: 4px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; }
.staff-btn:hover { background: #fabd83; }
.staff-btn--center { display: block; width: 100%; text-align: center; }
.staff-table-wrapper { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.staff-table { width: 100%; border-collapse: collapse; }
.staff-table thead { background: #f8f9fa; border-bottom: 2px solid #dee2e6; }
.staff-table th { padding: 0.75rem; text-align: left; font-weight: 600; color: #333; font-size: 0.85rem; }
.staff-table td { padding: 0.75rem; border-bottom: 1px solid #dee2e6; color: #333; }
.staff-table tbody tr:hover { background: #f8f9fa; }
.staff-table tbody tr.inactive { opacity: 0.7; background: #f8f9fa; }
.badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge-active { background: #d4edda; color: #155724; }
.badge-inactive { background: #f8d7da; color: #721c24; }
.actions { display: flex; gap: 0.5rem; }
.empty-state, .loading-state { text-align: center; padding: 2rem; background: white; border-radius: 8px; color: #666; }
.alert { padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; }
.modal-header h2 { margin: 0; color: #333; font-size: 1.25rem; }
.close-button { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; padding: 0; line-height: 1; }
.close-button:hover { color: #333; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; gap: 0.75rem; justify-content: flex-end; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; font-size: 0.9rem; }
.form-input { width: 100%; padding: 0.625rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #FF9A4A; box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1); }
.form-hint { display: block; margin-top: 0.25rem; color: #666; font-size: 0.8rem; }
.error-message { background: #f8d7da; color: #721c24; padding: 0.75rem; border-radius: 4px; font-size: 0.9rem; margin-top: 1rem; }
.btn { padding: 0.625rem 1.25rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; font-weight: 500; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary { background: #ff9f43; color: #fff; }
.btn-primary:hover { background: #fabd83; }
.btn-secondary { background: #6c757d; color: #fff; }
.btn-secondary:hover { background: #5a6268; }
.btn-sm { padding: 0.35rem 0.7rem; font-size: 0.8rem; }
.btn-info { background: #17a2b8; color: #fff; }
.btn-success { background: #28a745; color: #fff; }
.btn-danger { background: #dc3545; color: #fff; }
.logout-confirm-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.logout-confirm-box { background: white; padding: 2rem; border-radius: 8px; text-align: center; max-width: 400px; }
.logout-confirm-box h3 { margin: 0 0 0.5rem; color: #333; }
.logout-confirm-box p { margin: 0 0 1.5rem; color: #666; }
.logout-actions { display: flex; gap: 1rem; justify-content: center; }
.btn-cancel { padding: 0.625rem 1.25rem; background: #6c757d; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
.btn-confirm { padding: 0.625rem 1.25rem; background: #dc3545; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
.loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.95); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.logo-loading-box { text-align: center; }
.logo-loading-img { width: 120px; height: auto; margin-bottom: 1rem; }
.logo-loading-box p { color: #666; font-size: 1rem; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.panel-block { background: white; border-radius: 8px; padding: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1rem; }
.panel-header h2 { margin: 0; font-size: 1.1rem; color: #333; }
.attendance-override-toggle { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #eee; }
.toggle-label { flex: 1; }
.toggle-title { display: block; font-weight: 500; color: #333; }
.toggle-desc { display: block; font-size: 0.8rem; color: #666; }
.toggle-switch { position: relative; width: 48px; height: 24px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.3s; border-radius: 24px; }
.toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
.toggle-switch input:checked + .toggle-slider { background-color: #ff9f43; }
.toggle-switch input:checked + .toggle-slider:before { transform: translateX(24px); }
.panel-body--list { padding: 0.5rem 0; }
.side-item { padding: 0.5rem 0; color: #666; font-size: 0.9rem; }
.hr-attendance-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.hr-attendance-actions { display: flex; align-items: center; gap: 0.5rem; }
.hr-attendance-select { padding: 6px; border-radius: 6px; border: 1px solid #ddd; background: #fff; }
.panel-action { padding: 0.45rem 0.75rem; border: none; border-radius: 6px; background: #6c757d; color: #fff; cursor: pointer; }
.panel-action:hover { background: #5a6268; }
.panel-body--table { padding-top: 0.75rem; display: flex; flex-direction: column; gap: 0.35rem; }
.table-header, .table-row { display: grid; grid-template-columns: 1.5fr 1fr 0.9fr 0.9fr 0.7fr 0.8fr; gap: 0.75rem; align-items: center; }
.table-header { font-weight: 600; color: #333; font-size: 0.85rem; }
.table-row { background: #fafafa; padding: 0.5rem 0.75rem; border-radius: 6px; color: #333; font-size: 0.85rem; }
.badge--success { background: #d4edda; color: #155724; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge--warning { background: #fff3cd; color: #856404; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge--info { background: #d1ecf1; color: #0c5460; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
@media (max-width: 768px) { .staff-header { flex-direction: column; gap: 1rem; } .hr-header-actions { width: 100%; flex-wrap: wrap; } .hr-search-input { width: 100%; } .staff-table { font-size: 0.8rem; } .staff-table th, .staff-table td { padding: 0.5rem; } }

/* Positions Modal Styles */
.positions-modal-backdrop {
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
  padding: 1rem;
}
.positions-modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}
.positions-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #eee;
}
.positions-modal__header h3 {
  margin: 0 0 0.25rem;
  color: #333;
  font-size: 1.25rem;
  font-weight: 600;
}
.positions-modal__header .muted {
  margin: 0;
  color: #666;
  font-size: 0.85rem;
}
.modal-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #999;
  cursor: pointer;
  padding: 0.25rem;
  line-height: 1;
}
.modal-close:hover {
  color: #333;
}
.positions-modal__body {
  padding: 1rem 1.5rem;
  overflow-y: auto;
  flex: 1;
}
.loading-box, .empty-box {
  text-align: center;
  padding: 2rem;
  color: #666;
}
.positions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.position-row {
  background: #fafafa;
  border-radius: 8px;
  padding: 1rem;
  border: 1px solid #eee;
}
.position-row__meta {
  margin-bottom: 0.75rem;
}
.position-row__name {
  font-weight: 600;
  color: #333;
  font-size: 1rem;
}
.position-row__dept {
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}
.position-row__inputs {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.field-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: #333;
}
.field-input,
.field-textarea {
  padding: 0.625rem 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.9rem;
  width: 100%;
  box-sizing: border-box;
}
.field-input:focus,
.field-textarea:focus {
  outline: none;
  border-color: #ff9f43;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.15);
}
.field-textarea {
  resize: vertical;
  min-height: 60px;
}
.positions-modal__footer {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  padding: 1rem 1.5rem;
  border-top: 1px solid #eee;
  background: #fafafa;
  border-radius: 0 0 12px 12px;
}
.positions-top-actions {
  margin-bottom: 1.25rem;
}
.panel-action--primary {
  background: #ff9f43;
  color: #fff;
}
.panel-action--primary:hover {
  background: #fabd83;
}
.panel-action--primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
