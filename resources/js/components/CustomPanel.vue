<template>
  <div class="custom-panel-container" :class="{ 'sidebar-collapsed': sidebarCollapsed, 'sidebar-mobile': isMobileViewport }">
    <aside class="custom-sidebar" :aria-hidden="isMobileViewport && sidebarCollapsed" :inert="isMobileViewport && sidebarCollapsed">
      <div class="custom-sidebar__brand">
        <img :src="logoImg" alt="Chikin Tayo" class="panel-logo" />
        <span class="custom-sidebar__brand-name">Chikin Tayo</span>
      </div>

      <nav class="custom-sidebar__nav" aria-label="Assigned modules">
        <p class="custom-sidebar__label">WORKSPACE</p>
        <button
          v-for="module in modules"
          :key="module"
          type="button"
          class="custom-sidebar__link"
          :class="{ 'custom-sidebar__link--alert': pendingCount(module) > 0 }"
          :title="moduleLabel(module)"
          :aria-current="selectedFeature === module ? 'page' : undefined"
          @click="selectFeature(module)"
        >
          <span class="custom-sidebar__link-icon">{{ moduleLabel(module).charAt(0) }}</span>
          <span class="custom-sidebar__link-name">{{ moduleLabel(module) }}</span>
          <span v-if="pendingCount(module) > 0" class="custom-sidebar__badge">{{ pendingCount(module) > 99 ? '99+' : pendingCount(module) }}</span>
        </button>
        <p v-if="modules.length === 0" class="custom-sidebar__empty">No modules assigned</p>
      </nav>

      <footer class="custom-sidebar__footer">
        <div class="custom-sidebar__last-login">
          <span>Last login</span>
          <strong>{{ userLastLoginLabel }}</strong>
        </div>
        <button type="button" class="custom-sidebar__logout" @click="logout" :disabled="isLoggingOut">
          <span aria-hidden="true">↪</span>
          <span>{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</span>
        </button>
      </footer>
    </aside>

    <button
      v-if="isMobileViewport && !sidebarCollapsed"
      type="button"
      class="custom-sidebar-overlay"
      aria-label="Close navigation menu"
      @click="sidebarCollapsed = true"
    ></button>

    <div class="custom-panel-main">
      <header class="panel-header">
        <div class="header-inner">
          <button
            type="button"
            class="custom-sidebar-toggle"
            :aria-label="sidebarCollapsed ? 'Open navigation menu' : 'Collapse navigation menu'"
            :aria-expanded="(!sidebarCollapsed).toString()"
            @click="toggleSidebar"
          >
            <span aria-hidden="true">☰</span>
          </button>
          <div class="topbar-context">
            <span class="topbar-context__eyebrow">CUSTOM ACCOUNT</span>
            <strong>Workspace</strong>
          </div>
          <div class="topbar-actions">
            <OwnerMessageMenu />
            <PanelNotificationMenu
              :notification-items="customNotificationItems"
              :notification-count="customNotificationCount"
              :announcements="announcements"
              @open="refreshCustomNotifications"
              @select="handleCustomNotificationClick"
            />
            <div class="topbar-user-pill">
              <span class="topbar-user-pill__avatar">{{ userInitials }}</span>
              <span class="topbar-user-pill__name">{{ userFullName }}</span>
            </div>
          </div>
        </div>
      </header>

      <section class="panel-main">
      <div class="panel-main-inner">
        <div class="dashboard-welcome">
          <div class="dashboard-welcome__copy">
            <h1>{{ greetingLabel }}</h1>
            <p class="dashboard-welcome__subtitle">Your tools and daily attendance, all in one place.</p>
          </div>
        </div>

        <section v-if="selectedFeature" class="custom-feature-view" :aria-labelledby="'custom-feature-title-' + selectedFeature">
          <div class="custom-feature-view__header">
            <div>
              <p class="dashboard-eyebrow">SELECTED FEATURE</p>
              <h2 :id="'custom-feature-title-' + selectedFeature">{{ moduleLabel(selectedFeature) }}</h2>
            </div>
            <button type="button" class="custom-feature-view__close" @click="selectedFeature = ''">Close</button>
          </div>
          <component
            v-if="selectedFeatureComponent"
            :is="selectedFeatureComponent"
            :key="selectedFeature"
            :embedded-section="featureSections[selectedFeature] || ''"
          />
          <p v-else class="custom-feature-view__empty">
            This feature is assigned to your account, but its screen is not available in the custom workspace yet.
          </p>
        </section>

        <div v-if="!selectedFeature" class="custom-dashboard-grid">
          <section class="custom-dashboard-section custom-dashboard-section--attendance" aria-label="Attendance">
            <div class="attendance-card">
              <div class="attendance-header">
                <div class="attendance-heading">
                  <span class="attendance-title">Today’s attendance</span>
                  <span class="attendance-subtitle">Your shift status and recorded times.</span>
                </div>
                <span :class="['attendance-status-badge', attendanceStatus.is_clocked_in ? 'status-on-duty' : 'status-off-duty']">
                  {{ attendanceStatus.is_clocked_in ? 'On Duty' : 'Off Duty' }}
                </span>
              </div>
              <div class="attendance-body">
                <section class="attendance-summary" aria-labelledby="custom-attendance-summary-title">
                  <div class="attendance-summary__heading">
                    <h2 id="custom-attendance-summary-title">Shift summary</h2>
                    <p>{{ attendanceStatus.is_clocked_in ? 'Your shift is currently in progress.' : 'Your shift has not started yet.' }}</p>
                  </div>
                  <div class="attendance-times">
                    <div class="time-row">
                      <span class="time-label">Clock in</span>
                      <span class="time-value">{{ attendanceStatus.clock_in_time || '—' }}</span>
                    </div>
                    <div class="time-row">
                      <span class="time-label">Clock out</span>
                      <span class="time-value">{{ attendanceStatus.clock_out_time || '—' }}</span>
                    </div>
                    <div class="time-row">
                      <span class="time-label">Hours worked</span>
                      <span class="time-value">{{ attendanceStatus.hours_worked > 0 ? `${attendanceStatus.hours_worked} hrs` : '—' }}</span>
                    </div>
                  </div>
                </section>

                <section class="attendance-actions" aria-labelledby="custom-attendance-actions-title">
                  <div class="attendance-actions__heading">
                    <h2 id="custom-attendance-actions-title">Shift actions</h2>
                    <p>Clock in or out when you are ready.</p>
                  </div>
                  <div class="attendance-buttons">
                    <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing" class="btn-clock-in">
                      {{ isAttendanceProcessing ? 'Processing...' : 'Clock in' }}
                    </button>
                    <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut || !canClockInGeofencing || locationLoading" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">
                      {{ (isAttendanceProcessing || locationLoading) ? 'Processing...' : 'Clock out' }}
                    </button>
                  </div>
                </section>
              </div>

              <div v-if="locationError" class="geofencing-status geofencing-error" role="status">
                <span class="status-icon">!</span>
                <span>{{ locationError }}</span>
              </div>
              <div v-else-if="userLocation && canClockInGeofencing" class="geofencing-status geofencing-success" role="status">
                <span class="status-icon">✓</span>
                <span>Location verified</span>
              </div>
              <div v-else-if="!canClockInGeofencing && geofencingMessage" class="geofencing-status geofencing-error" role="status">
                <span class="status-icon">!</span>
                <span>{{ geofencingMessage }}</span>
              </div>

              <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction" role="status">
                <span class="restriction-icon" aria-hidden="true">!</span>
                <span>Cannot clock out before {{ scheduledTimeOut }}</span>
              </div>
              <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]" role="status" aria-live="polite">{{ attendanceMessage }}</div>
            </div>
          </section>

        </div>

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
                <span v-if="!isCapturing">Capture Photo</span>
                <span v-else>Capturing...</span>
              </button>
              <button v-if="cameraError" @click="startCamera" class="btn-capture">Try Again</button>
              <button @click="cancelFaceCapture" class="btn-cancel">Cancel</button>
            </div>
            <div v-if="capturedImage" class="captured-preview">
              <img :src="capturedImage" alt="Captured face" />
              <p class="preview-label">Photo captured!</p>
            </div>
          </div>
        </div>

      </div>
      </section>
    </div>

    <PanelNotificationDetailsModal
      :show="activeNotificationPanel === 'announcements'"
      title="Announcements"
      subtitle="Latest updates for your account."
      :show-announcements="true"
      :announcements="announcements"
      :loading="announcementsLoading"
      @close="activeNotificationPanel = ''"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { showToast } from './toastStore';
