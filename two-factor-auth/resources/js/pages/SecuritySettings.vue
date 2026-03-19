<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/dashboard" class="text-xl font-semibold text-gray-900">Two-Factor Auth</router-link>
                    </div>
                    <div class="flex items-center gap-4">
                        <router-link to="/dashboard" class="text-gray-600 hover:text-gray-900">
                            Dashboard
                        </router-link>
                        <button @click="logout" class="text-gray-600 hover:text-gray-900">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            <div v-if="message" :class="[
                'rounded-md p-4 mb-6',
                message.type === 'success' ? 'bg-green-50' : 'bg-red-50'
            ]">
                <p :class="[
                    'text-sm',
                    message.type === 'success' ? 'text-green-700' : 'text-red-700'
                ]">{{ message.text }}</p>
            </div>

            <div v-if="loading" class="flex justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div v-else class="space-y-6">
                <!-- 2FA Status Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Two-Factor Authentication</h2>
                    <p class="text-gray-600 mb-6">Add an extra layer of security to your account.</p>

                    <!-- Not Enabled State -->
                    <div v-if="!status.enabled && !setupMode">
                        <div class="flex items-center gap-4 p-4 bg-yellow-50 rounded-lg mb-6">
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <p class="font-medium text-yellow-800">Two-factor authentication is not enabled</p>
                                <p class="text-sm text-yellow-700">We strongly recommend enabling 2FA.</p>
                            </div>
                        </div>
                        <button @click="enableTwoFactor" :disabled="enabling" class="btn-primary">
                            <span v-if="enabling">Enabling...</span>
                            <span v-else>Enable Two-Factor Authentication</span>
                        </button>
                    </div>

                    <!-- Setup Mode -->
                    <div v-else-if="setupMode">
                        <div class="space-y-6">
                            <div class="flex flex-col items-center">
                                <p class="text-gray-600 mb-4">Scan this QR code with your authenticator app</p>
                                <div v-html="qrCode" class="bg-white p-4 rounded-lg shadow-inner"></div>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-2">Or enter this code manually:</p>
                                <code class="px-4 py-2 bg-gray-100 rounded-lg font-mono text-sm select-all">{{ secret }}</code>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Enter the 6-digit code from your app to confirm
                                </label>
                                <div class="flex gap-4">
                                    <input
                                        v-model="confirmCode"
                                        type="text"
                                        maxlength="6"
                                        class="input flex-1"
                                        placeholder="000000"
                                        :disabled="confirming"
                                    />
                                    <button @click="confirmTwoFactor" :disabled="confirming || confirmCode.length !== 6" class="btn-primary">
                                        <span v-if="confirming">Confirming...</span>
                                        <span v-else>Confirm</span>
                                    </button>
                                </div>
                            </div>

                            <button @click="cancelSetup" class="text-sm text-gray-500 hover:text-gray-700">
                                Cancel setup
                            </button>
                        </div>
                    </div>

                    <!-- Enabled State -->
                    <div v-else-if="status.enabled">
                        <div class="flex items-center gap-4 p-4 bg-green-50 rounded-lg mb-6">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div>
                                <p class="font-medium text-green-800">Two-factor authentication is enabled</p>
                                <p class="text-sm text-green-700">Your account has an extra layer of security.</p>
                            </div>
                        </div>

                        <!-- Disable 2FA -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Disable Two-Factor Authentication</h3>
                            <div class="flex gap-4">
                                <input
                                    v-model="disablePassword"
                                    type="password"
                                    class="input flex-1"
                                    placeholder="Enter your password"
                                    :disabled="disabling"
                                />
                                <button @click="disableTwoFactor" :disabled="disabling || !disablePassword" class="btn-danger">
                                    <span v-if="disabling">Disabling...</span>
                                    <span v-else>Disable 2FA</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recovery Codes Card (only when 2FA enabled) -->
                <div v-if="status.enabled" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Recovery Codes</h3>
                    <p class="text-gray-600 mb-4">
                        Recovery codes can be used to access your account if you lose your authenticator device.
                    </p>

                    <div v-if="recoveryCodes.length > 0" class="mb-6">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div
                                v-for="code in recoveryCodes"
                                :key="code"
                                class="px-4 py-3 rounded-lg font-mono text-sm bg-gray-50 text-gray-900"
                            >
                                {{ code }}
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ recoveryCodes.length }} codes remaining
                        </p>
                    </div>

                    <div v-if="status.recovery_codes_remaining <= 3 && status.recovery_codes_remaining > 0" class="mb-4 rounded-md bg-yellow-50 p-4">
                        <p class="text-sm text-yellow-700">
                            You only have <strong>{{ status.recovery_codes_remaining }}</strong> recovery codes remaining.
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <button @click="showRecoveryCodes" :disabled="loadingCodes" class="btn-secondary">
                            <span v-if="loadingCodes">Loading...</span>
                            <span v-else>{{ recoveryCodes.length > 0 ? 'Hide' : 'Show' }} Recovery Codes</span>
                        </button>
                        <button @click="showRegenerate = true" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                            Regenerate Codes
                        </button>
                    </div>

                    <!-- Regenerate Modal -->
                    <div v-if="showRegenerate" class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-4">
                            This will invalidate all existing recovery codes. Enter your password to confirm.
                        </p>
                        <div class="flex gap-4">
                            <input
                                v-model="regeneratePassword"
                                type="password"
                                class="input flex-1"
                                placeholder="Your password"
                                :disabled="regenerating"
                            />
                            <button @click="regenerateCodes" :disabled="regenerating || !regeneratePassword" class="btn-primary">
                                <span v-if="regenerating">Regenerating...</span>
                                <span v-else>Regenerate</span>
                            </button>
                            <button @click="showRegenerate = false" class="btn-secondary">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const loading = ref(true)
