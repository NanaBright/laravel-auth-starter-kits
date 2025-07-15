<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="flex items-center">
          <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
          </div>
          <h2 class="ml-3 text-2xl font-bold text-gray-900">Email Auth</h2>
        </div>
      </div>
      <h3 class="mt-6 text-center text-xl font-medium text-gray-700">
        Magic Link Authentication
      </h3>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form @submit.prevent="sendMagicLink" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Enter your email address"
              />
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </span>
              {{ loading ? 'Sending...' : 'Send Magic Link' }}
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

        <!-- Rate Limit Message -->
        <div v-if="rateLimitMessage" class="mt-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-md">
          <div class="flex">
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div class="ml-3">
              <p class="text-sm">{{ rateLimitMessage }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'MagicLinkLogin',
  setup() {
    const router = useRouter();
    const email = ref('');
    const loading = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');
    const rateLimitMessage = ref('');

    const sendMagicLink = async () => {
      if (!email.value) {
        errorMessage.value = 'Please enter your email address';
        return;
      }

      loading.value = true;
      successMessage.value = '';
      errorMessage.value = '';
      rateLimitMessage.value = '';

      try {
        const response = await window.axios.post('/auth/send-magic-link', {
          email: email.value
        });

        if (response.data.success) {
          successMessage.value = 'Magic link sent! Please check your email.';
          // Clear form
          email.value = '';
        }
      } catch (error) {
        if (error.response?.status === 429) {
          rateLimitMessage.value = 'Too many requests. Please wait before trying again.';
        } else if (error.response?.status === 422) {
          errorMessage.value = error.response.data.errors?.email?.[0] || 'Invalid email address';
        } else {
          errorMessage.value = error.response?.data?.message || 'An error occurred. Please try again.';
        }
      } finally {
        loading.value = false;
      }
    };

    return {
      email,
      loading,
      successMessage,
      errorMessage,
      rateLimitMessage,
      sendMagicLink
    };
  }
};
</script>
