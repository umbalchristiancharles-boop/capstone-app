<template>
  <section>
    <h2>Active Deliveries</h2>
    <div v-if="!deliveries.length">No active deliveries.</div>
    <ul v-else>
      <li v-for="d in deliveries" :key="d.id">{{ d.title }} - {{ d.status }}</li>
    </ul>
    <h2>Suppliers</h2>
    <div v-if="!suppliers.length">No suppliers found.</div>
    <ul v-else>
      <li v-for="s in suppliers" :key="s.id">{{ s.name }}</li>
    </ul>
  </section>
</template>
<script setup>
defineProps({ deliveries: Array, suppliers: Array })
</script>
