<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Staff Cashier Panel'"
    :panelDescription="'POS panel for creating transactions.'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <cashier-pos-panel :transactions="transactions" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import CashierPosPanel from './cashier/CashierPosPanel.vue'
import axios from 'axios'

const userProfile = ref({})
const transactions = ref([])

// Handle profile update from layout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

onMounted(async () => {
  const res = await axios.get('/api/staff/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const tx = await axios.get('/api/staff/cashier/transactions', { withCredentials: true })
  transactions.value = tx.data
})
</script>
