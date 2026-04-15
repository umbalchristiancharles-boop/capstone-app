<template>
  <div v-if="show" class="force-modal-overlay">
    <div class="force-modal-card">
      <div class="force-modal-header">
        <h3>Account security requires password update</h3>
        <p>First time login detected. Please change your password to continue.</p>
      </div>

      <div class="force-modal-body">
        <div class="form-group">
          <label>Username</label>
          <input
            type="text"
            :value="username"
            readonly
            class="form-input read-only"
            autocomplete="username"
          />
          <small class="hint">Your account username</small>
        </div>

        <div class="form-group">
          <label>Current Password *</label>
          <div class="password-input-wrapper">
            <input
              v-model="currentPassword"
              :type="showCurrentPassword ? 'text' : 'password'"
              class="form-input"
              placeholder="Enter your current password"
              autocomplete="current-password"
            />
            <button type="button" class="password-toggle" @click="showCurrentPassword = !showCurrentPassword" title="Toggle password visibility">
              <svg v-if="!showCurrentPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m2.46 9.15-.83-1.16c-1.47-2.05-1.47-4.99 0-7.04a9.23 9.23 0 0 1 13.77 0l.83 1.16"/>
                <circle cx="12" cy="12" r="3"/>
                <path d="m21.54 14.85.83 1.16c1.47 2.05 1.47 4.99 0 7.04a9.23 9.23 0 0 1-13.77 0l-.83-1.16"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <small class="hint">Enter the password you received from your administrator</small>
        </div>

        <div class="form-group">
          <label>New Password</label>
          <div class="password-input-wrapper">
            <input
              v-model="newPassword"
              :type="showNewPassword ? 'text' : 'password'"
              class="form-input"
              placeholder="Enter a strong password"
              @input="evaluateStrength"
              autocomplete="new-password"
            />
            <button type="button" class="password-toggle" @click="showNewPassword = !showNewPassword" title="Toggle password visibility">
              <svg v-if="!showNewPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m2.46 9.15-.83-1.16c-1.47-2.05-1.47-4.99 0-7.04a9.23 9.23 0 0 1 13.77 0l.83 1.16"/>
                <circle cx="12" cy="12" r="3"/>
                <path d="m21.54 14.85.83 1.16c1.47 2.05 1.47 4.99 0 7.04a9.23 9.23 0 0 1-13.77 0l-.83-1.16"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label>Confirm New Password</label>
          <div class="password-input-wrapper">
            <input
              v-model="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="form-input"
              placeholder="Re-enter new password"
              @input="evaluateStrength"
              autocomplete="new-password"
            />
            <button type="button" class="password-toggle" @click="showConfirmPassword = !showConfirmPassword" title="Toggle password visibility">
              <svg v-if="!showConfirmPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m2.46 9.15-.83-1.16c-1.47-2.05-1.47-4.99 0-7.04a9.23 9.23 0 0 1 13.77 0l.83 1.16"/>
                <circle cx="12" cy="12" r="3"/>
                <path d="m21.54 14.85.83 1.16c1.47 2.05 1.47 4.99 0 7.04a9.23 9.23 0 0 1-13.77 0l-.83-1.16"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="strength-meter">
          <div class="strength-bar">
            <span :style="{ width: strengthPercent + '%' }" :class="strengthClass"></span>
          </div>
          <div class="strength-label" :class="strengthClass">{{ strengthLabel }}</div>
        </div>

        <ul class="criteria-list">
          <li :class="{ ok: criteria.length }">✔ At least 8 characters</li>
          <li :class="{ ok: criteria.upper }">✔ One uppercase letter</li>
          <li :class="{ ok: criteria.lower }">✔ One lowercase letter</li>
          <li :class="{ ok: criteria.number }">✔ One number</li>
          <li :class="{ ok: criteria.special }">✔ One special (!@#$%^&*)</li>
        </ul>

        <div v-if="error" class="error-text">{{ error }}</div>
        <div v-if="success" class="success-text">{{ success }}</div>

        <!-- Email verification is handled on a separate page after password change -->
      </div>

      <div class="force-modal-footer">
        <button class="btn-secondary" @click="cancel" type="button">
          Back to Login
        </button>
        <button class="btn-primary" :disabled="isSubmitting" @click="submit" type="button">
          {{ isSubmitting ? 'Updating...' : 'Update Password' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: { type: Boolean, default: false },
  username: { type: String, default: '' },
  defaultPassword: { type: String, default: '' }
})

const emit = defineEmits(['completed', 'cancel'])

const newPassword = ref('')
const confirmPassword = ref('')
const currentPassword = ref('')
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const showCurrentPassword = ref(false)
const error = ref('')
const success = ref('')
const isSubmitting = ref(false)

function cancel() {
  emit('cancel')
}

const criteria = ref({
  length: false,
  upper: false,
  lower: false,
  number: false,
  special: false,
})

const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/

function evaluateStrength() {
  const v = newPassword.value
  criteria.value = {
    length: v.length >= 8,
    upper: /[A-Z]/.test(v),
    lower: /[a-z]/.test(v),
    number: /\d/.test(v),
    special: /[!@#$%^&*]/.test(v),
  }
}

const strengthScore = computed(() => {
  return Object.values(criteria.value).filter(Boolean).length
})

const strengthPercent = computed(() => (strengthScore.value / 5) * 100)

const strengthLabel = computed(() => {
  if (strengthScore.value <= 2) return 'Weak'
  if (strengthScore.value <= 4) return 'Medium'
  return 'Strong'
})

const strengthClass = computed(() => {
  if (strengthScore.value <= 2) return 'weak'
  if (strengthScore.value <= 4) return 'medium'
  return 'strong'
})

watch(() => props.show, (val) => {
  if (val) {
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    error.value = ''
    success.value = ''
    isSubmitting.value = false
    evaluateStrength()
  }
})

async function submit() {
  if (isSubmitting.value) return
  error.value = ''
  success.value = ''

  // Validate current password is provided
  if (!currentPassword.value || currentPassword.value.trim() === '') {
    error.value = 'Please enter your current password.'
    return
  }

  if (!strongRegex.test(newPassword.value)) {
    error.value = 'Password must be at least 8 chars with uppercase, number, and special character.'
    return
  }

  if (newPassword.value !== confirmPassword.value) {
    error.value = 'Passwords do not match.'
    return
  }

  isSubmitting.value = true
  try {
    // Ensure CSRF cookie is set (Sanctum) and send request with credentials
    try {
      await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
      // After obtaining cookie, ensure axios header uses cookie value
      try {
        const xsrf = decodeURIComponent(document.cookie.split('; ').find(r => r.trim().startsWith('XSRF-TOKEN='))?.split('=')[1] || '')
        if (xsrf) {
          axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrf
        }
      } catch (e) {}
    } catch (e) {
      // ignore, we'll still attempt the POST and surface server errors
    }

    // Use the password the user entered for verification
    const res = await axios.post('/api/change-password', {
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: confirmPassword.value,
      username: props.username,
    }, { withCredentials: true })

    if (res.data.ok) {
      // Build success message with email confirmation
      let successMsg = 'Password updated successfully!'
      if (res.data.user?.email && res.data.email_verified) {
        successMsg += ` Email verified: ${res.data.user.email}`
      } else if (res.data.user?.email) {
        successMsg += ` Email on file: ${res.data.user.email}`
      }
      success.value = successMsg
      // Emit completion with server response so parent can decide next step
      setTimeout(() => emit('completed', { 
        verification_sent: res.data.verification_sent ?? false,
        user: res.data.user
      }), 800)
    } else {
      error.value = res.data.message || 'Unable to update password.'
    }
  } catch (e) {
    console.error('Password change error:', e)
    error.value = e.response?.data?.message || 'Unable to update password.'
  } finally {
    isSubmitting.value = false
  }
}

// email verification handled on separate page
</script>

<style scoped>
.force-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(16,24,40,0.06); /* subtle dim to match StaffIndex page */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999; /* ensure overlay sits above other UI elements */
  padding: 1rem;
}

.force-modal-card {
  width: 100%;
  max-width: 520px;
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(14px);
  border-radius: 18px;
  overflow: hidden;
  position: relative;
  z-index: 10000; /* ensure the card is above the overlay and other elements */
  box-shadow: 0 12px 30px rgba(16,24,40,0.06);
  border: 1px solid var(--border-stroke, rgba(240,233,224,0.9));
}

.force-modal-header {
  padding: 1.25rem 1.5rem;
  background: transparent;
  color: var(--text-primary, #42210b);
}

.force-modal-header h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
}

.force-modal-header p {
  margin: 0;
  opacity: 0.95;
}

.force-modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.4rem;
}

