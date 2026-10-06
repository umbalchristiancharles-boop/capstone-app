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
  flex: 0 0 58px;
  align-items: flex-start;
  justify-content: flex-start;
  width: 100%;
  min-width: 0;
  height: 58px;
  margin-bottom: 0.2rem;
  padding: 0;
  box-sizing: border-box;
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
