<template>
  <OwnerPanelLayout :userProfile="userProfile" :panelTitle="'Manager Inventory Panel'" :panelDescription="'Monitor stock, add/update products, and view inventory reports.'">
    <template #main>
      <div class="overview-grid">
        <div class="overview-card"><span class="overview-label">Total Products:</span><span class="overview-value">{{ dashboardTotals.totalProducts }}</span></div>
        <div class="overview-card"><span class="overview-label">Low Stock:</span><span class="overview-value">{{ dashboardTotals.lowStock }}</span></div>
        <div class="overview-card"><span class="overview-label">Stock Value:</span><span class="overview-value">{{ dashboardTotals.stockValue }}</span></div>
      </div>
      <inventory-panel-content :products="products" :reports="inventoryReports" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import InventoryPanelContent from './inventory/InventoryPanelContent.vue'
import axios from 'axios'

const userProfile = ref({})
const dashboardTotals = ref({ totalProducts: 0, lowStock: 0, stockValue: 0 })
const products = ref([])
const inventoryReports = ref([])

onMounted(async () => {
  const res = await axios.get('/api/manager/inventory/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const dash = await axios.get('/api/manager/inventory/dashboard', { withCredentials: true })
  dashboardTotals.value = dash.data
  const prods = await axios.get('/api/manager/inventory/products', { withCredentials: true })
  products.value = prods.data
  const reports = await axios.get('/api/manager/inventory/reports', { withCredentials: true })
  inventoryReports.value = reports.data
})
</script>
