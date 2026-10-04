<template>
  <div ref="menuRoot" class="owner-message-menu" @click.stop>
    <button
      type="button"
      class="owner-message-menu__button"
      :aria-label="unreadUserCount ? `Messages, ${unreadUserCount} users with unread messages` : 'Messages'"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      title="Messages"
      @click="toggleMenu"
    >
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"></path>
      </svg>
      <span v-if="unreadUserCount > 0" class="owner-message-menu__badge">
        {{ unreadUserCount > 99 ? '99+' : unreadUserCount }}
      </span>
    </button>

    <section v-if="isOpen" class="owner-message-menu__dropdown" aria-label="Messages">
      <header class="owner-message-menu__header">
        <div>
          <h2>Messages</h2>
          <p>{{ unreadUserCount ? `${unreadUserCount} users with unread messages` : 'Your conversations' }}</p>
        </div>
        <button type="button" class="owner-message-menu__refresh" :disabled="isLoading" aria-label="Refresh conversations" title="Refresh" @click="loadUsers">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20 7v5h-5"></path>
            <path d="M4 17v-5h5"></path>
            <path d="M5.6 9a7 7 0 0 1 11.6-2L20 12M4 12l2.8 5a7 7 0 0 0 11.6-2"></path>
          </svg>
        </button>
      </header>

      <label class="owner-message-menu__search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path>
        </svg>
        <input v-model="searchQuery" type="search" placeholder="Search people" aria-label="Search messages" />
      </label>

      <div class="owner-message-menu__filters" role="tablist" aria-label="Filter conversations">
        <button type="button" role="tab" :aria-selected="activeFilter === 'all'" :class="{ 'is-active': activeFilter === 'all' }" @click="activeFilter = 'all'">All</button>
        <button type="button" role="tab" :aria-selected="activeFilter === 'unread'" :class="{ 'is-active': activeFilter === 'unread' }" @click="activeFilter = 'unread'">
          Unread <span v-if="unreadUserCount">{{ unreadUserCount }}</span>
        </button>
      </div>

      <div class="owner-message-menu__users">
        <p v-if="isLoading && users.length === 0" class="owner-message-menu__state">Loading conversations...</p>
        <p v-else-if="loadError" class="owner-message-menu__state owner-message-menu__state--error">{{ loadError }}</p>
        <p v-else-if="visibleUsers.length === 0" class="owner-message-menu__state">
          {{ searchQuery ? 'No matching conversations.' : activeFilter === 'unread' ? 'No unread messages.' : 'No conversations yet.' }}
        </p>
        <button
          v-for="user in visibleUsers"
          :key="user.id"
          type="button"
          class="owner-message-menu__user"
          :class="{ 'has-unread': Number(user.unread_count) > 0 }"
          @click="openConversation(user)"
        >
          <span class="owner-message-menu__avatar">{{ initials(user.name) }}</span>
          <span class="owner-message-menu__user-info">
            <strong>{{ user.name || 'User' }}</strong>
            <small>{{ roleLabel(user) }}</small>
          </span>
          <span v-if="Number(user.unread_count) > 0" class="owner-message-menu__unread">
            {{ user.unread_count > 99 ? '99+' : user.unread_count }}
          </span>
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'

const menuRoot = ref(null)
const users = ref([])
const isOpen = ref(false)
const isLoading = ref(false)
const loadError = ref('')
const searchQuery = ref('')
const activeFilter = ref('all')
let refreshTimer = null
let requestInProgress = false

const uniqueUsers = computed(() => {
  const unique = new Map()
  for (const user of users.value) {
    const id = String(user.id ?? '')
    if (id && !unique.has(id)) unique.set(id, user)
  }
  return [...unique.values()]
})

const unreadUserCount = computed(() => uniqueUsers.value.filter(user => Number(user.unread_count) > 0).length)

const visibleUsers = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return uniqueUsers.value.filter(user => {
    if (activeFilter.value === 'unread' && Number(user.unread_count) <= 0) return false
    return !query || `${user.name || ''} ${user.role || ''} ${user.department || ''}`.toLowerCase().includes(query)
  })
})

function applyUsers(list) {
  users.value = Array.isArray(list) ? list : []
}

async function loadUsers() {
  if (requestInProgress) return
  requestInProgress = true
  isLoading.value = true
  loadError.value = ''
  try {
    const response = await axios.get('/api/hr/messages/users', { withCredentials: true })
    applyUsers(response.data?.users)
  } catch (error) {
    loadError.value = error.response?.data?.message || 'Unable to load conversations.'
  } finally {
    isLoading.value = false
    requestInProgress = false
  }
}

function toggleMenu() {
  isOpen.value = !isOpen.value
  if (isOpen.value) loadUsers()
}

function openConversation(user) {
  isOpen.value = false
  window.dispatchEvent(new CustomEvent('open-message-widget', {
    detail: { ownerPanel: true, userId: user.id },
  }))
}

function handleUsersUpdated(event) {
  if (Array.isArray(event.detail?.users)) applyUsers(event.detail.users)
}

function closeWhenOutside(event) {
  if (!menuRoot.value?.contains(event.target)) isOpen.value = false
}

