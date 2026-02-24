<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT: PROFILE COLUMN -->
        <aside class="admin-profile-column">
          <div v-if="userProfile" class="admin-card admin-card--stacked">
            <div class="admin-card__header admin-card__header--stacked">
              <label class="admin-avatar admin-avatar--photo avatar-upload">
                <img v-if="userProfile.avatarUrl" :src="userProfile.avatarUrl" alt="Profile picture" class="avatar-img" />
                <div v-else class="avatar-placeholder"><span class="avatar-initials">CT</span></div>
              </label>
              <div class="admin-header-text admin-admin-header-text--center">
                <div class="admin-label">Account</div>
                <div class="admin-name">{{ userProfile.fullName || 'User' }}</div>
                <div class="admin-role">{{ userProfile.role || 'ROLE' }}</div>
              </div>
            </div>
            <div class="admin-card__body admin-card__body--stacked">
              <div class="admin-id-block admin-id-block--center">
                <span class="admin-id-label">Account I.D: </span>
                <span class="admin-id-value">&nbsp;{{ userProfile.accountId || 'id0001' }}</span>
              </div>
            </div>
            <div class="admin-card__footer admin-card__footer--stacked">
              <slot name="profileFooter"></slot>
              <div class="admin-actions-row">
                <button class="logout-btn logout-btn--center" @click="$emit('logout')">Logout</button>
              </div>
            </div>
          </div>
        </aside>
        <!-- MIDDLE: MAIN DASHBOARD -->
        <main class="admin-main">
          <header class="admin-main-header">
            <div class="admin-main-header-top">
              <div>
                <h1>{{ panelTitle }}</h1>
                <p>{{ panelDescription }}</p>
              </div>
              <slot name="headerActions"></slot>
            </div>
          </header>
          <slot name="main"></slot>
        </main>
        <!-- RIGHT: SIDE PANELS -->
        <aside class="admin-side">
          <slot name="side"></slot>
        </aside>
      </section>
    </div>
  </div>
</template>

<script setup>
defineProps({
  userProfile: { type: Object, default: () => ({}) },
  panelTitle: { type: String, required: true },
  panelDescription: { type: String, required: true }
})
</script>

<style scoped>
@import '../css/adminpanel.css';
</style>
