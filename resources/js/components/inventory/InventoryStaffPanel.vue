<template>
  <section>
    <h2>Product List</h2>
    <div v-if="!products.length">No products found.</div>
    <ul v-else>
      <li v-for="prod in products" :key="prod.id">{{ prod.name }} - Stock: {{ prod.stock }}</li>
    </ul>
  </section>
</template>
<script setup>
defineProps({ products: Array })
</script>
