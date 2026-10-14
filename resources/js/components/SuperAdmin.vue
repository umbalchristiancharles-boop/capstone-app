<template>
  <div class="super-admin-shell" :class="{ 'sidebar-collapsed': sidebarCollapsed, 'super-admin-sidebar-resizing': sidebarResizing }" :style="{ '--super-admin-sidebar-width': `${sidebarWidth}px` }">
    <aside id="super-admin-sidebar" class="super-admin-sidebar" :aria-hidden="sidebarCollapsed" :style="{ width: `${sidebarWidth}px` }">
      <PanelSidebarBrand />
      <nav class="super-admin-sidebar__nav" aria-label="Website panels">
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'dashboard' }" :aria-current="activeModule === 'dashboard' ? 'page' : undefined" @click="openModule('dashboard')">Dashboard</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'owner' }" @click="openModule('owner')">Owner Panel</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'mainBranchAdmin' }" @click="openModule('mainBranchAdmin')">Admin Main Branch</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'admin' }" @click="openModule('admin')">Admin Panel</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'mainBranchLogistics' }" @click="openModule('mainBranchLogistics')">Logistics Main Branch</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'mainBranchHr' }" @click="openModule('mainBranchHr')">HR Main Branch</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'mainBranchFinance' }" @click="openModule('mainBranchFinance')">Finance Manager Main Branch</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'logisticsManager' }" @click="openModule('logisticsManager')">Logistics Manager</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'procurementManager' }" @click="openModule('procurementManager')">Procurement Manager</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'hrManager' }" @click="openModule('hrManager')">HR Manager</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'financeManager' }" @click="openModule('financeManager')">Finance Manager</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'kitchenStaff' }" @click="openModule('kitchenStaff')">Kitchen Staff</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'cashierStaff' }" @click="openModule('cashierStaff')">Cashier Staff</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'inventoryStaff' }" @click="openModule('inventoryStaff')">Inventory Staff</button>
        <button class="super-admin-sidebar__item" :class="{ active: activeModule === 'supplier' }" @click="openModule('supplier')">Supplier Panel</button>
      </nav>

      <button
        class="super-admin-sidebar__resize-handle"
        type="button"
        aria-label="Resize sidebar"
        title="Resize sidebar"
        @pointerdown="startSidebarResize"
      ></button>
    </aside>

    <div class="super-admin-main-panel">
      <header class="super-admin-topbar">
        <button
          v-if="sidebarCollapsed"
          class="super-admin-hamburger"
          type="button"
          aria-label="Show menu"
          aria-controls="super-admin-sidebar"
          :aria-expanded="false"
          @click="sidebarCollapsed = false"
        >☰</button>
        <div class="super-admin-topbar__spacer"></div>
        <div ref="accountMenuRef" class="super-admin-account-menu">
          <button
            id="super-admin-account-toggle"
            class="super-admin-user-pill"
            type="button"
            aria-haspopup="menu"
            :aria-expanded="isAccountMenuOpen"
            aria-controls="super-admin-account-dropdown"
            @click="isAccountMenuOpen = !isAccountMenuOpen"
            @keydown.down.prevent="openAccountMenu"
          >
            <span class="super-admin-user-pill__avatar">{{ userInitial }}</span>
            <span>{{ superAdminProfile.fullName || 'Super Admin' }}</span>
            <span class="super-admin-user-pill__chevron" :class="{ 'is-open': isAccountMenuOpen }" aria-hidden="true">⌄</span>
          </button>
          <div
            v-if="isAccountMenuOpen"
            id="super-admin-account-dropdown"
            class="super-admin-account-dropdown"
            role="menu"
            aria-labelledby="super-admin-account-toggle"
            @keydown="handleAccountMenuKeydown"
          >
            <div class="super-admin-account-dropdown__heading">
              <span class="super-admin-account-dropdown__name">{{ superAdminProfile.fullName || 'Super Admin' }}</span>
              <span class="super-admin-account-dropdown__role">Super Administrator</span>
            </div>
            <button class="super-admin-account-dropdown__item" type="button" role="menuitem" @click="openAccountInfo">
              <span class="super-admin-account-dropdown__item-icon" aria-hidden="true">◉</span>
              <span>Account Info</span>
            </button>
            <button class="super-admin-account-dropdown__item" type="button" role="menuitem" @click="openAnnouncement">
              <span class="super-admin-account-dropdown__item-icon" aria-hidden="true">✉</span>
              <span>Send Announcement</span>
            </button>
            <button class="super-admin-account-dropdown__item" type="button" role="menuitem" @click="openTerms">
              <span class="super-admin-account-dropdown__item-icon" aria-hidden="true">▤</span>
              <span>Update Terms</span>
            </button>
            <div class="super-admin-account-dropdown__divider"></div>
            <button class="super-admin-account-dropdown__item super-admin-account-dropdown__item--logout" type="button" role="menuitem" @click="logoutFromAccountMenu">
              <span class="super-admin-account-dropdown__item-icon" aria-hidden="true">↪</span>
              <span>Logout</span>
            </button>
          </div>
        </div>
      </header>

      <div class="admin-page">
      <section v-if="activeModule === 'dashboard'" class="admin-layout super-admin-dashboard-layout">
          <div class="page-header-top super-admin-page-header">
            <div>
              <h1>{{ panelTitle }}</h1>
              <p>{{ panelDescription }}</p>
              <p v-if="isLoadingDashboard && !isInitialMount" class="small-hint">Loading dashboard...</p>
              <p v-else-if="dashboardError" class="small-hint small-hint--error">{{ dashboardError }}</p>
            </div>
          </div>

          <aside class="admin-side super-admin-dashboard-side">
            <section class="panel-block super-admin-summary-card">
              <div class="panel-header"><h2>Top Products (All Branches)</h2></div>
              <div class="panel-body panel-body--list">
                <div v-if="topProducts.length === 0" class="side-item"><span>No data for this range.</span></div>
                <div v-else v-for="prod in topProducts" :key="prod.id" class="side-item"><span>{{ prod.name }}</span><span class="side-value">{{ prod.orders }} orders</span></div>
              </div>
            </section>
            <section class="panel-block super-admin-summary-card super-admin-summary-card--stock">
              <div class="panel-header"><h2>Low Stock Alerts</h2></div>
              <div class="panel-body panel-body--list">
                <div v-if="lowStockItems.length === 0" class="side-item side-item--alert"><span>All items above minimum stock.</span></div>
                <div v-else v-for="item in lowStockItems" :key="item.id" class="side-item side-item--alert"><span>{{ item.name }}</span><span class="side-value">{{ item.stock }}</span></div>
              </div>
            </section>
            <section class="panel-block super-admin-summary-card super-admin-summary-card--attendance">
              <div class="panel-header"><h2>Staff Attendance (All Branches)</h2></div>
              <div class="panel-body panel-body--table super-admin-attendance-table">
                <div class="table-header"><span>Staff</span><span>Branch</span><span>Status</span></div>
                <div v-if="adminAttendance.length === 0" class="table-row"><span>No records.</span><span></span><span></span></div>
                <div v-else v-for="att in adminAttendance.slice(0, 10)" :key="att.id" class="table-row"><span>{{ att.user_name }}</span><span>{{ att.branch_name || '-' }}</span><span><span class="badge" :class="{ 'badge--success': att.status === 'present', 'badge--warning': att.status === 'late', 'badge--info': att.status === 'absent' }">{{ att.status || '-' }}</span></span></div>
              </div>
            </section>
          </aside>

        <main class="admin-main super-admin-dashboard-main">
          <div class="range-tabs super-admin-range-tabs">
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'today' }" @click="changeRange('today')">Today</button>
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'yesterday' }" @click="changeRange('yesterday')">Yesterday</button>
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisWeek' }" @click="changeRange('thisWeek')">This Week</button>
            <button class="range-tab" :class="{ 'range-tab--active': activeRange === 'thisMonth' }" @click="changeRange('thisMonth')">This Month</button>
          </div>

          <section class="overview-grid super-admin-overview-grid">
            <div class="overview-card"><span class="overview-label">Total Orders</span><span class="overview-value">{{ dashboardTotals.orders }}</span></div>
            <div class="overview-card"><span class="overview-label">Completed</span><span class="overview-value">{{ dashboardTotals.completed }}</span></div>
            <div class="overview-card"><span class="overview-label">Total Sales</span><span class="overview-value">{{ dashboardTotals.sales }}</span></div>
            <div class="overview-card"><span class="overview-label">Pending</span><span class="overview-value">{{ dashboardTotals.pending }}</span></div>
          </section>

          <section class="panel-block super-admin-branch-panel">
            <div class="panel-header">
              <h2>System Overview by Branch</h2>
            </div>
            <div class="panel-body panel-body--table super-admin-branch-table">
              <div class="table-header"><span>Branch</span><span>Orders</span><span>Sales</span><span>Staff</span><span>Status</span></div>
              <div v-if="branchStats.length === 0" class="table-row"><span>No branch data for this range.</span><span></span><span></span><span></span><span></span></div>
              <div v-else v-for="branch in branchStats" :key="branch.id" class="table-row">
                <span>{{ branch.name }}</span>
                <span>{{ branch.orders }}</span>
                <span>{{ branch.sales }}</span>
                <span>{{ branch.staff_count }}</span>
                <span>
                  <span class="badge" :class="branch.is_active ? 'badge--success' : 'badge--warning'">
                    {{ branch.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </span>
              </div>
            </div>
          </section>

          <section class="panel-block super-admin-activity-panel">
            <div class="panel-header">
              <div>
                <h2>Recent System Activity</h2>
                <p class="super-admin-activity-panel__hint">Verified events from the system activity sources.</p>
              </div>
              <span v-if="isLoadingDashboard" class="super-admin-activity-panel__loading">Updating...</span>
            </div>
            <div class="panel-body panel-body--list">
              <div v-if="systemActivity.length === 0" class="queue-item"><div class="queue-title">No recorded activity for this period.</div></div>
              <div v-else v-for="activity in systemActivity" :key="activity.id" class="queue-item">
                <div>
                  <div class="queue-title">{{ activity.title }}</div>
                  <div class="queue-meta">{{ activity.description }}</div>
                  <div class="super-admin-activity-meta">
                    <span>{{ activity.module || 'System' }}</span>
                    <span v-if="activity.branch">{{ activity.branch }}</span>
                    <time v-if="activity.timestamp" :datetime="activity.timestamp">{{ formatActivityTimestamp(activity.timestamp) }}</time>
                  </div>
                </div>
                <span class="badge" :class="activity.badgeClass">{{ activity.status }}</span>
              </div>
            </div>
          </section>
        </main>

      </section>

      <section
        v-else
        ref="monitoringViewRef"
        class="super-admin-module-view"
        @click.capture="guardMonitoringClick"
        @change.capture="guardMonitoringChange"
        @submit.capture="guardMonitoringSubmit"
      >
        <div
          v-if="showMonitoringBranchSelector || activeModule === 'inventoryStaff'"
          :data-monitoring-module="activeModule"
          class="super-admin-monitoring-toolbar"
        >
          <div class="super-admin-monitoring-toolbar__copy">
            <strong>Branch monitoring</strong>
            <span>Choose a branch to view its live monitoring data.</span>
          </div>
          <label
            class="super-admin-monitoring-branch-selector"
            :class="{ 'super-admin-monitoring-branch-selector--loading': isBranchOptionsLoading && !availableBranches.length }"
          >
            <span>Branch</span>
            <select
              v-model="selectedBranchId"
              :aria-label="`Filter ${activeModule} panel by branch`"
              :disabled="isBranchOptionsLoading && !availableBranches.length"
            >
              <option v-if="isBranchOptionsLoading && !availableBranches.length" value="">Loading branches...</option>
              <option value="">All Branches</option>
              <option v-for="branch in availableBranches" :key="branch.id" :value="String(branch.id)">{{ branch.name }}</option>
            </select>
          </label>
        </div>
        <component
          :is="activeModuleComponent"
          v-bind="{
            ...(activeModule === 'inventoryStaff' ? { isSuperAdmin: true } : {}),
            ...(activeModule === 'mainBranchAdmin' ? { monitoringOnly: true } : {}),
            ...(activeModule === 'admin' ? {
              showBranchSelector: true,
              superAdminBranches: availableBranches,
              superAdminBranchLoading: isBranchOptionsLoading,
            } : {}),
          }"
          :key="`${activeModule}:${selectedBranchId}`"
        />
      </section>

      <!-- ANNOUNCEMENT MODAL -->
      <transition name="fade">
        <div v-if="showAnnouncement" class="info-backdrop" @click.self="closeAnnouncementModal">
          <div class="info-modal announcement-modal">
            <div class="modal-header-custom">
              <div class="announcement-modal__heading">
                <span class="announcement-modal__icon" aria-hidden="true">📢</span>
                <div>
                  <h3>Send Announcement</h3>
                  <p>Share an update with selected people across your organization.</p>
                </div>
              </div>
              <button class="modal-close-btn" type="button" aria-label="Close announcement dialog" @click="closeAnnouncementModal">✕</button>
            </div>

            <div class="modal-body-custom">
              <!-- Title Field -->
              <div class="form-group-custom">
                <label class="info-label">Title</label>
                <input
                  v-model="announcementTitle"
                  class="info-input"
                  type="text"
                  placeholder="Enter announcement title"
                  @keyup.enter="sendAnnouncement"
                />
              </div>

              <!-- Message Field -->
              <div class="form-group-custom">
                <label class="info-label">Message</label>
                <textarea
                  v-model="announcementText"
                  class="info-input"
                  rows="5"
                  placeholder="Write your announcement message..."
                ></textarea>
              </div>

              <!-- Target Selection -->
              <div class="form-group-custom">
                <label class="info-label">Send To</label>
                <select v-model="announcementTarget" class="info-input">
                  <option value="all">👥 All Branches (Everyone)</option>
                  <option value="staff">👨‍🍳 All Staff</option>
                  <option value="managers">👔 Managers Only</option>
                </select>
              </div>

              <!-- Error/Success Messages -->
              <div v-if="announcementError" class="alert-message alert-error">
                ⚠️ {{ announcementError }}
              </div>
              <div v-if="announcementSuccess" class="alert-message alert-success">
                ✅ {{ announcementSuccess }}
              </div>
            </div>

            <div class="modal-footer-custom">
              <button
                class="btn-outline"
                @click="closeAnnouncementModal"
                :disabled="isSendingAnnouncement"
              >
                Cancel
              </button>
              <button
                class="btn-primary"
                @click="sendAnnouncement"
                :disabled="isSendingAnnouncement"
              >
                {{ isSendingAnnouncement ? 'Sending...' : 'Send Announcement' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- TERMS MODAL -->
      <transition name="fade">
        <div v-if="showTerms" class="info-backdrop" @click.self="showTerms = false">
          <div class="info-modal terms-modal">
            <div class="modal-header-custom">
              <div class="announcement-modal__heading">
                <span class="announcement-modal__icon" aria-hidden="true">📄</span>
                <div>
                  <h3>Update Terms &amp; Agreement</h3>
                  <p>Manage the terms and conditions shown throughout the system.</p>
                </div>
              </div>
              <button class="modal-close-btn" type="button" aria-label="Close terms dialog" @click="showTerms = false">✕</button>
            </div>

            <div class="terms-modal__body">
              <div class="form-group-custom">
                <label class="info-label" for="super-admin-terms-content">Terms content</label>
                <textarea
                  id="super-admin-terms-content"
                  v-model="termsText"
                  class="info-input"
                  rows="10"
                  placeholder="Enter the terms and conditions..."
                ></textarea>
                <span class="terms-modal__hint">Changes will apply system-wide after saving.</span>
              </div>

              <div v-if="termsError" class="info-error">{{ termsError }}</div>
              <div v-if="termsSuccess" class="info-success">{{ termsSuccess }}</div>
            </div>

            <div class="modal-footer-custom">
              <button class="btn-outline" type="button" @click="showTerms = false">Cancel</button>
              <button class="btn-primary" type="button" @click="updateTerms" :disabled="isUpdatingTerms">
                {{ isUpdatingTerms ? 'Saving...' : 'Save Terms' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- INFO MODAL -->
      <transition name="fade">
        <div v-if="showInfoModal" class="info-backdrop">
          <div class="info-modal">
            <h3>Super Admin Information</h3>
            <p class="info-sub">Personal details for this super admin account.</p>

            <div class="info-grid">
              <div class="info-row">
                <span class="info-label">Full name</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.fullName }}</span>
                <input v-else v-model="superAdminProfile.fullName" class="info-input" type="text" />
              </div>

              <div class="info-row">
                <span class="info-label">Role</span>
                <span class="info-value">{{ superAdminProfile.role }}</span>
              </div>

              <div class="info-row">
                <span class="info-label">Username</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.username }}</span>
                <input v-else v-model="superAdminProfile.username" class="info-input" type="text" />
              </div>

              <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.email }}</span>
                <input v-else v-model="superAdminProfile.email" class="info-input" type="email" />
              </div>

              <div class="info-row">
                <span class="info-label">Contact</span>
                <span class="info-value" v-if="!isEditingInfo">{{ superAdminProfile.contact }}</span>
                <input v-else v-model="superAdminProfile.contact" class="info-input" type="text" />
              </div>
            </div>

            <div v-if="profileError" class="info-error">{{ profileError }}</div>
            <div v-if="profileSuccess" class="info-success">{{ profileSuccess }}</div>

            <div class="info-actions">
              <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>
              <button class="btn-primary" @click="isEditingInfo ? saveProfile() : (isEditingInfo = true)" :disabled="isSavingProfile">
                {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- LOGOUT CONFIRM MODAL -->
      <transition name="fade">
        <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
          <div class="logout-confirm-box logout-confirm-dialog">
            <h3>Logout from Super Admin Panel?</h3>
            <p>This will end your current session for Chikin Tayo System.</p>
            <div class="logout-actions">
              <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
              <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, defineAsyncComponent, provide } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import '../css/adminpanel.css'
