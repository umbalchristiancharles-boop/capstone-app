<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Branch Confirmations'"
    :panelDescription="'Approve or reject new branch requests after finance confirmation'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    :showHeader="false"
    :showProfileColumn="false"
    :showAnnouncements="false"
    :showAttendanceCard="false"
    :singleColumnLayout="true"
    :fullWidth="true"
    @logout="confirmLogout"
  >
    <template #main>
      <div class="branch-approval-page">
        <section class="branch-approval">
          <button class="back-to-dashboard-btn branch-approval-back-button" @click="goBackToOwnerPanel" title="Back to Owner Panel">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Dashboard</span>
          </button>

          <div class="branch-approval-header">
            <div>
              <h1>Branch Confirmations</h1>
              <p>Approve or reject new branch requests after finance confirmation</p>
            </div>
            <div class="header-actions">
              <button class="refresh-btn" @click="loadPending" :disabled="loading">
                {{ loading ? 'Loading...' : 'Refresh' }}
              </button>
            </div>
          </div>

          <div v-if="loading" class="muted">Loading pending requests...</div>
        <div v-else-if="error" class="alert-error">Warning: {{ error }}</div>
        <div v-else-if="pendingBranches.length === 0" class="empty-state">
          <p>No pending branch requests right now.</p>
        </div>

        <div v-else class="request-grid">
          <article v-for="branch in pendingBranches" :key="branch.id" class="request-card">
            <div class="request-card__top">
              <div>
                <div class="request-title">
                  <h3>{{ branch.name }}</h3>
                  <span class="badge badge-pending">Pending</span>
                </div>
                <p class="request-meta">
                  Code: <strong>{{ branch.code }}</strong>
                  <span class="dot">|</span>
                  Budget: <strong>{{ formatCurrency(branch.budget || 0) }}</strong>
                </p>
                <p class="request-meta">
                  Requested by:
                  <strong>{{ branch.requested_by?.full_name || branch.requested_by?.username || 'Unknown' }}</strong>
                  <span class="dot">|</span>
                  {{ formatDate(branch.created_at) }}
                </p>
              </div>
              <div class="request-id">#{{ branch.id }}</div>
            </div>

            <div class="request-card__body">
              <div class="address-block">
                <div class="label">Address</div>
                <div class="value">{{ branch.address || 'No address provided' }}</div>
              </div>
            </div>

            <div class="request-card__actions">
              <button
                class="btn-approve"
                :disabled="approvingId === branch.id || rejectingId === branch.id"
                @click="approveBranch(branch)"
              >
                {{ approvingId === branch.id ? 'Approving...' : 'Approve & Activate' }}
              </button>
              <button
                class="btn-reject"
                :disabled="approvingId === branch.id || rejectingId === branch.id"
                @click="rejectBranch(branch)"
              >
                {{ rejectingId === branch.id ? 'Rejecting...' : 'Reject' }}
              </button>
            </div>
          </article>
        </div>
      </section>
      </div>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import Swal from 'sweetalert2'
import { showToast } from './toastStore'

const router = useRouter()
const userProfile = ref({})
const pendingBranches = ref([])
const loading = ref(false)
const error = ref('')
const approvingId = ref(null)
const rejectingId = ref(null)
const hasNotified = ref(false)

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatCurrency(amount) {
  try {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(amount)
  } catch (e) {
    return 'PHP ' + (amount || 0)
  }
}

async function loadProfile() {
  try {
    const res = await axios.get('/api/me', { withCredentials: true })
    if (res.data?.ok) {
      userProfile.value = res.data.user || {}
    }
  } catch (e) {
    console.warn('Owner branch confirmations: failed to load profile', e)
  }
}

