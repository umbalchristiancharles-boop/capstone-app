<template>
  <section class="logistics-panel">
    <header class="logistics-panel__header">
      <div>
        <span class="logistics-panel__eyebrow">Delivery tracking</span>
        <h2 class="section-title">Active Deliveries</h2>
        <p>Monitor delivery progress and review the latest updates.</p>
      </div>
      <span class="logistics-panel__count">
        {{ deliveries.length }} {{ deliveries.length === 1 ? 'delivery' : 'deliveries' }}
      </span>
    </header>
    <div v-if="!deliveries.length" class="empty-state logistics-panel__empty">
      <span class="logistics-panel__empty-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none">
          <path d="M3.75 7.5h10.5v10.75H3.75zM14.25 11h3.4l2.6 2.75v4.5h-6" />
          <circle cx="7.75" cy="18.25" r="1.75" />
          <circle cx="17.25" cy="18.25" r="1.75" />
        </svg>
      </span>
      <strong>No active deliveries</strong>
      <span>New delivery updates will appear here when they become available.</span>
    </div>
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
.logistics-panel {
  min-width: 0;
  padding: 1.25rem;
  border: 1px solid #eadfd5;
  border-radius: 14px;
  background: #fffaf5;
  box-shadow: 0 8px 22px rgba(61, 42, 31, 0.07);
}

.logistics-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid #eee2d8;
}

.logistics-panel__eyebrow {
  display: block;
  margin-bottom: 0.25rem;
  color: #a15d38;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.11em;
  text-transform: uppercase;
}

.section-title {
  margin: 0;
  color: #3d2a1f;
  font-size: 1.2rem;
  font-weight: 800;
  line-height: 1.3;
  letter-spacing: -0.02em;
}

.logistics-panel__header p {
  margin: 0.25rem 0 0;
  color: #846e5d;
  font-size: 0.82rem;
  line-height: 1.45;
}

.logistics-panel__count {
  flex: 0 0 auto;
  padding: 0.4rem 0.7rem;
  border: 1px solid #eadfd5;
  border-radius: 999px;
  background: #ffffff;
  color: #704b34;
  font-size: 0.75rem;
  font-weight: 700;
}

.delivery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
  gap: 0.85rem;
}

.delivery-card {
  min-width: 0;
  padding: 1rem;
  border: 1px solid #eadfd5;
  border-radius: 12px;
  background: #ffffff;
  box-shadow: 0 3px 10px rgba(61, 42, 31, 0.05);
}

.delivery-title {
  color: #3d2a1f;
  font-size: 0.96rem;
  font-weight: 750;
  overflow-wrap: anywhere;
}

.delivery-meta {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  flex-wrap: wrap;
  margin-top: 0.55rem;
  color: #846e5d;
  font-size: 0.8rem;
}

.delivery-status {
  padding: 0.25rem 0.55rem;
  border: 1px solid #eadfd5;
  border-radius: 999px;
  background: #f7f0ea;
  color: #704b34;
  font-weight: 700;
}

.delivery-date {
  color: #8a7b70;
}

.delivery-body {
  margin-top: 0.65rem;
  color: #675648;
  font-size: 0.86rem;
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.logistics-panel__empty {
  display: flex;
  min-height: 150px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 1.5rem;
  border: 1px dashed #d9c7b8;
  border-radius: 12px;
  background: #fffdfb;
  color: #846e5d;
  text-align: center;
}

.logistics-panel__empty-icon {
  display: grid;
  width: 2.6rem;
  height: 2.6rem;
  place-items: center;
  margin-bottom: 0.2rem;
  border-radius: 50%;
  background: #f4e8de;
  color: #a15d38;
}

.logistics-panel__empty-icon svg {
  width: 1.35rem;
  height: 1.35rem;
  stroke: currentColor;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.6;
}

.logistics-panel__empty strong {
  color: #493629;
  font-size: 0.96rem;
}

.logistics-panel__empty > span:last-child {
  max-width: 42ch;
  font-size: 0.82rem;
  line-height: 1.5;
}

@media (max-width: 640px) {
  .logistics-panel {
    padding: 0.9rem;
    border-radius: 12px;
  }

  .logistics-panel__header {
    align-items: flex-start;
    gap: 0.65rem;
    margin-bottom: 0.8rem;
    padding-bottom: 0.75rem;
  }

  .section-title {
    font-size: 1.08rem;
  }

  .logistics-panel__count {
    padding: 0.35rem 0.55rem;
    font-size: 0.7rem;
  }

  .logistics-panel__empty {
    min-height: 130px;
    padding: 1rem;
  }
}
</style>
