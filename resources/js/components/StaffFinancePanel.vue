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
      <div class="panel-section" style="position: relative;">
        <h2 class="section-title" style="margin: 0 0 10px 0; position: relative;">
          Finance Logs
          <span v-if="financePendingCount > 0" class="panel-badge">{{ financePendingCount }}</span>
        </h2>
      </div>
      <finance-logs-panel :logs="financeLogs" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import FinanceLogsPanel from './finance/FinanceLogsPanel.vue'
import axios from 'axios'
import { showToast } from './toastStore'

const userProfile = ref({})
const financeLogs = ref([])
const notificationCounts = ref({ finance: 0 })
const hasNotified = ref(false)
const financePendingCount = computed(() => Number(notificationCounts.value?.finance || 0))

watch(financePendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending finance approvals.', 'info')
    hasNotified.value = true
  }
})

// Handle profile update from layout
function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

onMounted(async () => {
  const res = await axios.get('/api/staff/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const logs = await axios.get('/api/staff/finance/logs', { withCredentials: true })
  financeLogs.value = logs.data
  await loadPanelNotifications()
})

async function loadPanelNotifications() {
  try {
    const res = await axios.get('/api/panel-notifications', { withCredentials: true })
    if (res.data && res.data.ok) {
      notificationCounts.value = { finance: Number(res.data.counts?.finance || 0) }
    }
  } catch (e) {
    notificationCounts.value = { finance: 0 }
  }
}
</script>

<style scoped>
.panel-badge {
  position: absolute;
  top: -8px;
  right: -16px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
}
</style>
