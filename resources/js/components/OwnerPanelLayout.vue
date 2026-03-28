<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page" :class="{ 'admin-page--wider': fullWidth }">
      <section class="admin-layout" :class="{ 'admin-layout--wider': fullWidth, 'no-profile-column': !showProfileColumn }">
        <!-- LEFT: PROFILE COLUMN (can be hidden via prop) -->
        <aside v-if="showProfileColumn" class="admin-profile-column">
          <div v-if="userProfile" class="admin-card admin-card--stacked">
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
              <div class="admin-actions-row">
                <button class="logout-btn logout-btn--center" @click="$emit('logout')">Logout</button>
              </div>
            </div>
          </div>
        </aside>
        <!-- MIDDLE: MAIN DASHBOARD -->
        <main class="admin-main">
          <header class="admin-main-header">
            <div class="admin-main-header-top">
              <div class="header-left-slot">
                  <button v-if="showDefaultBack" class="back-to-dashboard-btn" @click="handleBack">← Back</button>
                  <slot name="headerLeft"></slot>
              </div>
              <div>
                <h1>{{ panelTitle }}</h1>
                <p>{{ panelDescription }}</p>
              </div>
              <div class="header-actions-top">
                <slot name="headerActions"></slot>
              </div>
            </div>
          </header>
          <slot name="main"></slot>
        </main>
        <!-- RIGHT: SIDE PANELS -->
        <aside class="admin-side">
          <section v-if="showAnnouncements" class="panel-block announcements-panel">
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

          <slot name="side"></slot>
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
      <div v-if="showInfoModal" class="info-backdrop">
        <div class="info-modal">
          <h3>Info</h3>
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
import { ref, watch, computed, onMounted, onUnmounted, useSlots } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Toast from './Toast.vue'

const props = defineProps({
  userProfile: { type: Object, default: () => ({}) },
  panelTitle: { type: String, required: true },
  panelDescription: { type: String, required: true },
  fullWidth: { type: Boolean, default: false },
  enableProfileUpdate: { type: Boolean, default: false },
  canEditProfile: { type: Boolean, default: false },
  canChangePassword: { type: Boolean, default: false },
  profileEndpoint: { type: String, default: '' },
  updateEndpoint: { type: String, default: '' },
  avatarEndpoint: { type: String, default: '' }
  ,
  showProfileColumn: { type: Boolean, default: true }
  ,
  showBackButton: { type: Boolean, default: false }
  ,
  showAnnouncements: { type: Boolean, default: true }
})

const emit = defineEmits(['logout', 'profile-updated', 'back'])
const route = useRoute()
const router = useRouter()

const isCustomAccount = computed(() => {
  try {
    const raw = localStorage.getItem('user') || 'null'
    const u = JSON.parse(raw)
    return (u?.role || '').toLowerCase() === 'custom'
  } catch (e) {
    return false
  }
})

// Show a back button when parent explicitly requests it via prop, when the
// current route contains `?from=custom-panel`, or when the logged-in account is
// of type custom (so modules always have a way back).
const slots = useSlots()

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
  try {
    ;(async () => {
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
      } catch (e) {
        // non-fatal; continue to load announcements and profile
      }
    })()

    Promise.resolve(loadAnnouncements()).catch(() => {})
  } catch (e) {}
  // If parent did not provide a populated `userProfile` prop, try fetching the
  // authoritative current user so the profile column is not empty after SPA
  // navigations (e.g. coming from the custom panel).
  (async () => {
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

.admin-page--wider {
  max-width: 100%;
  padding: 0;
}

.admin-main-header-top-inner h1 { margin: 0 0 0.25rem 0 }
.admin-main-header-top-inner p { margin: 0; color: #475569 }

/* When a headerLeft slot is used, make it span full width so
  the title sits below the left content (e.g., back button). */
.header-left-slot { flex-basis: 100%; display: block; margin-bottom: 0.5rem; }

.admin-layout--wider {
  max-width: 64rem;
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
:deep(.admin-layout.no-profile-column) .header-actions-top { position: absolute; right: 24px; top: 12px; z-index: 120 }
:deep(.header-actions-top .header-profile-btn) { display: inline-flex; align-items: center }

/* Keep grid columns stable when profile column is hidden so main content
   doesn't jump width when toggling the left column. Reserve a right-side
   column for side panels (announcements) so they remain on the right. */
:deep(.admin-layout.no-profile-column) {
  grid-template-columns: 1fr minmax(260px, 360px);
  gap: 1rem;
}
:deep(.admin-layout.no-profile-column) .admin-main {
  width: 100%;
}
:deep(.admin-layout.no-profile-column) .admin-side {
  width: 360px;
}

/* Small avatar-only button inside announcements (proxies to header slot) */
/* announcements avatar styles removed */

@media (min-width: 640px) {
  .admin-layout--wider {
    padding: 1.5rem 1.5rem;
  }
}

@media (min-width: 1024px) {
  .admin-layout--wider {
    padding: 1.5rem 2.5rem;
  }
}

@media (min-width: 1000px) {
  /* Make side column sticky so announcements never overlay main content
     while allowing the main panel to scroll independently. */
  :deep(.admin-side) {
    position: sticky;
    top: 96px;
    align-self: start;
    max-height: calc(100vh - 120px);
    overflow: auto;
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
</style>
