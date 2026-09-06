<template>
  <div class="inventory-staff-panel-shell">
  <OwnerPanelLayout
    ref="ownerLayout"
    :userProfile="staffProfile"
    :panelTitle="pageTitle"
    :enableProfileUpdate="true"
    :showProfileColumn="false"
    :ownerTwoColumnLayout="true"
    :showOwnerSidebar="true"
    :showOwnerTopbar="true"
    :enableDarkMode="false"
    :showAnnouncements="false"
    :showAttendanceCard="!isAttendanceView"
    accountInfoStyle="finance"
    @logout="logout"
  >
    <template #ownerSidebar>
      <nav class="owner-sidebar-nav inventory-sidebar-nav" aria-label="Inventory sections">
        <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': !isDisposalView && !isAttendanceView && !isAnnouncementsView }" @click="isDisposalView = false; isAttendanceView = false; isAnnouncementsView = false">Inventory</button>
        <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': isDisposalView }" @click="isDisposalView = true; isAttendanceView = false; isAnnouncementsView = false">Product Disposal</button>
        <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': isAttendanceView }" @click="isDisposalView = false; isAttendanceView = true; isAnnouncementsView = false">Attendance</button>
        <button type="button" class="owner-sidebar-link" :class="{ 'owner-sidebar-link--active': isAnnouncementsView }" @click="isDisposalView = false; isAttendanceView = false; isAnnouncementsView = true">Announcements</button>
      </nav>
    </template>

    <template #ownerSidebarFooter>
      <div class="owner-sidebar-actions inventory-sidebar-actions">
        <button type="button" class="owner-sidebar-account" @click="ownerLayout?.openInfoModal()">
          Account Info
        </button>
        <button type="button" class="owner-sidebar-logout" @click="logout">
          Logout
        </button>
      </div>
    </template>

    <template #sideTop>
      <div class="header-profile-wrapper" ref="headerProfileWrapper" style="margin: 0 0 12px;">
        <button class="header-profile-btn" @click.stop="toggleProfileMenu" type="button">
          <div class="header-avatar">
            <div v-if="staffProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url(' + staffProfile.avatarUrl + ')' }"></div>
            <div v-else class="header-avatar-initials">{{ staffProfile.fullName ? (staffProfile.fullName.charAt(0) || 'U') : 'U' }}</div>
          </div>
          <div class="header-name">{{ ((staffProfile.fullName || staffProfile.full_name) || ((staffProfile.role || 'STAFF') + (staffProfile.branch_name ? ' - ' + staffProfile.branch_name : (staffProfile.branch ? ' - ' + staffProfile.branch : '')) )).toUpperCase() }}</div>
        </button>
        <input id="staff-avatar-input" type="file" accept="image/*" @change="onAvatarChange" style="display: none" />

        <!-- Profile dropdown -->
        <div v-if="showProfileMenu" class="profile-dropdown" ref="profileDropdown" @click.stop>
          <button class="dropdown-item" @click="openInfoFromMenu">Info</button>
          <button class="dropdown-item" @click="openLogoutFromMenu">Logout</button>
        </div>
      </div>
    </template>

    <template #main>
      <Transition name="inventory-section" mode="out-in">
        <div :key="isAnnouncementsView ? 'announcements' : (isAttendanceView ? 'attendance' : (isDisposalView ? 'product-disposal' : 'inventory-monitor'))" class="inventory-section-view">
      <!-- Dashboard stats (match manager panel) -->
      <div v-if="!isDisposalView && !isAttendanceView && !isAnnouncementsView" class="hr-stats-grid">
        <div class="hr-stat-card hr-stat-card--total">
          <div class="hr-stat-icon">●</div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Total Products</span>
            <span class="hr-stat-value">{{ dashboardTotals.totalProducts }}</span>
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--active">
          <div class="hr-stat-icon">●</div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Low Stock</span>
            <span class="hr-stat-value">{{ dashboardTotals.lowStock }}</span>
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--leave" :class="{ 'stat-alert': inventoryPendingCount > 0 }">
          <div class="hr-stat-icon">●</div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Pending Requests</span>
            <span class="hr-stat-value">{{ dashboardTotals.pendingRequests }}</span>
          </div>
          <span v-if="inventoryPendingCount > 0" class="panel-badge">{{ inventoryPendingCount }}</span>
        </div>
      </div>

      <!-- Inventory Monitor (manager-style table) -->
      <div v-if="!isDisposalView && !isAttendanceView && !isAnnouncementsView" class="panel-section">
        <h2 class="section-title">Inventory Monitor</h2>
        <p class="section-description">Current stock levels for your branch (Read-only)</p>
        <p class="section-note" style="color: #666; font-size: 13px; margin-top: 8px;">💡 <strong>Request Procurement:</strong> Click the button in the table to automatically request <strong>minimum 10 units</strong> of any low-stock product.</p>

        <div v-if="inventoryLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading inventory...</p>
        </div>

        <div v-else-if="inventoryError" class="error-container">
          <p class="error-message">{{ inventoryError }}</p>
          <button class="btn-retry" @click="fetchInventory">Retry</button>
        </div>

        <div v-else>
          <ProductList :fetchUrl="fetchUrl" :compact="true" :showPublishControls="(staffProfile.role || '').toUpperCase() === 'ADMIN'" ref="productListRef" @edit="handleEdit" @delete="deleteProduct" @toggle-publish="handleTogglePublish" @request-procurement="requestProcurement" @report-expired="openExpiredReportModal" />
        </div>
      </div>

      <div v-if="isAttendanceView" class="inventory-main-attendance-card">
        <div class="attendance-card">
          <div class="attendance-header">
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
            <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockInGeofencing || locationLoading" class="btn-clock-in">
              {{ (isAttendanceProcessing || locationLoading) ? '...' : 'Clock In' }}
            </button>
            <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut || !canClockInGeofencing || locationLoading" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">
              {{ (isAttendanceProcessing || locationLoading) ? '...' : 'Clock Out' }}
            </button>
          </div>
          <div v-if="locationError" class="geofencing-status geofencing-error">
            <span class="status-icon">⚠️</span>
            <span>{{ locationError }}</span>
          </div>
          <div v-else-if="userLocation && canClockInGeofencing" class="geofencing-status geofencing-success">
            <span class="status-icon">✓</span>
            <span>Location verified</span>
          </div>
          <div v-else-if="!canClockInGeofencing && geofencingMessage" class="geofencing-status geofencing-error">
            <span class="status-icon">🔒</span>
            <span>{{ geofencingMessage }}</span>
          </div>
          <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction">
            <span class="restriction-icon">LOCK</span>
            <span>Cannot clock out before {{ scheduledTimeOut }}</span>
          </div>
          <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
        </div>
      </div>

      <div v-if="isAnnouncementsView" class="panel-section inventory-announcements-section">
        <h2 class="section-title">Announcements</h2>
        <p class="section-description">Latest announcements for your account</p>
        <div v-if="loadingAnnouncements" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading announcements...</p>
        </div>
        <div v-else-if="announcements.length === 0" class="empty-container">
          <p>No announcements found.</p>
        </div>
        <div v-else class="inventory-announcement-list">
          <article v-for="announcement in announcements" :key="announcement.id" class="inventory-announcement-item">
            <h3>{{ announcement.title }}</h3>
            <p class="inventory-announcement-meta">{{ new Date(announcement.created_at).toLocaleString() }}<span v-if="announcement.target"> · {{ announcement.target }}</span></p>
            <p>{{ announcement.message }}</p>
          </article>
        </div>
      </div>

      <!-- Product Disposal List Section -->
      <div v-if="isDisposalView" class="panel-section disposal-only-section">
        <h2 class="section-title">Product Disposal List</h2>
        <p class="section-description">View and manage all expired product disposal reports</p>

        <div class="disposal-stats-row">
          <div class="disposal-stat-mini"><span class="disposal-stat-label">Total Disposals</span><span class="disposal-stat-value">{{ disposalStats.total }}</span></div>
          <div class="disposal-stat-mini"><span class="disposal-stat-label">Pending Review</span><span class="disposal-stat-value">{{ disposalStats.pending }}</span></div>
          <div class="disposal-stat-mini"><span class="disposal-stat-label">Resolved</span><span class="disposal-stat-value">{{ disposalStats.resolved }}</span></div>
          <div class="disposal-stat-mini"><span class="disposal-stat-label">Total Units</span><span class="disposal-stat-value">{{ disposalStats.totalQuantity }}</span></div>
        </div>

        <div class="disposal-controls">
          <div class="disposal-filters">
            <input v-model="disposalSearchQuery" @input="filterDisposals" type="text" placeholder="Search by product name or SKU..." class="disposal-search" />
            <select v-model="statusFilter" @change="filterDisposals" class="disposal-filter">
              <option value="all">All Status</option>
              <option value="pending">Pending Review</option>
              <option value="reviewed">Reviewed</option>
              <option value="resolved">Resolved</option>
            </select>
            <input v-model="dateFrom" @change="filterDisposals" type="date" class="disposal-date-filter" />
            <input v-model="dateTo" @change="filterDisposals" type="date" class="disposal-date-filter" />
          </div>
          <div class="disposal-actions">
            <button @click="exportDisposals" class="btn btn-light">Export CSV</button>
            <button @click="refreshDisposals" class="btn btn-primary" :disabled="isLoadingDisposals">Refresh</button>
          </div>
        </div>

        <div class="disposal-table-container">
          <div v-if="isLoadingDisposals" class="loading-container"><div class="loading-spinner"></div><p>Loading disposal records...</p></div>
          <div v-else-if="disposalError" class="error-container"><p class="error-message">{{ disposalError }}</p><button @click="refreshDisposals" class="btn-retry">Retry</button></div>
          <div v-else-if="filteredDisposals.length === 0" class="empty-container"><p>No disposal records found.</p></div>
          <table v-else class="disposal-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Reported By</th>
                <th>Notes</th>
                <th>Image</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="disposal in paginatedDisposals" :key="disposal.id" class="disposal-row">
                <td class="disposal-date">{{ formatDisposalDate(disposal.created_at) }}</td>
                <td class="disposal-product">{{ disposal.product_name }}</td>
                <td class="disposal-sku">{{ disposal.product_sku || 'N/A' }}</td>
                <td class="disposal-quantity">{{ disposal.quantity }}</td>
                <td class="disposal-reporter">{{ disposal.reported_by }}</td>
                <td class="disposal-notes"><span v-if="disposal.notes" class="notes-text" :title="disposal.notes">{{ truncateText(disposal.notes, 50) }}</span><span v-else class="no-notes">-</span></td>
                <td class="disposal-image"><img v-if="disposal.image_path" :src="disposal.image_path" :alt="disposal.product_name" class="disposal-thumb" @click="viewImage(disposal.image_path)" /><span v-else class="no-image">No image</span></td>
                <td>
                  <span :class="['status-badge', getDisposalStatusClass(disposal.status)]">{{ getDisposalStatusLabel(disposal.status) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="filteredDisposals.length > perPage" class="disposal-pagination">
          <button class="page-btn" :disabled="currentPage === 1" @click="currentPage = 1">First</button>
          <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">Prev</button>
          <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
          <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">Next</button>
          <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage = totalPages">Last</button>
          <select v-model.number="perPage" class="per-page-select"><option :value="10">10 / page</option><option :value="25">25 / page</option><option :value="50">50 / page</option></select>
        </div>

        <transition name="fade">
          <div v-if="showImageModal" class="image-modal-backdrop" @click.self="closeImageModal">
            <div class="image-modal"><button @click="closeImageModal" class="image-modal-close">x</button><img :src="selectedImage" alt="Disposal evidence" class="image-modal-img" /></div>
          </div>
        </transition>
      </div>

      <!-- Announcements removed per request -->
        </div>
      </Transition>
    </template>
  </OwnerPanelLayout>
  </div>

  <!-- INFO MODAL -->
  <transition name="fade">
    <div v-if="showInfoModal" class="info-backdrop">
      <div class="info-modal">
        <h3>Staff Information</h3>
        <p class="info-sub">Personal details for this staff can be updated from this panel.</p>
        <div class="info-grid">
          <div class="info-row"><span class="info-label">Full name</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.fullName }}</span>
            <input v-else v-model="staffProfile.fullName" class="info-input" type="text" />
          </div>
          <div class="info-row"><span class="info-label">Role</span><span class="info-value">{{ staffProfile.role }}</span></div>
          <div class="info-row"><span class="info-label">Username</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.username }}</span>
            <input v-else v-model="staffProfile.username" class="info-input" type="text" placeholder="Enter username" />
          </div>
          <div class="info-row"><span class="info-label">Email</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.email }}</span>
            <input v-else v-model="staffProfile.email" class="info-input" type="email" />
          </div>
          <div class="info-row"><span class="info-label">Contact</span><span class="info-value" v-if="!isEditingInfo">{{ staffProfile.contact }}</span>
            <input v-else v-model="staffProfile.contact" class="info-input" type="text" />
          </div>
          <!-- Password fields - only shown when editing -->
          <template v-if="isEditingInfo">
            <div class="info-row info-row--password">
              <span class="info-label">New Password</span>
              <input v-model="staffProfile.password" class="info-input" type="password" placeholder="Leave blank to keep current" />
            </div>
            <div class="info-row info-row--password">
              <span class="info-label">Confirm Password</span>
              <input v-model="staffProfile.password_confirmation" class="info-input" type="password" placeholder="Re-enter new password" />
            </div>
          </template>
        </div>
        <div v-if="profileError" class="info-error">{{ profileError }}</div>
        <div v-if="profileSuccess" class="info-success">{{ profileSuccess }}</div>
        <div class="info-actions">
          <button class="btn-outline" @click="handleInfoClose">{{ isEditingInfo ? ' Cancel' : 'Close' }}</button>
          <button class="btn-primary" @click="isEditingInfo ? saveStaffInfo() : (isEditingInfo = true)" :disabled="isSavingProfile">
            {{ isEditingInfo ? (isSavingProfile ? 'Saving...' : 'Save changes') : 'Edit information' }}
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- EXPIRED PRODUCT REPORT MODAL -->
  <transition name="fade">
    <div v-if="showExpiredReportModal" class="info-backdrop" @click.self="closeExpiredReportModal">
      <div class="info-modal" style="max-width: 600px;">
        <h3>Report Expired Product</h3>
        <p class="info-sub">Submit a report for the expired product: <strong>{{ expiredReportProduct?.name }}</strong></p>

        <div v-if="expiredReportError" class="info-error">{{ expiredReportError }}</div>
        <div v-if="expiredReportSuccess" class="info-success">{{ expiredReportSuccess }}</div>

        <div class="info-grid">
          <div class="info-row">
            <span class="info-label">Product:</span>
            <span class="info-value">{{ expiredReportProduct?.name }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">SKU:</span>
            <span class="info-value">{{ expiredReportProduct?.sku || 'N/A' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Expired Date:</span>
            <span class="info-value">{{ expiredReportProduct?.expires_at ? formatDate(expiredReportProduct.expires_at) : 'N/A' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Current Stock:</span>
            <span class="info-value">{{ expiredReportProduct?.stock || 0 }}</span>
          </div>

          <div class="info-row">
            <span class="info-label">Expired Quantity:</span>
            <div style="display: flex; align-items: center; gap: 8px;">
              <input
                v-model="expiredReportForm.quantity"
                type="number"
                min="1"
                :max="expiredReportProduct?.stock || 1"
                class="info-input"
                style="width: 120px;"
                @input="validateExpiredQuantity"
              />
              <button
                type="button"
                class="btn-outline"
                style="padding: 6px 12px; font-size: 0.85rem;"
                @click="autoFillExpiredQuantity"
                title="Auto-fill quantity from expired inventory lots"
              >
                Auto-Fill
              </button>
            </div>
            <span v-if="expiredReportError" style="color: #dc3545; font-size: 0.85rem; margin-left: 8px;">{{ expiredReportError }}</span>
            <div v-if="expiredReportProduct" style="font-size: 0.8rem; color: #666; margin-top: 4px;">
              Current stock: {{ expiredReportProduct.stock || 0 }} |
              <span v-if="expiredLotsSummary">Expired lots: {{ expiredLotsSummary.quantity }} units ({{ expiredLotsSummary.count }} lots)</span>
            </div>
          </div>

          <div class="info-row info-row--full">
            <span class="info-label">Report Notes:</span>
            <textarea
              v-model="expiredReportForm.notes"
              class="info-input"
              rows="4"
              placeholder="Describe the issue with the expired product..."
              style="width: 100%; resize: vertical;"
            ></textarea>
          </div>

          <div class="info-row info-row--full">
            <span class="info-label">Product Image: <span style="color: #dc3545;">*</span></span>
            <input
              type="file"
              accept="image/*"
              @change="onExpiredImageChange"
              class="info-input"
              style="width: 100%;"
              required
            />
            <div v-if="expiredReportForm.image" class="info-success" style="margin-top: 8px;">
              Image selected: {{ expiredReportForm.image.name }}
            </div>
            <div v-else-if="expiredReportError" style="color: #dc3545; font-size: 0.85rem; margin-top: 4px;">
              Product image is required
            </div>
          </div>
        </div>

        <div class="info-actions">
          <button class="btn-outline" @click="closeExpiredReportModal">Cancel</button>
          <button
            class="btn-primary"
            @click="submitExpiredReport"
            :disabled="expiredReportSubmitting"
          >
            {{ expiredReportSubmitting ? 'Submitting...' : 'Submit Report' }}
          </button>
        </div>
      </div>
    </div>
  </transition>

  <div v-if="showFaceCapture" class="face-capture-modal">
    <div class="face-capture-content">
      <h3>Take a Photo for Clock In</h3>
      <p class="face-capture-instruction">Please position your face in the frame and click capture</p>
      <div class="camera-container">
        <video ref="cameraVideo" autoplay playsinline></video>
        <canvas ref="cameraCanvas" style="display: none;"></canvas>
        <div v-if="cameraError" class="camera-error">{{ cameraError }}</div>
      </div>
      <div class="face-capture-buttons">
        <button @click="capturePhoto" :disabled="isCapturing || !!cameraError || !cameraStream" class="btn-capture">
          {{ isCapturing ? 'Capturing...' : 'Capture Photo' }}
        </button>
        <button v-if="cameraError" @click="startCamera" class="btn-capture">Try Again</button>
        <button @click="cancelFaceCapture" class="btn-cancel">Cancel</button>
      </div>
    </div>
  </div>
</template>

<style scoped src="./InventoryStaffPanel.css"></style>

<script setup>
import { ref, onMounted, watch, computed, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import OwnerPanelLayout from '../OwnerPanelLayout.vue'
import ProductList from './ProductList.vue'
import { showToast } from '../toastStore'

const router = useRouter();
const ownerLayout = ref(null);

const isDisposalView = ref(false);
const isAttendanceView = ref(false);
const isAnnouncementsView = ref(false);

// Back button logic
const showBackButton = computed(() => {
  return new URLSearchParams(window.location.search).get('from') === 'custom-panel'
})

function goBack() {
  // Check if there's a from parameter
  const params = new URLSearchParams(window.location.search)
  if (params.get('from') === 'custom-panel') {
    router.push({ path: '/custom-panel' })
  } else {
    router.back()
  }
}

const staffProfile = ref({
  avatarUrl: '',
  fullName: '',
  role: '',
  displayRole: 'Inventory Staff',
  username: '',
  email: '',
  contact: '',
  accountId: '',
  password: '',
  password_confirmation: ''
});
const isProfileLoading = ref(true);
const isEditingInfo = ref(false);
const isSavingProfile = ref(false);
const showInfoModal = ref(false);
const profileError = ref('');
const profileSuccess = ref('');
const showLogoutConfirm = ref(false);
const isLoggingOut = ref(false);

// Header/profile dropdown state
const showProfileMenu = ref(false);
const headerProfileWrapper = ref(null);
const profileDropdown = ref(null);

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
    return new URLSearchParams(window.location.search).get('from') === 'custom-panel' || isCustomAccount.value || isAttendanceView.value
  } catch (e) {
    return isCustomAccount.value || isAttendanceView.value
  }
})

// Inventory state
const inventory = ref([])
const inventoryLoading = ref(false)
const inventoryError = ref('')

// Per-product action loading states (e.g., requestProcurement)
const requesting = ref({})

// Attendance state variables
const attendanceStatus = ref({
  is_clocked_in: false,
  clock_in_time: null,
  clock_out_time: null,
  hours_worked: 0
});
const isAttendanceProcessing = ref(false);
const attendanceMessage = ref('');
const attendanceMessageType = ref('');

// Attendance settings state
const attendanceSettings = ref({
  early_clockout_override: false,
  scheduled_time_out: '17:00:00'
});

// Geofencing state
const userLocation = ref(null);
const locationLoading = ref(false);
const locationError = ref('');
const canClockInGeofencing = ref(true);
const geofencingMessage = ref('');
const showFaceCapture = ref(false);
const capturedImage = ref(null);
const cameraStream = ref(null);
const cameraError = ref('');
const isCapturing = ref(false);
const cameraVideo = ref(null);
const cameraCanvas = ref(null);
const notificationCounts = ref({ inventory: 0 })
const hasNotified = ref(false)
const inventoryPendingCount = computed(() => {
  const apiPending = Number(notificationCounts.value?.inventory || 0)
  const dashboardPending = Number(dashboardTotals.value?.pendingRequests || 0)
  const listPending = (procurementRequests.value || []).length
  return Math.max(apiPending, dashboardPending, listPending, 0)
})

// Computed property for scheduled time out display
const scheduledTimeOut = computed(() => {
  const time = attendanceSettings.value.scheduled_time_out || '17:00:00'
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const hour12 = hour % 12 || 12
  return `${hour12}:${minutes} ${ampm}`
})

// Computed property to check if clock out is allowed
const canClockOut = computed(() => {
  // If not clocked in, can't clock out
  if (!attendanceStatus.value.is_clocked_in) return false

  // If override is enabled, allow clock out
  if (attendanceSettings.value.early_clockout_override) return true

  // Get current time
  const now = new Date()
  const currentHours = now.getHours()
  const currentMinutes = now.getMinutes()

  // Get scheduled time out
  const [scheduledHours, scheduledMinutes] = (attendanceSettings.value.scheduled_time_out || '17:00:00').split(':')

  // Compare times
  const currentTotalMinutes = currentHours * 60 + currentMinutes
  const scheduledTotalMinutes = parseInt(scheduledHours) * 60 + parseInt(scheduledMinutes)

  // Allow clock out if current time >= scheduled time
  return currentTotalMinutes >= scheduledTotalMinutes
})

// optional products prop (we will rely on ProductList fetch by default)
const props = defineProps({
  products: { type: Array, default: () => [] },
  fetchUrl: { type: String, default: '/api/staff/inventory/products' },
  pageTitle: { type: String, default: 'Inventory' },
  isSuperAdmin: { type: Boolean, default: false }
});

// Internal products from parent (used when not fetching via API)
const internalProducts = ref(props.products || [])

// Watch for products prop changes
watch(() => props.products, (newProducts) => {
  internalProducts.value = newProducts || []
})

// Computed title
const pageTitle = computed(() => props.pageTitle)

// Compute API endpoints based on whether it's superadmin or not
const endpoints = computed(() => {
  if (props.isSuperAdmin) {
    return {
      products: '/api/superadmin/logistics/products',
      store: '/api/superadmin/logistics/products',
      update: (id) => `/api/superadmin/logistics/products/${id}`,
      destroy: (id) => `/api/superadmin/logistics/products/${id}`
    }
  }
  return {
    products: '/api/staff/inventory/products',
    store: '/api/staff/inventory/products',
    update: (id) => `/api/staff/inventory/products/${id}`,
    destroy: (id) => `/api/staff/inventory/products/${id}`
  }
})

// Also update the fetchUrl to use computed endpoints
// Include unpublished products for inventory management
const fetchUrl = computed(() => `${endpoints.value.products}?include_unpublished=1`)

// ref to the ProductList child so we can trigger refreshes
const productListRef = ref(null)

// Dashboard totals used by the header stats
const dashboardTotals = ref({ totalProducts: 0, lowStock: 0, pendingRequests: 0 })

// Procurement & Product Request state (staff)
const procurementRequests = ref([])
const procRequestsLoading = ref(false)
const procRequestForm = ref({ product_id: '', quantity: 1, request_budget: false, receipt: null, product_image: null })
const procRequestSubmitting = ref(false)
const procRequestFormError = ref('')
const showProcRequestForm = ref(false)

// Product request state (staff)
const productRequests = ref([])
const productRequestsLoading = ref(false)
const productRequestForm = ref({ name: '', description: '', unit: '' })
const productRequestSubmitting = ref(false)
const showProductRequestForm = ref(false)

async function fetchProcRequests() {
  procRequestsLoading.value = true
  try {
    const res = await axios.get('/api/procurement-requests', { withCredentials: true })
    const data = res.data?.data ?? res.data ?? []
    const arr = Array.isArray(data) ? data : []
    // deduplicate by id (avoid duplicates from multiple refreshes)
    const map = {}
    arr.forEach(item => { if (item && item.id) map[item.id] = item })
    procurementRequests.value = Object.values(map)
  } catch (e) {
    console.error('fetchProcRequests error', e)
    procurementRequests.value = []
  } finally {
    procRequestsLoading.value = false
  }
}

async function submitProcRequest() {
  procRequestFormError.value = ''

  // Validation
  if (!procRequestForm.value.product_id) {
    procRequestFormError.value = 'Please select a product'
    return
  }

  const quantity = Number(procRequestForm.value.quantity)
  if (!quantity || quantity <= 0) {
    procRequestFormError.value = 'Please enter a quantity greater than 0'
    return
  }

  procRequestSubmitting.value = true
  try {
    await ensureCsrf()
    // If the product has an associated supplier, include it so procurement managers
    // can acknowledge without requiring supplier-order submissions.
    // Build FormData for manual endpoint to support file uploads and budget flag
    const form = new FormData()
    form.append('product_id', procRequestForm.value.product_id)
    form.append('quantity', quantity)
    if (procRequestForm.value.request_budget) form.append('request_budget', '1')

    try {
      const prodRes = await axios.get(`/api/staff/inventory/products`, { withCredentials: true, params: { include_unpublished: 1 } })
      const allProducts = Array.isArray(prodRes.data) ? prodRes.data : (prodRes.data?.data || [])
      const selected = allProducts.find(p => Number(p.id) === Number(procRequestForm.value.product_id))
      if (selected && selected.supplier_id) form.append('supplier_id', selected.supplier_id)
    } catch (e) {
      // ignore; best-effort only
    }

    // attach files
    if (procRequestForm.value.receipt) form.append('receipt', procRequestForm.value.receipt)
    if (procRequestForm.value.product_image) form.append('product_image', procRequestForm.value.product_image)

    const res = await axios.post('/api/procurement-requests/manual', form, { withCredentials: true, headers: { 'Content-Type': 'multipart/form-data' } })
    // try to use created object returned from server to insert into list and avoid duplicates
    const created = res.data?.data ?? res.data ?? null
    if (created && created.id) {
      procurementRequests.value = [created, ...procurementRequests.value.filter(r => r.id !== created.id)]
    }
    showToast(`✓ Procurement request created for ${quantity} units`, 'success')
    showProcRequestForm.value = false
    procRequestForm.value = { product_id: '', quantity: 1, request_budget: false, receipt: null, product_image: null }
    procRequestFormError.value = ''
    // Add timeout to prevent hanging indefinitely
    try {
      await Promise.race([
        refreshList(),
        new Promise((_, reject) => setTimeout(() => reject(new Error('refresh timeout')), 5000))
      ])
    } catch (e) {
      // If refresh times out or fails, just continue - request was created successfully
      console.warn('refreshList timeout/error (non-blocking):', e.message)
    }
  } catch (e) {
    // Handle duplicate active procurement request (409 Conflict)
    if (e.response?.status === 409) {
      const data = e.response.data
      const message = `${data.error}\n\n${data.details}\n\nExisting Request ID: ${data.existing_request_id}\nStatus: ${data.existing_status}`

      if (window.swal) {
        window.swal('⚠️ Cannot Create Duplicate Request', message, 'warning')
      } else {
        showToast(message, 'error')
      }
    } else {
      const errorMsg = e.response?.data?.message || 'Failed to create procurement request'
      if (window.swal) {
        window.swal('Error', `❌ ${errorMsg}`, 'error')
      } else {
        showToast(errorMsg, 'error')
      }
    }
  } finally {
    procRequestSubmitting.value = false
    try { if (window.hideRouteOverlay) window.hideRouteOverlay() } catch (e) {}
    try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
  }
}

function cancelProcRequest() {
  showProcRequestForm.value = false
  procRequestForm.value = { product_id: '', quantity: 1, request_budget: false, receipt: null, product_image: null }
  procRequestFormError.value = ''
}

async function requestProcurement(product) {
  if (!product) return
  const ok = window.swalConfirm ? await window.swalConfirm(`Create procurement request for ${product.name}?\n\n(Minimum 10 units will be requested)`) : true
  if (!ok) return
  requesting.value = { ...requesting.value, [product.id]: true }
  try {
    // Ensure minimum 10 units for quick procurement requests
    const minStock = Number(product.min_stock) > 0 ? Number(product.min_stock) : 10
    const currentStock = Number(product.real_stock ?? product.stock ?? 0) || 0
    const diff = Math.ceil(minStock - currentStock)
    const quantity = Math.max(diff, 10)  // Ensure at least 10 units
    await ensureCsrf()
    const res = await axios.post('/api/procurement-requests', { product_id: product.id, quantity: quantity }, { withCredentials: true })
    const created = res.data?.data ?? res.data ?? null
    if (created && created.id) {
      procurementRequests.value = [created, ...procurementRequests.value.filter(r => r.id !== created.id)]
    }

    if (window.swal) {
      window.swal('Success!', `✅ Procurement request created for ${quantity} units`, 'success')
    } else {
      showToast(`✓ Procurement request created for ${quantity} units`, 'success')
    }

    // Add timeout to prevent hanging indefinitely
    try {
      await Promise.race([
        refreshList(),
        new Promise((_, reject) => setTimeout(() => reject(new Error('refresh timeout')), 5000))
      ])
    } catch (e) {
      // If refresh times out or fails, just continue - request was created successfully
      console.warn('refreshList timeout/error (non-blocking):', e.message)
    }
  } catch (e) {
    // Handle duplicate active procurement request (409 Conflict)
    if (e.response?.status === 409) {
      const data = e.response.data
      const message = `${data.error}\n\n${data.details}\n\nExisting Request ID: ${data.existing_request_id}\nStatus: ${data.existing_status}`

      if (window.swal) {
        window.swal('⚠️ Cannot Create Duplicate Request', message, 'warning')
      } else {
        showToast(message, 'error')
      }
    } else {
      const errorMsg = e.response?.data?.message || 'Failed to create procurement request'
      if (window.swal) {
        window.swal('Error', `❌ ${errorMsg}`, 'error')
      } else {
        showToast(errorMsg, 'error')
      }
    }
  } finally {
    requesting.value = { ...requesting.value, [product.id]: false }
    try { if (window.hideRouteOverlay) window.hideRouteOverlay() } catch (e) {}
    try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
  }
}

function onReceiptChange(e) {
  const f = e?.target?.files?.[0] ?? null
  procRequestForm.value.receipt = f
}

function onProductImageChange(e) {
  const f = e?.target?.files?.[0] ?? null
  procRequestForm.value.product_image = f
}

async function fetchProductRequests() {
  productRequestsLoading.value = true
  try {
    console.log('[ProductRequests] Fetching...')
    const res = await axios.get('/api/product-requests', { withCredentials: true })
    const data = res.data?.data ?? res.data ?? []
    console.log('[ProductRequests] Fetch complete, count:', Array.isArray(data) ? data.length : 'not-array')
    productRequests.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('[ProductRequests] Fetch error:', e.message)
    productRequests.value = []
  } finally {
    productRequestsLoading.value = false
  }
}

async function submitProductRequest() {
  productRequestSubmitting.value = true
  try {
    console.log('[ProductRequest] Starting submission...')
    await ensureCsrf()
    const payload = { name: productRequestForm.value.name, description: productRequestForm.value.description || null, unit: productRequestForm.value.unit || null }
    console.log('[ProductRequest] Payload:', payload)

    const res = await axios.post('/api/product-requests', payload, { withCredentials: true })
    console.log('[ProductRequest] Response:', res.data)

    showToast('Product request submitted for approval', 'success')
    showProductRequestForm.value = false
    productRequestForm.value = { name: '', description: '', unit: '' }

    console.log('[ProductRequest] Fetching updated product requests...')
    // Add timeout to prevent hanging indefinitely
    try {
      await Promise.race([
        fetchProductRequests(),
        new Promise((_, reject) => setTimeout(() => reject(new Error('fetch timeout')), 5000))
      ])
    } catch (e) {
      // If fetch times out or fails, just continue - request was created successfully
      console.warn('fetchProductRequests timeout/error (non-blocking):', e.message)
    }
    console.log('[ProductRequest] Submission complete!')
  } catch (e) {
    console.error('[ProductRequest] Error:', e)
    console.error('[ProductRequest] Response data:', e.response?.data)
    console.error('[ProductRequest] Status:', e.response?.status)
    const msg = e.response?.data?.error || e.response?.data?.message || e.message || 'Failed to submit product request'
    showToast(msg, 'error')
  } finally {
    productRequestSubmitting.value = false
    try { if (window.hideRouteOverlay) window.hideRouteOverlay() } catch (e) {}
    try { if (window.pageBlur && typeof window.pageBlur.hide === 'function') window.pageBlur.hide() } catch (e) {}
  }
}

function cancelProductRequest() {
  showProductRequestForm.value = false
  productRequestForm.value = { name: '', description: '', unit: '' }
}

// Modals / forms
const showCountModal = ref(false);
const showAdjustModal = ref(false);
const showAddModal = ref(false);
const activeProduct = ref(null);
const countValue = ref(0);
const adjust = ref({ delta: 0, note: '' });
const newProduct = ref({ name: '', price: 0, stock: 0, sku: '' });
const previewSku = ref('');

// Expired product report modal state
const showExpiredReportModal = ref(false);
const expiredReportProduct = ref(null);
const expiredReportForm = ref({
  quantity: 1,
  notes: '',
  image: null
});
const expiredReportSubmitting = ref(false);
const expiredReportError = ref('');
const expiredReportSuccess = ref('');

// Expired products list
const expiredProducts = ref([]);
const expiredProductsLoading = ref(false);

// Disposal list data
const disposals = ref([])
const isLoadingDisposals = ref(false)
const disposalError = ref('')
const disposalStats = ref({
  total: 0,
  pending: 0,
  resolved: 0,
  totalQuantity: 0
})
const filteredDisposals = ref([])
const disposalSearchQuery = ref('')
const statusFilter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')
const currentPage = ref(1)
const perPage = ref(25)
const showImageModal = ref(false)
const selectedImage = ref('')
const totalPages = computed(() => Math.max(1, Math.ceil(filteredDisposals.value.length / perPage.value)))
const paginatedDisposals = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredDisposals.value.slice(start, start + perPage.value)
})

// Expired lots summary for the modal
const expiredLotsSummary = ref(null);

// Fetch expired products from the server
async function fetchExpiredProducts() {
  expiredProductsLoading.value = true
  try {
    const res = await axios.get('/api/staff/inventory/expired-products', { withCredentials: true })
    if (res.data && res.data.ok) {
      expiredProducts.value = res.data.data || []
    } else {
      expiredProducts.value = []
    }
  } catch (e) {
    console.error('Failed to fetch expired products:', e)
    expiredProducts.value = []
  } finally {
    expiredProductsLoading.value = false
  }
}

// Disposal list functions
async function loadDisposals() {
  isLoadingDisposals.value = true
  disposalError.value = ''

  try {
    const res = await axios.get('/api/staff/inventory/expired-products', { withCredentials: true })
    if (res.data && res.data.ok) {
      const data = res.data.data || []
      disposals.value = data
      filteredDisposals.value = data
      calculateDisposalStats()
    } else {
      disposals.value = []
      filteredDisposals.value = []
      calculateDisposalStats()
    }
  } catch (e) {
    console.error('Failed to fetch disposals:', e)
    disposalError.value = 'Failed to load disposal records: ' + (e.response?.data?.message || e.message)
    disposals.value = []
    filteredDisposals.value = []
    calculateDisposalStats()
  } finally {
    isLoadingDisposals.value = false
  }
}

function calculateDisposalStats() {
  const stats = {
    total: disposals.value.length,
    pending: 0,
    resolved: 0,
    totalQuantity: 0
  }

  disposals.value.forEach(disposal => {
    stats.totalQuantity += disposal.quantity || 0

    const status = (disposal.status || '').toLowerCase()
    if (status === 'pending') stats.pending++
    else if (status === 'resolved') stats.resolved++
  })

  disposalStats.value = stats
}

const recentDisposals = computed(() => {
  return disposals.value.slice(0, 10) // Show only the 10 most recent disposals
})

function filterDisposals() {
  currentPage.value = 1
  let filtered = [...disposals.value]
  const query = disposalSearchQuery.value.trim().toLowerCase()
  if (query) {
    filtered = filtered.filter(item =>
      String(item.product_name || '').toLowerCase().includes(query) ||
      String(item.product_sku || '').toLowerCase().includes(query)
    )
  }
  if (statusFilter.value !== 'all') filtered = filtered.filter(item => item.status === statusFilter.value)
  if (dateFrom.value) filtered = filtered.filter(item => new Date(item.created_at) >= new Date(dateFrom.value))
  if (dateTo.value) {
    const end = new Date(dateTo.value)
    end.setHours(23, 59, 59, 999)
    filtered = filtered.filter(item => new Date(item.created_at) <= end)
  }
  filteredDisposals.value = filtered
}

function refreshDisposals() {
  loadDisposals()
}

function exportDisposals() {
  const headers = ['Date', 'Product Name', 'SKU', 'Quantity', 'Reported By', 'Notes', 'Status']
  const rows = [headers.join(',')]
  filteredDisposals.value.forEach(item => rows.push([
    formatDate(item.created_at),
    `"${String(item.product_name || '').replace(/"/g, '""')}"`,
    item.product_sku || 'N/A',
    item.quantity || 0,
    `"${String(item.reported_by || '').replace(/"/g, '""')}"`,
    `"${String(item.notes || '').replace(/"/g, '""')}"`,
    item.status || 'Unknown'
  ].join(',')))
  const url = URL.createObjectURL(new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' }))
  const link = document.createElement('a')
  link.href = url
  link.download = `disposal-list-${new Date().toISOString().split('T')[0]}.csv`
  link.click()
  URL.revokeObjectURL(url)
  showToast('Disposal list exported successfully', 'success')
}

function viewImage(imagePath) {
  if (imagePath) {
    selectedImage.value = imagePath
    showImageModal.value = true
  }
}

function closeImageModal() {
  showImageModal.value = false
  selectedImage.value = ''
}

function getDisposalStatusLabel(status) {
  const labels = {
    'pending': 'Pending Review',
    'reviewed': 'Reviewed',
    'resolved': 'Resolved'
  }
  return labels[status] || status || 'Unknown'
}

function getDisposalStatusClass(status) {
  const classes = {
    'pending': 'status-pending',
    'reviewed': 'status-reviewed',
    'resolved': 'status-resolved'
  }
  return classes[status] || 'status-unknown'
}

async function markDisposalResolved(disposalId) {
  try {
    await axios.post(
      `/api/staff/inventory/expired-products/${disposalId}/resolve`,
      {},
      { withCredentials: true }
    )
    showToast('Disposal marked as resolved', 'success')
    await loadDisposals()
  } catch (e) {
    console.error('Failed to mark disposal as resolved:', e)
    showToast(e.response?.data?.message || 'Failed to update disposal status', 'error')
  }
}

// Helper functions for expired product reports
function getReportStatusLabel(status) {
  const labels = {
    'pending': 'Pending Review',
    'reviewed': 'Reviewed',
    'resolved': 'Resolved'
  }
  return labels[status] || status || 'Unknown'
}

function getReportStatusClass(status) {
  const classes = {
    'pending': 'status-pending',
    'reviewed': 'status-reviewed',
    'resolved': 'status-resolved'
  }
  return classes[status] || 'status-unknown'
}

// small helper to force ProductList to refresh after server changes
function refreshList() {
  if (productListRef.value && typeof productListRef.value.fetchProducts === 'function') {
    return productListRef.value.fetchProducts().then(() => updateStats()).catch(() => {})
  }
  // fallback: fetch inventory directly
  return fetchInventory().then(() => updateDashboardTotals()).catch(() => {})
}

// Helper: perform request with graceful handling and token-fallback on 401/403
async function requestWithFallback(method, url, options = {}) {
  const token = (typeof localStorage !== 'undefined') ? localStorage.getItem('token') || '' : '';
  const baseHeaders = Object.assign({}, options.headers || {});
  if (token && !baseHeaders['Authorization'] && !baseHeaders['authorization']) {
    baseHeaders['Authorization'] = `Bearer ${token}`;
  }

  try {
    const res = await axios[method](url, Object.assign({}, options, { headers: baseHeaders }));
    return res;
  } catch (e) {
    const status = e?.response?.status;
    if (status === 401 || status === 403) {
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        const retry = await axios[method](url, Object.assign({}, options, { headers: baseHeaders }));
        return retry
      } catch (e2) {
        // try token fallback
        if (token) {
          try {
            const retryHeaders = Object.assign({}, baseHeaders, { Authorization: `Bearer ${token}` })
            const retryOpts = Object.assign({}, options, { headers: retryHeaders })
            const res2 = await axios[method](url, retryOpts)
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
            return res2
          } catch (e3) {}
        }
      }
    }
    throw e
  }
}

async function requestWithFallbackPost(url, data = {}, options = {}) {
  return requestWithFallback('post', url, Object.assign({}, options, { data }))
}

function formatPricingType(type) {
  const typeMap = { 'individual': 'Individual', 'per_pack': 'Per Pack', 'both': 'Both' }
  return typeMap[type] || 'N/A'
}

function isExpired(expiryDate) {
  if (!expiryDate) return false
  try { return new Date(expiryDate) < new Date() } catch (e) { return false }
}

function isExpiringSoon(expiryDate) {
  if (!expiryDate) return false
  try {
    const expiry = new Date(expiryDate)
    const now = new Date()
    const daysUntil = (expiry - now) / (1000*60*60*24)
    return daysUntil >= 0 && daysUntil <= 7
  } catch (e) { return false }
}

function updateDashboardTotals() {
  const inv = inventory.value || []
  const procs = procurementRequests.value || []
  const prodReqs = productRequests.value || []
  dashboardTotals.value.totalProducts = inv.length
  dashboardTotals.value.lowStock = inv.filter(i => (i.status || '').toString().toLowerCase() !== 'ok').length
  dashboardTotals.value.pendingRequests = procs.filter(r => (r.status || '').toString().toLowerCase() === 'pending').length + prodReqs.filter(r => (r.approval_status || '').toString().toLowerCase() === 'pending_approval').length
}

async function fetchInventory() {
  inventoryLoading.value = true
  inventoryError.value = ''
  try {
    const res = await requestWithFallback('get', '/api/staff/inventory/products', { params: { include_unpublished: 1 }, withCredentials: true })
    const rawData = res.data?.data ?? res.data ?? []
    const arr = Array.isArray(rawData) ? rawData : []
    // Deduplicate products by normalized name: prefer published item, otherwise most recently updated
    const nameMap = {}
    arr.forEach(p => {
      if (!p || !p.name) return
      const key = String(p.name || '').trim().toLowerCase()
      const existing = nameMap[key]
      if (!existing) {
        nameMap[key] = p
        return
      }
      // prefer published
      const existingPublished = !!existing.is_published
      const curPublished = !!p.is_published
      if (curPublished && !existingPublished) {
        nameMap[key] = p
        return
      }
      if (curPublished === existingPublished) {
        // choose the most recently updated
        try {
          const eTime = new Date(existing.updated_at || existing.created_at || 0).getTime()
          const pTime = new Date(p.updated_at || p.created_at || 0).getTime()
          if (pTime > eTime) nameMap[key] = p
        } catch (e) {
          // fallback: keep existing
        }
      }
    })

    inventory.value = Object.values(nameMap).map(p => ({
      ...p,
      min_stock: (Number(p.min_stock) > 0) ? Number(p.min_stock) : 10,
      stock: Number(p.stock ?? 0),
      status: (Number(p.stock ?? 0) < ((Number(p.min_stock) > 0) ? Number(p.min_stock) : 10)) ? 'LOW STOCK' : 'OK'
    }))
  } catch (e) {
    console.error('Inventory fetch error:', e)
    inventoryError.value = 'Failed to load inventory: ' + (e.response?.data?.message || e.message)
    inventory.value = []
  } finally {
    inventoryLoading.value = false
    try { updateDashboardTotals() } catch (e) {}
  }
}

function makePreviewSku(name) {
  let base = (name || '').toUpperCase().replace(/[^A-Z0-9]+/g, '').substring(0, 6)
  if (!base) base = 'PRD'
  const random = Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0,4).toUpperCase() || (Math.random()*1e6|0).toString(36).substring(0,4).toUpperCase()
  return `${base}-${random}`
}

