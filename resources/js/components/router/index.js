import { createRouter, createWebHistory } from 'vue-router';

// Import components
import StaffList from '../components/admin/StaffList. vue';

const routes = [
  {
    path: '/',
    name:  'Home',
    redirect: '/admin-panel'
  },
  {
    path: '/admin-panel',
    name: 'AdminPanel',
    component: () => import('../components/AdminPanel.vue'),
    meta: { requiresAuth:  true }
  },
  {
    path: '/admin/staff-management',
    name: 'StaffManagement',
    component: StaffList,
    meta: {
      requiresAuth: true,
      role: 'OWNER'
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = sessionStorage. getItem('user_id');
  const userRole = sessionStorage. getItem('user_role');

  if (to.meta.requiresAuth && ! isAuthenticated) {
    // Redirect to login if not authenticated
    window.location.href = '/login';
  } else if (to. meta.role && to.meta. role !== userRole) {
    // Redirect to home if wrong role
    next('/');
  } else {
    next();
  }
});

export default router;
