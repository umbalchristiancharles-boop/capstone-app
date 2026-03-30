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
        <MainBranchCRMPanel />
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
import { ref } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import MainBranchCRMPanel from './MainBranchCRMPanel.vue'
import axios from 'axios'

const userProfile = ref({})
const profileDropdownVisible = ref(false)

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
    try { window.location.replace('/') } catch (e) {}
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

// Close dropdown when clicking outside
window.addEventListener('click', () => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})
</script>

<style scoped>
.main-branch-admin-panel {
  width: 100%;
}
</style>
