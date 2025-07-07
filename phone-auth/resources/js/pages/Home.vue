<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900">
            {{ isLogin ? 'Sign In' : 'Create Account' }}
          </h1>
          <p class="text-gray-600 mt-2">
            {{ isLogin ? 'Enter your phone number to sign in' : 'Enter your phone number to get started' }}
          </p>
        </div>

        <!-- Phone Number Form -->
        <form v-if="step === 'phone'" @submit.prevent="submitPhone" class="space-y-4">
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
              Phone Number
            </label>
            <input
              id="phone"
              v-model="phoneNumber"
              type="tel"
              placeholder="+1 (555) 123-4567"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div v-if="!isLogin">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
              Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="Enter your password"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div v-if="!isLogin">
            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">
              Confirm Password
            </label>
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              type="password"
              placeholder="Confirm your password"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <LoaderIcon class="animate-spin h-4 w-4 mr-2" />
              {{ isLogin ? 'Signing In...' : 'Sending Code...' }}
            </span>
            <span v-else>
              {{ isLogin ? 'Sign In' : 'Send Verification Code' }}
            </span>
          </button>

          <div v-if="error" class="text-red-600 text-sm text-center">
            {{ error }}
          </div>
        </form>

        <!-- OTP Verification Form -->
        <form v-if="step === 'otp'" @submit.prevent="verifyOTP" class="space-y-4">
          <div class="text-center mb-4">
            <PhoneIcon class="h-12 w-12 text-blue-600 mx-auto mb-2" />
            <p class="text-gray-600">
              We sent a verification code to
            </p>
            <p class="font-medium text-gray-900">{{ phoneNumber }}</p>
            
            <!-- Development message -->
            <div v-if="showLogMessage" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
              <p class="text-sm text-yellow-800">
                <strong>Development Mode:</strong> Check your Laravel logs for the OTP code.
                <br>
                <code class="text-xs">tail -f storage/logs/laravel.log</code>
              </p>
            </div>
          </div>

          <div>
            <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">
              Verification Code
            </label>
            <input
              id="otp"
              v-model="otpCode"
              type="text"
              placeholder="Enter 6-digit code"
              maxlength="6"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-lg tracking-widest"
            />
          </div>

          <button
            type="submit"
            :disabled="loading || otpCode.length !== 6"
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <LoaderIcon class="animate-spin h-4 w-4 mr-2" />
              Verifying...
            </span>
            <span v-else>
              Verify Code
            </span>
          </button>

          <div class="text-center">
            <button
              type="button"
              @click="resendCode"
              :disabled="resendTimer > 0"
              class="text-blue-600 hover:text-blue-700 text-sm disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              {{ resendTimer > 0 ? `Resend code in ${resendTimer}s` : 'Resend code' }}
            </button>
          </div>

          <div class="text-center">
            <button
              type="button"
              @click="goBack"
              class="text-gray-600 hover:text-gray-700 text-sm"
            >
              ← Change phone number
            </button>
          </div>

          <div v-if="error" class="text-red-600 text-sm text-center">
            {{ error }}
          </div>
        </form>

        <!-- Success State -->
        <div v-if="step === 'success'" class="text-center space-y-4">
          <CheckCircleIcon class="h-16 w-16 text-green-600 mx-auto" />
          <h2 class="text-xl font-semibold text-gray-900">
            {{ isLogin ? 'Welcome back!' : 'Account created successfully!' }}
          </h2>
          <p class="text-gray-600">
            You have been {{ isLogin ? 'signed in' : 'registered' }} successfully.
          </p>
          <button
            @click="goToDashboard"
            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200"
          >
            Continue to Dashboard
          </button>
        </div>

        <!-- Toggle between login and register -->
        <div v-if="step === 'phone'" class="mt-6 text-center">
          <p class="text-gray-600">
            {{ isLogin ? "Don't have an account?" : "Already have an account?" }}
            <button
              @click="toggleMode"
              class="text-blue-600 hover:text-blue-700 font-medium ml-1"
            >
              {{ isLogin ? 'Sign up' : 'Sign in' }}
            </button>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { PhoneIcon, LoaderIcon, CheckCircleIcon } from 'lucide-vue-next'

