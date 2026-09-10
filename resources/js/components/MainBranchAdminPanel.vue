<template>
  <div class="main-branch-admin-panel">
    <OwnerPanelLayout
      ref="ownerLayout"
      :userProfile="userProfile"
      :panelTitle="'Main Branch Administration'"
      :panelDescription="'Main Branch management and configuration'"
      :fullWidth="true"
      :enableProfileUpdate="true"
      :canEditProfile="false"
      :canChangePassword="true"
      :showHeader="false"
      :showProfileColumn="false"
      :showAnnouncements="false"
      :showOwnerSidebar="true"
      :showOwnerTopbar="true"
      topbarLabel="ADMIN - Main Branch"
      :showAttendanceCard="false"
      accountInfoStyle="finance"
      @logout="askLogout"
      @profile-updated="onProfileUpdated"
    >
      <template #ownerSidebar>
        <nav class="owner-sidebar-nav" aria-label="Administration sections">
          <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': activeSection === 'finance-overview' }" @click="showFinanceSection('branch-financial-reports')">Branch Financial Reports</button>
          <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': activeSection === 'transactions' }" @click="showFinanceSection('recent-transactions')">Recent Transactions</button>
          <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': activeSection === 'crm' }" @click="activeSection = 'crm'">CRM</button>
          <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': activeSection === 'branches' }" @click="activeSection = 'branches'">Add Branch</button>
        </nav>
      </template>

      <template #ownerSidebarFooter>
        <div class="owner-sidebar-actions">
          <button type="button" class="owner-sidebar-account" @click="ownerLayout?.openInfoModal()">Account Info</button>
          <button type="button" class="owner-sidebar-logout" @click="askLogout">Logout</button>
        </div>
      </template>

      <template #main>
        <Transition name="main-branch-section" mode="out-in">
        <div :key="activeSection" class="main-branch-section-view">
        <template v-if="activeSection === 'finance-overview' || activeSection === 'transactions'">
        <header class="main-branch-admin-hero">
          <div class="main-branch-admin-hero__copy">
            <span class="main-branch-admin-hero__eyebrow">Administration dashboard</span>
            <h2 class="main-branch-admin-hero__title">Main Branch Administration</h2>
            <p class="main-branch-admin-hero__subtitle">Manage branches, CRM access, and financial performance from one place.</p>
          </div>
          <button class="refresh-finance main-branch-admin-hero__action" @click.prevent="refreshFinance" :disabled="financeLoading">
            {{ financeLoading ? 'Refreshing...' : 'Refresh Dashboard' }}
          </button>
        </header>

        <section id="branch-financial-reports" class="finance-panel">
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
            <div v-if="selectedBranch !== 'all'" class="branch-info-banner">
              Showing data for:
              <strong>{{ getSelectedBranchName() }}</strong>
            </div>
            <FinancePanelContent
              :reports="financeReports"
              :transactions="financeTransactions"
              :transactionsLoading="financeLoading"
              :chartLoading="financeLoading"
              :showOverview="activeSection === 'finance-overview'"
              :showTransactions="activeSection === 'transactions'"
            />
          </div>
        </section>
        </template>

        <MainBranchCRMPanel v-else-if="activeSection === 'crm'" />
        <OwnerAddBranches v-else-if="activeSection === 'branches'" />
        </div>
        </Transition>
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

    <div v-if="showLogoutOverlay" class="main-branch-logout-overlay">
      <div class="main-branch-logout-box">
        <img :src="logoImg" alt="Chikin Tayo" class="main-branch-logout-logo" />
        <p>Logging out...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import FinancePanelContent from './finance/FinancePanelContent.vue'
import MainBranchCRMPanel from './MainBranchCRMPanel.vue'
import OwnerAddBranches from './OwnerAddBranches.vue'

const userProfile = ref({})
const ownerLayout = ref(null)
const activeSection = ref('finance-overview')
const profileDropdownVisible = ref(false)
const showLogoutOverlay = ref(false)
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href
const router = useRouter()

const financeReports = ref([])
const financeTransactions = ref([])
const financeLoading = ref(true)
const financeError = ref('')
const branches = ref([])
const branchesLoading = ref(false)
const selectedBranch = ref('all')
const FINANCE_TIMEOUT_MS = 12000
let autoRefreshInterval = null

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
  if (showLogoutOverlay.value) return
  showLogoutOverlay.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    safeNavigate('/admin-login')
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

