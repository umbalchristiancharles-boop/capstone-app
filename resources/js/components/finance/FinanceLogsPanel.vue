<template>
  <section>
    <h2>Finance Logs</h2>
    <div v-if="!logs.length">No finance logs found.</div>
    <ul v-else>
      <li v-for="log in logs" :key="log.id">{{ log.description }} - {{ log.amount }}</li>
    </ul>
  </section>
</template>
<script setup>
defineProps({ logs: Array })
</script>
