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
.verify-email-page {
  --verify-ink: #3d2a1f;
  --verify-muted: #64748b;
  --verify-orange: #ff9f43;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(1rem, 4vw, 2rem);
  background:
    radial-gradient(circle at top left, rgba(255, 159, 67, 0.12), transparent 28%),
    radial-gradient(circle at bottom right, rgba(34, 197, 94, 0.08), transparent 30%),
    linear-gradient(180deg, rgba(251, 247, 244, 0.98), rgba(247, 244, 240, 1));
}

.card {
  width: 100%;
  max-width: 540px;
  padding: clamp(1.25rem, 4vw, 2rem);
  background: linear-gradient(135deg, #fffaf5 0%, #ffffff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 16px;
  box-shadow: 0 18px 46px rgba(66, 33, 11, 0.12);
}

.card-header {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  margin-bottom: 1.25rem;
}

.brand-mark {
  width: 44px;
  height: 44px;
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 21px;
  background: linear-gradient(135deg, #ff9f43, #f97316);
  color: #fff;
  border-radius: 10px;
}

.card h2 {
  margin: 0 0 0.25rem;
  color: var(--verify-ink);
  font-size: clamp(1.35rem, 3vw, 1.65rem);
}

.muted {
  color: var(--verify-muted);
  margin: 0;
  line-height: 1.45;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.35rem;
  color: var(--verify-ink);
  font-size: 0.82rem;
  font-weight: 600;
}

.form-input {
  width: 100%;
  box-sizing: border-box;
  padding: 0.75rem;
  color: var(--verify-ink);
  background: #fff;
  border: 1px solid #f1e5d8;
  border-radius: 8px;
  outline: none;
}

.form-input:focus {
  border-color: var(--verify-orange);
  box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.14);
}

.error-text,
.success-text {
  padding: 0.7rem 0.8rem;
  border-radius: 8px;
  margin-bottom: 0.75rem;
}

.error-text { color: #9b1c1c; background: #fff5f5; }
.success-text { color: #065f46; background: #ecfdf5; }

.btn-primary {
  background: linear-gradient(90deg, #ff6a3d, #f59e0b);
  color: #fff;
  border: none;
  padding: 0.7rem 1.1rem;
  border-radius: 8px;
  box-shadow: 0 6px 18px rgba(255, 106, 61, 0.18);
  cursor: pointer;
  font-weight: 700;
}

.btn-ghost {
  background: #fffaf5;
  color: var(--verify-ink);
  border: 1px solid #ead2bd;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.horizontal-note {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.note { color: var(--verify-muted); }

.verify-actions {
  margin-top: 0.75rem;
  display: flex;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.footer-actions {
  margin-top: 1.25rem;
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

@media (max-width: 520px) {
  .horizontal-note {
    align-items: stretch;
    flex-direction: column;
  }

  .horizontal-note .btn-primary,
  .verify-actions .btn-primary,
  .verify-actions .btn-ghost,
  .footer-actions .btn-ghost {
    width: 100%;
  }

  .footer-actions {
    justify-content: stretch;
  }
}
</style>
