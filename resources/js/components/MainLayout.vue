<template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <div class="admin-page" :class="{ 'admin-page--wider': fullWidth }">
      <section class="admin-layout" :class="{ 'admin-layout--wider': fullWidth }">
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
import { onMounted } from 'vue'
import { useTheme } from '../composables/useTheme'

defineProps({
  fullWidth: { type: Boolean, default: false }
})

// Ensure theme is initialized for all pages using MainLayout
const { initializeTheme } = useTheme()
onMounted(() => {
  try { initializeTheme() } catch (e) { /* no-op */ }
})
</script>

<style scoped>
@import '../css/adminpanel.css';

/* Wider mode - max-w-5xl with px-6 sm:px-8 lg:px-10 */
.admin-page--wider {
  max-width: 100%;
  padding: 0;
}

.admin-layout--wider {
  max-width: 64rem;
  width: 100%;
  min-height: 100vh;
  border-radius: 0;
  padding: 1.5rem;
  gap: 1.5rem;
  margin: 0 auto;
}

.admin-layout--wider .admin-main {
  width: 100%;
}

/* Responsive breakpoints with px-6 sm:px-8 lg:px-10 equivalent */
@media (min-width: 640px) {
  .admin-layout--wider {
    padding: 1.5rem 1.5rem;
  }
}

@media (min-width: 1024px) {
  .admin-layout--wider {
    padding: 1.5rem 2.5rem;
  }
}
</style>
