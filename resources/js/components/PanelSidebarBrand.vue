<template>
  <div class="panel-sidebar-brand">
    <img :src="logoSrc" alt="Chikin Tayo" class="panel-sidebar-brand__logo" />
  </div>
</template>

<script setup>
const logoSrc = new URL('../assets/chikinlogo.png', import.meta.url).href
</script>

<style scoped>
.panel-sidebar-brand {
  display: flex;
  flex: 0 0 74px;
  align-items: center;
  justify-content: center;
  width: 100%;
  min-width: 0;
  height: 74px;
  margin-bottom: 0.5rem;
  padding: 0.75rem;
  box-sizing: border-box;
  border: 1px solid #e8c8ad;
  border-radius: 0.875rem;
  background: linear-gradient(145deg, #fffaf5, #f7ece2);
  box-shadow: 0 6px 14px rgba(138, 79, 47, 0.1);
}

.panel-sidebar-brand__logo {
  display: block;
  width: 100%;
  max-width: 100px;
  max-height: 36px;
  height: auto;
  min-width: 0;
  box-sizing: border-box;
  object-fit: contain;
}
</style>
