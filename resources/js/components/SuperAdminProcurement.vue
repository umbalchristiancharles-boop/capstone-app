<template>
  <div class="panel-root">
    <button @click="router.push('/super-admin-panel')" class="btn-secondary back-to-dashboard-btn">Back to Super Admin</button>
    <h1>Super Admin Procurement</h1>
    <p>Manage procurement managers and procurement-related settings across branches.</p>
    <!-- Placeholder content: build out procurement features here -->
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<style scoped>
.panel-root { padding: 1rem; }
.back-to-dashboard-btn { margin-bottom: 1rem; }
</style>
