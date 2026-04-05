<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    panelTitle="Owner Panel"
    panelDescription="Overview and controls for store owners"
    :enableProfileUpdate="true"
    :canEditProfile="true"
    :canChangePassword="true"
    profileEndpoint="/api/profile"
    updateEndpoint="/api/profile/update"
    avatarEndpoint="/api/profile/avatar"
    @logout="handleLogout"
  >
    <template #main>
      <section class="owner-dashboard">
        <div class="owner-welcome">
          <h2>Welcome, {{ userProfile.full_name || userProfile.fullName || userProfile.username }}</h2>
          <p>This is the owner dashboard. Add owner-specific widgets here.</p>
        </div>
        <!-- Placeholder: owner widgets can be added here -->
      </section>
    </template>

    <template #sideTop>
      <div class="owner-side-widgets">
        <!-- Simple owner widgets placeholder -->
        <div class="panel-block">
          <h3>Quick Links</h3>
          <ul>
            <li>
              <router-link to="/owner/dish-approval">Dish Approval</router-link>
              <span v-if="pendingCounts.kitchen > 0" class="panel-badge">{{ pendingCounts.kitchen }}</span>
            </li>
            <li><router-link to="/owner/staff-management">Staff Management</router-link></li>
            <li>
              <router-link to="/owner/branch-confirmations">Branch Confirmations</router-link>
              <span v-if="pendingCounts.branchOwner > 0" class="panel-badge">{{ pendingCounts.branchOwner }}</span>
            </li>
            <li>
              <router-link to="/owner/price-markup-approvals">Price Markup Approvals</router-link>
              <span v-if="pendingCounts.priceMarkup > 0" class="panel-badge">{{ pendingCounts.priceMarkup }}</span>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'
import Swal from 'sweetalert2'

const userProfile = ref({})
const pendingCounts = ref({
  kitchen: 0,
  branchOwner: 0,
  priceMarkup: 0,
})
const hasNotified = ref(false)

onMounted(async () => {
  try {
    const local = JSON.parse(localStorage.getItem('user') || 'null')
    if (local) {
      userProfile.value = {
        full_name: local.full_name || local.fullName,
        username: local.username,
        role: local.role,
        department: local.department,
        account_id: local.id,
        avatarUrl: local.avatar_url || null,
      }
    }

    const res = await axios.get('/api/me', { withCredentials: true })
    if (res && res.data && res.data.user) {
      userProfile.value = Object.assign({}, userProfile.value, res.data.user)
    }
  } catch (e) {
    console.warn('OwnerPanel: failed to load profile', e)
  }

  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      pendingCounts.value = {
        kitchen: Number(res.data.counts?.kitchen || 0),
        branchOwner: Number(res.data.extras?.branchPendingOwner || 0),
        priceMarkup: Number(res.data.extras?.priceMarkupPending || 0),
      }
      const total = pendingCounts.value.kitchen + pendingCounts.value.branchOwner + pendingCounts.value.priceMarkup
      if (!hasNotified.value && total > 0) {
        showToast('You have pending approvals to review.', 'info')
        hasNotified.value = true
      }
    }
  } catch (e) {
    pendingCounts.value = { kitchen: 0, branchOwner: 0, priceMarkup: 0 }
  }
})

const handleLogout = async () => {
  const result = await Swal.fire({
    title: 'Confirm logout',
    text: 'This will end your current session for Chikin Tayo.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#FF6A3D',
    cancelButtonColor: '#636B7B',
  })

  if (result.isConfirmed) {
    try {
      await axios.post('/logout', {}, { withCredentials: true })
    } catch (e) {
      console.warn('Logout request failed:', e)
    }
    // Clear local storage
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    // Redirect to login
    window.location.href = '/login'
  }
}
</script>

<style scoped>
.owner-dashboard { padding: 18px; }
.owner-side-widgets ul { list-style: none; padding: 0; margin: 0; display: grid; gap: 8px; }
.owner-side-widgets li { position: relative; padding-right: 28px; }
.panel-badge { position: absolute; top: -2px; right: 0; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #ef4444; color: #ffffff; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35); }
.owner-welcome h2 { margin: 0 0 8px; }
</style>

