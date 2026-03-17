<template>
  <main class="page staff-landing">
    <section class="hero" id="staff-hero">
      <div class="hero-left">
<h1>STAFF LOGIN</h1>
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
  #staff-hero {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    min-height: 100vh;
background: linear-gradient(135deg, #0066FF 0%, #3B82F6 55%, #FACC15 100%);
  color: white;
}

.hero-left h1 {
  color: white;
  font-weight: 800;
  font-family: 'Inter', 'Poppins', sans-serif;
  letter-spacing: -0.5px;
}

.hero-left p {
  color: rgba(255,255,255,0.9);
  font-size: 15px;
  line-height: 1.6;
  opacity: 0.9;
}

.btn-login {
  background: white;
  color: #0066FF;
  padding: 12px 22px;
  border-radius: 8px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.25s ease;
}

.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.btn-back {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.4);
  color: white;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-back:hover {
  background: rgba(255,255,255,0.15);
  transform: translateY(-1px);
}

.hero {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  align-items: center;
  padding: 80px 40px;
  position: relative;
  z-index: 2;
}



.hero-left {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 20px;
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 24px;
  justify-content: center;
  padding: 60px 40px;
  max-width: 500px;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: var(--dirty-white, #fff4e6);
  color: var(--text-dark, #42210b);
  padding: 6px 16px;
  border-radius: 999px;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  width: fit-content;
}

.badge-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: var(--accent-pink, #ff9fbf);
}

h1 {
  font-size: clamp(2.8rem, 4vw, 3.6rem);
  line-height: 1.1;
  padding-bottom: 16px;
}

p {
  font-size: 1.2rem;
  max-width: 440px;
  padding-bottom: 12px;
}

.cta-row {
  display: flex;
  gap: 14px;
  margin-top: 20px;
}



.btn-login {
  border-radius: 999px;
  padding: 12px 28px;
  font-size: 0.98rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border: none;
  transition: all 0.15s ease;
  position: relative;
}

.btn-login .icon {
  font-size: 1.25rem;
}

.hero-right {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  position: relative;
  z-index: 2;
}

.hero-right img {
  max-width: 420px;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* Responsive */
@media (max-width: 900px) {
  .hero {
    grid-template-columns: 1fr;
    min-height: 100vh;
    padding: 40px 20px;
    gap: 40px;
  }

  .hero-left {
    margin: 0 auto;
    padding: 40px 20px;
    text-align: center;
    max-width: 400px;
  }

  h1 {
    font-size: 2.5rem;
  }

  p {
    font-size: 1.1rem;
    max-width: none;
  }

  .hero-right {
    padding: 20px;
  }

  .hero-right img {
    width: 100%;
    max-width: none;
  }
}
</style>
