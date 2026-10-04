<template>
  <div class="min-h-screen owner-panel-light-mode bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]" :class="{ 'owner-panel-layout--embedded': isEmbedded }">
    <div class="admin-page" :class="[pageClass, { 'admin-page--wider': fullWidth }]">
      <section class="admin-layout" :class="{ 'admin-layout--wider': fullWidth, 'admin-layout--owner-two-column': ownerTwoColumnLayout, 'admin-layout--owner-sidebar-layout': showOwnerSidebar, 'owner-sidebar-collapsed': ownerSidebarCollapsed, 'owner-sidebar-resizing': ownerSidebarResizing, 'admin-layout--single-column': singleColumnLayout, 'admin-layout--fit-content': fitContent, 'no-profile-column': !showProfileColumn, 'kitchen-staff-container': pageClass === 'kitchen-staff-page' }" :style="{ '--owner-sidebar-width': `${ownerSidebarWidth}px` }">
        <header v-if="showOwnerTopbar" class="owner-panel-topbar">
          <button
            type="button"
            class="owner-panel-hamburger"
            :aria-label="ownerSidebarCollapsed ? 'Show menu' : 'Hide menu'"
            :aria-expanded="(!ownerSidebarCollapsed).toString()"
            @click.prevent.stop="toggleOwnerSidebar"
          >☰</button>
          <div class="owner-panel-topbar-spacer"></div>
          <button
            v-if="ownerMessagesButton"
            type="button"
            class="owner-panel-message-button"
            :aria-label="notificationSummary.messages ? `Messages, ${notificationSummary.messages} unread` : 'Messages'"
            title="Messages"
            @click="openOwnerMessages"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"></path>
            </svg>
            <span v-if="notificationSummary.messages > 0" class="owner-panel-message-button__badge">
              {{ notificationSummary.messages > 99 ? '99+' : notificationSummary.messages }}
            </span>
          </button>
          <PanelNotificationMenu
            :notification-items="panelNotificationMenuItems"
            :notification-count="panelNotificationMenuCount"
            @select="handleNotificationClick"
          />
          <div class="owner-panel-user-pill" aria-label="Current account">
            <span class="owner-panel-user-pill__avatar">{{ (userProfile.fullName || userProfile.full_name || userProfile.role || 'O').charAt(0).toUpperCase() }}</span>
            <span>{{ ownerUserLabel }}</span>
          </div>
        </header>
        <aside v-if="showOwnerSidebar" class="owner-panel-sidebar" aria-label="Owner sections">
          <slot name="ownerSidebar"></slot>
          <div class="owner-sidebar-footer">
            <slot name="ownerSidebarFooter"></slot>
          </div>
          <button
            class="owner-panel-sidebar__resize-handle"
            type="button"
            aria-label="Resize sidebar"
            title="Resize sidebar"
            @pointerdown="startOwnerSidebarResize"
          ></button>
        </aside>
        <!-- MIDDLE: MAIN DASHBOARD -->
        <main class="admin-main">
          <header v-if="showHeader" class="admin-main-header" :class="{ 'kitchen-staff-header': pageClass === 'kitchen-staff-page' }">
            <div class="admin-main-header-top">
              <div class="header-left-slot">
                  <button v-if="showDefaultBack" class="back-to-dashboard-btn" @click="handleBack">← Back</button>
                  <slot name="headerLeft"></slot>
              </div>
              <div>
                <span v-if="panelEyebrow" class="admin-main-header__eyebrow">{{ panelEyebrow }}</span>
                <h1>{{ panelTitle }}</h1>
                <p>{{ panelDescription }}</p>
              </div>
              <div class="header-actions-top">
                <PanelNotificationMenu
                  v-if="!showOwnerTopbar"
                  :notification-items="panelNotificationMenuItems"
                  :notification-count="panelNotificationMenuCount"
                  @select="handleNotificationClick"
                />
                <template v-if="!isRightColumnHeaderRoute()">
                  <slot name="headerActions"></slot>
                </template>
                <button v-if="showThemeToggle && enableDarkMode" type="button" class="theme-toggle-btn" @click="toggleTheme" :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                  <span class="theme-toggle-btn__icon" aria-hidden="true">
                    <svg v-if="isDarkMode" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="4"></circle>
                      <path d="M12 2v2"></path>
                      <path d="M12 20v2"></path>
                      <path d="M4.93 4.93l1.41 1.41"></path>
                      <path d="M17.66 17.66l1.41 1.41"></path>
                      <path d="M2 12h2"></path>
                      <path d="M20 12h2"></path>
                      <path d="M4.93 19.07l1.41-1.41"></path>
                      <path d="M17.66 6.34l1.41-1.41"></path>
                    </svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
                    </svg>
                  </span>
                  <span class="theme-toggle-btn__text">{{ themeButtonLabel }}</span>
                </button>
              </div>
            </div>
          </header>
          <div v-if="!showHeader && !showOwnerTopbar" class="panel-notification-toolbar">
            <PanelNotificationMenu
              :notification-items="panelNotificationMenuItems"
              :notification-count="panelNotificationMenuCount"
              @select="handleNotificationClick"
            />
          </div>
          <section v-if="activeNotificationPanel && !isNotificationModalOpen" class="panel-notification-details" aria-live="polite">
            <header class="panel-notification-details__header">
              <h2>{{ activeNotificationPanel === 'announcements' ? 'Announcements' : notificationItems.find(item => item.key === activeNotificationPanel)?.label || 'Notification details' }}</h2>
              <button type="button" @click="activeNotificationPanel = ''" aria-label="Close notification details">Close</button>
            </header>
            <template v-if="activeNotificationPanel === 'announcements'">
              <p v-if="loadingAnnouncements">Loading announcements...</p>
              <p v-else-if="announcements.length === 0">No announcements found.</p>
              <ul v-else class="panel-notification-details__list">
                <li v-for="announcement in announcements" :key="announcement.id">
                  <strong>{{ announcement.title }}</strong>
                  <small>{{ new Date(announcement.created_at).toLocaleString() }}</small>
                  <p>{{ announcement.message }}</p>
                </li>
              </ul>
            </template>
            <template v-else>
              <p v-if="notificationDetailItems.length === 0">No items need attention.</p>
              <ul v-else class="panel-notification-details__list panel-notification-details__counts">
                <li v-for="item in notificationDetailItems" :key="item.key">
                  <span>{{ item.label }}</span>
                  <strong>{{ item.count }}</strong>
                </li>
              </ul>
            </template>
          </section>
          <transition name="fade">
            <div
              v-if="isNotificationModalOpen"
              class="info-backdrop info-backdrop--finance-style owner-announcement-backdrop"
              @click.self="closeNotificationModal"
            >
              <section class="info-modal info-modal--finance-style owner-announcement-modal" role="dialog" aria-modal="true" aria-labelledby="owner-announcement-title">
                <button type="button" class="info-modal-close" aria-label="Close notifications" @click="closeNotificationModal">✕</button>
                <h3 id="owner-announcement-title">{{ activeNotificationLabel }}</h3>
                <p class="info-sub">{{ activeNotificationPanel === 'announcements' ? 'Latest updates for your account.' : 'Items that need your attention.' }}</p>
                <div class="info-grid owner-announcement-list">
                  <template v-if="activeNotificationPanel === 'announcements'">
                    <p v-if="loadingAnnouncements" class="owner-announcement-state">Loading announcements...</p>
                    <p v-else-if="announcements.length === 0" class="owner-announcement-state">No announcements found.</p>
                    <article v-for="announcement in announcements" :key="announcement.id" class="owner-announcement-entry">
                      <strong>{{ announcement.title }}</strong>
                      <small>{{ new Date(announcement.created_at).toLocaleString() }}<span v-if="announcement.target"> · {{ announcement.target }}</span></small>
                      <p>{{ announcement.message }}</p>
                    </article>
                  </template>
                  <template v-else>
                    <p v-if="notificationDetailItems.length === 0" class="owner-announcement-state">No items need attention.</p>
                    <ul v-else class="panel-notification-details__list panel-notification-details__counts owner-notification-modal-counts">
                      <li v-for="item in notificationDetailItems" :key="item.key">
                        <span>{{ item.label }}</span>
                        <strong>{{ item.count }}</strong>
                      </li>
                    </ul>
                  </template>
                </div>
                <div class="info-actions">
                  <button type="button" class="btn-outline" @click="closeNotificationModal">Close</button>
                </div>
              </section>
            </div>
          </transition>
          <slot name="main"></slot>
        </main>
        <!-- RIGHT: SIDE PANELS -->
        <aside v-if="!singleColumnLayout && showProfileColumn" class="admin-side">
          <div v-if="showProfileColumn && userProfile" class="admin-card admin-card--stacked">
            <div class="admin-card__header admin-card__header--stacked">
              <label class="admin-avatar admin-avatar--photo avatar-upload" for="avatar-input">
                <img v-if="userProfile.avatarUrl" :src="userProfile.avatarUrl" alt="Profile picture" class="avatar-img" />
                <div v-else class="avatar-placeholder"><span class="avatar-initials">CT</span></div>
                <div class="avatar-overlay" v-if="enableProfileUpdate">
                  <span class="avatar-change-text">Change Photo</span>
                </div>
              </label>
              <div class="admin-header-text admin-admin-header-text--center">
                <div class="admin-label">Account</div>
                <div class="admin-name">{{ userProfile.fullName || userProfile.full_name || 'User' }}</div>
                <div class="admin-role">{{ userProfile.role || 'ROLE' }}</div>
              </div>
              <input
                v-if="enableProfileUpdate"
                id="avatar-input"
                type="file"
                accept="image/*"
                @change="onAvatarChange"
                style="display: none"
              />
            </div>
            <div class="admin-card__body admin-card__body--stacked">
              <div class="admin-id-block admin-id-block--center">
                <span class="admin-id-label">Account I.D: </span>
                <span class="admin-id-value">&nbsp;{{ formatAccountId(userProfile.accountId || userProfile.account_id || userProfile.id || '') }}</span>
              </div>
              <!-- View Info Button -->
              <button v-if="enableProfileUpdate" class="admin-info-btn admin-info-btn--center" @click="openInfoModal">Info</button>
            </div>
            <div class="admin-card__footer admin-card__footer--stacked">
              <slot name="profileFooter"></slot>
              <button v-if="showThemeToggle && enableDarkMode" type="button" class="theme-toggle-btn logout-btn--center" @click="toggleTheme" :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                <span class="theme-toggle-btn__icon" aria-hidden="true">
                  <svg v-if="isDarkMode" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2"></path>
                    <path d="M12 20v2"></path>
                    <path d="M4.93 4.93l1.41 1.41"></path>
                    <path d="M17.66 17.66l1.41 1.41"></path>
                    <path d="M2 12h2"></path>
                    <path d="M20 12h2"></path>
                    <path d="M4.93 19.07l1.41-1.41"></path>
                    <path d="M17.66 6.34l1.41-1.41"></path>
                  </svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
                  </svg>
                </span>
                <span class="theme-toggle-btn__text">{{ themeButtonLabel }}</span>
              </button>
              <div class="admin-actions-row">
                <button class="logout-btn logout-btn--center" @click="$emit('logout')">Logout</button>
              </div>
            </div>
          </div>
          <div v-if="showProfileColumn" class="admin-profile-column__below">
            <slot
              name="profileBottom"
              :announcements="announcements"
              :loadingAnnouncements="loadingAnnouncements"
              :attendanceStatus="attendanceStatus"
              :scheduledTimeOut="scheduledTimeOut"
              :canClockOut="canClockOut"
              :isAttendanceProcessing="isAttendanceProcessing"
              :attendanceMessage="attendanceMessage"
              :attendanceMessageType="attendanceMessageType"
              :performClockIn="performClockIn"
              :performClockOut="performClockOut"
            ></slot>
          </div>
          <template v-if="isRightColumnHeaderRoute()">
            <div class="header-actions-side">
              <slot name="headerActions"></slot>
            </div>
          </template>
          <slot name="sideTop"></slot>
          <section v-if="showAnnouncements && !announcementsAfterAttendance" class="panel-block announcements-panel">
            <div class="panel-header announcements-header">
              <h2>Announcements</h2>
              <!-- announcements header - avatar removed (profile button available in page header) -->
            </div>
            <div class="panel-body">
              <div v-if="loadingAnnouncements">Loading...</div>
              <div v-else-if="announcements.length === 0">No announcements</div>
              <ul v-else class="announcement-list">
                <li v-for="a in announcements" :key="a.id" class="announcement-item">
                  <div class="announcement-title">{{ a.title }}</div>
                  <div class="announcement-meta">{{ new Date(a.created_at).toLocaleString() }} • {{ a.target }}</div>
                  <div class="announcement-message">{{ a.message }}</div>
                </li>
              </ul>
            </div>
          </section>

          <template v-if="hasSideSlot">
            <slot name="side"></slot>
          </template>
          <template v-else>
            <div v-if="!hideAttendanceCard" class="attendance-card" style="background:#ffffff;">
              <div class="attendance-header" style="margin-top:50px;">
                <span class="attendance-title">Attendance</span>
                <span :class="['attendance-status-badge', attendanceStatus.is_clocked_in ? 'status-on-duty' : 'status-off-duty']">
                  {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
                </span>
              </div>
              <div class="attendance-times" v-if="attendanceStatus.clock_in_time || attendanceStatus.clock_out_time">
                <div class="time-row"><span class="time-label">Clock In:</span><span class="time-value">{{ attendanceStatus.clock_in_time || '-' }}</span></div>
                <div class="time-row"><span class="time-label">Clock Out:</span><span class="time-value">{{ attendanceStatus.clock_out_time || '-' }}</span></div>
                <div class="time-row" v-if="attendanceStatus.hours_worked > 0"><span class="time-label">Hours:</span><span class="time-value">{{ attendanceStatus.hours_worked }} hrs</span></div>
              </div>
              <div class="attendance-buttons">
                <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing" class="btn-clock-in">{{ isAttendanceProcessing ? '...' : 'Clock In' }}</button>
                <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">{{ isAttendanceProcessing ? '...' : 'Clock Out' }}</button>
              </div>
              <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction"><span class="restriction-icon">🔒</span><span>Cannot clock out before {{ scheduledTimeOut }}</span></div>
              <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
            </div>
          </template>
          <section v-if="showAnnouncements && announcementsAfterAttendance" class="panel-block announcements-panel">
            <div class="panel-header announcements-header">
              <h2>Announcements</h2>
            </div>
            <div class="panel-body">
              <div v-if="loadingAnnouncements">Loading...</div>
              <div v-else-if="announcements.length === 0">No announcements</div>
              <ul v-else class="announcement-list">
                <li v-for="a in announcements" :key="a.id" class="announcement-item">
                  <div class="announcement-title">{{ a.title }}</div>
                  <div class="announcement-meta">{{ new Date(a.created_at).toLocaleString() }} • {{ a.target }}</div>
                  <div class="announcement-message">{{ a.message }}</div>
                </li>
              </ul>
            </div>
          </section>
        </aside>
      </section>
    </div>
      <Toast />

      <!-- Global avatar input (always available even when profile column hidden) -->
      <input
        ref="globalAvatarInput"
        id="global-avatar-input"
        type="file"
        accept="image/*"
        @change="onAvatarChange"
        style="display:none"
        v-if="enableProfileUpdate"
      />

    <!-- PROFILE INFO MODAL -->
    <transition name="fade">
      <div v-if="showInfoModal" class="info-backdrop" :class="{ 'info-backdrop--finance-style': accountInfoStyle === 'finance' }">
        <div class="info-modal" :class="{ 'info-modal--finance-style': accountInfoStyle === 'finance' }">
          <button v-if="accountInfoStyle === 'finance'" type="button" class="info-modal-close" aria-label="Close account information" @click="handleInfoClose">✕</button>
          <h3>{{ accountInfoStyle === 'finance' ? 'Account Information' : 'Info' }}</h3>
          <p class="info-sub">Your account information.</p>

          <div class="info-grid">
            <!-- Avatar preview + change control (appears when editing and profile updates enabled) -->
            <div v-if="enableProfileUpdate && isEditingInfo" class="info-avatar-row">
              <div class="info-avatar">
                <img v-if="localProfile.avatarUrl" :src="localProfile.avatarUrl" alt="avatar" />
                <div v-else class="info-avatar-initials">{{ (localProfile.fullName || localProfile.full_name || 'U').charAt(0) }}</div>
              </div>
              <div class="info-avatar-actions">
                <button class="btn-outline" type="button" @click.prevent="$refs.avatarInputModal.click()">Change Photo</button>
                <input ref="avatarInputModal" id="avatar-input-modal" type="file" accept="image/*" @change="onAvatarChange" style="display:none" />
              </div>
            </div>
            <div class="info-row">
              <span class="info-label">Full name</span>
              <span class="info-value">{{ localProfile.fullName || localProfile.full_name || '-' }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Account I.D</span>
              <span class="info-value">{{ formatAccountId(localProfile.accountId) }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Role</span>
              <span class="info-value">{{ localProfile.role || '-' }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Username</span>
              <span class="info-value">{{ localProfile.username || '-' }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Email</span>
              <span class="info-value">{{ localProfile.email || '-' }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Contact</span>
              <span class="info-value">{{ localProfile.contact || localProfile.phone_number || '-' }}</span>
            </div>

            <div class="info-row">
              <span class="info-label">Department</span>
              <span class="info-value">{{ localProfile.department || '-' }}</span>
            </div>

            <!-- Password fields - only shown when canChangePassword is true and editing -->
            <template v-if="canChangePassword && isEditingInfo">
              <form @submit.prevent="saveProfile">
                <div class="info-row info-row--password">
                  <span class="info-label">New Password</span>
                  <input v-model="localProfile.password" class="info-input" type="password" placeholder="Enter new password" />
                </div>

                <div class="info-row info-row--password">
                  <span class="info-label">Confirm Password</span>
                  <input v-model="localProfile.password_confirmation" class="info-input" type="password" placeholder="Re-enter new password" />
                </div>
              </form>
            </template>
          </div>

          <!-- Error message display -->
          <div v-if="profileError" class="info-error">
            {{ profileError }}
          </div>

          <!-- Success message display -->
          <div v-if="profileSuccess" class="info-success">
            {{ profileSuccess }}
          </div>

          <div class="info-actions">
            <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? 'Cancel' : 'Close' }}</button>

            <!-- Show Change Password button for Owner/HR roles -->
            <button
              v-if="canChangePassword && !isEditingInfo"
              class="btn-primary"
              @click="isEditingInfo = true"
              :disabled="isSavingProfile"
            >
              Change Password
            </button>

            <!-- Show Save button when editing and canChangePassword -->
            <button
              v-if="canChangePassword && isEditingInfo"
              class="btn-primary"
              @click="saveProfile"
              :disabled="isSavingProfile"
            >
              {{ isSavingProfile ? 'Saving...' : 'Save Password' }}
            </button>

            <!-- Show Edit Information button for Owner role (canEditProfile) -->
            <button
              v-if="canEditProfile && !isEditingInfo"
              class="btn-primary"
              @click="isEditingInfo = true"
              :disabled="isSavingProfile"
            >
              Edit Information
            </button>

            <!-- Show Save button when editing and canEditProfile -->
            <button
              v-if="canEditProfile && isEditingInfo"
              class="btn-primary"
              @click="saveProfile"
              :disabled="isSavingProfile"
            >
              {{ isSavingProfile ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, useSlots, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Toast from './Toast.vue'
import { showToast } from './toastStore'
import PanelNotificationMenu from './PanelNotificationMenu.vue'

const props = defineProps({
  embedded: { type: Boolean, default: false },
  userProfile: { type: Object, default: () => ({}) },
  panelEyebrow: { type: String, default: '' },
  panelTitle: { type: String, default: '' },
  panelDescription: { type: String, default: '' },
  fullWidth: { type: Boolean, default: false },
  ownerTwoColumnLayout: { type: Boolean, default: false },
  singleColumnLayout: { type: Boolean, default: false },
  fitContent: { type: Boolean, default: false },
  showHeader: { type: Boolean, default: true },
  enableProfileUpdate: { type: Boolean, default: false },
  canEditProfile: { type: Boolean, default: false },
  canChangePassword: { type: Boolean, default: false },
  profileEndpoint: { type: String, default: '' },
  updateEndpoint: { type: String, default: '' },
  avatarEndpoint: { type: String, default: '' }
  ,
  pageClass: { type: String, default: '' }
  ,
  showProfileColumn: { type: Boolean, default: true }
  ,
  showBackButton: { type: Boolean, default: false }
  ,
  showAnnouncements: { type: Boolean, default: true }
  ,
  announcementsAfterAttendance: { type: Boolean, default: false }
  ,
  showAttendanceCard: { type: Boolean, default: true }
  ,
  enableDarkMode: { type: Boolean, default: true }
  ,
  showOwnerSidebar: { type: Boolean, default: false },
  showOwnerTopbar: { type: Boolean, default: false },
  ownerMessagesButton: { type: Boolean, default: false },
  announcementsInModal: { type: Boolean, default: false },
  notificationDetailsInModal: { type: Boolean, default: false },
  topbarLabel: { type: String, default: '' },
  accountInfoStyle: { type: String, default: 'default' }
})

const superAdminEmbedded = inject('superAdminEmbedded', false)
const isEmbedded = computed(() => props.embedded || superAdminEmbedded)

const emit = defineEmits(['logout', 'profile-updated', 'back'])
const route = useRoute()
const router = useRouter()
const ownerSidebarCollapsed = ref(false)
const ownerSidebarWidth = ref(156)
const ownerSidebarResizing = ref(false)

const ownerUserLabel = computed(() => {
  if (props.topbarLabel) return props.topbarLabel
  const displayRole = props.userProfile.displayRole || props.userProfile.role || 'OWNER'
  const branchName = props.userProfile.branch?.name || props.userProfile.branch || props.userProfile.branch_name
  return branchName ? `${displayRole} - ${branchName}` : displayRole
})

function toggleOwnerSidebar() {
  ownerSidebarCollapsed.value = !ownerSidebarCollapsed.value
}

function startOwnerSidebarResize(event) {
  if (ownerSidebarCollapsed.value) return

  event.preventDefault()
  ownerSidebarResizing.value = true
  const startX = event.clientX
  const startWidth = ownerSidebarWidth.value

  const resize = (moveEvent) => {
    const nextWidth = startWidth + moveEvent.clientX - startX
    ownerSidebarWidth.value = Math.min(320, Math.max(120, nextWidth))
  }

  const stopResize = () => {
    ownerSidebarResizing.value = false
    document.removeEventListener('pointermove', resize)
    document.removeEventListener('pointerup', stopResize)
  }

  document.addEventListener('pointermove', resize)
  document.addEventListener('pointerup', stopResize)
}

const isCustomAccount = computed(() => {
  try {
    const raw = localStorage.getItem('user') || 'null'
    const u = JSON.parse(raw)
    return (u?.role || '').toLowerCase() === 'custom'
  } catch (e) {
    return false
  }
})

const hideAttendanceCard = computed(() => {
  try {
    return !props.showAttendanceCard || Boolean(route && route.query && route.query.from === 'custom-panel') || isCustomAccount.value
  } catch (e) {
    return !props.showAttendanceCard || isCustomAccount.value
  }
})

// Show a back button when parent explicitly requests it via prop, when the
// current route contains `?from=custom-panel`, or when the logged-in account is
// of type custom (so modules always have a way back).
const slots = useSlots()
const hasSideSlot = computed(() => {
  try { return Boolean(slots.side && slots.side().length) } catch (e) { return false }
})

// Attendance card state (default side when child doesn't supply one)
const attendanceStatus = ref({ is_clocked_in: false, clock_in_time: null, clock_out_time: null, hours_worked: 0 })
const isAttendanceProcessing = ref(false)
const attendanceMessage = ref('')
const attendanceMessageType = ref('')
const attendanceSettings = ref({ early_clockout_override: false, scheduled_time_out: '17:00:00' })

const scheduledTimeOut = computed(() => {
  const time = attendanceSettings.value.scheduled_time_out || '17:00:00'
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour % 12 || 12
  return `${hour12}:${minutes} ${ampm}`
})

const canClockOut = computed(() => {
  if (!attendanceStatus.value.is_clocked_in) return false
  if (attendanceSettings.value.early_clockout_override) return true
  const now = new Date()
  const currentTotalMinutes = now.getHours() * 60 + now.getMinutes()
  const [scheduledHours, scheduledMinutes] = (attendanceSettings.value.scheduled_time_out || '17:00:00').split(':')
  const scheduledTotalMinutes = parseInt(scheduledHours) * 60 + parseInt(scheduledMinutes)
  return currentTotalMinutes >= scheduledTotalMinutes
})

async function loadAttendanceStatus() {
  try {
    const role = (localProfile.value.role || props.userProfile?.role || '').toString().toUpperCase()
    const prefix = role.includes('MANAGER') ? '/api/manager' : (role === 'OWNER' ? '/api/owner' : '/api/staff')
    const res = await axios.get(`${prefix}/attendance/status`, { withCredentials: true })
    if (res.data && res.data.success) {
      attendanceStatus.value = {
        is_clocked_in: !!res.data.clocked_in,
        clock_in_time: res.data.time_in || res.data.status?.clock_in_time || null,
        clock_out_time: res.data.time_out || res.data.status?.clock_out_time || null,
        hours_worked: res.data.status?.hours_worked || 0
      }
    }
  } catch (e) {
    // ignore - non-critical
  }
}

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      attendanceSettings.value = {
        early_clockout_override: res.data.data.early_clockout_override || false,
        scheduled_time_out: res.data.data.scheduled_time_out || '17:00:00'
      }
    }
  } catch (e) {
    // ignore
  }
}

async function performClockIn() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''
  try {
    const role = (localProfile.value.role || props.userProfile?.role || '').toString().toUpperCase()
    const prefix = role.includes('MANAGER') ? '/api/manager' : (role === 'OWNER' ? '/api/owner' : '/api/staff')
    const res = await axios.post(`${prefix}/clock-in`, {}, { withCredentials: true })
    if (res.data && res.data.success) {
      attendanceMessage.value = 'Clocked in successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking in'
    attendanceMessageType.value = 'error'
  } finally { isAttendanceProcessing.value = false; setTimeout(() => { attendanceMessage.value = '' }, 3000) }
}

async function performClockOut() {
  if (isAttendanceProcessing.value) return
  isAttendanceProcessing.value = true
  attendanceMessage.value = ''
  try {
    const role = (localProfile.value.role || props.userProfile?.role || '').toString().toUpperCase()
    const prefix = role.includes('MANAGER') ? '/api/manager' : (role === 'OWNER' ? '/api/owner' : '/api/staff')
    const res = await axios.post(`${prefix}/clock-out`, {}, { withCredentials: true })
    if (res.data && res.data.success) {
      attendanceMessage.value = 'Clocked out successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking out'
    attendanceMessageType.value = 'error'
  } finally { isAttendanceProcessing.value = false; setTimeout(() => { attendanceMessage.value = '' }, 3000) }
}

const showBackComputed = computed(() => {
  try {
    return Boolean(props.showBackButton) || (route && route.query && route.query.from === 'custom-panel') || isCustomAccount.value
  } catch (e) {
    return Boolean(props.showBackButton) || isCustomAccount.value
  }
})

// Only render the default back button when the parent did not provide a custom
// `headerLeft` slot (prevents duplicate back buttons when a parent injects its
// own back control into the headerLeft slot).
const showDefaultBack = computed(() => {
  try {
    const hasHeaderLeft = Boolean(slots.headerLeft && slots.headerLeft().length)
    return showBackComputed.value && !hasHeaderLeft
  } catch (e) {
    return showBackComputed.value
  }
})

const themeKey = 'owner_module_theme'
const theme = ref('light')
const isDarkMode = computed(() => theme.value === 'dark')
const themeButtonLabel = computed(() => (isDarkMode.value ? 'Light Mode' : 'Dark Mode'))
const showThemeToggle = computed(() => props.enableDarkMode && !isInventoryRoute() && !isRightColumnHeaderRoute() && !isKitchenStaffRoute() && !isManagerLogisticsRoute() && !isManagerProcurementRoute() && !isMainBranchHrRoute() && !isSupplierRoute())

const isInventoryRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/manager/inventory') || route.includes('/staff/inventory') || route.includes('/inventory')
  } catch (e) {
    return false
  }
}

const isKitchenStaffRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/staff/kitchen')
  } catch (e) {
    return false
  }
}

const isSupplierRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/supplier')
  } catch (e) {
    return false
  }
}

const isMainBranchAdminRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/main-branch/admin')
  } catch (e) {
    return false
  }
}

const isMainBranchHrRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/main-branch/hr')
  } catch (e) {
    return false
  }
}

const isRightColumnHeaderRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/main-branch/admin') ||
      route.includes('/manager/finance') ||
      route.includes('/main-branch/finance') ||
      route.includes('/main-branch/hr')
  } catch (e) {
    return false
  }
}

const isManagerLogisticsRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/manager/logistics') ||
      route.includes('/main-branch/logistics') ||
      route.includes('/super-admin/logistics') ||
      route.includes('/manager/finance') ||
      route.includes('/main-branch/finance') ||
      route.includes('/manager/hr') ||
      route.includes('/manager/inventory') ||
      route.includes('/staff/inventory') ||
      route.includes('/inventory') ||
      route.includes('/staff/kitchen') ||
      route.includes('/staff/cashier')
  } catch (e) {
    return false
  }
}

const isManagerProcurementRoute = () => {
  try {
    const route = (window.location.pathname || '').toLowerCase()
    return route.includes('/manager/procurement') ||
      route.includes('/main-branch/procurement') ||
      route.includes('/super-admin/procurement')
  } catch (e) {
    return false
  }
}

