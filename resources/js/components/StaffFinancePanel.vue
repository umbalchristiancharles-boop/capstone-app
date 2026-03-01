<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Staff Finance Panel'"
    :panelDescription="'Record payments and view financial logs.'"
    :enableProfileUpdate="true"
    :canEditProfile="false"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <finance-logs-panel :logs="financeLogs" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import FinanceLogsPanel from './finance/FinanceLogsPanel.vue'
import axios from 'axios'

const userProfile = ref({})
const financeLogs = ref([])

// Handle profile update from layout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

onMounted(async () => {
  const res = await axios.get('/api/staff/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const logs = await axios.get('/api/staff/finance/logs', { withCredentials: true })
  financeLogs.value = logs.data
})
</script>
