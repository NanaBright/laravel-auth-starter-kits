<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Verify your identity</h1>
                <p class="mt-2 text-gray-600">
                    Enter the 6-digit code sent to your {{ channel === 'email' ? 'email' : 'phone' }}
                </p>
                <p class="mt-1 text-sm text-gray-500">{{ maskedIdentifier }}</p>
            </div>

            <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
                <!-- OTP Input -->
                <div class="flex justify-center gap-2">
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

                <!-- Error Message -->
                <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-700">{{ error }}</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full" :disabled="loading || !isComplete">
                    <span v-if="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verifying...
                    </span>
                    <span v-else>Verify code</span>
                </button>

                <!-- Resend / Backup Code -->
                <div class="flex items-center justify-between text-sm">
                    <button
                        type="button"
                        @click="resendCode"
                        :disabled="resendLoading || resendCooldown > 0"
                        class="text-indigo-600 hover:text-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="resendCooldown > 0">Resend in {{ resendCooldown }}s</span>
                        <span v-else-if="resendLoading">Sending...</span>
                        <span v-else>Resend code</span>
                    </button>
                    <button
                        type="button"
                        @click="showBackupInput = !showBackupInput"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        Use backup code
                    </button>
                </div>

                <!-- Backup Code Input -->
                <div v-if="showBackupInput" class="pt-4 border-t border-gray-200">
                    <label for="backup" class="block text-sm font-medium text-gray-700 mb-1">Backup code</label>
                    <div class="flex gap-2">
                        <input
                            id="backup"
                            v-model="backupCode"
                            type="text"
                            class="input flex-1"
                            placeholder="Enter your backup code"
                            :disabled="loading"
                        />
                        <button
                            type="button"
                            @click="verifyBackup"
                            class="btn-secondary"
                            :disabled="loading || !backupCode"
                        >
                            Verify
                        </button>
                    </div>
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
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const identifier = ref('')
const channel = ref('')
const otp = reactive(['', '', '', '', '', ''])
const inputRefs = []
const loading = ref(false)
const error = ref('')
const showBackupInput = ref(false)
const backupCode = ref('')
const resendLoading = ref(false)
const resendCooldown = ref(0)
let cooldownInterval = null

onMounted(() => {
    identifier.value = sessionStorage.getItem('otp_identifier') || ''
    channel.value = sessionStorage.getItem('otp_channel') || 'email'
    
    if (!identifier.value) {
        router.push('/login')
    }
    
    // Focus first input
    if (inputRefs[0]) {
        inputRefs[0].focus()
    }
})

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval)
    }
})

const maskedIdentifier = computed(() => {
    if (!identifier.value) return ''
    
    if (channel.value === 'email') {
        const [local, domain] = identifier.value.split('@')
        if (local.length <= 2) return identifier.value
        return `${local[0]}${'*'.repeat(local.length - 2)}${local[local.length - 1]}@${domain}`
    } else {
        if (identifier.value.length <= 4) return identifier.value
        return `${'*'.repeat(identifier.value.length - 4)}${identifier.value.slice(-4)}`
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

const handleSubmit = async () => {
    if (!isComplete.value) return
    
    loading.value = true
    error.value = ''

    try {
        const code = otp.join('')
        const response = await axios.post('/auth/verify', {
            identifier: identifier.value,
            channel: channel.value,
            code
        })

        // Store auth token
        localStorage.setItem('auth_token', response.data.token)
        
        // Clear session storage
        sessionStorage.removeItem('otp_identifier')
        sessionStorage.removeItem('otp_channel')

        router.push('/dashboard')
    } catch (err) {
        error.value = err.response?.data?.message || 'Invalid verification code'
        // Clear OTP inputs
        otp.fill('')
        inputRefs[0]?.focus()
    } finally {
        loading.value = false
    }
}

const resendCode = async () => {
    resendLoading.value = true
    error.value = ''

    try {
        await axios.post('/auth/resend', {
            identifier: identifier.value,
            channel: channel.value
        })

        // Start cooldown
        resendCooldown.value = 60
        cooldownInterval = setInterval(() => {
            resendCooldown.value--
            if (resendCooldown.value <= 0) {
                clearInterval(cooldownInterval)
            }
        }, 1000)
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to resend code'
    } finally {
        resendLoading.value = false
    }
}

const verifyBackup = async () => {
    if (!backupCode.value) return
    
    loading.value = true
    error.value = ''

    try {
        const response = await axios.post('/auth/verify-backup', {
            identifier: identifier.value,
            code: backupCode.value
        })

        localStorage.setItem('auth_token', response.data.token)
        sessionStorage.removeItem('otp_identifier')
        sessionStorage.removeItem('otp_channel')

        router.push('/dashboard')
    } catch (err) {
        error.value = err.response?.data?.message || 'Invalid backup code'
    } finally {
        loading.value = false
    }
}
</script>
