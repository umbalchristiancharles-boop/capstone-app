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
.hero {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  align-items: center;
}

.hero::before {
  content: "✻";
  position: absolute;
  top: 24px;
  left: 32px;
  font-size: 42px;
  color: rgba(255, 255, 255, 0.22);
  z-index: 1;
}

.hero::after {
  content: "✻";
  position: absolute;
  bottom: 32px;
  right: 40px;
  font-size: 42px;
  color: rgba(255, 255, 255, 0.22);
  z-index: 1;
}

.hero-left {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 24px;
  justify-content: center;
  padding: 80px 120px 120px 120px;
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
  color: var(--dirty-white, #fff4e6);
  padding-bottom: 16px;
}

p {
  font-size: 1.2rem;
  max-width: 440px;
  color: rgba(255, 244, 230, 0.9);
  padding-bottom: 12px;
}

.cta-row {
  margin-top: 18px;
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.btn-back {
  background: transparent;
  color: rgba(255, 255, 255, 0.8);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 999px;
  padding: 12px 20px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-back:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.5);
  color: white;
}

.btn-login {
  background: var(--dirty-white, #fff4e6);
  color: var(--text-dark, #42210b);
  border-radius: 999px;
  padding: 12px 28px;
  font-size: 0.98rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border: none;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2), 0 0 20px rgba(255, 165, 0, 0.4);
  transition: all 0.15s ease;
  position: relative;
}

.btn-login::before {
  content: "";
  position: absolute;
  top: -5px;
  left: -5px;
  right: -5px;
  bottom: -5px;
  background: radial-gradient(circle, rgba(255, 165, 0, 0.3) 0%, transparent 70%);
  border-radius: 999px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(0, 0, 0, 0.26), 0 0 30px rgba(255, 165, 0, 0.6);
}

.btn-login:hover::before {
  opacity: 1;
}

@keyframes hero-glow {
  0%, 100% {
    box-shadow:
      0 20px 40px rgba(0, 0, 0, 0.3),
      0 0 20px rgba(255, 107, 28, 0.4),
      0 0 40px rgba(255, 107, 28, 0.25),
      0 0 60px rgba(255, 107, 28, 0.15);
    filter: drop-shadow(0 0 15px rgba(255, 107, 28, 0.3));
  }
  50% {
    box-shadow:
      0 20px 40px rgba(0, 0, 0, 0.3),
      0 0 30px rgba(255, 107, 28, 0.55),
      0 0 50px rgba(255, 107, 28, 0.35),
      0 0 80px rgba(255, 107, 28, 0.2);
    filter: drop-shadow(0 0 25px rgba(255, 107, 28, 0.45));
  }
}

.btn-login .icon {
  font-size: 1.25rem;
}

.hero-right {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px;
  position: relative;
  z-index: 2;
}

.hero-right img {
  max-width: 100%;
  width: 100%;
  height: auto;
  object-fit: contain;
  border-radius: 28px;
  box-shadow:
    0 20px 40px rgba(0, 0, 0, 0.3),
    0 0 20px rgba(255, 107, 28, 0.4),
    0 0 40px rgba(255, 107, 28, 0.25),
    0 0 60px rgba(255, 107, 28, 0.15);
  filter: drop-shadow(0 0 15px rgba(255, 107, 28, 0.3));
  animation: hero-glow 3s ease-in-out infinite;
}

/* Responsive */
@media (max-width: 900px) {
  .hero {
    grid-template-columns: 1fr;
    min-height: auto;
    padding: 40px 20px;
    gap: 40px;
  }

  .hero-left {
    padding: 40px 20px;
    text-align: center;
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
