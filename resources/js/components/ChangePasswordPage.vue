<template>
  <div class="change-password-page">
    <div class="change-password-panel">
      <ForcePasswordChangeModal
        :show="true"
        :username="username"
        :defaultPassword="defaultPassword"
        @completed="handleCompleted"
        @cancel="handleCancel"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ForcePasswordChangeModal from './ForcePasswordChangeModal.vue'

const router = useRouter()
const route = useRoute()

// Try to get username from localStorage or fallback to blank
const username = ref(localStorage.getItem('pending_username') || '')
const defaultPassword = ref('')

onMounted(() => {
  // Only reload once per redirection using a sessionStorage flag
  if (route.path === '/change-password') {
    if (!sessionStorage.getItem('changePasswordReloaded')) {
      sessionStorage.setItem('changePasswordReloaded', '1');
      window.location.reload();
    } else {
      sessionStorage.removeItem('changePasswordReloaded');
    }
  }
})

function handleCompleted(payload) {
  // If server sent verification code, send user to verify page first
  if (payload && payload.verification_sent) {
    router.push('/verify-email')
    return
  }

  // Otherwise redirect to dashboard
  router.push('/staff-landing')
}

function handleCancel() {
  router.push('/staff-landing')
}
</script>

<style scoped>
.change-password-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-main, radial-gradient(circle at center, #FFFFFF 0%, #FCFCFC 40%, #EFEFEF 100%));
  color: var(--text-primary, #42210b);
  font-family: 'Inter', 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.change-password-panel {
  width: 100%;
  max-width: 560px;
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255,255,255,0.14);
  border-radius: 16px;
  padding: 36px 28px;
  box-shadow: 0 12px 30px rgba(16,24,40,0.06);
  box-sizing: border-box;
}
</style>
