<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Sign in</h1>
                <p class="mt-2 text-gray-600">Enter your credentials to continue</p>
            </div>

            <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="input"
                            placeholder="you@example.com"
                            :disabled="loading"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="input"
                            placeholder="Your password"
                            :disabled="loading"
                        />
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-700">{{ error }}</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full" :disabled="loading">
                    <span v-if="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                    <span v-else>Sign in</span>
                </button>

                <!-- Register Link -->
                <p class="text-center text-sm text-gray-600">
                    Don't have an account?
                    <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Sign up
                    </router-link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const loading = ref(false)
const error = ref('')

const form = reactive({
    email: '',
    password: ''
})

const handleSubmit = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await axios.post('/auth/login', form)

        if (response.data.two_factor_required) {
            // Redirect to 2FA challenge
            router.push('/two-factor-challenge')
        } else {
            // No 2FA, direct login
            localStorage.setItem('auth_token', response.data.token)
            router.push('/dashboard')
        }
    } catch (err) {
        error.value = err.response?.data?.errors?.email?.[0] || err.response?.data?.message || 'Login failed'
    } finally {
        loading.value = false
    }
}
</script>
