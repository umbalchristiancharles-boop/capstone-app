<template>
  <div class="forgot-bg">
    <div class="container">
      <!-- Header -->
      <div class="text-center" style="margin-bottom: 2rem;">
        <div class="rounded-xl shadow" style="width:64px;height:64px;background:#fff7ed;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
          <span style="font-size:2rem;color:#ea580c;">🔑</span>
        </div>
        <h1 class="text-orange-700" style="font-size:1.5rem;font-weight:bold;margin-bottom:0.5rem;">
          Forgot Password?
        </h1>
        <p class="text-gray-400 text-xs">
          Enter your email and we'll send you a reset link.
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleForgot">
        <label for="email">Email Address</label>
        <input
          v-model="form.email"
          id="email"
          type="email"
          required
          placeholder="your.email@chikintayo.com"
        />

        <button type="submit" :disabled="loading">
          <span v-if="loading">Sending link...</span>
          <span v-else>Send Reset Link</span>
        </button>
      </form>

      <!-- Back to Login -->
      <div class="mt-8 pt-6 border-t text-center">
        <button
          type="button"
          @click="router.push('/admin/login')"
          class="text-orange-600 font-medium"
          style="background:none;border:none;cursor:pointer;padding:0;"
        >
          ← Back to Login
        </button>
        <p class="text-gray-400 text-xs" style="margin-top:0.5rem;">© 2026 Chikin Tayo. All rights reserved.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({
  email: ''
})
const loading = ref(false)

const handleForgot = async () => {
  loading.value = true
  try {
    await axios.post('/api/forgot-password', form)
    alert('Reset link sent to your email!')
    router.push('/admin/login')
  } catch (error) {
    alert('Email not found. Check and try again.')
  } finally {
    loading.value = false
  }
}
</script>

<style>
.forgot-bg {
  min-height: 100vh;
  background: linear-gradient(135deg, #f97316 0%, #c2410c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>