async function loadPending() {
  loading.value = true
  error.value = ''
  try {
    const res = await axios.get('/api/owner/branch-requests', { withCredentials: true })
    if (res.data?.ok) {
      pendingBranches.value = res.data.branches || []
      notifyIfPending()
    } else {
      error.value = res.data?.message || 'Failed to load pending branches.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load pending branches.'
  } finally {
    loading.value = false
  }
}

function notifyIfPending() {
  if (hasNotified.value) return
  if ((pendingBranches.value || []).length > 0) {
    showToast('You have pending branch approvals.', 'info')
    hasNotified.value = true
  }
}

async function approveBranch(branch) {
  const result = await Swal.fire({
    title: 'Approve branch? ',
    text: `This will activate ${branch.name} and enable all accounts.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Approve',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#16a34a',
  })

  if (!result.isConfirmed) return

  approvingId.value = branch.id
  try {
    const res = await axios.post(`/api/owner/branch-requests/${branch.id}/approve`, {}, { withCredentials: true })
    if (res.data?.ok) {
      await loadPending()
    } else {
      error.value = res.data?.message || 'Failed to approve branch.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to approve branch.'
  } finally {
    approvingId.value = null
  }
}

async function rejectBranch(branch) {
  const result = await Swal.fire({
    title: 'Reject branch? ',
    text: `This will keep ${branch.name} inactive.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Reject',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#dc2626',
  })

  if (!result.isConfirmed) return

  rejectingId.value = branch.id
  try {
    const res = await axios.post(`/api/owner/branch-requests/${branch.id}/reject`, {}, { withCredentials: true })
    if (res.data?.ok) {
      await loadPending()
    } else {
      error.value = res.data?.message || 'Failed to reject branch.'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to reject branch.'
  } finally {
    rejectingId.value = null
  }
}

function goBackToOwnerPanel() {
  router.push('/owner-panel')
}

const confirmLogout = async () => {
  const result = await Swal.fire({
    title: 'Confirm logout',
    text: 'This will end your current session for Chikin Tayo.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#FF6A3D',
    cancelButtonColor: '#636B7B',
  })

  if (result.isConfirmed) {
    try {
      await axios.post('/logout', {}, { withCredentials: true })
    } catch (e) {
      console.warn('Logout request failed:', e)
    }
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    window.location.href = '/login'
  }
}

onMounted(async () => {
  await Promise.all([loadProfile(), loadPending()])
})
</script>

<style scoped>
.branch-approval {
  --paper: #fff8ef;
  --ink: #2f261f;
  --accent: #ff6a3d;
  --accent-soft: #ffe3d6;
  --success: #16a34a;
  --danger: #dc2626;
  padding: 16px 18px 40px;
}

.branch-approval__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 18px;
  border-radius: 18px;
  background: linear-gradient(135deg, rgba(255,154,74,0.16), rgba(255,106,61,0.12));
  box-shadow: 0 14px 30px rgba(0,0,0,0.08);
  margin-bottom: 20px;
}

.branch-approval-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 1.25rem 1.5rem;
  border-radius: 24px;
  background: linear-gradient(135deg, #fff7f0, #fff6f1);
  border: 1px solid rgba(255, 106, 61, 0.15);
  box-shadow: 0 24px 64px rgba(15, 23, 42, 0.08);
  margin-bottom: 1.5rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.branch-approval-header h1 {
  margin: 0 0 0.25rem;
  font-size: 2rem;
  line-height: 1.05;
  color: var(--ink);
}

.branch-approval-header p {
  margin: 0;
  color: rgba(47,38,31,0.7);
}

.branch-approval-back-button {
  margin-bottom: 1rem;
}

.back-to-dashboard-btn,
.branch-approval-back-button {
  display: inline-flex;
  width: auto;
  max-width: fit-content;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1rem;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(255, 106, 61, 0.12), rgba(251, 191, 36, 0.16));
  color: #c2410c;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.92rem;
  line-height: 1;
  box-shadow: none;
  border: 0;
  white-space: nowrap;
  transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
}

.back-to-dashboard-btn:hover,
.branch-approval-back-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(255, 106, 61, 0.16);
  opacity: 0.95;
}

.back-icon {
  flex-shrink: 0;
}

.panel-badge { position:absolute; top:-8px; right:-18px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }

.back-btn--inline {
  background: rgba(47,38,31,0.12);
  color: var(--ink);
  padding: 8px 14px;
  border-radius: 999px;
  border: 1px solid rgba(47,38,31,0.15);
}

:deep(.admin-layout.no-profile-column),
:deep(.admin-layout.admin-layout--wider.no-profile-column) {
  grid-template-columns: minmax(0, 1fr) !important;
}

:deep(.admin-layout.no-profile-column) .admin-side,
:deep(.admin-layout.admin-layout--wider.no-profile-column) .admin-side {
  display: none !important;
}

.refresh-btn {
  padding: 10px 18px;
  border-radius: 999px;
  border: none;
  background: #fff;
  color: var(--ink);
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 18px rgba(0,0,0,0.08);
}

.muted {
  color: rgba(47,38,31,0.7);
  padding: 12px 0;
}

.empty-state {
  background: var(--paper);
  padding: 18px;
  border-radius: 14px;
  color: var(--ink);
}

.alert-error {
  background: #fee2e2;
  border: 1px solid #fecaca;
  color: #991b1b;
  padding: 12px 16px;
  border-radius: 10px;
}

.request-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.request-card {
  background: var(--paper);
  border-radius: 18px;
  padding: 18px;
  box-shadow: 0 16px 28px rgba(0,0,0,0.08);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.request-card__top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.request-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.request-title h3 {
  margin: 0;
  font-size: 1.2rem;
  color: var(--ink);
}

.request-meta {
  margin: 6px 0 0;
  color: rgba(47,38,31,0.7);
  font-size: 0.92rem;
}

.dot {
  margin: 0 6px;
}

.request-id {
  font-weight: 700;
  color: rgba(47,38,31,0.5);
}

.address-block .label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: rgba(47,38,31,0.5);
}

.address-block .value {
  font-size: 0.95rem;
  color: var(--ink);
  margin-top: 6px;
}

.request-card__actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-approve,
.btn-reject {
  flex: 1;
  padding: 10px 16px;
  border-radius: 999px;
  border: none;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-approve {
  background: var(--success);
  color: #fff;
  box-shadow: 0 10px 18px rgba(22,163,74,0.25);
}

.btn-reject {
  background: var(--danger);
  color: #fff;
  box-shadow: 0 10px 18px rgba(220,38,38,0.2);
}

.btn-approve:hover,
.btn-reject:hover {
  transform: translateY(-1px);
}

.badge {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.badge-pending {
  background: var(--accent-soft);
  color: var(--accent);
}

@media (max-width: 640px) {
  .branch-approval__header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
  }

  .request-card__top {
    flex-direction: column;
  }

  .btn-approve,
  .btn-reject {
    width: 100%;
  }
}
</style>