import PanelSidebarBrand from './PanelSidebarBrand.vue'
import { showToast } from './toastStore'

const router = useRouter()
const route = useRoute()

// Profile state
const superAdminProfile = ref({
   username: '',
  role: 'SUPER_ADMIN',
 fullName: '',
  email: '',
  contact: '',
  accountId: '',
  avatarUrl: ''
})
const isProfileLoading = ref(true)
const isEditingInfo = ref(false)
const isSavingProfile = ref(false)
const profileError = ref('')
const profileSuccess = ref('')

// Modals
const showInfoModal = ref(false)
const showLogoutConfirm = ref(false)
const showAnnouncement = ref(false)
const showTerms = ref(false)
const isLoggingOut = ref(false)
const isAccountMenuOpen = ref(false)
const accountMenuRef = ref(null)
const monitoringViewRef = ref(null)
const sidebarCollapsed = ref(false)
const sidebarWidth = ref(220)
const sidebarResizing = ref(false)
const activeModule = ref('dashboard')
const selectedBranchId = ref('')
const availableBranches = ref([])
const isBranchOptionsLoading = ref(true)
const branchScopedModules = new Set(['admin', 'logisticsManager', 'procurementManager', 'hrManager', 'financeManager', 'kitchenStaff', 'cashierStaff', 'inventoryStaff', 'supplier'])
const showBranchSelector = computed(() => branchScopedModules.has(activeModule.value))
const monitoringBranchSelectorModules = new Set(['hrManager', 'financeManager', 'kitchenStaff', 'cashierStaff', 'inventoryStaff', 'supplier'])
const showMonitoringBranchSelector = computed(() => activeModule.value === 'logisticsManager' || monitoringBranchSelectorModules.has(activeModule.value))
provide('superAdminEmbedded', true)
provide('superAdminBranchId', selectedBranchId)
provide('superAdminBranchScoped', showBranchSelector)

const monitoringActionPattern = /\b(?:log[\s_-]*out|sign[\s_-]*out|clock[\s_-]*(?:in|out)|check[\s_-]*(?:in|out)|create|add|edit|update|save|delete|remove|approve|reject|confirm|submit|send|publish|unpublish|request|receive|dispatch|transfer|assign|issue|adjust|process|complete|refund|void|upload|import|activate|deactivate|toggle|archive|restore|reset)\b/i
const monitoringReadControlPattern = /^\s*(?:view|details|show|hide|expand|collapse|refresh|reload|search|filter|clear filters?|export|download|print|previous|next|page(?:\s+\d+)?|\d+|today|week|month|year|all|active|inactive|pending|completed|dashboard|overview|back|close|menu|more|less)\b/i
let monitoringControlsObserver = null

const branchRequestInterceptor = axios.interceptors.request.use((config) => {
  const method = String(config.method || 'get').toLowerCase()
  const requestPath = String(config.url || '')
    .replace(/^https?:\/\/[^/]+/i, '')
    .split(/[?#]/, 1)[0]
  const isSuperAdminOwnAction = config.superAdminAction === true && (
    (method === 'put' && requestPath === '/api/superadmin-profile') ||
    (method === 'post' && ['/api/superadmin/avatar', '/api/superadmin/announce', '/api/superadmin/terms'].includes(requestPath))
  )
  const isPanelActionPath = /(?:^|\/)(?:logout|clock[\s_-]*(?:in|out)|check[\s_-]*(?:in|out)|approve|reject|confirm|mark-as-[^/]+|delete|remove|create|update|save|publish|unpublish|assign|receive|transfer|submit|cancel|send|upload|activate|deactivate|toggle|archive|restore|reset)(?:\/|$)/i.test(requestPath)

  if (activeModule.value !== 'dashboard' && !isSuperAdminOwnAction &&
      (!['get', 'head', 'options'].includes(method) || isPanelActionPath)) {
    showToast('Read-only monitoring mode: operational actions are unavailable.', 'info')
    throw new Error('This panel is view-only in Super Admin monitoring mode.')
  }

  function formatActivityTimestamp(timestamp) {
    const date = new Date(timestamp)
    if (Number.isNaN(date.getTime())) return 'Time unavailable'
    return date.toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })
  }

  if (!showBranchSelector.value || method !== 'get' || !selectedBranchId.value || !String(config.url || '').startsWith('/api/')) {
    return config
  }

  config.params = { ...(config.params || {}), branch_id: selectedBranchId.value }
  return config
})

const moduleComponents = {
  mainBranchAdmin: defineAsyncComponent(() => import('./MainBranchAdminPanel.vue')),
  mainBranchLogistics: defineAsyncComponent(() => import('./MainBranchLogisticsPanel.vue')),
  mainBranchHr: defineAsyncComponent(() => import('./MainBranchHrPanel.vue')),
  mainBranchFinance: defineAsyncComponent(() => import('./MainBranchFinancePanel.vue')),
  owner: defineAsyncComponent(() => import('./OwnerPanel.vue')),
  admin: defineAsyncComponent(() => import('./adminpanel.vue')),
  logisticsManager: defineAsyncComponent(() => import('./ManagerLogisticsPanel.vue')),
  procurementManager: defineAsyncComponent(() => import('./ProcurementManagerPanel.vue')),
  hrManager: defineAsyncComponent(() => import('./ManagerHRPanel.vue')),
  financeManager: defineAsyncComponent(() => import('./ManagerFinancePanel.vue')),
  kitchenStaff: defineAsyncComponent(() => import('./SuperAdminKitchenStaff.vue')),
  cashierStaff: defineAsyncComponent(() => import('./Cashier.vue')),
  inventoryStaff: defineAsyncComponent(() => import('./inventory/InventoryStaffPanel.vue')),
  supplier: defineAsyncComponent(() => import('./SuperAdminSupplier.vue')),
}

const activeModuleComponent = computed(() => moduleComponents[activeModule.value] || null)
let lightModeObserver = null

function getMonitoringControlDescription(control) {
  const associatedLabels = Array.from(control.labels || []).map(label => label.textContent)
  return [
    control.textContent,
    control.getAttribute('aria-label'),
    control.getAttribute('title'),
    control.getAttribute('href'),
    control.getAttribute('placeholder'),
    ...associatedLabels,
    control.id,
    typeof control.className === 'string' ? control.className : '',
  ].filter(Boolean).join(' ')
}

function isMonitoringReadFilterForm(form) {
  const description = getMonitoringControlDescription(form)
  return !monitoringActionPattern.test(description) && /\b(?:search|filter|report|date|range)\b/i.test(description)
}

function isRestrictedMonitoringControl(control) {
  if (control.closest('.super-admin-monitoring-branch-selector')) return false
  const description = getMonitoringControlDescription(control)
  const isLogoutOrAttendanceControl = /\b(?:log[\s_-]*out|sign[\s_-]*out|clock[\s_-]*(?:in|out)|check[\s_-]*(?:in|out))\b/i.test(description) ||
    /\b(?:attendance-buttons|btn-clock|clock-in|clock-out)\b/i.test(description)
  if (isLogoutOrAttendanceControl) {
    return true
  }

  if (control.closest('nav, [role="tablist"], [class*="sidebar"], [class*="navigation"], [class*="pagination"], [class*="filter-bar"], [class*="filters"]')) {
    return false
  }

  if (monitoringActionPattern.test(description)) return true

  if (control instanceof HTMLAnchorElement &&
      (control.origin !== window.location.origin || control.pathname !== window.location.pathname)) {
    return true
  }

  if (control.matches(':disabled, [readonly]')) return false
  const form = control.closest('form')
  if (form && isMonitoringReadFilterForm(form)) return false
  if (control.matches('[type="submit"], input[type="button"], input[type="submit"]')) return true
  if (control.matches('[role="tab"], [aria-expanded]')) return false
  if (control.matches('input[type="search"]')) return false

  return !monitoringReadControlPattern.test(description)
}

function updateMonitoringControls() {
  const monitoringView = monitoringViewRef.value
  if (!monitoringView) return

  monitoringView.querySelectorAll('[data-super-admin-monitoring-restricted="true"]').forEach((element) => {
    element.removeAttribute('data-super-admin-monitoring-restricted')
  })

  const controls = monitoringView.querySelectorAll('button, [role="button"], input:not([type="hidden"]), select, textarea, a[href]')
  controls.forEach((control) => {
    if (isRestrictedMonitoringControl(control)) {
      control.setAttribute('data-super-admin-monitoring-restricted', 'true')
    } else {
      control.removeAttribute('data-super-admin-monitoring-restricted')
    }
  })

  const forms = monitoringView.querySelectorAll('form')
  forms.forEach((form) => {
    if (isMonitoringReadFilterForm(form)) return
    const hasRestrictedControl = Array.from(form.querySelectorAll('button, [role="button"], input:not([type="hidden"]), select, textarea'))
      .some(isRestrictedMonitoringControl)
    if (!hasRestrictedControl) return

    form.setAttribute('data-super-admin-monitoring-restricted', 'true')
    const panelBlock = form.closest('.panel-block')
    if (panelBlock && panelBlock.querySelectorAll('form').length === 1 &&
        !panelBlock.querySelector('table, .table-row, [role="table"], .side-item, .queue-item, .overview-card, ul, ol')) {
      panelBlock.setAttribute('data-super-admin-monitoring-restricted', 'true')
    }
  })
}

function observeMonitoringControls() {
  monitoringControlsObserver?.disconnect()
  monitoringControlsObserver = null
  if (!monitoringViewRef.value) return

  updateMonitoringControls()
  monitoringControlsObserver = new MutationObserver(updateMonitoringControls)
  monitoringControlsObserver.observe(monitoringViewRef.value, {
    attributes: true,
    attributeFilter: ['aria-label', 'class', 'href', 'title'],
    characterData: true,
    childList: true,
    subtree: true,
  })
}

function notifyMonitoringRestriction() {
  showToast('Read-only monitoring mode: operational actions are unavailable.', 'info')
}

function guardMonitoringClick(event) {
  const target = event.target
  if (!(target instanceof Element)) return

  const control = target.closest('button, [role="button"], input[type="button"], input[type="submit"], a[href]')
  if (control && isRestrictedMonitoringControl(control)) {
    event.preventDefault()
    event.stopPropagation()
    notifyMonitoringRestriction()
  }
}

function guardMonitoringChange(event) {
  const target = event.target
  if (!(target instanceof Element) || !isRestrictedMonitoringControl(target)) return
  event.preventDefault()
  event.stopPropagation()
  notifyMonitoringRestriction()
}

function guardMonitoringSubmit(event) {
  const form = event.target
  if (form instanceof HTMLFormElement && isMonitoringReadFilterForm(form)) return

  event.preventDefault()
  event.stopPropagation()
  notifyMonitoringRestriction()
}

