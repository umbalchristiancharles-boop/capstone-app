<template>
  <section class="logistics-panel">
    <h2 class="section-title">Active Deliveries</h2>
    <div v-if="!deliveries.length" class="empty-state">No active deliveries.</div>
    <div v-else class="delivery-grid">
      <div v-for="d in deliveries" :key="d.id" class="delivery-card">
        <div class="delivery-title">{{ d.title || d.reference || ('Delivery #' + d.id) }}</div>
        <div class="delivery-meta">
          <span class="delivery-status">{{ d.status || 'Pending' }}</span>
          <span class="delivery-date">{{ d.updated_at || d.created_at || '' }}</span>
        </div>
        <div class="delivery-body">{{ d.note || d.description || '' }}</div>
      </div>
    </div>

    <!-- Suppliers list removed from supplier panel -->
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

// Toast notification helper (fallback if not imported)
const showToast = (msg, type = 'info') => {
  // Try to use toast if available, otherwise console
  console.log(`[${type.toUpperCase()}] ${msg}`)
}

const props = withDefaults(defineProps<{
  deliveries?: Array<any>
}>(), {
  deliveries: () => []
})
const emit = defineEmits(['product-added'])
</script>

<style scoped>
/* Deliveries */
.delivery-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(240px,1fr)); gap:0.75rem; margin-top:0.75rem }
.delivery-card { background:#fff; border-radius:10px; padding:0.75rem; box-shadow:0 8px 24px rgba(15,23,42,0.06); border:1px solid #eef2f6 }
.delivery-title { font-weight:700; color:#0f172a }
.delivery-meta { display:flex; gap:0.5rem; align-items:center; font-size:0.85rem; color:#4b5563; margin-top:4px }
.delivery-status { background:#f1f5f9; padding:4px 8px; border-radius:8px }
.delivery-date { color:#9ca3af }
.delivery-body { margin-top:8px; color:#374151; font-size:0.95rem }

.empty-state { color:#6b7280 }

.section-title { margin-top:0.6rem; margin-bottom:0.4rem }
</style>