function regeneratePreview() {
  previewSku.value = makePreviewSku(newProduct.value.name || '')
}

const displaySku = computed(() => {
  return newProduct.value.sku && newProduct.value.sku.trim() !== '' ? newProduct.value.sku : (previewSku.value || makePreviewSku(newProduct.value.name || ''))
})
const formError = ref('');
const formSuccess = ref('');
const isLoading = ref(false)

// controls state for header-area filters
const searchQuery = ref('')
const selectedStockFilter = ref('all')

// stats shown in sidebar
const totalProducts = ref(0)
const lowStockCount = ref(0)
const outOfStockCount = ref(0)

// Announcements state for staff panels
const announcements = ref([])
const loadingAnnouncements = ref(false)

async function fetchAnnouncements() {
  loadingAnnouncements.value = true
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true })
    if (res.data) {
      // API may return { announcements: [...] } or data array directly
      if (Array.isArray(res.data)) announcements.value = res.data
      else if (Array.isArray(res.data.announcements)) announcements.value = res.data.announcements
      else if (Array.isArray(res.data.data)) announcements.value = res.data.data
      else announcements.value = []
    }
  } catch (e) {
    console.error('Failed to load announcements:', e)
    announcements.value = []
  } finally {
    loadingAnnouncements.value = false
  }
}

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleString() } catch (e) { return d }
}

