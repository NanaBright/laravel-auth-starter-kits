<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const form = ref({
    email: '',
    password: ''
});

const loading = ref(false);
const error = ref('');

const login = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.post('/auth/login', form.value);
        
        localStorage.setItem('admin_token', response.data.token);
        localStorage.setItem('admin_user', JSON.stringify(response.data.user));
        
        router.push('/');
    } catch (err) {
        error.value = err.response?.data?.message || 'Login failed. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Admin Login</h1>
                    <p class="text-gray-600 mt-2">Sign in to access the dashboard</p>
                </div>

                <form @submit.prevent="login" class="space-y-6">
                    <div v-if="error" class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm">
                        {{ error }}
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="admin@example.com"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter your password"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full btn-primary py-3 disabled:opacity-50"
                    >
                        <span v-if="loading">Signing in...</span>
                        <span v-else>Sign In</span>
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>Default credentials:</p>
                    <p class="font-mono">admin@example.com / password</p>
                </div>
            </div>
        </div>
    </div>
</template>