function showFinanceSection(sectionId) {
  activeSection.value = sectionId === 'recent-transactions' ? 'transactions' : 'finance-overview'
  nextTick(() => {
    document.querySelector('.main-branch-admin-panel .admin-main')?.scrollTo({
      top: 0,
      behavior: 'smooth'
    })
  })
}

function getSelectedBranchName() {
  if (selectedBranch.value === 'all') return 'All Branches'
  const branch = branches.value.find(b => b.id === parseInt(selectedBranch.value))
  return branch?.name || branch?.branch_name || ('Branch ' + selectedBranch.value)
}

async function loadFinance() {
  financeLoading.value = true
  financeError.value = ''
  try {
    const params = {
      _t: Date.now() // Cache buster - force fresh data
    }
    if (selectedBranch.value !== 'all') params.branch_id = selectedBranch.value
    console.log('Loading finance data with params:', params)
    const [reportsRes, txRes] = await Promise.all([
      axios.get('/api/admin/finance/reports', {
        withCredentials: true,
        timeout: FINANCE_TIMEOUT_MS,
        params,
        headers: {
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache'
        }
      }),
      axios.get('/api/admin/finance/transactions', {
        withCredentials: true,
        timeout: FINANCE_TIMEOUT_MS,
        params,
        headers: {
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache'
        }
      })
    ])

    console.log('Reports response:', reportsRes.data)
    console.log('Transactions response:', txRes.data)

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
    console.log('Loaded branches:', branches.value)
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

  // Auto-refresh finance data every 30 seconds to ensure fresh data
  autoRefreshInterval = setInterval(async () => {
    console.log('Auto-refreshing finance data...')
    await loadFinance()
  }, 30000) // 30 seconds
})

onUnmounted(() => {
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval)
  }
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

.main-branch-logout-overlay {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.35);
  -webkit-backdrop-filter: blur(4px);
  backdrop-filter: blur(4px);
}

.main-branch-logout-box {
  min-width: 168px;
  padding: 12px 18px 14px;
  border-radius: 12px;
  background: #ffffff;
  text-align: center;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
}

.main-branch-logout-logo {
  display: block;
  width: 80px;
  height: auto;
  margin: 0 auto 8px;
}

.main-branch-logout-box p {
  margin: 0;
  color: #6b6b6b;
  font-size: 0.9rem;
  font-weight: 500;
}

.main-branch-section-view {
  min-height: 1px;
}

.main-branch-section-enter-active,
.main-branch-section-leave-active {
  transition: opacity 220ms ease, transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
}

.main-branch-section-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.main-branch-section-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.main-branch-admin-panel :deep(.admin-layout--owner-sidebar-layout) {
  padding: 0;
}

