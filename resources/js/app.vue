<template>
  <div id="app">
    <router-view></router-view>
    <div id="page-blur" aria-hidden="true"></div>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
}

body {
  font-family: 'Inter', 'Segoe UI', sans-serif;
  background: linear-gradient(180deg, #FF9A4A 0%, #FF6A3D 100%);
  margin: 0;
  min-height: 100%;
}

#app {
  width: 100%;
  min-height: 100vh;
}

/* Global page blur layer */
#page-blur {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 40;
  opacity: 0;
  transition: opacity 600ms ease;
  backdrop-filter: blur(0px);
  -webkit-backdrop-filter: blur(0px);
}

#page-blur.active {
  opacity: 1;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
</style>