function closeAccountMenuOnOutsideClick(event) {
  if (!accountMenuRef.value?.contains(event.target)) {
    isAccountMenuOpen.value = false
  }
}

function closeAccountMenuOnEscape(event) {
  if (event.key === 'Escape' && isAccountMenuOpen.value) {
    isAccountMenuOpen.value = false
    document.getElementById('super-admin-account-toggle')?.focus()
  }
}

function openAccountMenu() {
  isAccountMenuOpen.value = true
  requestAnimationFrame(() => {
    accountMenuRef.value?.querySelector('[role="menuitem"]')?.focus()
  })
}

function handleAccountMenuKeydown(event) {
  const items = Array.from(accountMenuRef.value?.querySelectorAll('[role="menuitem"]') || [])
  const currentIndex = items.indexOf(document.activeElement)
  let nextIndex = currentIndex

  if (event.key === 'ArrowDown') nextIndex = (currentIndex + 1) % items.length
  else if (event.key === 'ArrowUp') nextIndex = (currentIndex - 1 + items.length) % items.length
  else if (event.key === 'Home') nextIndex = 0
  else if (event.key === 'End') nextIndex = items.length - 1
  else return

  event.preventDefault()
  items[nextIndex]?.focus()
}

function openAccountInfo() {
  isAccountMenuOpen.value = false
  openInfoModal()
}

function openAnnouncement() {
  isAccountMenuOpen.value = false
  showAnnouncement.value = true
}

function openTerms() {
  isAccountMenuOpen.value = false
  showTerms.value = true
}

function logoutFromAccountMenu() {
  isAccountMenuOpen.value = false
  askLogout()
}

function startSidebarResize(event) {
  if (sidebarCollapsed.value) return

  event.preventDefault()
  sidebarResizing.value = true
  const startX = event.clientX
  const startWidth = sidebarWidth.value
  const resizeHandle = event.currentTarget
  resizeHandle.setPointerCapture(event.pointerId)

  const stopResize = () => {
    sidebarResizing.value = false
    document.removeEventListener('pointermove', resize)
    document.removeEventListener('pointerup', stopResize)
    document.removeEventListener('pointercancel', stopResize)
  }

  const resize = (moveEvent) => {
    const nextWidth = startWidth + moveEvent.clientX - startX
    if (nextWidth < 180) {
      sidebarCollapsed.value = true
      stopResize()
      return
    }

    sidebarWidth.value = Math.min(360, Math.max(200, nextWidth))
  }

  document.addEventListener('pointermove', resize)
  document.addEventListener('pointerup', stopResize)
  document.addEventListener('pointercancel', stopResize)
}

// Announcement
const announcementTitle = ref('')
const announcementText = ref('')
const announcementTarget = ref('all')
const announcementError = ref('')
const announcementSuccess = ref('')
const isSendingAnnouncement = ref(false)

// Close announcement modal and reset state
function closeAnnouncementModal() {
  showAnnouncement.value = false
  // Reset form fields
  announcementTitle.value = ''
  announcementText.value = ''
  announcementTarget.value = 'all'
  announcementError.value = ''
  announcementSuccess.value = ''
}

// Terms
const termsText = ref('')
const termsError = ref('')
const termsSuccess = ref('')
const isUpdatingTerms = ref(false)

// Dashboard
const activeRange = ref('today')
const isLoadingDashboard = ref(false)
const isInitialMount = ref(true)
const dashboardError = ref('')
const dashboardTotals = ref({ orders: 0, completed: 0, sales: '₱0', pending: 0 })
const summaryTotals = ref({ totalBranches: 0, totalEmployees: 0, totalAdmins: 0 })
const branchStats = ref([])
const systemActivity = ref([])
const topProducts = ref([])
const lowStockItems = ref([])
const adminAttendance = ref([])
let dashboardRefreshTimer = null
const pendingCounts = ref({
  procurement: 0,
  logistics: 0,
  supplier: 0,
  finance: 0,
  kitchen: 0,
  cashier: 0,
})
const hasNotified = ref(false)

const panelTitle = computed(() => 'Chikin Tayo Super Admin Panel')
const panelDescription = computed(() => 'Full system access - manage all modules, branches, and system settings.')
const userInitial = computed(() => (superAdminProfile.value.fullName || superAdminProfile.value.role || 'S').charAt(0).toUpperCase())

function enforceSuperAdminLightMode() {
  try {
    document.documentElement.classList.remove('dark-mode')
    document.body.classList.remove('dark-mode')
    document.documentElement.classList.add('light-mode')
    document.body.classList.add('light-mode')
    document.documentElement.removeAttribute('data-superadmin-theme')
    document.body.removeAttribute('data-superadmin-theme')
  } catch (e) {}
}

function startLightModeGuard() {
  enforceSuperAdminLightMode()
  lightModeObserver = new MutationObserver(() => {
    if (document.documentElement.classList.contains('dark-mode') || document.body.classList.contains('dark-mode')) {
      enforceSuperAdminLightMode()
    }
  })
  lightModeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-superadmin-theme'] })
  lightModeObserver.observe(document.body, { attributes: true, attributeFilter: ['class', 'data-superadmin-theme'] })
}

function isDashboardSection(section) {
  if (section === 'staff') return route.path.includes('/staff-management')
  if (section === 'hr') return route.path.includes('/hr')
  return false
}

function normalizeUser(u) {
  if (!u) return { fullName: '', username: '', role: '', email: '', contact: '', accountId: '', avatarUrl: '' }
  return {
    fullName: u.fullName ?? u.full_name ?? '',
    username: u.username ?? '',
    role: u.role ?? '',
    email: u.email ?? '',
    contact: u.contact ?? u.phone_number ?? '',
    accountId: u.accountId ?? u.account_id ?? '',
    avatarUrl: u.avatarUrl ?? u.avatar_url ?? '',
  }
}

async function loadProfile() {
  try {
    const res = await axios.get('/api/superadmin-profile', { withCredentials: true })
    if (res.data && res.data.ok && res.data.user) {
      superAdminProfile.value = normalizeUser(res.data.user)
    }
    isProfileLoading.value = false
  } catch (e) {
    if (e.response?.status === 401) { router.push('/staff-landing'); return }
    isProfileLoading.value = false
  }
}

async function loadDashboard(range, { silent = false } = {}) {
  if (!silent) {
    isLoadingDashboard.value = true
    dashboardTotals.value = { orders: 0, completed: 0, sales: '₱0', pending: 0 }
    summaryTotals.value = { totalBranches: 0, totalEmployees: 0, totalAdmins: 0 }
    branchStats.value = []
    systemActivity.value = []
    topProducts.value = []
    lowStockItems.value = []
    adminAttendance.value = []
  }
  dashboardError.value = ''

  try {
    const res = await axios.get('/api/superadmin/dashboard', { params: { range }, withCredentials: true })
    if (res.data) {
      summaryTotals.value = {
        totalBranches: res.data.totalBranches || res.data.branches_count || 0,
        totalEmployees: res.data.totalEmployees || res.data.staff_count || 0,
        totalAdmins: res.data.totalAdmins || 0
      }
      dashboardTotals.value = {
        orders: res.data.orders || 0,
        completed: res.data.completed || 0,
        sales: res.data.sales || '₱0',
        pending: res.data.pending || 0
      }
      branchStats.value = res.data.branchStats || []
      systemActivity.value = res.data.systemActivity || []
      topProducts.value = res.data.topProducts || []
      lowStockItems.value = res.data.lowStockItems || []
      adminAttendance.value = res.data.attendance || []
    }
  } catch (e) {
    if (e.response?.status === 401) { router.push('/staff-landing'); return }
    dashboardError.value = 'Error loading dashboard.'
  } finally {
    if (!silent) isLoadingDashboard.value = false
  }
}

async function changeRange(range) {
  if (activeRange.value === range) return
  activeRange.value = range
  await loadDashboard(range)
}

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      pendingCounts.value = { ...pendingCounts.value, ...(res.data.counts || {}) }
      const total = Object.values(pendingCounts.value).reduce((sum, v) => sum + Number(v || 0), 0)
      if (!hasNotified.value && total > 0) {
        showToast('You have pending items across modules.', 'info')
        hasNotified.value = true
      }
    }
  } catch (e) {
    // Non-blocking
  }
}

async function openInfoModal() {
  showInfoModal.value = true
  isEditingInfo.value = false
  profileError.value = ''
  profileSuccess.value = ''
  try {
    const res = await axios.get('/api/superadmin-profile', { withCredentials: true })
    if (res.data.ok) superAdminProfile.value = normalizeUser(res.data.user)
  } catch (e) {}
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false
    profileError.value = ''
    profileSuccess.value = ''
  } else {
    showInfoModal.value = false
  }
}

async function saveProfile() {
  isSavingProfile.value = true
  profileError.value = ''
  profileSuccess.value = ''
  try {
    const payload = {
      fullName: superAdminProfile.value.fullName,
      username: superAdminProfile.value.username,
      email: superAdminProfile.value.email,
      contact: superAdminProfile.value.contact
    }
    const res = await axios.put('/api/superadmin-profile', payload, { withCredentials: true, superAdminAction: true })
    if (res.data.ok) {
      isEditingInfo.value = false
      profileSuccess.value = 'Profile updated successfully.'
    } else {
      profileError.value = res.data.message || 'Failed to update profile.'
    }
  } catch (e) {
    profileError.value = e?.response?.data?.message || 'Failed to update profile.'
  } finally {
    isSavingProfile.value = false
  }
}

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (!(await window.swalConfirm('Are you sure you want to change your profile picture?'))) return

  try {
    // Get CSRF cookie first
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

    // Wait a bit for cookie to be set
    await new Promise(resolve => setTimeout(resolve, 200))

    // Get CSRF token from cookie
    function getCookie(name) {
      const value = `; ${document.cookie}`
      const parts = value.split(`; ${name}=`)
      if (parts.length === 2) {
        return parts.pop().split(';').shift()
      }
      return null
    }
    const xsrfToken = getCookie('XSRF-TOKEN')

    // Prepare form data
    const formData = new FormData()
    formData.append('avatar', file)

    // Upload avatar to superadmin endpoint
    const res = await axios.post('/api/superadmin/avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-XSRF-TOKEN': xsrfToken || '',
      },
      withCredentials: true,
      superAdminAction: true,
    })

    if (res.data && res.data.ok) {
      // Update local avatar immediately
      superAdminProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()

      // Optionally refresh profile from server to ensure consistency
      try {
        const profileRes = await axios.get('/api/superadmin-profile', { withCredentials: true })
        if (profileRes.data && profileRes.data.ok && profileRes.data.user) {
          superAdminProfile.value.avatarUrl = profileRes.data.user.avatarUrl
        }
      } catch (profileError) {
        console.log('Profile refresh skipped, using direct upload response')
      }

      alert('Profile picture updated successfully!')
    } else {
      alert(res.data.message || 'Failed to upload profile picture.')
    }
  } catch (e) {
    console.error('Avatar upload failed:', e)
    const errorMessage = e?.response?.data?.message || e?.message || 'Failed to upload profile picture. Please try again.'
    alert(errorMessage)
  }
}

function openModule(name) {
  if (name === 'dashboard' || moduleComponents[name]) activeModule.value = name
}

function ownerAddBranches() {
  activeModule.value = 'branches'
}

function goToSuperAdminStaff() {
  activeModule.value = 'staff'
}

async function sendAnnouncement() {
  if (!announcementTitle.value.trim() || !announcementText.value.trim()) {
    announcementError.value = 'Please enter a title and message.'
    return
  }
  isSendingAnnouncement.value = true
  announcementError.value = ''
  announcementSuccess.value = ''
  try {
    const res = await axios.post('/api/superadmin/announce', {
      title: announcementTitle.value,
      message: announcementText.value,
      target: announcementTarget.value
    }, { withCredentials: true, superAdminAction: true })
    if (res.data.ok) {
      announcementSuccess.value = 'Announcement sent successfully!'
      setTimeout(() => {
        showAnnouncement.value = false
        announcementTitle.value = ''
        announcementText.value = ''
        announcementTarget.value = 'all'
      }, 1500)
    } else {
      announcementError.value = res.data.message || 'Failed to send announcement.'
    }
  } catch (e) {
    announcementError.value = e?.response?.data?.message || 'Failed to send announcement.'
  } finally {
    isSendingAnnouncement.value = false
  }
}

async function updateTerms() {
  if (!termsText.value.trim()) {
    termsError.value = 'Please enter terms content.'
    return
  }
  isUpdatingTerms.value = true
  termsError.value = ''
  termsSuccess.value = ''
  try {
    const res = await axios.post('/api/superadmin/terms', {
      content: termsText.value
    }, { withCredentials: true, superAdminAction: true })
    if (res.data.ok) {
      termsSuccess.value = 'Terms updated successfully!'
      setTimeout(() => {
        showTerms.value = false
      }, 1500)
    } else {
      termsError.value = res.data.message || 'Failed to update terms.'
    }
  } catch (e) {
    termsError.value = e?.response?.data?.message || 'Failed to update terms.'
  } finally {
    isUpdatingTerms.value = false
  }
}

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    localStorage.clear()
    sessionStorage.clear()
    window.location.replace('/logout')
  } catch (e) {
    localStorage.clear()
    sessionStorage.clear()
    window.location.replace('/admin-login')
  }
}

