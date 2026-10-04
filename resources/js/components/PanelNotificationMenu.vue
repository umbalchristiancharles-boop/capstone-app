<template>
  <div ref="menuRoot" class="panel-notification-menu" @click.stop>
    <button
      type="button"
      class="panel-notification-menu__button"
      :aria-label="notificationCount ? `Notifications, ${notificationCount} unread` : 'Notifications'"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      title="Notifications"
      @click="isOpen = !isOpen"
    >
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
        <path d="M10 21h4"></path>
      </svg>
      <span v-if="notificationCount > 0" class="panel-notification-menu__badge">
        {{ notificationCount > 99 ? '99+' : notificationCount }}
      </span>
    </button>
    <div v-if="isOpen" class="panel-notification-menu__dropdown" role="menu" aria-label="Notifications">
      <p class="panel-notification-menu__heading">Notifications</p>
      <button
        v-for="item in notificationItems"
        :key="item.key"
        type="button"
        class="panel-notification-menu__entry"
        role="menuitem"
        @click="selectNotification(item.key)"
      >
        <span>{{ item.label }}</span>
        <strong>{{ item.count }}</strong>
      </button>
      <p v-if="notificationItems.length === 0" class="panel-notification-menu__empty">You're all caught up.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

defineProps({
  notificationItems: { type: Array, default: () => [] },
  notificationCount: { type: Number, default: 0 },
})

const emit = defineEmits(['select'])
const menuRoot = ref(null)
const isOpen = ref(false)

function selectNotification(key) {
  isOpen.value = false
  emit('select', key)
}

function closeWhenOutside(event) {
  if (!menuRoot.value?.contains(event.target)) isOpen.value = false
}

function closeOnEscape(event) {
  if (event.key === 'Escape') isOpen.value = false
}

onMounted(() => {
  document.addEventListener('click', closeWhenOutside)
  document.addEventListener('keydown', closeOnEscape)
})

onUnmounted(() => {
  document.removeEventListener('click', closeWhenOutside)
  document.removeEventListener('keydown', closeOnEscape)
})
</script>

<style scoped>
.panel-notification-menu {
  position: relative;
  display: flex;
  align-items: center;
}

.panel-notification-menu__button {
  position: relative;
  display: grid;
  width: 38px;
  height: 38px;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 50%;
  color: #334155;
  background: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  transition: background-color 160ms ease, box-shadow 160ms ease;
}

.panel-notification-menu__button:hover,
.panel-notification-menu__button:focus-visible {
  background: #fff;
  box-shadow: 0 4px 12px rgba(36, 52, 71, 0.12);
  outline: none;
}

.panel-notification-menu__badge,
.panel-notification-menu__entry strong {
  display: grid;
  place-items: center;
  border-radius: 999px;
  color: #fff;
  background: #ef4444;
  font-weight: 800;
}

.panel-notification-menu__badge {
  position: absolute;
  top: -5px;
  right: -5px;
  min-width: 19px;
  height: 19px;
  padding: 0 4px;
  border: 2px solid #f4ebe4;
  font-size: 0.65rem;
  line-height: 1;
}

.panel-notification-menu__dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  z-index: 500;
  width: min(300px, calc(100vw - 24px));
  max-height: min(360px, calc(100vh - 90px));
  padding: 0.5rem;
  overflow-y: auto;
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.18);
}

.panel-notification-menu__heading {
  margin: 0;
  padding: 0.55rem 0.65rem;
  color: #334155;
  font-size: 0.8rem;
  font-weight: 800;
}

.panel-notification-menu__entry {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.65rem;
  border: 0;
  border-radius: 9px;
  color: #334155;
  background: transparent;
  font: inherit;
  font-size: 0.78rem;
  text-align: left;
  cursor: pointer;
}

.panel-notification-menu__entry:hover,
.panel-notification-menu__entry:focus-visible {
  background: #fff7ed;
  outline: none;
}

.panel-notification-menu__entry strong {
  min-width: 1.5rem;
  height: 1.5rem;
  padding: 0 0.35rem;
  font-size: 0.7rem;
}

.panel-notification-menu__empty {
  margin: 0;
  padding: 0.65rem;
  color: #64748b;
  font-size: 0.78rem;
}
</style>
