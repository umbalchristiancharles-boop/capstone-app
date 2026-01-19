import { createRouter, createWebHistory } from 'vue-router';

// Import components
import StaffList from '../components/admin/StaffList.vue';

// NEW: Forgot Password import (adjust path base sa folder mo)
import ForgotPassword from '../components/ForgotPassword.vue';  // ← Change to your actual path

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
    path: '/admin/staff-management',
    name: 'StaffManagement',
    component: StaffList,
    meta: {
      requiresAuth: true,
      role: 'OWNER'
    }
  },
  // NEW: Forgot Password Route (guest only - no auth needed)
  {
    path: '/admin/forgot-password',
    name: 'AdminForgotPassword',
    component: ForgotPassword,
    meta: { requiresGuest: true }
  },
  // NEW: Admin Login Route (add kung wala pa)
  {
    path: '/admin-login',
    name: 'AdminLogin',
    component: () => import('../components/adminlogin.vue'),
    meta: { requiresGuest: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Updated Navigation guard (handles requiresGuest + requiresAuth)
router.beforeEach((to, from, next) => {
  const isAuthenticated = sessionStorage.getItem('user_id');
  const userRole = sessionStorage.getItem('user_role');

  // Guest routes: redirect if already logged in
  if (to.meta.requiresGuest && isAuthenticated) {
    next('/admin-panel');  // Redirect to dashboard if logged in
    return;
  }

  // Auth required routes: redirect to login if not logged in
  if (to.meta.requiresAuth && !isAuthenticated) {
    window.location.href = '/admin-login';  // Use Vue route instead of external
    return;
  }

  // Role check for auth routes
  if (to.meta.role && to.meta.role !== userRole) {
    next('/');  // Redirect to home for wrong role
    return;
  }

  next();
});

export default router;