function cancelLogout() {
  if (isLoggingOut.value) return
  showLogoutConfirm.value = false
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

onMounted(async () => {
  document.addEventListener('pointerdown', closeAccountMenuOnOutsideClick)
  document.addEventListener('keydown', closeAccountMenuOnEscape)
  observeMonitoringControls()
  startLightModeGuard()
  isInitialMount.value = false
  superAdminProfile.value = { fullName: '', role: 'SUPER_ADMIN', email: '', contact: '', accountId: '', avatarUrl: '' }
  await Promise.all([loadProfile(), loadAvailableBranches()])
  await loadDashboard(activeRange.value)
  await loadPanelNotifications()
  dashboardRefreshTimer = window.setInterval(() => {
    if (activeModule.value === 'dashboard' && !isLoadingDashboard.value) {
      loadDashboard(activeRange.value, { silent: true })
    }
  }, 30000)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', closeAccountMenuOnOutsideClick)
  document.removeEventListener('keydown', closeAccountMenuOnEscape)
  axios.interceptors.request.eject(branchRequestInterceptor)
  monitoringControlsObserver?.disconnect()
  monitoringControlsObserver = null
  lightModeObserver?.disconnect()
  lightModeObserver = null
  if (dashboardRefreshTimer) {
    window.clearInterval(dashboardRefreshTimer)
    dashboardRefreshTimer = null
  }
})

async function loadAvailableBranches() {
  isBranchOptionsLoading.value = true
  try {
    const response = await axios.get('/api/superadmin/branches', { withCredentials: true })
    availableBranches.value = response.data?.branches || response.data?.data || response.data || []
    if (!selectedBranchId.value && availableBranches.value.length) {
      selectedBranchId.value = String(availableBranches.value[0].id)
    }
  } catch (error) {
    console.error('Failed to load branch selector options:', error)
    availableBranches.value = []
  } finally {
    isBranchOptionsLoading.value = false
  }
}

watch(activeModule, observeMonitoringControls, { flush: 'post' })

// Reload dashboard whenever we navigate to this route so external changes (like added branches)
// are reflected immediately without requiring a manual refresh.
watch(() => route.path, (p) => {
  try {
    if (p === '/super-admin-panel') {
      loadDashboard(activeRange.value)
    }
  } catch (e) {}
})
</script>

<style scoped>
.super-admin-shell {
  --super-admin-sidebar-width: 156px;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  min-height: 100vh;
  background: #f1e5dc;
  color: #42210b;
}

.super-admin-shell :deep(.info-backdrop),
.super-admin-shell :deep(.logout-confirm-backdrop) {
  z-index: 1000;
}

.super-admin-shell :deep(.announcement-modal) {
  display: flex;
  width: min(560px, calc(100vw - 2rem));
  max-width: 560px;
  max-height: calc(100vh - 2rem);
  flex-direction: column;
  overflow: hidden;
  padding: 0;
  border: 1px solid #e8e2dc;
  border-radius: 18px;
  background: #fff;
  color: #33251d;
  box-shadow: 0 24px 70px rgba(31, 25, 20, 0.24);
}

.super-admin-shell :deep(.announcement-modal .modal-header-custom) {
  min-height: 88px;
  margin: 0;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #eee8e3;
  background: #fffaf6;
}

.super-admin-shell :deep(.announcement-modal__heading) {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 0.9rem;
}

.super-admin-shell :deep(.announcement-modal__icon) {
  display: grid;
  width: 42px;
  height: 42px;
  flex: 0 0 auto;
  place-items: center;
  border-radius: 12px;
  background: #fff0e7;
  font-size: 1.2rem;
}

.super-admin-shell :deep(.announcement-modal .modal-header-custom h3) {
  margin: 0;
  color: #42210b;
  font-size: 1.15rem;
  font-weight: 750;
  letter-spacing: -0.02em;
}

.super-admin-shell :deep(.announcement-modal .modal-header-custom p) {
  margin: 0.25rem 0 0;
  color: #7b6c62;
  font-size: 0.82rem;
  line-height: 1.45;
}

.super-admin-shell :deep(.announcement-modal .modal-close-btn) {
  width: 34px;
  height: 34px;
  flex: 0 0 auto;
  background: #f3efeb;
  color: #62554c;
}

.super-admin-shell :deep(.announcement-modal .modal-close-btn:hover) {
  background: #e9e1da;
  color: #42210b;
}

.super-admin-shell :deep(.announcement-modal .modal-body-custom) {
  gap: 1.1rem;
  overflow-y: auto;
  padding: 1.4rem 1.5rem;
}

.super-admin-shell :deep(.announcement-modal .form-group-custom) {
  gap: 0.45rem;
}

.super-admin-shell :deep(.announcement-modal .form-group-custom .info-label) {
  margin: 0;
  color: #4d4037;
  font-size: 0.82rem;
  font-weight: 650;
}

.super-admin-shell :deep(.announcement-modal .info-input) {
  width: 100%;
  min-height: 44px;
  padding: 0.7rem 0.8rem;
  border: 1px solid #ded8d2;
  border-radius: 9px;
  background: #fff;
  color: #33251d;
  font: inherit;
  font-size: 0.88rem;
  box-sizing: border-box;
  transition: border-color 150ms ease, box-shadow 150ms ease;
}

.super-admin-shell :deep(.announcement-modal textarea.info-input) {
  min-height: 132px;
  resize: vertical;
  line-height: 1.5;
}

.super-admin-shell :deep(.announcement-modal .info-input:focus) {
  border-color: #e85b22;
  outline: none;
  box-shadow: 0 0 0 3px rgba(232, 91, 34, 0.13);
}

.super-admin-shell :deep(.announcement-modal .info-input::placeholder) {
  color: #a49a92;
}

.super-admin-shell :deep(.announcement-modal .modal-footer-custom) {
  gap: 0.65rem;
  margin: 0;
  padding: 1rem 1.5rem;
  border-top: 1px solid #eee8e3;
  background: #fff;
}

.super-admin-shell :deep(.announcement-modal .modal-footer-custom button) {
  min-width: 116px;
  min-height: 42px;
  border-radius: 9px;
  font-size: 0.86rem;
  font-weight: 650;
}

.super-admin-shell :deep(.announcement-modal .modal-footer-custom .btn-outline) {
  border: 1px solid #ded8d2;
  background: #fff !important;
  color: #55483f !important;
}

.super-admin-shell :deep(.announcement-modal .modal-footer-custom .btn-outline:hover:not(:disabled)) {
  background: #f8f5f2 !important;
}

.super-admin-shell :deep(.announcement-modal .modal-footer-custom .btn-primary) {
  border: 1px solid #df4e16;
  background: #e85b22;
  color: #fff;
  box-shadow: 0 3px 8px rgba(232, 91, 34, 0.17);
}

.super-admin-shell :deep(.announcement-modal .modal-footer-custom .btn-primary:hover:not(:disabled)) {
  background: #cf4b18;
}

.super-admin-shell :deep(.terms-modal) {
  display: flex;
  width: min(620px, calc(100vw - 2rem));
  max-width: 620px;
  max-height: calc(100vh - 2rem);
  flex-direction: column;
  overflow: hidden;
  padding: 0;
  border: 1px solid #e8e2dc;
  border-radius: 18px;
  background: #fff;
  color: #33251d;
  box-shadow: 0 24px 70px rgba(31, 25, 20, 0.24);
}

.super-admin-shell :deep(.terms-modal .modal-header-custom) {
  min-height: 88px;
  margin: 0;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #eee8e3;
  background: #fffaf6;
}

.super-admin-shell :deep(.terms-modal .modal-header-custom h3) {
  margin: 0;
  color: #42210b;
  font-size: 1.15rem;
  font-weight: 750;
  letter-spacing: -0.02em;
}

.super-admin-shell :deep(.terms-modal .modal-header-custom p) {
  margin: 0.25rem 0 0;
  color: #7b6c62;
  font-size: 0.82rem;
  line-height: 1.45;
}

.super-admin-shell :deep(.terms-modal .modal-close-btn) {
  width: 34px;
  height: 34px;
  flex: 0 0 auto;
  background: #f3efeb;
  color: #62554c;
}

.super-admin-shell :deep(.terms-modal .modal-close-btn:hover) {
  background: #e9e1da;
  color: #42210b;
}

.super-admin-shell :deep(.terms-modal__body) {
  display: flex;
  min-height: 0;
  flex-direction: column;
  gap: 0.75rem;
  overflow-y: auto;
  padding: 1.4rem 1.5rem;
}

.super-admin-shell :deep(.terms-modal .form-group-custom) {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.super-admin-shell :deep(.terms-modal .info-label) {
  color: #4d4037;
  font-size: 0.82rem;
  font-weight: 650;
}

.super-admin-shell :deep(.terms-modal textarea.info-input) {
  width: 100%;
  min-height: 260px;
  padding: 0.8rem 0.9rem;
  border: 1px solid #ded8d2;
  border-radius: 9px;
  background: #fff;
  color: #33251d;
  font: inherit;
  font-size: 0.88rem;
  line-height: 1.55;
  resize: vertical;
  box-sizing: border-box;
  transition: border-color 150ms ease, box-shadow 150ms ease;
}

.super-admin-shell :deep(.terms-modal textarea.info-input:focus) {
  border-color: #e85b22;
  outline: none;
  box-shadow: 0 0 0 3px rgba(232, 91, 34, 0.13);
}

.super-admin-shell :deep(.terms-modal textarea.info-input::placeholder) {
  color: #a49a92;
}

.super-admin-shell :deep(.terms-modal__hint) {
  color: #84766d;
  font-size: 0.76rem;
}

.super-admin-shell :deep(.terms-modal .info-error),
.super-admin-shell :deep(.terms-modal .info-success) {
  margin: 0;
  font-size: 0.82rem;
}

.super-admin-shell :deep(.terms-modal .modal-footer-custom) {
  gap: 0.65rem;
  margin: 0;
  padding: 1rem 1.5rem;
  border-top: 1px solid #eee8e3;
  background: #fff;
}

.super-admin-shell :deep(.terms-modal .modal-footer-custom button) {
  min-width: 116px;
  min-height: 42px;
  border-radius: 9px;
  font-size: 0.86rem;
  font-weight: 650;
}

.super-admin-shell :deep(.terms-modal .modal-footer-custom .btn-outline) {
  border: 1px solid #ded8d2;
  background: #fff !important;
  color: #55483f !important;
}

.super-admin-shell :deep(.terms-modal .modal-footer-custom .btn-outline:hover:not(:disabled)) {
  background: #f8f5f2 !important;
}

.super-admin-shell :deep(.terms-modal .modal-footer-custom .btn-primary) {
  border: 1px solid #df4e16;
  background: #e85b22;
  color: #fff;
  box-shadow: 0 3px 8px rgba(232, 91, 34, 0.17);
}

.super-admin-shell :deep(.terms-modal .modal-footer-custom .btn-primary:hover:not(:disabled)) {
  background: #cf4b18;
}

.super-admin-sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 400;
  display: flex;
  width: var(--super-admin-sidebar-width);
  min-height: 100vh;
  overflow-x: hidden;
  overflow-y: auto;
  padding: 1.5rem 1rem 1rem;
  box-sizing: border-box;
  flex-direction: column;
  background: #f3e9e1;
  border-right: 1px solid #d8c8bc;
  transition: transform 220ms ease, opacity 220ms ease;
  scrollbar-width: none;
}

.super-admin-sidebar::-webkit-scrollbar,
.super-admin-sidebar__nav::-webkit-scrollbar,
.super-admin-sidebar__footer::-webkit-scrollbar {
  display: none;
}

.super-admin-sidebar__nav,
.super-admin-sidebar__footer {
  scrollbar-width: none;
}

.super-admin-sidebar__resize-handle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 30;
  width: 10px;
  padding: 0;
  border: 0;
  background: transparent;
  cursor: col-resize;
  touch-action: none;
}

.super-admin-sidebar__resize-handle::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 4px;
  width: 2px;
  height: 42px;
  border-radius: 999px;
  background: rgba(143, 79, 47, 0.38);
  transform: translateY(-50%);
  transition: height 160ms ease, background-color 160ms ease;
}

.super-admin-sidebar__resize-handle:hover::after,
.super-admin-sidebar__resize-handle:focus-visible::after {
  height: 64px;
  background: #8f4f2f;
}

.super-admin-sidebar-resizing .super-admin-sidebar,
.super-admin-sidebar-resizing .super-admin-main-panel,
.super-admin-sidebar-resizing .super-admin-topbar {
  transition: none !important;
}

.super-admin-sidebar__nav,
.super-admin-sidebar__footer {
  display: flex;
  width: 100%;
  min-width: 0;
  overflow-x: hidden;
  flex-direction: column;
  gap: 0.75rem;
}

.super-admin-sidebar__nav {
  overflow-y: auto;
}

.super-admin-sidebar__footer {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #d8c8bc;
}

.super-admin-sidebar__item,
.super-admin-sidebar__account,
.super-admin-sidebar__action,
.super-admin-sidebar__logout {
  position: relative;
  width: 100%;
  box-sizing: border-box;
  min-width: 0;
  min-height: 40px;
  flex: 0 0 auto;
  padding: 0.65rem 0.55rem;
  border-radius: 10px;
  font-size: 0.76rem;
  font-weight: 700;
  line-height: 1.25;
  white-space: normal;
  overflow-wrap: anywhere;
  cursor: pointer;
}

.super-admin-sidebar__item {
  border: 1px solid transparent;
  background: transparent;
  color: #3d2a1f;
  text-align: left;
}

.super-admin-sidebar__item:hover,
.super-admin-sidebar__item.active {
  background: rgba(255, 255, 255, 0.85);
  border-color: rgba(148, 163, 184, 0.3);
  box-shadow: 0 8px 18px rgba(66, 33, 11, 0.08);
}

.super-admin-sidebar__account {
  border: 1px solid rgba(59, 130, 246, 0.25);
  background: rgba(59, 130, 246, 0.12);
  color: #30445a;
}

.super-admin-sidebar__action {
  border: 0;
  background: #64748b;
  color: #fff;
}

.super-admin-sidebar__action--primary {
  background: #ff5c1a;
}

.super-admin-sidebar__logout {
  border: 1px solid rgba(138, 113, 95, 0.25);
  background: rgba(255, 159, 67, 0.12);
  color: #a23d32;
}

.super-admin-main-panel {
  min-height: 100vh;
  margin-left: var(--super-admin-sidebar-width);
}

.super-admin-topbar {
  position: fixed;
  top: 0;
  right: 0;
  left: var(--super-admin-sidebar-width);
  z-index: 300;
  display: flex;
  min-height: 66px;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  box-sizing: border-box;
  background: #eee2d9;
  border-bottom: 1px solid #d8c8bc;
}

.super-admin-hamburger {
  display: grid;
  width: 34px;
  height: 34px;
  padding: 0;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.7);
  color: #334155;
  cursor: pointer;
}

.super-admin-topbar__spacer { flex: 1; }

.super-admin-branch-filter {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #5b4638;
  font-size: 0.78rem;
  font-weight: 700;
}

.super-admin-branch-filter select {
  width: min(220px, 30vw);
  min-width: 150px;
  height: 36px;
  padding: 0 0.6rem;
  border: 1px solid #d8c8bc;
  border-radius: 8px;
  background: #fffaf6;
  color: #3d2a1f;
  font: inherit;
}

.super-admin-user-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.45rem 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.58);
  color: #1f2937;
  font-size: 0.82rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: background-color 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
}