function onSearchInput() {
  if (productListRef.value && typeof productListRef.value.setQuery === 'function') {
    productListRef.value.setQuery(searchQuery.value)
  }
}

function onStockFilterChange() {
  if (productListRef.value && typeof productListRef.value.setStockFilter === 'function') {
    productListRef.value.setStockFilter(selectedStockFilter.value)
  }
}

async function updateStats() {
  if (productListRef.value && typeof productListRef.value.getStats === 'function') {
    const s = productListRef.value.getStats()
    totalProducts.value = s.total || 0
    lowStockCount.value = s.low || 0
    outOfStockCount.value = s.out || 0
  }
}

// click-away listener to close header profile dropdown
function onDocClick(e) {
  try {
    if (!showProfileMenu.value) return
    const wrapper = headerProfileWrapper.value
    if (!wrapper) return
    if (!wrapper.contains(e.target)) {
      showProfileMenu.value = false
    }
  } catch (err) { /* ignore */ }
}

// Register cleanup during setup (must be registered synchronously)
onUnmounted(() => {
  try { document.removeEventListener('click', onDocClick) } catch (e) {}
  stopCamera()
})

onMounted(async () => {
  isProfileLoading.value = true;
  try {
    const res = await axios.get('/api/staff/profile', { withCredentials: true });
    if (res.data && res.data.ok && res.data.user) {
      const u = res.data.user;
      staffProfile.value = {
        avatarUrl: u.avatarUrl || u.avatar_url || '',
        fullName: u.full_name || u.fullName || '',
        role: u.role || '',
        displayRole: 'Inventory Staff',
        username: u.username || '',
        email: u.email || '',
        contact: u.contact || u.phone_number || '',
        accountId: u.account_id || '',
        branch_name: u.branch_name || (u.branch && (u.branch.name || u.branch.branch_name)) || '',
        password: '',
        password_confirmation: ''
      };
    }
  } catch (e) {
    profileError.value = 'Failed to load profile info.';
  } finally {
    isProfileLoading.value = false;
  }
  // Load attendance status and settings on mount
  if (!hideAttendanceCard.value) {
    loadAttendanceStatus()
    loadAttendanceSettings()
    getUserLocation()
  }
  // ProductList will handle fetching when given a fetchUrl; if a parent passed products prop, ProductList will display them.
  // initial stats update after mount
  setTimeout(() => updateStats(), 300)
  fetchAnnouncements()
  // pending/confirmed procurement loading removed for staff panel

  // Load internal products for selects (use same fetchUrl so include_unpublished is respected)
  try {
    const prodRes = await axios.get(fetchUrl.value, { withCredentials: true })
    internalProducts.value = prodRes.data?.data ?? prodRes.data ?? []
  } catch (e) {
    internalProducts.value = []
  }

  // load inventory for manager-style table and dashboard
  await fetchInventory()

  await loadPanelNotifications()

  // fetch expired products
  await fetchExpiredProducts()

  // load disposal list
  await loadDisposals()

  // attach click-away listener (defined in setup scope)
  try { document.addEventListener('click', onDocClick) } catch (e) {}
});

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      const count = Number(res.data.counts?.inventory || 0)
      notificationCounts.value = { inventory: Number.isNaN(count) ? 0 : count }
    }
  } catch (e) {
    notificationCounts.value = { inventory: 0 }
  }
}

