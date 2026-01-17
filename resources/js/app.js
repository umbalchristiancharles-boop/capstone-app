import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './app.vue'
import Index from './components/index.vue'
import AdminPanel from './components/adminpanel.vue'
import ManagerPanel from './components/ManagerPanel.vue'
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
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Index },
    { path: '/admin-panel', component: AdminPanel },
    { path: '/manager-panel', component: ManagerPanel },
    { path: '/admin-login', component: adminlogin },
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
  ],
})

// === GLOBAL GUARD PARA PROTECTED ANG /admin-panel ===
router.beforeEach(async (to, from, next) => {
  if (to.path === '/admin-panel' || to.meta.requiresAuth) {
    try {
      const res = await axios.get('/api/me', {
        withCredentials: true,
      })
      if (res.data.ok) {
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

    const app = createApp(App)
    app.use(router)
    app.mount('#app')
  })
