<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Two-Factor Auth</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <router-link to="/settings/security" class="text-gray-600 hover:text-gray-900">
                            Security
                        </router-link>
                        <button @click="logout" class="text-gray-600 hover:text-gray-900">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div v-if="loading" class="flex justify-center">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div v-else-if="user" class="space-y-8">
                <!-- Welcome Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-2xl font-bold text-gray-900">Welcome, {{ user.name }}!</h2>
                    <p class="mt-2 text-gray-600">You have successfully authenticated.</p>
                </div>

                <!-- Account Info Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-gray-500">Name</dt>
                            <dd class="text-gray-900">{{ user.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-gray-900">{{ user.email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Two-Factor Authentication</dt>
                            <dd class="text-gray-900">
                                <span v-if="twoFactorEnabled" class="inline-flex items-center gap-1 text-green-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Enabled
                                </span>
                                <span v-else class="text-yellow-600">Not Enabled</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Security Recommendation -->
                <div v-if="!twoFactorEnabled" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Enable Two-Factor Authentication</h3>
                            <p class="mt-2 text-sm text-yellow-700">
                                Protect your account with an additional layer of security.
                            </p>
                            <div class="mt-4">
                                <router-link 
                                    to="/settings/security"
                                    class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition-colors text-sm font-medium"
                                >
                                    Enable 2FA
                                </router-link>
                            </div>
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
const user = ref(null)
const twoFactorEnabled = ref(false)
const loading = ref(true)

onMounted(async () => {
    try {
        const response = await axios.get('/user')
        user.value = response.data.user
        twoFactorEnabled.value = response.data.two_factor_enabled
    } catch (err) {
        router.push('/login')
    } finally {
        loading.value = false
    }
})

const logout = async () => {
    try {
        await axios.post('/auth/logout')
    } catch (err) {
        // Ignore errors on logout
    } finally {
        localStorage.removeItem('auth_token')
        router.push('/login')
    }
}
</script>
