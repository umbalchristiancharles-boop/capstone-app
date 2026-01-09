import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './app.vue'
import Index from './components/index.vue'
import AdminPanel from './components/adminpanel.vue'
import adminlogin from './components/adminlogin.vue'
import axios from 'axios'

axios.defaults.baseURL = 'http://localhost:8000'

// === ROUTER SETUP ===
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Index },
    { path: '/admin-panel', component: AdminPanel },
    { path: '/admin-login', component: adminlogin },
  ],
})

// === GLOBAL GUARD PARA PROTECTED ANG /admin-panel ===
router.beforeEach(async (to, from, next) => {
  if (to.path === '/admin-panel') {
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