.super-admin-user-pill:hover,
.super-admin-user-pill[aria-expanded="true"] {
  border-color: #ddcbbd;
  background: #fff;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.08);
}

.super-admin-user-pill:focus-visible {
  outline: 3px solid rgba(232, 91, 34, 0.3);
  outline-offset: 2px;
}

.super-admin-account-menu {
  position: relative;
  z-index: 500;
}

.super-admin-user-pill__chevron {
  margin-left: 0.15rem;
  color: #857467;
  font-size: 1rem;
  line-height: 1;
  transition: transform 160ms ease;
}

.super-admin-user-pill__chevron.is-open {
  transform: rotate(180deg);
}

.super-admin-account-dropdown {
  position: absolute;
  top: calc(100% + 0.65rem);
  right: 0;
  z-index: 510;
  width: min(280px, calc(100vw - 2rem));
  padding: 0.45rem;
  border: 1px solid #e8e0d9;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 16px 38px rgba(48, 35, 25, 0.17);
  animation: super-admin-menu-enter 140ms ease-out;
}

.super-admin-account-dropdown__heading {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 0.75rem 0.8rem 0.85rem;
}

.super-admin-account-dropdown__name {
  overflow: hidden;
  color: #3d2a1f;
  font-size: 0.88rem;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.super-admin-account-dropdown__role {
  color: #85776d;
  font-size: 0.74rem;
}

.super-admin-account-dropdown__item {
  display: flex;
  width: 100%;
  min-height: 42px;
  align-items: center;
  gap: 0.7rem;
  padding: 0.6rem 0.75rem;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: #493b31;
  font: inherit;
  font-size: 0.82rem;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: background-color 140ms ease, color 140ms ease;
}

.super-admin-account-dropdown__item:hover,
.super-admin-account-dropdown__item:focus-visible {
  outline: none;
  background: #f8f3ef;
  color: #a9431b;
}

.super-admin-account-dropdown__item-icon {
  display: grid;
  width: 28px;
  height: 28px;
  flex: 0 0 auto;
  place-items: center;
  border-radius: 8px;
  background: #fff3eb;
  color: #d95a26;
  font-size: 0.95rem;
}

.super-admin-account-dropdown__divider {
  height: 1px;
  margin: 0.4rem 0.35rem;
  background: #eee8e2;
}

.super-admin-account-dropdown__item--logout {
  color: #b42318;
}

.super-admin-account-dropdown__item--logout .super-admin-account-dropdown__item-icon {
  background: #fff0ef;
  color: #c43227;
}

.super-admin-account-dropdown__item--logout:hover,
.super-admin-account-dropdown__item--logout:focus-visible {
  background: #fff3f2;
  color: #a51d14;
}

@keyframes super-admin-menu-enter {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.super-admin-user-pill__avatar {
  display: grid;
  width: 26px;
  height: 26px;
  place-items: center;
  border-radius: 50%;
  background: #f7b97a;
  color: #1f2937;
  font-size: 0.72rem;
  font-weight: 800;
}

.super-admin-main-panel > .admin-page {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
  min-height: 100vh;
  padding-top: 66px;
  box-sizing: border-box;
}

.super-admin-module-view {
  position: relative;
  width: 100%;
  min-width: 0;
  min-height: calc(100vh - 66px);
  padding: 1.25rem;
  box-sizing: border-box;
  overflow-x: hidden;
  background: #f1e5dc;
}

.super-admin-monitoring-toolbar {
  display: flex;
  min-height: 66px;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin: 0 0 0.9rem;
  padding: 0.8rem 1rem;
  border: 1px solid #e5d7cc;
  border-radius: 14px;
  background: #fffdfb;
  box-shadow: 0 5px 16px rgba(66, 33, 11, 0.06);
}

.super-admin-monitoring-toolbar__copy {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 0.2rem;
}

.super-admin-monitoring-toolbar__copy strong {
  color: #2c211c;
  font-size: 0.84rem;
  letter-spacing: 0.01em;
}

.super-admin-monitoring-toolbar__copy span {
  color: #846f63;
  font-size: 0.75rem;
}

.super-admin-monitoring-branch-selector {
  display: inline-flex;
  min-height: 42px;
  flex: 0 0 auto;
  align-items: center;
  gap: 0.5rem;
  margin: 0;
  padding: 0.3rem 0.45rem 0.3rem 0.8rem;
  border: 1px solid #e6d8cd;
  border-radius: 11px;
  background: #fffaf6;
  color: #4d3c35;
  box-shadow: 0 3px 10px rgba(66, 33, 11, 0.045);
  font-size: 0.76rem;
  font-weight: 700;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
}

.super-admin-monitoring-branch-selector span {
  flex: 0 0 auto;
}

.super-admin-monitoring-branch-selector select {
  width: min(230px, 28vw);
  min-width: 165px;
  height: 36px;
  padding: 0 2rem 0 0.7rem;
  border: 1px solid #ddcbbd;
  border-radius: 9px;
  background: #fff;
  color: #2a211d;
  font: inherit;
  font-size: 0.78rem;
  cursor: pointer;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
}

.super-admin-monitoring-branch-selector select:hover:not(:disabled) {
  border-color: #c89574;
  background: #fffdfb;
}

.super-admin-monitoring-branch-selector select:focus {
  border-color: #d8662b;
  outline: none;
  box-shadow: 0 0 0 3px rgba(216, 102, 43, 0.14);
}

.super-admin-monitoring-branch-selector--loading {
  border-color: #edcdb8;
  background: #fffaf6;
}

.super-admin-monitoring-branch-selector select:disabled {
  cursor: wait;
  color: #806f64;
  opacity: 0.85;
}

.super-admin-module-view :deep(.manager-logistics-sidebar-nav) {
  padding-right: 0.65rem !important;
}

.super-admin-module-view :deep(.manager-hr-sidebar__nav),
.super-admin-module-view :deep(.finance-sidebar__nav),
.super-admin-module-view :deep(.cashier-right-sidebar__nav) {
  padding-right: 0.65rem !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .branch-selector-section) {
  display: none !important;
}

@media (max-width: 767px) {
  .super-admin-monitoring-branch-selector {
    width: 100%;
    justify-content: space-between;
  }

  .super-admin-monitoring-toolbar {
    align-items: stretch;
    flex-direction: column;
    gap: 0.7rem;
    margin-bottom: 0.75rem;
    padding: 0.8rem;
  }

  .super-admin-monitoring-branch-selector select {
    width: min(230px, 58vw);
    min-width: 0;
  }

  .super-admin-module-view :deep(.manager-logistics-sidebar-nav) {
    padding-right: 0.65rem !important;
  }

  .super-admin-module-view :deep(.manager-hr-sidebar__nav),
  .super-admin-module-view :deep(.finance-sidebar__nav),
  .super-admin-module-view :deep(.cashier-right-sidebar__nav) {
    padding-right: 0.65rem !important;
  }
}

.super-admin-module-view:has(.admin-panel-shell) {
  height: calc(100dvh - 66px);
  min-height: 0;
  overflow-y: auto;
  overscroll-behavior-y: contain;
}

.super-admin-monitoring-notice {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin: 0 0 0.85rem;
  padding: 0.75rem 1rem;
  border: 1px solid #b8dfc8;
  border-radius: 12px;
  background: #f0faf3;
  color: #315b40;
  font-size: 0.82rem;
  line-height: 1.45;
}

.super-admin-monitoring-notice strong {
  flex: 0 0 auto;
  color: #17633a;
}

.super-admin-module-view :deep(.owner-dish-section) {
  margin-top: 1rem !important;
}

.super-admin-module-view :deep([data-super-admin-monitoring-restricted="true"]) {
  display: none !important;
}

.super-admin-module-view :deep(.owner-panel-topbar),
.super-admin-module-view :deep(.admin-topbar),
.super-admin-module-view :deep(.logistics-topbar),
.super-admin-module-view :deep(.finance-main-header),
.super-admin-module-view :deep(.manager-hr-compact-profile),
.super-admin-module-view :deep(.panel-sidebar-brand),
.super-admin-module-view :deep(.owner-sidebar-footer),
.super-admin-module-view :deep(.finance-sidebar__footer),
.super-admin-module-view :deep(.manager-hr-sidebar__footer),
.super-admin-module-view :deep(.logistics-sidebar__footer),
.super-admin-module-view :deep(.cashier-right-sidebar__toggle),
.super-admin-module-view :deep(.cashier-right-sidebar__reopen),
.super-admin-module-view :deep(.admin-sidebar__footer),
.super-admin-module-view :deep(.admin-sidebar__resize-handle),
.super-admin-module-view :deep(.admin-panel-shell .panel-sidebar-resize-handle),
.super-admin-module-view :deep(.owner-panel-sidebar__resize-handle),
.super-admin-module-view :deep(.back-to-dashboard-btn),
.super-admin-module-view :deep(.back-to-superadmin-btn),
.super-admin-module-view :deep(.back-to-super-admin-btn) {
  display: none !important;
}

.super-admin-module-view :deep(.admin-layout--owner-sidebar-layout),
.super-admin-module-view :deep(.main-branch-admin-panel .admin-layout--owner-sidebar-layout),
.super-admin-module-view :deep(.main-branch-admin-panel .admin-layout--owner-sidebar-layout.no-profile-column.admin-layout--wider) {
  display: grid !important;
  grid-template-columns: minmax(0, 1fr) !important;
  grid-template-rows: auto minmax(0, 1fr) !important;
  align-items: start;
  min-height: 0 !important;
  padding: 0 !important;
}

.super-admin-module-view :deep(.owner-panel-layout--embedded .owner-panel-sidebar) {
  position: static !important;
  inset: auto !important;
  z-index: auto !important;
  display: contents !important;
  width: auto !important;
  min-width: 0 !important;
  min-height: 0 !important;
  height: auto !important;
  max-height: none !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  transform: none !important;
  opacity: 1 !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.owner-panel-layout--embedded .admin-main) {
  grid-column: 1 !important;
  grid-row: 2 !important;
  width: 100% !important;
  min-width: 0 !important;
  margin: 0 !important;
  padding: 1rem !important;
}

.super-admin-module-view :deep(.main-branch-admin-panel .admin-layout--owner-sidebar-layout .admin-main) {
  grid-column: 1 !important;
  grid-row: 2 !important;
  width: 100% !important;
  min-width: 0 !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.owner-sidebar-nav),
.super-admin-module-view :deep(.main-branch-hr-sidebar-nav),
.super-admin-module-view :deep(.manager-logistics-sidebar-nav),
.super-admin-module-view :deep(.manager-procurement-sidebar-nav),
.super-admin-module-view :deep(.inventory-sidebar-nav) {
  position: static !important;
  grid-column: 1 / -1 !important;
  grid-row: 1 !important;
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  flex-direction: row !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 0.5rem !important;
  margin: 0 0 0.75rem !important;
  padding: 0.65rem !important;
  border: 1px solid #eadfd6 !important;
  border-radius: 12px !important;
  background: #fff !important;
  box-shadow: 0 3px 12px rgba(66, 33, 11, 0.045) !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.owner-sidebar-nav > button),
.super-admin-module-view :deep(.main-branch-hr-sidebar-link),
.super-admin-module-view :deep(.manager-logistics-sidebar-link),
.super-admin-module-view :deep(.manager-procurement-sidebar-link),
.super-admin-module-view :deep(.inventory-sidebar-nav .owner-sidebar-link) {
  display: inline-flex !important;
  width: auto !important;
  min-width: 0 !important;
  min-height: 38px !important;
  flex: 0 0 auto !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 0.55rem 0.8rem !important;
  border: 1px solid transparent !important;
  border-radius: 9px !important;
  text-align: center !important;
  white-space: normal !important;
}

.super-admin-module-view :deep(.owner-sidebar-nav > button:hover),
.super-admin-module-view :deep(.main-branch-hr-sidebar-link:hover),
.super-admin-module-view :deep(.manager-logistics-sidebar-link:hover),
.super-admin-module-view :deep(.manager-procurement-sidebar-link:hover),
.super-admin-module-view :deep(.inventory-sidebar-nav .owner-sidebar-link:hover) {
  transform: none !important;
}

.super-admin-module-view :deep(.main-branch-page) {
  display: grid !important;
  grid-template-columns: minmax(0, 1fr) !important;
  grid-template-rows: auto minmax(0, 1fr) !important;
  gap: 0 !important;
  min-height: 0 !important;
  padding: 0 !important;
}

.super-admin-module-view :deep(.main-branch-page .logistics-sidebar) {
  position: static !important;
  inset: auto !important;
  z-index: auto !important;
  display: contents !important;
  grid-column: auto !important;
  grid-row: auto !important;
  width: auto !important;
  min-height: 0 !important;
  height: auto !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  transform: none !important;
  opacity: 1 !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.logistics-sidebar__nav),
.super-admin-module-view :deep(.finance-sidebar__nav),
.super-admin-module-view :deep(.manager-hr-sidebar__nav),
.super-admin-module-view :deep(.cashier-right-sidebar__nav) {
  position: static !important;
  grid-column: 1 / -1 !important;
  grid-row: 1 !important;
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  flex-direction: row !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  justify-content: flex-start !important;
  gap: 0.5rem !important;
  margin: 0 0 0.75rem !important;
  padding: 0.65rem !important;
  border: 1px solid #eadfd6 !important;
  border-radius: 12px !important;
  background: #fff !important;
  box-shadow: 0 3px 12px rgba(66, 33, 11, 0.045) !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.logistics-sidebar__item),
.super-admin-module-view :deep(.finance-sidebar__item),
.super-admin-module-view :deep(.manager-hr-sidebar__item),
.super-admin-module-view :deep(.cashier-right-sidebar__item) {
  display: inline-flex !important;
  width: auto !important;
  min-width: 0 !important;
  min-height: 38px !important;
  flex: 0 0 auto !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 0.55rem 0.8rem !important;
  border: 1px solid transparent !important;
  border-radius: 9px !important;
  text-align: center !important;
  white-space: normal !important;
}

.super-admin-module-view :deep(.logistics-sidebar__item:hover),
.super-admin-module-view :deep(.finance-sidebar__item:hover),
.super-admin-module-view :deep(.manager-hr-sidebar__item:hover),
.super-admin-module-view :deep(.cashier-right-sidebar__item:hover) {
  transform: none !important;
}

.super-admin-module-view :deep(.main-branch-page .logistics-workspace) {
  grid-column: 1 !important;
  grid-row: 2 !important;
  width: 100% !important;
  margin-left: 0 !important;
  padding-top: 0 !important;
}

/* Keep the Main Branch logistics view compact when embedded in Super Admin.
   These overrides are scoped to this module so the standalone logistics panel
   keeps its existing spacing. */
.super-admin-module-view :deep(.main-branch-page .panel-layout) {
  grid-template-columns: minmax(0, 1fr) !important;
  gap: 0 !important;
  padding: 0.5rem 0.875rem 1.25rem !important;
}

.super-admin-module-view :deep(.main-branch-page .logistics-sidebar__nav) {
  margin-bottom: 0.5rem !important;
}

.super-admin-module-view :deep(.main-branch-page .main-col) {
  gap: 0.75rem !important;
}

.super-admin-module-view :deep(.main-branch-page .logistics-feature-header) {
  margin-bottom: 0 !important;
  padding: 0.875rem 1rem !important;
}

.super-admin-module-view :deep(.main-branch-page .overview-grid) {
  gap: 0.75rem !important;
}

.super-admin-module-view :deep(.main-branch-page .overview-card) {
  padding: 0.875rem !important;
}

.super-admin-module-view :deep(.main-branch-page .main-col > .panel-section),
.super-admin-module-view :deep(.main-branch-page .logistics-section-view > .panel-section) {
  padding: 0.875rem !important;
}

.super-admin-module-view :deep(.main-branch-page .logistics-section-view > .panel-section + .panel-section) {
  margin-top: 0.75rem !important;
}

.super-admin-module-view :deep(.main-branch-page .main-col .section-title) {
  min-height: 36px;
  margin-bottom: 0.375rem;
  padding: 0.45rem 0.7rem;
}

.super-admin-module-view :deep(.main-branch-page .main-col .section-description) {
  margin-bottom: 0.625rem;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-layout) {
  display: grid !important;
  grid-template-columns: minmax(0, 1fr) !important;
  grid-template-rows: auto auto !important;
  width: 100% !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  overflow: visible !important;
  padding: 0 !important;
}

.super-admin-module-view :deep(.admin-panel-shell),
.super-admin-module-view :deep(.admin-panel-shell .admin-page) {
  width: 100% !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar) {
  position: static !important;
  inset: auto !important;
  z-index: auto !important;
  display: contents !important;
  grid-column: auto !important;
  grid-row: auto !important;
  width: auto !important;
  height: auto !important;
  min-height: 0 !important;
  max-height: none !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  transform: none !important;
  opacity: 1 !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__nav) {
  position: static !important;
  grid-column: 1 / -1 !important;
  grid-row: 1 !important;
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  flex-direction: row !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 0.5rem !important;
  margin: 0 0 0.75rem !important;
  padding: 0.65rem !important;
  border: 1px solid #eadfd6 !important;
  border-radius: 12px !important;
  background: #fff !important;
  box-shadow: 0 3px 12px rgba(66, 33, 11, 0.045) !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__item) {
  display: inline-flex !important;
  width: auto !important;
  min-width: 0 !important;
  min-height: 38px !important;
  flex: 0 0 auto !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 0.55rem 0.8rem !important;
  border: 1px solid transparent !important;
  border-radius: 9px !important;
  text-align: center !important;
  white-space: normal !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter) {
  display: inline-flex !important;
  align-items: center !important;
  gap: 0.5rem !important;
  margin-left: auto !important;
  min-height: 42px !important;
  margin-right: 0 !important;
  padding: 0.3rem 0.45rem 0.3rem 0.8rem !important;
  border: 1px solid #e6d8cd !important;
  border-radius: 11px !important;
  background: #fffaf6 !important;
  color: #4d3c35 !important;
  font-size: 0.76rem !important;
  font-weight: 700 !important;
  box-shadow: 0 3px 10px rgba(66, 33, 11, 0.045) !important;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter span) {
  flex: 0 0 auto !important;
  color: #4d3c35 !important;
  font-weight: 700 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter select) {
  width: min(230px, 28vw) !important;
  min-width: 165px !important;
  height: 36px !important;
  padding: 0 2rem 0 0.7rem !important;
  border: 1px solid #ddcbbd !important;
  border-radius: 9px !important;
  background: #fff !important;
  color: #2a211d !important;
  font: inherit !important;
  font-size: 0.78rem !important;
  cursor: pointer !important;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter select:hover:not(:disabled)) {
  border-color: #c89574 !important;
  background: #fffdfb !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter select:focus) {
  border-color: #d8662b !important;
  outline: none !important;
  box-shadow: 0 0 0 3px rgba(216, 102, 43, 0.14) !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter--loading) {
  border-color: #edcdb8 !important;
  background: #fffaf6 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter select:disabled) {
  cursor: wait !important;
  color: #806f64 !important;
  opacity: 0.85 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-main .range-tabs) {
  display: flex !important;
  width: 100% !important;
  max-width: 100% !important;
  min-height: 44px !important;
  align-items: center !important;
  justify-content: flex-start !important;
  gap: 0.35rem !important;
  margin: 0 0 1rem !important;
  padding: 0.25rem !important;
  overflow: visible !important;
  box-sizing: border-box !important;
  flex-wrap: wrap !important;
  border-radius: 12px !important;
  background: #eaded4 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-main .range-tabs .range-tab) {
  display: inline-flex !important;
  min-width: 0 !important;
  min-height: 36px !important;
  flex: 0 0 auto !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 0.5rem 0.9rem !important;
  border: 1px solid transparent !important;
  border-radius: 9px !important;
  background: transparent !important;
  color: #3d2a1f !important;
  font-size: 0.78rem !important;
  font-weight: 650 !important;
  white-space: nowrap !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-main .range-tabs .range-tab:hover) {
  border-color: #e4b99f !important;
  background: #fff8f2 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-main .range-tabs .range-tab.range-tab--active) {
  border-color: #ff6a24 !important;
  background: #ff6a24 !important;
  color: #fff !important;
  box-shadow: 0 2px 7px rgba(219, 89, 25, 0.2) !important;
}

@media (max-width: 767px) {
  .super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__nav) {
    align-items: stretch !important;
  }

  .super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter) {
    width: 100% !important;
    margin-left: 0 !important;
    justify-content: space-between !important;
  }

  .super-admin-module-view :deep(.admin-panel-shell .admin-sidebar__branch-filter select) {
    width: min(230px, 58vw) !important;
    min-width: 0 !important;
  }

  .super-admin-module-view :deep(.admin-panel-shell .admin-main .range-tabs) {
    align-items: stretch !important;
    gap: 0.25rem !important;
  }

  .super-admin-module-view :deep(.admin-panel-shell .admin-main .range-tabs .range-tab) {
    flex: 1 1 auto !important;
    padding-inline: 0.55rem !important;
    font-size: 0.72rem !important;
  }
}

