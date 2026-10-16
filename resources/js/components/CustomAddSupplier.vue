<template>
  <section class="custom-add-supplier">
    <div class="custom-add-supplier__heading">
      <p class="dashboard-eyebrow">SUPPLIER MANAGEMENT</p>
      <h2>Add Supplier</h2>
      <p>Create a supplier account for procurement operations.</p>
    </div>

    <form class="custom-add-supplier__form" @submit.prevent="submit">
      <label>
        Full Name
        <input v-model.trim="form.fullName" type="text" placeholder="Supplier full name" required>
      </label>
      <label>
        Business Name
        <input v-model.trim="form.businessName" type="text" placeholder="Company/Business name" required>
      </label>
      <label>
        Username
        <input v-model.trim="form.username" type="text" placeholder="username" required>
      </label>
      <label>
        Email
        <input v-model.trim="form.email" type="email" placeholder="supplier@example.com" required>
      </label>
      <label>
        Phone
        <input v-model.trim="form.phone" type="text" placeholder="Optional">
      </label>
      <div class="custom-add-supplier__password">
        <span>Default Password</span>
        <strong>{{ defaultPassword }}</strong>
        <small>This password will be assigned automatically.</small>
      </div>
      <p v-if="message" :class="{ 'is-error': error }" class="custom-add-supplier__message">{{ message }}</p>
      <div class="custom-add-supplier__actions">
        <button type="submit" :disabled="submitting">
          {{ submitting ? 'Creating...' : 'Create Supplier' }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const defaultPassword = ref('Chikintayo_123')
const submitting = ref(false)
const error = ref(false)
const message = ref('')
const form = ref({
  username: '',
  email: '',
  fullName: '',
  businessName: '',
  phone: '',
})

async function submit() {
  submitting.value = true
  error.value = false
  message.value = ''
  try {
    const response = await axios.post('/api/manager/procurement/suppliers', {
      ...form.value,
      password: undefined,
    }, { withCredentials: true })
    message.value = response.data?.message || 'Supplier created successfully.'
    form.value = { username: '', email: '', fullName: '', businessName: '', phone: '' }
  } catch (submitError) {
    error.value = true
    message.value = submitError.response?.data?.message || 'Failed to create supplier.'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.custom-add-supplier { padding: 18px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
.custom-add-supplier__heading { padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; }
.custom-add-supplier h2 { margin: 4px 0 6px; color: #1f2937; }
.custom-add-supplier__heading p:not(.dashboard-eyebrow) { margin: 0; color: #6b7280; }
.custom-add-supplier__form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 18px; }
.custom-add-supplier label, .custom-add-supplier__password { display: flex; flex-direction: column; gap: 6px; color: #374151; font-size: 13px; font-weight: 700; }
.custom-add-supplier input { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; font-weight: 400; }
.custom-add-supplier__password { padding: 10px 12px; border: 1px solid #fde68a; border-radius: 6px; background: #fffbeb; }
.custom-add-supplier__password strong { color: #92400e; }
.custom-add-supplier__password small { color: #78716c; font-weight: 400; }
.custom-add-supplier__message { grid-column: 1 / -1; margin: 0; color: #15803d; font-size: 13px; }
.custom-add-supplier__message.is-error { color: #b91c1c; }
.custom-add-supplier__actions { grid-column: 1 / -1; display: flex; justify-content: flex-end; }
.custom-add-supplier button { border: 0; border-radius: 999px; padding: 10px 18px; background: linear-gradient(135deg, #ff6a3d, #f59e0b); color: #fff; font-weight: 700; cursor: pointer; }
.custom-add-supplier button:disabled { cursor: wait; opacity: .65; }
@media (max-width: 640px) { .custom-add-supplier__form { grid-template-columns: 1fr; } }
</style>
