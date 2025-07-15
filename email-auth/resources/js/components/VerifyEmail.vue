<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="flex items-center">
          <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h2 class="ml-3 text-2xl font-bold text-gray-900">Email Auth</h2>
        </div>
      </div>
      <h3 class="mt-6 text-center text-xl font-medium text-gray-700">
        Verify Your Email
      </h3>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <!-- Loading State -->
        <div v-if="loading" class="text-center">
          <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-gray-600">Verifying your email...</p>
        </div>

        <!-- Success State -->
        <div v-else-if="verified" class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Email Verified!</h3>
          <p class="text-gray-600 mb-6">You have been successfully logged in.</p>
          <button
            @click="goToDashboard"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Go to Dashboard
          </button>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center">
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Verification Failed</h3>
          <p class="text-gray-600 mb-6">{{ errorMessage }}</p>
          <div class="space-y-3">
            <button
              @click="goToLogin"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Try Again
            </button>
            <button
              @click="resendMagicLink"
              :disabled="resendLoading"
              class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ resendLoading ? 'Sending...' : 'Resend Magic Link' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

export default {
  name: 'VerifyEmail',
  setup() {
    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();
    
    const loading = ref(true);
    const verified = ref(false);
    const error = ref(false);
    const errorMessage = ref('');
    const resendLoading = ref(false);

    const verifyMagicLink = async () => {
      const token = route.query.token;
      
      if (!token) {
        error.value = true;
        errorMessage.value = 'Invalid verification link';
        loading.value = false;
        return;
      }

      try {
        const response = await window.axios.post('/auth/verify-magic-link', {
          token: token
        });

        if (response.data.success) {
          // Store the token
          localStorage.setItem('token', response.data.token);
          
          // Update auth store
          await authStore.fetchUser();
          
          verified.value = true;
          
          // Redirect to dashboard after 2 seconds
          setTimeout(() => {
            router.push('/dashboard');
          }, 2000);
        }
      } catch (error) {
        this.error = true;
        if (error.response?.status === 400) {
          errorMessage.value = 'Invalid or expired verification link';
        } else {
          errorMessage.value = error.response?.data?.message || 'Verification failed. Please try again.';
        }
      } finally {
        loading.value = false;
      }
    };

    const goToDashboard = () => {
      router.push('/dashboard');
    };

    const goToLogin = () => {
      router.push('/login');
    };

    const resendMagicLink = async () => {
      const email = route.query.email;
      
      if (!email) {
        router.push('/login');
        return;
      }

      resendLoading.value = true;

      try {
        await window.axios.post('/auth/send-magic-link', {
          email: email
        });
        
        // Redirect to login with success message
        router.push('/login');
      } catch (error) {
        console.error('Failed to resend magic link:', error);
      } finally {
        resendLoading.value = false;
      }
    };

    onMounted(() => {
      verifyMagicLink();
    });

    return {
      loading,
      verified,
      error,
      errorMessage,
      resendLoading,
      goToDashboard,
      goToLogin,
      resendMagicLink
    };
  }
};
</script>
