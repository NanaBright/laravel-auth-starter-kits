<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Sign in</h1>
                <p class="mt-2 text-gray-600">Enter your email or phone to receive a verification code</p>
            </div>

            <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <!-- Channel Selection -->
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                        <button
                            type="button"
                            @click="channel = 'email'"
                            :class="[
                                'flex-1 py-3 text-sm font-medium transition-colors',
                                channel === 'email' 
                                    ? 'bg-indigo-600 text-white' 
                                    : 'bg-white text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            Email
                        </button>
                        <button
                            type="button"
                            @click="channel = 'sms'"
                            :class="[
                                'flex-1 py-3 text-sm font-medium transition-colors',
                                channel === 'sms' 
                                    ? 'bg-indigo-600 text-white' 
                                    : 'bg-white text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            SMS
                        </button>
                    </div>

                    <!-- Email Input -->
                    <div v-if="channel === 'email'">
                        <label for="email" class="sr-only">Email address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="input"
                            placeholder="Email address"
                            :disabled="loading"
                        />
                    </div>

                    <!-- Phone Input -->
                    <div v-else>
                        <label for="phone" class="sr-only">Phone number</label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            required
                            class="input"
                            placeholder="Phone number (e.g., +1234567890)"
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
                        Sending code...
                    </span>
                    <span v-else>Send verification code</span>
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

const channel = ref('email')
const loading = ref(false)
const error = ref('')

const form = reactive({
    email: '',
    phone: ''
})

const handleSubmit = async () => {
    loading.value = true
    error.value = ''

    try {
        const identifier = channel.value === 'email' ? form.email : form.phone
        
        const response = await axios.post('/auth/login', {
            identifier,
            channel: channel.value
        })

        // Store identifier for verification page
        sessionStorage.setItem('otp_identifier', identifier)
        sessionStorage.setItem('otp_channel', channel.value)

        router.push('/verify')
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send verification code'
    } finally {
        loading.value = false
    }
}
</script>