.super-admin-module-view :deep(.admin-panel-shell .admin-main) {
  grid-column: 1 !important;
  grid-row: 2 !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 1rem !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-side) {
  grid-column: 1 !important;
  grid-row: 3 !important;
  display: block !important;
  width: 100% !important;
  min-height: 0 !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-grid) {
  gap: 1rem !important;
  margin-top: 1rem !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-grid > .panel-block) {
  margin: 0 !important;
  padding: 1rem 1.1rem !important;
  border: 1px solid #e8ded6 !important;
  border-radius: 16px !important;
  background: #fff !important;
  box-shadow: 0 8px 22px rgba(66, 33, 11, 0.06) !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-grid > .panel-block > .panel-header) {
  align-items: center !important;
  justify-content: space-between !important;
  gap: 0.75rem !important;
  margin: 0 !important;
  padding: 0.1rem 0 0.85rem !important;
  border: 0 !important;
  border-bottom: 1px solid #eee6e0 !important;
  border-radius: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-grid .panel-header h2) {
  margin: 0 !important;
  color: #263445 !important;
  font-size: 1rem !important;
  font-weight: 750 !important;
  letter-spacing: -0.02em !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-caption) {
  flex: 0 0 auto;
  padding: 0.3rem 0.55rem;
  border: 1px solid #eee5dd;
  border-radius: 999px;
  background: #faf7f4;
  color: #76685e;
  font-size: 0.72rem;
  font-weight: 650;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-grid .panel-body) {
  padding: 0.85rem 0 0.1rem !important;
  border: 0 !important;
  border-radius: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-summary) {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 0.45rem 0.55rem !important;
  padding: 0 0 0.85rem !important;
  color: #6d625b !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-summary > span) {
  padding: 0.35rem 0.6rem;
  border: 1px solid #eee7e1;
  border-radius: 8px;
  background: #faf8f6;
  font-size: 0.75rem;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-summary strong) {
  color: #263445 !important;
  font-weight: 750 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table-wrap) {
  max-width: 100% !important;
  overflow-x: auto !important;
  border: 1px solid #eee7e1;
  border-radius: 10px;
  background: #fff;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table) {
  width: 100% !important;
  min-width: 760px !important;
  border-collapse: separate !important;
  border-spacing: 0 !important;
  font-size: 0.78rem !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table th) {
  padding: 0.7rem 0.75rem !important;
  border-bottom: 1px solid #e8ded6 !important;
  background: #faf8f6 !important;
  color: #776b63 !important;
  font-size: 0.68rem !important;
  font-weight: 750 !important;
  letter-spacing: 0.045em !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table td) {
  padding: 0.75rem !important;
  border-bottom: 1px solid #f0ebe7 !important;
  color: #51463e !important;
  line-height: 1.45 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table tbody tr:last-child td) {
  border-bottom: 0 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table tbody tr:hover) {
  background: #fcfaf8 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table td strong) {
  color: #263445 !important;
  font-weight: 700 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .dashboard-detail-table td small) {
  color: #81756d !important;
  line-height: 1.4 !important;
}

.super-admin-module-view :deep(.main-branch-finance-panel .manager-finance.finance-manager-layout) {
  position: relative !important;
  inset: auto !important;
  display: grid !important;
  grid-template-columns: minmax(0, 1fr) !important;
  grid-template-rows: auto auto !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.main-branch-finance-panel),
.super-admin-module-view :deep(.main-branch-finance-panel .content-area) {
  position: relative !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  overflow: visible !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.manager-finance .finance-sidebar) {
  position: static !important;
  inset: auto !important;
  z-index: auto !important;
  grid-column: 1 !important;
  grid-row: 1 !important;
  display: block !important;
  width: 100% !important;
  min-width: 0 !important;
  min-height: 0 !important;
  height: auto !important;
  max-height: none !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  transform: none !important;
  opacity: 1 !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.manager-finance.finance-manager-layout:not(.main-branch-finance)) {
  position: relative !important;
  inset: auto !important;
  display: grid !important;
  grid-template-columns: minmax(0, 1fr) !important;
  grid-template-rows: auto auto !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.manager-finance.finance-manager-layout:not(.main-branch-finance) .finance-main-panel) {
  display: block !important;
  grid-column: 1 !important;
  grid-row: 2 !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  background: transparent !important;
}

.super-admin-module-view :deep(.manager-finance.finance-manager-layout:not(.main-branch-finance) .finance-dashboard-body) {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0.25rem 0 1rem !important;
  overflow: visible !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.manager-finance.finance-manager-layout:not(.main-branch-finance) .finance-section-view) {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  gap: 1rem !important;
}

.super-admin-module-view :deep(.manager-finance.finance-manager-layout:not(.main-branch-finance) .finance-section-content) {
  border-color: #e8ded6 !important;
  border-radius: 16px !important;
  background: rgba(255, 255, 255, 0.82) !important;
  box-shadow: 0 8px 22px rgba(66, 33, 11, 0.05) !important;
}

.super-admin-module-view :deep(.main-branch-finance-panel .manager-finance .finance-main-panel) {
  display: block !important;
  grid-column: 1 !important;
  grid-row: 2 !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  background: transparent !important;
}

.super-admin-module-view :deep(.main-branch-finance-panel .manager-finance .finance-dashboard-body) {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0.25rem 0 1rem !important;
  overflow: visible !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.main-branch-finance-panel .manager-finance .finance-section-view) {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  gap: 1rem !important;
}

.super-admin-module-view :deep(.main-branch-finance-panel .manager-finance .finance-section-content) {
  border-color: #e8ded6 !important;
  border-radius: 16px !important;
  background: rgba(255, 255, 255, 0.82) !important;
  box-shadow: 0 8px 22px rgba(66, 33, 11, 0.05) !important;
}

.super-admin-module-view :deep(.manager-hr-page) {
  position: relative !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 0.75rem !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  box-sizing: border-box !important;
  transition: none !important;
}

.super-admin-module-view :deep(.manager-hr-sidebar) {
  position: static !important;
  inset: auto !important;
  z-index: auto !important;
  display: block !important;
  width: 100% !important;
  min-width: 0 !important;
  min-height: 0 !important;
  height: auto !important;
  max-height: none !important;
  margin: 0 !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  transform: none !important;
  opacity: 1 !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.manager-hr-sidebar__nav) {
  justify-content: flex-start !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.manager-hr-sidebar__item) {
  min-height: 38px !important;
  padding: 0.55rem 0.85rem !important;
  border: 1px solid transparent !important;
  border-radius: 9px !important;
  font-weight: 650 !important;
  transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease !important;
}

.super-admin-module-view :deep(.manager-hr-sidebar__item--active) {
  border-color: #f2d8c7 !important;
  background: #fff6ef !important;
  color: #9a3412 !important;
  box-shadow: none !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main) {
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  flex-direction: column !important;
  gap: 0.9rem !important;
  margin: 0 !important;
  padding: 0.75rem 1rem 1.25rem !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .admin-main-header) {
  margin: 0 0 0.15rem !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .procurement-overview-card) {
  width: 100% !important;
  min-width: 0 !important;
  gap: 0.75rem !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .procurement-overview-card .hr-stat-card) {
  min-width: 0 !important;
  margin: 0 !important;
  padding: 0.9rem 1rem !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .manual-procurement),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .requested-products),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .budget-requests),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .requests-history),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .supplier-products) {
  width: 100% !important;
  min-width: 0 !important;
  margin: 0 !important;
  padding: 1rem !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .theme-toggle-btn) {
  display: none !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .admin-main) {
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  flex-direction: column !important;
  gap: 0.9rem !important;
  margin: 0 !important;
  padding: 0.75rem 1rem 1.25rem !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .admin-main > .admin-main-header) {
  margin: 0 0 0.15rem !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view) {
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  flex-direction: column !important;
  gap: 0.9rem !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view > .hr-stats-grid) {
  width: 100% !important;
  min-width: 0 !important;
  gap: 0.75rem !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view > .hr-stats-grid .hr-stat-card) {
  min-width: 0 !important;
  margin: 0 !important;
  padding: 0.9rem 1rem !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view > .panel-section) {
  width: 100% !important;
  min-width: 0 !important;
  margin: 0 !important;
  padding: 1rem !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view > .panel-section .section-title) {
  margin-top: 0 !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view > .panel-section .section-description) {
  margin-bottom: 0.75rem !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .logistics-three-panel) {
  gap: 0.75rem !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .logistics-panel-card) {
  min-width: 0 !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.manager-logistics-panel .table-container) {
  max-width: 100% !important;
  margin: 0 !important;
}

