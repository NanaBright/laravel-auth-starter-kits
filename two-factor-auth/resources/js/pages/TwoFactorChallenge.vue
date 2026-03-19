<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Two-Factor Authentication</h1>
                <p class="mt-2 text-gray-600">
                    {{ useRecovery ? 'Enter a recovery code' : 'Enter the code from your authenticator app' }}
                </p>
            </div>

            <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
                <!-- OTP Input -->
                <div v-if="!useRecovery" class="flex justify-center gap-2">
                    <input
                        v-for="(digit, index) in otp"
                        :key="index"
                        :ref="el => { if (el) inputRefs[index] = el }"
                        v-model="otp[index]"
                        type="text"
                        maxlength="1"
                        inputmode="numeric"
                        pattern="[0-9]"
                        class="otp-input"
                        :disabled="loading"
                        @input="handleOtpInput(index, $event)"
                        @keydown="handleKeydown(index, $event)"
                        @paste="handlePaste"
                    />
                </div>

                <!-- Recovery Code Input -->
                <div v-else>
                    <label for="recovery" class="block text-sm font-medium text-gray-700 mb-1">Recovery Code</label>
                    <input
                        id="recovery"
                        v-model="recoveryCode"
                        type="text"
                        class="input"
                        placeholder="XXXX-XXXX"
                        :disabled="loading"
                    />
                </div>

                <!-- Error Message -->
                <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-700">{{ error }}</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full" :disabled="loading || (!useRecovery && !isComplete)">
                    <span v-if="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verifying...
                    </span>
                    <span v-else>Verify</span>
                </button>

                <!-- Toggle Recovery -->
                <div class="text-center">
                    <button
                        type="button"
                        @click="toggleRecovery"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        {{ useRecovery ? 'Use authenticator code' : 'Use recovery code' }}
                    </button>
                </div>

                <!-- Back to Login -->
                <p class="text-center text-sm text-gray-600">
                    <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Back to login
                    </router-link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const otp = reactive(['', '', '', '', '', ''])
const inputRefs = []
const recoveryCode = ref('')
const useRecovery = ref(false)
const loading = ref(false)
const error = ref('')

onMounted(() => {
    // Focus first input
    if (inputRefs[0]) {
        inputRefs[0].focus()
    }
})

const isComplete = computed(() => otp.every(digit => digit !== ''))

const handleOtpInput = (index, event) => {
    const value = event.target.value.replace(/[^0-9]/g, '')
    otp[index] = value.slice(-1)
    
    if (value && index < 5) {
        inputRefs[index + 1]?.focus()
    }
}

const handleKeydown = (index, event) => {
    if (event.key === 'Backspace' && !otp[index] && index > 0) {
        inputRefs[index - 1]?.focus()
    }
}

const handlePaste = (event) => {
    event.preventDefault()
    const text = event.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6)
    text.split('').forEach((char, index) => {
        if (index < 6) {
            otp[index] = char
        }
    })
    inputRefs[Math.min(text.length, 5)]?.focus()
}

const toggleRecovery = () => {
    useRecovery.value = !useRecovery.value
    error.value = ''
}

const handleSubmit = async () => {
    loading.value = true
    error.value = ''

    try {
        const payload = useRecovery.value
            ? { code: recoveryCode.value, recovery: true }
            : { code: otp.join(''), recovery: false }

        const response = await axios.post('/auth/two-factor', payload)
        
        localStorage.setItem('auth_token', response.data.token)
        router.push('/dashboard')
    } catch (err) {
        error.value = err.response?.data?.message || 'Invalid code'
        if (!useRecovery.value) {
            otp.fill('')
            inputRefs[0]?.focus()
        }
    } finally {
        loading.value = false
    }
}
</script>
