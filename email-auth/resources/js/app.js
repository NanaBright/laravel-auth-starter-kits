import './bootstrap';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';

// Import components
import MagicLinkLogin from './components/MagicLinkLogin.vue';
import VerifyEmail from './components/VerifyEmail.vue';
import Dashboard from './components/Dashboard.vue';
import Profile from './components/Profile.vue';

// Import stores
import { useAuthStore } from './stores/auth';

const routes = [
    {
        path: '/',
        name: 'home',
        component: MagicLinkLogin,
        meta: { requiresAuth: false }
    },
    {
        path: '/login',
        name: 'login',
        component: MagicLinkLogin,
        meta: { requiresAuth: false }
    },
    {
        path: '/verify-email',
        name: 'verify-email',
        component: VerifyEmail,
        meta: { requiresAuth: false }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.name === 'login' && authStore.isAuthenticated) {
        next('/dashboard');
    } else {
        next();
    }
});

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.mount('#app');
