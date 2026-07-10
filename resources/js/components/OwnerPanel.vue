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
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-white to-orange-50/80 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)] ring-1 ring-white/70 dark:border-slate-700 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900 dark:ring-slate-700/60 sm:p-7">
          <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-orange-600"></div>
          <div class="flex flex-col gap-2">
            <span class="inline-flex w-fit items-center rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-700 dark:bg-orange-500/15 dark:text-orange-300">
              Owner dashboard
            </span>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Welcome, {{ userProfile.full_name || userProfile.fullName || userProfile.username }}</h2>
            <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">This is the owner dashboard. Add owner-specific widgets here.</p>
          </div>
        </div>
        <!-- Placeholder: owner widgets can be added here -->
      </section>
    </template>

    <template #sideTop>
      <div class="space-y-4">
        <!-- Quick Links Card -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.08)] dark:border-slate-700 dark:bg-slate-800">
          <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-gradient-to-r from-orange-50 to-amber-50 px-5 py-4 dark:border-slate-700 dark:from-slate-800 dark:to-slate-700/60">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-600 dark:text-orange-300">Quick Links</p>
              <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">Owner actions</h3>
            </div>
          </div>
          <div class="p-5">
            <ul class="space-y-3">
              <li class="flex items-center justify-between gap-3 rounded-xl border border-transparent px-3 py-2 transition-all duration-200 hover:-translate-y-0.5 hover:border-orange-100 hover:bg-orange-50/80 hover:shadow-sm dark:hover:border-slate-600 dark:hover:bg-slate-700/60">
                <router-link to="/owner/dish-approval" class="font-medium text-slate-700 transition-colors duration-200 hover:text-orange-600 dark:text-slate-200 dark:hover:text-orange-300">
                  Dish Approval
                </router-link>
                <span v-if="pendingCounts.kitchen > 0" class="owner-panel-badge">
                  {{ pendingCounts.kitchen }}
                </span>
              </li>
              <li class="rounded-xl border border-transparent px-3 py-2 transition-all duration-200 hover:-translate-y-0.5 hover:border-orange-100 hover:bg-orange-50/80 hover:shadow-sm dark:hover:border-slate-600 dark:hover:bg-slate-700/60">
                <router-link to="/owner/staff-management" class="font-medium text-slate-700 transition-colors duration-200 hover:text-orange-600 dark:text-slate-200 dark:hover:text-orange-300">
                  Staff Management
                </router-link>
              </li>
              <li class="flex items-center justify-between gap-3 rounded-xl border border-transparent px-3 py-2 transition-all duration-200 hover:-translate-y-0.5 hover:border-orange-100 hover:bg-orange-50/80 hover:shadow-sm dark:hover:border-slate-600 dark:hover:bg-slate-700/60">
                <router-link to="/owner/branch-confirmations" class="font-medium text-slate-700 transition-colors duration-200 hover:text-orange-600 dark:text-slate-200 dark:hover:text-orange-300">
                  Branch Confirmations
                </router-link>
                <span v-if="pendingCounts.branchOwner > 0" class="owner-panel-badge">
                  {{ pendingCounts.branchOwner }}
                </span>
              </li>
              <li class="flex items-center justify-between gap-3 rounded-xl border border-transparent px-3 py-2 transition-all duration-200 hover:-translate-y-0.5 hover:border-orange-100 hover:bg-orange-50/80 hover:shadow-sm dark:hover:border-slate-600 dark:hover:bg-slate-700/60">
                <router-link to="/owner/price-markup-approvals" class="font-medium text-slate-700 transition-colors duration-200 hover:text-orange-600 dark:text-slate-200 dark:hover:text-orange-300">
                  Price Markup Approvals
                </router-link>
                <span v-if="pendingCounts.priceMarkup > 0" class="owner-panel-badge">
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
.owner-panel-badge {
  display: inline-flex;
  min-width: 1.5rem;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  background: rgb(254 226 226);
  padding: 0.25rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 800;
  line-height: 1;
  color: rgb(153 27 27);
  box-shadow: 0 8px 20px rgba(239, 68, 68, 0.18);
}

:global(.dark) .owner-panel-badge,
:global(.dark-mode) .owner-panel-badge {
  background: rgba(127, 29, 29, 0.35);
  color: rgb(252 165 165);
}
</style>

