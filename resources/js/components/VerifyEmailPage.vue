<template>
  <div class="verify-email-page">
    <div class="card">
      <h2>Verify Your Email</h2>
      <p>{{ isPostLoginFlow ? 'Please verify your email to continue.' : 'Please provide and verify an email address so we can confirm your account.' }}</p>

      <div v-if="error" class="error-text">{{ error }}</div>
      <div v-if="success" class="success-text">{{ success }}</div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="email" type="email" class="form-input" placeholder="your@email.com" :readonly="isPostLoginFlow" />
      </div>

      <div class="form-group" style="display:flex; gap:0.5rem;">
        <button class="btn-secondary" @click="sendCode" :disabled="isSending">{{ isSending ? 'Sending...' : 'Send Code' }}</button>
        <small style="align-self:center; color:#6b6b6b;">A 6-digit code will be sent to this email.</small>
      </div>

      <div v-if="codeSent" class="form-group">
        <label>Verification Code</label>
        <input v-model="code" type="text" class="form-input" placeholder="Enter 6-digit code" />
        <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
          <button class="btn-primary" @click="confirm" :disabled="isVerifying">{{ isVerifying ? 'Verifying...' : 'Verify Email' }}</button>
          <button class="btn-secondary" @click="resend" :disabled="isSending">Resend</button>
        </div>
      </div>

      <div v-if="!isPostLoginFlow" style="margin-top:1rem; display:flex; gap:0.5rem; justify-content:flex-end;">
        <button class="btn-secondary" @click="backToLogin">Back to Login</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('')
const code = ref('')
const codeSent = ref(false)
const isSending = ref(false)
const isVerifying = ref(false)
const error = ref('')
const success = ref('')
const isPostLoginFlow = ref(false)
const userRole = ref('')

onMounted(async () => {
  // Check if user is coming from post-login verification
  try {
    const pendingEmail = localStorage.getItem('pending_email')
    if (pendingEmail) {
      email.value = pendingEmail
      isPostLoginFlow.value = true
      // Clear the stored email
      try {
        localStorage.removeItem('pending_email')
      } catch (e) {}
    } else {
      // Try to get from authenticated user
      const res = await axios.get('/api/me', { withCredentials: true })
      const u = res.data.user
      if (u && u.email) {
        email.value = u.email
        isPostLoginFlow.value = true
        userRole.value = u.role
      }
    }
  } catch (e) {
    // ignore
  }
})

function backToLogin() {
  router.push('/staff-landing')
}

async function sendCode() {
  error.value = ''
  success.value = ''
  if (!email.value || email.value.trim() === '') {
    error.value = 'Please enter an email address.'
    return
  }
  isSending.value = true
  try {
    await axios.post('/api/auth/send-verification', { email: email.value }, { withCredentials: true })
    codeSent.value = true
    success.value = 'Verification code sent. Check your inbox.'
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to send verification code.'
  } finally {
    isSending.value = false
  }
}

async function resend() {
  await sendCode()
}

async function confirm() {
  error.value = ''
  success.value = ''
  if (!code.value || code.value.trim().length !== 6) {
    error.value = 'Please enter the 6-digit code.'
    return
  }
  isVerifying.value = true
  try {
    await axios.post('/api/auth/confirm-email', { email: email.value, code: code.value }, { withCredentials: true })
    success.value = 'Email verified successfully. Redirecting...'
    
    // Determine redirect path
    let redirectPath = '/admin-panel'
    if (isPostLoginFlow.value) {
      // Try to get redirect path from API
      try {
        const meRes = await axios.get('/api/me', { withCredentials: true })
        const u = meRes.data.user
        // Determine role-based redirect (simplified version of backend logic)
        const role = (u?.role || '').toUpperCase()
        if (role === 'OWNER') redirectPath = '/owner-panel'
        else if (role === 'ADMIN') redirectPath = '/admin-panel'
        else if (role === 'HR') redirectPath = '/hr-panel'
        else if (role === 'STAFF') redirectPath = '/staff-panel'
        else redirectPath = '/staff-landing'
      } catch (e) {
        redirectPath = '/staff-landing'
      }
    }
    
    setTimeout(() => router.push(redirectPath), 900)
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to verify email.'
  } finally {
    isVerifying.value = false
  }
}
</script>

<style scoped>
.verify-email-page { min-height: 100vh; display:flex; align-items:center; justify-content:center; background: linear-gradient(120deg, #ff9a4a 0%, #ff6a3d 100%); padding:1rem }
.card { width:100%; max-width:520px; background:#fff; padding:1.25rem; border-radius:12px }
.form-group { margin-bottom:1rem }
.form-input { width:100%; padding:0.7rem; border-radius:8px; border:1px solid #eee }
.error-text { color:#c23b3b; background:#fdecec; padding:0.6rem; border-radius:8px; margin-bottom:0.5rem }
.success-text { color:#1c7c54; background:#e8f8f0; padding:0.6rem; border-radius:8px; margin-bottom:0.5rem }
.btn-primary { background: linear-gradient(135deg,#ff9a4a,#ff6a3d); color:#fff; border:none; padding:0.6rem 1rem; border-radius:8px }
.btn-secondary { background:#fff; color:#ff6a3d; border:2px solid #ff9a4a; padding:0.6rem 1rem; border-radius:8px }
</style>
