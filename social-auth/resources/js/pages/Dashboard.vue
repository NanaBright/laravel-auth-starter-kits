<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Social Auth</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <router-link to="/settings/accounts" class="text-gray-600 hover:text-gray-900">
                            Connected Accounts
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
                    <div class="flex items-center gap-4">
                        <img 
                            v-if="user.avatar" 
                            :src="user.avatar" 
                            :alt="user.name"
                            class="w-16 h-16 rounded-full"
                        />
                        <div v-else class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-2xl font-bold text-indigo-600">{{ user.name.charAt(0) }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Welcome, {{ user.name }}!</h2>
                            <p class="text-gray-600">{{ user.email }}</p>
                        </div>
                    </div>
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
                            <dt class="text-sm text-gray-500">Email Verified</dt>
                            <dd class="text-gray-900">
                                <span v-if="user.email_verified_at" class="text-green-600">Yes</span>
                                <span v-else class="text-yellow-600">No</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Account Created</dt>
                            <dd class="text-gray-900">{{ formatDate(user.created_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="flex gap-4">
                        <router-link 
                            to="/settings/accounts"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Manage Connected Accounts
                        </router-link>
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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

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