@media (max-width: 767px) {
  .super-admin-module-view :deep(.manager-logistics-panel .admin-main) {
    gap: 0.75rem !important;
    padding: 0.5rem 0.75rem 1rem !important;
  }

  .super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view) {
    gap: 0.75rem !important;
  }

  .super-admin-module-view :deep(.manager-logistics-panel .manager-logistics-section-view > .panel-section) {
    padding: 0.875rem !important;
  }
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .manual-procurement h2),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .requested-products h2),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .budget-requests h2),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .requests-history h2),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .supplier-products h2) {
  margin-top: 0 !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .manual-procurement > .mb-1),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .budget-requests > .mb-1) {
  margin-bottom: 0 !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .requested-products .section-description),
.super-admin-module-view :deep(.manager-procurement-panel .admin-main > .manual-procurement .section-description) {
  margin-bottom: 0.75rem !important;
}

@media (max-width: 767px) {
  .super-admin-module-view :deep(.manager-procurement-panel .admin-main) {
    gap: 0.75rem !important;
    padding: 0.5rem 0.75rem 1rem !important;
  }

  .super-admin-module-view :deep(.manager-procurement-panel .admin-main > .manual-procurement),
  .super-admin-module-view :deep(.manager-procurement-panel .admin-main > .requested-products),
  .super-admin-module-view :deep(.manager-procurement-panel .admin-main > .budget-requests),
  .super-admin-module-view :deep(.manager-procurement-panel .admin-main > .requests-history),
  .super-admin-module-view :deep(.manager-procurement-panel .admin-main > .supplier-products) {
    padding: 0.875rem !important;
  }
}

.super-admin-module-view :deep(.manager-hr-section-view) {
  display: flex !important;
  flex-direction: column !important;
  gap: 0.75rem !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  margin: 0 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .owner-panel-layout--embedded),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-page),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-layout) {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  min-height: 0 !important;
  height: auto !important;
  margin: 0 !important;
  overflow: visible !important;
  box-sizing: border-box !important;
  background: transparent !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-layout) {
  display: block !important;
  padding: 0 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-main) {
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  min-height: 0 !important;
  flex-direction: column !important;
  gap: 1rem !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-page-header) {
  display: flex !important;
  min-height: 104px !important;
  align-items: flex-start !important;
  justify-content: space-between !important;
  gap: 1rem !important;
  margin: 0 !important;
  padding: 1.25rem 1.5rem !important;
  border: 1px solid #e8ded6 !important;
  border-top: 3px solid #e85b22 !important;
  border-radius: 16px !important;
  background: linear-gradient(110deg, #fff 0%, #fffaf5 100%) !important;
  box-shadow: 0 8px 22px rgba(66, 33, 11, 0.06) !important;
  text-align: left !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-page-header__copy) {
  width: 100% !important;
  text-align: left !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-page-header__copy h1) {
  margin: 0 !important;
  color: #263445 !important;
  font-size: clamp(1.45rem, 2vw, 1.9rem) !important;
  font-weight: 800 !important;
  letter-spacing: -0.035em !important;
  line-height: 1.15 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-page-header__copy p) {
  margin: 0.4rem 0 0 !important;
  color: #76685e !important;
  font-size: 0.88rem !important;
  line-height: 1.5 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-main > .panel-section) {
  display: flex !important;
  min-width: 0 !important;
  flex-direction: column !important;
  gap: 1rem !important;
  margin: 0 !important;
  padding: 1.25rem !important;
  border: 1px solid #e8ded6 !important;
  border-radius: 16px !important;
  background: #fff !important;
  box-shadow: 0 8px 22px rgba(66, 33, 11, 0.055) !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .section-title) {
  margin: 0 !important;
  color: #263445 !important;
  font-size: 1.15rem !important;
  font-weight: 750 !important;
  letter-spacing: -0.025em !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .section-description) {
  margin: -0.65rem 0 0 !important;
  color: #76685e !important;
  font-size: 0.85rem !important;
  line-height: 1.5 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-directory-section),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-products) {
  min-width: 0 !important;
  margin: 0 !important;
  padding: 1rem !important;
  border: 1px solid #eee6e0 !important;
  border-radius: 12px !important;
  background: #fff !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-directory-section h3),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-products h3),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-main > .panel-section > .panel-section h3) {
  margin: 0 0 0.75rem !important;
  color: #344454 !important;
  font-size: 0.98rem !important;
  font-weight: 750 !important;
  line-height: 1.35 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-main > .panel-section > .panel-section) {
  min-width: 0 !important;
  margin: 0 !important;
  padding: 1rem !important;
  border: 1px solid #eee6e0 !important;
  border-radius: 12px !important;
  background: #fff !important;
  box-shadow: none !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .table-container),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .orders-table-wrapper),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .products-table-wrapper) {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  border: 1px solid #eee6e0;
  border-radius: 10px;
  background: #fff;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .table-container) {
  overflow: auto !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .data-table),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .products-table) {
  width: 100% !important;
  border-collapse: separate !important;
  border-spacing: 0 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .data-table) {
  min-width: 720px !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-directory-section .data-table) {
  min-width: 540px !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .products-table) {
  min-width: 440px !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .data-table thead th),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .products-table thead th) {
  padding: 0.75rem 0.85rem !important;
  border-bottom: 1px solid #eee6e0 !important;
  background: #faf7f4 !important;
  color: #76685e !important;
  font-size: 0.69rem !important;
  font-weight: 750 !important;
  letter-spacing: 0.045em !important;
  white-space: nowrap !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .data-table td),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .products-table td) {
  padding: 0.75rem 0.85rem !important;
  border-bottom: 1px solid #f0ebe7 !important;
  color: #51463e !important;
  font-size: 0.82rem !important;
  line-height: 1.45 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .data-table tbody tr:last-child td),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .products-table tbody tr:last-child td) {
  border-bottom: 0 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .data-table tbody tr:hover td),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .products-table tbody tr:hover td) {
  background: #fcfaf8 !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .empty-message) {
  display: flex !important;
  min-height: 68px !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 0.85rem 1rem !important;
  border: 1px dashed #e5d9cf !important;
  border-radius: 10px !important;
  background: #fcfaf8 !important;
  color: #81756d !important;
  font-size: 0.82rem !important;
  font-style: normal !important;
  text-align: center !important;
}

.super-admin-module-view :deep(.superadmin-logistics-wrapper .loading-container) {
  min-height: 120px !important;
  border: 1px solid #eee6e0;
  border-radius: 10px;
  background: #fcfaf8;
  color: #76685e;
}

.super-admin-module-view :deep(.cashier-page) {
  width: 100% !important;
  min-width: 0 !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 1rem !important;
}

.super-admin-module-view :deep(.cashier-right-sidebar) {
  position: static !important;
  inset: auto !important;
  z-index: auto !important;
  display: block !important;
  width: 100% !important;
  min-width: 0 !important;
  min-height: 0 !important;
  height: auto !important;
  max-height: none !important;
  margin: 0 0 0.75rem !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  transform: none !important;
  opacity: 1 !important;
  overflow: visible !important;
}

.super-admin-module-view :deep(.cashier-sidebar-collapsed .cashier-right-sidebar) {
  transform: none !important;
  opacity: 1 !important;
  pointer-events: auto !important;
}

.super-admin-module-view :deep(.cashier-sidebar-collapsed) {
  padding-right: 1rem !important;
}

.super-admin-module-view :deep(.cashier-body),
.super-admin-module-view :deep(.cashier-header),
.super-admin-module-view :deep(.cashier-page > .branch-filter) {
  width: 100% !important;
  max-width: 100% !important;
  margin-left: 0 !important;
  margin-right: 0 !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-content-scroll) {
  position: static !important;
  inset: auto !important;
  z-index: auto !important;
  display: block !important;
  grid-column: 1 / -1 !important;
  grid-row: 2 !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  transition: none !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-content-scroll > .admin-main),
.super-admin-module-view :deep(.admin-panel-shell .admin-content-scroll > .admin-left),
.super-admin-module-view :deep(.admin-panel-shell .admin-content-scroll > .admin-side) {
  position: static !important;
  grid-column: auto !important;
  grid-row: auto !important;
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  margin: 0 !important;
  box-sizing: border-box !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-content-scroll > .admin-main) {
  padding: 1rem !important;
}

.super-admin-module-view :deep(.admin-panel-shell .admin-content-scroll > .admin-side) {
  padding: 0 1rem 1rem !important;
}

.super-admin-module-view :deep(.manager-procurement-panel .admin-main-header-top > div:nth-child(2)) {
  transform: none !important;
}

.super-admin-module-view :deep(.main-branch-admin-panel .admin-main) {
  margin-top: 0 !important;
}

.super-admin-module-view :deep(.manager-finance .filter-bar .filter-group:first-child),
.super-admin-module-view :deep(.cashier-page .branch-filter),
.super-admin-module-view :deep(.superadmin-logistics-wrapper .branch-selector-section),
.super-admin-module-view :deep(.staff-management-page .filter-select),
.super-admin-module-view :deep(.admin-panel-shell #admin-attendance .panel-header select) {
  display: none !important;
}

.super-admin-module-view > :deep(*) {
  max-width: 100%;
  min-width: 0;
}

.super-admin-module-back {
  display: inline-flex;
  align-items: center;
  margin-bottom: 0.75rem;
  padding: 0.55rem 0.9rem;
  border: 1px solid #cbd5df;
  border-radius: 8px;
  color: #334155;
  background: #ffffff;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.super-admin-module-back:hover {
  background: #f8fafc;
}

.super-admin-main-panel :deep(.admin-layout) {
  display: block;
  width: 100%;
  max-width: none !important;
  min-height: calc(100vh - 66px);
  padding: 1rem;
  box-sizing: border-box;
}

.super-admin-main-panel :deep(.admin-layout > .admin-main),
.super-admin-main-panel :deep(.admin-layout > .admin-side),
.super-admin-main-panel :deep(.admin-layout > .admin-left),
.super-admin-main-panel :deep(.admin-layout > .page-header-top) {
  grid-column: auto !important;
  width: 100% !important;
  max-width: none !important;
  min-width: 0;
}

.super-admin-main-panel :deep(.admin-main) {
  width: 100%;
  max-width: none !important;
  min-width: 0;
  margin: 0;
  overflow-x: hidden !important;
}

.super-admin-main-panel .page-header-top {
  width: 100%;
  margin: 0 0 1rem;
  padding: 1rem 0;
  background: transparent;
  border: 0;
  box-shadow: none;
}