// Reactive state
const isLogin = ref(true)
const step = ref('phone') // 'phone', 'otp', 'success'
const phoneNumber = ref('')
const password = ref('')
const confirmPassword = ref('')
const otpCode = ref('')
const loading = ref(false)
const error = ref('')
const resendTimer = ref(0)
let resendInterval = null

// API base URL - adjust this to match your Laravel backend
const API_BASE_URL = 'http://127.0.0.1:8000/api'

// Development helper - check logs for OTP
const showLogMessage = ref(false)

// Toggle between login and register
const toggleMode = () => {
  isLogin.value = !isLogin.value
  error.value = ''
  password.value = ''
  confirmPassword.value = ''
}

// Submit phone number
const submitPhone = async () => {
  error.value = ''
  
  // Validation
  if (!phoneNumber.value.trim()) {
    error.value = 'Phone number is required'
    return
  }

  if (!isLogin.value) {
    if (!password.value) {
      error.value = 'Password is required'
      return
    }
    if (password.value !== confirmPassword.value) {
      error.value = 'Passwords do not match'
      return
    }
    if (password.value.length < 6) {
      error.value = 'Password must be at least 6 characters'
      return
    }
  }

  loading.value = true

  try {
    const endpoint = isLogin.value ? '/auth/login' : '/auth/register'
    const payload = isLogin.value 
      ? { phone: phoneNumber.value }
      : { phone: phoneNumber.value, password: password.value }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    })

    // Add this check
    if (!response.ok) {
      const errorText = await response.text()
      console.error('Response error:', errorText)
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    if (response.ok) {
      step.value = 'otp'
      showLogMessage.value = true
      startResendTimer()
    } else {
      error.value = data.message || 'An error occurred'
    }
  } catch (err) {
    error.value = 'Network error. Please try again.'
    console.error('Auth error:', err)
  } finally {
    loading.value = false
  }
}

// Verify OTP
const verifyOTP = async () => {
  error.value = ''
  loading.value = true

  try {
    const response = await fetch(`${API_BASE_URL}/auth/verify-otp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        phone: phoneNumber.value,
        otp: otpCode.value
      })
    })

    const data = await response.json()

    if (response.ok) {
      // Store the token
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
      
      step.value = 'success'
      clearResendTimer()
    } else {
      error.value = data.message || 'Invalid verification code'
    }
  } catch (err) {
    error.value = 'Network error. Please try again.'
    console.error('OTP verification error:', err)
  } finally {
    loading.value = false
  }
}

// Resend OTP code
const resendCode = async () => {
  if (resendTimer.value > 0) return

  loading.value = true
  error.value = ''

  try {
    const response = await fetch(`${API_BASE_URL}/auth/resend-otp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        phone: phoneNumber.value
      })
    })

    const data = await response.json()

    if (response.ok) {
      startResendTimer()
    } else {
      error.value = data.message || 'Failed to resend code'
    }
  } catch (err) {
    error.value = 'Network error. Please try again.'
    console.error('Resend OTP error:', err)
  } finally {
    loading.value = false
  }
}

// Start resend timer
const startResendTimer = () => {
  resendTimer.value = 60
  resendInterval = setInterval(() => {
    resendTimer.value--
    if (resendTimer.value <= 0) {
      clearInterval(resendInterval)
    }
  }, 1000)
}

// Clear resend timer
const clearResendTimer = () => {
  if (resendInterval) {
    clearInterval(resendInterval)
    resendInterval = null
  }
  resendTimer.value = 0
}

// Go back to phone input
const goBack = () => {
  step.value = 'phone'
  otpCode.value = ''
  error.value = ''
  clearResendTimer()
}

// Navigate to dashboard
const goToDashboard = () => {
  // Redirect to your dashboard or emit an event
  window.location.href = '/dashboard'
}

// Cleanup on unmount
onUnmounted(() => {
  clearResendTimer()
})
</script>
