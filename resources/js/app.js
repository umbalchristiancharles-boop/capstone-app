import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './app.vue'
import Index from './components/index.vue'
import AdminPanel from './components/adminpanel.vue'
import ManagerPanel from './components/ManagerPanel.vue'
import StaffPanel from './components/StaffPanel.vue'
import adminlogin from './components/adminlogin.vue'
import StaffList from './components/StaffList.vue'
import axios from 'axios'

axios.defaults.baseURL = 'http://localhost:8000'

// === ROUTER SETUP ===
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Index },
    { path: '/login', component: adminlogin },
    { path: '/admin-login', component: adminlogin },
    { path: '/admin-panel', component: AdminPanel, meta: { requiresAuth: true } },
    { path: '/manager-panel', component: ManagerPanel, meta: { requiresAuth: true } },
    { path: '/staff-panel', component: StaffPanel, meta: { requiresAuth: true } },
    {
      path:  '/admin/staff-management',
      component: StaffList,
      meta: { requiresAuth: true }
    },
  ],
})

// === GLOBAL GUARD PARA PROTECTED ANG /admin-panel ===
router. beforeEach(async (to, from, next) => {
  // ← UPDATE THIS LINE (add || to.meta.requiresAuth)
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

const app = createApp(App)
app.use(router)
app.mount('#app')
