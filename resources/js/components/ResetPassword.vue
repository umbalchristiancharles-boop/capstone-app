<!-- filepath: resources/js/components/ResetPassword.vue -->
<template>
  <div class="container">
    <form @submit.prevent="submitForm">
      <h1 class="text-orange-700" style="margin-bottom: 1.5rem;">Reset Admin Password</h1>

      <input type="hidden" v-model="form.token" />
      <input type="hidden" v-model="form.email" />

      <label for="password">New Password</label>
      <input
        id="password"
        type="password"
        v-model="form.password"
        required
        placeholder="Enter new password"
      />

      <label for="password_confirmation">Confirm Password</label>
      <input
        id="password_confirmation"
        type="password"
        v-model="form.password_confirmation"
        required
        placeholder="Confirm new password"
      />

      <p v-if="error" class="text-xs" style="color: #dc2626; margin-bottom: 0.5rem;">{{ error }}</p>
      <p v-if="success" class="text-xs" style="color: #16a34a; margin-bottom: 0.5rem;">{{ success }}</p>

      <button type="submit">
        Reset Password
      </button>

      <div class="mt-8 pt-6 border-t text-center">
        <a href="/admin/login">← Back to Admin Login</a>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const form = ref({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const error = ref('');
const success = ref('');

onMounted(() => {
  form.value.token = route.query.token || '';
  form.value.email = route.query.email || '';
});

async function submitForm() {
  error.value = '';
  success.value = '';
  try {
    const response = await fetch('/api/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify(form.value)
    });

    const data = await response.json();

    if (response.ok) {
      success.value = 'Password reset successful! Redirecting to login...';
      setTimeout(() => {
        router.push('/admin-login');
      }, 2000);
    } else {
      error.value = data.message || 'Reset failed. Please check your input.';
    }
  } catch (e) {
    error.value = 'An error occurred. Please try again.';
  }
}
</script>

<style scoped>
@import '../../css/app.css';
</style>