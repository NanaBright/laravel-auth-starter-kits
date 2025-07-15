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
              to="/profile"
              class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              Profile
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
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>
            
            <!-- Welcome Message -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
              <div class="flex">
                <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800">Welcome back!</h3>
                  <p class="mt-1 text-sm text-blue-700">
                    You have successfully logged in using magic link authentication.
                  </p>
                </div>
              </div>
            </div>

            <!-- User Information -->
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
              <dt class="text-sm font-medium text-gray-500">User Information</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                <div class="space-y-3">
                  <div v-if="user">
                    <span class="font-medium">Email:</span> {{ user.email }}
                  </div>
                  <div v-if="user">
                    <span class="font-medium">Member since:</span> {{ formatDate(user.created_at) }}
                  </div>
                  <div v-if="user">
                    <span class="font-medium">Email verified:</span> 
                    <span :class="user.email_verified_at ? 'text-green-600' : 'text-red-600'">
                      {{ user.email_verified_at ? 'Yes' : 'No' }}
                    </span>
                  </div>
                </div>
              </dd>
            </div>

            <!-- Statistics -->
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
              <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Authentication Method
                        </dt>
                        <dd class="text-lg font-medium text-gray-900">
                          Magic Link
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Security Status
                        </dt>
                        <dd class="text-lg font-medium text-green-600">
                          Secure
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Last Login
                        </dt>
                        <dd class="text-lg font-medium text-gray-900">
                          {{ formatDate(new Date()) }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <router-link
                  to="/profile"
                  class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow"
                >
                  <div>
                    <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-700 ring-4 ring-white">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </span>
                  </div>
                  <div class="mt-4">
                    <h3 class="text-lg font-medium text-gray-900">
                      Update Profile
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                      Manage your account information and preferences
                    </p>
                  </div>
                </router-link>

                <div class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow">
                  <div>
                    <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-700 ring-4 ring-white">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                      </svg>
                    </span>
                  </div>
                  <div class="mt-4">
                    <h3 class="text-lg font-medium text-gray-900">
                      Security Settings
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                      Review your security settings and activity
                    </p>
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
  name: 'Dashboard',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    const user = ref(null);

    const logout = async () => {
      await authStore.logout();
      router.push('/login');
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    onMounted(async () => {
      await authStore.fetchUser();
      user.value = authStore.user;
    });

    return {
      user,
      logout,
      formatDate
    };
  }
};
</script>
