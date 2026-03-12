<template>
  <div class="change-password-page">
    <ForcePasswordChangeModal
      :show="true"
      :username="username"
      :defaultPassword="defaultPassword"
      @completed="handleCompleted"
      @cancel="handleCancel"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ForcePasswordChangeModal from './ForcePasswordChangeModal.vue'
import axios from 'axios'

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

async function handleCompleted() {
  // After password change, check whether user has an email; if not, go to verification flow
  try {
    const res = await axios.get('/api/me', { withCredentials: true })
    const u = res.data.user
    if (!u || !u.email) {
      router.push('/verify-email')
      return
    }
  } catch (e) {
    // ignore and fall through
  }
  router.push('/admin-login')
}

function handleCancel() {
  router.push('/admin-login')
}
</script>

<style scoped>
.change-password-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(120deg, #ff9a4a 0%, #ff6a3d 100%);
}
</style>