const applyThemeMode = () => {
  try {
    const root = document.documentElement
    const body = document.body
    if (theme.value === 'dark') {
      root.classList.add('dark-mode')
      root.classList.remove('light-mode')
      body.classList.add('dark-mode')
      body.classList.remove('light-mode')
    } else {
      root.classList.remove('dark-mode')
      root.classList.add('light-mode')
      body.classList.remove('dark-mode')
      body.classList.add('light-mode')
    }

    document.querySelectorAll('.admin-page, .admin-layout, .admin-side, .admin-main, .panel-block, .panel-body, .admin-card, .owner-hero-card, .owner-quicklinks-card, .owner-announcements-card').forEach(el => {
      el.classList.toggle('dark-mode', theme.value === 'dark')
      el.classList.toggle('light-mode', theme.value !== 'dark')
    })
  } catch (e) {
    console.warn('OwnerPanelLayout: failed to apply theme', e)
  }
}

const persistThemeMode = () => {
  try {
    localStorage.setItem(themeKey, theme.value)
  } catch (e) {
    // ignore localStorage failures
  }
}

const loadThemeMode = () => {
  try {
    if (isEmbedded.value || !props.enableDarkMode) {
      theme.value = 'light'
      applyThemeMode()
      return
    }

    if (isManagerLogisticsRoute() || isManagerProcurementRoute() || isMainBranchAdminRoute() || isMainBranchHrRoute() || isSupplierRoute()) {
      theme.value = 'light'
      applyThemeMode()
      return
    }

    const saved = localStorage.getItem(themeKey)
    if (saved === 'dark' || saved === 'light') {
      theme.value = saved
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      theme.value = 'dark'
    } else {
      theme.value = 'light'
    }
  } catch (e) {
    theme.value = 'light'
  }
  applyThemeMode()
}

