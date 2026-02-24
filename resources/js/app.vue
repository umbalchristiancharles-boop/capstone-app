<template>
  <div id="app">
    <router-view v-slot="{ Component }">
      <transition :name="transitionName" mode="out-in">
        <div class="route-view">
          <component :is="Component" />
        </div>
      </transition>
    </router-view>
    <div id="page-blur" aria-hidden="true"></div>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      transitionName: 'route-fade'
    }
  },
  mounted() {
    try {
      if (sessionStorage.getItem('suppressRouteTransition') === '1') {
        sessionStorage.removeItem('suppressRouteTransition')
        this.transitionName = ''
        setTimeout(() => {
          this.transitionName = 'route-fade'
        }, 50)
      }
    } catch (e) {}
  }
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

/* Route transitions */
.route-fade-enter-active,
.route-fade-leave-active {
  transition: opacity 260ms ease, transform 260ms ease;
}

.route-fade-enter-from,
.route-fade-leave-to {
  opacity: 0;
  transform: translateY(6px);
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
