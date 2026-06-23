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
                  <div class="header-content">
                    <div class="header-top">
                      <h4 class="branch-name">{{ branch.branch_name || 'Unassigned Branch' }}</h4>
                      <span class="badge-branch">Branch #{{ branch.branch_id || 'N/A' }}</span>
                    </div>
                    <p class="branch-subtitle">
                      <span class="stat-total">{{ branch.total_staff }} total</span>
                      <span class="stat-active">{{ branch.active_staff }} active</span>
                    </p>
                  </div>
                </div>

                <div class="table-container" v-if="branch.staff && branch.staff.length">
                  <table class="staff-table data-table">
                    <thead>
                      <tr>
                        <th class="col-name">Name</th>
                        <th class="col-username">Username</th>
                        <th class="col-role">Role</th>
                        <th class="col-dept">Department</th>
                        <th class="col-status">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="member in branch.staff" :key="member.id" class="staff-row">
                        <td class="col-name">
                          <span class="name-badge">{{ (member.full_name || member.username).charAt(0) }}</span>
                          {{ member.full_name || member.username }}
                        </td>
                        <td class="col-username"><code class="code-text">{{ member.username }}</code></td>
                        <td class="col-role">
                          <span class="role-badge" :class="'role-' + (member.role || 'unknown').toLowerCase()">
                            {{ displayRole(member.role) }}
                          </span>
                        </td>
                        <td class="col-dept">{{ member.department || '—' }}</td>
                        <td class="col-status">
                          <span :class="['status-badge', member.is_active ? 'status-active' : 'status-inactive']">
                            <span class="status-dot" :class="member.is_active ? 'dot-active' : 'dot-inactive'"></span>
                            {{ member.is_active ? 'Active' : 'Inactive' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
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
          <button class="btn-secondary" @click="closePositionsModal" :disabled="submittingPositions">
            Cancel
          </button>
          <button class="btn-primary" @click="submitPositionsRequests" :disabled="submittingPositions || positionsLoading">
            {{ submittingPositions ? 'Submitting...' : 'Submit Request(s)' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
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

const showPositionsModal = ref(false)
const positions = ref([])
const positionsLoading = ref(false)
const submittingPositions = ref(false)

// Per-position form state: quantity + notes
const requestQuantities = ref({})
const requestNotes = ref({})


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

function closePositionsModal() {
  showPositionsModal.value = false
}

async function openPositionsModal() {
  showPositionsModal.value = true
  positionsLoading.value = true
  try {
    const res = await axios.get('/api/hr/positions', { withCredentials: true })
    positions.value = res.data?.positions || []

    // Initialize form state for each position
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

  // Collect only positions with valid quantity
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
  try {
    // Submit sequentially to keep backend simple
    for (const item of payloads) {
      const res = await axios.post('/api/hr/positions/requests', item, { withCredentials: true })
      if (!res.data?.ok) throw new Error(res.data?.message || 'Request failed')
    }

    alert('Open position request(s) submitted successfully.')
    closePositionsModal()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit position request(s).')
  } finally {
    submittingPositions.value = false
  }
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
.positions-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
}

.positions-modal {
  width: 92%;
  max-width: 860px;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 12px 50px rgba(0,0,0,0.25);
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.positions-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  padding: 16px 18px;
  background: linear-gradient(90deg, rgba(255,154,74,0.12), rgba(255,106,61,0.10));
  border-bottom: 1px solid #f3f4f6;
}

.positions-modal__header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #111827;
}

.positions-modal__body {
  padding: 14px 18px;
  max-height: 60vh;
  overflow: auto;
}

.positions-modal__footer {
  padding: 14px 18px;
  border-top: 1px solid #f3f4f6;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.modal-close {
  border: none;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
  color: #6b7280;
}
.modal-close:hover { color: #111827; }

.positions-list { display: flex; flex-direction: column; gap: 10px; }
.position-row {
  border: 1px solid #e5e7eb;
  background: #fafafa;
  border-radius: 12px;
  padding: 12px;
}

.position-row__meta {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 10px;
}

.position-row__name { font-weight: 800; color: #111827; }
.position-row__dept { color: #6b7280; font-size: 13px; }

.position-row__inputs {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 12px;
}

.field { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-weight: 700; font-size: 12px; color: #374151; }
.field-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  outline: none;
}
.field-input:focus {
  border-color: #ff9f43;
  box-shadow: 0 0 0 4px rgba(255,154,74,0.12);
}
.field-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  outline: none;
  resize: vertical;
}

@media (max-width: 768px) {
  .position-row__inputs { grid-template-columns: 1fr; }
  .positions-modal__footer { flex-direction: column; align-items: stretch; }
}

.main-branch-hr-panel { width: 100%; padding: 20px; background: #fafbfc; }
.hero { margin-bottom: 16px; display: flex; flex-direction: column; gap: 12px; }
.panel-section { display: flex; flex-direction: column; gap: 12px; }
.section-title { font-size: 22px; font-weight: 800; color: #1f2937; margin: 0 0 6px 0; letter-spacing: -0.3px; }
.section-description { margin: 0 0 8px 0; color: #6b7280; font-size: 15px; }
.info-box { background: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%); border: 1px solid #dbeafe; border-radius: 14px; padding: 20px 24px; width: 100%; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08); }
.info-box p { margin: 6px 0; color: #1e40af; font-size: 14px; line-height: 1.5; }

.branch-stats { background: #ffffff; padding: 20px; border-radius: 14px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.section-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
.muted { color: #6b7280; margin: 2px 0 0 0; font-size: 14px; }
.pill-btn { background: linear-gradient(90deg, #ff9f43, #ff7a18); color: white; border: none; padding: 11px 18px; border-radius: 999px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(255, 122, 24, 0.3); transition: all 0.3s ease; font-size: 14px; }
.pill-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(255, 122, 24, 0.4); }
.pill-btn:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

.branch-grid { display: grid; grid-template-columns: 1fr; gap: 12px; width: 100%; }
.branch-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); padding: 16px; display: flex; flex-direction: column; gap: 12px; transition: all 0.3s ease; width: 100%; }
.branch-card:hover { border-color: #d1d5db; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

.branch-card__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 2px solid #f3f4f6; }
.header-content { flex: 1; }
.header-top { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
.branch-name { margin: 0; font-size: 17px; font-weight: 700; color: #111827; }
.badge-branch { background: #eff6ff; color: #0c4a6e; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
.branch-subtitle { margin: 0; color: #6b7280; font-size: 13px; display: flex; gap: 12px; }
.stat-total { font-weight: 600; color: #374151; }
.stat-active { color: #22c55e; font-weight: 600; }

.table-container { width: 100%; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb; }
.staff-table { width: 100%; border-collapse: collapse; background: #ffffff; }
.staff-table thead { position: sticky; top: 0; z-index: 10; }
.staff-table th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #374151; background: linear-gradient(180deg, #f9fafb 0%, #f3f4f6 100%); border-bottom: 2px solid #e5e7eb; text-transform: uppercase; letter-spacing: 0.4px; }
.staff-table td { padding: 10px 14px; text-align: left; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; height: auto; }
.staff-row { transition: background-color 0.2s ease; }
.staff-row:hover { background-color: #f9fafb; }

.col-name { width: 35%; }
.col-username { width: 25%; }
.col-role { width: 18%; }
.col-dept { width: 15%; }
.col-status { width: 15%; }

.name-badge { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; font-weight: 700; font-size: 13px; margin-right: 8px; vertical-align: middle; }
.code-text { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px; color: #6b7280; }

.role-badge { display: inline-block; padding: 4px 9px; border-radius: 6px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px; }
.role-admin { background: #fee2e2; color: #991b1b; }
.role-branch_manager { background: #fef3c7; color: #92400e; }
.role-hr { background: #e0e7ff; color: #3730a3; }
.role-manager { background: #dbeafe; color: #0c4a6e; }
.role-staff { background: #dcfce7; color: #166534; }
.role-finance { background: #f3e8ff; color: #6b21a8; }
.role-logistics { background: #fce7f3; color: #831843; }
.role-procurement { background: #fef8e0; color: #78350f; }
.role-custom { background: #e0f2fe; color: #0369a1; }
.role-unknown { background: #f3f4f6; color: #6b7280; }

.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 9px; border-radius: 6px; font-weight: 600; font-size: 12px; }
.status-active { background: #dcfce7; color: #166534; }
.status-inactive { background: #fee2e2; color: #991b1b; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.dot-active { background: #22c55e; box-shadow: 0 0 8px rgba(34, 197, 94, 0.3); }
.dot-inactive { background: #ef4444; }

.empty-branch { color: #9ca3af; font-style: italic; padding: 12px; text-align: center; font-size: 14px; }
.loading-box, .empty-box { padding: 18px; color: #6b7280; background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 10px; text-align: center; font-size: 14px; }
.alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 14px 16px; border-radius: 10px; font-size: 14px; }

@media (max-width: 1024px) {
  .branch-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .main-branch-hr-panel { padding: 16px; }
  .branch-stats { padding: 16px; }
  .section-header { flex-direction: column; align-items: stretch; }
  .pill-btn { width: 100%; }
  .col-dept { display: none; }
  .branch-card { padding: 14px; }
  .staff-table th, .staff-table td { padding: 10px 12px; font-size: 12px; }
  .name-badge { width: 28px; height: 28px; font-size: 11px; margin-right: 6px; }
  .header-top { flex-direction: column; align-items: flex-start; gap: 6px; }
}
</style>