const toggleTheme = () => {
  if (isManagerLogisticsRoute() || isManagerProcurementRoute() || isMainBranchAdminRoute() || isMainBranchHrRoute() || isSupplierRoute()) {
    theme.value = 'light'
    persistThemeMode()
    applyThemeMode()
    return
  }

  theme.value = theme.value === 'dark' ? 'light' : 'dark'
  persistThemeMode()
  applyThemeMode()
}

watch(theme, () => {
  persistThemeMode()
  applyThemeMode()
})

function handleBack() {
  try { emit('back') } catch (e) {}
  try { router.push('/custom-panel') } catch (e) { window.location.href = '/custom-panel' }
}

const localProfile = ref({})
const showInfoModal = ref(false)
const isEditingInfo = ref(false)
const isSavingProfile = ref(false)
const profileError = ref('')
const profileSuccess = ref('')

const notificationSummary = ref({ approvals: 0, messages: 0, announcements: 0, updates: 0 })
const notificationBreakdown = ref({ counts: {}, extras: {} })
const activeNotificationPanel = ref('')
let notificationTimer = null
let notificationSummaryRequestId = 0

const moduleNotificationDefinitions = [
  { key: 'admin', label: 'Orders', tone: 'warning' },
  { key: 'finance', label: 'Finance approvals', tone: 'warning' },
  { key: 'inventory', label: 'Inventory confirmations', tone: 'success' },
  { key: 'logistics', label: 'Logistics', tone: 'success' },
  { key: 'procurement', label: 'Procurement', tone: 'success' },
  { key: 'kitchen', label: 'Kitchen approvals', tone: 'warning' },
  { key: 'supplier', label: 'Supplier orders', tone: 'success' },
  { key: 'cashier', label: 'Cashier orders', tone: 'warning' },
  { key: 'hr', label: 'HR', tone: 'info' },
  { key: 'branchPendingOwner', label: 'Branch approvals', tone: 'warning' },
  { key: 'branchPendingFinance', label: 'Finance branch approvals', tone: 'warning' },
  { key: 'priceMarkupPending', label: 'Price markup approvals', tone: 'warning' },
  { key: 'ownerProductRequests', label: 'Product requests', tone: 'warning' },
]

const currentPanelModuleKeys = computed(() => {
  const title = String(props.panelTitle || '').toLowerCase()
  if (title.includes('owner panel')) return moduleNotificationDefinitions.map(item => item.key)
  if (title.includes('price markup')) return ['priceMarkupPending']
  if (title.includes('branch confirmation')) return ['branchPendingOwner', 'branchPendingFinance']
  if (title.includes('main branch administration')) return ['admin', 'logistics', 'supplier', 'branchPendingOwner']
  if (title.includes('logistics')) return ['logistics']
  if (title.includes('procurement')) return ['procurement']
  if (title.includes('inventory')) return ['inventory']
  if (title.includes('supplier')) return ['supplier']
  if (title.includes('finance')) return ['finance']
  if (title.includes('kitchen') || title.includes('dish approval')) return ['kitchen']
  if (title.includes('admin') || title.includes('administration')) return ['admin', 'cashier']
  if (title.includes('hr')) return ['hr']
  return []
})

function moduleNotificationCount(key) {
  return Number(notificationBreakdown.value.counts[key] ?? notificationBreakdown.value.extras[key] ?? 0)
}

const notificationItems = computed(() => [
  { key: 'messages', label: 'Messages', icon: 'M', tone: 'info', count: notificationSummary.value.messages },
  { key: 'announcements', label: 'Announcements', icon: 'A', tone: 'purple', count: notificationSummary.value.announcements },
  ...moduleNotificationDefinitions
    .filter(item => currentPanelModuleKeys.value.includes(item.key))
    .map(item => ({ ...item, icon: '!', count: moduleNotificationCount(item.key) })),
].filter(item => item.count > 0))
const panelNotificationMenuItems = computed(() => (
  props.ownerMessagesButton
    ? notificationItems.value.filter(item => item.key !== 'messages')
    : notificationItems.value
))
const panelNotificationMenuCount = computed(() => (
  panelNotificationMenuItems.value.reduce((total, item) => total + item.count, 0)
))

const notificationDetailItems = computed(() => {
  const item = moduleNotificationDefinitions.find(definition => definition.key === activeNotificationPanel.value)
  return item ? [{ ...item, count: moduleNotificationCount(item.key) }] : []
})
const isNotificationModalOpen = computed(() => (
  props.notificationDetailsInModal ||
  (props.announcementsInModal && activeNotificationPanel.value === 'announcements')
) && Boolean(activeNotificationPanel.value))
const activeNotificationLabel = computed(() => (
  notificationItems.value.find(item => item.key === activeNotificationPanel.value)?.label || 'Notifications'
))

async function loadNotificationSummary() {
  const requestId = ++notificationSummaryRequestId
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (requestId === notificationSummaryRequestId && res.data?.ok && res.data.summary) {
      notificationSummary.value = { ...notificationSummary.value, ...res.data.summary }
      notificationBreakdown.value = {
        counts: res.data.counts || {},
        extras: res.data.extras || {},
      }
    }
  } catch (e) {
    // Notifications are non-critical and should not interrupt the panel.
  }
}

async function markNotificationRead(key) {
  if (!props.notificationDetailsInModal) return

  const readableCategories = ['announcements', 'logistics', 'supplier', 'branchPendingOwner']
  if (!readableCategories.includes(key)) return

  try {
    await axios.post('/api/panel-notifications/read', { category: key }, { withCredentials: true })
    await loadNotificationSummary()
  } catch (e) {
    showToast(e.response?.data?.message || 'Unable to update notification read status.', 'error')
  }
}

function openOwnerMessages() {
  window.dispatchEvent(new CustomEvent('open-message-widget', { detail: { ownerPanel: true } }))
}

function updateOwnerUnreadMessageCount(event) {
  const count = Number(event.detail?.count)
  if (Number.isFinite(count) && count >= 0) {
    notificationSummary.value.messages = count
  }
}

