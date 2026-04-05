<template>
  <div class="main-branch-admin-panel">
    <OwnerPanelLayout
      :userProfile="userProfile"
      :panelTitle="'Main Branch Administration'"
      :panelDescription="'Main Branch management and configuration'"
      :fullWidth="true"
      :enableProfileUpdate="true"
      :canEditProfile="false"
      :canChangePassword="true"
      :showProfileColumn="false"
      :showAnnouncements="false"
      @logout="askLogout"
      @profile-updated="onProfileUpdated"
    >
      <template #main>
        <section class="finance-panel">
          <div class="finance-header">
            <div>
              <h3 class="finance-title">Branch Financial Reports</h3>
              <p class="finance-sub">View branch KPIs and recent transactions</p>
            </div>
            <div class="finance-actions">
              <button class="add-branch" @click.prevent="goToBranches">+ Add Branch</button>
              <button class="crm-button" @click.prevent="goToCRM">CRM</button>
              <select
                class="branch-select"
                v-model="selectedBranch"
                @change="refreshFinance"
                :disabled="branchesLoading || financeLoading"
              >
                <option value="all">All Branches</option>
                <option v-for="b in branches" :key="b.id" :value="b.id">
                  {{ b.name || b.branch_name || ('Branch ' + b.id) }}
                </option>
              </select>
              <button class="refresh-finance" @click.prevent="refreshFinance" :disabled="financeLoading">
                {{ financeLoading ? 'Refreshing...' : 'Refresh Finance' }}
              </button>
            </div>
          </div>

          <div v-if="financeLoading" class="loading-state">Loading financial reports...</div>
          <div v-else-if="financeError" class="error-state">{{ financeError }}</div>
          <div v-else class="finance-wrapper">
            <FinancePanelContent
              :reports="financeReports"
              :transactions="financeTransactions"
              :transactionsLoading="financeLoading"
              :chartLoading="financeLoading"
            />
          </div>
        </section>
      </template>

      <template #headerActions>
        <div class="header-profile-wrapper" @click.stop>
          <button class="header-profile-btn" @click="toggleProfileDropdown">
            <div class="header-avatar">
              <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
              <div v-else class="header-avatar-initials">{{ (userProfile.fullName || 'A').charAt(0) }}</div>
            </div>
            <div class="header-name">{{ ((userProfile.fullName || userProfile.full_name) || 'ADMIN').toUpperCase() }}</div>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import FinancePanelContent from './finance/FinancePanelContent.vue'

const userProfile = ref({})
const profileDropdownVisible = ref(false)
const router = useRouter()

const financeReports = ref([])
const financeTransactions = ref([])
const financeLoading = ref(true)
const financeError = ref('')
const branches = ref([])
const branchesLoading = ref(false)
const selectedBranch = ref('all')
const FINANCE_TIMEOUT_MS = 12000

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
    const ok = await (window.swalConfirm ? window.swalConfirm('Logout from Main Branch Admin Panel?', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) {}
}

async function confirmLogout() {
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    safeNavigate('/')
  }, 600)
}

function askLogout() {
  try {
    window.swalConfirm('Logout from Main Branch Admin Panel?', 'Confirm logout').then(ok => {
      if (ok) confirmLogout()
    })
  } catch (e) {}
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

function safeNavigate(path) {
  try {
    router.push(path)
    return
  } catch (e) {}

  try {
    const protocol = window.location && window.location.protocol ? window.location.protocol : ''
    if (protocol === 'http:' || protocol === 'https:') {
      window.location.href = path
      return
    }
    if (window.top && window.top !== window.self) {
      window.top.location.href = path
    }
  } catch (e) {}
}

function goToBranches() {
  safeNavigate('/main-branch/branches')
}

function goToCRM() {
  safeNavigate('/main-branch/crm')
}

async function loadFinance() {
  financeLoading.value = true
  financeError.value = ''
  try {
    const params = {}
    if (selectedBranch.value !== 'all') params.branch_id = selectedBranch.value
    const [reportsRes, txRes] = await Promise.all([
      axios.get('/api/admin/finance/reports', { withCredentials: true, timeout: FINANCE_TIMEOUT_MS, params }),
      axios.get('/api/admin/finance/transactions', { withCredentials: true, timeout: FINANCE_TIMEOUT_MS, params })
    ])

    if (reportsRes.data && reportsRes.data.ok) {
      const r = reportsRes.data.reports || reportsRes.data.data || []
      financeReports.value = Array.isArray(r) ? r : []
    } else {
      financeReports.value = []
    }

    if (txRes.data && txRes.data.ok) {
      const t = txRes.data.transactions || txRes.data.data || []
      financeTransactions.value = Array.isArray(t) ? t : []
    } else {
      financeTransactions.value = []
    }
  } catch (e) {
    console.error('Failed to load finance data', e)
    financeError.value = 'Failed to load finance data. Please refresh.'
  } finally {
    financeLoading.value = false
  }
}

async function refreshFinance() {
  await loadFinance()
}

async function loadBranches() {
  branchesLoading.value = true
  try {
    const res = await axios.get('/api/admin/branches', { withCredentials: true, timeout: FINANCE_TIMEOUT_MS })
    const list = res.data?.branches || res.data?.data || res.data || []
    branches.value = Array.isArray(list) ? list : []
  } catch (e) {
    console.error('Failed to load branches', e)
    branches.value = []
  } finally {
    branchesLoading.value = false
  }
}

onMounted(async () => {
  await loadBranches()
  await loadFinance()
})

// Close dropdown when clicking outside
window.addEventListener('click', () => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})
</script>

<style scoped>
.main-branch-admin-panel {
  width: 100%;
}

.finance-panel {
  background: #fff;
  padding: 18px;
  border-radius: 10px;
  box-shadow: 0 6px 12px rgba(17,24,39,0.04);
}
.finance-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f2f5;
  margin-bottom: 12px;
}
.finance-title { margin: 0; font-size: 18px; font-weight: 700 }
.finance-sub { margin: 4px 0 0; color: #6b7280; font-size: 13px }
.finance-actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap }
.branch-select { padding: 8px 12px; border-radius: 8px; border: 1px solid #e6e6e6; min-width: 160px; height: 40px }
.add-branch {
  background: #111827;
  color: #fff;
  border: none;
  padding: 10px 14px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.add-branch:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(17,24,39,0.18); opacity: 0.98 }
.add-branch:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(17,24,39,0.14) }
.crm-button {
  background: linear-gradient(180deg, #06b6d4, #0891b2);
  color: #fff;
  border: none;
  padding: 10px 14px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.crm-button:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(6,182,212,0.18); opacity: 0.98 }
.crm-button:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(6,182,212,0.14) }
.refresh-finance {
  background: linear-gradient(180deg, #ff8a42, #ff6a00);
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 700;
  box-shadow: 0 6px 18px rgba(255,106,0,0.14);
  cursor: pointer;
  transition: transform 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.refresh-finance:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255,106,0,0.18); opacity: 0.98 }
.refresh-finance:active { transform: translateY(0); box-shadow: 0 6px 18px rgba(255,106,0,0.14) }
.loading-state { color: #6b7280; padding: 10px 0 }
.error-state { color: #ef4444; padding: 10px 0 }
.finance-wrapper { margin-top: 6px }

@media (max-width: 800px) {
  .finance-header { flex-direction: column; align-items: stretch }
  .branch-select { width: 100%; min-width: 0 }
  .refresh-finance { width: 100% }
  .crm-button { width: 100% }
}
</style>