watch(inventoryPendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending inventory requests.', 'info')
    hasNotified.value = true
  }
})


function toggleProfileMenu() {
  showProfileMenu.value = !showProfileMenu.value
}

function openInfoFromMenu() {
  showProfileMenu.value = false
  openInfoModal()
}

function openLogoutFromMenu() {
  showProfileMenu.value = false
  // Use global SweetAlert2 wrapper if available for consistent confirmation UI
  ;(async () => {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await logout()
  })()
}

/* Pending procurement confirmation removed from staff panel */

// Note: ProductList can fetch products itself via `fetchUrl`. Parent mutating actions will call `refreshList()` after success.

function formatCurrency(v) {
  if (v === null || v === undefined) return '-';
  return Number(v).toLocaleString(undefined, { style: 'currency', currency: 'PHP' });
}

function openCountModal(prod) {
  activeProduct.value = prod;
  countValue.value = prod.stock || 0;
  formError.value = '';
  formSuccess.value = '';
  showCountModal.value = true;
}

async function submitCount() {
  if (!activeProduct.value) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    const payload = { stock: Number(countValue.value) };
    const res = await axios.put(endpoints.value.update(activeProduct.value.id), payload, { withCredentials: true });
    await refreshList()
    formSuccess.value = 'Stock updated successfully.';
    showCountModal.value = false;
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to update stock.';
  }
}

