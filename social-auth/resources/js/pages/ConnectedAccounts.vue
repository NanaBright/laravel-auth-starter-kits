<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/dashboard" class="text-xl font-semibold text-gray-900">Social Auth</router-link>
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

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Connected Accounts</h2>
                <p class="text-gray-600 mb-6">Manage your social login connections</p>

                <div v-if="loading" class="flex justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div v-else class="space-y-4">
                    <!-- Google -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Google</p>
                                <p v-if="getAccount('google')" class="text-sm text-gray-500">
                                    {{ getAccount('google').nickname || 'Connected' }}
                                </p>
                                <p v-else class="text-sm text-gray-400">Not connected</p>
                            </div>
                        </div>
                        <button
                            v-if="getAccount('google')"
                            @click="disconnect(getAccount('google').id)"
                            :disabled="accounts.length <= 1"
                            class="text-red-600 hover:text-red-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Disconnect
                        </button>
                        <a v-else href="/auth/google" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            Connect
                        </a>
                    </div>

                    <!-- GitHub -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">GitHub</p>
                                <p v-if="getAccount('github')" class="text-sm text-gray-500">
                                    {{ getAccount('github').nickname || 'Connected' }}
                                </p>
                                <p v-else class="text-sm text-gray-400">Not connected</p>
                            </div>
                        </div>
                        <button
                            v-if="getAccount('github')"
                            @click="disconnect(getAccount('github').id)"
                            :disabled="accounts.length <= 1"
                            class="text-red-600 hover:text-red-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Disconnect
                        </button>
                        <a v-else href="/auth/github" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            Connect
                        </a>
                    </div>

                    <!-- Facebook -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Facebook</p>
                                <p v-if="getAccount('facebook')" class="text-sm text-gray-500">
                                    {{ getAccount('facebook').nickname || 'Connected' }}
                                </p>
                                <p v-else class="text-sm text-gray-400">Not connected</p>
                            </div>
                        </div>
                        <button
                            v-if="getAccount('facebook')"
                            @click="disconnect(getAccount('facebook').id)"
                            :disabled="accounts.length <= 1"
                            class="text-red-600 hover:text-red-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Disconnect
                        </button>
                        <a v-else href="/auth/facebook" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            Connect
                        </a>
                    </div>

                    <!-- Twitter/X -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-black rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">X (Twitter)</p>
                                <p v-if="getAccount('twitter')" class="text-sm text-gray-500">
                                    {{ getAccount('twitter').nickname || 'Connected' }}
                                </p>
                                <p v-else class="text-sm text-gray-400">Not connected</p>
                            </div>
                        </div>
                        <button
                            v-if="getAccount('twitter')"
                            @click="disconnect(getAccount('twitter').id)"
                            :disabled="accounts.length <= 1"
                            class="text-red-600 hover:text-red-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Disconnect
                        </button>
                        <a v-else href="/auth/twitter" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            Connect
                        </a>
                    </div>
                </div>

                <p v-if="accounts.length <= 1" class="mt-6 text-sm text-gray-500">
                    You must have at least one connected account to sign in.
                </p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const accounts = ref([])
const loading = ref(true)
const message = ref(null)

onMounted(async () => {
    // Check for success/error in URL
    if (route.query.success) {
        message.value = { type: 'success', text: 'Account connected successfully!' }
    } else if (route.query.error) {
        message.value = { type: 'error', text: route.query.error }
    }

    await fetchAccounts()
})

const fetchAccounts = async () => {
    loading.value = true
    try {
        const response = await axios.get('/user/connected-accounts')
        accounts.value = response.data.accounts
    } catch (err) {
        if (err.response?.status === 401) {
            router.push('/login')
        }
    } finally {
        loading.value = false
    }
}

const getAccount = (provider) => {
    return accounts.value.find(a => a.provider === provider)
}

const disconnect = async (accountId) => {
    if (accounts.value.length <= 1) {
        message.value = { type: 'error', text: 'Cannot disconnect the only linked account.' }
        return
    }

    if (!confirm('Are you sure you want to disconnect this account?')) {
        return
    }

    try {
        await axios.delete(`/user/connected-accounts/${accountId}`)
        message.value = { type: 'success', text: 'Account disconnected successfully!' }
        await fetchAccounts()
    } catch (err) {
        message.value = { type: 'error', text: err.response?.data?.message || 'Failed to disconnect account' }
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
