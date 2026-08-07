<template>
  <div class="verify-email-page">
      <div class="card">
        <div class="card-header">
          <div class="brand-mark">🔒</div>
          <div>
            <h2>Verify Your Email</h2>
            <p class="muted">{{ isPostLoginFlow ? 'Please verify your email to continue.' : 'Provide an email and verify it so we can confirm your account.' }}</p>
          </div>
        </div>

      <div v-if="error" class="error-text">{{ error }}</div>
      <div v-if="success" class="success-text">{{ success }}</div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="email" type="email" class="form-input" placeholder="your@email.com" :readonly="isPostLoginFlow" />
      </div>

      <div class="form-group horizontal-note">
        <button class="btn-primary" @click="sendCode" :disabled="isSending">{{ isSending ? 'Sending...' : 'Send Code' }}</button>
        <small class="note">A 6-digit code will be sent to this email.</small>
      </div>

      <div v-if="codeSent" class="form-group">
        <label>Verification Code</label>
        <input v-model="code" type="text" class="form-input" placeholder="Enter 6-digit code" />
        <div class="verify-actions">
          <button class="btn-primary" @click="confirm" :disabled="isVerifying">{{ isVerifying ? 'Verifying...' : 'Verify Email' }}</button>
          <button class="btn-ghost" @click="resend" :disabled="isSending">Resend</button>
        </div>
      </div>

      <div v-if="!isPostLoginFlow" class="footer-actions">
        <button class="btn-ghost" @click="backToLogin">Back to Login</button>
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
.verify-email-page { min-height: 100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; background:
  radial-gradient(circle at top left, rgba(255, 106, 61, 0.08), transparent 28%),
  radial-gradient(circle at top right, rgba(251, 191, 36, 0.08), transparent 24%),
  linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(241, 245, 249, 1)); }
.card { width:100%; max-width:540px; background:linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.98)); padding:1.5rem; border-radius:16px; box-shadow: 0 10px 30px rgba(16,24,40,0.12); border: 1px solid rgba(0,0,0,0.04) }
.card-header { display:flex; gap:0.75rem; align-items:center; margin-bottom:0.5rem }
.brand-mark { width:44px; height:44px; display:flex; align-items:center; justify-content:center; font-size:22px; background: linear-gradient(135deg,#ff6a3d,#f59e0b); color:#fff; border-radius:10px }
.muted { color:#6b7280; margin:0 }
.form-group { margin-bottom:1rem }
.form-input { width:100%; padding:0.75rem; border-radius:10px; border:1px solid rgba(15,23,42,0.06); background:#fff }
.error-text { color:#9b1c1c; background:#fff5f5; padding:0.6rem; border-radius:8px; margin-bottom:0.5rem }
.success-text { color:#065f46; background:#ecfdf5; padding:0.6rem; border-radius:8px; margin-bottom:0.5rem }
.btn-primary { background: linear-gradient(90deg,#ff6a3d,#f59e0b); color:#fff; border:none; padding:0.7rem 1.1rem; border-radius:10px; box-shadow: 0 6px 18px rgba(255,106,61,0.18) }
.btn-ghost { background:transparent; color:#374151; border:1px solid rgba(15,23,42,0.06); padding:0.6rem 1rem; border-radius:10px }
.horizontal-note { display:flex; align-items:center; gap:0.75rem }
.note { color:#6b7280 }
.verify-actions { margin-top:0.5rem; display:flex; gap:0.6rem }
.footer-actions { margin-top:1rem; display:flex; gap:0.5rem; justify-content:flex-end }
</style>