function openAdjustModal(prod) {
  activeProduct.value = prod;
  adjust.value = { delta: 0, note: '' };
  formError.value = '';
  formSuccess.value = '';
  showAdjustModal.value = true;
}

function handleEdit(prod) {
  // open the Add/Edit modal prefilled for editing
  newProduct.value = { id: prod.id, name: prod.name, price: prod.price, stock: prod.stock, sku: prod.sku }
  formError.value = '';
  formSuccess.value = '';
  showAddModal.value = true;
}

async function submitAdjust() {
  if (!activeProduct.value) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    const newStock = Number(activeProduct.value.stock) + Number(adjust.value.delta);
    const payload = { stock: newStock };
    const res = await axios.put(endpoints.value.update(activeProduct.value.id), payload, { withCredentials: true });
    refreshList()
    formSuccess.value = 'Stock adjusted successfully.';
    showAdjustModal.value = false;
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to adjust stock.';
  }
}

function openAddProduct() {
  newProduct.value = { name: '', price: 0, stock: 0, sku: '' };
  formError.value = '';
  formSuccess.value = '';
  showAddModal.value = true;
  // prepare preview SKU
  previewSku.value = makePreviewSku('')
}

// procurement confirmation UI removed from staff panel

async function submitAddProduct() {
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { formError.value = 'Unable to refresh CSRF token. Please reload or login.'; return }
  try {
    // If user didn't provide SKU, send the preview so server and UI match
    const payload = { ...newProduct.value };
    if (!payload.sku || payload.sku.trim() === '') payload.sku = previewSku.value || makePreviewSku(payload.name || '')
    let res
    if (payload.id) {
      // update existing product
      res = await axios.put(endpoints.value.update(payload.id), payload, { withCredentials: true })
    } else {
      res = await axios.post(endpoints.value.store, payload, { withCredentials: true });
    }
    if (res.data && (res.data.product || res.data.ok)) {
      // refresh the list so ProductList reflects the change
      refreshList()
      formSuccess.value = payload.id ? 'Product updated.' : 'Product added.';
      showAddModal.value = false;
    }
  } catch (e) {
    formError.value = (e.response && e.response.data && e.response.data.message) || 'Failed to create product.';
  }
}

async function deleteProduct(prod) {
  if (!(await window.swalConfirm('Delete product "' + prod.name + '"? This cannot be undone.'))) return;
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { alert('Unable to refresh CSRF token. Please reload or login.'); return }
  try {
    await axios.delete(endpoints.value.destroy(prod.id), { withCredentials: true });
    refreshList()
  } catch (e) {
    alert((e.response && e.response.data && e.response.data.message) || 'Failed to delete product');
  }
}

async function handleTogglePublish(payload) {
  if (!payload || !payload.id) return
  // Only allow branch ADMIN to toggle publish from this panel
  if (!staffProfile.value || (staffProfile.value.role || '').toUpperCase() !== 'ADMIN') {
    showToast('Only branch admins can publish or unpublish products', 'error')
    return
  }
  const ok = await window.swalConfirm ? await window.swalConfirm(`Are you sure you want to ${payload.publish ? 'publish' : 'unpublish'} this product?`) : true
  if (!ok) return
  const okCsrf = await ensureCsrf()
  if (!okCsrf) { showToast('Unable to refresh CSRF token', 'error'); return }
  try {
    await axios.put(endpoints.value.update(payload.id), { is_published: payload.publish ? 1 : 0 }, { withCredentials: true })
    showToast(payload.publish ? 'Product published' : 'Product unpublished', 'success')
    await refreshList()
  } catch (e) {
    console.error('publish toggle error', e)
    showToast(e.response?.data?.message || 'Failed to update product visibility', 'error')
  }
}

// Ensure a fresh CSRF cookie/header is present before mutating requests
async function ensureCsrf() {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    const match = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'));
    const token = match ? decodeURIComponent(match[2]) : null;
    if (token) axios.defaults.headers.common['X-XSRF-TOKEN'] = token;
    return true;
  } catch (e) {
    return false;
  }
}

