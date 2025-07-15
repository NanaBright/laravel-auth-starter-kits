<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <div class="flex-shrink-0 flex items-center">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </div>
              <span class="ml-2 text-xl font-bold text-gray-900">Email Auth</span>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              to="/dashboard"
              class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              Dashboard
            </router-link>
            <button
              @click="logout"
              class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Profile</h1>
            
            <!-- Profile Form -->
            <form @submit.prevent="updateProfile" class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Full Name
                  </label>
                  <div class="mt-1">
                    <input
                      id="name"
                      v-model="form.name"
                      name="name"
                      type="text"
                      class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="Enter your full name"
                    />
                  </div>
                </div>

                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">
                    Email Address
                  </label>
                  <div class="mt-1">
                    <input
                      id="email"
                      v-model="form.email"
                      name="email"
                      type="email"
                      class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="Enter your email address"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label for="bio" class="block text-sm font-medium text-gray-700">
                  Bio
                </label>
                <div class="mt-1">
                  <textarea
                    id="bio"
                    v-model="form.bio"
                    name="bio"
                    rows="3"
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Tell us about yourself"
                  />
                </div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="loading"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ loading ? 'Updating...' : 'Update Profile' }}
                </button>
              </div>
            </form>

            <!-- Success Message -->
            <div v-if="successMessage" class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
              <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                  <p class="text-sm">{{ successMessage }}</p>
                </div>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
              <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                  <p class="text-sm">{{ errorMessage }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Information -->
        <div class="mt-6 bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Account Information</h2>
            
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user?.created_at) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                <dd class="mt-1 text-sm">
                  <span :class="user?.email_verified_at ? 'text-green-600' : 'text-red-600'">
                    {{ user?.email_verified_at ? 'Yes' : 'No' }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user?.updated_at) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                <dd class="mt-1 text-sm">
                  <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                    Active
                  </span>
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Danger Zone -->
        <div class="mt-6 bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Danger Zone</h2>
            
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
              <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">Delete Account</h3>
                  <p class="mt-1 text-sm text-red-700">
                    Once you delete your account, there is no going back. Please be certain.
                  </p>
                  <div class="mt-4">
                    <button
                      @click="confirmDeleteAccount"
                      class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700"
                    >
                      Delete Account
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

export default {
  name: 'Profile',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    
    const user = ref(null);
    const loading = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');
    
    const form = ref({
      name: '',
      email: '',
      bio: ''
    });

    const updateProfile = async () => {
      loading.value = true;
      successMessage.value = '';
      errorMessage.value = '';

      try {
        const response = await window.axios.put('/user/profile', form.value);
        
        if (response.data.success) {
          successMessage.value = 'Profile updated successfully!';
          await authStore.fetchUser();
          user.value = authStore.user;
        }
      } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Failed to update profile. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const confirmDeleteAccount = () => {
      if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        deleteAccount();
      }
    };

    const deleteAccount = async () => {
      try {
        await window.axios.delete('/user/profile');
        await authStore.logout();
        router.push('/login');
      } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Failed to delete account. Please try again.';
      }
    };

    const logout = async () => {
      await authStore.logout();
      router.push('/login');
    };

    const formatDate = (date) => {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    onMounted(async () => {
      await authStore.fetchUser();
      user.value = authStore.user;
      
      if (user.value) {
        form.value = {
          name: user.value.name || '',
          email: user.value.email || '',
          bio: user.value.bio || ''
        };
      }
    });

    return {
      user,
      form,
      loading,
      successMessage,
      errorMessage,
      updateProfile,
      confirmDeleteAccount,
      logout,
      formatDate
    };
  }
};
</script>
