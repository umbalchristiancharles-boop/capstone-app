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

function handleCompleted() {
  // Redirect to login or dashboard after password change
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
  background: linear-gradient(120deg, #ff9a4a 0%, #ff6a3d 100%);
}
</style>
