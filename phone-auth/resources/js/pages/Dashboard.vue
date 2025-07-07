<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
          <button
            @click="logout"
            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200"
          >
            Logout
          </button>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h2 class="text-lg font-semibold text-blue-900 mb-2">Welcome!</h2>
          <p class="text-blue-700">
            You have successfully authenticated with your phone number: 
            <span class="font-medium">{{ user?.phone }}</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const user = ref(null)

onMounted(() => {
  const userData = localStorage.getItem('user')
  if (userData) {
    user.value = JSON.parse(userData)
  }
})

const logout = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  router.push('/')
}
</script>