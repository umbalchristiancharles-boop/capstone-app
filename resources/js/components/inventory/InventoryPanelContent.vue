<!--
  =============================================================================
  DEPRECATED COMPONENT - NOT USED
  =============================================================================
  This component is deprecated and not used in the application.

  The active implementation for Inventory functionality is:
  - InventoryStaffPanel.vue (for staff inventory operations)
  - ProductList.vue (reusable product list component)
  - StaffInventoryController.php (backend API)

  This file is kept for reference only and can be safely removed.
  =============================================================================
-->
<template>
  <section>
    <h2>Product List</h2>
    <div v-if="!products.length">No products found.</div>
    <ul v-else>
      <li v-for="prod in products" :key="prod.id">{{ prod.name }} - Stock: {{ prod.stock }}</li>
    </ul>
    <h2>Inventory Reports</h2>
    <div v-if="!reports.length">No inventory reports available.</div>
    <ul v-else>
      <li v-for="report in reports" :key="report.id">{{ report.title }}</li>
    </ul>
  </section>
</template>
<script setup lang="ts">
const props = withDefaults(defineProps<{
  products?: Array<any>
  reports?: Array<any>
}>(), {
  products: () => [],
  reports: () => []
})
</script>