function handleNotificationClick(key) {
  if (key === 'messages') {
    window.dispatchEvent(new CustomEvent('open-message-widget'))
    return
  }

  activeNotificationPanel.value = key
  if (props.notificationDetailsInModal) {
    Promise.resolve(markNotificationRead(key)).catch(() => {})
  }
  if ((key === 'announcements' && props.announcementsInModal) || props.notificationDetailsInModal) {
    if (key === 'announcements') Promise.resolve(loadAnnouncements()).catch(() => {})
    return
  }

  if (key === 'announcements') {
    Promise.resolve(loadAnnouncements()).catch(() => {})
  }
}

function closeNotificationModal() {
  activeNotificationPanel.value = ''
}

// Announcements for the current user
const announcements = ref([])
const loadingAnnouncements = ref(false)

const loadAnnouncements = async () => {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    if (res.data && res.data.ok) announcements.value = res.data.announcements || []
  } catch (e) {
    // ignore - non-critical
  } finally {
    loadingAnnouncements.value = false
  }
}

// Computed property to check if password change is allowed for the current user's role
const canChangePasswordForRole = computed(() => {
  const role = (props.userProfile.role || '').toUpperCase()
  return role === 'OWNER' || role === 'HR'
})

// Combined computed property that checks both the prop and the role
const canChangePassword = computed(() => {
  return props.canChangePassword && canChangePasswordForRole.value
})

watch(() => props.userProfile, (newVal) => {
  if (newVal) {
    // Ensure accountId is available under a consistent key for the Info modal
    const normalized = { ...newVal }
    normalized.accountId = normalized.accountId || normalized.account_id || normalized.id || normalized.user_id || normalized.staff_id || ''
    localProfile.value = normalized
  }
}, { immediate: true })

onMounted(() => {
  window.addEventListener('owner-message-unread-count', updateOwnerUnreadMessageCount)
  if (window.matchMedia('(max-width: 1023px)').matches) {
    ownerSidebarCollapsed.value = true
  }

  try {
    loadThemeMode()
    ;(async () => {
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
      } catch (e) {
        // non-fatal; continue to load announcements and profile
      }
    })()

    Promise.resolve(loadAnnouncements()).catch(() => {})
    Promise.resolve(loadNotificationSummary()).catch(() => {})
    notificationTimer = window.setInterval(loadNotificationSummary, 60000)
    // Load attendance status/settings for default side attendance card
    if (!hideAttendanceCard.value) {
      Promise.resolve(loadAttendanceStatus()).catch(() => {})
      Promise.resolve(loadAttendanceSettings()).catch(() => {})
    }
  } catch (e) {
    // ignore initialization errors
  }

  // If parent did not provide a populated `userProfile` prop, try fetching the
  // authoritative current user so the profile column is not empty after SPA
  // navigations (e.g. coming from the custom panel).
  ;(async () => {
    try {
      const isEmpty = !props.userProfile || Object.keys(props.userProfile).length === 0
      if (isEmpty) {
        const res = await axios.get('/api/me', { withCredentials: true })
        const u = res.data?.user || res.data?.data || res.data || null
        if (u) {
          const normalized = { ...u }
          normalized.accountId = normalized.accountId || normalized.account_id || normalized.id || normalized.user_id || ''
          localProfile.value = normalized
          emit('profile-updated', normalized)
        }
      }
    } catch (e) {
      // ignore - non-critical; child panels will still attempt their own profile fetch
    }
  })()
  // listen for global triggers from parent panels when they cannot call child methods directly
  window.addEventListener('open-owner-edit-profile', openEditProfile)
  window.addEventListener('open-owner-info', openInfoModal)
})

onUnmounted(() => {
  window.removeEventListener('owner-message-unread-count', updateOwnerUnreadMessageCount)
  if (notificationTimer) window.clearInterval(notificationTimer)
  try { window.removeEventListener('open-owner-edit-profile', openEditProfile) } catch (e) {}
  try { window.removeEventListener('open-owner-info', openInfoModal) } catch (e) {}
})

// Register Toast component for global toasts

const getProfileEndpoint = () => {
  if (props.profileEndpoint) return props.profileEndpoint
  const role = (props.userProfile.role || '').toUpperCase()
  const department = (props.userProfile.department || '').toUpperCase()

  if (role === 'MANAGER' || role === 'HR') {
    if (department === 'HR') return '/api/manager/hr/profile'
    if (department === 'FINANCE') return '/api/manager/finance/profile'
    if (department === 'LOGISTICS') return '/api/manager/logistics/profile'
    if (department === 'INVENTORY') return '/api/manager/inventory/profile'
  }
  return '/api/staff/profile'
}

const getUpdateEndpoint = () => {
  if (props.updateEndpoint) return props.updateEndpoint
  const role = (props.userProfile.role || '').toUpperCase()
  const department = (props.userProfile.department || '').toUpperCase()

  if (role === 'MANAGER' || role === 'HR') {
    if (department === 'HR') return '/api/manager/hr/profile'
    if (department === 'FINANCE') return '/api/manager/finance/profile'
    if (department === 'LOGISTICS') return '/api/manager/logistics/profile'
    if (department === 'INVENTORY') return '/api/manager/inventory/profile'
  }
  return '/api/staff/profile'
}

const getAvatarEndpoint = () => {
  if (props.avatarEndpoint) return props.avatarEndpoint
  return '/api/staff/avatar'
}

function openInfoModal() {
  showInfoModal.value = true
  isEditingInfo.value = false
  profileError.value = ''
  profileSuccess.value = ''

  // Normalize field names for form binding
  const profile = { ...props.userProfile }
  profile.fullName = profile.fullName || profile.full_name || ''
  profile.contact = profile.contact || profile.phone_number || ''
  profile.password = ''
  profile.password_confirmation = ''

  profile.accountId = profile.accountId || profile.account_id || profile.id || profile.user_id || profile.staff_id || ''
  localProfile.value = profile
}

function openEditProfile() {
  showInfoModal.value = true
  isEditingInfo.value = true
  profileError.value = ''
  profileSuccess.value = ''

  // Normalize fields for form binding (same as openInfoModal but in edit mode)
  const profile = { ...props.userProfile }
  profile.fullName = profile.fullName || profile.full_name || ''
  profile.contact = profile.contact || profile.phone_number || ''
  profile.password = ''
  profile.password_confirmation = ''
  profile.accountId = profile.accountId || profile.account_id || profile.id || profile.user_id || profile.staff_id || ''
  localProfile.value = profile
}

function formatAccountId(val) {
  if (val === null || val === undefined || val === '') return '-'
  const s = String(val)
  // already looks like an id with letters
  if (/^id[\-0-9]/i.test(s) || /[a-zA-Z]/.test(s)) return s
  // numeric -> pad to 4 digits and prefix with 'id'
  const digits = s.replace(/[^0-9]/g, '')
  if (!digits) return s
  const padded = digits.padStart(4, '0')
  return 'id' + padded
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false
    profileError.value = ''
    profileSuccess.value = ''

    // Normalize field names when resetting
    const profile = { ...props.userProfile }
    profile.fullName = profile.fullName || profile.full_name || ''
    profile.contact = profile.contact || profile.phone_number || ''
    profile.password = ''
    profile.password_confirmation = ''

    localProfile.value = profile
  } else {
    showInfoModal.value = false
  }
}

async function saveProfile() {
  isSavingProfile.value = true
  profileError.value = ''
  profileSuccess.value = ''

  try {
    // Fetch CSRF cookie first
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    await new Promise(resolve => setTimeout(resolve, 100))

    const endpoint = getUpdateEndpoint()

    // Determine mode based on props
    const isPasswordOnlyMode = props.canChangePassword && !props.canEditProfile
    const isFullEditMode = props.canEditProfile

    let payload = {}

    if (isPasswordOnlyMode) {
      // Password only mode (HR role)
      const password = (localProfile.value.password || '').trim()
      const passwordConfirmation = localProfile.value.password_confirmation || ''

      if (!password) {
        profileError.value = 'Please enter a new password.'
        isSavingProfile.value = false
        return
      }

      payload = {
        password,
        password_confirmation: passwordConfirmation
      }
    } else if (isFullEditMode) {
      // Full edit mode (Owner role)
      payload = {
        fullName: localProfile.value.fullName || '',
        username: localProfile.value.username || '',
        email: localProfile.value.email || '',
        contact: localProfile.value.contact || ''
      }

      if (localProfile.value.password && localProfile.value.password.trim() !== '') {
        payload.password = localProfile.value.password
        payload.password_confirmation = localProfile.value.password_confirmation
      }
    }

    const res = await axios.put(endpoint, payload, { withCredentials: true })

    if (res.data.ok) {
      isEditingInfo.value = false
      profileSuccess.value = res.data.message || 'Profile updated successfully.'

      // Clear password fields
      localProfile.value.password = ''
      localProfile.value.password_confirmation = ''

      // Update local profile with returned data
      if (res.data.user) {
        const updatedUser = res.data.user
        updatedUser.fullName = updatedUser.fullName || updatedUser.full_name || ''
        updatedUser.contact = updatedUser.contact || updatedUser.phone_number || ''
        localProfile.value = { ...localProfile.value, ...updatedUser }
      }

      emit('profile-updated', res.data.user || localProfile.value)
    } else {
      profileError.value = res.data.message || 'Failed to update profile.'
    }
  } catch (e) {
    const apiMessage = e?.response?.data?.message
    const apiErrors = e?.response?.data?.errors

    if (apiMessage) {
      profileError.value = apiMessage
    } else if (apiErrors && typeof apiErrors === 'object') {
      const firstKey = Object.keys(apiErrors)[0]
      const firstError = firstKey && Array.isArray(apiErrors[firstKey]) ? apiErrors[firstKey][0] : null
      profileError.value = firstError || 'Failed to update profile.'
    } else {
      profileError.value = 'Failed to update profile.'
    }
  } finally {
    isSavingProfile.value = false
  }
}

