<template>
  <div class="custom-panel-container">
    <header class="panel-header">
      <div class="header-inner">
        <div class="header-left">
          <img :src="logoImg" alt="Chikin Tayo" class="panel-logo" />
          <div class="profile-row">
            <div class="profile-name">{{ userFullName }}</div>
            <div class="profile-role">{{ userRoleLabel }}</div>
            <div class="profile-status">
              <span class="status-dot" aria-hidden="true"></span>
              <span>Online</span>
            </div>
            <div class="profile-last-login">Last login: {{ userLastLoginLabel }}</div>
          </div>
        </div>
        <button @click="logout" class="btn btn-ghost nav-logout" :disabled="isLoggingOut">{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</button>
      </div>
    </header>

    <section class="panel-main">
      <div class="panel-main-inner">
        <div class="cards-greeting-row">
          <div class="profile-avatar cards-avatar">{{ userInitials }}</div>
          <div class="cards-greeting-text">{{ greetingLabel }}</div>
        </div>

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
            <button @click="performClockIn" :disabled="attendanceStatus.is_clocked_in || isAttendanceProcessing" class="btn-clock-in">{{ isAttendanceProcessing ? '...' : 'Clock In' }}</button>
            <button @click="performClockOut" :disabled="!attendanceStatus.is_clocked_in || isAttendanceProcessing || !canClockOut" class="btn-clock-out" :class="{ 'btn-disabled': !canClockOut && attendanceStatus.is_clocked_in }">{{ isAttendanceProcessing ? '...' : 'Clock Out' }}</button>
          </div>
          <div v-if="!canClockOut && attendanceStatus.is_clocked_in" class="clockout-restriction"><span class="restriction-icon">LOCK</span><span>Cannot clock out before {{ scheduledTimeOut }}</span></div>
          <div v-if="attendanceMessage" :class="['attendance-message', attendanceMessageType]">{{ attendanceMessage }}</div>
        </div>

        <p v-if="!modules || modules.length === 0" class="empty-message">No modules assigned. Contact admin to enable modules.</p>

        <div v-else class="modules-grid">
          <div
            v-for="m in modules"
            :key="m"
            class="module-tile"
            :class="{ 'module-tile--alert': pendingCount(m) > 0 }"
            @click="goToModule(m)"
            role="button"
            tabindex="0"
            @keydown.enter="goToModule(m)"
          >
            <div class="module-icon">{{ (prettyName(m) || '').charAt(0) }}</div>
            <span v-if="pendingCount(m) > 0" class="module-badge">{{ pendingCount(m) }}</span>
            <div class="module-info">
              <div class="module-name">{{ prettyName(m) }}</div>
              <div class="module-desc">{{ panelDescription(m) }}</div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { showToast } from './toastStore';

const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href;
const router = useRouter();
const modules = ref([]);
const isLoggingOut = ref(false);
const userFullName = ref('User');
const userRole = ref('');
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
const notificationCounts = ref({});
const isNotificationsLoading = ref(false);
const hasNotified = ref(false);

const toTitleCase = (value) => {
  return String(value || '')
    .split(/\s+/)
    .filter(Boolean)
    .map(part => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase())
    .join(' ');
};

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
  userRole.value = String(user.role || '').toLowerCase();
  userInitials.value = buildInitials(fullName || user.username || 'User');
  userLastLoginRaw.value = String(user.last_login_at || user.last_activity_at || user.updated_at || '');
};

const userRoleLabel = computed(() => userRole.value ? toTitleCase(userRole.value) : 'User');

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
  isAttendanceProcessing.value = true;
  attendanceMessage.value = '';

  try {
    const res = await axios.post('/api/staff/clock-in', {}, { withCredentials: true });
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked in successfully!';
      attendanceMessageType.value = 'success';
      await loadAttendanceStatus();
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock in';
      attendanceMessageType.value = 'error';
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking in';
    attendanceMessageType.value = 'error';
  } finally {
    isAttendanceProcessing.value = false;
    setTimeout(() => { attendanceMessage.value = ''; }, 3000);
  }
};