import OwnerMessageMenu from './OwnerMessageMenu.vue';
import PanelNotificationMenu from './PanelNotificationMenu.vue';
import PanelNotificationDetailsModal from './PanelNotificationDetailsModal.vue';
import ManagerFinancePanel from './ManagerFinancePanel.vue';
import ProcurementManagerPanel from './ProcurementManagerPanel.vue';
import ManagerLogisticsPanel from './ManagerLogisticsPanel.vue';
import InventoryStaffPanel from './inventory/InventoryStaffPanel.vue';
import ManagerHRPanel from './ManagerHRPanel.vue';
import AdminPanel from './adminpanel.vue';
import CustomAutomaticClockout from './CustomAutomaticClockout.vue';
import CustomAddSupplier from './CustomAddSupplier.vue';

const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href;
const router = useRouter();
const modules = ref([]);
const selectedFeature = ref('');
const moduleLabels = {
  'budget-request-approvals': 'Budget Request Approvals',
  'current-price-markup': 'Current Price Markup',
  'receipt-approval': 'Receipt Approval',
  'financial-overview': 'Financial Overview',
  'recent-transactions': 'Recent Transactions',
  'manual-procurement': 'Manual Procurement',
  'procurement-requests': 'Procurement Requests',
  'add-supplier': 'Add Supplier',
  'supplier-products': 'Supplier Products',
  'stock-confirmation-history': 'Stock Confirmation History',
  'product-barcodes': 'Product Barcodes',
  'pending-stock': 'Pending Stock',
  'request-new-product': 'Request New Product',
  'supplier-product-confirmations': 'Supplier Product Confirmations',
  'inventory-monitor': 'Inventory Monitor',
  'product-disposal': 'Product Disposal List',
  'landing-page-product-images': 'Landing Page Product Images',
  'staff-activity': 'Staff Activity',
  'automatic-clock-out': 'Automatic Clock-Out',
  'staff-management': 'Staff Management',
};
const featureComponents = {
  'budget-request-approvals': ManagerFinancePanel,
  'current-price-markup': ManagerFinancePanel,
  'receipt-approval': ManagerFinancePanel,
  'financial-overview': ManagerFinancePanel,
  'recent-transactions': ManagerFinancePanel,
  'manual-procurement': ProcurementManagerPanel,
  'procurement-requests': ProcurementManagerPanel,
  'add-supplier': CustomAddSupplier,
  'supplier-products': ManagerLogisticsPanel,
  'stock-confirmation-history': ManagerLogisticsPanel,
  'product-barcodes': ManagerLogisticsPanel,
  'pending-stock': ManagerLogisticsPanel,
  'request-new-product': AdminPanel,
  'supplier-product-confirmations': ManagerLogisticsPanel,
  'inventory-monitor': InventoryStaffPanel,
  'product-disposal': InventoryStaffPanel,
  'landing-page-product-images': AdminPanel,
  'staff-activity': ManagerHRPanel,
  'automatic-clock-out': CustomAutomaticClockout,
  'staff-management': ManagerHRPanel,
};
const featureSections = {
  'budget-request-approvals': 'approvals',
  'current-price-markup': 'markup',
  'receipt-approval': 'receipt-approvals',
  'financial-overview': 'overview',
  'recent-transactions': 'transactions',
  'manual-procurement': 'overview',
  'procurement-requests': 'budget-requests',
  'supplier-products': 'suppliers',
  'stock-confirmation-history': 'confirmation-history',
  'product-barcodes': 'barcodes',
  'pending-stock': 'pending-stock',
  'request-new-product': 'product-request',
  'supplier-product-confirmations': 'confirmation-history',
  'inventory-monitor': 'inventory-monitor',
  'product-disposal': 'product-disposal',
  'landing-page-product-images': 'landing-page-product-images',
  'staff-activity': 'overview',
  'staff-management': 'overview',
};
const sidebarCollapsed = ref(false);
const isMobileViewport = ref(false);
const isLoggingOut = ref(false);
const userFullName = ref('User');
const userInitials = ref('U');
const userLastLoginRaw = ref('');
const panelDescriptions = ref({});
const attendanceStatus = ref({
  is_clocked_in: false,
  clock_in_time: null,
  clock_out_time: null,
  hours_worked: 0
});
const isAttendanceProcessing = ref(false);
const attendanceMessage = ref('');
const attendanceMessageType = ref('');
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
let locationInterval = null;
let sidebarMediaQuery = null;
let sidebarViewportChange = null;

const notificationCounts = ref({});
const notificationSummary = ref({ announcements: 0 });
const announcements = ref([]);
const announcementsLoading = ref(false);
const activeNotificationPanel = ref('');
const isNotificationsLoading = ref(false);
const hasNotified = ref(false);

const buildInitials = (name) => {
  const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
  if (!parts.length) return 'U';
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return `${parts[0].charAt(0)}${parts[parts.length - 1].charAt(0)}`.toUpperCase();
};

const applyUserProfile = (user) => {
  if (!user) return;
  const fullName = String(user.full_name || user.name || user.username || 'User').trim();
  userFullName.value = fullName || 'User';
  userInitials.value = buildInitials(fullName || user.username || 'User');
  userLastLoginRaw.value = String(user.last_login_at || '');
};

const userFirstName = computed(() => {
  const parts = String(userFullName.value || '').trim().split(/\s+/).filter(Boolean);
  return parts.length ? parts[0] : 'User';
});

const greetingLabel = computed(() => {
  const hour = new Date().getHours();
  let greeting = 'Good evening';
  if (hour < 12) greeting = 'Good morning';
  else if (hour < 18) greeting = 'Good afternoon';
  return `${greeting}, ${userFirstName.value}! 👋`;
});

const userLastLoginLabel = computed(() => {
  const raw = String(userLastLoginRaw.value || '').trim();
  if (!raw) return 'N/A';
  const dt = new Date(raw);
  if (Number.isNaN(dt.getTime())) return raw;
  return dt.toLocaleString('en-PH', {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
});

const scheduledTimeOut = computed(() => {
  const time = attendanceSettings.value.scheduled_time_out || '17:00:00';
  const [hours, minutes] = time.split(':');
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const hour12 = hour % 12 || 12;
  return `${hour12}:${minutes} ${ampm}`;
});

const canClockOut = computed(() => {
  if (!attendanceStatus.value.is_clocked_in) return false;
  if (attendanceSettings.value.early_clockout_override) return true;

  const now = new Date();
  const currentHours = now.getHours();
  const currentMinutes = now.getMinutes();
  const [scheduledHours, scheduledMinutes] = (attendanceSettings.value.scheduled_time_out || '17:00:00').split(':');
  const currentTotalMinutes = currentHours * 60 + currentMinutes;
  const scheduledTotalMinutes = parseInt(scheduledHours) * 60 + parseInt(scheduledMinutes);
  return currentTotalMinutes >= scheduledTotalMinutes;
});

const loadAttendanceStatus = async () => {
  try {
    const res = await axios.get('/api/staff/attendance/status', { withCredentials: true });
    if (res.data && res.data.ok) {
      attendanceStatus.value = {
        is_clocked_in: res.data.status?.is_clocked_in || false,
        clock_in_time: res.data.status?.clock_in_time || null,
        clock_out_time: res.data.status?.clock_out_time || null,
        hours_worked: res.data.status?.hours_worked || 0
      };
    }
  } catch (e) {
    console.error('Failed to load attendance status:', e);
  }
};

// Geofencing methods
const getUserLocation = async () => {
  locationLoading.value = true;
  locationError.value = '';
  canClockInGeofencing.value = true;
  geofencingMessage.value = '';

  if (!navigator.geolocation) {
    locationError.value = 'Geolocation is not supported by your browser';
    canClockInGeofencing.value = false;
    locationLoading.value = false;
    return;
  }

  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      });
    });

    userLocation.value = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude
    };
    if (attendanceMessageType.value === 'warning') {
      attendanceMessage.value = '';
      attendanceMessageType.value = '';
    }
  } catch (error) {
    console.error('Error getting location:', error);
    locationError.value = 'Unable to retrieve your location. Please enable location services.';
    canClockInGeofencing.value = false;
    userLocation.value = null;
  } finally {
    locationLoading.value = false;
  }
};

const loadAttendanceSettings = async () => {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true });
    if (res.data && res.data.ok && res.data.data) {
      attendanceSettings.value = {
        early_clockout_override: res.data.data.early_clockout_override || false,
        scheduled_time_out: res.data.data.scheduled_time_out || '17:00:00'
      };
    }
  } catch (e) {
    console.error('Failed to load attendance settings:', e);
    attendanceSettings.value = {
      early_clockout_override: false,
      scheduled_time_out: '17:00:00'
    };
  }
};

const performClockIn = async () => {
  if (isAttendanceProcessing.value) return;

  attendanceMessage.value = '';
  attendanceMessageType.value = '';

  showFaceCapture.value = true;
  capturedImage.value = null;
  cameraError.value = '';
  await startCamera();
};