// modal controls are exposed further below together with avatar picker

// Expose global avatar picker so parent components can open file dialog
const globalAvatarInput = ref(null)
function openAvatarPicker() {
  try {
    if (globalAvatarInput && globalAvatarInput.value) globalAvatarInput.value.click()
  } catch (e) {}
}

defineExpose({ openInfoModal, openEditProfile, openAvatarPicker })

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (!(await window.swalConfirm('Are you sure you want to change your profile picture?'))) return

  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    await new Promise(resolve => setTimeout(resolve, 100))

    function getCookie(name) {
      const m = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'))
      return m ? m[2] : null
    }

    const xsrf = getCookie('XSRF-TOKEN')
    const formData = new FormData()
    formData.append('avatar', file)

    if (xsrf) {
      try {
        formData.append('_token', decodeURIComponent(xsrf))
      } catch (_) {
        formData.append('_token', xsrf)
      }
    }

    const config = {
      headers: { 'Content-Type': 'multipart/form-data' },
      withCredentials: true
    }

    if (xsrf) {
      try {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrf)
      } catch (_) {
        config.headers['X-XSRF-TOKEN'] = xsrf
      }
    }

    const endpoint = getAvatarEndpoint()
    const res = await axios.post(endpoint, formData, config)

    if (res.data && res.data.ok) {
      localProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
      emit('profile-updated', localProfile.value)
      alert('Profile picture updated successfully!')
    }
  } catch (e) {
    console.error('Avatar upload failed:', e)
    alert(e.response?.data?.message || 'Failed to upload profile picture. Please try again.')
  }
}
</script>

<style scoped>
@import '../css/adminpanel.css';

