import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './app.vue'
import CustomerIndex from './components/CustomerIndex.vue'
import StaffIndex from './components/StaffIndex.vue'
import AdminPanel from './components/adminpanel.vue'
import adminlogin from './components/adminlogin.vue'
import StaffList from './components/StaffList.vue'
import OwnerStaffManagement from './components/OwnerStaffManagement.vue'
import DeletedStaffList from './components/DeletedStaffList.vue'
import ManagerInventoryPanel from './components/ManagerInventoryPanel.vue'
import ManagerFinancePanel from './components/ManagerFinancePanel.vue'
import ManagerLogisticsPanel from './components/ManagerLogisticsPanel.vue'
import SupplierPanel from './components/SupplierPanel.vue'
import ManagerHRPanel from './components/ManagerHRPanel.vue'
import ManagerHRStaffManagement from './components/ManagerHRStaffManagement.vue'
import ManagerProcurementPanel from './components/ProcurementManagerPanel.vue'
import StaffCashierPanel from './components/StaffCashierPanel.vue'
import SuperAdmin from './components/SuperAdmin.vue'
import axios from 'axios'

// SweetAlert2 wrapper (exposes `swalAlert`, `swalConfirm`, `swalPrompt`, and replaces `window.alert` visually)
import './sweet-alerts'

// GLOBAL CSS (body margin reset, etc.)
import './css/index.css'

// === AXIOS GLOBAL CONFIG ===
axios.defaults.baseURL = '' // use relative URLs so requests go to current origin
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

// Set Authorization header from token if available (set after login)
function setAuthToken() {
  const token = localStorage.getItem('token')
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
}

// Initialize auth token on app load
setAuthToken()

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
function getCookie(name) {
  try {
    const v = document.cookie.split('; ').find(row => row.startsWith(name + '='))
    if (!v) return null
    return decodeURIComponent(v.split('=')[1])
  } catch (e) {
    return null
  }
}

