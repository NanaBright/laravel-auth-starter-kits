<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Sidebar from './components/Sidebar.vue';

const router = useRouter();
const route = useRoute();

const user = ref(null);

const isAuthenticated = computed(() => {
    return !!localStorage.getItem('admin_token');
});

const showSidebar = computed(() => {
    return isAuthenticated.value && route.path !== '/login';
});

onMounted(() => {
    const storedUser = localStorage.getItem('admin_user');
    if (storedUser) {
        user.value = JSON.parse(storedUser);
    }
});

const handleLogout = async () => {
    localStorage.removeItem('admin_token');
    localStorage.removeItem('admin_user');
    router.push('/login');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <div v-if="showSidebar" class="flex">
            <Sidebar :user="user" @logout="handleLogout" />
            <main class="flex-1 ml-64">
                <router-view />
            </main>
        </div>
        <div v-else>
            <router-view />
        </div>
    </div>
</template>
