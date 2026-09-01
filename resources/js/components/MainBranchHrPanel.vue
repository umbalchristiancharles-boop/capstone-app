<template>
  <div class="main-branch-hr-panel">
    <OwnerPanelLayout
      :userProfile="userProfile"
      :panelTitle="'Main Branch HR Management'"
      :panelDescription="'Human Resources management for Main Branch'"
      :enableProfileUpdate="true"
      :canEditProfile="false"
      :canChangePassword="true"
      :showHeader="false"
      :showProfileColumn="false"
      :ownerTwoColumnLayout="true"
      @logout="askLogout"
      @profile-updated="onProfileUpdated"
    >
      <template #main>
        <div class="main-branch-hr-page">
          <header class="main-branch-hr-hero">
            <div>
              <span class="main-branch-hr-eyebrow">HR dashboard</span>
              <h2 class="main-branch-hr-title">HR overview</h2>
              <p class="main-branch-hr-subtitle">Review position requests and manage workforce accounts across all branches.</p>
            </div>
            <div class="main-branch-hr-hero__actions">
              <button class="pill-btn main-branch-hr-hero__action" @click="openApplicationsModal" :disabled="loadingApplications">
                {{ loadingApplications ? 'Loading...' : 'View Applications' }}
              </button>
              <span v-if="applicationsCount > 0" class="pending-badge">{{ applicationsCount }} total</span>
              <button class="pill-btn main-branch-hr-hero__action" @click="loadPositionRequests" :disabled="loadingPositionRequests">
                {{ loadingPositionRequests ? 'Loading...' : 'Refresh Requests' }}
              </button>
            </div>
          </header>

          <div class="info-box">
            <p>This panel provides HR functions for Main Branch headquarters personnel.</p>
            <p>Manage staff schedules, attendance, benefits, and performance from this location.</p>
          </div>

          <div class="hr-summary-grid" aria-label="HR summary">
            <div class="hr-summary-card hr-summary-card--pending">
              <span class="hr-summary-label">Pending Requests</span>
              <strong>{{ positionRequestsPendingCount }}</strong>
            </div>
            <div class="hr-summary-card">
              <span class="hr-summary-label">Branches</span>
              <strong>{{ branchSections.length }}</strong>
            </div>
            <div class="hr-summary-card">
              <span class="hr-summary-label">Total Accounts</span>
              <strong>{{ totalStaffCount }}</strong>
            </div>
            <div class="hr-summary-card hr-summary-card--active">
              <span class="hr-summary-label">Active Accounts</span>
              <strong>{{ activeStaffCount }}</strong>
            </div>
          </div>

          <div class="position-requests-section">
            <div class="section-header">
              <div>
                <h3>Position Requests</h3>
                <p class="muted">Approve or reject open position requests from branches.</p>
                <span v-if="positionRequestsPendingCount > 0" class="pending-badge">{{ positionRequestsPendingCount }} pending</span>
              </div>
              <button class="pill-btn" @click="loadPositionRequests" :disabled="loadingPositionRequests">
                {{ loadingPositionRequests ? 'Loading...' : 'Refresh' }}
              </button>
            </div>



            <div v-if="loadingPositionRequests" class="loading-box">Loading position requests...</div>
            <div v-else-if="positionRequests.length === 0" class="empty-box">No position requests found.</div>

            <div v-else class="requests-list">
              <div v-for="req in positionRequests" :key="req.id" class="request-card" :class="'request-card--' + req.status.toLowerCase()">

                <div class="request-card__header">
                  <span class="request-card__position">{{ req.position?.name || 'Unknown Position' }}</span>
                  <span class="request-card__status" :class="'status-' + req.status.toLowerCase()">{{ req.status }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Branch:</span>
                  <span class="value">{{ req.branch?.name || 'Main Branch' }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Quantity:</span>
                  <span class="value">{{ req.quantity }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Requested by:</span>
                  <span class="value">{{ req.requested_by?.full_name || req.requested_by?.username || 'Unknown' }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Date:</span>
                  <span class="value">{{ formatDate(req.created_at) }}</span>
                </div>
                <div v-if="req.notes" class="request-card__notes">
                  <span class="label">Notes:</span>
                  <p>{{ req.notes }}</p>
                </div>
                <div v-if="req.rejection_reason" class="request-card__notes request-card__notes--rejection">
                  <span class="label">Rejection reason:</span>
                  <p>{{ req.rejection_reason }}</p>
                </div>

                <div v-if="req.status === 'Pending'" class="request-card__actions">
                  <button @click="approveRequest(req)" class="btn-success btn-sm" :disabled="processingRequestId === req.id">
                    {{ processingRequestId === req.id ? 'Processing...' : 'Approve' }}
                  </button>
                  <button @click="openRejectModal(req)" class="btn-danger btn-sm" :disabled="processingRequestId === req.id">
                    Reject
                  </button>
                </div>
              </div>
            </div>
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

            <div class="account-filters">
              <label class="sr-only" for="account-search">Search accounts</label>
              <input
                id="account-search"
                v-model="accountSearch"
                type="search"
                placeholder="Search name, username, role, or department"
                class="account-search"
              />
              <label class="sr-only" for="account-status">Filter account status</label>
              <select id="account-status" v-model="accountStatusFilter" class="account-status-filter">
                <option value="all">All statuses</option>
                <option value="active">Active only</option>
                <option value="inactive">Inactive only</option>
              </select>
            </div>

            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <div v-else-if="loading" class="loading-box">Loading accounts...</div>
            <div v-else-if="filteredBranchSections.length === 0" class="empty-box">No accounts match the current filters.</div>

            <div v-else class="branch-grid">
              <div v-for="branch in filteredBranchSections" :key="branch.branch_id" class="branch-card">
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

      <template #sideTop>
        <div class="header-profile-wrapper main-branch-hr-profile" @click.stop>
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

  <!-- Applications Modal -->
  <transition name="fade">
    <div v-if="showApplicationsModal" class="positions-modal-backdrop" @click.self="closeApplicationsModal">
      <div class="positions-modal">
        <div class="positions-modal__header">
          <div>
            <h3>Job Applications (HR View)</h3>
            <p class="muted">Applications submitted for positions on your branch.</p>
          </div>
          <button class="modal-close" @click="closeApplicationsModal" aria-label="Close">✕</button>
        </div>

        <div class="positions-modal__body">
          <div v-if="loadingApplications" class="loading-box">Loading applications...</div>
          <div v-else-if="applications.length === 0" class="empty-box">No applications found.</div>

          <div v-else class="positions-list">
            <div v-for="a in applications" :key="a.id" class="position-row">
              <div class="position-row__meta">
                <div class="position-row__name">{{ a.applicant_full_name }}</div>
                <div class="position-row__dept">{{ a.job_title }}</div>
              </div>

              <div class="request-card__info" style="margin-bottom: 0;">
                <span class="label">Department:</span>
                <span class="value">{{ a.department || '—' }}</span>
              </div>
              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Status:</span>
                <span class="value">{{ a.status || 'Submitted' }}</span>
              </div>

              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Applied On:</span>
                <span class="value">{{ formatDate(a.created_at) }}</span>
              </div>

              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Contact:</span>
                <span class="value">{{ a.applicant_email }} • {{ a.applicant_phone }}</span>
              </div>

              <div class="request-card__actions" style="margin-top: 12px; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button class="btn-success btn-sm" @click="openApplicationDetails(a)">
                  View Application Details
                </button>
                <button
                  v-if="!isReadyForInterview(a.status)"
                  class="btn-primary btn-sm"
                  @click="openInterviewScheduleModal(a)"
                  :disabled="sendingInterviewEmail[a.id]"
                >
                  {{ sendingInterviewEmail[a.id] ? 'Sending...' : 'Ready for Interview' }}
                </button>
                <button
                  v-if="isReadyForInterview(a.status) && !isPassedForHiring(a.status)"
                  class="btn-success btn-sm"
                  @click="markAsPassed(a)"
                  :disabled="markingAsPassed[a.id]"
                >
                  {{ markingAsPassed[a.id] ? 'Processing...' : '✓ Mark as Passed' }}
                </button>
                <button
                  v-if="isReadyForInterview(a.status) && !isPassedForHiring(a.status)"
                  class="btn-danger btn-sm"
                  @click="markAsNotPassed(a)"
                  :disabled="markingAsNotPassed[a.id]"
                >
                  {{ markingAsNotPassed[a.id] ? 'Processing...' : '✗ Mark as Not Passed' }}
                </button>
                <span v-else-if="isPassedForHiring(a.status)" class="status-approved">✓ Passed - Ready for Hiring</span>
              </div>
            </div>
          </div>
        </div>

        <div class="positions-modal__footer">
          <button class="btn-secondary" @click="closeApplicationsModal">Close</button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Interview Schedule Modal -->
  <div v-if="showInterviewScheduleModal" class="positions-modal-backdrop" @click.self="closeInterviewScheduleModal">
    <div class="positions-modal">
      <div class="positions-modal__header">
        <div>
          <h3>Schedule Interview</h3>
          <p class="muted">Select date and time for {{ selectedApplication?.applicant_full_name }}</p>
        </div>
        <button class="modal-close" @click="closeInterviewScheduleModal" aria-label="Close">✕</button>
      </div>
      <div class="positions-modal__body">
        <label class="field"><span class="field-label">Interview Date *</span><input v-model="interviewSchedule.date" class="field-input" type="date" :min="getMinDate()"></label>
        <label class="field"><span class="field-label">Interview Time *</span><input v-model="interviewSchedule.time" class="field-input" type="time"></label>
        <label class="field"><span class="field-label">Additional Notes</span><textarea v-model="interviewSchedule.notes" class="field-textarea" rows="3"></textarea></label>
      </div>
      <div class="positions-modal__footer">
        <button class="btn-secondary" @click="closeInterviewScheduleModal">Cancel</button>
        <button class="btn-primary" @click="confirmInterviewSchedule" :disabled="!isInterviewScheduleValid() || sendingInterviewEmail[selectedApplication?.id]">
          {{ sendingInterviewEmail[selectedApplication?.id] ? 'Sending...' : 'Send Interview Email' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Application Details Modal -->
  <div v-if="showApplicationDetailsModal && selectedApplicationDetails" class="positions-modal-backdrop" @click.self="closeApplicationDetailsModal">
    <div class="positions-modal application-details-modal">
      <div class="positions-modal__header">
        <div><h3>Application Details</h3><p class="muted">Complete application information</p></div>
        <button class="modal-close" @click="closeApplicationDetailsModal" aria-label="Close">✕</button>
      </div>
      <div class="positions-modal__body">
        <div class="request-card__info"><span class="label">Full Name:</span><span class="value">{{ selectedApplicationDetails.applicant_full_name || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Email:</span><span class="value">{{ selectedApplicationDetails.applicant_email || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Phone:</span><span class="value">{{ selectedApplicationDetails.applicant_phone || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Address:</span><span class="value">{{ selectedApplicationDetails.applicant_address || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Position:</span><span class="value">{{ selectedApplicationDetails.job_title || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Department:</span><span class="value">{{ selectedApplicationDetails.department || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Experience:</span><span class="value">{{ selectedApplicationDetails.years_of_experience || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Education:</span><span class="value">{{ selectedApplicationDetails.education || '-' }}</span></div>
        <div v-if="selectedApplicationDetails.cover_letter" class="request-card__notes"><span class="label">Cover Letter</span><p>{{ selectedApplicationDetails.cover_letter }}</p></div>
        <div class="request-card__actions">
          <a v-if="selectedApplicationDetails.resume_path" :href="getStorageUrl(selectedApplicationDetails.resume_path)" target="_blank" rel="noopener noreferrer" class="btn-primary">Open Resume</a>
        </div>
      </div>
      <div class="positions-modal__footer"><button class="btn-secondary" @click="closeApplicationDetailsModal">Close</button></div>
    </div>
  </div>

  <!-- Reject Reason Modal -->
  <div v-if="showRejectModal" class="reject-modal-backdrop" @click.self="closeRejectModal">

    <div class="reject-modal">
      <div class="reject-modal__header">
        <h3>Reject Position Request</h3>
        <button class="modal-close" @click="closeRejectModal" aria-label="Close">✕</button>
      </div>
      <div class="reject-modal__body">
        <p>You are about to reject the position request for <strong>{{ rejectingRequest?.position?.name }}</strong> ({{ rejectingRequest?.quantity }} position(s)).</p>
        <label class="field">
          <span class="field-label">Rejection Reason (optional)</span>
          <textarea
            class="field-textarea"
            rows="3"
            v-model.trim="rejectReason"
            placeholder="Provide a reason for rejection..."
          ></textarea>
        </label>
      </div>
      <div class="reject-modal__footer">
        <button class="btn-secondary" @click="closeRejectModal">Cancel</button>
        <button class="btn-danger" @click="confirmReject" :disabled="processingRequestId">
          {{ processingRequestId ? 'Processing...' : 'Confirm Rejection' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { swalAlert, swalConfirm } from '../sweet-alerts'

const userProfile = ref({})
const profileDropdownVisible = ref(false)
const branchSections = ref([])
const loading = ref(false)
const errorMessage = ref('')

// Position Requests for approval
const positionRequests = ref([])
const loadingPositionRequests = ref(false)
const processingRequestId = ref(null)
const showRejectModal = ref(false)
const rejectingRequest = ref(null)
const rejectReason = ref('')
const accountSearch = ref('')
const accountStatusFilter = ref('all')

// Job Applications modal state
const showApplicationsModal = ref(false)
const loadingApplications = ref(false)
const applications = ref([])
const applicationsCount = computed(() => {
  return Array.isArray(applications.value) ? applications.value.length : 0
})
const sendingInterviewEmail = ref({})
const markingAsPassed = ref({})
const markingAsNotPassed = ref({})
const showInterviewScheduleModal = ref(false)
const selectedApplication = ref(null)
const interviewSchedule = ref({ date: '', time: '', notes: '' })
const showApplicationDetailsModal = ref(false)
const selectedApplicationDetails = ref(null)

const positionRequestsPendingCount = computed(() => {
  return positionRequests.value.filter(r => r.status === 'Pending').length
})

const totalStaffCount = computed(() => branchSections.value.reduce((total, branch) => total + Number(branch.total_staff || 0), 0))
const activeStaffCount = computed(() => branchSections.value.reduce((total, branch) => total + Number(branch.active_staff || 0), 0))
const filteredBranchSections = computed(() => {
  const query = accountSearch.value.trim().toLowerCase()
  const status = accountStatusFilter.value

  return branchSections.value
    .map(branch => ({
      ...branch,
      staff: (branch.staff || []).filter(member => {
        const matchesQuery = !query || [
          member.full_name,
          member.username,
          member.role,
          member.department,
          branch.branch_name
        ].some(value => String(value || '').toLowerCase().includes(query))
        const matchesStatus = status === 'all' || (status === 'active' ? member.is_active : !member.is_active)
        return matchesQuery && matchesStatus
      })
    }))
    .filter(branch => branch.staff.length > 0)
})



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

function closeApplicationsModal() {
  showApplicationsModal.value = false
}

function openApplicationDetails(application) {
  selectedApplicationDetails.value = application
  showApplicationDetailsModal.value = true
}

function closeApplicationDetailsModal() {
  showApplicationDetailsModal.value = false
  selectedApplicationDetails.value = null
}

function isReadyForInterview(status) {
  const value = String(status || '').toLowerCase().trim()
  return ['ready for interview', 'ready_for_interview', 'interview scheduled'].includes(value)
}

function isPassedForHiring(status) {
  const value = String(status || '').toLowerCase().trim()
  return ['passed - ready for hiring', 'passed_ready_for_hiring', 'passed'].includes(value)
}

function openInterviewScheduleModal(application) {
  if (!application?.id || !application?.applicant_email) {
    alert('Invalid application data')
    return
  }
  selectedApplication.value = application
  interviewSchedule.value = { date: '', time: '', notes: '' }
  showInterviewScheduleModal.value = true
}

function closeInterviewScheduleModal() {
  showInterviewScheduleModal.value = false
  selectedApplication.value = null
  interviewSchedule.value = { date: '', time: '', notes: '' }
}

function getMinDate() {
  const date = new Date()
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function isInterviewScheduleValid() {
  return Boolean(interviewSchedule.value.date && interviewSchedule.value.time)
}

async function confirmInterviewSchedule() {
  const applicationId = selectedApplication.value?.id
  if (!applicationId || !isInterviewScheduleValid()) return
  sendingInterviewEmail.value[applicationId] = true
  try {
    const res = await axios.post(`/api/hr/positions/applications/${applicationId}/send-interview-email`, {
      interview_date: interviewSchedule.value.date,
      interview_time: interviewSchedule.value.time,
      notes: interviewSchedule.value.notes
    }, { withCredentials: true })
    if (res.data?.ok) {
      const application = applications.value.find(item => item.id === applicationId)
      if (application) application.status = 'Ready for Interview'
      alert(res.data.message || 'Interview email sent successfully!')
      closeInterviewScheduleModal()
    } else {
      alert(res.data?.message || 'Failed to send interview email')
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to send interview email. Please try again.')
  } finally {
    sendingInterviewEmail.value[applicationId] = false
  }
}

async function markAsPassed(application) {
  if (!application?.id) return
  const confirmed = await swalConfirm(`Are you sure you want to mark ${application.applicant_full_name} as passed?`, 'Mark as Passed')
  if (!confirmed) return
  markingAsPassed.value[application.id] = true
  try {
    const res = await axios.post(`/api/hr/positions/applications/${application.id}/mark-as-passed`, {}, { withCredentials: true })
    if (res.data?.ok) application.status = 'Passed - Ready for Hiring'
    await swalAlert(res.data?.message || 'Applicant status updated.', 'success')
  } catch (err) {
    await swalAlert(err.response?.data?.message || 'Failed to mark applicant as passed.', 'error')
  } finally {
    markingAsPassed.value[application.id] = false
  }
}

async function markAsNotPassed(application) {
  if (!application?.id) return
  const confirmed = await swalConfirm(`Are you sure you want to mark ${application.applicant_full_name} as not passed?`, 'Mark as Not Passed')
  if (!confirmed) return
  markingAsNotPassed.value[application.id] = true
  try {
    const res = await axios.post(`/api/hr/positions/applications/${application.id}/mark-as-not-passed`, {}, { withCredentials: true })
    if (res.data?.ok) application.status = 'Not Passed'
    await swalAlert(res.data?.message || 'Applicant status updated.', 'success')
  } catch (err) {
    await swalAlert(err.response?.data?.message || 'Failed to mark applicant as not passed.', 'error')
  } finally {
    markingAsNotPassed.value[application.id] = false
  }
}

function getStorageUrl(path) {
  if (!path) return ''
  // resume_path is stored using Laravel's public disk under /storage
  // The stored string is usually like: position-applications/.../resume_cv.pdf
  return path.startsWith('/') ? path : `/storage/${path}`
}

async function openApplicationsModal() {
  showApplicationsModal.value = true
  loadingApplications.value = true
  try {
    const res = await axios.get('/api/hr/positions/applications', { withCredentials: true })
    if (res.data?.ok) {
      applications.value = Array.isArray(res.data.applications) ? res.data.applications : []
    } else {
      applications.value = []
    }
  } catch (err) {
    console.error('[MainBranchHrPanel] Failed loading applications:', err)
    applications.value = []
    alert(err.response?.data?.message || 'Failed to load applications.')
  } finally {
    loadingApplications.value = false
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
loadPositionRequests()

// Position Requests (for approval)
async function loadPositionRequests() {
  loadingPositionRequests.value = true
  try {
    const res = await axios.get('/api/hr/positions/requests/pending', { withCredentials: true })
    if (res.data && res.data.ok) {
      positionRequests.value = res.data.requests || []
    } else {
      positionRequests.value = []
    }
  } catch (e) {
    console.error('[MainBranchHrPanel] Failed loading position requests:', e.response || e.message || e)
    positionRequests.value = []
  } finally {
    loadingPositionRequests.value = false
  }
}

async function approveRequest(req) {
  processingRequestId.value = req.id
  try {
    const res = await axios.post(`/api/hr/positions/requests/${req.id}/approve`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Request approved successfully.')
      await loadPositionRequests()
    } else {
      alert(res.data?.message || 'Failed to approve request.')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to approve request.')
  } finally {
    processingRequestId.value = null
  }
}

function openRejectModal(req) {
  rejectingRequest.value = req
  rejectReason.value = ''
  showRejectModal.value = true
}

function closeRejectModal() {
  showRejectModal.value = false
  rejectingRequest.value = null
  rejectReason.value = ''
}

async function confirmReject() {
  if (!rejectingRequest.value) return
  processingRequestId.value = rejectingRequest.value.id
  try {
    const res = await axios.post(`/api/hr/positions/requests/${rejectingRequest.value.id}/reject`, {
      reason: rejectReason.value
    }, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Request rejected.')
      closeRejectModal()
      await loadPositionRequests()
    } else {
      alert(res.data?.message || 'Failed to reject request.')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to reject request.')
  } finally {
    processingRequestId.value = null
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

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
  padding: 12px 14px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.positions-modal__header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.positions-modal__body {
  padding: 12px 14px;
  max-height: 60vh;
  overflow: auto;
}

.positions-modal__footer {
  padding: 12px 14px;
  border-top: 1px solid #f3f4f6;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.modal-close {
  border: none;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
  color: #6b7280;
}
.modal-close:hover { color: #111827; }

.positions-list { display: flex; flex-direction: column; gap: 8px; }
.position-row {
  border: 1px solid #e5e7eb;
  background: #fafafa;
  border-radius: 8px;
  padding: 10px;
}

.position-row__meta {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 8px;
}

.position-row__name { font-weight: 700; color: #111827; font-size: 13px; }
.position-row__dept { color: #6b7280; font-size: 12px; }

.position-row__inputs {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 10px;
}

.field { display: flex; flex-direction: column; gap: 4px; }
.field-label { font-weight: 700; font-size: 11px; color: #374151; }
.field-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  outline: none;
  font-size: 12px;
}
.field-input:focus {
  border-color: #4b5563;
  box-shadow: 0 0 0 3px rgba(75, 85, 99, 0.1);
}
.field-textarea {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  outline: none;
  resize: vertical;
  font-size: 12px;
}

@media (max-width: 768px) {
  .position-row__inputs { grid-template-columns: 1fr; }
  .positions-modal__footer { flex-direction: column; align-items: stretch; }
}

.main-branch-hr-panel { width: 100%; padding: 0; background: transparent; height: auto; min-height: 0; display: block; }

.main-branch-hr-page { width: 100%; }
.main-branch-hr-hero {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 18px 16px;
  margin-bottom: 14px;
  background: linear-gradient(135deg, #fffaf5 0%, #fff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(66,33,11,0.05);
}
.main-branch-hr-eyebrow { display: inline-block; margin-bottom: 6px; color: #c25a12; font-size: 10px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
.main-branch-hr-title { margin: 0; color: #1f2937; font-size: 26px; line-height: 1.1; }
.main-branch-hr-subtitle { margin: 6px 0 0; color: #64748b; font-size: 13px; max-width: 560px; }
.main-branch-hr-hero__action { flex-shrink: 0; margin-top: 2px; }
.main-branch-hr-profile { margin-bottom: 14px; }


/* Position Requests Section */
.position-requests-section {
  background: #fff;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 4px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  display: block;
  height: auto;
}

.pending-badge {
  display: inline-block;
  background: #ffc107;
  color: #000;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: bold;
  margin-top: 3px;
}

.requests-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 10px;
}

.request-card {
  background: #fafafa;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 10px;
}

.request-card--approved { border-left: 4px solid #22c55e; }
.request-card--rejected { border-left: 4px solid #dc3545; }
.request-card--pending { border-left: 4px solid #ffc107; }

.request-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.request-card__position {
  font-weight: 700;
  font-size: 13px;
  color: #111827;
}

.request-card__status {
  padding: 2px 7px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 600;
}

.status-approved { background: #dcfce7; color: #166534; }
.status-rejected { background: #fee2e2; color: #991b1b; }
.status-pending { background: #fef3c7; color: #92400e; }

.request-card__info {
  display: grid;
  grid-template-columns: 92px minmax(0, 1fr);
  gap: 6px;
  margin-bottom: 4px;
  font-size: 12px;
}

.request-card__info .label {
  color: #6b7280;
  font-weight: 600;
}

.request-card__info .value {
  color: #111827;
}

.request-card__notes {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
}

.request-card__notes .label {
  font-weight: 600;
  font-size: 11px;
  color: #6b7280;
}

.request-card__notes p {
  margin: 2px 0 0 0;
  font-size: 12px;
  color: #111827;
}

.request-card__notes--rejection {
  background: #fef2f2;
  padding: 8px;
  border-radius: 6px;
}

.request-card__actions {
  display: flex;
  gap: 8px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #e5e7eb;
}

.btn-success,
.btn-danger,
.btn-secondary,
.btn-primary {
  background: #4b5563;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 12px;
}

.btn-success:hover,
.btn-danger:hover,
.btn-secondary:hover,
.btn-primary:hover {
  background: #374151;
}

.btn-success:disabled,
.btn-danger:disabled,
.btn-secondary:disabled,
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Reject Modal */
.reject-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
}

.reject-modal {
  width: 92%;
  max-width: 480px;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 12px 50px rgba(0,0,0,0.25);
  overflow: hidden;
}

.reject-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.reject-modal__header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.reject-modal__body {
  padding: 12px 14px;
}

.reject-modal__body p {
  margin: 0 0 12px 0;
  color: #374151;
  font-size: 13px;
}

.reject-modal__footer {
  padding: 12px 14px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.hero { margin-bottom: 0; display: none; }
.panel-section { display: block; background: transparent; padding: 0; }
.position-requests-section + .branch-stats {
  margin-top: 0;
}
.section-title { font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 3px 0; letter-spacing: -0.2px; }
.section-description { margin: 0 0 4px 0; color: #6b7280; font-size: 13px; }
.info-box { background: transparent; border: none; border-radius: 0; padding: 0; width: 100%; box-shadow: none; height: auto; margin-bottom: 0; display: none; }
.info-box p { margin: 0; color: #374151; font-size: 13px; line-height: 1.4; }
.info-box p:first-child { margin-top: 0; }
.info-box p:last-child { margin-bottom: 0; }

.hr-summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 0 0 14px; }
.hr-summary-card { display: flex; flex-direction: column; gap: 4px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; }
.hr-summary-card strong { color: #111827; font-size: 22px; line-height: 1; }
.hr-summary-label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
.hr-summary-card--pending { border-left: 3px solid #f59e0b; }
.hr-summary-card--active { border-left: 3px solid #22c55e; }

.branch-stats { background: transparent; padding: 0; border-radius: 8px; border: none; box-shadow: none; height: auto; min-height: 0; }
.section-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin: 0 0 6px 0; flex-wrap: wrap; }
.muted { color: #6b7280; margin: 2px 0 0 0; font-size: 12px; }
.pill-btn { background: #4b5563; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; box-shadow: 0 1px 3px rgba(75, 85, 99, 0.1); transition: all 0.2s ease; font-size: 12px; }
.pill-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(75, 85, 99, 0.12); }
.pill-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.account-filters { display: flex; gap: 8px; margin: 0 0 10px; }
.account-search, .account-status-filter { min-height: 34px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; color: #1f2937; font-size: 12px; padding: 7px 10px; }
.account-search { flex: 1; min-width: 180px; }
.account-search:focus, .account-status-filter:focus { outline: 2px solid rgba(59,130,246,0.2); border-color: #64748b; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }

.branch-grid { display: flex; flex-direction: column; gap: 0; width: 100%; align-items: flex-start; }
.branch-card { background: transparent; border: none; border-radius: 0; box-shadow: none; padding: 0; display: contents; flex-direction: column; gap: 0; transition: none; width: 100%; min-width: 0; align-self: flex-start; margin: 0; }
.branch-card:hover { border-color: transparent; box-shadow: none; }

.branch-card__header { display: flex; align-items: center; justify-content: space-between; gap: 6px; padding: 8px 10px; border-bottom: 2px solid #111827; flex-shrink: 0; background: #f5f6f7; margin: 4px 0 0 0; border-radius: 0; border-top: 1px solid #d1d5db; }
.header-content { flex: 1; }
.header-top { display: flex; align-items: center; gap: 8px; margin-bottom: 0; }
.branch-name { margin: 0; font-size: 15px; font-weight: 700; color: #111827; }
.badge-branch { background: #e0e7ff; color: #3730a3; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.2px; }
.branch-subtitle { margin: 0; color: #6b7280; font-size: 12px; display: flex; gap: 12px; }
.stat-total { font-weight: 600; color: #374151; }
.stat-active { color: #22c55e; font-weight: 600; }

.table-container { width: 100%; overflow-x: auto; border: 1px solid #d1d5db; border-radius: 0; background: #ffffff; margin-top: 0; border-top: none; }
.staff-table { width: 100%; border-collapse: collapse; background: #ffffff; table-layout: fixed; font-size: 12px; }
.staff-table thead { position: sticky; top: 0; z-index: 10; }
.staff-table th { padding: 3px 5px; text-align: left; font-size: 10px; font-weight: 700; color: #374151; background: #f3f4f6; border-bottom: 2px solid #d1d5db; text-transform: uppercase; letter-spacing: 0.2px; white-space: nowrap; }
.staff-table td { padding: 3px 5px; text-align: left; font-size: 11px; color: #374151; border-bottom: 1px solid #e5e7eb; height: auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.staff-row { transition: background-color 0.2s ease; }
.staff-row:hover { background-color: #f9fafb; }

.col-name { width: 35%; }
.col-username { width: 25%; }
.col-role { width: 18%; }
.col-dept { width: 15%; }
.col-status { width: 15%; }

.name-badge { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 4px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; font-weight: 700; font-size: 9px; margin-right: 2px; vertical-align: middle; }
.code-text { background: #f0f1f3; padding: 1px 3px; border-radius: 2px; font-family: 'Courier New', monospace; font-size: 10px; color: #6b7280; }

.role-badge { display: inline-block; padding: 1px 4px; border-radius: 3px; font-weight: 600; font-size: 8px; text-transform: uppercase; letter-spacing: 0.1px; }
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

.status-badge { display: inline-flex; align-items: center; gap: 2px; padding: 1px 4px; border-radius: 3px; font-weight: 600; font-size: 9px; }
.status-active { background: #dcfce7; color: #166534; }
.status-inactive { background: #fee2e2; color: #991b1b; }
.status-dot { width: 4px; height: 4px; border-radius: 50%; display: inline-block; }
.dot-active { background: #22c55e; box-shadow: 0 0 2px rgba(34, 197, 94, 0.3); }
.dot-inactive { background: #ef4444; }

.empty-branch { color: #9ca3af; font-style: italic; padding: 12px; text-align: center; font-size: 12px; background: #ffffff; border: 1px solid #d1d5db; border-top: none; }
.loading-box, .empty-box { padding: 12px; color: #6b7280; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 6px; text-align: center; font-size: 12px; }
.alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 12px; border-radius: 6px; font-size: 12px; }

@media (max-width: 1024px) {
  .branch-grid { gap: 6px; }
}

@media (max-width: 768px) {
  .main-branch-hr-panel { padding: 0; }
  .main-branch-hr-hero { flex-direction: column; gap: 12px; }
  .main-branch-hr-hero__action { width: 100%; }
  .branch-stats { padding: 6px; }
  .section-header { flex-direction: column; align-items: stretch; gap: 8px; }
  .pill-btn { width: 100%; padding: 8px 12px; }
  .hr-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .account-filters { flex-direction: column; }
  .account-search, .account-status-filter { width: 100%; box-sizing: border-box; }
  .col-dept { display: none; }
  .branch-card { padding: 6px; }
  .staff-table th, .staff-table td { padding: 2px 3px; font-size: 9px; }
  .name-badge { width: 18px; height: 18px; font-size: 8px; margin-right: 2px; }
  .header-top { flex-direction: column; align-items: flex-start; gap: 2px; }
  .branch-grid { gap: 2px; }
}

/* Hide header title and description, keep actions */
:deep(.admin-main-header > .admin-main-header-top > div:nth-child(1)),
:deep(.admin-main-header > .admin-main-header-top > div:nth-child(2)) {
  display: none;
}

/* Make header actions full width and centered */
:deep(.admin-main-header) {
  padding: 0;
}

:deep(.admin-main-header-top) {
  justify-content: flex-end;
  gap: 0;
}
</style>
