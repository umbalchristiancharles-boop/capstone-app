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
  // Manager Panels
  {
    path: '/manager/hr',
    name: 'ManagerHRPanel',
    component: () => import('../components/ManagerHRPanel.vue'),
    meta: { requiresAuth: true, role: 'manager', department: 'hr' }
  },
  {
    path: '/manager/finance',
    name: 'ManagerFinancePanel',
    component: () => import('../components/ManagerFinancePanel.vue'),
    meta: { requiresAuth: true, role: 'manager', department: 'finance' }
  },
  {
    path: '/manager/inventory',
    name: 'ManagerInventoryPanel',
    component: () => import('../components/ManagerInventoryPanel.vue'),
    meta: { requiresAuth: true, role: 'manager', department: 'inventory' }
  },
  {
    path: '/manager/logistics',
    name: 'ManagerLogisticsPanel',
    component: () => import('../components/ManagerLogisticsPanel.vue'),
    meta: { requiresAuth: true, role: 'manager', department: 'logistics' }
  },
  // Staff Panels
  {
    path: '/staff/cashier',
    name: 'StaffCashierPanel',
    component: () => import('../components/StaffCashierPanel.vue'),
    meta: { requiresAuth: true, role: 'staff', department: 'cashier' }
  },
  {
    path: '/staff/finance',
    name: 'StaffFinancePanel',
    component: () => import('../components/StaffFinancePanel.vue'),
    meta: { requiresAuth: true, role: 'staff', department: 'finance' }
  },
  {
    path: '/staff/inventory',
    name: 'StaffInventoryPanel',
    component: () => import('../components/StaffInventoryPanel.vue'),
    meta: { requiresAuth: true, role: 'staff', department: 'inventory' }
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


// Role-based route guard
router.beforeEach(async (to, from, next) => {
  // Simulate getting user from localStorage/session or API
  let user = null;
  try {
    user = JSON.parse(localStorage.getItem('user')) || null;
  } catch (e) {}

  // If route requires authentication
  if (to.meta.requiresAuth) {
    if (!user) {
      return next({ path: '/admin-login' });
    }
    // Owner
    if (to.meta.role === 'OWNER' && user.role !== 'OWNER') {
      return next({ path: getPanelRoute(user) });
    }
    // Manager
    if (to.meta.role === 'manager' && user.role === 'manager') {
      if (to.meta.department && user.department !== to.meta.department) {
        return next({ path: getPanelRoute(user) });
      }
    } else if (to.meta.role === 'manager' && user.role !== 'manager') {
      return next({ path: getPanelRoute(user) });
    }
    // Staff
    if (to.meta.role === 'staff' && user.role === 'staff') {
      if (to.meta.department && user.department !== to.meta.department) {
        return next({ path: getPanelRoute(user) });
      }
    } else if (to.meta.role === 'staff' && user.role !== 'staff') {
      return next({ path: getPanelRoute(user) });
    }
  }
  // If route requires guest (not logged in)
  if (to.meta.requiresGuest && user) {
    return next({ path: getPanelRoute(user) });
  }
  next();
});

// Helper: get correct panel route for user
function getPanelRoute(user) {
  if (!user) return '/admin-login';
  if (user.role === 'OWNER') return '/owner-panel';
  if (user.role === 'manager') {
    if (user.department === 'hr') return '/manager/hr';
    if (user.department === 'finance') return '/manager/finance';
    if (user.department === 'inventory') return '/manager/inventory';
    if (user.department === 'logistics') return '/manager/logistics';
  }
  if (user.role === 'staff') {
    if (user.department === 'cashier') return '/staff/cashier';
    if (user.department === 'finance') return '/staff/finance';
    if (user.department === 'inventory') return '/staff/inventory';
  }
  return '/admin-panel';
}

export default router;
