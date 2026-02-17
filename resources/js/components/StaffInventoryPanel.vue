<template>
  <OwnerPanelLayout :userProfile="userProfile" :panelTitle="'Staff Inventory Panel'" :panelDescription="'Update stock and view product list.'">
    <template #main>
      <inventory-staff-panel :products="products" />
    </template>
  </OwnerPanelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import InventoryStaffPanel from './inventory/InventoryStaffPanel.vue'
import axios from 'axios'

const userProfile = ref({})
const products = ref([])

onMounted(async () => {
  const res = await axios.get('/api/staff/inventory/profile', { withCredentials: true })
  userProfile.value = res.data.user
  const prods = await axios.get('/api/staff/inventory/products', { withCredentials: true })
  products.value = prods.data
})
</script>