const status = ref({ enabled: false, pending: false, recovery_codes_remaining: 0 })
const message = ref(null)

// Setup state
const setupMode = ref(false)
const qrCode = ref('')
const secret = ref('')
const confirmCode = ref('')
const enabling = ref(false)
const confirming = ref(false)

// Disable state
const disablePassword = ref('')
const disabling = ref(false)

// Recovery codes state
const recoveryCodes = ref([])
const loadingCodes = ref(false)
const showRegenerate = ref(false)
const regeneratePassword = ref('')
const regenerating = ref(false)

onMounted(async () => {
    await fetchStatus()
})

const fetchStatus = async () => {
    loading.value = true
    try {
        const response = await axios.get('/two-factor/status')
        status.value = response.data
    } catch (err) {
        if (err.response?.status === 401) {
            router.push('/login')
        }
    } finally {
        loading.value = false
    }
}

const enableTwoFactor = async () => {
    enabling.value = true
    message.value = null
    try {
        const response = await axios.post('/two-factor/enable')
        qrCode.value = response.data.qr_code_svg
        secret.value = response.data.secret
        setupMode.value = true
    } catch (err) {
        message.value = { type: 'error', text: err.response?.data?.message || 'Failed to enable 2FA' }
    } finally {
        enabling.value = false
    }
}

const confirmTwoFactor = async () => {
    confirming.value = true
    message.value = null
    try {
        const response = await axios.post('/two-factor/confirm', { code: confirmCode.value })
        recoveryCodes.value = response.data.recovery_codes
        setupMode.value = false
        message.value = { type: 'success', text: 'Two-factor authentication enabled! Save your recovery codes.' }
        await fetchStatus()
    } catch (err) {
        message.value = { type: 'error', text: err.response?.data?.message || 'Invalid code' }
    } finally {
        confirming.value = false
    }
}

const cancelSetup = () => {
    setupMode.value = false
    qrCode.value = ''
    secret.value = ''
    confirmCode.value = ''
}

const disableTwoFactor = async () => {
    if (!confirm('Are you sure you want to disable two-factor authentication?')) {
        return
    }

    disabling.value = true
    message.value = null
    try {
        await axios.post('/two-factor/disable', { password: disablePassword.value })
        message.value = { type: 'success', text: 'Two-factor authentication disabled.' }
        disablePassword.value = ''
        recoveryCodes.value = []
        await fetchStatus()
    } catch (err) {
        message.value = { type: 'error', text: err.response?.data?.message || 'Failed to disable 2FA' }
    } finally {
        disabling.value = false
    }
}

const showRecoveryCodes = async () => {
    if (recoveryCodes.value.length > 0) {
        recoveryCodes.value = []
        return
    }

    loadingCodes.value = true
    try {
        const response = await axios.get('/two-factor/recovery-codes')
        recoveryCodes.value = response.data.recovery_codes
    } catch (err) {
        message.value = { type: 'error', text: 'Failed to load recovery codes' }
    } finally {
        loadingCodes.value = false
    }
}

const regenerateCodes = async () => {
    regenerating.value = true
    message.value = null
    try {
        const response = await axios.post('/two-factor/recovery-codes', { password: regeneratePassword.value })
        recoveryCodes.value = response.data.recovery_codes
        showRegenerate.value = false
        regeneratePassword.value = ''
        message.value = { type: 'success', text: 'Recovery codes regenerated. Save your new codes.' }
        await fetchStatus()
    } catch (err) {
        message.value = { type: 'error', text: err.response?.data?.message || 'Failed to regenerate codes' }
    } finally {
        regenerating.value = false
    }
}

const logout = async () => {
    try {
        await axios.post('/auth/logout')
    } catch (err) {
        // Ignore
    } finally {
        localStorage.removeItem('auth_token')
        router.push('/login')
    }
}
</script>
