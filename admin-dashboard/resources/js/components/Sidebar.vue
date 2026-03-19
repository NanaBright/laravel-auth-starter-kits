<script setup>
import { useRoute, useRouter } from 'vue-router';
import { computed } from 'vue';

const props = defineProps({
    user: Object
});

const emit = defineEmits(['logout']);

const route = useRoute();
const router = useRouter();

const navItems = [
    { path: '/', label: 'Dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { path: '/users', label: 'Users', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z' },
    { path: '/logs', label: 'Activity Logs', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
];

const isActive = (path) => {
    if (path === '/') {
        return route.path === '/';
    }
    return route.path.startsWith(path);
};

const logout = () => {
    emit('logout');
};
</script>

<template>
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 bg-gray-900">
                <span class="text-xl font-bold">Admin Dashboard</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 mt-4">
                <router-link
                    v-for="item in navItems"
                    :key="item.path"
                    :to="item.path"
                    :class="['sidebar-link', { active: isActive(item.path) }]"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </router-link>
            </nav>

            <!-- User section -->
            <div class="border-t border-gray-700 p-4">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-lg font-medium">{{ user?.name?.charAt(0) || 'A' }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ user?.name || 'Admin' }}</p>
                        <p class="text-xs text-gray-400">{{ user?.email || '' }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <router-link to="/profile" class="flex-1 text-center py-2 text-sm bg-gray-700 rounded hover:bg-gray-600 transition">
                        Profile
                    </router-link>
                    <button @click="logout" class="flex-1 py-2 text-sm bg-red-600 rounded hover:bg-red-700 transition">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </aside>
</template>
