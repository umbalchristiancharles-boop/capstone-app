<template>
  <main class="page staff-landing">
    <section class="hero" id="staff-hero">
      <div class="hero-content">
        <div class="hero-left">
          <div class="badge">
            <span class="badge-dot"></span>
            <span>Staff Portal</span>
          </div>
<h1>STAFF LOGIN</h1>
          <p>This is the separate staff landing page. Authorized staff should sign in below.</p>
          <div class="cta-row">
            <button class="btn-login" @click.prevent="goToAdminLogin">
              <span class="icon">🔑</span>
              <span>Staff Login</span>
            </button>
          </div>
        </div>
        <div class="hero-right">
          <img :src="chikintayoImg" alt="Chikintayo" />
        </div>
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

</script>

<style scoped>
.staff-landing {
  --staff-ink: #3d2a1f;
  --staff-muted: #64748b;
  --staff-orange: #ff9f43;
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(255, 159, 67, 0.12), transparent 28%),
    radial-gradient(circle at bottom right, rgba(34, 197, 94, 0.08), transparent 30%),
    linear-gradient(180deg, rgba(251, 247, 244, 0.98), rgba(247, 244, 240, 1));
}

  #staff-hero {
    position: relative;
    min-height: 100vh;
    width: 100%;
    background: transparent;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(2rem, 6vw, 5rem) 1.5rem;
  }

.hero-content {
  width: 100%;
  max-width: 1080px;
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(300px, 1.1fr);
  gap: clamp(2rem, 7vw, 5rem);
  align-items: center;
}

.hero-left h1 {
  margin: 0;
  color: var(--staff-ink);
  font-weight: 800;
  font-family: 'Inter', 'Poppins', sans-serif;
  letter-spacing: 0;
}

.hero-left p {
  color: var(--staff-muted);
  font-size: 15px;
  line-height: 1.6;
  opacity: 1;
}

.btn-login {
  background: linear-gradient(135deg, #fffaf5 0%, #fff 72%);
  color: var(--staff-ink);
  border: 1px solid #f1e5d8;
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
  box-shadow: 0 10px 20px rgba(66, 33, 11, 0.12);
}

.btn-back {
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid #f1e5d8;
  color: var(--staff-ink);
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-back:hover {
  background: #fffaf5;
  transform: translateY(-1px);
}

.hero-left {
  display: flex;
  flex-direction: column;
  gap: 24px;
  justify-content: center;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: var(--dirty-white, #fff4e6);
  color: var(--staff-ink);
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
  font-size: clamp(2.6rem, 5vw, 4rem);
  line-height: 1.1;
  padding-bottom: 0;
}

p {
  font-size: 1.05rem;
  max-width: 440px;
  padding-bottom: 0;
}

.cta-row {
  display: flex;
  gap: 14px;
  margin-top: 4px;
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
  position: relative;
}

.hero-right img {
  max-width: 100%;
  width: min(420px, 100%);
  border-radius: 16px;
  border: 1px solid #f1e5d8;
  box-shadow: 0 20px 40px rgba(66, 33, 11, 0.12);
}

/* Responsive */
@media (max-width: 900px) {
  .hero-content {
    grid-template-columns: 1fr;
    gap: 40px;
    max-width: 500px;
  }

  .hero-left {
    text-align: center;
    align-items: center;
  }

  .cta-row {
    justify-content: center;
    flex-wrap: wrap;
  }

  h1 {
    font-size: 2.5rem;
  }

  p {
    font-size: 1.1rem;
    max-width: none;
  }

  .hero-right img {
    width: 100%;
    max-width: 300px;
  }
}

@media (max-width: 480px) {
  #staff-hero {
    padding: 2rem 1rem 3rem;
  }

  .hero-left {
    gap: 1rem;
  }

  .cta-row,
  .btn-back,
  .btn-login {
    width: 100%;
  }

  .cta-row {
    gap: 0.75rem;
  }

  .btn-back,
  .btn-login {
    justify-content: center;
  }
}
</style>
