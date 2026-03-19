import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './bootstrap'

// Import pages
import Login from './pages/Login.vue'
import Register from './pages/Register.vue'
import TwoFactorChallenge from './pages/TwoFactorChallenge.vue'
import Dashboard from './pages/Dashboard.vue'
import SecuritySettings from './pages/SecuritySettings.vue'

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
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true }
    },
    {
        path: '/two-factor-challenge',
        name: 'two-factor-challenge',
        component: TwoFactorChallenge
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { auth: true }
    },
    {
        path: '/settings/security',
        name: 'security-settings',
        component: SecuritySettings,
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
