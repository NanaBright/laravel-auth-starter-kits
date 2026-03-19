<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/dashboard" class="text-xl font-semibold text-gray-900">OTP Auth</router-link>
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
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Backup Codes</h2>
                <p class="text-gray-600 mb-6">
                    Backup codes can be used to access your account if you lose access to your phone or email.
                    Keep these codes in a safe place.
                </p>

                <div v-if="loading" class="flex justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div v-else>
                    <!-- Warning if few codes remaining -->
                    <div v-if="unusedCount > 0 && unusedCount <= 3" class="mb-6 rounded-md bg-yellow-50 p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    You only have <strong>{{ unusedCount }}</strong> backup codes remaining.
                                    Consider regenerating new codes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- No codes warning -->
                    <div v-if="unusedCount === 0" class="mb-6 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    You have no backup codes remaining. Generate new codes to maintain account recovery options.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Codes Grid -->
                    <div v-if="codes.length > 0" class="mb-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                v-for="code in codes"
                                :key="code.code"
                                :class="[
                                    'px-4 py-3 rounded-lg font-mono text-sm',
                                    code.used_at 
                                        ? 'bg-gray-100 text-gray-400 line-through' 
                                        : 'bg-gray-50 text-gray-900'
                                ]"
                            >
                                {{ code.code }}
                                <span v-if="code.used_at" class="text-xs ml-2">(used)</span>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-500">
                            {{ unusedCount }} of {{ codes.length }} codes remaining
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <button
                            @click="regenerateCodes"
                            :disabled="regenerating"
                            class="btn-primary"
                        >
                            <span v-if="regenerating" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Generating...
                            </span>
                            <span v-else>Generate New Codes</span>
                        </button>
                        <button
                            v-if="codes.length > 0 && unusedCount > 0"
                            @click="downloadCodes"
                            class="btn-secondary"
                        >
                            Download Codes
                        </button>
                    </div>

                    <!-- Regeneration Warning -->
                    <p class="mt-4 text-sm text-gray-500">
                        Warning: Generating new codes will invalidate all existing codes.
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const codes = ref([])
const loading = ref(true)
const regenerating = ref(false)

onMounted(async () => {
    await fetchCodes()
})

const unusedCount = computed(() => {
    return codes.value.filter(c => !c.used_at).length
})

const fetchCodes = async () => {
    loading.value = true
    try {
        const response = await axios.get('/auth/backup-codes')
        codes.value = response.data.codes
    } catch (err) {
        if (err.response?.status === 401) {
            router.push('/login')
        }
    } finally {
        loading.value = false
    }
}

const regenerateCodes = async () => {
    if (!confirm('This will invalidate all existing backup codes. Continue?')) {
        return
    }

    regenerating.value = true
    try {
        const response = await axios.post('/auth/backup-codes/regenerate')
        codes.value = response.data.codes
    } catch (err) {
        alert('Failed to generate new codes')
    } finally {
        regenerating.value = false
    }
}

const downloadCodes = () => {
    const unusedCodes = codes.value.filter(c => !c.used_at).map(c => c.code)
    const content = `OTP Auth Backup Codes
=====================

Keep these codes in a safe place. Each code can only be used once.

${unusedCodes.join('\n')}

Generated: ${new Date().toISOString()}
`
    
    const blob = new Blob([content], { type: 'text/plain' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'backup-codes.txt'
    a.click()
    URL.revokeObjectURL(url)
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