.form-input {
  width: 100%;
  padding: 0.7rem 2.8rem 0.7rem 0.85rem;
  border-radius: 10px;
  border: 1px solid var(--border-stroke, #f0e9e0);
  background: var(--surface-card, #fff);
  outline: none;
  box-sizing: border-box;
}

.form-input:focus {
  border-color: var(--color-royal-blue, #ff6b1c);
  box-shadow: 0 6px 20px rgba(16,24,40,0.06);
}

.read-only {
  background: #fff7f1;
}

.hint {
  display: block;
  margin-top: 0.35rem;
  color: var(--text-secondary, rgba(66,33,11,0.6));
}

.strength-meter {
  margin: 0.8rem 0 0.4rem 0;
}

.strength-bar {
  height: 8px;
  border-radius: 999px;
  background: #f5e4dc;
  overflow: hidden;
}

.strength-bar span {
  display: block;
  height: 100%;
  transition: width 0.25s ease;
}

.strength-label {
  margin-top: 0.35rem;
  font-weight: 700;
}

.strength-label.weak,
.strength-bar span.weak {
  color: #c23b3b;
  background: #f46d6d;
}

.strength-label.medium,
.strength-bar span.medium {
  color: var(--alert, #FF9800);
  background: #f2b04b;
}

.strength-label.strong,
.strength-bar span.strong {
  color: var(--success, #4CAF50);
  background: #36c186;
}

.criteria-list {
  list-style: none;
  padding: 0;
  margin: 0.6rem 0 0.8rem 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.35rem 1rem;
  font-size: 0.9rem;
  color: #9a6b58;
}

.criteria-list li.ok {
  color: #1c7c54;
  font-weight: 600;
}

.error-text {
  color: #c23b3b;
  background: #fdecec;
  padding: 0.6rem 0.8rem;
  border-radius: 8px;
  margin-top: 0.6rem;
}

.success-text {
  color: #1c7c54;
  background: #e8f8f0;
  padding: 0.6rem 0.8rem;
  border-radius: 8px;
  margin-top: 0.6rem;
}

.force-modal-footer {
  padding: 1rem 1.5rem 1.5rem;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}
.btn-secondary {
  background: transparent;
  color: var(--text-primary, #42210b);
  border: 1px solid rgba(66,33,11,0.12);
  padding: 0.6rem 1.2rem;
  border-radius: 999px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s ease;
}

.btn-secondary:hover {
  background: rgba(255,255,255,0.04);
}

.btn-primary {
  background: var(--dirty-white, #fff4e6);
  color: var(--text-dark, #42210b);
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 999px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s ease;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Eye icon styles for password toggle */
.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #666;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.password-toggle:hover {
  background: rgba(66,33,11,0.04);
  color: var(--text-primary, #42210b);
}

.password-toggle svg {
  width: 18px;
  height: 18px;
}
</style>

