<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">OTP Auth</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <router-link to="/backup-codes" class="text-gray-600 hover:text-gray-900">
                            Backup Codes
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
                    <p class="mt-2 text-gray-600">You have successfully authenticated using OTP verification.</p>
                </div>

                <!-- User Info Card -->
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
                        <div v-if="user.phone">
                            <dt class="text-sm text-gray-500">Phone</dt>
                            <dd class="text-gray-900">{{ user.phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email Verified</dt>
                            <dd class="text-gray-900">
                                <span v-if="user.email_verified_at" class="text-green-600">Yes</span>
                                <span v-else class="text-yellow-600">No</span>
                            </dd>
                        </div>
                        <div v-if="user.phone">
                            <dt class="text-sm text-gray-500">Phone Verified</dt>
                            <dd class="text-gray-900">
                                <span v-if="user.phone_verified_at" class="text-green-600">Yes</span>
                                <span v-else class="text-yellow-600">No</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Verification Channels -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Verification Channels</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Email</p>
                                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full">Active</span>
                        </div>
                        <div v-if="user.phone" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">SMS</p>
                                    <p class="text-sm text-gray-500">{{ user.phone }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full">Active</span>
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
const loading = ref(true)

onMounted(async () => {
    try {
        const response = await axios.get('/user')
        user.value = response.data
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