.super-admin-main-panel .page-header-top.super-admin-page-header {
  display: flex;
  min-height: 116px;
  align-items: center;
  margin-bottom: 1.25rem;
  padding: 1.5rem 1.75rem;
  border: 1px solid #e5d4c7;
  border-radius: 14px;
  background: linear-gradient(135deg, #fff 0%, #fffaf6 100%);
  box-shadow: 0 8px 24px rgba(66, 33, 11, 0.07);
  box-sizing: border-box;
}

.super-admin-page-header h1 {
  margin-bottom: 0.5rem;
}

.super-admin-page-header p {
  margin: 0;
}

.super-admin-dashboard-side {
  display: grid;
  width: 100%;
  min-width: 0;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.super-admin-dashboard-side .panel-block { margin: 0; }

.super-admin-main-panel :deep(.super-admin-dashboard-layout) {
  display: flex !important;
  min-height: calc(100vh - 66px);
  flex-direction: column;
  gap: 1.25rem;
  padding: 1.25rem;
}

.super-admin-main-panel .page-header-top.super-admin-page-header {
  margin: 0;
}

.super-admin-main-panel :deep(.super-admin-dashboard-side) {
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin: 0;
}

.super-admin-main-panel :deep(.super-admin-summary-card),
.super-admin-main-panel :deep(.super-admin-branch-panel),
.super-admin-main-panel :deep(.super-admin-activity-panel) {
  min-width: 0;
  padding: 1rem 1.1rem;
  border: 1px solid #e7ddd5;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.045);
}

.super-admin-main-panel :deep(.super-admin-summary-card) {
  min-height: 132px;
  margin: 0;
}

.super-admin-main-panel :deep(.super-admin-summary-card .panel-header),
.super-admin-main-panel :deep(.super-admin-branch-panel .panel-header),
.super-admin-main-panel :deep(.super-admin-activity-panel .panel-header) {
  margin-bottom: 0.75rem;
}

.super-admin-main-panel :deep(.super-admin-summary-card .panel-header h2),
.super-admin-main-panel :deep(.super-admin-branch-panel .panel-header h2),
.super-admin-main-panel :deep(.super-admin-activity-panel .panel-header h2) {
  margin: 0;
  color: #42210b;
  font-size: 0.95rem;
  font-weight: 750;
}

.super-admin-main-panel :deep(.super-admin-activity-panel .panel-header) {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.super-admin-main-panel :deep(.super-admin-activity-panel__hint) {
  margin: 0.25rem 0 0;
  color: #927f73;
  font-size: 0.72rem;
}

.super-admin-main-panel :deep(.super-admin-activity-panel__loading) {
  flex: 0 0 auto;
  color: #c65b27;
  font-size: 0.7rem;
  font-weight: 700;
}

.super-admin-main-panel :deep(.super-admin-summary-card .panel-body),
.super-admin-main-panel :deep(.super-admin-branch-panel .panel-body),
.super-admin-main-panel :deep(.super-admin-activity-panel .panel-body) {
  color: #594638;
}

.super-admin-main-panel :deep(.super-admin-summary-card .side-item) {
  min-height: 32px;
  align-items: center;
  border-bottom: 1px solid #f2ece7;
}

.super-admin-main-panel :deep(.super-admin-summary-card .side-item:last-child) {
  border-bottom: 0;
}

.super-admin-main-panel :deep(.super-admin-summary-card:not(.super-admin-summary-card--stock) .side-value) {
  color: #a94d20;
  font-weight: 700;
}

.super-admin-main-panel :deep(.super-admin-summary-card--stock .side-value) {
  color: #dc2626;
  font-weight: 700;
}

.super-admin-main-panel :deep(.super-admin-attendance-table) {
  overflow-x: auto;
}

.super-admin-main-panel :deep(.super-admin-attendance-table .table-header),
.super-admin-main-panel :deep(.super-admin-attendance-table .table-row) {
  min-width: 360px;
  grid-template-columns: minmax(100px, 1.2fr) minmax(90px, 1fr) minmax(75px, 0.8fr);
  gap: 0.75rem;
}

.super-admin-main-panel :deep(.super-admin-dashboard-main) {
  display: flex;
  width: 100%;
  min-width: 0;
  flex-direction: column;
  gap: 1rem;
  margin: 0;
  overflow: visible !important;
}

.super-admin-main-panel :deep(.super-admin-range-tabs) {
  width: fit-content;
  max-width: 100%;
  gap: 0.25rem;
  margin: 0;
  padding: 0.3rem;
  border: 1px solid #e7ddd5;
  border-radius: 999px;
  background: #fff;
}

.super-admin-main-panel :deep(.super-admin-range-tabs .range-tab) {
  min-height: 36px;
  padding: 0.45rem 1rem;
  border-radius: 999px;
  color: #594638;
  font-weight: 650;
}

.super-admin-main-panel :deep(.super-admin-range-tabs .range-tab--active) {
  background: #e85b22;
  color: #fff;
  outline: none;
  box-shadow: 0 3px 8px rgba(232, 91, 34, 0.2);
}

.super-admin-main-panel :deep(.super-admin-overview-grid) {
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
  margin: 0;
}

.super-admin-main-panel :deep(.super-admin-overview-grid .overview-card) {
  position: relative;
  display: flex;
  min-height: 104px;
  flex-direction: column;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 1.1rem;
  overflow: hidden;
  border: 1px solid #e7ddd5;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.045);
}

.super-admin-main-panel :deep(.super-admin-overview-grid .overview-card::before) {
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: #e85b22;
  content: '';
}

.super-admin-main-panel :deep(.super-admin-overview-grid .overview-card:nth-child(2)::before) {
  background: #26936b;
}

.super-admin-main-panel :deep(.super-admin-overview-grid .overview-card:nth-child(3)::before) {
  background: #bd7b18;
}

.super-admin-main-panel :deep(.super-admin-overview-grid .overview-card:nth-child(4)::before) {
  background: #64748b;
}

.super-admin-main-panel :deep(.super-admin-overview-grid .overview-label) {
  color: #76665c;
  font-size: 0.8rem;
  font-weight: 600;
}

.super-admin-main-panel :deep(.super-admin-overview-grid .overview-value) {
  color: #42210b;
  font-size: 1.45rem;
  line-height: 1.15;
}

.super-admin-main-panel :deep(.super-admin-branch-panel),
.super-admin-main-panel :deep(.super-admin-activity-panel) {
  margin: 0;
  padding: 1.1rem 1.2rem;
}

.super-admin-main-panel :deep(.super-admin-branch-table) {
  overflow-x: auto;
}

.super-admin-main-panel :deep(.super-admin-branch-table .table-header),
.super-admin-main-panel :deep(.super-admin-branch-table .table-row) {
  min-width: 620px;
  grid-template-columns: minmax(160px, 1.6fr) minmax(75px, 0.7fr) minmax(110px, 1fr) minmax(75px, 0.7fr) minmax(95px, 0.8fr);
  align-items: center;
  gap: 0.75rem;
  padding: 0.8rem 0.5rem;
  border: 0;
  border-bottom: 1px solid #f0e9e3;
}

.super-admin-main-panel :deep(.super-admin-branch-table .table-header) {
  color: #76665c;
  background: #fbf8f5;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.super-admin-main-panel :deep(.super-admin-branch-table .table-row:last-child) {
  border-bottom: 0;
}

.super-admin-main-panel :deep(.super-admin-branch-table .table-row > span),
.super-admin-main-panel :deep(.super-admin-attendance-table .table-row > span) {
  min-width: 0;
}

.super-admin-main-panel :deep(.super-admin-branch-table .badge),
.super-admin-main-panel :deep(.super-admin-attendance-table .badge) {
  display: inline-flex;
  min-width: 68px;
  justify-content: center;
  padding: 0.35rem 0.65rem;
  background: #fff4e8;
  color: #87502e;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: capitalize;
}

.super-admin-main-panel :deep(.super-admin-branch-table .badge--success),
.super-admin-main-panel :deep(.super-admin-attendance-table .badge--success) {
  background: #e8f6ee;
  color: #187443;
}

.super-admin-main-panel :deep(.super-admin-branch-table .badge--warning),
.super-admin-main-panel :deep(.super-admin-attendance-table .badge--warning) {
  background: #fff3dc;
  color: #9a5b00;
}

.super-admin-main-panel :deep(.super-admin-attendance-table .badge--info) {
  background: #eaf2ff;
  color: #2859a8;
}

.super-admin-main-panel :deep(.super-admin-activity-panel .queue-item) {
  gap: 1rem;
  padding: 0.85rem 0.25rem;
  border-bottom: 1px solid #f0e9e3;
}

.super-admin-main-panel :deep(.super-admin-activity-panel .queue-item:last-child) {
  border-bottom: 0;
}

.super-admin-main-panel :deep(.super-admin-activity-panel .queue-title) {
  color: #42210b;
  font-size: 0.85rem;
  font-weight: 700;
}

.super-admin-main-panel :deep(.super-admin-activity-panel .queue-meta) {
  margin-top: 0.2rem;
  color: #84766d;
}

.super-admin-main-panel :deep(.super-admin-activity-meta) {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.45rem 0.8rem;
  margin-top: 0.4rem;
  color: #9a897e;
  font-size: 0.68rem;
}

.super-admin-main-panel :deep(.super-admin-activity-meta span),
.super-admin-main-panel :deep(.super-admin-activity-meta time) {
  display: inline-flex;
  align-items: center;
}

.super-admin-main-panel :deep(.super-admin-activity-meta span + span::before),
.super-admin-main-panel :deep(.super-admin-activity-meta time::before) {
  margin-right: 0.8rem;
  color: #d5c4b8;
  content: '•';
}

.sidebar-collapsed .super-admin-sidebar {
  transform: translateX(-100%);
  opacity: 0;
  pointer-events: none;
}

.sidebar-collapsed .super-admin-sidebar__resize-handle { display: none; }

.sidebar-collapsed .super-admin-main-panel { margin-left: 0; }

.sidebar-collapsed .super-admin-topbar { left: 0; }

@media (max-width: 900px) {
  .super-admin-shell { --super-admin-sidebar-width: 200px; }
  .super-admin-sidebar { width: var(--super-admin-sidebar-width); }
  .super-admin-main-panel { margin-left: var(--super-admin-sidebar-width); }
  .super-admin-topbar { left: var(--super-admin-sidebar-width); }
  .super-admin-dashboard-side { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .super-admin-main-panel :deep(.super-admin-overview-grid) { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .super-admin-main-panel :deep(.super-admin-summary-card--attendance) { grid-column: 1 / -1; }
  .super-admin-main-panel .page-header-top.super-admin-page-header { padding: 1.25rem; }
}

@media (max-width: 640px) {
  .super-admin-shell { --super-admin-sidebar-width: 200px; }
  .super-admin-sidebar { width: var(--super-admin-sidebar-width); }
  .super-admin-main-panel { margin-left: 0; }
  .super-admin-topbar { left: 0; }
  .super-admin-shell:not(.sidebar-collapsed) .super-admin-sidebar { transform: translateX(0); }
  .super-admin-main-panel > .admin-page { padding-top: 66px; }
  .super-admin-module-view { padding: 0.75rem; }
  .super-admin-monitoring-notice { align-items: flex-start; flex-direction: column; gap: 0.2rem; }
  .super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-page-header) {
    min-height: 0 !important;
    padding: 1rem !important;
  }
  .super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-main > .panel-section) {
    gap: 0.75rem !important;
    padding: 0.9rem !important;
  }
  .super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-directory-section),
  .super-admin-module-view :deep(.superadmin-logistics-wrapper .supplier-products),
  .super-admin-module-view :deep(.superadmin-logistics-wrapper .admin-main > .panel-section > .panel-section) {
    padding: 0.75rem !important;
  }
  .super-admin-module-view :deep(.admin-layout--owner-sidebar-layout),
  .super-admin-module-view :deep(.main-branch-page),
  .super-admin-module-view :deep(.admin-panel-shell .admin-layout) {
    grid-template-columns: minmax(0, 1fr) !important;
    grid-template-rows: auto auto auto !important;
  }
  .super-admin-module-view :deep(.owner-panel-layout--embedded .owner-panel-sidebar),
  .super-admin-module-view :deep(.main-branch-page .logistics-sidebar),
  .super-admin-module-view :deep(.admin-panel-shell .admin-sidebar) {
    grid-column: 1 !important;
    grid-row: 1 !important;
    min-height: 0 !important;
  }
  .super-admin-module-view :deep(.owner-panel-layout--embedded .admin-main),
  .super-admin-module-view :deep(.main-branch-page .logistics-workspace),
  .super-admin-module-view :deep(.admin-panel-shell .admin-main) {
    grid-column: 1 !important;
    grid-row: 2 !important;
  }
  .super-admin-module-view :deep(.admin-panel-shell .admin-side) {
    grid-column: 1 !important;
    grid-row: 3 !important;
  }
  .super-admin-main-panel :deep(.admin-layout) { padding: 0.75rem; }
  .super-admin-dashboard-side { grid-template-columns: 1fr; }
  .super-admin-main-panel :deep(.super-admin-dashboard-layout) { gap: 1rem; padding: 0.75rem; }
  .super-admin-main-panel :deep(.super-admin-summary-card--attendance) { grid-column: auto; }
  .super-admin-main-panel :deep(.super-admin-overview-grid) { grid-template-columns: 1fr; gap: 0.75rem; }
  .super-admin-main-panel :deep(.super-admin-range-tabs) { width: 100%; justify-content: space-between; }
  .super-admin-main-panel :deep(.super-admin-range-tabs .range-tab) { padding-inline: 0.65rem; }
  .super-admin-main-panel :deep(.super-admin-activity-panel .queue-item) { align-items: flex-start; }
  .super-admin-shell :deep(.announcement-modal .modal-header-custom) { padding: 1rem; }
  .super-admin-shell :deep(.announcement-modal .modal-body-custom) { padding: 1rem; }
  .super-admin-shell :deep(.announcement-modal .modal-footer-custom) { padding: 0.85rem 1rem; }
  .super-admin-shell :deep(.announcement-modal .modal-footer-custom button) { min-width: 0; flex: 1; }
  .super-admin-shell :deep(.terms-modal .modal-header-custom) { padding: 1rem; }
  .super-admin-shell :deep(.terms-modal__body) { padding: 1rem; }
  .super-admin-shell :deep(.terms-modal textarea.info-input) { min-height: 200px; }
  .super-admin-shell :deep(.terms-modal .modal-footer-custom) { padding: 0.85rem 1rem; }
  .super-admin-shell :deep(.terms-modal .modal-footer-custom button) { min-width: 0; flex: 1; }
  .super-admin-main-panel .page-header-top.super-admin-page-header { min-height: 0; padding: 1rem; }
  .super-admin-branch-filter { gap: 0.25rem; }
  .super-admin-branch-filter span { display: none; }
  .super-admin-branch-filter select { width: min(180px, 42vw); min-width: 120px; }
}

.primary-action-btn {
  background: linear-gradient(135deg, #2b8aef, #1a6ed8);
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  margin-top: 0.5rem;
  transition: all 0.2s;
}
.primary-action-btn:hover {
  background: linear-gradient(135deg, #1a6ed8, #1557b0);
}

.secondary-action-btn {
  background: #6c757d;
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  width: 100%;
  margin-top: 0.5rem;
  transition: all 0.2s;
}
.secondary-action-btn:hover {
  background: #5a6268;
}

.staff-btn--center {
  position: relative;
  width: 100%;
  margin-bottom: 0.5rem;
}

.panel-badge {
  position: absolute;
  top: -8px;
  right: -8px;
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

.info-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}
.info-input:focus {
  outline: none;
  border-color: #2b8aef;
}

textarea.info-input {
  resize: vertical;
  min-height: 80px;
}

.admin-main-header h1 {
  color: var(--text-dark);
  font-weight: 800;
  font-family: 'Inter', 'Poppins', sans-serif;
  letter-spacing: -0.5px;
  margin-bottom: 8px;
}

/* Header at the top of layout - spans all columns */
.page-header-top {
  grid-column: 1 / -1;
  width: 100%;
  padding: 12px 0;
  background: white;
  border-bottom: 1px solid #F0E9E0;
  box-shadow: 0 2px 4px rgba(16,24,40,0.04);
  margin-bottom: 4px;
  transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

.page-header-top div {
  padding: 0;
}

.page-header-top h1 {
  color: var(--text-dark);
  font-weight: 800;
  font-family: 'Inter', 'Poppins', sans-serif;
  letter-spacing: -0.5px;
  margin: 0 0 8px 0;
  font-size: 1.5rem;
  word-break: break-word;
  transition: color 0.3s ease;
}

.page-header-top p {
  font-size: 0.9rem;
  color: rgba(66,33,11,0.6);
  margin: 4px 0;
  transition: color 0.3s ease;
}

.page-header-top .small-hint {
  font-size: 0.8rem;
  margin-top: 4px;
  transition: color 0.3s ease;
}

.page-header-top .small-hint--error {
  color: #dc2626;
}

@media (max-width: 479px) {
  .page-header-top h1 {
    font-size: 1.2rem;
  }
}

/* Reduce top spacing for side panels so left side panels move up
   and maximize available space at the top on wide screens. */
@media (min-width: 1024px) {
  .admin-side {
    margin-top: 8px !important;
    position: static !important;
    top: auto !important;
  }
}
</style>
