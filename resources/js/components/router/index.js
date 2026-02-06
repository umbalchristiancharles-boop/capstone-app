import { createRouter, createWebHistory } from 'vue-router';

// Import components
import StaffList from '../components/admin/StaffList.vue';
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
    path: '/admin/staff-management',
    name: 'StaffManagement',
    component: StaffList,
    meta: {
      requiresAuth: true,
      role: 'OWNER'
    }
  },
  // Branch Manager Routes
  {
    path: '/manager/dashboard',
    name: 'ManagerDashboard',
    component: () => import('../components/ManagerPanel.vue'),
    meta: {
      requiresAuth: true,
      role: 'BRANCH_MANAGER'
    }
  },
  // Branch Manager Inventory
  {
    path: '/manager/inventory',
    name: 'ManagerInventory',
    component: () => import('../components/InventoryManagement.vue'),
    meta: {
      requiresAuth: true,
      role: 'BRANCH_MANAGER'
    }
  },
  // Branch Manager Staff Management
  {
    path: '/manager/staff',
    name: 'ManagerStaffManagement',
    component: StaffList,
    meta: {
      requiresAuth: true,
      role: 'BRANCH_MANAGER'
    }
  },
  // Staff Routes
  {
    path: '/staff/dashboard',
    name: 'StaffDashboard',
    component: () => import('../components/StaffPanel.vue'),
    meta: {
      requiresAuth: true,
      role: 'STAFF'
    }
  },
  // Staff Clock In/Out
  {
    path: '/staff/clock',
    name: 'StaffClockInOut',
    component: () => import('../components/ClockInOut.vue'),
    meta: {
      requiresAuth: true,
      role: 'STAFF'
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

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = sessionStorage.getItem('user_id');
  const userRole = sessionStorage.getItem('user_role');

  // Redirect logged-in users away from guest-only pages
  if (to.meta.requiresGuest && isAuthenticated) {
    next('/admin-panel');
    return;
  }

  // Redirect guests to login if route requires auth
  if (to.meta.requiresAuth && !isAuthenticated) {
    window.location.href = '/admin-login';
    return;
  }

  // Role-based route protection
  if (to.meta.role && to.meta.role !== userRole) {
    next('/');
    return;
  }

  next();
});

export default router;
