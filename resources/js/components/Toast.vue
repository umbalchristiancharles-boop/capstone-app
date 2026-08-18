<template>
  <Teleport to="body">
    <div class="toast-viewport" v-if="state.toasts.length">
      <div v-for="t in state.toasts" :key="t.id" class="toast" :class="t.type">
        <div class="toast-message">{{ t.message }}</div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { toastState as state } from './toastStore'
</script>

<style scoped>
.toast-viewport {
  position: fixed !important;
  right: 16px !important;
  top: 16px !important;
  z-index: 999999 !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 8px !important;
}
.toast {
  min-width: 220px;
  max-width: 420px;
  padding: 10px 12px;
  border-radius: 8px;
  color: #fff;
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
  font-weight: 600;
}
.toast.success { background: #16a34a; }
.toast.error { background: #dc2626; }
.toast.info { background: #2563eb; }
.toast .toast-message { font-size: 0.95rem; }
</style>
