<template>
  <div class="min-h-screen bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center p-4">
    <div class="bg-white/20 backdrop-blur-xl shadow-2xl rounded-3xl p-8 w-full max-w-md border border-white/30">
      <!-- Header -->
      <div class="flex flex-col items-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg mb-4">
          <span class="text-2xl font-bold text-orange-900">🔑</span>
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-white to-gray-200 bg-clip-text text-transparent drop-shadow-lg">
          Forgot Password?
        </h1>
        <p class="text-white/80 text-sm mt-2 text-center">
          Enter your email and we'll send you a reset link.
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleForgot" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Email Address</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent transition-all duration-300 backdrop-blur-sm"
            placeholder="your.email@chikintayo.com"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading" class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.25"/>
              <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            Sending link...
          </span>
          <span v-else>Send Reset Link</span>
        </button>
      </form>

      <!-- Back to Login -->
      <div class="mt-8 pt-6 border-t border-white/20 text-center">
        <button
          @click="$router.push('/admin/login')"
          class="text-white/90 hover:text-white font-medium flex items-center justify-center mx-auto text-sm transition-colors duration-300"
        >
          ← Back to Login
        </button>
        <p class="text-white/70 text-xs mt-2">© 2026 Chikin Tayo. All rights reserved.</p>
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
    // Call Laravel API: POST /api/forgot-password
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
