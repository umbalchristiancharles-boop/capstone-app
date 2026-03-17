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

<style scoped>
h1, h2 {
  color: #0066FF !important;
  font-weight: 800 !important;
  font-family: 'Inter', 'Poppins', sans-serif !important;
  letter-spacing: -0.5px !important;
  margin-bottom: 8px !important;
}

.admin-label, .metric-label, .overview-label {
  color: #64748B !important;
}

.avatar-change-text {
  color: #0066FF !important;
}

.btn-primary {
  background: #0066FF !important;
  color: white !important;
}

.btn-primary:hover {
  background: #0057e6 !important;
}

.btn-secondary, .btn-outline {
  background: #64748B !important;
  color: white !important;
}

.btn-secondary:hover, .btn-outline:hover {
  background: #525c6a !important;
}
</style>
