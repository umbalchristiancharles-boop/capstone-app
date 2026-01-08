import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import Index from './components/index.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: Index },
        // Add more routes as needed
    ]
})

const app = createApp(Index)
app.use(router)
app.mount('#app')
