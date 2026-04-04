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
            <li><router-link to="/owner/dish-approval">Dish Approval</router-link></li>
            <li><router-link to="/owner/staff-management">Staff Management</router-link></li>
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

const userProfile = ref({})

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
})
</script>

<style scoped>
.owner-dashboard { padding: 18px; }
.owner-welcome h2 { margin: 0 0 8px; }
</style>