function openInfoModal() {
  showInfoModal.value = true;
  isEditingInfo.value = false;
  profileError.value = '';
  profileSuccess.value = '';
}

function handleInfoClose() {
  if (isEditingInfo.value) {
    isEditingInfo.value = false;
    profileError.value = '';
    profileSuccess.value = '';
  } else {
    showInfoModal.value = false;
  }
}

async function saveStaffInfo() {
  isSavingProfile.value = true;
  profileError.value = '';
  profileSuccess.value = '';
  // TODO: Replace with real API call
  setTimeout(() => {
    isSavingProfile.value = false;
    isEditingInfo.value = false;
    profileSuccess.value = 'Profile updated!';
  }, 1000);
}

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

    const endpoint = '/api/staff/inventory/avatar'
    const res = await axios.post(endpoint, formData, config)

    if (res.data && res.data.ok) {
      staffProfile.value.avatarUrl = res.data.avatarUrl + '?t=' + Date.now()
      alert('Profile picture updated successfully!')
    }
  } catch (e) {
    console.error('Avatar upload failed:', e)
    alert(e.response?.data?.message || 'Failed to upload profile picture. Please try again.')
  }
}

async function logout() {
  if (isLoggingOut.value) return;
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(false))
    if (!ok) return
    isLoggingOut.value = true;
    try { await axios.post('/api/logout', {}, { withCredentials: true }) } catch (e) {}
    try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
    // Optional: show overlay (if you have one)
    showLogoutConfirm.value = false;
    setTimeout(() => {
      try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
      try { window.location.replace('/staff-landing') } catch (e) { router.push('/staff-landing').catch(() => {}) }
    }, 600);
  } catch (e) { console.error('logout failed', e) }
}

// Attendance functions
async function loadAttendanceStatus() {
  try {
    const res = await axios.get('/api/staff/attendance/status', { withCredentials: true })
    if (res.data && res.data.ok) {
      attendanceStatus.value = {
        is_clocked_in: res.data.status?.is_clocked_in || false,
        clock_in_time: res.data.status?.clock_in_time || null,
        clock_out_time: res.data.status?.clock_out_time || null,
        hours_worked: res.data.status?.hours_worked || 0
      }
    }
  } catch (e) {
    console.error('Failed to load attendance status:', e)
  }
}

// Geofencing methods
const getUserLocation = async () => {
  locationLoading.value = true
  locationError.value = ''
  canClockInGeofencing.value = true
  geofencingMessage.value = ''

  if (!navigator.geolocation) {
    locationError.value = 'Geolocation is not supported by your browser'
    canClockInGeofencing.value = false
    locationLoading.value = false
    return
  }

  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      })
    })

    userLocation.value = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude
    }
    if (attendanceMessageType.value === 'warning') {
      attendanceMessage.value = ''
      attendanceMessageType.value = ''
    }
  } catch (error) {
    console.error('Error getting location:', error)
    locationError.value = 'Unable to retrieve your location. Please enable location services.'
    canClockInGeofencing.value = false
    userLocation.value = null
  } finally {
    locationLoading.value = false
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
    console.error('Failed to load attendance settings:', e)
    attendanceSettings.value = {
      early_clockout_override: false,
      scheduled_time_out: '17:00:00'
    }
  }
}

async function performClockIn() {
  if (isAttendanceProcessing.value || locationLoading.value) return

  if (!userLocation.value) {
    attendanceMessage.value = 'Please enable location services to clock in'
    attendanceMessageType.value = 'warning'
    await getUserLocation()
    if (!userLocation.value) return
  }

  attendanceMessage.value = ''
  attendanceMessageType.value = ''
  showFaceCapture.value = true
  capturedImage.value = null
  cameraError.value = ''
  await startCamera()
}

async function startCamera() {
  stopCamera()
  cameraError.value = ''

  if (!navigator.mediaDevices?.getUserMedia) {
    cameraError.value = 'Camera access is unavailable. Use HTTPS or localhost and enable a camera.'
    return
  }

  try {
    let stream
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
      })
    } catch (error) {
      if (error.name !== 'OverconstrainedError' && error.name !== 'NotFoundError') throw error
      stream = await navigator.mediaDevices.getUserMedia({ video: true })
    }
    cameraStream.value = stream
    await nextTick()
    if (cameraVideo.value) cameraVideo.value.srcObject = stream
  } catch (error) {
    console.error('Camera access error:', error)
    cameraError.value = error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError'
      ? 'Camera permission is blocked. Allow camera access, then click Try Again.'
      : 'Unable to access camera. Check browser permissions, then click Try Again.'
    stopCamera()
  }
}

function stopCamera() {
  if (cameraStream.value) {
    cameraStream.value.getTracks().forEach(track => track.stop())
    cameraStream.value = null
  }
}

function capturePhoto() {
  const video = cameraVideo.value
  const canvas = cameraCanvas.value
  if (!video || !canvas || !video.videoWidth) {
    attendanceMessage.value = 'Camera not ready. Please try again.'
    attendanceMessageType.value = 'error'
    return
  }

  isCapturing.value = true
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight
  canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height)
  capturedImage.value = canvas.toDataURL('image/jpeg', 0.8)
  stopCamera()
  showFaceCapture.value = false
  proceedWithClockIn()
}

function cancelFaceCapture() {
  stopCamera()
  showFaceCapture.value = false
  capturedImage.value = null
  cameraError.value = ''
  isCapturing.value = false
}

async function proceedWithClockIn() {
  if (isAttendanceProcessing.value || !userLocation.value || !capturedImage.value) return
  isAttendanceProcessing.value = true

  try {
    const res = await axios.post('/api/staff/clock-in', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude,
      face_image: capturedImage.value
    }, { withCredentials: true })

    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked in successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else if (res.data.geofencing_error) {
      attendanceMessage.value = res.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = res.data.message
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.geofencing_error) {
      attendanceMessage.value = e.response.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = e.response.data.message
    } else {
      attendanceMessage.value = e.response?.data?.message || 'Error clocking in'
      attendanceMessageType.value = 'error'
    }
  } finally {
    isAttendanceProcessing.value = false
    isCapturing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

async function performClockOut() {
  if (isAttendanceProcessing.value || locationLoading.value) return

  if (!userLocation.value) {
    attendanceMessage.value = 'Please enable location services to clock out'
    attendanceMessageType.value = 'warning'
    await getUserLocation()
    if (!userLocation.value) return
    return
  }

  isAttendanceProcessing.value = true
  attendanceMessage.value = ''

  try {
    const res = await axios.post('/api/staff/clock-out', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude
    }, { withCredentials: true })

    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked out successfully!'
      attendanceMessageType.value = 'success'
      await loadAttendanceStatus()
    } else if (res.data.geofencing_error) {
      attendanceMessage.value = res.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = res.data.message
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out'
      attendanceMessageType.value = 'error'
    }
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.geofencing_error) {
      attendanceMessage.value = e.response.data.message || 'You are not within the branch vicinity'
      attendanceMessageType.value = 'error'
      canClockInGeofencing.value = false
      geofencingMessage.value = e.response.data.message
    } else {
      attendanceMessage.value = e.response?.data?.message || 'Error clocking out'
      attendanceMessageType.value = 'error'
    }
  } finally {
    isAttendanceProcessing.value = false
    setTimeout(() => { attendanceMessage.value = '' }, 3000)
  }
}

// Navigation to disposal list
function goToDisposalList() {
  router.push('/staff/inventory/disposal-list')
}

// Expired product report functions
function openExpiredReportModal(product) {
  expiredReportProduct.value = product

  // Set default quantity to 1, allowing user to manually enter the actual expired quantity
  // This handles cases where some stock may be expired and some may be fresh
  expiredReportForm.value = {
    quantity: 1,
    notes: '',
    image: null
  }

  expiredReportError.value = ''
  expiredReportSuccess.value = ''
  showExpiredReportModal.value = true
}

function closeExpiredReportModal() {
  showExpiredReportModal.value = false
  expiredReportProduct.value = null
  expiredReportForm.value = {
    quantity: 1,
    notes: '',
    image: null
  }
  expiredReportError.value = ''
  expiredReportSuccess.value = ''
}

function validateExpiredQuantity() {
  const maxStock = expiredReportProduct.value?.stock || 1
  const quantity = parseInt(expiredReportForm.value.quantity)

  if (isNaN(quantity) || quantity < 1) {
    expiredReportError.value = 'Quantity must be at least 1'
    return false
  }

  if (quantity > maxStock) {
    expiredReportError.value = `Quantity cannot exceed current stock (${maxStock})`
    return false
  }

  expiredReportError.value = ''
  return true
}

function onExpiredImageChange(event) {
  const file = event.target.files[0]
  expiredReportForm.value.image = file
}

// Auto-fill expired quantity from inventory lots
async function autoFillExpiredQuantity() {
  if (!expiredReportProduct.value) return

  try {
    const productId = expiredReportProduct.value.id
    const res = await axios.get(`/api/staff/inventory/products/${productId}/inventory-lots`, { withCredentials: true })

    if (res.data && res.data.ok && res.data.data) {
      const lots = res.data.data.lots || []
      const expiredLots = lots.filter(lot => lot.is_expired && lot.quantity > 0)

      if (expiredLots.length > 0) {
        const totalExpired = expiredLots.reduce((sum, lot) => sum + lot.quantity, 0)
        const currentStock = expiredReportProduct.value.stock || 0

        // Cap the auto-filled quantity to current stock to prevent validation errors
        const cappedQuantity = Math.min(totalExpired, currentStock)

        expiredReportForm.value.quantity = cappedQuantity
        expiredLotsSummary.value = {
          quantity: totalExpired,
          count: expiredLots.length
        }

        // Show appropriate message based on whether capping was needed
        if (totalExpired > currentStock) {
          expiredReportError.value = `Note: Auto-filled quantity capped to current stock (${currentStock}) from ${totalExpired} lot units`
          showToast(`Auto-filled: ${cappedQuantity} units (capped from ${totalExpired} to match current stock)`, 'info')
        } else {
          expiredReportError.value = ''
          showToast(`Auto-filled: ${cappedQuantity} units from ${expiredLots.length} expired lot(s)`, 'info')
        }
      } else {
        expiredReportError.value = 'No expired inventory lots found for this product'
        expiredLotsSummary.value = null
      }
    }
  } catch (e) {
    console.error('Failed to fetch inventory lots:', e)
    expiredReportError.value = 'Failed to load inventory lots. Please enter quantity manually.'
  }
}

// Fetch expired lots summary when modal opens
watch(showExpiredReportModal, (isOpen) => {
  if (isOpen && expiredReportProduct.value) {
    // Reset summary when modal opens
    expiredLotsSummary.value = null
    // Pre-fetch the summary in the background
    autoFillExpiredQuantity()
  }
})

