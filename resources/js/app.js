import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './app.vue'
import Index from './components/index.vue'
import AdminPanel from './components/adminpanel.vue'
import OwnerPanel from './components/OwnerPanel.vue'
import adminlogin from './components/adminlogin.vue'
import StaffList from './components/StaffList.vue'
import OwnerStaffManagement from './components/OwnerStaffManagement.vue'
import DeletedStaffList from './components/DeletedStaffList.vue'
import axios from 'axios'

// GLOBAL CSS (body margin reset, etc.)
import './css/index.css'

// === AXIOS GLOBAL CONFIG ===
axios.defaults.baseURL = '' // use relative URLs so requests go to current origin
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

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
  try {
    console.debug('[AXIOS] Request ->', (config.method || '').toUpperCase(), config.url, 'cookies:', document.cookie, 'headers:', config.headers)
  } catch (e) {}
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
  try {
    const resp = error && error.response
    const req = error && error.config
    const status = resp && resp.status
    console.warn('[AXIOS] Response error', {
      url: req && (req.url || req.baseURL),
      method: req && req.method,
      status: status,
      headers: resp && resp.headers,
      cookie: document.cookie,
    })
  } catch (e) {}
  return Promise.reject(error)
})

// === ROUTER SETUP ===
const ResetPassword = () => import('./components/ResetPassword.vue');

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Index },
    { path: '/login', component: adminlogin },
    { path: '/admin-login', component: adminlogin },
    { path: '/admin-panel', component: AdminPanel },
    { path: '/manager-panel', component: AdminPanel, meta: { requiresAuth: true } },
    { path: '/manager/inventory', component: () => import('./components/ManagerInventoryPanel.vue'), meta: { requiresAuth: true } },
    { path: '/manager/finance', component: () => import('./components/ManagerFinancePanel.vue'), meta: { requiresAuth: true } },
    { path: '/manager/logistics', component: () => import('./components/ManagerLogisticsPanel.vue'), meta: { requiresAuth: true } },
    { path: '/manager/hr', component: () => import('./components/ManagerHRPanel.vue'), meta: { requiresAuth: true } },
    { path: '/staff-panel', component: StaffList },
    { path: '/staff/cashier', component: () => import('./components/StaffCashierPanel.vue'), meta: { requiresAuth: true } },
    { path: '/staff/finance', component: () => import('./components/StaffFinancePanel.vue'), meta: { requiresAuth: true } },
    { path: '/staff/inventory', component: () => import('./components/inventory/InventoryStaffPanel.vue'), meta: { requiresAuth: true } },
    { path: '/owner-panel', component: OwnerPanel },
    { path: '/hr-panel', component: DeletedStaffList},
    {
      path: '/staff-management',
      component: () => import('./components/StaffManagement.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/owner/staff-management',
      component: OwnerStaffManagement,
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
  const protectedRoutes = ['/admin-panel', '/manager-panel', '/staff-panel', '/hr-panel', '/staff-management', '/owner/staff-management', '/admin/deleted-staff', '/manager/staff']
  const isProtectedRoute = protectedRoutes.some(route => to.path.startsWith(route)) || to.meta.requiresAuth

  if (isProtectedRoute) {
    // Clear reload flag when navigating away from admin/manager panel
    if (from && (from.path === '/admin-panel' || from.path === '/manager-panel')) {
      try { sessionStorage.removeItem('appReloaded') } catch (e) {}
    }

    // One-time reload for staff-management and staff inventory to sync CSRF
    if ((to.path === '/staff-management' || to.path === '/owner/staff-management' || to.path === '/manager-panel' || to.path === '/manager/staff' || to.path === '/staff/inventory') && !sessionStorage.getItem('appReloaded')) {
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
    // If the server-rendered page is already at /staff-management,
    // do a one-time reload to ensure CSRF meta tag and XSRF cookie are fresh
    if ((window.location.pathname === '/staff-management' || window.location.pathname === '/owner/staff-management' || window.location.pathname === '/manager-panel' || window.location.pathname === '/manager/staff' || window.location.pathname === '/staff/inventory') && !sessionStorage.getItem('appReloaded')) {
      try {
        sessionStorage.setItem('suppressRouteOverlay', '1')
        sessionStorage.setItem('suppressRouteTransition', '1')
      } catch (e) {}
      sessionStorage.setItem('appReloaded', '1')
      sessionStorage.setItem('preReloadPath', window.location.pathname)
      window.location.reload()
      return
    }

    // Central interceptor: try to recover from HTML/index responses or auth failures
    // by refreshing the Sanctum CSRF cookie and retrying the original request once.
    function isHtmlResponse(res) {
      try {
        const ct = res && res.headers && res.headers['content-type']
        if (typeof res.data === 'string' && res.data.trim().toLowerCase().startsWith('<!doctype')) return true
        if (ct && ct.indexOf('text/html') !== -1) return true
      } catch (e) {}
      return false
    }

    async function refreshCsrf() {
      try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
        const match = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'))
        const token = match ? decodeURIComponent(match[2]) : null
        if (token) axios.defaults.headers.common['X-XSRF-TOKEN'] = token
        return true
      } catch (e) {
        return false
      }
    }

    axios.interceptors.response.use(async function (response) {
      if (isHtmlResponse(response)) {
        const req = response.config || {}
        if (req._retriedForCsrf) {
          router.replace('/admin-login').catch(() => { window.location.href = '/admin-login' })
          return Promise.reject(new Error('Received HTML response from API'))
        }
        req._retriedForCsrf = true
        const ok = await refreshCsrf()
        if (!ok) {
          router.replace('/admin-login').catch(() => { window.location.href = '/admin-login' })
          return Promise.reject(new Error('Failed to refresh CSRF'))
        }
        return axios(req)
      }
      return response
    }, async function (error) {
      const resp = error && error.response
      const req = error.config || {}
      const status = resp && resp.status

      if ((status === 401 || status === 419 || status === 403) || isHtmlResponse(resp || {})) {
        if (req._retriedForCsrf) {
          router.replace('/admin-login').catch(() => { window.location.href = '/admin-login' })
          return Promise.reject(error)
        }
        req._retriedForCsrf = true
        const ok = await refreshCsrf()
        if (!ok) {
          router.replace('/admin-login').catch(() => { window.location.href = '/admin-login' })
          return Promise.reject(error)
        }
        try {
          return axios(req)
        } catch (e) {
          router.replace('/admin-login').catch(() => { window.location.href = '/admin-login' })
          return Promise.reject(e)
        }
      }

      return Promise.reject(error)
    })

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
