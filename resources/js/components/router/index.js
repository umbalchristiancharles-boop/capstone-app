import { createRouter, createWebHistory } from 'vue-router';

// Import components
import StaffList from '../components/StaffList.vue';
import ForgotPassword from '../components/ForgotPassword.vue';
import ResetPassword from '../components/ResetPassword.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    redirect: '/admin-panel'
  },
  {
    path: '/admin-panel',
    name: 'AdminPanel',
    component: () => import('../components/AdminPanel.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/owner-panel',
    name: 'OwnerPanel',
    component: () => import('../components/OwnerPanel.vue'),
    meta: { requiresAuth: true, role: 'OWNER' }
  },
  {
    path: '/owner/staff-management',
    name: 'OwnerStaffManagement',
    component: () => import('../components/OwnerStaffManagement.vue'),
    meta: { requiresAuth: true, role: 'OWNER' }
  },
  {
    path: '/staff-management',
    name: 'StaffManagement',
    component: StaffList,
    meta: {
      requiresAuth: true,
      role: 'OWNER'
    }
  },
  // HR Routes
  {
    path: '/hr/dashboard',
    name: 'HRDashboard',
    component: () => import('../components/hrpanel.vue'),
    meta: {
      requiresAuth: true,
      role: 'HR'
    }
  },
  // Forgot Password Route (guest only)
  {
    path: '/admin/forgot-password',
    name: 'AdminForgotPassword',
    component: ForgotPassword,
    meta: { requiresGuest: true }
  },
  // Admin Login Route
  {
    path: '/admin-login',
    name: 'AdminLogin',
    component: () => import('../components/adminlogin.vue'),
    meta: { requiresGuest: true }
  },
  // RESET PASSWORD ROUTE (guest only, supports both query and path param)
  {
    path: '/admin/reset-password',
    name: 'AdminResetPassword',
    component: ResetPassword,
    meta: { requiresGuest: true }
  },
  {
    path: '/admin/reset-password/:token',
    name: 'AdminResetPasswordToken',
    component: ResetPassword,
    meta: { requiresGuest: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