const startCamera = async () => {
  stopCamera();
  cameraError.value = '';

  if (!navigator.mediaDevices?.getUserMedia) {
    cameraError.value = 'Camera access is unavailable. Use HTTPS or localhost and enable a camera.';
    return;
  }

  try {
    let stream;
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
      });
    } catch (error) {
      if (error.name !== 'OverconstrainedError' && error.name !== 'NotFoundError') throw error;
      stream = await navigator.mediaDevices.getUserMedia({ video: true });
    }
    cameraStream.value = stream;
    await nextTick();
    if (cameraVideo.value) cameraVideo.value.srcObject = stream;
  } catch (error) {
    console.error('Camera access error:', error);
    cameraError.value = error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError'
      ? 'Camera permission is blocked. Allow camera access, then click Try Again.'
      : 'Unable to access camera. Check browser permissions, then click Try Again.';
    stopCamera();
  }
};

const stopCamera = () => {
  if (cameraStream.value) {
    cameraStream.value.getTracks().forEach(track => track.stop());
    cameraStream.value = null;
  }
};

const capturePhoto = () => {
  const video = cameraVideo.value;
  const canvas = cameraCanvas.value;
  if (!video || !canvas || !video.videoWidth) {
    attendanceMessage.value = 'Camera not ready. Please try again.';
    attendanceMessageType.value = 'error';
    return;
  }

  isCapturing.value = true;
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
  capturedImage.value = canvas.toDataURL('image/jpeg', 0.8);
  stopCamera();
  showFaceCapture.value = false;
  proceedWithClockIn();
};

const cancelFaceCapture = () => {
  stopCamera();
  showFaceCapture.value = false;
  capturedImage.value = null;
  cameraError.value = '';
  isCapturing.value = false;
};

const proceedWithClockIn = async () => {
  if (isAttendanceProcessing.value) return;
  if (!capturedImage.value) {
    attendanceMessage.value = 'Face photo is required to clock in';
    attendanceMessageType.value = 'warning';
    return;
  }

  if (!userLocation.value) {
    attendanceMessage.value = 'Please enable location services to clock in';
    attendanceMessageType.value = 'warning';
    await getUserLocation();
    if (!userLocation.value) {
      showFaceCapture.value = false;
      isCapturing.value = false;
      setTimeout(() => { attendanceMessage.value = ''; }, 3000);
      return;
    }
  }

  isAttendanceProcessing.value = true;
  attendanceMessage.value = '';

  try {
    const res = await axios.post('/api/staff/clock-in', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude,
      face_image: capturedImage.value
    }, { withCredentials: true });
    
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked in successfully!';
      attendanceMessageType.value = 'success';
      await loadAttendanceStatus();
    } else if (res.data.geofencing_error) {
      attendanceMessage.value = res.data.message || 'You are not within the branch vicinity';
      attendanceMessageType.value = 'error';
      canClockInGeofencing.value = false;
      geofencingMessage.value = res.data.message;
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in';
      attendanceMessageType.value = 'error';
    }
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.geofencing_error) {
      attendanceMessage.value = e.response.data.message || 'You are not within the branch vicinity';
      attendanceMessageType.value = 'error';
      canClockInGeofencing.value = false;
      geofencingMessage.value = e.response.data.message;
    } else {
      attendanceMessage.value = e.response?.data?.message || 'Error clocking in';
      attendanceMessageType.value = 'error';
    }
  } finally {
    isAttendanceProcessing.value = false;
    isCapturing.value = false;
    setTimeout(() => { attendanceMessage.value = ''; }, 3000);
  }
};

const performClockOut = async () => {
  if (isAttendanceProcessing.value) return;

  if (!userLocation.value) {
    attendanceMessage.value = 'Please enable location services to clock out';
    attendanceMessageType.value = 'warning';
    await getUserLocation();
    setTimeout(() => { attendanceMessage.value = ''; }, 3000);
    return;
  }

  isAttendanceProcessing.value = true;
  attendanceMessage.value = '';

  try {
    const res = await axios.post('/api/staff/clock-out', {
      latitude: userLocation.value.latitude,
      longitude: userLocation.value.longitude
    }, { withCredentials: true });
    
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked out successfully!';
      attendanceMessageType.value = 'success';
      await loadAttendanceStatus();
    } else if (res.data.geofencing_error) {
      attendanceMessage.value = res.data.message || 'You are not within the branch vicinity';
      attendanceMessageType.value = 'error';
      canClockInGeofencing.value = false;
      geofencingMessage.value = res.data.message;
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out';
      attendanceMessageType.value = 'error';
    }
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.geofencing_error) {
      attendanceMessage.value = e.response.data.message || 'You are not within the branch vicinity';
      attendanceMessageType.value = 'error';
      canClockInGeofencing.value = false;
      geofencingMessage.value = e.response.data.message;
    } else {
      attendanceMessage.value = e.response?.data?.message || 'Error clocking out';
      attendanceMessageType.value = 'error';
    }
  } finally {
    isAttendanceProcessing.value = false;
    setTimeout(() => { attendanceMessage.value = ''; }, 3000);
  }
};

const loadPanelDescriptions = async () => {
  try {
    const res = await axios.get('/api/panel-descriptions', { withCredentials: true });
    const descriptions = res?.data?.descriptions;
    if (descriptions && typeof descriptions === 'object') {
      panelDescriptions.value = descriptions;
    }
  } catch (e) {
    // Keep local fallback descriptions when endpoint is unavailable.
  }
};

onMounted(() => {
  sidebarMediaQuery = window.matchMedia('(max-width: 900px)');
  sidebarViewportChange = (event) => {
    isMobileViewport.value = event.matches;
    sidebarCollapsed.value = event.matches;
  };
  sidebarViewportChange(sidebarMediaQuery);
  if (sidebarMediaQuery.addEventListener) {
    sidebarMediaQuery.addEventListener('change', sidebarViewportChange);
  } else {
    sidebarMediaQuery.addListener(sidebarViewportChange);
  }

  loadPanelDescriptions();
  loadPanelNotifications();

  try {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    applyUserProfile(user);
    if (user && Array.isArray(user.permissions?.modules)) {
      modules.value = user.permissions.modules.map(m => (m || '').toLowerCase());
    }
  } catch (e) {
    modules.value = [];
  }

  // Always refresh from authenticated session source to show real user profile.
  axios.get('/api/me', { withCredentials: true }).then(res => {
    const rawUser = res?.data?.user?.user || res?.data?.user || null;
    if (rawUser) {
      const normalized = {
        id: rawUser.id,
        username: rawUser.username || rawUser.user_name || rawUser.name || '',
        role: (rawUser.role || '').toLowerCase(),
        department: (rawUser.department || '').toLowerCase(),
        full_name: rawUser.full_name || rawUser.name || '',
        last_login_at: rawUser.last_login_at || '',
        branch_id: rawUser.branch_id || null,
        permissions: rawUser.permissions || {},
      };

      let cached = null;
      try {
        cached = JSON.parse(localStorage.getItem('user') || 'null');
      } catch (e) {
        cached = null;
      }
      if (cached?.id && String(cached.id) !== String(normalized.id)) {
        return;
      }

      applyUserProfile(normalized);

      try {
        localStorage.setItem('user', JSON.stringify({ ...(cached || {}), ...normalized }));
      } catch (e) {
        localStorage.setItem('user', JSON.stringify(normalized));
      }

      const perms = normalized.permissions || null;
      if (perms && Array.isArray(perms.modules)) {
        modules.value = perms.modules.map(m => (m || '').toLowerCase());
      }
    }

    // Keep token bootstrap behavior if API returns a token payload.
    const token = res.data.token || res.data.access_token || null;
    if (token) {
      localStorage.setItem('token', token);
      if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      }
      console.log('[CustomPanel] Token stored for API auth');
    }
  }).catch((err) => {
    console.warn('[CustomPanel] /api/me failed:', err.response?.status);
  });

  loadAttendanceStatus();
  loadAttendanceSettings();
  getUserLocation();
  
  // Refresh location every 5 minutes
  locationInterval = setInterval(getUserLocation, 300000);
});

onUnmounted(() => {
  stopCamera();
  if (locationInterval) clearInterval(locationInterval);
  if (sidebarMediaQuery && sidebarViewportChange) {
    if (sidebarMediaQuery.removeEventListener) {
      sidebarMediaQuery.removeEventListener('change', sidebarViewportChange);
    } else {
      sidebarMediaQuery.removeListener(sidebarViewportChange);
    }
    sidebarViewportChange = null;
  }
});

const loadPanelNotifications = async () => {
  isNotificationsLoading.value = true;
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true });
    if (res.data && res.data.ok) {
      notificationCounts.value = res.data.counts || {};
      notificationSummary.value = {
        announcements: Number(res.data.extras?.announcements ?? res.data.summary?.announcements ?? 0),
      };
      const total = Object.values(notificationCounts.value || {}).reduce((sum, v) => sum + Number(v || 0), 0)
        + notificationSummary.value.announcements;
      if (!hasNotified.value && total > 0) {
        showToast('You have pending items in your modules.', 'info');
        hasNotified.value = true;
      }
    }
  } catch (e) {
    notificationCounts.value = {};
    notificationSummary.value = { announcements: 0 };
    console.error('Failed to load custom panel notifications:', e);
  } finally {
    isNotificationsLoading.value = false;
  }
};

