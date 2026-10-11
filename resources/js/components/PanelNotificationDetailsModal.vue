<template>
  <transition name="fade">
    <div
      v-if="show"
      class="info-backdrop info-backdrop--finance-style owner-announcement-backdrop"
      @click.self="$emit('close')"
    >
      <section
        class="info-modal info-modal--finance-style owner-announcement-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="panel-notification-modal-title"
      >
        <button type="button" class="info-modal-close" aria-label="Close notifications" @click="$emit('close')">✕</button>
        <h3 id="panel-notification-modal-title">{{ title }}</h3>
        <p class="info-sub">{{ subtitle }}</p>
        <div class="info-grid owner-announcement-list">
          <template v-if="showAnnouncements">
            <p v-if="loading" class="owner-announcement-state">Loading announcements...</p>
            <p v-else-if="announcements.length === 0" class="owner-announcement-state">No announcements found.</p>
            <article v-for="announcement in announcements" :key="announcement.id" class="owner-announcement-entry">
              <strong>{{ announcement.title }}</strong>
              <small>{{ new Date(announcement.created_at).toLocaleString() }}<span v-if="announcement.target"> · {{ announcement.target }}</span></small>
              <p>{{ announcement.message }}</p>
            </article>
          </template>
          <template v-else>
            <p v-if="detailItems.length === 0" class="owner-announcement-state">No items need attention.</p>
            <ul v-else class="panel-notification-details__list panel-notification-details__counts owner-notification-modal-counts">
              <li v-for="item in detailItems" :key="item.key">
                <span>{{ item.label }}</span>
                <strong>{{ item.count }}</strong>
              </li>
            </ul>
          </template>
        </div>
        <div class="info-actions">
          <button type="button" class="btn-outline" @click="$emit('close')">Close</button>
        </div>
      </section>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'Notifications' },
  subtitle: { type: String, default: 'Latest updates for your account.' },
  showAnnouncements: { type: Boolean, default: true },
  announcements: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  detailItems: { type: Array, default: () => [] },
})

defineEmits(['close'])
</script>

<style scoped>
.owner-announcement-backdrop {
  z-index: 600;
  background: rgba(0, 0, 0, 0.42);
  -webkit-backdrop-filter: blur(3px);
  backdrop-filter: blur(3px);
}

.owner-announcement-modal {
  position: relative;
  display: flex;
  width: min(90vw, 640px);
  max-height: 80vh;
  flex-direction: column;
  overflow: hidden;
  padding: 0;
  border: 1px solid rgba(219, 188, 160, 0.4);
  border-radius: 20px;
  color: #3d2a1f;
  background: linear-gradient(180deg, #fff 0%, #fef8f3 100%);
  box-shadow: 0 28px 72px rgba(15, 23, 42, 0.18);
}

.owner-announcement-modal h3 {
  flex: 0 0 auto;
  margin: 0;
  padding: 1.25rem 1.5rem 0.35rem;
  color: #3d2a1f;
  font-size: 1.15rem;
  font-weight: 800;
}

.owner-announcement-modal .info-sub {
  flex: 0 0 auto;
  margin: 0;
  padding: 0 1.5rem 1rem;
  border-bottom: 1px solid rgba(219, 188, 160, 0.3);
  color: #94735f;
}

.owner-announcement-list {
  display: block;
  flex: 1 1 auto;
  min-height: 0;
  max-height: min(55vh, 480px);
  overflow-y: auto;
  padding: 0.75rem 1.5rem;
  background: transparent;
}

.owner-announcement-entry {
  padding: 0.85rem 0;
  border-bottom: 1px solid rgba(219, 188, 160, 0.3);
}

.owner-announcement-entry:last-child {
  border-bottom: 0;
}

.owner-announcement-entry strong {
  display: block;
  color: #3d2a1f;
  font-size: 0.92rem;
}

.owner-announcement-entry small {
  display: block;
  margin-top: 0.25rem;
  color: #94735f;
  font-size: 0.75rem;
}

.owner-announcement-entry p {
  margin: 0.45rem 0 0;
  color: #5f4b3e;
  font-size: 0.86rem;
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.owner-announcement-state {
  margin: 0;
  padding: 0.75rem 0;
  color: #64748b;
  font-size: 0.86rem;
}

.owner-notification-modal-counts {
  display: grid;
  gap: 0.65rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.owner-notification-modal-counts li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid rgba(219, 188, 160, 0.3);
  color: #7c6758;
}

.owner-notification-modal-counts strong {
  color: #3d2a1f;
}

.owner-announcement-modal .info-actions {
  flex: 0 0 auto;
  justify-content: flex-end;
  margin: 0;
  padding: 0.75rem 1.5rem 1rem;
  border-top: 1px solid rgba(219, 188, 160, 0.3);
}

.info-modal-close {
  position: absolute;
  top: 1rem;
  right: 1.25rem;
  width: 32px;
  height: 32px;
  padding: 0;
  border: 0;
  color: #94735f;
  background: transparent;
  font-size: 1.5rem;
  cursor: pointer;
}

.info-modal-close:hover {
  color: #3d2a1f;
  transform: scale(1.1);
}

@media (max-width: 480px) {
  .owner-announcement-modal h3 {
    padding-right: 3.25rem;
  }

  .owner-announcement-modal .info-sub,
  .owner-announcement-list {
    padding-right: 1rem;
    padding-left: 1rem;
  }
}
</style>
