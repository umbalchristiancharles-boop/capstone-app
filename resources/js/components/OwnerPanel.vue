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
      <section class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6 border border-slate-200 dark:border-slate-700">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Welcome, {{ userProfile.full_name || userProfile.fullName || userProfile.username }}</h2>
          <p class="text-slate-600 dark:text-slate-400">This is the owner dashboard. Add owner-specific widgets here.</p>
        </div>
        <!-- Placeholder: owner widgets can be added here -->
      </section>
    </template>

    <template #sideTop>
      <div class="space-y-4">
        <!-- Quick Links Card -->
        <div class="tw-panel-block">
          <div class="tw-panel-header">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Quick Links</h3>
          </div>
          <div class="tw-panel-body">
            <ul class="space-y-2">
              <li class="flex items-center justify-between">
                <router-link to="/owner/dish-approval" class="text-orange-600 dark:text-orange-400 hover:underline font-medium">
                  Dish Approval
                </router-link>
                <span v-if="pendingCounts.kitchen > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                  {{ pendingCounts.kitchen }}
                </span>
              </li>
              <li>
                <router-link to="/owner/staff-management" class="text-orange-600 dark:text-orange-400 hover:underline font-medium">
                  Staff Management
                </router-link>
              </li>
              <li class="flex items-center justify-between">
                <router-link to="/owner/branch-confirmations" class="text-orange-600 dark:text-orange-400 hover:underline font-medium">
                  Branch Confirmations
                </router-link>
                <span v-if="pendingCounts.branchOwner > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                  {{ pendingCounts.branchOwner }}
                </span>
              </li>
              <li class="flex items-center justify-between">
                <router-link to="/owner/price-markup-approvals" class="text-orange-600 dark:text-orange-400 hover:underline font-medium">
                  Price Markup Approvals
                </router-link>
                <span v-if="pendingCounts.priceMarkup > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                  {{ pendingCounts.priceMarkup }}
                </span>
              </li>
            </ul>
          </div>
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

