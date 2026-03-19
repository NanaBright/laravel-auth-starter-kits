<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const user = ref(null);
const loading = ref(true);
const saving = ref(false);
const success = ref('');
const error = ref('');

const form = ref({
    name: '',
    email: '',
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const fetchProfile = async () => {
    try {
        const response = await axios.get('/auth/user');
        user.value = response.data;
        form.value.name = user.value.name;
        form.value.email = user.value.email;
    } catch (err) {
        error.value = 'Failed to load profile';
    } finally {
        loading.value = false;
    }
};

const updateProfile = async () => {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        const data = {
            name: form.value.name,
            email: form.value.email
        };

        if (form.value.new_password) {
            data.current_password = form.value.current_password;
            data.new_password = form.value.new_password;
        }

        const response = await axios.put('/auth/profile', data);
        
        // Update local storage
        const stored = JSON.parse(localStorage.getItem('admin_user') || '{}');
        stored.name = response.data.user.name;
        stored.email = response.data.user.email;
        localStorage.setItem('admin_user', JSON.stringify(stored));

        // Clear password fields
        form.value.current_password = '';
        form.value.new_password = '';
        form.value.new_password_confirmation = '';

        success.value = 'Profile updated successfully';
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update profile';
    } finally {
        saving.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
};

onMounted(() => {
    fetchProfile();
});
</script>

<template>
    <div class="p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Profile</h1>
            <p class="text-gray-600">Manage your account settings</p>
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
                <!-- Profile form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Account Information</h3>

                        <!-- Alerts -->
                        <div v-if="error" class="mb-4 bg-red-50 text-red-600 px-4 py-3 rounded-lg">
                            {{ error }}
                        </div>
                        <div v-if="success" class="mb-4 bg-green-50 text-green-600 px-4 py-3 rounded-lg">
                            {{ success }}
                        </div>

                        <form @submit.prevent="updateProfile" class="space-y-6">
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

                            <hr class="my-6" />

                            <h4 class="text-md font-medium text-gray-900">Change Password</h4>
                            <p class="text-sm text-gray-500 mb-4">Leave blank to keep current password</p>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input
                                    v-model="form.current_password"
                                    type="password"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Password</label>
                                <input
                                    v-model="form.new_password"
                                    type="password"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="saving" class="btn-primary">
                                    {{ saving ? 'Saving...' : 'Save Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Profile sidebar -->
                <div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center mb-6">
                            <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-4xl text-indigo-600 font-medium">{{ user.name.charAt(0) }}</span>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900">{{ user.name }}</h3>
                            <p class="text-gray-500">{{ user.email }}</p>
                            <span class="badge badge-info mt-2">Administrator</span>
                        </div>

                        <hr class="my-6" />

                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Member Since</dt>
                                <dd class="text-gray-900 font-medium">{{ formatDate(user.created_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Last Login</dt>
                                <dd class="text-gray-900 font-medium">{{ formatDate(user.last_login_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
