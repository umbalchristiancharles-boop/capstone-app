<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page" :class="{ 'admin-page--wider': fullWidth }">
      <section class="admin-layout" :class="{ 'admin-layout--wider': fullWidth }">
        <!-- LEFT: PROFILE COLUMN -->
        <aside class="admin-profile-column">
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
                <span class="admin-id-value">&nbsp;{{ userProfile.accountId || userProfile.account_id || 'id0001' }}</span>
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
              <div>
                <h1>{{ panelTitle }}</h1>
                <p>{{ panelDescription }}</p>
              </div>
              <slot name="headerActions"></slot>
            </div>
          </header>
          <slot name="main"></slot>
        </main>
        <!-- RIGHT: SIDE PANELS -->
        <aside class="admin-side">
          <slot name="side"></slot>
        </aside>
      </section>
    </div>

    <!-- PROFILE INFO MODAL -->
    <transition name="fade">
      <div v-if="showInfoModal" class="info-backdrop">
        <div class="info-modal">
          <h3>Info</h3>
          <p class="info-sub">Your account information.</p>

          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">Full name</span>
              <span class="info-value">{{ localProfile.fullName || localProfile.full_name || '-' }}</span>
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
import { ref, watch, computed } from 'vue'
import axios from 'axios'

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
})

const emit = defineEmits(['logout', 'profile-updated'])

const localProfile = ref({})
const showInfoModal = ref(false)
const isEditingInfo = ref(false)
const isSavingProfile = ref(false)
const profileError = ref('')
const profileSuccess = ref('')

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
    localProfile.value = { ...newVal }
  }
}, { immediate: true })

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

  localProfile.value = profile
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

async function onAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (!window.confirm('Are you sure you want to change your profile picture?')) return

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
</style>