axios.interceptors.request.use(config => {
  // Prefer XSRF token from cookie (set by /sanctum/csrf-cookie) to avoid stale meta tokens
  const xsrf = getCookie('XSRF-TOKEN')
  if (xsrf) {
    config.headers['X-XSRF-TOKEN'] = xsrf
  } else {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken
    }
  }
  // If there is no laravel session cookie, avoid sending any stored Authorization header
  // This prevents sending a stale Bearer token for endpoints expecting session (web) auth
  try {
    const laravelSession = getCookie('laravel_session')
    // Preserve Authorization header for API requests (Sanctum token auth).
    // Only remove the Authorization header for non-API requests when there is
    // no session cookie to avoid accidentally stripping Bearer tokens used
    // for stateless API endpoints.
    // Check if URL is an API request (starts with /api or includes /api/)
    const url = String(config.url || '')
    const isApiRequest = url.startsWith('/api') || url.includes('/api/')
    if (!laravelSession && config && config.headers && config.headers['Authorization'] && !isApiRequest) {
      delete config.headers['Authorization']
    }
  } catch (e) {}
  
  // Also ensure Bearer token is always sent for API requests
  try {
    const url = String(config.url || '')
    if ((url.startsWith('/api') || url.includes('/api/')) && !config.headers['Authorization']) {
      const token = localStorage.getItem('token')
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`
      }
    }
  } catch (e) {}
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
  // Handle 419 CSRF Token Mismatch by fetching a fresh CSRF cookie and retrying once
  const resp = error && error.response
  const req = error && error.config
  if (resp && resp.status === 419 && req && !req._retry) {
    req._retry = true
    console.warn('[AXIOS] 419 detected - fetching /sanctum/csrf-cookie and retrying request')
    return axios.get('/sanctum/csrf-cookie', { withCredentials: true }).then(() => {
      // Re-attach X-XSRF-TOKEN header from cookie if present
      const xsrf = (function(){ try { return decodeURIComponent(document.cookie.split('; ').find(r=>r.startsWith('XSRF-TOKEN='))?.split('=')[1]||'') } catch(e){return ''} })()
      if (xsrf) req.headers['X-XSRF-TOKEN'] = xsrf
      return axios(req)
    }).catch(err => {
      return Promise.reject(err)
    })
  }

  return Promise.reject(error)
})

// === ROUTER SETUP ===
const ResetPassword = () => import('./components/ResetPassword.vue');

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: CustomerIndex },
    { path: '/staff-landing', component: StaffIndex },
    { path: '/login', component: adminlogin },
    { path: '/admin-login', component: adminlogin },
    { path: '/admin-panel', component: AdminPanel },
    { path: '/super-admin-panel', component: SuperAdmin, meta: { requiresAuth: true } },
    { path: '/super-admin/dashboard', redirect: '/super-admin-panel' },
{ path: '/super-admin/hr', component: () => import('./components/HRStaffManagement.vue'), meta: { requiresAuth: true } },
{ path: '/super-admin/logistics', component: () => import('./components/SuperAdminLogisticsPanel.vue'), meta: { requiresAuth: true } },
  { path: '/super-admin/procurement', component: () => import('./components/SuperAdminProcurement.vue'), meta: { requiresAuth: true } },
    { path: '/super-admin/finance', component: () => import('./components/SuperAdminFinance.vue'), meta: { requiresAuth: true } },
    { path: '/super-admin/cashier', component: () => import('./components/Cashier.vue'), meta: { requiresAuth: true } },
    { path: '/main-branch/admin', component: () => import('./components/MainBranchAdminPanel.vue'), meta: { requiresAuth: true } },
    { path: '/main-branch/hr', component: () => import('./components/MainBranchHrPanel.vue'), meta: { requiresAuth: true } },
    { path: '/main-branch/finance', component: () => import('./components/MainBranchFinancePanel.vue'), meta: { requiresAuth: true } },
    { path: '/main-branch/logistics', component: () => import('./components/MainBranchLogisticsPanel.vue'), meta: { requiresAuth: true } },
    { path: '/main-branch/branches', component: () => import('./components/OwnerAddBranches.vue'), meta: { requiresAuth: true } },
    { path: '/manager-panel', component: AdminPanel, meta: { requiresAuth: true } },
    { path: '/manager/inventory', component: ManagerInventoryPanel, meta: { requiresAuth: true } },
    { path: '/manager/finance', component: ManagerFinancePanel, meta: { requiresAuth: true } },
    { path: '/manager/logistics', component: ManagerLogisticsPanel, meta: { requiresAuth: true } },
    { path: '/manager/logistics/suppliers', component: SupplierPanel, meta: { requiresAuth: true } },
{ path: '/manager/hr', component: ManagerHRPanel, meta: { requiresAuth: true } },
  { path: '/manager/procurement', component: ManagerProcurementPanel, meta: { requiresAuth: true } },
    { path: '/manager/hr/staff-management', component: ManagerHRStaffManagement, meta: { requiresAuth: true } },
    { path: '/staff-panel', component: StaffList },
    { path: '/staff/cashier', component: StaffCashierPanel, meta: { requiresAuth: true } },
    { path: '/staff/finance', component: () => import('./components/StaffFinancePanel.vue'), meta: { requiresAuth: true } },
    { path: '/staff/inventory', component: () => import('./components/inventory/InventoryStaffPanel.vue'), meta: { requiresAuth: true } },
    { path: '/staff/kitchen', component: () => import('./components/KitchenStaffPanel.vue'), meta: { requiresAuth: true } },
    { path: '/supplier-panel', component: SupplierPanel, meta: { requiresAuth: true } },
    { path: '/owner-panel', component: AdminPanel },
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
    // Unauthorized page for role-based access denied
    {
      path: '/unauthorized',
      name: 'Unauthorized',
      component: () => import('./components/Unauthorized.vue'),
      meta: { requiresAuth: false },
    },
    // Change password page for forced password changes
    {
      path: '/change-password',
      name: 'ChangePassword',
      component: () => import('./components/ChangePasswordPage.vue'),
      meta: { requiresAuth: false },
    },
    // Verify email page after forced password change
    {
      path: '/verify-email',
      name: 'VerifyEmail',
      component: () => import('./components/VerifyEmailPage.vue'),
      meta: { requiresAuth: true },
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

// Intercept non-SPA navigations (regular <a> clicks and form submits)
// to show the same route overlay used by the SPA so full-page redirects
// feel smooth. Skips external links, _blank targets, download links,
// and links with data-no-overlay attribute.
function shouldInterceptLink(anchor) {
  try {
    if (!anchor || !anchor.href) return false
    if (anchor.target && anchor.target !== '_self') return false
    if (anchor.hasAttribute('download')) return false
    if (anchor.dataset && anchor.dataset.noOverlay === '1') return false
    const url = new URL(anchor.href, window.location.href)
    if (url.origin !== window.location.origin) return false
    // allow mailto/tel and hash-only anchors to proceed normally
    if (url.protocol === 'mailto:' || url.protocol === 'tel:') return false
    return true
  } catch (e) { return false }
}

function attachGlobalNavInterceptors() {
  if (window.__nav_interceptors_attached) return
  window.__nav_interceptors_attached = true
  // Link clicks
  document.addEventListener('click', (ev) => {
    const a = ev.target.closest && ev.target.closest('a')
    if (!a) return
    if (!shouldInterceptLink(a)) return
    // If the router can handle this path, allow SPA navigation
    const href = a.getAttribute('href') || a.href
    // Skip fragment-only links
    if (href.startsWith('#')) return

    // show overlay and then navigate
    ev.preventDefault()
    try { showRouteOverlay('Loading...') } catch (e) {}
    try { showPageBlur() } catch (e) {}
    // small delay to let CSS show overlay, then navigate
    setTimeout(() => { window.location.href = href }, 140)
  }, { capture: true })

  // Form submissions that will trigger full page reload
  document.addEventListener('submit', (ev) => {
    const form = ev.target
    if (!form || !(form instanceof HTMLFormElement)) return
    // If form has attribute data-no-overlay, skip
    if (form.dataset && form.dataset.noOverlay === '1') return
    try { showRouteOverlay('Submitting...') } catch (e) {}
    try { showPageBlur() } catch (e) {}
    // allow submission to proceed normally
  }, { capture: true })
}

// Add page-exit class right before unload for a quick fade-out on full reloads
window.addEventListener('beforeunload', () => {
  try { document.documentElement.classList.add('page-exit') } catch (e) {}
})

// On DOM ready remove any exit class to allow a fade-in effect
document.addEventListener('DOMContentLoaded', () => {
  try { document.documentElement.classList.remove('page-exit') } catch (e) {}
  try { attachGlobalNavInterceptors() } catch (e) {}
})

// --- Global route loading overlay ---
const routeLogoUrl = new URL('./assets/chikinlogo.png', import.meta.url).href

function showRouteOverlay(text = 'Loading...') {
  try {
    if (window.__chikin_temp_overlay || window.__route_loading_overlay) return
    if (document.querySelector('.route-loading-overlay')) return
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
    }, 480)
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
    }, 420)
    setTimeout(() => {
      hideRouteOverlay()
    }, 420)
  })

  // Refresh CSRF cookie after navigation to protected panels to avoid 419s
  try {
    const protectedPaths = ['/admin-panel', '/manager-panel', '/staff-panel', '/hr-panel', '/admin', '/manager', '/staff', '/hr', '/super-admin']
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
  // Public routes - allow always (including unauthorized and staff landing)
  if (to.path === '/' || to.path === '/admin-login' || to.path === '/login' || to.path === '/staff-landing' || to.path === '/unauthorized') {
    return next()
  }

  // CRITICAL: Get user from localStorage for strict role checking
  let user = null;
  try {
    user = JSON.parse(localStorage.getItem('user') || 'null');
    // Normalize role to lowercase for comparison (database has uppercase: ADMIN, MANAGER, OWNER, STAFF)
    if (user) {
      user.role = (user.role || '').toLowerCase();
    }
  } catch (e) {
    console.warn('[ROUTER] Failed to parse user from localStorage:', e);
  }

  // Protected panel routes - require authentication
  const protectedRoutes = ['/admin-panel', '/manager-panel', '/staff-panel', '/hr-panel', '/staff-management', '/owner/staff-management', '/admin/deleted-staff', '/manager/staff', '/super-admin', '/supplier-panel']
  const isProtectedRoute = protectedRoutes.some(route => to.path.startsWith(route)) || to.meta.requiresAuth

  if (isProtectedRoute) {
    // If no user in localStorage, redirect to login
    if (!user) {
      console.warn('[ROUTER] No user in localStorage, redirecting to staff landing');
      return next('/staff-landing');
    }

    // STRICT ROLE CHECK - Manager Inventory should only access /manager/inventory
    if (to.path.startsWith('/manager/inventory')) {
      if (user.role !== 'manager' || user.department !== 'inventory') {
        console.warn('[ROUTER] Manager Inventory - wrong role/department:', user);
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/manager/finance')) {
      if (user.role !== 'manager' || user.department !== 'finance') {
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/manager/logistics')) {
      if (user.role !== 'manager' || user.department !== 'logistics') {
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/manager/hr')) {
      if (user.role !== 'manager' || user.department !== 'hr') {
        return next('/unauthorized');
      }
      // Allow navigation to staff-management sub-route
      return next();
    }

    if (to.path.startsWith('/manager/procurement')) {
      if (user.role !== 'manager' || user.department !== 'procurement') {
        return next('/unauthorized');
      }
    }

    // Staff Inventory should only access /staff/inventory
    // Supplier panel access
    if (to.path.startsWith('/supplier-panel')) {
      if (user.role !== 'supplier') {
        console.warn('[ROUTER] Supplier panel - wrong role:', user);
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/staff/inventory')) {
      if (user.role !== 'staff' || user.department !== 'inventory') {
        console.warn('[ROUTER] Staff Inventory - wrong role/department:', user);
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/staff/cashier')) {
      if (user.role !== 'staff' || user.department !== 'cashier') {
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/staff/finance')) {
      if (user.role !== 'staff' || user.department !== 'finance') {
        return next('/unauthorized');
      }
    }

    // Owner panel - allow OWNER users and also SUPER_ADMIN users to access owner routes
    if (to.path.startsWith('/owner-panel') || to.path.startsWith('/owner/')) {
      if (!(user.role === 'owner' || user.role === 'super_admin' || user.role === 'superadmin')) {
        return next('/unauthorized');
      }
    }

    // Admin panel - authenticated but not admin → redirect to unauthorized
    if (to.path === '/admin-panel') {
      if (user.role !== 'admin') {
        return next('/unauthorized');
      }
    }

    // Main Branch role pages
    if (to.path.startsWith('/main-branch/admin')) {
      if (user.role !== 'admin') {
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/main-branch/hr')) {
      if (user.role !== 'manager' || user.department !== 'hr') {
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/main-branch/finance')) {
      if (user.role !== 'manager' || user.department !== 'finance') {
        return next('/unauthorized');
      }
    }
    if (to.path.startsWith('/main-branch/logistics')) {
      if (user.role !== 'manager' || user.department !== 'logistics') {
        return next('/unauthorized');
      }
    }

    // Super Admin panel and sub-routes - authenticated but not superadmin → redirect to unauthorized
    if (to.path === '/super-admin-panel' || to.path.startsWith('/super-admin/')) {
      const userRoleUpper = (user.role || '').toUpperCase();
      if (userRoleUpper !== 'SUPER_ADMIN' && userRoleUpper !== 'SUPERADMIN') {
        return next('/unauthorized');
      }
    }

    // Clear reload flag when navigating away from admin/manager panel
    if (from && (from.path === '/admin-panel' || from.path === '/manager-panel')) {
      try { sessionStorage.removeItem('appReloaded') } catch (e) {}
    }

    // FIXED: Removed problematic auto-reload that was causing logout for non-admin roles
    // The reload was unnecessary and caused session/localStorage to be lost
    // CSRF token is now properly handled by axios interceptor

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
    // FIXED: Removed problematic auto-reload on page load that was causing logout
    // CSRF token is handled by axios interceptor, no page reload needed

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