async function submitExpiredReport() {
  if (!expiredReportProduct.value) return

  // Validate quantity before submission
  if (!validateExpiredQuantity()) {
    return
  }

  expiredReportSubmitting.value = true
  expiredReportError.value = ''

  try {
    await ensureCsrf()

    const formData = new FormData()
    formData.append('product_id', expiredReportProduct.value.id)
    formData.append('quantity', expiredReportForm.value.quantity)
    formData.append('notes', expiredReportForm.value.notes || '')

    if (expiredReportForm.value.image) {
      formData.append('image', expiredReportForm.value.image)
    }

    const res = await axios.post('/api/staff/inventory/expired-reports', formData, {
      withCredentials: true,
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (res.data && (res.data.ok || res.data.success)) {
      showToast('Expired product report submitted successfully', 'success')
      closeExpiredReportModal()
      // Refresh the list to show updated data
      refreshList()
      // Refresh expired products list
      fetchExpiredProducts()
    } else {
      throw new Error(res.data?.message || 'Failed to submit report')
    }
  } catch (e) {
    console.error('Submit expired report error:', e)
    expiredReportError.value = e.response?.data?.message || e.message || 'Failed to submit report'
  } finally {
    expiredReportSubmitting.value = false
  }
}
</script>

<style>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1200;
}
.modal-panel {
  background: #ffffff;
  border-radius: 8px;
  padding: 18px 20px;
  width: min(760px, 95%);
  box-shadow: 0 12px 30px rgba(0,0,0,0.18);
  z-index: 1201;
  max-height: 90vh;
  overflow: auto;
}
.modal-panel h3 { margin: 0 0 6px 0; font-size: 18px; }
.modal-sub { color: #666; margin-bottom: 10px; }
.modal-panel .form-group { margin-bottom: 10px; }
.modal-panel .form-group label { display:block; font-weight:600; margin-bottom:6px; }
.modal-panel .form-group input,
.modal-panel .form-group textarea,
.modal-panel .form-group select { width:100%; padding:8px 10px; border:1px solid #e2e8f0; border-radius:6px; font-size:14px; }
.modal-panel .form-actions { display:flex; gap:8px; justify-content:flex-end; }
.modal-panel .btn-secondary { background:#f3f4f6; border:1px solid #d1d5db; padding:8px 12px; border-radius:6px; }
.modal-panel .btn-primary { background:#ff8a00; color:#fff; padding:8px 12px; border-radius:6px; border: none; }

/* Ensure the ProductList header stays visible under modal backdrop layering issues */
.pl-root, .pl-right-column { position: relative; }
</style>

<style scoped>
.inventory-table th, .inventory-table td {
  padding: 0.55rem 0.7rem;
  font-size: 0.92rem;
  vertical-align: middle;
}
.inventory-table thead th {
  font-size: 0.95rem;
}
.prod-thumb {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid rgba(255,211,107,0.6);
  background: #fff4e6;
  display: inline-block;
}
.prod-thumb--placeholder {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #4b2a06;
  font-weight: 700;
  background: rgba(255,232,163,0.9);
}
.sku-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(255,211,107,0.7);
  color: #4b2a06;
  font-weight: 700;
  font-size: 0.85rem;
}
.prod-name { font-size: 0.98rem; }
.staff-table td.actions { white-space: nowrap; }

.pl-page { padding: 16px; background: radial-gradient(circle at center, #FFFFFF 0%, #FCFCFC 40%, #EFEFEF 100%); min-height: 100vh; width: 100vw; }
.pl-container { width: 100%; max-width: none; margin: 0 auto; background: #FFFFFF; border-radius: 12px; border: 1px solid #F0E9E0; padding: 20px; box-shadow: 0 8px 24px rgba(16,24,40,0.06); box-sizing: border-box; display: grid; grid-template-columns: minmax(240px, 320px) 1fr minmax(260px, 360px); gap: 20px; align-items: start }

/* root columns inside the container */
.pl-root { display: flex; gap: 20px; align-items: flex-start }
.pl-left-panel { width: 280px; flex: 0 0 280px }
.pl-right-column { flex: 1 1 auto; }

/* make the right column content stand out as a white card */
.pl-right-column .pl-header { background: #ffffff; border-radius: 12px; padding: 12px; margin-top: 8px; box-shadow: 0 8px 28px rgba(0,0,0,0.06); }
.pl-right-column .pl-main,
.pl-right-column .pl-table-wrap { background: #ffffff; border-radius: 12px; padding: 12px; box-shadow: 0 8px 28px rgba(0,0,0,0.06); }

.pl-header { display:flex; justify-content:space-between; align-items:center; gap:12px }
.pl-title { margin:0; font-size:1.05rem; color:#2c2c2c }
.pl-sub { margin:0; color:#6b6b6b; font-size:0.9rem }
.pl-actions { display:flex; gap:12px; align-items:center }
.pl-filters select, .pl-search input { border-radius:8px; border:1px solid rgba(0,0,0,0.06); padding:8px }
.pl-page-header { background: transparent; padding: 4px 0 }
.pl-container > .pl-page-header { grid-column: 1 / -1 }

/* Make ProductList span the first two columns (left + center) so there's no empty gutter */
.pl-container > ProductList { grid-column: 2 / 3 }
.pl-container > .pl-right-column { grid-column: 3 / 4 }
.pl-h1 { margin:0; font-size:1.4rem; color:#2c2c2c }
.pl-lead { margin:0; color:#6b6b6b }
.pl-controls { display:flex; justify-content:space-between; gap:12px; align-items:center }
.pl-controls-left { flex:1 }
.pl-controls-right { display:flex; gap:8px; align-items:center }
.pl-search { width:100%; padding:8px 12px; border-radius:8px; border:1px solid rgba(0,0,0,0.06); background:#ffffff }
.pl-stats { display:flex; flex-direction:column; gap:12px }
.stat-card { background: #ffffff; padding:12px; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
.stat-title { color:#6b6b6b; font-size:0.85rem }
.stat-value { font-weight:800; font-size:1.25rem; color:#333333 }
.pl-main { min-width:0 }

/* compact ProductList overrides when embedded */
ProductList[compact] { width:100% }

/* Profile card styles (restored owner look) */
.profile-card { background: #ffffff; border-radius: 14px; padding: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); display:flex; flex-direction:column; gap:12px }
.profile-avatar { display:flex; justify-content:center }
.avatar-circle { width:72px; height:72px; border-radius:50%; background: linear-gradient(180deg,#ff9a4b,#ff7043); color:white; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.25rem; border:4px solid rgba(255,255,255,0.9) }
.profile-info { text-align:center }
.profile-role { font-size:0.75rem; color:#8a4b1a; font-weight:700 }
.profile-name { font-size:1.05rem; font-weight:800; color:#7a2b00 }
.profile-sub { font-size:0.8rem; color:#a65a2a }
.profile-box { background: #ffffff; padding:12px; border-radius:10px; display:flex; flex-direction:column; gap:8px; align-items:center }
.account-id { color:#333333; font-weight:700 }
.btn-info-small { padding:6px 12px; border-radius:999px; background: #f0f0f0; border:none; color:#333333 }
.qr-placeholder { width:84px; height:84px; border-radius:8px; border:2px dashed rgba(255,211,107,0.6); display:flex; align-items:center; justify-content:center; color:#7a2b00 }
.profile-actions { display:flex; flex-direction:column; gap:10px }
.small-stats { display:flex; justify-content:space-between; gap:12px }
.small-stat-title { font-size:0.75rem; color:#8a4b1a }
.small-stat-val { font-weight:800; color:#7a2b00 }

/* Avatar upload styles */
.avatar-upload { cursor: pointer; position: relative; display: inline-block; }
.avatar-img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,244,230,0.9); }
.avatar-overlay { position: absolute; top: 0; left: 0; width: 72px; height: 72px; border-radius: 50%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
.avatar-upload:hover .avatar-overlay { opacity: 1; }
.avatar-change-text { color: white; font-size: 0.6rem; font-weight: 500; text-transform: uppercase; text-align: center; }

/* Attendance Card Styles */
.attendance-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

/* Desktop-specific adjustments to mirror ManagerLogisticsPanel behavior */
@media (min-width: 1000px) {
  /* ensure container uses the same column sizing as admin layout at large widths */
  .pl-container {
    grid-template-columns: minmax(240px, 320px) 1fr minmax(260px, 360px);
    gap: 20px;
  }

  /* keep right column in document flow and align it vertically with main content */
  .pl-right-column {
    position: static !important;
    margin-top: 212px !important;
    align-self: start !important;
    max-height: none !important;
    overflow: visible !important;
    padding-right: 0 !important;
  }

  /* announcements/pending box should scroll normally and not be sticky */
  .pending-box, .history-box {
    position: static !important;
    margin-top: 0 !important;
    max-height: none !important;
    overflow: visible !important;
  }
}

/* Pending/History centered section */
.pending-center { display:flex; gap:20px; justify-content:center; align-items:flex-start; margin:12px 0; }
.pending-box, .history-box { background: rgba(255,255,255,0.9); padding:12px; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); width:48%; }
.pending-box h3, .history-box h3 { margin:0 0 8px; color:#7a2b00 }
.pending-table, .history-table { width:100%; border-collapse:collapse }
.pending-table th, .pending-table td, .history-table th, .history-table td { padding:8px; border-bottom:1px solid rgba(0,0,0,0.06); text-align:left }
.pending-table thead th, .history-table thead th { font-weight:700; font-size:0.9rem }
.pending-table tbody tr:last-child td, .history-table tbody tr:last-child td { border-bottom:none }
.history-box { max-height:360px; overflow:auto }

/* Pending confirmations box styling */
.pending-box {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-sizing: border-box;
  width: 100%;
  max-width: 360px;
  min-height: 120px;
  margin: 0 auto;
}
.pending-box h3 { margin: 0 0 10px; color: #7a2b00; font-size: 1.05rem; font-weight:700 }
.pending-box .empty-text { color: #6b6b6b; font-size:1rem; padding: 0 }
.pending-table { width:100%; border-collapse:collapse; margin-top:8px }
.pending-table th { font-size:0.85rem; color:#6b6b6b; padding:8px 6px; text-align:left }
.pending-table td { font-size:0.95rem; padding:8px 6px; border-bottom:1px solid rgba(0,0,0,0.04) }
.pending-table button.btn-primary { padding:6px 10px; font-size:0.88rem }

/* Disposal List Section */
.disposal-stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin-bottom: 20px;
}

.disposal-stat-mini {
  background: white;
  border-radius: 10px;
  padding: 14px 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  gap: 6px;
  transition: all 0.2s ease;
}

.disposal-stat-mini:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.disposal-stat-label {
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.disposal-stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.disposal-table-wrapper {
  overflow-x: auto;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.disposal-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  font-size: 0.9rem;
}

.disposal-table thead {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.disposal-table thead th {
  padding: 12px 14px;
  font-weight: 700;
  color: #92400e;
  font-size: 0.85rem;
  text-align: left;
  border-bottom: 2px solid #fbbf24;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.disposal-table tbody tr {
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s ease;
}

.disposal-table tbody tr:hover {
  background: #fffbeb;
}

.disposal-table tbody td {
  padding: 12px 14px;
  font-size: 0.9rem;
  color: #333;
  vertical-align: middle;
}

.disposal-table .status-badge {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.disposal-table .status-pending {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fbbf24;
}

.disposal-table .status-reviewed {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #3b82f6;
}

.disposal-table .status-resolved {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #10b981;
}

.disposal-table .status-unknown {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.disposal-table .btn-resolve {
  padding: 6px 14px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.disposal-table .btn-resolve:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.disposal-table .btn-resolve:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.disposal-table .text-muted {
  color: #9ca3af;
  font-style: italic;
}

.loading-container,
.error-container,
.empty-message {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.error-message {
  color: #dc3545;
  background: #f8d7da;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.btn-retry {
  margin-top: 12px;
  padding: 8px 16px;
  background: #ff8a00;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.btn-retry:hover {
  background: #e67a00;
}

@media (max-width: 768px) {
  .disposal-stats-row {
    grid-template-columns: repeat(2, 1fr);
  }

  .disposal-table {
    font-size: 0.85rem;
  }

  .disposal-table thead th,
  .disposal-table tbody td {
    padding: 8px 10px;
  }
}

@media (max-width: 480px) {
  .disposal-stats-row {
    grid-template-columns: 1fr;
  }
}

/* Expired Products Table */
.expired-products-table {
  margin-top: 16px;
  overflow-x: auto;
}

.expired-products-table .inventory-table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.expired-products-table .inventory-table thead {
  background: #fee2e2;
}

.expired-products-table .inventory-table thead th {
  padding: 12px 16px;
  font-weight: 700;
  color: #7f1d1d;
  font-size: 0.9rem;
  text-align: left;
  border-bottom: 2px solid #fecaca;
}

.expired-products-table .inventory-table tbody tr {
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s ease;
}

.expired-products-table .inventory-table tbody tr:hover {
  background: #fef2f2;
}

.expired-products-table .inventory-table tbody td {
  padding: 12px 16px;
  font-size: 0.9rem;
  color: #333;
}

.expired-products-table .product-name {
  font-weight: 600;
  color: #991b1b;
}

.expired-products-table .product-sku {
  font-family: monospace;
  color: #666;
}

.expired-products-table .product-stock {
  font-weight: 600;
}

.expired-products-table .product-expiry {
  color: #dc3545;
  font-weight: 600;
}

.expired-products-table .reported-by {
  color: #555;
}

.expired-products-table .report-date {
  color: #666;
  font-size: 0.85rem;
}

.expired-products-table .report-status {
  text-align: center;
}

.expired-products-table .status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.8rem;
}

.expired-products-table .status-pending {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fbbf24;
}

.expired-products-table .status-reviewed {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #3b82f6;
}

.expired-products-table .status-resolved {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #10b981;
}

.expired-products-table .status-unknown {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

/* Inventory Lots Display */
.inventory-lots {
  min-width: 200px;
}

.lots-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.lot-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 8px;
  background: #f8fafc;
  border-radius: 4px;
  font-size: 0.85rem;
  border: 1px solid #e2e8f0;
}

.lot-item.lot-expired {
  background: #fef2f2;
  border-color: #fecaca;
}

.lot-quantity {
  font-weight: 600;
  color: #334155;
}

.lot-item.lot-expired .lot-quantity {
  color: #991b1b;
}

.lot-date {
  font-size: 0.8rem;
  color: #64748b;
}

.lot-item.lot-expired .lot-date {
  color: #dc3545;
  font-weight: 600;
}

.no-lots {
  color: #94a3b8;
  font-style: italic;
  font-size: 0.85rem;
}

/* Disposal Navigation Card */
.disposal-nav-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  transition: all 0.2s ease;
}

.disposal-nav-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.disposal-nav-content {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
}

.disposal-nav-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.disposal-nav-text {
  flex: 1;
}

.disposal-nav-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.disposal-nav-description {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
}

.disposal-nav-button {
  flex-shrink: 0;
  padding: 10px 20px;
  font-size: 0.95rem;
}

@media (max-width: 768px) {
  .disposal-nav-card {
    flex-direction: column;
    align-items: stretch;
  }

  .disposal-nav-content {
    flex-direction: column;
    text-align: center;
  }

  .disposal-nav-button {
    width: 100%;
  }
}

/* Inventory Summary Cards - below Attendance card */
.inventory-summary {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 10px;
}

@media (max-width: 600px) {
  .inventory-summary {
    grid-template-columns: 1fr;
  }
}

.attendance-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.attendance-title {
  font-weight: 700;
  color: #333333;
  font-size: 0.9rem;
}

.attendance-status-badge {
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
}

.status-on-duty {
  background: #d4edda;
  color: #155724;
}

.status-off-duty {
  background: #f8d7da;
  color: #721c24;
}

.attendance-times {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
}

.time-row {
  display: flex;
  justify-content: space-between;
}

.time-label {
  color: #8a4b1a;
}

.time-value {
  font-weight: 600;
  color: #7a2b00;
}

.attendance-buttons {
  display: flex;
  gap: 8px;
}

.btn-clock-in,
.btn-clock-out {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-clock-in {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.btn-clock-in:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-clock-in:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-clock-out {
  background: linear-gradient(135deg, #dc3545, #ff6b6b);
  color: white;
}

.btn-clock-out:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.btn-clock-out:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-disabled {
  background: #999 !important;
  cursor: not-allowed !important;
}

.clockout-restriction {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 6px;
  color: #856404;
  font-size: 0.7rem;
}

.restriction-icon {
  font-size: 1rem;
}

.attendance-message {
  padding: 8px;
  border-radius: 4px;
  text-align: center;
  font-size: 0.75rem;
  font-weight: 500;
}

.attendance-message.success {
  background: #d4edda;
  color: #155724;
}

.attendance-message.error {
  background: #f8d7da;
  color: #721c24;
}

/* Geofencing Status Styles */
.geofencing-status {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px;
  margin-top: 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
}

.geofencing-success {
  background: rgba(34, 197, 94, 0.12);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #15803d;
}

.geofencing-error {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #b91c1c;
}

.status-icon {
  font-size: 1.1rem;
  font-weight: bold;
}

.face-capture-modal {
  position: fixed;
  inset: 0;
  z-index: 1100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(15, 23, 42, 0.68);
}

.face-capture-content {
  width: min(100%, 520px);
  padding: 24px;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 18px 50px rgba(15, 23, 42, 0.24);
}

.face-capture-content h3 { margin: 0 0 8px; color: #333; }
.face-capture-instruction { margin: 0 0 16px; color: #6b6b6b; }
.camera-container { position: relative; overflow: hidden; min-height: 240px; border-radius: 8px; background: #111827; }
.camera-container video { display: block; width: 100%; min-height: 240px; object-fit: cover; }
.camera-error { padding: 24px; color: #fecaca; text-align: center; }
.face-capture-buttons { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
.btn-capture, .btn-cancel { padding: 10px 14px; border: 0; border-radius: 8px; cursor: pointer; font-weight: 600; }
.btn-capture { background: #16a34a; color: #fff; }
.btn-capture:disabled { cursor: not-allowed; opacity: .55; }
.btn-cancel { background: #e5e7eb; color: #1f2937; }

/* Modal styles */
.info-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.info-modal {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.info-modal h3 {
  margin: 0 0 8px;
  color: #333333;
}

.info-sub {
  margin: 0 0 16px;
  color: #6b6b6b;
  font-size: 0.9rem;
}

.info-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-label {
  font-weight: 600;
  color: #7a2b00;
}

.info-value {
  color: #8a4b1a;
}

.info-input {
  padding: 8px 12px;
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 6px;
  width: 200px;
}

.info-error {
  color: #dc3545;
  background: #f8d7da;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.info-success {
  color: #155724;
  background: #d4edda;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 12px;
}

.info-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn-outline {
  padding: 8px 16px;
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 6px;
  background: transparent;
  color: #333333;
  cursor: pointer;
}

.btn-primary {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  background: linear-gradient(180deg,#ff8a4b,#ff7043);
  color: white;
  cursor: pointer;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Header profile dropdown */
.header-profile-wrapper { position: relative; display: inline-block }
.profile-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(2,6,23,0.12);
  padding: 6px;
  z-index: 1200;
  min-width: 140px;
}
.profile-dropdown .dropdown-item {
  display: block;
  width: 100%;
  padding: 8px 12px;
  text-align: left;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 6px;
}
.profile-dropdown .dropdown-item:hover { background: #f5f5f5 }

/* header profile button (match Manager panels) - ensure styles apply inside OwnerPanelLayout slot */
:deep(.header-actions-top .header-profile-btn) { border: 1px solid rgba(0,0,0,0.08); background: #fff; padding: 6px 10px; border-radius: 8px; display:flex; gap:8px; align-items:center }
:deep(.header-actions-top .header-avatar) { width:36px; height:36px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f3f4f6; margin-right:8px }
:deep(.header-actions-top .header-avatar-img) { width:100%; height:100%; background-size:cover; background-position:center }
:deep(.header-actions-top .header-avatar-initials) { font-weight:700; color:#374151 }
:deep(.header-actions-top .header-name) { font-size: 0.8rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 320px }

/* New list/card styles to replace tables in staff inventory panel */
.inventory-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.inventory-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #fff;
  border: 1px solid #f0e7df;
  border-radius: 10px;
}
.inventory-item .item-left {
  flex: 1 1 60%;
}
.inventory-item .item-name { font-weight: 600; color: #3b2a12; }
.inventory-item .item-meta { font-size: 13px; color: #777; margin-top: 4px }
.inventory-item .item-right { display:flex; align-items:center; gap:16px }
.item-stats { display:flex; gap:12px; align-items:center }
.stat { text-align:center }
.stat-label { font-size:11px; color:#888 }
.stat-value { font-weight:700; color:#222 }
.item-action { min-width:160px; display:flex; justify-content:flex-end }

.requests-list-items { display:flex; flex-direction:column; gap:10px }
.request-item { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:#fff; border-radius:10px; border:1px solid #f3efe8 }
.request-left { flex:1 1 50%; font-weight:600 }
.request-mid { flex:0 0 160px; color:#444; text-align:left }
.request-right { flex:0 0 180px; text-align:right }
.request-updated { font-size:12px; color:#777; margin-top:6px }

</style>

<!-- Global override: ensure admin side column is not sticky for this panel -->
<style>
.admin-layout.no-profile-column .admin-side {
  position: static !important;
  top: auto !important;
  align-self: stretch !important;
  margin-top: 0 !important;
  max-height: none !important;
  overflow: visible !important;
  padding-right: 0 !important;
}
.announcements-panel .panel-header,
.announcements-panel .panel-body {
  position: static !important;
  max-height: none !important;
  overflow: visible !important;
}
@media (min-width: 1000px) {
  .admin-layout.no-profile-column .admin-side {
    position: static !important;
    margin-top: 0 !important;
  }
}

/* Ensure admin-side content sits below the ProductList header/profile area on wider screens.
   Increase offset so the side column aligns with the Product List panel. Use the same
   breakpoint as ProductList responsive rules. */
@media (min-width: 880px) {
  .admin-side {
    margin-top: 100px !important;
  }
}

/* Specifically nudge the announcements panel lower so it doesn't overlap
   with the ProductList header area in this inventory panel. Tune value as needed. */
@media (min-width: 880px) {
  .admin-side .announcements-panel {
    margin-top: 60px !important;
  }
}
</style>
