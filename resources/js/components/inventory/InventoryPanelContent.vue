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
<script setup>
defineProps({ products: Array, reports: Array })
</script>
