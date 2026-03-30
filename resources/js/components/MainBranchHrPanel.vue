<template>
  <div class="main-branch-hr-panel">
    <OwnerPanelLayout
      :userProfile="userProfile"
      :panelTitle="'Main Branch HR Management'"
      :panelDescription="'Human Resources management for Main Branch'"
      :enableProfileUpdate="true"
      :canEditProfile="false"
      :canChangePassword="true"
      :showProfileColumn="false"
      @logout="askLogout"
      @profile-updated="onProfileUpdated"
    >
      <template #main>
        <div class="panel-section hero">
          <h2 class="section-title">Main Branch HR Panel</h2>
          <p class="section-description">Human Resources and staff management for Main Branch</p>
          
          <div class="info-box">
            <p>This panel provides HR functions for Main Branch headquarters personnel.</p>
            <p>Manage staff schedules, attendance, benefits, and performance from this location.</p>
          </div>

          <div class="branch-stats">
            <div class="section-header">
              <div>
                <h3>Accounts by Branch</h3>
                <p class="muted">Main Branch HR can review every account, grouped per branch.</p>
              </div>
              <button class="pill-btn" @click="loadBranchStaff" :disabled="loading">
                {{ loading ? 'Loading...' : 'Refresh' }}
              </button>
            </div>

            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <div v-else-if="loading" class="loading-box">Loading accounts...</div>
            <div v-else-if="branchSections.length === 0" class="empty-box">No accounts found.</div>

            <div v-else class="branch-grid">
              <div v-for="branch in branchSections" :key="branch.branch_id" class="branch-card">
                <div class="branch-card__header">
                  <div>
                    <h4 class="branch-name">{{ branch.branch_name || 'Unassigned Branch' }}</h4>
                    <p class="branch-subtitle">{{ branch.total_staff }} accounts · {{ branch.active_staff }} active</p>
                  </div>
                  <span class="badge-pill">{{ branch.branch_id || 'N/A' }}</span>
                </div>

                <div class="table-container" v-if="branch.staff && branch.staff.length">
                  <div class="table-scroll">
                    <table class="staff-table data-table">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="member in branch.staff" :key="member.id">
                        <td>{{ member.full_name || member.username }}</td>
                        <td>{{ member.username }}</td>
                        <td>{{ displayRole(member.role) }}</td>
                        <td>{{ member.department || '—' }}</td>
                        <td>
                          <span :class="['status-dot', member.is_active ? 'status-dot--active' : 'status-dot--inactive']"></span>
                          {{ member.is_active ? 'Active' : 'Inactive' }}
                        </td>
                      </tr>
                    </tbody>
                    </table>
                  </div>
                </div>
                <div v-else class="empty-branch">No accounts for this branch.</div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #headerActions>
        <div class="header-profile-wrapper" @click.stop>
          <button class="header-profile-btn" @click="toggleProfileDropdown">
            <div class="header-avatar">
              <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
              <div v-else class="header-avatar-initials">{{ (userProfile.fullName || 'H').charAt(0) }}</div>
            </div>
            <div class="header-name">{{ ((userProfile.fullName || userProfile.full_name) || 'HR MANAGER').toUpperCase() }}</div>
          </button>
          <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
            <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
            <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
          </div>
        </div>
      </template>
    </OwnerPanelLayout>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'

const userProfile = ref({})
const profileDropdownVisible = ref(false)
const branchSections = ref([])
const loading = ref(false)
const errorMessage = ref('')

function toggleProfileDropdown() {
  profileDropdownVisible.value = !profileDropdownVisible.value
}

function closeProfileDropdown() {
  profileDropdownVisible.value = false
}

function openInfoFromHeader() {
  closeProfileDropdown()
  try {
    window.dispatchEvent(new Event('open-owner-info'))
  } catch (e) {}
}

async function triggerLogoutFromHeader() {
  closeProfileDropdown()
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('Logout from Main Branch HR Panel?', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) {}
}

