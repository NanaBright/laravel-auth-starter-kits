<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_admin: false,
    is_active: true,
    mark_verified: false
});

const saving = ref(false);
const error = ref('');
const errors = ref({});

const createUser = async () => {
    saving.value = true;
    error.value = '';
    errors.value = {};

    try {
        await axios.post('/admin/users', form.value);
        router.push('/users');
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {};
        }
        error.value = err.response?.data?.message || 'Failed to create user';
    } finally {
        saving.value = false;
    }
};
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
                <h1 class="text-2xl font-bold text-gray-900">Create User</h1>
                <p class="text-gray-600">Add a new user account</p>
            </div>
        </div>

        <div class="max-w-2xl">
            <div class="bg-white rounded-lg shadow p-6">
                <!-- Error alert -->
                <div v-if="error" class="mb-4 bg-red-50 text-red-600 px-4 py-3 rounded-lg">
                    {{ error }}
                </div>

                <form @submit.prevent="createUser" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            :class="{ 'border-red-500': errors.name }"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            :class="{ 'border-red-500': errors.email }"
                        />
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            :class="{ 'border-red-500': errors.password }"
                        />
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
                    </div>

                    <div class="space-y-3">
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
                        <label class="flex items-center">
                            <input
                                v-model="form.mark_verified"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span class="ml-2 text-sm text-gray-700">Mark email as verified</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <router-link to="/users" class="btn-secondary">
                            Cancel
                        </router-link>
                        <button type="submit" :disabled="saving" class="btn-primary">
                            {{ saving ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
