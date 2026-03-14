<template>
  <main class="page staff-landing">
    <section class="hero" id="staff-hero">
      <div class="hero-left">
        <h1>Staff Landing</h1>
        <p>This is the separate staff landing page. Authorized staff should sign in below.</p>
        <div class="cta-row">
          <button class="btn-back" @click.prevent="goToCustomerLanding">← Back</button>
          <button class="btn-login" @click.prevent="goToAdminLogin">
            <span class="icon">🔑</span>
            <span>Staff Login</span>
          </button>
        </div>
      </div>
      <div class="hero-right">
        <img :src="chikintayoImg" alt="Chikintayo" />
      </div>
    </section>
  </main>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()
const chikintayoImg = new URL('../assets/chikintayo.jpg', import.meta.url).href
function goToAdminLogin() {
  router.push('/admin-login')
}

function goToCustomerLanding() {
  router.push('/')
}
</script>

<style scoped>
.staff-landing .hero { display:flex; gap:2rem; align-items:center; padding:3rem; }
.staff-landing .hero-right img { max-width:320px; width:100%; height:auto; }
.btn-login { background:#f59e42; border:none; padding:0.6rem 1rem; color:#fff; cursor:pointer; }
</style>
