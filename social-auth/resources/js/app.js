import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './bootstrap'

// Import pages
import Login from './pages/Login.vue'
import Callback from './pages/Callback.vue'
import Dashboard from './pages/Dashboard.vue'
import ConnectedAccounts from './pages/ConnectedAccounts.vue'

// Define routes
const routes = [
    {
        path: '/',
        redirect: '/login'
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/auth/callback',
        name: 'callback',
        component: Callback
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { auth: true }
    },
    {
        path: '/settings/accounts',
        name: 'connected-accounts',
        component: ConnectedAccounts,
        meta: { auth: true }
    }
]

// Create router
const router = createRouter({
    history: createWebHistory(),
    routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token')
    
    if (to.meta.auth && !token) {
        next({ name: 'login' })
    } else if (to.meta.guest && token) {
        next({ name: 'dashboard' })
    } else {
        next()
    }
})

// Create and mount app
const app = createApp(App)
app.use(router)
app.mount('#app')