const pendingCount = (m) => {
  const key = String(m || '').toLowerCase();
  const raw = notificationCounts.value[key] ?? 0;
  const count = Number(raw || 0);
  return Number.isNaN(count) ? 0 : count;
};

const customNotificationItems = computed(() => [
  ...modules.value.map(module => ({
    key: module,
    label: prettyName(module),
    count: pendingCount(module),
  })),
  { key: 'announcements', label: 'Announcements', count: notificationSummary.value.announcements },
].filter(item => item.count > 0));

const customNotificationCount = computed(() => (
  customNotificationItems.value.reduce((total, item) => total + item.count, 0)
));

const loadAnnouncements = async () => {
  announcementsLoading.value = true;
  try {
    const res = await axios.get('/api/announcements', { withCredentials: true });
    const data = res.data?.announcements ?? res.data?.data ?? [];
    announcements.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Failed to load custom panel announcements:', error);
    showToast('Unable to load announcements.', 'error');
  } finally {
    announcementsLoading.value = false;
  }
};

const refreshCustomNotifications = async () => {
  await Promise.all([loadPanelNotifications(), loadAnnouncements()]);
};

const handleCustomNotificationClick = async (key) => {
  if (key !== 'announcements') {
    goToModule(key);
    return;
  }

  activeNotificationPanel.value = 'announcements';
  await loadAnnouncements();
  try {
    const res = await axios.post('/api/panel-notifications/read', { category: 'announcements' }, { withCredentials: true });
    if (!res.data?.ok) throw new Error(res.data?.message || 'Unable to update announcement read status.');
    await loadPanelNotifications();
  } catch (error) {
    console.error('Failed to mark custom panel announcements as read:', error);
    showToast(error.response?.data?.message || 'Unable to update announcement read status.', 'error');
  }
};

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};

const prettyName = (m) => {
  if (!m) return 'Unknown';
  return m.charAt(0).toUpperCase() + m.slice(1);
}

const moduleLabel = (m) => moduleLabels[String(m || '').toLowerCase()] || prettyName(m);

const selectedFeatureComponent = computed(() => featureComponents[selectedFeature.value] || null);

const selectFeature = (feature) => {
  selectedFeature.value = selectedFeature.value === feature ? '' : feature;
  if (isMobileViewport.value) sidebarCollapsed.value = true;
};

const panelDescription = (m) => {
  const fallbackDescriptions = {
    admin: 'View operational KPIs, monitor orders and sales, manage staff access, and handle approvals like dishes, announcements, and price markups.',
    finance: 'Track branch financial KPIs, review transactions and reports, approve budget requests, confirm supplier receipts, and manage branch budgets.',
    inventory: 'Manage product inventory with stock monitoring, low-stock tracking, stock confirmations, procurement history, and inventory reporting tools.',
    hr: 'Manage staff records and status, monitor HR metrics, open staff management tools, and control attendance settings and HR reports.',
    logistics: 'Monitor inventory health, create procurement and product requests, track request statuses, and coordinate branch logistics workflows.',
    procurement: 'Handle supplier operations, review branch procurement history, process logistics requests, and manage budget and order progression.',
    kitchen: 'Create and manage dishes with ingredient mappings, monitor kitchen order queues, mark orders done, and update ingredient stock.',
    cashier: 'Run POS transactions with cart, discounts, VAT, and payments, print receipts, and monitor recent transactions with refund actions.',
    reports: 'Access cross-module reporting endpoints for sales, staff performance, inventory, and finance analytics and exports.',
  };
  const key = String(m || '').toLowerCase();
  return panelDescriptions.value[key] || fallbackDescriptions[key] || `Open ${prettyName(m)} panel`;
}

const goToModule = (m) => {
  // Map modules to existing panels/routes and include a query marker so
  // the target panel can show a "Back to Custom Panel" control.
  const q = { query: { from: 'custom-panel' } }
  const mod = (m || '').toLowerCase();
  if (mod === 'admin') return router.push({ path: '/admin-panel', ...q });
  if (mod === 'finance') return router.push({ path: '/manager/finance', ...q });
  if (mod === 'procurement') return router.push({ path: '/manager/procurement', ...q });
  if (mod === 'logistics') return router.push({ path: '/manager/logistics', ...q });
  if (mod === 'inventory') return router.push({ path: '/staff/inventory', ...q });
  if (mod === 'hr') return router.push({ path: '/manager/hr', ...q });
  if (mod === 'kitchen') return router.push({ path: '/staff/kitchen', ...q });
  if (mod === 'cashier') return router.push({ path: '/staff/cashier', ...q });
  if (mod === 'supplier') return router.push({ path: '/supplier-panel', ...q });
  // Fallback
  return router.push({ path: '/admin-panel', ...q });
}

const logout = async () => {
  if (isLoggingOut.value) return;
  const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(true));
  if (!ok) return;
  isLoggingOut.value = true;
  try { await axios.post('/api/logout', {}, { withCredentials: true }); } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  delete axios.defaults.headers.common['Authorization'];
  try {
    await router.replace('/admin-login');
  } catch (e) {
    window.location.replace('/admin-login');
  }
}
</script>

