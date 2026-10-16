<template>
  <section class="custom-clockout-module">
    <div class="custom-clockout-module__heading">
      <div>
        <p class="dashboard-eyebrow">STAFF SETTINGS</p>
        <h2>Automatic Clock-Out</h2>
      </div>
    </div>
    <p>Staff still clocked in after this time will be clocked out automatically for this branch.</p>
    <div class="custom-clockout-module__field">
      <label for="custom-auto-clockout-time">Clock out time</label>
      <div>
        <input id="custom-auto-clockout-time" v-model="clockoutTime" type="time" :disabled="saving">
        <button type="button" :disabled="saving" @click="save">
          {{ saving ? 'Saving...' : 'Save time' }}
        </button>
      </div>
    </div>
    <span v-if="message" :class="{ 'is-error': error }">{{ message }}</span>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const clockoutTime = ref('22:00')
const saving = ref(false)
const message = ref('')
const error = ref(false)

async function load() {
  try {
    const response = await axios.get('/api/attendance/settings', { withCredentials: true })
    clockoutTime.value = response.data?.data?.auto_clockout_time || '22:00'
  } catch (loadError) {
    error.value = true
    message.value = loadError.response?.data?.message || 'Unable to load automatic clock-out settings.'
  }
}

async function save() {
  saving.value = true
  error.value = false
  message.value = ''
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const response = await axios.put('/api/attendance/settings', {
      auto_clockout_time: clockoutTime.value,
    }, { withCredentials: true })
    clockoutTime.value = response.data?.data?.auto_clockout_time || clockoutTime.value
    message.value = 'Saved successfully.'
  } catch (saveError) {
    error.value = true
    message.value = saveError.response?.data?.message || 'Unable to save automatic clock-out settings.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.custom-clockout-module { padding: 18px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
.custom-clockout-module__heading { border: 1px solid #e5e7eb; padding: 14px; }
.custom-clockout-module h2 { margin: 4px 0 0; color: #1f2937; }
.custom-clockout-module p:not(.dashboard-eyebrow) { color: #6b7280; }
.custom-clockout-module__field { display: flex; justify-content: space-between; align-items: center; gap: 12px; border: 1px solid #e5e7eb; padding: 14px; font-weight: 600; }
.custom-clockout-module__field > div { display: flex; gap: 8px; align-items: center; }
.custom-clockout-module input { padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; }
.custom-clockout-module button { border: 0; border-radius: 999px; padding: 9px 16px; background: linear-gradient(135deg, #ff6a3d, #f59e0b); color: #fff; font-weight: 700; cursor: pointer; }
.custom-clockout-module button:disabled { opacity: .65; cursor: wait; }
.custom-clockout-module span { display: block; margin-top: 10px; color: #15803d; }
.custom-clockout-module span.is-error { color: #b91c1c; }
</style>