:global(.owner-panel-light-mode) {
  --color-royal-blue: #ff6b1c;
  --color-golden-yellow: #ffd66b;
  --color-deep-navy: #42210b;
  --bg-main: radial-gradient(circle at center, #ffffff 0%, #fcfcfc 40%, #efefef 100%);
  --surface-card: #ffffff;
  --border-stroke: #f0e9e0;
  --text-dark: #42210b;
  --text-primary: #42210b;
  --text-secondary: rgba(66, 33, 11, 0.6);
  color: #42210b;
}

.admin-page--wider {
  max-width: 100%;
  padding: 0;
}

.admin-main-header-top-inner h1 { margin: 0 0 0.25rem 0 }
.owner-panel-layout--embedded {
  min-height: 0;
  background: transparent;
}

.owner-panel-layout--embedded .admin-page,
.owner-panel-layout--embedded .admin-layout {
  min-height: 0;
  background: transparent;
}

.owner-panel-layout--embedded .admin-layout {
  display: block;
}

.owner-panel-layout--embedded .admin-main {
  width: 100%;
  min-height: 0;
  padding: 0;
}

.owner-panel-layout--embedded .back-to-dashboard-btn {
  display: none;
}
.admin-main-header-top-inner p { margin: 0; color: #475569 }

.panel-notification-toolbar {
  display: flex;
  justify-content: flex-end;
  margin: 0 0 1rem;
}

.panel-notification-details {
  margin: 0 0 1rem;
  padding: 1rem 1.1rem;
  border: 1px solid rgba(218, 190, 168, 0.55);
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 5px 18px rgba(91, 59, 39, 0.06);
}

.panel-notification-details__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.7rem;
}

.panel-notification-details__header h2 {
  margin: 0;
  color: #3d2a1f;
  font-size: 1.05rem;
}

.panel-notification-details__header button {
  padding: 0.4rem 0.65rem;
  border: 1px solid #e7d9cf;
  border-radius: 7px;
  background: #fffaf5;
  color: #49372b;
  cursor: pointer;
}

.panel-notification-details__list {
  display: grid;
  gap: 0.65rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.panel-notification-details__list li {
  display: grid;
  gap: 0.25rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid #f0e8e1;
}

.panel-notification-details__list small {
  color: #806e62;
  font-size: 0.8rem;
}

.panel-notification-details__list p {
  margin: 0;
}

.panel-notification-details__counts li {
  grid-template-columns: 1fr auto;
  align-items: center;
}

.owner-announcement-backdrop {
  z-index: 600;
  background: rgba(15, 23, 42, 0.28);
  -webkit-backdrop-filter: blur(3px);
  backdrop-filter: blur(3px);
}

.owner-announcement-modal {
  width: min(90vw, 640px);
}

.owner-announcement-modal .owner-announcement-list {
  display: block;
  max-height: min(55vh, 480px);
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.owner-announcement-entry {
  padding: 0.85rem 0;
  border-bottom: 1px solid rgba(219, 188, 160, 0.3);
}

.owner-announcement-entry:last-child {
  border-bottom: 0;
}

.owner-announcement-entry strong {
  display: block;
  color: #3d2a1f;
  font-size: 0.92rem;
}

.owner-announcement-entry small {
  display: block;
  margin-top: 0.25rem;
  color: #94735f;
  font-size: 0.75rem;
}

.owner-announcement-entry p {
  margin: 0.45rem 0 0;
  color: #5f4b3e;
  font-size: 0.86rem;
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.owner-announcement-state {
  margin: 0;
  padding: 0.75rem 0;
  color: #64748b;
  font-size: 0.86rem;
}

@media (max-width: 767px) {
}

/* When a headerLeft slot is used, make it span full width so
  the title sits below the left content (e.g., back button). */
.header-left-slot { flex-basis: 100%; display: block; margin-bottom: 0.5rem; position: relative; z-index: 130; }

.admin-layout--wider {
  display: grid;
  grid-template-columns: 1fr;
  width: 100%;
  min-height: 100vh;
  border-radius: 0;
  padding: 1.5rem;
  gap: 1.5rem;
  margin: 0 auto;
}

.admin-layout--wider .admin-main {
  width: 100%;
}

.admin-layout--owner-two-column {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
  gap: 20px;
}

.admin-layout--owner-sidebar-layout {
  --owner-sidebar-width: 156px;
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
  border-radius: 0;
  border: 0;
  grid-template-columns: var(--owner-sidebar-width) minmax(0, 1fr);
  grid-template-rows: auto 1fr;
  gap: 0;
}

.owner-panel-topbar {
  grid-column: 2 / -1;
  grid-row: 1;
  position: relative;
  z-index: 300;
  display: flex;
  align-items: center;
  gap: 1rem;
  min-height: 66px;
  padding: 0.75rem 1rem;
  box-sizing: border-box;
  border-bottom: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(255, 255, 255, 0.22);
}

.owner-panel-hamburger {
  display: grid;
  width: 34px;
  height: 34px;
  place-items: center;
  padding: 0;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 10px;
  color: #334155;
  background: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  position: relative;
  z-index: 301;
  pointer-events: auto;
  transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1), background-color 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
}

.owner-sidebar-collapsed .owner-panel-hamburger {
  transform: rotate(180deg);
  position: fixed;
  top: 12px;
  left: 12px;
  z-index: 10000;
}

.owner-panel-topbar-spacer {
  flex: 1;
}

.owner-panel-user-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.45rem 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 999px;
  color: #1f2937;
  background: rgba(255, 255, 255, 0.58);
  font-size: 0.82rem;
  font-weight: 600;
}

.owner-panel-message-button {
  position: relative;
  display: grid;
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 50%;
  color: #334155;
  background: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  transition: background-color 160ms ease, box-shadow 160ms ease;
}

.owner-panel-message-button:hover,
.owner-panel-message-button:focus-visible {
  background: #fff;
  box-shadow: 0 4px 12px rgba(36, 52, 71, 0.12);
  outline: none;
}

.owner-panel-message-button__badge {
  position: absolute;
  top: -5px;
  right: -5px;
  display: grid;
  min-width: 19px;
  height: 19px;
  place-items: center;
  padding: 0 4px;
  border: 2px solid #f4ebe4;
  border-radius: 999px;
  color: #fff;
  background: #ef4444;
  font-size: 0.65rem;
  font-weight: 800;
  line-height: 1;
}

.owner-panel-user-pill__avatar {
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

.info-modal--finance-style {
  position: relative;
  width: min(90vw, 520px);
  max-height: 80vh;
  padding: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: linear-gradient(180deg, #ffffff 0%, #fef8f3 100%);
  border: 1px solid rgba(219, 188, 160, 0.4);
  border-radius: 20px;
  box-shadow: 0 28px 72px rgba(15, 23, 42, 0.18);
}

.info-backdrop--finance-style {
  background: rgba(0, 0, 0, 0.42);
  z-index: 500;
}

.info-modal--finance-style h3 {
  flex: 0 0 auto;
  margin: 0;
  padding: 1.25rem 1.5rem 0.35rem;
  color: #3d2a1f;
  font-size: 1.15rem;
  font-weight: 800;
}

.info-modal--finance-style .info-sub {
  flex: 0 0 auto;
  margin: 0;
  padding: 0 1.5rem 1rem;
  color: #94735f;
  border-bottom: 1px solid rgba(219, 188, 160, 0.3);
}

.info-modal--finance-style .info-grid {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  padding: 0.75rem 1.5rem;
  background: transparent;
}

.info-modal--finance-style .info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid rgba(219, 188, 160, 0.3);
}

.info-modal--finance-style .info-row + .info-row {
  border-top: 0;
  margin-top: 0;
  padding-top: 0.65rem;
}

.info-modal--finance-style .info-label {
  color: #7c6758;
  font-weight: 700;
}

.info-modal--finance-style .info-value {
  color: #3d2a1f;
  font-weight: 700;
  text-align: right;
}

.info-modal--finance-style .info-actions {
  flex: 0 0 auto;
  margin: 0;
  padding: 0.75rem 1.5rem 1rem;
  border-top: 1px solid rgba(219, 188, 160, 0.3);
}

.info-modal-close {
  position: absolute;
  top: 1rem;
  right: 1.25rem;
  width: 32px;
  height: 32px;
  padding: 0;
  border: 0;
  background: transparent;
  color: #94735f;
  font-size: 1.5rem;
  cursor: pointer;
}

.info-modal-close:hover {
  color: #3d2a1f;
  transform: scale(1.1);
}

.owner-sidebar-collapsed.admin-layout--owner-sidebar-layout {
  grid-template-columns: 0 minmax(0, 1fr);
}

.owner-sidebar-collapsed .owner-panel-sidebar {
  width: 0;
  min-width: 0;
  padding-left: 0;
  padding-right: 0;
  overflow: hidden;
  opacity: 0;
}

.admin-layout--owner-sidebar-layout .owner-panel-sidebar {
  position: relative;
  grid-column: 1;
  grid-row: 1 / -1;
  width: var(--owner-sidebar-width);
  min-height: 100vh;
  padding: 1.5rem 1rem 1rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  background: rgba(255, 255, 255, 0.42);
  border-right: 1px solid rgba(138, 113, 95, 0.18);
}

.admin-layout--owner-sidebar-layout:not(.owner-sidebar-collapsed) .owner-panel-sidebar {
  width: var(--owner-sidebar-width);
  min-width: var(--owner-sidebar-width);
  padding-left: 1rem;
  padding-right: 1rem;
  opacity: 1;
  pointer-events: auto;
}

.owner-panel-sidebar__resize-handle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 3;
  width: 10px;
  padding: 0;
  border: 0;
  background: transparent;
  cursor: col-resize;
}

.owner-panel-sidebar__resize-handle::after {
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

.owner-panel-sidebar__resize-handle:hover::after,
.owner-panel-sidebar__resize-handle:focus-visible::after {
  height: 64px;
  background: #8f4f2f;
}

.owner-sidebar-resizing .owner-panel-sidebar,
.owner-sidebar-resizing .owner-panel-topbar,
.owner-sidebar-resizing .admin-main {
  transition: none !important;
}

.owner-sidebar-collapsed .owner-panel-sidebar__resize-handle { display: none; }

.admin-layout--owner-sidebar-layout .owner-panel-sidebar,
.admin-layout--owner-sidebar-layout .owner-panel-sidebar * {
  min-width: 0;
  box-sizing: border-box;
}

.admin-layout--owner-sidebar-layout .owner-panel-sidebar {
  overflow-x: hidden;
  overflow-y: auto;
}

.admin-layout--owner-sidebar-layout .owner-sidebar-nav,
.admin-layout--owner-sidebar-layout .owner-sidebar-actions,
.admin-layout--owner-sidebar-layout .owner-sidebar-link,
.admin-layout--owner-sidebar-layout .owner-sidebar-account,
.admin-layout--owner-sidebar-layout .owner-sidebar-logout {
  max-width: 100%;
  min-width: 0;
  white-space: normal;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.owner-panel-light-mode .owner-panel-sidebar {
  background: #f3e9e1 !important;
  color: #243447 !important;
  border-right-color: #d8c8bc !important;
}

.owner-panel-light-mode .owner-panel-topbar {
  background: #eee2d9 !important;
  border-bottom-color: #d8c8bc !important;
}

.owner-panel-light-mode .owner-sidebar-footer {
  border-top-color: #d8c8bc !important;
}

.owner-sidebar-footer {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(138, 113, 95, 0.16);
}

.admin-layout--owner-sidebar-layout .admin-main {
  grid-column: 2;
  grid-row: 2;
}

.admin-layout--owner-sidebar-layout .admin-side {
  display: none;
}

.admin-layout--owner-two-column .admin-side {
  grid-column: 2;
  width: 100%;
}

.admin-layout--owner-two-column.admin-layout--wider .admin-side,
.admin-layout--owner-two-column .admin-side {
  display: block;
}

.admin-layout--wider .admin-side {
  display: none;
}

.announcements-panel {
  background: var(--surface-card);
  color: var(--text-primary);
  border-radius: 12px;
  padding: 0.75rem 0.75rem;
  border: 1px solid var(--border-stroke);
  box-shadow: 0 8px 24px rgba(16,24,40,0.06);
}
.announcements-panel .panel-header {
  background: transparent;
  padding: 0;
}
.announcements-panel .announcement-list { list-style: none; margin: 0; padding: 0; }
.announcements-panel .announcement-item { padding: 0.75rem; border-bottom: 1px solid #f1f1f1; border-radius: 8px; background: transparent; word-break: break-word; }
.announcements-panel .announcement-item:last-child { border-bottom: none; }
.announcements-panel .announcement-title { font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
.announcements-panel .announcement-meta { font-size: 0.8rem; color: #64748b; margin-bottom: 0.25rem; }
.announcements-panel .announcement-message { font-size: 0.95rem; color: #475569; white-space: normal; overflow-wrap: anywhere; word-break: break-word; }

/* Avatar controls inside Info modal */
.info-avatar-row { display:flex; gap:12px; align-items:center; padding-bottom:8px }
.info-avatar { width:72px; height:72px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid #eee }
.info-avatar img { width:100%; height:100%; object-fit:cover }
.info-avatar-initials { font-weight:700; color:#374151 }
.info-avatar-actions { display:flex; flex-direction:column; gap:8px }
.info-avatar-actions .btn-outline { padding:6px 10px }

/* Position the header/profile control inside the announcements card (top-right) */
.announcements-panel .announcements-header { position: relative }
/* cleaned: announcements header no longer contains proxy avatar button */
.announcements-panel .announcements-header h2 { margin-right: 0 }

/* When profile column hidden, float the header action to the top-right of the layout */
:deep(.admin-layout.no-profile-column) { position: relative }
:deep(.admin-layout.no-profile-column) .header-actions-top {
  position: relative;
  right: auto;
  top: auto;
  z-index: 120;
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}
:deep(.admin-layout.no-profile-column) .header-actions-top .header-profile-wrapper {
  position: relative;
}
:deep(.header-actions-top .header-profile-btn) { display: inline-flex; align-items: center }

/* Keep grid columns stable when profile column is hidden so main content
   doesn't jump width when toggling the left column. Reserve a right-side
   column for side panels (announcements) so they remain on the right. */
:deep(.admin-layout.no-profile-column) {
  grid-template-columns: 1fr minmax(260px, 360px);
  gap: 1rem;
}
:deep(.admin-layout.admin-layout--wider.no-profile-column),
:deep(.admin-layout.no-profile-column.admin-layout--wider) {
  grid-template-columns: minmax(0, 1fr) minmax(260px, 360px) !important;
  gap: 24px !important;
}
:deep(.admin-layout.no-profile-column) .admin-main {
  width: 100%;
}
:deep(.admin-layout.no-profile-column) .admin-side {
  width: 360px;
}

/* Keep the owner shell two-column when its profile column is disabled. */
:deep(.admin-layout--owner-sidebar-layout.no-profile-column) {
  grid-template-columns: var(--owner-sidebar-width) minmax(0, 1fr) !important;
  gap: 0 !important;
}

:deep(.owner-sidebar-collapsed.admin-layout--owner-sidebar-layout.no-profile-column) {
  grid-template-columns: 0 minmax(0, 1fr) !important;
}

/* Ensure announcements sit below the header profile button when the
   profile column is hidden (move panel down a bit to avoid overlap). */
:deep(.admin-layout.no-profile-column) .announcements-panel {
  margin-top: 40px;
}

/* Small avatar-only button inside announcements (proxies to header slot) */
/* announcements avatar styles removed */

.header-actions-top {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.header-actions-top .header-profile-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (min-width: 640px) {
  .admin-layout--wider {
    padding: 1.5rem 1.5rem;
  }
}

@media (max-width: 900px) {
  .admin-layout--owner-sidebar-layout {
    grid-template-columns: var(--owner-sidebar-width) minmax(0, 1fr);
  }

  .owner-sidebar-collapsed.admin-layout--owner-sidebar-layout {
    grid-template-columns: 0 minmax(0, 1fr);
  }

  .admin-layout--owner-sidebar-layout .owner-panel-sidebar {
    width: var(--owner-sidebar-width);
  }

  .admin-layout--owner-sidebar-layout .admin-main {
    grid-column: 2;
  }

  .admin-layout--owner-sidebar-layout .admin-side {
    grid-column: 2;
  }
}

@media (max-width: 767px) {
  .admin-layout--owner-sidebar-layout {
    grid-template-columns: 1fr;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-sidebar,
  .admin-layout--owner-sidebar-layout .admin-main,
  .admin-layout--owner-sidebar-layout .admin-side {
    grid-column: 1;
    width: 100%;
  }

}

@media (min-width: 1024px) {
  .admin-layout--wider {
    padding: 1.5rem 2.5rem;
  }
}

.header-actions-side {
  display: flex;
  justify-content: flex-end;
  width: 100%;
  margin-bottom: 1rem;
}

:deep(.admin-layout--fit-content .admin-side) {
  position: static;
  max-height: none;
  overflow: visible;
}

@media (min-width: 1000px) {
  /* Make side column sticky so announcements never overlay main content
     while allowing the main panel to scroll independently. */
  :deep(.admin-side) {
    position: sticky;
    top: 96px;
    align-self: start;
    max-height: calc(100vh - 120px);
    overflow: visible;
    padding-right: 8px;
  }

  :deep(.announcements-panel) {
    max-height: calc(100vh - 160px);
    overflow: auto;
  }
}

/* Header profile dropdown styles (shared) */
.header-profile-wrapper { position:relative; display:flex; align-items:center }
.header-profile-btn { display:flex; gap:8px; align-items:center; background:transparent; border:none; cursor:pointer; padding:6px 8px; border-radius:8px }
.header-avatar { width:36px; height:36px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f3f4f6 }
.header-avatar-img { width:100%; height:100%; background-size:cover; background-position:center }
.header-avatar-initials { font-weight:700; color:#374151 }
.header-name { font-weight:700; color:#333; font-size:0.86rem }
.header-profile-dropdown { position:absolute; right:0; top:46px; background:#fff; border-radius:8px; box-shadow:0 8px 24px rgba(16,24,40,0.12); padding:6px; min-width:160px; z-index:100200 }
.dropdown-item { display:block; width:100%; text-align:left; padding:8px 12px; background:transparent; border:none; color:#374151; cursor:pointer }
.dropdown-item:hover { background:#f7f7f8 }

@media (max-width: 767px) {
  .admin-layout--owner-sidebar-layout {
    display: block;
    position: relative;
    width: 100% !important;
    height: 100vh;
    min-height: 100vh;
    overflow-x: hidden;
    overflow-y: hidden;
  }

  .admin-page:has(.admin-layout--owner-sidebar-layout),
  .admin-page:has(.admin-layout--owner-sidebar-layout) .admin-layout--owner-sidebar-layout,
  .admin-layout--owner-sidebar-layout .admin-main {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    margin: 0 !important;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-topbar {
    position: relative;
    z-index: 300;
    width: 100%;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-sidebar {
    position: absolute;
    inset: 66px auto 0 0;
    z-index: 200;
    width: var(--owner-sidebar-width);
    min-height: 100%;
    transform: translateX(0);
    transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 180ms ease;
  }

  .admin-layout--owner-sidebar-layout:not(.owner-sidebar-collapsed) .owner-panel-sidebar {
    width: var(--owner-sidebar-width);
    min-width: var(--owner-sidebar-width);
    padding-left: 1rem;
    padding-right: 1rem;
    transform: translateX(0);
    opacity: 1;
    pointer-events: auto;
  }

  .owner-sidebar-collapsed.admin-layout--owner-sidebar-layout .owner-panel-sidebar {
    width: var(--owner-sidebar-width);
    transform: translateX(-100%);
    opacity: 0;
    pointer-events: none;
  }

  .admin-layout--owner-sidebar-layout .owner-sidebar-nav {
    display: flex;
    flex-direction: column;
    grid-template-columns: none;
    gap: 0.5rem;
  }

  .admin-layout--owner-sidebar-layout .admin-main {
    width: 100%;
    overflow-x: hidden;
  }

  .admin-layout--owner-sidebar-layout .owner-main-section,
  .admin-layout--owner-sidebar-layout .owner-hero-card,
  .admin-layout--owner-sidebar-layout .owner-dish-section {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
  }

  .admin-layout--owner-sidebar-layout .owner-hero-content,
  .admin-layout--owner-sidebar-layout .owner-hero-left,
  .admin-layout--owner-sidebar-layout .owner-hero-stats {
    min-width: 0;
    max-width: 100%;
  }

  .admin-layout--owner-sidebar-layout .owner-hero-stats {
    display: grid;
    grid-template-columns: 1fr;
  }

  .admin-layout--owner-sidebar-layout .owner-dish-section .ingredient-row {
    display: flex;
    flex-wrap: wrap;
  }

  .admin-layout--owner-sidebar-layout .owner-dish-section .ingredient-row > * {
    flex: 1 1 100%;
    min-width: 0;
    max-width: 100%;
  }
}

@media (min-width: 768px) {
  .admin-layout--owner-sidebar-layout {
    display: block;
    position: relative;
    width: 100% !important;
    min-height: 100vh;
    overflow-x: hidden;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 400;
    width: var(--owner-sidebar-width);
    min-width: var(--owner-sidebar-width);
    min-height: 100vh;
    transform: translateX(0);
    transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 180ms ease;
  }

  .owner-sidebar-collapsed.admin-layout--owner-sidebar-layout .owner-panel-sidebar {
    width: var(--owner-sidebar-width);
    min-width: var(--owner-sidebar-width);
    transform: translateX(-100%);
    opacity: 0;
    pointer-events: none;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-topbar,
  .admin-layout--owner-sidebar-layout .admin-main {
    width: calc(100% - var(--owner-sidebar-width));
    margin-left: var(--owner-sidebar-width);
    transition: width 260ms cubic-bezier(0.22, 1, 0.36, 1), margin-left 260ms cubic-bezier(0.22, 1, 0.36, 1);
  }

  .owner-sidebar-collapsed.admin-layout--owner-sidebar-layout .owner-panel-topbar,
  .owner-sidebar-collapsed.admin-layout--owner-sidebar-layout .admin-main {
    width: 100%;
    margin-left: 0;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-topbar {
    height: 66px;
    min-height: 66px;
  }

  .admin-layout--owner-sidebar-layout .admin-main {
    height: calc(100vh - 66px);
    box-sizing: border-box;
    padding: 1.2rem 1.3rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    overflow-x: hidden;
    overflow-y: auto;
    overscroll-behavior: contain;
  }

  .admin-layout--owner-sidebar-layout .admin-main > .owner-main-section,
  .admin-layout--owner-sidebar-layout .admin-main > .owner-dish-section {
    width: 100%;
    max-width: 100%;
  }

  .admin-layout--owner-sidebar-layout .admin-main > .owner-main-section {
    margin-bottom: 0;
  }

  .admin-layout--owner-sidebar-layout .admin-main > * {
    flex: 0 0 auto;
  }
}

@media (max-width: 1023px) {
  .admin-layout--owner-sidebar-layout {
    display: block;
    position: relative;
    width: 100% !important;
    min-height: 100vh;
    overflow-x: hidden;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-topbar,
  .admin-layout--owner-sidebar-layout .admin-main {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-topbar {
    position: relative;
    left: auto;
    z-index: 300;
  }

  .admin-layout--owner-sidebar-layout .owner-panel-sidebar {
    position: absolute;
    inset: 66px auto 0 0;
    z-index: 400;
    width: var(--owner-sidebar-width);
    min-width: var(--owner-sidebar-width);
    min-height: calc(100vh - 66px);
    transform: translateX(0);
    transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 180ms ease;
  }

  .owner-sidebar-collapsed.admin-layout--owner-sidebar-layout .owner-panel-sidebar {
    transform: translateX(-100%);
    opacity: 0;
    pointer-events: none;
  }

  .admin-layout--owner-sidebar-layout .admin-main {
    height: auto;
    max-height: none;
    padding: 1rem;
    overflow-x: hidden;
    overflow-y: visible;
  }

  .admin-layout--owner-sidebar-layout .admin-main > *,
  .admin-layout--owner-sidebar-layout .admin-main > * * {
    max-width: 100%;
    min-width: 0;
  }

  .admin-layout--owner-sidebar-layout input,
  .admin-layout--owner-sidebar-layout select,
  .admin-layout--owner-sidebar-layout textarea,
  .admin-layout--owner-sidebar-layout button {
    max-width: 100%;
  }
}

/* Embedded owner workflows must use the dashboard's existing content column. */
.owner-panel-layout--embedded .admin-page,
.owner-panel-layout--embedded .admin-layout,
.owner-panel-layout--embedded .admin-main {
  width: 100% !important;
  max-width: none !important;
  min-width: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
}

.owner-panel-layout--embedded .admin-layout {
  display: grid !important;
  grid-template-columns: minmax(120px, 156px) minmax(0, 1fr) !important;
  grid-template-rows: minmax(0, 1fr) !important;
  align-items: start;
  min-height: 0 !important;
  height: auto !important;
  gap: 0 !important;
  overflow: visible !important;
}

.owner-panel-layout--embedded .admin-layout--owner-sidebar-layout {
  grid-template-columns: minmax(120px, 156px) minmax(0, 1fr) !important;
}

.owner-panel-layout--embedded .admin-main {
  display: block !important;
  grid-column: 2 !important;
  grid-row: 1 !important;
  min-height: 0 !important;
  height: auto !important;
  overflow: visible !important;
}

.owner-panel-layout--embedded .owner-panel-sidebar {
  position: relative !important;
  grid-column: 1 !important;
  grid-row: 1 !important;
  display: flex !important;
  width: 100% !important;
  min-width: 0 !important;
  min-height: calc(100vh - 100px) !important;
  height: auto !important;
  padding: 1rem 0.6rem !important;
  transform: none !important;
  opacity: 1 !important;
  overflow-x: hidden !important;
  overflow-y: auto !important;
}

.owner-panel-layout--embedded .owner-panel-topbar {
  display: none !important;
}

:deep(.owner-panel-layout--embedded .back-to-dashboard-btn) {
  display: none !important;
}

:deep(.owner-panel-layout--embedded .dish-approval-page) {
  min-height: 0 !important;
  padding: 0 !important;
  overflow-x: hidden;
}
</style>
