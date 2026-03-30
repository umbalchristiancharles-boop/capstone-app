<template>
  <transition name="fade">
    <div v-if="show" class="loading-overlay">
      <div class="logo-loading-box">
        <img :src="resolvedLogoSrc" alt="Chikin Tayo" class="logo-loading-img" />
        <p>{{ text }}</p>
      </div>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  text: {
    type: String,
    default: 'Loading...',
  },
  logoSrc: {
    type: String,
    default: '',
  },
})

const resolvedLogoSrc = props.logoSrc || new URL('../assets/chikinlogo.png', import.meta.url).href
</script>

<style scoped>
.loading-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.45);
  -webkit-backdrop-filter: blur(4px);
  backdrop-filter: blur(4px);
  z-index: 9999;
}

.loading-overlay .logo-loading-box {
  background: rgba(255, 255, 255, 0.6);
  border-radius: 12px;
  padding: 18px 22px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
  text-align: center;
  min-width: 200px;
  backdrop-filter: blur(2px);
}

.loading-overlay .logo-loading-img {
  width: 96px;
  height: auto;
  margin-bottom: 8px;
  display: block;
}

.loading-overlay .logo-loading-box p {
  margin: 0;
  font-size: 0.95rem;
  color: var(--text-dark);
  font-weight: 500;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>