import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './app.vue'
import Index from './components/index.vue'
import AdminPanel from './components/adminpanel.vue'
import ManagerPanel from './components/ManagerPanel.vue'
import StaffPanel from './components/StaffPanel.vue'
import HrPanel from './components/hrpanel.vue'
import adminlogin from './components/adminlogin.vue'
import StaffList from './components/StaffList.vue'
import DeletedStaffList from './components/DeletedStaffList.vue'
import axios from 'axios'

// GLOBAL CSS (body margin reset, etc.)
import './css/index.css'

// === AXIOS GLOBAL CONFIG ===
axios.defaults.baseURL = 'http://localhost:8000'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute('content')

if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
}

// Ensure CSRF cookie is initialized (useful when running via Vite dev server)
axios
  .get('/sanctum/csrf-cookie', { withCredentials: true })
  .catch(() => {
    // ignore errors; server may not have sanctum route in some environments
  })
// Configure axios XSRF names to match Laravel defaults
axios.defaults.xsrfCookieName = 'XSRF-TOKEN'
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN'

// === ROUTER SETUP ===
const ResetPassword = () => import('./components/ResetPassword.vue');

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Index },
    { path: '/admin-panel', component: AdminPanel },
    { path: '/manager-panel', component: ManagerPanel },
    { path: '/staff-panel', component: StaffPanel },
    { path: '/admin-login', component: adminlogin },
    { path: '/hr-panel', component: HrPanel},
    {
      path: '/admin/staff-management',
      component: StaffList,
      meta: { requiresAuth: true },
    },
    {
      path: '/admin/deleted-staff',
      component: DeletedStaffList,
      meta: { requiresAuth: true },
    },
    // Admin Reset Password (SPA route)
    {
      path: '/admin/reset-password',
      name: 'AdminResetPassword',
      component: ResetPassword,
      meta: { requiresGuest: true },
    },
    {
      path: '/admin/reset-password/:token',
      name: 'AdminResetPasswordToken',
      component: ResetPassword,
      meta: { requiresGuest: true },
    },
  ],
})

// --- Page blur helpers (global) ---
function showPageBlur() {
  try {
    const el = document.getElementById('page-blur')
    if (el) el.classList.add('active')
  } catch (e) {}
}

function hidePageBlur() {
  try {
    const el = document.getElementById('page-blur')
    if (el) el.classList.remove('active')
  } catch (e) {}
}

window.pageBlur = {
  show: showPageBlur,
  hide: hidePageBlur,
}

// show blur right when navigation starts; hide after a delay so transition persists
router.beforeEach((to, from, next) => {
  // Do not show the global blur when navigating from Index -> Admin Login
  // (this avoids the delayed blur covering login inputs on that transition)
  if (to.path === '/admin-login' && from && from.path === '/') {
    return next()
  }

  showPageBlur()
  next()
})

router.afterEach(() => {
  setTimeout(() => {
    hidePageBlur()
  }, 600)
})

// === GLOBAL GUARD PARA PROTECTED ANG /admin-panel ===
router.beforeEach(async (to, from, next) => {
  if (to.path === '/admin-panel' || to.meta.requiresAuth) {
    // If the user is navigating away from admin panel, clear the reload flag
    // so coming back to staff-management triggers a fresh reload.
    if (from && (from.path === '/admin-panel' || from.path === '/manager-panel')) {
      try { sessionStorage.removeItem('appReloaded') } catch (e) {}
    }
    try {
      const res = await axios.get('/api/me', {
        withCredentials: true,
      })
      if (res.data.ok) {
        // If navigating to staff-management, perform a one-time full reload
        // to ensure server-rendered CSRF meta tag and cookies are in sync.
        if ((to.path === '/admin/staff-management' || to.path === '/manager-panel') && !sessionStorage.getItem('appReloaded')) {
          sessionStorage.setItem('appReloaded', '1')
          // remember where we wanted to go so we can restore after reload
          sessionStorage.setItem('preReloadPath', to.path || window.location.pathname)
          window.location.reload()
          return
        }

        return next()
      }
      return next('/admin-login')
    } catch (e) {
      return next('/admin-login')
    }
  }

  next()
})

// Mount the app after attempting to initialize CSRF cookie so axios can
// automatically send the X-XSRF-TOKEN header for stateful requests.
axios
  .get('/sanctum/csrf-cookie', { withCredentials: true })
  .finally(() => {
    // If the XSRF cookie is present, make sure axios sends it as a header
    function getCookie(name) {
      const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'))
      return match ? match[2] : null
    }

    const xsrfCookie = getCookie('XSRF-TOKEN')
    if (xsrfCookie) {
      try {
        axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrfCookie)
      } catch (e) {
        axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrfCookie
      }
    }
    // If the server-rendered page is already at /admin/staff-management,
    // do a one-time reload to ensure CSRF meta tag and XSRF cookie are fresh
    if ((window.location.pathname === '/admin/staff-management' || window.location.pathname === '/manager-panel') && !sessionStorage.getItem('appReloaded')) {
      sessionStorage.setItem('appReloaded', '1')
      sessionStorage.setItem('preReloadPath', window.location.pathname)
      window.location.reload()
      return
    }

    const app = createApp(App)
    app.use(router)
    app.mount('#app')

    // If we saved a preReloadPath, navigate there now to restore user's location
    try {
      const pre = sessionStorage.getItem('preReloadPath')
      if (pre) {
        sessionStorage.removeItem('preReloadPath')
        // use router.replace to avoid adding extra history entry
        router.replace(pre).catch(() => {})
      }
    } catch (e) {
      // ignore
    }
  })
