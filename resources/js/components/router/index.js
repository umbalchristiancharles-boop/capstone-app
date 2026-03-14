import { createRouter, createWebHistory } from 'vue-router';

// Import components
import StaffList from '../components/StaffList.vue';
import ForgotPassword from '../components/ForgotPassword.vue';
import ResetPassword from '../components/ResetPassword.vue';

const routes = [
    {
      path: '/change-password',
      name: 'ChangePassword',
      component: () => import('../components/ChangePasswordPage.vue'),
      meta: { requiresAuth: false }
    },
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
    path: '/super-admin-panel',
    name: 'SuperAdminPanel',
    component: () => import('../components/SuperAdmin.vue'),
    meta: { requiresAuth: true, role: 'superadmin' }
  },
  {
    path: '/super-admin/logistics',
    name: 'SuperAdminLogistics',
    component: () => import('../components/LogisticsManager.vue'),
    meta: { requiresAuth: true, role: 'superadmin' }
  },
  {
    path: '/super-admin/procurement',
    name: 'SuperAdminProcurement',
    component: () => import('../components/SuperAdminProcurement.vue'),
    meta: { requiresAuth: true, role: 'superadmin' }
  },
  {
    path: '/super-admin/finance',
    name: 'SuperAdminFinance',
    component: () => import('../components/SuperAdminFinance.vue'),
    meta: { requiresAuth: true, role: 'superadmin' }
  },
  {
    path: '/super-admin/hr',
    name: 'SuperAdminHRStaffManagement',
    component: () => import('../components/SuperAdminStaffManagement.vue'),
    meta: { requiresAuth: true, role: 'superadmin' }
  },
  {
    path: '/owner-panel',
    name: 'OwnerPanel',
    component: () => import('../components/AdminPanel.vue'),
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
    component: () => import('../components/StaffManagement.vue'),
    meta: {
      requiresAuth: true,
      role: 'admin'
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
    path: '/manager/hr/staff-management',
    name: 'ManagerHRStaffManagement',
    component: () => import('../components/ManagerHRStaffManagement.vue'),
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
  },
  // Unauthorized page for role-based access denied
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: () => import('../components/Unauthorized.vue'),
    meta: { requiresAuth: false }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});


// ============================================================
// FULL ROUTER GUARD WITH DEBUGGING
// ============================================================

