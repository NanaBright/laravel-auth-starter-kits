<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div v-if="error">
                <div class="rounded-md bg-red-50 p-4 mb-4">
                    <p class="text-sm text-red-700">{{ error }}</p>
                </div>
                <router-link to="/login" class="text-indigo-600 hover:text-indigo-500">
                    Back to login
                </router-link>
            </div>
            <div v-else>
                <svg class="animate-spin h-8 w-8 mx-auto text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-4 text-gray-600">Completing authentication...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const error = ref('')

onMounted(() => {
    const token = route.query.token
    const errorParam = route.query.error

    if (errorParam) {
        error.value = errorParam
        return
    }

    if (token) {
        localStorage.setItem('auth_token', token)
        router.push('/dashboard')
    } else {
        error.value = 'Authentication failed. No token received.'
    }
})
</script>
