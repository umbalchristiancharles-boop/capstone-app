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
axios.defaults.baseURL = '' // use relative URLs so requests go to current origin
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

let pendingRequests = 0
const requestWaiters = []

function notifyRequestWaiters() {
  if (pendingRequests > 0) return
  while (requestWaiters.length) {
    const resolve = requestWaiters.shift()
    try { resolve() } catch (e) {}
  }
}

// Axios interceptor to always get fresh CSRF token before each request
axios.interceptors.request.use(config => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (csrfToken) {
    config.headers['X-CSRF-TOKEN'] = csrfToken
  }
  pendingRequests += 1
  return config
}, error => {
  return Promise.reject(error)
})

axios.interceptors.response.use(response => {
  pendingRequests = Math.max(0, pendingRequests - 1)
  notifyRequestWaiters()
  return response
}, error => {
  pendingRequests = Math.max(0, pendingRequests - 1)
  notifyRequestWaiters()
  return Promise.reject(error)
})

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
      path: '/manager/staff',
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

// --- Global route loading overlay ---
const routeLogoUrl = new URL('./assets/chikinlogo.png', import.meta.url).href

function showRouteOverlay(text = 'Loading...') {
  try {
    if (window.__chikin_temp_overlay || window.__route_loading_overlay) return
    if (document.querySelector('.loading-overlay')) return
    const overlay = document.createElement('div')
    overlay.className = 'route-loading-overlay'
    overlay.innerHTML = `
      <div class="logo-loading-box">
        <img src="${routeLogoUrl}" alt="Chikin Tayo" class="logo-loading-img" />
        <p>${text}</p>
      </div>
    `
    document.body.appendChild(overlay)
    window.__route_loading_overlay = overlay
    requestAnimationFrame(() => overlay.classList.add('active'))
  } catch (e) {}
}

function hideRouteOverlay() {
  try {
    const overlay = window.__route_loading_overlay
    if (!overlay) return
    overlay.classList.remove('active')
    setTimeout(() => {
      try { overlay.remove() } catch (e) {}
      if (window.__route_loading_overlay === overlay) {
        window.__route_loading_overlay = null
      }
    }, 260)
  } catch (e) {}
}

// show blur right when navigation starts; hide after a delay so transition persists
router.beforeEach((to, from, next) => {
  const skipOverlay = sessionStorage.getItem('skipRouteOverlay') === '1'
  const suppressOverlay = sessionStorage.getItem('suppressRouteOverlay') === '1'
  if (skipOverlay) {
    try { sessionStorage.removeItem('skipRouteOverlay') } catch (e) {}
  }
  if (suppressOverlay) {
    try { sessionStorage.removeItem('suppressRouteOverlay') } catch (e) {}
  } else if (!skipOverlay) {
    showRouteOverlay('Loading...')
    showPageBlur()
  }
  next()
})

router.afterEach(() => {
  const waitForRequests = (timeoutMs = 2500) => new Promise(resolve => {
    if (pendingRequests === 0) return resolve()
    requestWaiters.push(resolve)
    setTimeout(resolve, timeoutMs)
  })

  waitForRequests().then(() => {
    setTimeout(() => {
      hidePageBlur()
    }, 200)
    setTimeout(() => {
      hideRouteOverlay()
    }, 200)
  })

  // Refresh CSRF cookie after navigation to protected panels to avoid 419s
  try {
    const protectedPaths = ['/admin-panel', '/manager-panel', '/staff-panel', '/hr-panel', '/admin', '/manager', '/staff', '/hr']
    const toPath = window.location.pathname || ''
    if (protectedPaths.some(p => toPath.startsWith(p))) {
      // fetch fresh XSRF cookie and set axios header
      axios.get('/sanctum/csrf-cookie', { withCredentials: true }).finally(() => {
        function getCookie(name) { const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)')); return match ? match[2] : null }
        const xsrfCookie = getCookie('XSRF-TOKEN')
        if (xsrfCookie) {
          try { axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrfCookie) } catch (e) { axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrfCookie }
        }
      }).catch(() => {})
    }
  } catch (e) {}
})

router.onError(() => {
  hideRouteOverlay()
  hidePageBlur()
})

// === GLOBAL GUARD PARA PROTECTED ANG /admin-panel ===
router.beforeEach(async (to, from, next) => {
  // Public routes - allow always
  if (to.path === '/' || to.path === '/admin-login') {
    return next()
  }

  // Protected panel routes
  const protectedRoutes = ['/admin-panel', '/manager-panel', '/staff-panel', '/hr-panel', '/admin/staff-management', '/admin/deleted-staff', '/manager/staff']
  const isProtectedRoute = protectedRoutes.some(route => to.path.startsWith(route)) || to.meta.requiresAuth

  if (isProtectedRoute) {
    // Clear reload flag when navigating away from admin/manager panel
    if (from && (from.path === '/admin-panel' || from.path === '/manager-panel')) {
      try { sessionStorage.removeItem('appReloaded') } catch (e) {}
    }

    // One-time reload for staff-management to sync CSRF
    if ((to.path === '/admin/staff-management' || to.path === '/manager-panel' || to.path === '/manager/staff') && !sessionStorage.getItem('appReloaded')) {
      try {
        sessionStorage.setItem('suppressRouteOverlay', '1')
        sessionStorage.setItem('suppressRouteTransition', '1')
      } catch (e) {}
      sessionStorage.setItem('appReloaded', '1')
      sessionStorage.setItem('preReloadPath', to.path || window.location.pathname)
      window.location.reload()
      return
    }

    // Allow navigation to proceed - components will handle auth errors
    return next()
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
    if ((window.location.pathname === '/admin/staff-management' || window.location.pathname === '/manager-panel' || window.location.pathname === '/manager/staff') && !sessionStorage.getItem('appReloaded')) {
      try {
        sessionStorage.setItem('suppressRouteOverlay', '1')
        sessionStorage.setItem('suppressRouteTransition', '1')
      } catch (e) {}
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