<style scoped>
.custom-panel-container {
  min-height: 100vh;
  width: 100%;
  background: #F8FAFC;
  display: flex;
  flex-direction: column;
}
.panel-header { width: 100%; background: #FFFFFF; border-bottom: 1px solid #E5E7EB; box-shadow: 0 4px 14px rgba(15,23,42,0.06) }
.header-inner { width: 100%; padding: 18px 16px; display: flex; align-items: center; justify-content: space-between; gap: 18px }
.header-left { display: flex; align-items: center; gap: 36px; min-width: 0; flex: 1 }
.panel-main {
  flex: 1;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
}
.panel-main-inner { width: 100%; max-width: 1240px }
.custom-feature-view { margin: 0 0 24px; padding: 20px; background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; box-shadow: 0 8px 18px rgba(0,0,0,0.06); }
.custom-feature-view__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 18px; }
.custom-feature-view__header h2 { margin: 4px 0 0; color: #1F2937; }
.custom-feature-view__close { border: 1px solid #E5E7EB; border-radius: 8px; padding: 8px 12px; background: #fff; color: #374151; cursor: pointer; }
.custom-feature-view__close:hover { border-color: #F97316; color: #C2410C; }
.custom-feature-view__empty { margin: 0; color: #6B7280; }

.custom-feature-view > :deep(.manager-procurement-panel),
.custom-feature-view > :deep(.manager-logistics-panel),
.custom-feature-view > :deep(.inventory-staff-panel-shell),
.custom-feature-view > :deep(.manager-finance),
.custom-feature-view > :deep(.owner-panel-layout--embedded),
.custom-feature-view > :deep(.admin-panel-shell--embedded) {
  min-height: 0;
  height: auto;
  background: transparent;
}

.custom-feature-view > :deep(.manager-procurement-panel--embedded),
.custom-feature-view > :deep(.manager-logistics-panel--embedded) {
  color: #3d2a1f;
}

.custom-feature-view > :deep(.owner-panel-layout--embedded .admin-main) {
  min-height: 0;
  padding: 0;
}

.custom-feature-view > :deep(.owner-panel-layout--embedded .admin-layout) {
  min-height: 0 !important;
  width: 100% !important;
  max-width: none !important;
  display: block !important;
  grid-template-columns: minmax(0, 1fr) !important;
}

.custom-feature-view > :deep(.owner-panel-layout--embedded .admin-page),
.custom-feature-view > :deep(.owner-panel-layout--embedded .admin-main) {
  width: 100% !important;
  max-width: none !important;
  min-width: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
}
.panel-logo { display:block; width:58px; height:auto; flex-shrink: 0 }
.cards-greeting-row { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; margin: 0 0 52px 0 }
.cards-greeting-text { font-size: 18px; font-weight: 700; color: #1F2937 }
.profile-row { display:flex; align-items:center; gap:14px; flex-wrap: nowrap; white-space: nowrap; min-width: 0; overflow: hidden }
.profile-name { font-size:13px; font-weight:700; color:#1F2937; line-height:1.2 }
.profile-role { font-size:12px; color:#6B7280 }
.profile-status { display:flex; align-items:center; gap:6px; font-size:12px; color:#4B5563 }
.status-dot { width:8px; height:8px; border-radius:50%; background:#22C55E; box-shadow:0 0 0 3px rgba(34,197,94,0.14) }
.profile-last-login { font-size:12px; color:#6B7280 }
.nav-logout { flex-shrink: 0 }
.empty-message { color:#6B7280; text-align:center; margin:12px 0 }

.modules-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:22px; margin:36px auto 18px auto; width:100%; align-items:start }
.module-tile { position: relative; display:flex; gap:14px; align-items:center; align-self:start; padding:18px; background:#fff; border-radius:14px; border:1px solid #E5E7EB; box-shadow:0 8px 18px rgba(0,0,0,0.08); transition:transform .24s ease, box-shadow .24s ease, padding .28s ease, border-color .24s ease }
.module-tile--alert { border-color:#fb923c; box-shadow:0 0 0 2px rgba(251,146,60,0.18), 0 10px 20px rgba(251,146,60,0.2) }
.module-badge { position:absolute; top:-10px; right:-10px; min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#ffffff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(239,68,68,0.35) }
.module-tile:hover { transform:translateY(-4px); box-shadow:0 12px 24px rgba(249,115,22,0.20) }
.module-tile:focus { outline: none; box-shadow: 0 0 0 3px rgba(0,102,255,0.12) }
.module-tile:hover,
.module-tile:focus { padding-top: 22px; padding-bottom: 22px }

.module-icon { width:88px; height:58px; border-radius:12px; background: linear-gradient(135deg,#ff9f43,#ff6b3a); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:21px }
.module-info { display:flex; flex-direction:column; min-width:0 }
.module-name { font-weight:700; color:#1F2937; font-size:16px }
.module-desc {
  font-size:13px;
  color:#6B7280;
  line-height:1.4;
  max-height:0;
  opacity:0;
  overflow:hidden;
  margin-top:0;
  transform: translateY(-3px);
  transition: max-height .3s ease, opacity .24s ease, margin-top .24s ease, transform .24s ease;
}
.module-tile:hover .module-desc,
.module-tile:focus .module-desc {
  max-height: 72px;
  opacity:1;
  margin-top:6px;
  transform: translateY(0);
}

.btn { font-weight:600; border-radius:8px; padding:10px 16px; cursor:pointer; border:none }
.btn-ghost { background:#fff; color:#1F2937; border:1px solid #E5E7EB }
.btn-ghost:hover { background:#F3F4F6 }
.btn.btn-secondary {
  background: #F97316 !important;
  color: #FFFFFF !important;
  border: 1px solid #F97316 !important;
  border-radius: 8px;
  padding: 10px 16px;
  cursor: pointer;
  font-weight: 600;
}
.btn.btn-secondary:hover {
  background: #EA580C !important;
  border-color: #EA580C !important;
}

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
  background: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
}

.geofencing-error {
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  color: #721c24;
}

.face-capture-modal {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow-y: auto;
  padding: clamp(12px, 4vw, 24px);
  background: rgba(15, 23, 42, 0.68);
  backdrop-filter: blur(4px);
}

.face-capture-content {
  width: min(100%, 540px);
  max-height: min(90vh, 760px);
  overflow-y: auto;
  padding: clamp(18px, 3vw, 28px);
  border: 1px solid #f1e5d8;
  border-top: 4px solid #f59e0b;
  border-radius: 18px;
  background: #fffaf5;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
}

.face-capture-content h3 { margin: 0 0 8px; color: #3d2a1f; font-size: 1.35rem; font-weight: 700; text-align: center; }
.face-capture-instruction { margin: 0 0 1.1rem; color: #7c6758; font-size: 0.9rem; line-height: 1.5; text-align: center; }
.camera-container { position: relative; overflow: hidden; width: 100%; min-height: 220px; max-height: 52vh; aspect-ratio: 16 / 9; margin-bottom: 1.1rem; border-radius: 12px; background: #111827; }
.camera-container video { display: block; width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
.camera-error { position: absolute; top: 50%; left: 50%; width: min(90%, 360px); padding: 1rem; transform: translate(-50%, -50%); border-radius: 10px; background: rgba(185, 28, 28, 0.92); color: #fff; text-align: center; }
.face-capture-buttons { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-top: 0; }
.btn-capture, .btn-cancel { min-height: 46px; padding: 0.75rem 1rem; border: 1px solid transparent; border-radius: 10px; cursor: pointer; font-size: 0.95rem; font-weight: 600; transition: all 0.3s ease; }
.face-capture-content .btn-capture { border-color: #c2410c; background: linear-gradient(135deg, #c2410c, #d97706); color: #fff; }
.face-capture-content .btn-capture:hover:not(:disabled) { transform: translateY(-2px); border-color: #9a3412; background: linear-gradient(135deg, #9a3412, #b45309); color: #fff; box-shadow: 0 6px 14px rgba(154, 52, 18, 0.28); }
.face-capture-content .btn-capture:disabled { border-color: #8b5e3c; background: #8b5e3c; color: #fff; cursor: not-allowed; opacity: 1; }
.btn-cancel { border-color: #e2d8ce; background: #fff; color: #5b4638; }
.btn-cancel:hover { background: #f5eee7; color: #3d2a1f; }
.captured-preview { margin-top: 1rem; padding: 1rem; border: 1px solid #d6e7d8; border-radius: 12px; background: #f8f5f1; text-align: center; }
.captured-preview img { max-width: 100%; max-height: 220px; border-radius: 8px; }
.preview-label { margin: 6px 0 0; color: #166534; font-weight: 600; }

@media (max-width: 520px) {
  .face-capture-modal { align-items: flex-start; }
  .face-capture-content { max-height: calc(100vh - 24px); }
  .camera-container { min-height: 190px; }
}

.status-icon {
  font-size: 1.1rem;
  font-weight: bold;
}

@media (max-width:640px) {
  .header-inner { padding: 14px 16px; align-items: flex-start }
  .header-left { width: 100% }
  .header-left { gap: 30px }
  .panel-main { justify-content: flex-start; padding: 18px 14px }
  .panel-logo { width: 52px }
  .profile-row { flex-wrap: wrap; row-gap: 6px; white-space: normal; overflow: visible }
  .nav-logout { align-self: flex-end }
}

@media (max-width:420px) {
  .module-icon { width:62px; height:40px }
  .modules-grid { grid-template-columns: 1fr }
}

.custom-panel-container {
  --custom-ink: #172b4d;
  --custom-muted: #66758a;
  --custom-line: #e2e8f0;
  --custom-accent: #e46b2e;
  --custom-accent-dark: #bd4c1e;
  --custom-accent-soft: #fff2e9;
  min-height: 100vh;
  color: var(--custom-ink);
  background: #f3f6fa;
  font-family: Inter, "Segoe UI", sans-serif;
}

.panel-header {
  position: relative;
  z-index: 2;
  background: rgba(255, 255, 255, 0.96);
  border-bottom: 1px solid var(--custom-line);
  box-shadow: 0 3px 14px rgba(23, 43, 77, 0.04);
}

.header-inner {
  max-width: 1440px;
  min-height: 76px;
  margin: 0 auto;
  padding: 12px clamp(18px, 4vw, 52px);
}

.header-left { gap: 24px; }
.panel-logo { width: 68px; }
.profile-row { gap: 16px; }
.profile-identity { display: grid; gap: 3px; }
.profile-name { color: var(--custom-ink); font-size: 14px; }
.profile-role,
.profile-last-login { color: var(--custom-muted); font-size: 12px; }
.profile-divider { width: 1px; height: 32px; background: var(--custom-line); }
.profile-status {
  padding: 7px 10px;
  border: 1px solid #d8eee3;
  border-radius: 999px;
  color: #28704e;
  background: #f0faf4;
  font-size: 11px;
  font-weight: 700;
}
.status-dot { width: 7px; height: 7px; }
.profile-last-login { display: grid; gap: 2px; }
.profile-last-login span {
  color: #95a1b1;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
}

.nav-logout {
  padding: 9px 16px;
  border-color: #f0d8ca;
  border-radius: 9px;
  color: var(--custom-accent-dark);
  background: #fff;
  font-size: 13px;
}
.nav-logout:hover { border-color: #e9bda5; background: var(--custom-accent-soft); }
.nav-logout:disabled { cursor: wait; opacity: .65; }

.panel-main {
  display: block;
  width: 100%;
  padding: clamp(26px, 4vw, 48px) clamp(16px, 4vw, 52px) 56px;
}

.panel-main-inner { width: 100%; max-width: 1200px; margin: 0 auto; }

.dashboard-welcome {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  margin-bottom: 26px;
}

.dashboard-welcome__copy { min-width: 0; }
.dashboard-eyebrow {
  margin: 0 0 8px;
  color: var(--custom-accent-dark);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .12em;
}
.dashboard-welcome h1 {
  margin: 0;
  color: var(--custom-ink);
  font-size: clamp(25px, 3.3vw, 34px);
  font-weight: 750;
  letter-spacing: -.045em;
  line-height: 1.2;
}
.dashboard-welcome__subtitle {
  margin: 9px 0 0;
  color: var(--custom-muted);
  font-size: 14px;
  line-height: 1.5;
}
.attendance-card {
  margin: 0 0 34px;
  padding: 22px 24px;
  border: 1px solid var(--custom-line);
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 8px 24px rgba(23, 43, 77, .045);
}
.attendance-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}
.attendance-heading { display: grid; gap: 5px; }
.attendance-title { color: var(--custom-ink); font-size: 16px; font-weight: 750; }
.attendance-subtitle { color: var(--custom-muted); font-size: 12px; }
.attendance-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 11px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 750;
  white-space: nowrap;
}
.status-on-duty { color: #23724b; background: #eaf7ef; }
.status-off-duty { color: #8a5a28; background: #fff5e6; }
.attendance-times {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 10px;
  margin-bottom: 16px;
}
.time-row {
  display: grid;
  gap: 7px;
  padding: 12px 14px;
  border: 1px solid #edf1f5;
  border-radius: 11px;
  background: #f8fafc;
}
.time-label {
  color: #8794a6;
  font-size: 10px;
  font-weight: 750;
  letter-spacing: .07em;
  text-transform: uppercase;
}
.time-value { color: var(--custom-ink); font-size: 16px; font-weight: 700; }
.attendance-buttons { display: flex; gap: 10px; }
.btn-clock-in,
.btn-clock-out {
  min-height: 42px;
  flex: 1;
  padding: 10px 16px;
  border: 1px solid transparent;
  border-radius: 9px;
  font: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: background .16s ease, border-color .16s ease, transform .16s ease;
}
.btn-clock-in { color: #fff; background: var(--custom-accent); }
.btn-clock-in:hover:not(:disabled) { background: var(--custom-accent-dark); transform: translateY(-1px); }
.btn-clock-out { color: var(--custom-ink); border-color: var(--custom-line); background: #fff; }
.btn-clock-out:hover:not(:disabled) { border-color: #b9c5d4; background: #f7f9fc; }
.btn-clock-in:disabled,
.btn-clock-out:disabled,
.btn-disabled { cursor: not-allowed; opacity: .48; }

.geofencing-status {
  justify-content: flex-start;
  margin-top: 14px;
  padding: 10px 12px;
  border-radius: 9px;
  font-size: 12px;
}
.geofencing-success { color: #28704e; border-color: #d8eee3; background: #f0faf4; }
.geofencing-error { color: #a33d3d; border-color: #f3d8d8; background: #fff5f5; }
.attendance-message,
.clockout-restriction {
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: 9px;
  color: #72501e;
  background: #fff7e8;
  font-size: 12px;
}
.attendance-message.success { color: #28704e; background: #f0faf4; }
.attendance-message.error { color: #a33d3d; background: #fff5f5; }
.attendance-message.warning { color: #72501e; background: #fff7e8; }

.modules-section-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}
.modules-section-heading .dashboard-eyebrow { margin-bottom: 6px; }
.modules-section-heading h2 {
  margin: 0;
  color: var(--custom-ink);
  font-size: 20px;
  font-weight: 750;
  letter-spacing: -.025em;
}
.modules-section-heading > div > p:last-child {
  margin: 6px 0 0;
  color: var(--custom-muted);
  font-size: 13px;
}
.module-count {
  padding: 6px 10px;
  border: 1px solid var(--custom-line);
  border-radius: 999px;
  color: var(--custom-muted);
  background: #fff;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
}
.modules-grid {
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr));
  gap: 14px;
  margin: 0;
  align-items: stretch;
}
.module-tile {
  min-width: 0;
  min-height: 116px;
  align-items: flex-start;
  gap: 14px;
  padding: 18px;
  border: 1px solid var(--custom-line);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 4px 14px rgba(23, 43, 77, .035);
  transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.module-tile:hover,
.module-tile:focus {
  padding: 18px;
  transform: translateY(-2px);
  border-color: #e9bda5;
  box-shadow: 0 10px 22px rgba(23, 43, 77, .09);
}
.module-tile:focus-visible { outline: 3px solid rgba(228, 107, 46, .24); outline-offset: 2px; }
.module-tile--alert { border-color: #efc2a9; }
.module-icon {
  display: grid;
  width: 42px;
  height: 42px;
  flex: 0 0 42px;
  place-items: center;
  border: 1px solid #f3dfd3;
  border-radius: 12px;
  color: var(--custom-accent-dark);
  background: var(--custom-accent-soft);
  font-size: 15px;
  font-weight: 800;
}
.module-info { gap: 6px; padding-right: 7px; }
.module-name { color: var(--custom-ink); font-size: 14px; font-weight: 750; }
.module-desc {
  max-height: 60px;
  margin-top: 0;
  transform: none;
  color: var(--custom-muted);
  font-size: 11px;
  line-height: 1.5;
  opacity: 1;
}
.module-badge {
  top: 10px;
  right: 10px;
  min-width: 20px;
  height: 20px;
  border: 2px solid #fff;
  background: #cf4545;
  box-shadow: none;
  font-size: 10px;
}
.empty-message {
  margin: 0;
  padding: 24px;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  color: var(--custom-muted);
  background: rgba(255,255,255,.7);
  font-size: 13px;
}

.face-capture-content {
  border: 1px solid var(--custom-line);
  border-top: 4px solid var(--custom-accent);
  border-radius: 16px;
  background: #fff;
}
.face-capture-content h3 { color: var(--custom-ink); }
.face-capture-instruction { color: var(--custom-muted); }
.face-capture-content .btn-capture {
  border-color: var(--custom-accent);
  background: var(--custom-accent);
}
.face-capture-content .btn-capture:hover:not(:disabled) {
  border-color: var(--custom-accent-dark);
  background: var(--custom-accent-dark);
}
.face-capture-content .btn-capture:disabled { border-color: #9aa7b7; background: #9aa7b7; }

@media (max-width: 760px) {
  .header-inner { min-height: 68px; padding: 10px 18px; }
  .header-left { gap: 16px; }
  .profile-row { gap: 10px; }
  .profile-last-login,
  .profile-divider { display: none; }
  .panel-main { padding: 28px 18px 40px; }
  .attendance-card { padding: 18px; }
}

@media (max-width: 520px) {
  .header-inner { align-items: center; gap: 10px; }
  .panel-logo { width: 48px; }
  .header-left { gap: 12px; }
  .profile-row { gap: 8px 10px; flex-wrap: wrap; }
  .profile-identity { flex-basis: 100%; }
  .profile-status { padding: 5px 8px; font-size: 10px; }
  .nav-logout { padding: 8px 11px; font-size: 11px; }
  .dashboard-welcome { align-items: flex-start; margin-bottom: 20px; }
  .dashboard-welcome h1 { font-size: 25px; }
  .attendance-card { padding: 16px; margin-bottom: 28px; }
  .attendance-header { align-items: flex-start; }
  .attendance-status-badge { padding: 6px 8px; font-size: 10px; }
  .attendance-buttons { flex-direction: column; }
  .modules-section-heading h2 { font-size: 18px; }
  .module-count { padding: 5px 8px; font-size: 10px; }
  .modules-grid { grid-template-columns: 1fr; }
}

@media (prefers-reduced-motion: reduce) {
  .module-tile,
  .btn-clock-in,
  .btn-clock-out { transition: none; }
}

.custom-panel-container {
  --custom-ink: #26354a;
  --custom-muted: #806f62;
  --custom-line: #eadbd0;
  --custom-accent: #df6d32;
  --custom-accent-dark: #bd4c1e;
  --custom-accent-soft: #fff3e9;
  background: #efe5dc;
}

.panel-header {
  background: rgba(255, 250, 246, .96);
  border-bottom-color: rgba(111, 78, 49, .12);
  box-shadow: 0 5px 18px rgba(83, 57, 37, .07);
}

.profile-status {
  border-color: #dceadf;
  color: #31724f;
  background: #f4faf5;
}

.panel-main {
  align-items: flex-start;
  padding-top: 18px;
}

.dashboard-welcome {
  margin-bottom: 22px;
  padding: 22px 24px;
  border: 1px solid #e6d7cb;
  border-radius: 16px;
  background: #fffaf6;
  box-shadow: 0 7px 18px rgba(97, 69, 45, .07);
}
.dashboard-eyebrow { color: #a65327; }
.dashboard-welcome h1,
.modules-section-heading h2 { color: #26354a; }
.dashboard-welcome__subtitle,
.modules-section-heading > div > p:last-child { color: #806f62; }

.attendance-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: 0;
  margin-bottom: 30px;
  padding: 20px;
  border-color: #e6d7cb;
  border-radius: 14px;
  background: #fffaf6;
  box-shadow: 0 7px 18px rgba(97, 69, 45, .07);
}

.attendance-header {
  margin: 0 0 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid #efe4db;
}

.attendance-title { color: #26354a; font-size: 15px; }
.attendance-subtitle { color: #806f62; }
.attendance-body {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(215px, 260px);
  align-items: stretch;
  gap: 18px;
}

.attendance-summary { min-width: 0; }
.attendance-summary__heading,
.attendance-actions__heading { display: grid; gap: 5px; }
.attendance-summary__heading h2,
.attendance-actions__heading h2 {
  margin: 0;
  color: #422f24;
  font-size: 12px;
  font-weight: 750;
}
.attendance-summary__heading p,
.attendance-actions__heading p {
  margin: 0;
  color: #8a786b;
  font-size: 11px;
  line-height: 1.45;
}

.attendance-times {
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  margin: 12px 0 0;
}
.time-row {
  min-width: 0;
  gap: 6px;
  padding: 11px 12px;
  border-color: #eee2d8;
  border-radius: 9px;
  background: #fff;
}
.time-label { color: #927c6b; font-size: 9px; }
.time-value { color: #352d28; font-size: 14px; }

.attendance-actions {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 12px;
  padding: 14px;
  border: 1px solid #efdfd1;
  border-radius: 11px;
  background: #fff5ec;
}
.attendance-buttons { flex-direction: column; gap: 8px; }
.btn-clock-in,
.btn-clock-out {
  min-height: 38px;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 12px;
}
.btn-clock-in { background: #c85a2c; }
.btn-clock-in:hover:not(:disabled) { background: #a94825; }
.btn-clock-out { border-color: #e4d4c7; color: #4d4038; background: #fff; }
.btn-clock-out:hover:not(:disabled) { border-color: #d7bda9; background: #fffaf6; }

.attendance-status-badge { padding: 6px 10px; }
.status-on-duty { color: #28704e; background: #eaf5ed; }
.status-off-duty { color: #8a5a28; background: #fff1df; }
.geofencing-status,
.clockout-restriction,
.attendance-message { grid-column: 1 / -1; }
.geofencing-status {
  margin-top: 14px;
  padding: 9px 11px;
  border-radius: 8px;
  font-size: 11px;
}
.geofencing-success { border-color: #d8eadd; color: #28704e; background: #f2f8f2; }
.geofencing-error { border-color: #f0d8d2; color: #9b4438; background: #fff5f2; }
.clockout-restriction,
.attendance-message {
  margin-top: 10px;
  padding: 9px 11px;
  border: 1px solid #f0e0bf;
  border-radius: 8px;
  color: #72501e;
  background: #fff8ea;
  font-size: 11px;
}
.attendance-message.success { border-color: #d8eadd; color: #28704e; background: #f2f8f2; }
.attendance-message.error { border-color: #f0d8d2; color: #9b4438; background: #fff5f2; }

.modules-section-heading { margin-bottom: 14px; }
.module-count {
  border-color: #e6d7cb;
  color: #806f62;
  background: #fffaf6;
}
.modules-grid { gap: 12px; }
.module-tile {
  min-height: 0;
  padding: 16px;
  border-color: #e6d7cb;
  border-radius: 12px;
  background: #fffaf6;
  box-shadow: 0 4px 12px rgba(97, 69, 45, .045);
}
.module-tile:hover,
.module-tile:focus {
  padding: 16px;
  border-color: #dfb89f;
  box-shadow: 0 9px 20px rgba(97, 69, 45, .1);
}
.module-tile--alert { border-color: #e9b89a; }
.module-icon {
  border-color: #f0dccd;
  color: #a65327;
  background: #fff0e5;
}
.module-name { color: #352d28; }
.module-desc,
.module-tile:hover .module-desc,
.module-tile:focus .module-desc {
  display: block;
  max-height: none;
  overflow: visible;
  margin-top: 0;
  transform: none;
  color: #806f62;
  opacity: 1;
}
.module-badge { border-color: #fffaf6; }
.empty-message {
  border-color: #dfcbbb;
  color: #806f62;
  background: rgba(255, 250, 246, .82);
}

@media (max-width: 760px) {
  .attendance-body { grid-template-columns: minmax(0, 1fr); }
  .attendance-actions { padding: 13px; }
  .attendance-buttons { flex-direction: row; }
}

@media (max-width: 520px) {
  .attendance-times { grid-template-columns: minmax(0, 1fr); }
  .attendance-buttons { flex-direction: column; }
}

.panel-main-inner { max-width: 1560px; }

.custom-dashboard-grid {
  display: grid;
  grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
  align-items: start;
  gap: 24px;
}

.custom-dashboard-section { min-width: 0; }

.custom-dashboard-section--workspace {
  padding: 24px;
  border: 1px solid #e6d7cb;
  border-radius: 14px;
  background: #fffaf6;
  box-shadow: 0 7px 18px rgba(97, 69, 45, .07);
}

.custom-dashboard-section--workspace .modules-section-heading {
  align-items: flex-start;
  margin-bottom: 20px;
}

.custom-dashboard-section--workspace .modules-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.custom-dashboard-section--workspace .module-tile {
  min-height: 142px;
  gap: 12px;
  padding: 17px;
}

.custom-dashboard-section--workspace .module-tile:hover,
.custom-dashboard-section--workspace .module-tile:focus {
  padding: 17px;
}

.custom-dashboard-section--workspace .module-icon {
  width: 42px;
  height: 42px;
  flex-basis: 42px;
  border-radius: 11px;
  font-size: 15px;
}

.custom-dashboard-section--workspace .module-info { gap: 7px; }
.custom-dashboard-section--workspace .module-name { font-size: 14px; }
.custom-dashboard-section--workspace .module-desc { font-size: 12px; line-height: 1.5; }

.custom-dashboard-section--attendance .attendance-card {
  min-height: 0;
  margin: 0;
  padding: 24px;
}

.custom-dashboard-section--attendance .attendance-header {
  margin-bottom: 20px;
  padding-bottom: 17px;
}

.custom-dashboard-section--attendance .attendance-title { font-size: 17px; }
.custom-dashboard-section--attendance .attendance-subtitle { font-size: 13px; }
.custom-dashboard-section--attendance .attendance-body {
  grid-template-columns: minmax(0, 1fr) minmax(220px, 260px);
  gap: 18px;
}

.custom-dashboard-section--attendance .attendance-summary__heading h2,
.custom-dashboard-section--attendance .attendance-actions__heading h2 { font-size: 14px; }

.custom-dashboard-section--attendance .attendance-summary__heading p,
.custom-dashboard-section--attendance .attendance-actions__heading p { font-size: 12px; }

.custom-dashboard-section--attendance .attendance-times {
  grid-template-columns: minmax(0, 1fr);
  gap: 10px;
  margin-top: 16px;
}

.custom-dashboard-section--attendance .time-row {
  min-height: 66px;
  align-content: center;
  padding: 13px 15px;
}

.custom-dashboard-section--attendance .time-label { font-size: 10px; }
.custom-dashboard-section--attendance .time-value { font-size: 17px; }
.custom-dashboard-section--attendance .attendance-actions { padding: 17px; }
.custom-dashboard-section--attendance .attendance-buttons { gap: 10px; }
.custom-dashboard-section--attendance .btn-clock-in,
.custom-dashboard-section--attendance .btn-clock-out { min-height: 44px; font-size: 13px; }
.custom-dashboard-section--attendance .geofencing-status { font-size: 12px; }
.custom-dashboard-section--attendance .clockout-restriction,
.custom-dashboard-section--attendance .attendance-message { font-size: 12px; }

.custom-dashboard-section--workspace .modules-section-heading h2 { font-size: 23px; }
.custom-dashboard-section--workspace .modules-section-heading > div > p:last-child { font-size: 14px; }
.custom-dashboard-section--workspace .module-count { padding: 7px 12px; font-size: 12px; }

@media (min-width: 1500px) {
  .custom-dashboard-grid { grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); }
  .custom-dashboard-section--workspace { padding: 28px; }
  .custom-dashboard-section--workspace .modules-grid { gap: 16px; }
  .custom-dashboard-section--workspace .module-tile { min-height: 150px; padding: 18px; }
  .custom-dashboard-section--workspace .module-tile:hover,
  .custom-dashboard-section--workspace .module-tile:focus { padding: 18px; }
  .custom-dashboard-section--workspace .module-desc { font-size: 13px; }
}

@media (max-width: 1050px) {
  .custom-dashboard-grid { gap: 14px; }
  .custom-dashboard-section--workspace { padding: 16px; }
  .custom-dashboard-section--attendance .attendance-body { grid-template-columns: minmax(0, 1fr); }
  .custom-dashboard-section--attendance .attendance-actions { padding: 12px; }
  .custom-dashboard-section--attendance .attendance-buttons { flex-direction: row; }
}

@media (max-width: 820px) {
  .custom-dashboard-grid { grid-template-columns: minmax(0, 1fr); }
  .custom-dashboard-section--attendance .attendance-body {
    grid-template-columns: minmax(0, 1fr) minmax(215px, 260px);
  }
  .custom-dashboard-section--attendance .attendance-actions { padding: 14px; }
  .custom-dashboard-section--attendance .attendance-times { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 560px) {
  .dashboard-welcome { padding: 17px; }
  .custom-dashboard-section--workspace { padding: 15px; }
  .custom-dashboard-section--workspace .modules-grid { grid-template-columns: minmax(0, 1fr); }
  .custom-dashboard-section--workspace .module-tile { min-height: 112px; padding: 15px; }
  .custom-dashboard-section--workspace .module-tile:hover,
  .custom-dashboard-section--workspace .module-tile:focus { padding: 15px; }
  .custom-dashboard-section--attendance .attendance-body { grid-template-columns: minmax(0, 1fr); }
  .custom-dashboard-section--attendance .attendance-times { grid-template-columns: minmax(0, 1fr); }
  .custom-dashboard-section--attendance .attendance-buttons { flex-direction: column; }
}

.custom-panel-container {
  --custom-sidebar-bg: #f4e9df;
  --custom-sidebar-line: #e7d7c9;
  display: grid;
  grid-template-columns: 252px minmax(0, 1fr);
  min-height: 100vh;
  background: #efe5dc;
  transition: grid-template-columns .24s ease;
}

.custom-sidebar {
  position: sticky;
  top: 0;
  z-index: 20;
  display: flex;
  width: 252px;
  height: 100vh;
  min-height: 540px;
  flex-direction: column;
  gap: 20px;
  overflow: hidden;
  padding: 22px 14px 16px;
  border-right: 1px solid var(--custom-sidebar-line);
  background: var(--custom-sidebar-bg);
  transition: width .24s ease, transform .24s ease;
}

.custom-sidebar__brand {
  display: flex;
  min-height: 46px;
  align-items: center;
  gap: 11px;
  padding: 0 8px;
}
.custom-sidebar__brand .panel-logo { width: 43px; }
.custom-sidebar__brand-name {
  color: #6d432b;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: -.02em;
  white-space: nowrap;
}

.topbar-user-pill__avatar {
  display: grid;
  flex: 0 0 auto;
  place-items: center;
  border-radius: 10px;
  color: #fff;
  background: linear-gradient(145deg, #e98a4e, #c85a2c);
  font-weight: 800;
}

.custom-sidebar__nav {
  display: grid;
  align-content: start;
  gap: 5px;
  min-height: 0;
  flex: 1;
  overflow-y: auto;
}
.custom-sidebar__label {
  margin: 3px 9px 6px;
  color: #9a7964;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: .12em;
}
.custom-sidebar__link {
  display: flex;
  width: 100%;
  min-height: 42px;
  align-items: center;
  gap: 10px;
  padding: 7px 9px;
  border: 1px solid transparent;
  border-radius: 9px;
  color: #49372c;
  background: transparent;
  font: inherit;
  font-size: 12px;
  font-weight: 650;
  text-align: left;
  cursor: pointer;
  transition: background .16s ease, border-color .16s ease, color .16s ease;
}
.custom-sidebar__link:hover,
.custom-sidebar__link:focus-visible {
  border-color: #edd5c4;
  outline: none;
  color: #a65327;
  background: #fffaf6;
}
.custom-sidebar__link-icon {
  display: grid;
  width: 27px;
  height: 27px;
  flex: 0 0 27px;
  place-items: center;
  border: 1px solid #efdfd2;
  border-radius: 8px;
  color: #a65327;
  background: #fff8f2;
  font-size: 11px;
  font-weight: 800;
}
.custom-sidebar__link-name { min-width: 0; flex: 1; }
.custom-sidebar__badge {
  display: grid;
  min-width: 20px;
  height: 20px;
  place-items: center;
  padding: 0 5px;
  border-radius: 999px;
  color: #fff;
  background: #c64c43;
  font-size: 10px;
  font-weight: 800;
}
.custom-sidebar__link--alert .custom-sidebar__link-icon {
  border-color: #edc6ad;
  background: #fff0e5;
}
.custom-sidebar__empty { margin: 5px 9px; color: #806f62; font-size: 11px; }

.custom-sidebar__footer {
  display: grid;
  gap: 12px;
  padding-top: 14px;
  border-top: 1px solid var(--custom-sidebar-line);
}
.custom-sidebar__last-login { display: grid; gap: 4px; padding: 0 8px; }
.custom-sidebar__last-login span {
  color: #9a7964;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: .1em;
  text-transform: uppercase;
}
.custom-sidebar__last-login strong {
  color: #6f5a4b;
  font-size: 10px;
  font-weight: 600;
  line-height: 1.45;
}
.custom-sidebar__logout {
  display: flex;
  min-height: 40px;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 1px solid #edc8bd;
  border-radius: 9px;
  color: #a33d32;
  background: #fff7f4;
  font: inherit;
  font-size: 12px;
  font-weight: 750;
  cursor: pointer;
}
.custom-sidebar__logout:hover:not(:disabled) { border-color: #d98b78; background: #fff0eb; }
.custom-sidebar__logout:disabled { cursor: wait; opacity: .65; }

.custom-panel-main { min-width: 0; }
.custom-panel-main .panel-header {
  position: sticky;
  top: 0;
  z-index: 15;
  border-bottom-color: rgba(111, 78, 49, .12);
  background: rgba(255, 250, 246, .96);
  -webkit-backdrop-filter: blur(14px);
  backdrop-filter: blur(14px);
}
.custom-panel-main .header-inner {
  max-width: none;
  min-height: 72px;
  margin: 0;
  gap: 10px;
  padding: 10px 20px 10px 10px;
}
.custom-sidebar-toggle {
  display: grid;
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  place-items: center;
  border: 1px solid #e7d7c9;
  border-radius: 10px;
  color: #554337;
  background: #fffaf6;
  font-size: 17px;
  cursor: pointer;
  transition: background .16s ease, border-color .16s ease;
}
.custom-sidebar-toggle:hover,
.custom-sidebar-toggle:focus-visible {
  border-color: #dfb89f;
  outline: none;
  background: #fff2e9;
}
.topbar-context { display: grid; gap: 3px; }
.topbar-context__eyebrow {
  color: #a65327;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: .12em;
}
.topbar-context strong { color: #352d28; font-size: 14px; }
.topbar-actions { display: flex; align-items: center; gap: 12px; margin-left: auto; }
.topbar-user-pill {
  display: flex;
  max-width: 220px;
  min-height: 40px;
  align-items: center;
  gap: 9px;
  padding: 4px 12px 4px 5px;
  border: 1px solid #eadbd0;
  border-radius: 999px;
  color: #49372c;
  background: #fffaf6;
}
.topbar-user-pill__avatar { width: 30px; height: 30px; border-radius: 50%; font-size: 10px; }
.topbar-user-pill__name {
  overflow: hidden;
  font-size: 11px;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.custom-sidebar-overlay {
  position: fixed;
  inset: 0;
  z-index: 18;
  border: 0;
  background: rgba(24, 32, 43, .42);
  -webkit-backdrop-filter: blur(2px);
  backdrop-filter: blur(2px);
}

.custom-panel-container.sidebar-collapsed { grid-template-columns: 78px minmax(0, 1fr); }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar { width: 78px; padding-right: 10px; padding-left: 10px; }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__brand { justify-content: center; padding: 0; }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__brand .panel-logo { width: 42px; }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__brand-name,
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__label,
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__link-name,
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__badge,
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__last-login,
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__logout > span:last-child { display: none; }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__link { justify-content: center; padding: 6px; }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__footer { justify-items: center; }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__logout { width: 40px; height: 40px; }
.sidebar-collapsed:not(.sidebar-mobile) .custom-sidebar__logout > span:first-child { font-size: 18px; }
.sidebar-mobile.custom-panel-container { display: block; }
.sidebar-mobile .custom-sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 21;
  width: min(290px, 84vw);
  height: 100dvh;
  min-height: 0;
  box-shadow: 14px 0 36px rgba(34, 41, 52, .16);
}
.sidebar-mobile.sidebar-collapsed .custom-sidebar { transform: translateX(-105%); }
.sidebar-mobile .custom-panel-main { min-height: 100vh; }

@media (max-width: 820px) {
  .custom-panel-main .header-inner { min-height: 66px; }
  .topbar-user-pill { max-width: 160px; }
}

@media (max-width: 520px) {
  .custom-panel-main .header-inner { gap: 9px; padding: 9px 12px 9px 8px; }
  .custom-sidebar-toggle { width: 36px; height: 36px; flex-basis: 36px; }
  .topbar-context__eyebrow { font-size: 8px; }
  .topbar-context strong { font-size: 12px; }
  .topbar-actions { gap: 7px; }
  .topbar-user-pill { width: 36px; min-height: 36px; justify-content: center; padding: 2px; }
  .topbar-user-pill__avatar { width: 29px; height: 29px; }
  .topbar-user-pill__name { display: none; }
}

@media (prefers-reduced-motion: reduce) {
  .custom-panel-container,
  .custom-sidebar,
  .custom-sidebar-toggle { transition: none; }
}
</style>