async function confirmLogout() {
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    try { window.location.replace('/') } catch (e) {}
  }, 600)
}

function askLogout() {
  try {
    window.swalConfirm('Logout from Main Branch HR Panel?', 'Confirm logout').then(ok => {
      if (ok) confirmLogout()
    })
  } catch (e) {}
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  return role.replace(/_/g, ' ')
}

async function loadBranchStaff() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await axios.get('/api/manager/hr/staff', { withCredentials: true })
    if (res.data && res.data.ok) {
      if (Array.isArray(res.data.branches)) {
        branchSections.value = res.data.branches
      } else if (Array.isArray(res.data.staff)) {
        const grouped = res.data.staff.reduce((acc, member) => {
          const key = member.branch_id || 'unassigned'
          if (!acc[key]) {
            acc[key] = {
              branch_id: member.branch_id || null,
              branch_name: member.branch_name || 'Unassigned Branch',
              total_staff: 0,
              active_staff: 0,
              staff: []
            }
          }
          acc[key].staff.push(member)
          acc[key].total_staff += 1
          acc[key].active_staff += member.is_active ? 1 : 0
          return acc
        }, {})
        branchSections.value = Object.values(grouped)
      } else {
        branchSections.value = []
      }
    } else {
      errorMessage.value = res.data?.message || 'Failed to load accounts'
      branchSections.value = []
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Error loading accounts'
    branchSections.value = []
  } finally {
    loading.value = false
  }
}

// Initial load
loadBranchStaff()

// Close dropdown when clicking outside
window.addEventListener('click', () => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})
</script>

<style scoped>
.main-branch-hr-panel { width: 100%; padding: 16px; }
.hero { margin-bottom: 20px; display: flex; flex-direction: column; gap: 14px; }
.panel-section { display: flex; flex-direction: column; gap: 12px; }
.section-title { font-size: 18px; font-weight: 700; color: #4b2a06; margin: 0 0 8px 0; }
.section-description { margin: 0 0 12px 0; color: #6b7280; }
.info-box { background: #f0f4f8; border: 1px solid #d0dce6; border-radius: 12px; padding: 20px; width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
.info-box p { margin: 8px 0; color: #4b506d; }

.branch-stats { background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-top: 4px; }
.section-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.muted { color: #6b7280; margin: 2px 0 0 0; font-size: 14px; }
.pill-btn { background: linear-gradient(90deg, #ff9f43, #ff7a18); color: white; border: none; padding: 10px 16px; border-radius: 999px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
.pill-btn:disabled { opacity: 0.7; cursor: not-allowed; }

.branch-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
.branch-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.06); padding: 14px; display: flex; flex-direction: column; gap: 12px; }
.branch-card__header { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.branch-name { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
.branch-subtitle { margin: 2px 0 0 0; color: #6b7280; font-size: 13px; }
.badge-pill { background: #eef2ff; color: #4338ca; padding: 6px 10px; border-radius: 999px; font-weight: 600; font-size: 12px; }

.table-container { width: 100%; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 10px; }
.table-scroll { max-height: 360px; overflow: auto; }
.staff-table { width: 100%; border-collapse: collapse; }
.staff-table th, .staff-table td { padding: 12px 16px; text-align: left; font-size: 14px; }
.staff-table th { background: #eff6ff; color: #1e3a8a; font-weight: 600; }
.staff-table td { color: #374151; border-bottom: 1px solid #e5e7eb; }
.status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; vertical-align: middle; }
.status-dot--active { background: #22c55e; }
.status-dot--inactive { background: #f87171; }
.empty-branch { color: #9ca3af; font-style: italic; padding: 8px 0; }

.loading-box, .empty-box { padding: 16px; color: #6b7280; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; }
.alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px 14px; border-radius: 8px; }
@media (max-width: 768px) {
  .panel-section { padding: 12px; }
  .branch-grid { grid-template-columns: 1fr; }
}
</style>
