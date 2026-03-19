<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Create an account</h1>
                <p class="mt-2 text-gray-600">Enter your details to get started</p>
            </div>

            <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="input"
                            placeholder="Your name"
                            :disabled="loading"
                        />
                    </div>

                    <!-- Email -->
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

                    <!-- Phone (Optional) -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="input"
                            placeholder="+1234567890"
                            :disabled="loading"
                        />
                    </div>

                    <!-- Preferred Channel -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preferred verification method</label>
                        <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                            <button
                                type="button"
                                @click="form.preferred_channel = 'email'"
                                :class="[
                                    'flex-1 py-3 text-sm font-medium transition-colors',
                                    form.preferred_channel === 'email' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-white text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                Email
                            </button>
                            <button
                                type="button"
                                @click="form.preferred_channel = 'sms'"
                                :disabled="!form.phone"
                                :class="[
                                    'flex-1 py-3 text-sm font-medium transition-colors',
                                    form.preferred_channel === 'sms' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-white text-gray-700 hover:bg-gray-50',
                                    !form.phone && 'opacity-50 cursor-not-allowed'
                                ]"
                            >
                                SMS
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-700">{{ error }}</p>
                </div>

                <!-- Validation Errors -->
                <div v-if="Object.keys(errors).length > 0" class="rounded-md bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        <li v-for="(messages, field) in errors" :key="field">{{ messages[0] }}</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full" :disabled="loading">
                    <span v-if="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating account...
                    </span>
                    <span v-else>Create account</span>
                </button>

                <!-- Login Link -->
                <p class="text-center text-sm text-gray-600">
                    Already have an account?
                    <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Sign in
                    </router-link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const loading = ref(false)
const error = ref('')
const errors = ref({})

const form = reactive({
    name: '',
    email: '',
    phone: '',
    preferred_channel: 'email'
})

// Reset preferred channel if phone is removed
watch(() => form.phone, (newVal) => {
    if (!newVal && form.preferred_channel === 'sms') {
        form.preferred_channel = 'email'
    }
})

const handleSubmit = async () => {
    loading.value = true
    error.value = ''
    errors.value = {}

    try {
        const response = await axios.post('/auth/register', form)

        // Store identifier for verification page
        const identifier = form.preferred_channel === 'email' ? form.email : form.phone
        sessionStorage.setItem('otp_identifier', identifier)
        sessionStorage.setItem('otp_channel', form.preferred_channel)

        router.push('/verify')
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {}
        } else {
            error.value = err.response?.data?.message || 'Failed to create account'
        }
    } finally {
        loading.value = false
    }
}
</script>
