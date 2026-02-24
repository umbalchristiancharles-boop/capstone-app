<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page">
      <section class="admin-layout">
        <!-- LEFT: PROFILE COLUMN -->
        <aside class="admin-profile-column">
          <slot name="profile"></slot>
        </aside>
        <!-- MIDDLE: MAIN DASHBOARD -->
        <main class="admin-main">
          <header class="admin-main-header">
            <div class="admin-main-header-top">
              <slot name="header"></slot>
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
// No props needed, all content via slots
</script>

<style scoped>
@import '../css/adminpanel.css';
</style>
