<template>
  <OwnerPanelLayout :userProfile="userProfile" :panelTitle="'Manager Logistics Panel'" :panelDescription="'Track deliveries and manage suppliers.'">
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Active Deliveries:</span><span class="overview-value">{{ dashboardTotals.activeDeliveries }}</span></div>
        <div class="overview-card"><span class="overview-label">Suppliers:</span><span class="overview-value">{{ dashboardTotals.suppliers }}</span></div>
      </div>
      <logistics-panel-content :deliveries="deliveries" :suppliers="suppliers" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import LogisticsPanelContent from './logistics/LogisticsPanelContent.vue'
import axios from 'axios'

const userProfile = ref({})
const dashboardTotals = ref({ activeDeliveries: 0, suppliers: 0 })
const deliveries = ref([])
const suppliers = ref([])

onMounted(async () => {
  const res = await axios.get('/api/manager/logistics/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const dash = await axios.get('/api/manager/logistics/dashboard', { withCredentials: true })
  dashboardTotals.value = dash.data
  const del = await axios.get('/api/manager/logistics/deliveries', { withCredentials: true })
  deliveries.value = del.data
  const supp = await axios.get('/api/manager/logistics/suppliers', { withCredentials: true })
  suppliers.value = supp.data
})
</script>