.main-branch-admin-panel :deep(.owner-panel-topbar) {
  position: fixed;
  top: 0;
  right: 0;
  padding: 0.75rem 1.25rem;
  background: linear-gradient(180deg, #e7d9cf 0%, #eee5df 100%) !important;
  border-bottom-color: rgba(115, 93, 84, 0.18);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
}

.main-branch-admin-panel :deep(.admin-main) {
  margin-top: 66px !important;
}

@media (max-width: 767px) {
  .main-branch-admin-panel :deep(.owner-panel-topbar) {
    left: 0;
    width: 100%;
    margin-left: 0;
  }
}

.main-branch-admin-panel :deep(.owner-sidebar-nav) {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

.main-branch-admin-panel :deep(.owner-sidebar-link) {
  width: 100%;
  padding: 0.7rem 0.75rem;
  border: 1px solid transparent;
  border-radius: 12px;
  background: transparent;
  color: #1f2937;
  font-size: 0.78rem;
  font-weight: 600;
  line-height: 1.25;
  text-align: left;
  text-decoration: none;
  cursor: pointer;
}

.main-branch-admin-panel :deep(.owner-sidebar-link:hover),
.main-branch-admin-panel :deep(.owner-sidebar-link--active) {
  background: rgba(255, 255, 255, 0.85);
  border-color: rgba(148, 163, 184, 0.3);
  color: #111827;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.main-branch-admin-panel :deep(.owner-sidebar-actions) {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.main-branch-admin-panel :deep(.owner-sidebar-account),
.main-branch-admin-panel :deep(.owner-sidebar-logout) {
  width: 100%;
  padding: 0.7rem 0.75rem;
  border-radius: 12px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
}

.main-branch-admin-panel :deep(.owner-sidebar-account) {
  border: 1px solid rgba(59, 130, 246, 0.25);
  background: rgba(59, 130, 246, 0.12);
  color: #30445a;
}

.main-branch-admin-panel :deep(.owner-sidebar-logout) {
  border: 1px solid rgba(138, 113, 95, 0.25);
  background: rgba(255, 159, 67, 0.12);
  color: #a23d32;
}

.main-branch-admin-hero {
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
.main-branch-admin-hero__copy { min-width: 0; }
.main-branch-admin-hero__eyebrow { display: inline-block; margin-bottom: 6px; color: #c25a12; font-size: 10px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
.main-branch-admin-hero__title { margin: 0; color: #1f2937; font-size: 26px; line-height: 1.1; }
.main-branch-admin-hero__subtitle { margin: 6px 0 0; color: #64748b; font-size: 13px; max-width: 560px; }
.main-branch-admin-hero__action { flex-shrink: 0; margin-top: 2px; }

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
  background: #4b5563;
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
.crm-button:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(75,85,99,0.18); opacity: 0.98 }
.crm-button:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(75,85,99,0.14) }
.refresh-finance {
  background: #4b5563;
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(75,85,99,0.12);
  cursor: pointer;
  transition: transform 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.refresh-finance:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(75,85,99,0.18); opacity: 0.98 }
.refresh-finance:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(75,85,99,0.14) }
.loading-state { color: #6b7280; padding: 10px 0 }
.error-state { color: #ef4444; padding: 10px 0 }
.finance-wrapper { margin-top: 6px }

.branch-info-banner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  font-size: 14px;
  font-weight: 500;
}

.dark-mode .main-branch-admin-panel .finance-panel {
  background: #161b26;
  border: 1px solid rgba(148,163,184,0.16);
  box-shadow: 0 14px 40px rgba(0, 0, 0, 0.28);
}

.dark-mode .main-branch-admin-panel .finance-header {
  border-bottom-color: rgba(148,163,184,0.18);
}

.dark-mode .main-branch-admin-panel .finance-title,
.dark-mode .main-branch-admin-panel .loading-state,
.dark-mode .main-branch-admin-panel .error-state {
  color: #e2e8f0;
}

.dark-mode .main-branch-admin-panel .finance-sub {
  color: rgba(226,232,240,0.75);
}

.dark-mode .main-branch-admin-panel .branch-select {
  background: #111827;
  color: #e2e8f0;
  border-color: rgba(148,163,184,0.25);
}

.dark-mode .main-branch-admin-panel .branch-select option {
  background: #111827;
  color: #e2e8f0;
}

.dark-mode .main-branch-admin-panel .branch-info-banner {
  background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
}

.dark-mode .main-branch-admin-panel .header-profile-btn {
  color: #e2e8f0;
}

.dark-mode .main-branch-admin-panel .header-profile-dropdown {
  background: #0f172a;
  border: 1px solid rgba(255,255,255,0.12);
  box-shadow: 0 16px 44px rgba(0, 0, 0, 0.4);
}

.dark-mode .main-branch-admin-panel .header-profile-dropdown .dropdown-item {
  color: #e2e8f0;
  background: transparent;
}

.dark-mode .main-branch-admin-panel .header-profile-dropdown .dropdown-item:hover {
  background: rgba(255,255,255,0.08);
}

/* Move attendance card down slightly */
:deep(.attendance-section),
:deep(.attendance-card),
:deep([class*="attendance"]) {
  margin-top: 12px;
}

@media (max-width: 800px) {
  .main-branch-admin-hero { flex-direction: column; gap: 12px; }
  .main-branch-admin-hero__action { width: 100%; }
  .finance-header { flex-direction: column; align-items: stretch }
  .branch-select { width: 100%; min-width: 0 }
  .refresh-finance { width: 100% }
  .crm-button { width: 100% }
}
</style>
