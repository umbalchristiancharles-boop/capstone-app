<template>
  <Teleport to="body">
    <div class="toast-viewport" v-if="state.toasts.length">
      <TransitionGroup name="toast-slide" tag="div" class="toast-stack">
        <article
          v-for="t in state.toasts"
          :key="t.id"
          class="toast"
          :class="`toast--${toastType(t.type)}`"
          :role="t.type === 'error' ? 'alert' : 'status'"
          :aria-live="t.type === 'error' ? 'assertive' : 'polite'"
        >
          <span class="toast-icon" aria-hidden="true">{{ toastIcon(t.type) }}</span>
          <div class="toast-content">
            <strong class="toast-title">{{ toastTitle(t.type) }}</strong>
            <p class="toast-message">{{ t.message }}</p>
          </div>
          <button
            type="button"
            class="toast-dismiss"
            aria-label="Dismiss notification"
            @click="dismissToast(t.id)"
          >×</button>
        </article>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { toastState as state, clearToast } from './toastStore'

function dismissToast(id) {
  clearToast(id)
}

function toastType(type) {
  return ['success', 'error', 'warning'].includes(type) ? type : 'info'
}

function toastIcon(type) {
  const icons = { success: '✓', error: '!', warning: '!', info: 'i' }
  return icons[toastType(type)]
}

function toastTitle(type) {
  const titles = {
    success: 'Success',
    error: 'Needs attention',
    warning: 'Please check',
    info: 'Heads up',
  }
  return titles[toastType(type)]
}
</script>

<style scoped>
.toast-viewport {
  position: fixed !important;
  right: 16px !important;
  top: 16px !important;
  z-index: 999999 !important;
  width: min(400px, calc(100vw - 32px));
  pointer-events: none;
}

.toast-stack {
  display: grid;
  gap: 10px;
}

.toast {
  position: relative;
  display: grid;
  grid-template-columns: 36px minmax(0, 1fr) 28px;
  align-items: start;
  gap: 11px;
  overflow: hidden;
  padding: 14px 13px;
  border: 1px solid #e7e5e4;
  border-left: 4px solid #ea580c;
  border-radius: 12px;
  color: #292524;
  background: #ffffff;
  box-shadow: 0 12px 32px rgba(41, 37, 36, 0.16);
  pointer-events: auto;
}

.toast--success {
  border-left-color: #16a34a;
}

.toast--error {
  border-left-color: #dc2626;
}

.toast--warning {
  border-left-color: #d97706;
}

.toast--info {
  border-left-color: #ea580c;
}

.toast-icon {
  display: grid;
  width: 34px;
  height: 34px;
  place-items: center;
  border-radius: 50%;
  color: #c2410c;
  background: #fff7ed;
  font-size: 1rem;
  font-weight: 800;
}

.toast--success .toast-icon {
  color: #15803d;
  background: #f0fdf4;
}

.toast--error .toast-icon {
  color: #b91c1c;
  background: #fef2f2;
}

.toast--warning .toast-icon {
  color: #b45309;
  background: #fffbeb;
}

.toast-content {
  min-width: 0;
  padding-top: 1px;
}

.toast-title {
  display: block;
  margin-bottom: 3px;
  color: #292524;
  font-size: 0.86rem;
  font-weight: 750;
}

.toast .toast-message {
  margin: 0;
  color: #57534e;
  font-size: 0.82rem;
  font-weight: 500;
  line-height: 1.45;
  overflow-wrap: anywhere;
}

.toast-dismiss {
  display: grid;
  width: 28px;
  height: 28px;
  place-items: center;
  padding: 0;
  border: 0;
  border-radius: 7px;
  color: #78716c;
  background: transparent;
  font-size: 1.25rem;
  line-height: 1;
  cursor: pointer;
}

.toast-dismiss:hover,
.toast-dismiss:focus-visible {
  color: #292524;
  background: #f5f5f4;
}

.toast-dismiss:focus-visible {
  outline: 2px solid #ea580c;
  outline-offset: 2px;
}

.toast-slide-enter-active,
.toast-slide-leave-active,
.toast-slide-move {
  transition: opacity 180ms ease, transform 180ms ease;
}

.toast-slide-enter-from,
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(12px);
}

.toast-slide-leave-active {
  position: absolute;
}
</style>
