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
          <label>New Password</label>
          <input
            v-model="newPassword"
            type="password"
            class="form-input"
            placeholder="Enter a strong password"
            @input="evaluateStrength"
            autocomplete="new-password"
          />
        </div>

        <div class="form-group">
          <label>Confirm New Password</label>
          <input
            v-model="confirmPassword"
            type="password"
            class="form-input"
            placeholder="Re-enter new password"
            @input="evaluateStrength"
            autocomplete="new-password"
          />
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
  defaultPassword: { type: String, default: 'ChikinTayo_2526' }
})

const emit = defineEmits(['completed', 'cancel'])

const newPassword = ref('')
const confirmPassword = ref('')
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
    // Fetch fresh CSRF token and update meta tag
    const tokenRes = await axios.get('/api/csrf-token')
    const freshToken = tokenRes.data.token

    // Update meta tag so axios interceptor picks it up
    const metaTag = document.querySelector('meta[name="csrf-token"]')
    if (metaTag) {
      metaTag.setAttribute('content', freshToken)
    }

    const res = await axios.post('/api/change-password', {
      current_password: props.defaultPassword,
      new_password: newPassword.value,
      new_password_confirmation: confirmPassword.value,
    })

    if (res.data.ok) {
      success.value = 'Password updated! Redirecting...'
      setTimeout(() => {
        emit('completed')
      }, 800)
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
</script>

<style scoped>
.force-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 60;
  padding: 1rem;
}

.force-modal-card {
  width: 100%;
  max-width: 520px;
  background: #fff;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(255, 106, 61, 0.35);
  border: 1px solid rgba(255, 154, 74, 0.4);
}

.force-modal-header {
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, #ff9a4a, #ff6a3d);
  color: #fff;
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
  padding: 0.7rem 0.85rem;
  border-radius: 10px;
  border: 1px solid #f0c1a8;
  background: #fff;
  outline: none;
}

.form-input:focus {
  border-color: #ff8c5f;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.2);
}

.read-only {
  background: #fff7f1;
}

.hint {
  display: block;
  margin-top: 0.35rem;
  color: #8b5d4a;
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
  color: #c27a1d;
  background: #f2b04b;
}

.strength-label.strong,
.strength-bar span.strong {
  color: #1c7c54;
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
  background: #fff;
  color: #ff6a3d;
  border: 2px solid #ff9a4a;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #fff7f1;
}

.btn-primary {
  background: linear-gradient(135deg, #ff9a4a, #ff6a3d);
  color: #fff;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