router.beforeEach((to, from, next) => {
  // DEBUG: Log navigation attempt
  console.log('[ROUTER] Navigation:', {
    to: to.path,
    from: from.path,
    meta: to.meta
  });

  // Get user from localStorage
  let user = null;
  try {
    const userStr = localStorage.getItem('user');
    console.log('[ROUTER] localStorage.getItem("user"):', userStr);
    user = userStr ? JSON.parse(userStr) : null;
    console.log('[ROUTER] Parsed user:', user);
  } catch (e) {
    console.error('[ROUTER] Error parsing user:', e);
    user = null;
  }

  // DEBUG: Log auth status
  console.log('[ROUTER] Auth status:', {
    hasUser: !!user,
    userRole: user ? user.role : null,
    userRoleLower: user ? (user.role || '').toLowerCase() : null
  });

  // === PUBLIC ROUTES - Allow always ===
  const publicRoutes = ['/', '/login', '/admin-login', '/unauthorized', '/admin/forgot-password', '/admin/reset-password'];
  if (publicRoutes.includes(to.path) || to.path.startsWith('/admin/reset-password/')) {
    console.log('[ROUTER] Public route - allowing');
    return next();
  }

  // === LANDING PAGE REDIRECT ===
  if (to.path === '/' || to.path === '/login') {
    if (user) {
      const userRole = (user.role || '').toLowerCase();
      if (userRole === 'owner') return next('/owner-panel');
      if (userRole === 'admin') return next('/admin-panel');
      if (userRole === 'manager') {
        if (user.department === 'hr') return next('/manager/hr');
        if (user.department === 'finance') return next('/manager/finance');
        if (user.department === 'inventory') return next('/manager/inventory');
        if (user.department === 'logistics') return next('/manager/logistics');
      }
      if (userRole === 'staff') {
        if (user.department === 'cashier') return next('/staff/cashier');
        if (user.department === 'finance') return next('/staff/finance');
        if (user.department === 'inventory') return next('/staff/inventory');
      }
      return next('/admin-panel');
    }
    if (to.path === '/') {
      return next('/admin-login');
    }
  }

  // === CHECK: If NO user (not authenticated) → redirect to /admin-login ===
  if (!user) {
    console.log('[ROUTER] NO USER - Redirecting to /admin-login');
    return next('/admin-login');
  }

  // === User is authenticated - normalize role to lowercase ===
  const userRole = (user.role || '').toLowerCase();
  console.log('[ROUTER] User role (normalized):', userRole);

  // === CHECK: Route requires specific role ===
  if (to.meta.role) {
    const requiredRole = (to.meta.role || '').toLowerCase();
    console.log('[ROUTER] Required role:', requiredRole);

    // Owner routes
    if (requiredRole === 'owner') {
      if (userRole === 'owner') {
        console.log('[ROUTER] Owner check PASSED - allowing access');
        return next();
      } else {
        console.log('[ROUTER] Owner check FAILED - redirecting to /unauthorized');
        return next('/unauthorized');
      }
    }

    // Manager routes
    if (requiredRole === 'manager') {
      if (userRole !== 'manager') {
        console.log('[ROUTER] Manager check FAILED - redirecting to /unauthorized');
        return next('/unauthorized');
      }
      // Check department if specified
      if (to.meta.department && user.department !== to.meta.department) {
        console.log('[ROUTER] Manager department check FAILED - redirecting to /unauthorized');
        return next('/unauthorized');
      }
      console.log('[ROUTER] Manager check PASSED - allowing access');
      return next();
    }

    // Staff routes
    if (requiredRole === 'staff') {
      if (userRole !== 'staff') {
        console.log('[ROUTER] Staff check FAILED - redirecting to /unauthorized');
        return next('/unauthorized');
      }
      // Check department if specified
      if (to.meta.department && user.department !== to.meta.department) {
        console.log('[ROUTER] Staff department check FAILED - redirecting to /unauthorized');
        return next('/unauthorized');
      }
      console.log('[ROUTER] Staff check PASSED - allowing access');
      return next();
    }

    // Generic role check for roles not explicitly handled above (admin, superadmin, etc.)
    // If requiredRole equals the current user's role, allow; otherwise deny.
    if (requiredRole && requiredRole !== 'owner' && requiredRole !== 'manager' && requiredRole !== 'staff') {
      if (userRole === requiredRole) {
        console.log('[ROUTER] Generic role check PASSED - allowing access');
        return next();
      }
      console.log('[ROUTER] Generic role check FAILED - redirecting to /unauthorized');
      return next('/unauthorized');
    }
  }

  // === Routes that just require authentication (no specific role) ===
  if (to.meta.requiresAuth) {
    console.log('[ROUTER] Route requires auth only - allowing');
    return next();
  }

  // Default: allow
  console.log('[ROUTER] Default - allowing');
  next();
});

// Helper: get correct panel route for user
function getPanelRoute(user) {
  if (!user) return '/admin-login';
  const userRole = (user.role || '').toLowerCase();
  if (userRole === 'owner') return '/owner-panel';
  if (userRole === 'admin') return '/admin-panel';
  if (userRole === 'manager') {
    if (user.department === 'hr') return '/manager/hr';
    if (user.department === 'finance') return '/manager/finance';
    if (user.department === 'inventory') return '/manager/inventory';
    if (user.department === 'logistics') return '/manager/logistics';
  }
  if (userRole === 'staff') {
    if (user.department === 'cashier') return '/staff/cashier';
    if (user.department === 'finance') return '/staff/finance';
    if (user.department === 'inventory') return '/staff/inventory';
  }
  return '/admin-panel';
}

export default router;