const performClockOut = async () => {
  if (isAttendanceProcessing.value) return;
  isAttendanceProcessing.value = true;
  attendanceMessage.value = '';

  try {
    const res = await axios.post('/api/staff/clock-out', {}, { withCredentials: true });
    if (res.data && (res.data.success || res.data.ok)) {
      attendanceMessage.value = 'Clocked out successfully!';
      attendanceMessageType.value = 'success';
      await loadAttendanceStatus();
    } else {
      attendanceMessage.value = res.data.message || 'Failed to clock out';
      attendanceMessageType.value = 'error';
    }
  } catch (e) {
    attendanceMessage.value = e.response?.data?.message || 'Error clocking out';
    attendanceMessageType.value = 'error';
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
        last_login_at: rawUser.last_login_at || rawUser.last_activity_at || rawUser.updated_at || '',
        branch_id: rawUser.branch_id || null,
        permissions: rawUser.permissions || {},
      };

      applyUserProfile(normalized);

      try {
        const cached = JSON.parse(localStorage.getItem('user') || '{}') || {};
        localStorage.setItem('user', JSON.stringify({ ...cached, ...normalized }));
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
});

const loadPanelNotifications = async () => {
  isNotificationsLoading.value = true;
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true });
    if (res.data && res.data.ok) {
      notificationCounts.value = res.data.counts || {};
      const total = Object.values(notificationCounts.value || {}).reduce((sum, v) => sum + Number(v || 0), 0);
      if (!hasNotified.value && total > 0) {
        showToast('You have pending items in your modules.', 'info');
        hasNotified.value = true;
      }
    }
  } catch (e) {
    notificationCounts.value = {};
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

const prettyName = (m) => {
  if (!m) return 'Unknown';
  return m.charAt(0).toUpperCase() + m.slice(1);
}

const panelDescription = (m) => {
  const fallbackDescriptions = {
    admin: 'View operational KPIs, monitor orders and sales, manage staff access, and handle approvals like dishes, announcements, and price markups.',
    finance: 'Track branch financial KPIs, review transactions and reports, approve budget requests, confirm supplier receipts, and manage branch budgets.',
    inventory: 'Manage product inventory with stock monitoring, low-stock tracking, stock confirmations, procurement history, and inventory reporting tools.',
    hr: 'Manage staff records and status, monitor HR metrics, open staff management tools, and control attendance settings and HR reports.',
    logistics: 'Monitor inventory health, create procurement and product requests, track request statuses, and coordinate branch logistics workflows.',
    procurement: 'Handle supplier operations, review branch procurement history, process logistics requests, and manage budget and order progression.',
    kitchen: 'Create and manage dishes with ingredient mappings, monitor kitchen order queues, mark orders done, and flag low-stock ingredients.',
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
  try {
    await router.replace('/staff-landing');
  } catch (e) {
    window.location.replace('/staff-landing');
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
.panel-logo { display:block; width:58px; height:auto; flex-shrink: 0 }
.cards-greeting-row { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; margin: 0 0 52px 0 }
.cards-avatar { width: 124px; height: 124px; font-size: 46px }
.cards-greeting-text { font-size: 18px; font-weight: 700; color: #1F2937 }
.profile-row { display:flex; align-items:center; gap:14px; flex-wrap: nowrap; white-space: nowrap; min-width: 0; overflow: hidden }
.profile-avatar { width:44px; height:44px; border-radius:50%; background:#F97316; color:#FFFFFF; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; letter-spacing:.5px; box-shadow: 0 0 0 3px rgba(249,115,22,0.12) }
.profile-name { font-size:13px; font-weight:700; color:#1F2937; line-height:1.2 }
.profile-role { font-size:12px; color:#6B7280 }
.profile-status { display:flex; align-items:center; gap:6px; font-size:12px; color:#4B5563 }
.status-dot { width:8px; height:8px; border-radius:50%; background:#22C55E; box-shadow:0 0 0 3px rgba(34,197,94,0.14) }
.profile-last-login { font-size:12px; color:#6B7280 }
.nav-logout { flex-shrink: 0 }
.empty-message { color:#6B7280; text-align:center; margin:12px 0 }

.modules-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:22px; margin:36px auto 18px auto; width:100%; align-items:start }
.module-tile { position: relative; display:flex; gap:14px; align-items:center; align-self:start; padding:18px; background:#fff; border-radius:14px; border:1px solid #E5E7EB; cursor:pointer; box-shadow:0 8px 18px rgba(0,0,0,0.08); transition:transform .24s ease, box-shadow .24s ease, padding .28s ease, border-color .24s ease }
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
</style>