function closeOnEscape(event) {
  if (event.key === 'Escape') isOpen.value = false
}

function initials(name) {
  return String(name || 'U').trim().split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase()
}

function roleLabel(user) {
  const role = String(user.role || 'User').replace(/_/g, ' ')
  return user.department ? `${role} · ${user.department}` : role
}

onMounted(() => {
  loadUsers()
  refreshTimer = window.setInterval(loadUsers, 15000)
  window.addEventListener('owner-message-users-updated', handleUsersUpdated)
  document.addEventListener('click', closeWhenOutside)
  document.addEventListener('keydown', closeOnEscape)
})

onUnmounted(() => {
  if (refreshTimer) window.clearInterval(refreshTimer)
  window.removeEventListener('owner-message-users-updated', handleUsersUpdated)
  document.removeEventListener('click', closeWhenOutside)
  document.removeEventListener('keydown', closeOnEscape)
})
</script>

<style scoped>
.owner-message-menu {
  position: relative;
  display: flex;
  align-items: center;
}

.owner-message-menu__button {
  position: relative;
  display: grid;
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  place-items: center;
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 50%;
  color: #334155;
  background: rgba(255, 255, 255, 0.7);
  cursor: pointer;
}

.owner-message-menu__button:hover,
.owner-message-menu__button:focus-visible {
  background: #fff;
  box-shadow: 0 4px 12px rgba(36, 52, 71, 0.12);
  outline: none;
}

.owner-message-menu__badge,
.owner-message-menu__unread {
  display: grid;
  place-items: center;
  border-radius: 999px;
  color: #fff;
  background: #ef4444;
  font-weight: 800;
}

.owner-message-menu__badge {
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

.owner-message-menu__dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  z-index: 510;
  display: flex;
  width: min(360px, calc(100vw - 24px));
  max-height: min(560px, calc(100vh - 90px));
  flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(219, 188, 160, 0.5);
  border-radius: 16px;
  background: #fffdfa;
  box-shadow: 0 18px 44px rgba(52, 34, 22, 0.2);
  color: #3d2a1f;
}

.owner-message-menu__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 17px 12px;
  border-bottom: 1px solid #f1e5da;
}

.owner-message-menu__header h2 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 800;
}

.owner-message-menu__header p {
  margin: 3px 0 0;
  color: #94735f;
  font-size: 0.72rem;
}

.owner-message-menu__refresh {
  display: grid;
  width: 32px;
  height: 32px;
  place-items: center;
  border: 1px solid #eaded4;
  border-radius: 9px;
  color: #725641;
  background: #fff;
  cursor: pointer;
}

.owner-message-menu__refresh:disabled {
  opacity: 0.55;
  cursor: wait;
}

.owner-message-menu__search {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 12px 14px 8px;
  padding: 0 10px;
  border: 1px solid #eaded4;
  border-radius: 10px;
  color: #94735f;
  background: #fff;
}

.owner-message-menu__search input {
  width: 100%;
  height: 36px;
  border: 0;
  outline: 0;
  color: #3d2a1f;
  background: transparent;
  font: inherit;
  font-size: 0.78rem;
}

.owner-message-menu__filters {
  display: flex;
  gap: 6px;
  padding: 0 14px 9px;
}

.owner-message-menu__filters button {
  padding: 6px 11px;
  border: 0;
  border-radius: 999px;
  color: #725641;
  background: transparent;
  font: inherit;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
}

.owner-message-menu__filters button.is-active {
  color: #a34214;
  background: #fff0e5;
}

.owner-message-menu__filters button span {
  margin-left: 3px;
  color: #b94b1b;
}

.owner-message-menu__users {
  min-height: 48px;
  overflow-y: auto;
  padding: 0 8px 8px;
}

.owner-message-menu__user {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 10px;
  padding: 9px 8px;
  border: 0;
  border-radius: 11px;
  color: #3d2a1f;
  background: transparent;
  text-align: left;
  cursor: pointer;
}

.owner-message-menu__user:hover,
.owner-message-menu__user:focus-visible {
  outline: none;
  background: #fff3e8;
}

.owner-message-menu__avatar {
  display: grid;
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  place-items: center;
  border-radius: 50%;
  color: #8e431d;
  background: #f8d4b4;
  font-size: 0.72rem;
  font-weight: 800;
}

.owner-message-menu__user-info {
  display: grid;
  min-width: 0;
  flex: 1;
  gap: 3px;
}

.owner-message-menu__user-info strong,
.owner-message-menu__user-info small {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.owner-message-menu__user-info strong {
  font-size: 0.78rem;
}

.owner-message-menu__user-info small {
  color: #94735f;
  font-size: 0.68rem;
}

.owner-message-menu__unread {
  min-width: 21px;
  height: 21px;
  padding: 0 5px;
  font-size: 0.65rem;
}

.owner-message-menu__state {
  margin: 0;
  padding: 20px 12px;
  color: #8c796d;
  font-size: 0.78rem;
  text-align: center;
}

.owner-message-menu__state--error {
  color: #b42318;
}
</style>
