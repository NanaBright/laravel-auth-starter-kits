<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

const userId = route.params.id;
const user = ref(null);
const activityLogs = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');
const success = ref('');

const form = ref({
    name: '',
    email: '',
    password: '',
    is_admin: false,
    is_active: true
});

const fetchUser = async () => {
    try {
        const response = await axios.get(`/admin/users/${userId}`);
        user.value = response.data.user;
        activityLogs.value = response.data.recent_activity;

        form.value = {
            name: user.value.name,
            email: user.value.email,
            password: '',
            is_admin: user.value.is_admin,
            is_active: user.value.is_active
        };
    } catch (err) {
        error.value = 'Failed to load user';
    } finally {
        loading.value = false;
    }
};

const saveUser = async () => {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        const data = { ...form.value };
        if (!data.password) {
            delete data.password;
        }

        await axios.put(`/admin/users/${userId}`, data);
        success.value = 'User updated successfully';
        
        // Update local user data
        user.value.name = data.name;
        user.value.email = data.email;
        user.value.is_admin = data.is_admin;
        user.value.is_active = data.is_active;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update user';
    } finally {
        saving.value = false;
    }
};

const markVerified = async () => {
    try {
        await axios.post(`/admin/users/${userId}/mark-verified`);
        user.value.email_verified_at = new Date().toISOString();
        success.value = 'User marked as verified';
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to verify user';
    }
};

const deleteUser = async () => {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }

    try {
        await axios.delete(`/admin/users/${userId}`);
        router.push('/users');
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete user';
    }
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
};

onMounted(() => {
    fetchUser();
});
</script>

<template>
    <div class="p-8">
        <!-- Header -->
        <div class="flex items-center mb-8">
            <router-link to="/users" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </router-link>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="text-gray-600">Update user information</p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center h-64">
            <svg class="animate-spin h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <template v-else>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Edit form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <!-- Alerts -->
                        <div v-if="error" class="mb-4 bg-red-50 text-red-600 px-4 py-3 rounded-lg">
                            {{ error }}
                        </div>
                        <div v-if="success" class="mb-4 bg-green-50 text-green-600 px-4 py-3 rounded-lg">
                            {{ success }}
                        </div>

                        <form @submit.prevent="saveUser" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Password</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Leave blank to keep current"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <div class="flex items-center space-x-6">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_admin"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Admin privileges</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Active account</span>
                                </label>
                            </div>

                            <div class="flex justify-between pt-4">
                                <button type="button" @click="deleteUser" class="btn-danger">
                                    Delete User
                                </button>
                                <button type="submit" :disabled="saving" class="btn-primary">
                                    {{ saving ? 'Saving...' : 'Save Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- User info sidebar -->
                <div class="space-y-6">
                    <!-- User card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-3xl text-indigo-600 font-medium">{{ user.name.charAt(0) }}</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ user.name }}</h3>
                            <p class="text-gray-500">{{ user.email }}</p>
                            <div class="flex justify-center space-x-2 mt-3">
                                <span :class="['badge', user.is_active ? 'badge-success' : 'badge-danger']">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span :class="['badge', user.is_admin ? 'badge-info' : 'badge-warning']">
                                    {{ user.is_admin ? 'Admin' : 'User' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-medium text-gray-900 mb-4">Account Info</h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Joined</dt>
                                <dd class="text-gray-900">{{ formatDate(user.created_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Last Login</dt>
                                <dd class="text-gray-900">{{ formatDate(user.last_login_at) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Login Count</dt>
                                <dd class="text-gray-900">{{ user.login_count }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500">Email Verified</dt>
                                <dd>
                                    <span v-if="user.email_verified_at" class="text-green-600">
                                        {{ formatDate(user.email_verified_at) }}
                                    </span>
                                    <button v-else @click="markVerified" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                        Mark Verified
                                    </button>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Activity log -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-medium text-gray-900 mb-4">Recent Activity</h4>
                        <div v-if="activityLogs.length === 0" class="text-gray-500 text-sm">
                            No activity recorded
                        </div>
                        <ul v-else class="space-y-3">
                            <li v-for="log in activityLogs" :key="log.id" class="text-sm">
                                <div class="flex items-start">
                                    <span class="badge badge-info mr-2">{{ log.action }}</span>
                                    <div>
                                        <p class="text-gray-700">{{ log.description }}</p>
                                        <p class="text-gray-400 text-xs">{{ formatDate(log.created_at) }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
