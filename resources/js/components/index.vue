<template>
  <main class="page">
    <!-- CHIKIN TAYO LOADING OVERLAY PAG CLICK NG LOGIN -->
    <transition name="fade">
      <template>
  <div class="min-h-screen bg-gradient-to-b from-[#FF9A4A] to-[#FF6A3D]">
    <!-- buong admin layout mo dito -->
  </div>
</template>

    </transition>

    <section class="hero">
      <!-- LEFT content -->
      <div class="hero-left">
        <div class="badge">
          <span class="badge-dot"></span>
          <span>Admin Control Center</span>
        </div>

        <h1>
          <span class="highlight-korean">Your fun Korean</span>
          <span>Bakery Admin Bake Stop</span>
        </h1>

        <p class="subheading">
          Manage cakes, breads, and holiday specials from a single, playful
          dashboard styled just like the shop front.
        </p>

        <p class="admin-tagline">
          For <strong>authorized staff</strong> access only.
        </p>

        <div class="cta-row">
          <!-- ADMIN LOGIN ONLY -->
          <RouterLink
            to=""
            class="btn-login"
            @click.prevent="goToAdminLogin"
          >
            <span class="icon">🔑</span>
            <span>Login to Admin Panel</span>
          </RouterLink>

          <span class="note">
            No sign up. Credentials are provided by the system administrator.
          </span>
        </div>
      </div>

      <!-- RIGHT side: big logo only -->
      <div class="hero-right">
        <div class="logo-wrapper">
          <img :src="chikintayoImg" alt="Chikintayo" />
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'

const router = useRouter()
const showLoginLoader = ref(false)

const chikintayoImg = new URL('../assets/chikintayo.jpg', import.meta.url).href
const mrLoaderImg   = new URL('../assets/chikinlogo.png', import.meta.url).href

function goToAdminLogin() {
  if (showLoginLoader.value) return
  showLoginLoader.value = true

  setTimeout(() => {
    router.push('/admin-login')
  }, 1000)
}
</script>

<style src="../css/index.css"></style>
