<template>
  <div class="unauthorized-container">
    <div class="unauthorized-content">
      <div class="icon-container">
        <svg xmlns="http://www.w3.org/2000/svg" class="warning-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h1>Access Denied</h1>
      <p class="message">You do not have permission to access this page.</p>
      <p class="details">Your current role does not allow you to view this content.</p>

      <div class="actions">
        <button @click="goBack" class="btn btn-secondary">Go Back</button>
        <button @click="goToDashboard" class="btn btn-primary">Go to Dashboard</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const user = JSON.parse(localStorage.getItem('user') || 'null');

const goBack = () => {
  router.go(-1);
};

const goToDashboard = () => {
  // Redirect based on user role
  if (user) {
    const userRole = (user.role || '').toLowerCase();
    if (userRole === 'owner') {
      router.push('/owner-panel');
      return;
    }
    if (userRole === 'admin') {
      router.push('/admin-panel');
      return;
    }
    if (userRole === 'manager') {
      if (user.department === 'hr') {
        router.push('/manager/hr');
        return;
      }
      if (user.department === 'finance') {
        router.push('/manager/finance');
        return;
      }
      if (user.department === 'inventory') {
        router.push('/manager/inventory');
        return;
      }
      if (user.department === 'logistics') {
        router.push('/manager/logistics');
        return;
      }
    }
    if (userRole === 'staff') {
      if (user.department === 'cashier') {
        router.push('/staff/cashier');
        return;
      }
      if (user.department === 'finance') {
        router.push('/staff/finance');
        return;
      }
      if (user.department === 'inventory') {
        router.push('/staff/inventory');
        return;
      }
    }
  }
  // Default fallback
  router.push('/staff-landing');
};

onMounted(() => {
  // Auto-redirect after 5 seconds if no action
  setTimeout(() => {
    goToDashboard();
  }, 5000);
});
</script>

<style scoped>
.unauthorized-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.unauthorized-content {
  background: white;
  border-radius: 16px;
  padding: 48px;
  text-align: center;
  max-width: 480px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.icon-container {
  margin-bottom: 24px;
}

.warning-icon {
  width: 80px;
  height: 80px;
  color: #f59e0b;
  margin: 0 auto;
}

h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.message {
  font-size: 18px;
  color: #4b5563;
  margin: 0 0 8px 0;
}

.details {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 32px 0;
}

.actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5568d3;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}
</style>
