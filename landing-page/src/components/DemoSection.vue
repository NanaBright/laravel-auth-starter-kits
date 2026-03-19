<template>
  <section id="demo" class="py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="section-title mb-4">Interactive Demo</h2>
        <p class="section-subtitle">
          Try out the authentication flows directly. No sign-up required.
        </p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Phone Auth Demo -->
        <div class="card">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Phone Authentication</h3>
          </div>
          
          <div class="bg-gray-50 rounded-xl p-6">
            <div v-if="phoneStep === 'enter'" class="space-y-4">
              <label class="block">
                <span class="text-sm font-medium text-gray-700">Phone Number</span>
                <input 
                  type="tel" 
                  v-model="phoneNumber"
                  placeholder="+1 (555) 123-4567"
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-4 py-3"
                >
              </label>
              <button @click="sendPhoneOTP" class="btn-primary w-full">
                Send Verification Code
              </button>
            </div>
            
            <div v-else-if="phoneStep === 'verify'" class="space-y-4">
              <p class="text-sm text-gray-600">Enter the 6-digit code sent to {{ phoneNumber }}</p>
              <div class="flex gap-2 justify-center">
                <input 
                  v-for="i in 6" 
                  :key="i"
                  type="text" 
                  maxlength="1"
                  v-model="phoneOTP[i-1]"
                  @input="handleOTPInput($event, i)"
                  :ref="el => otpInputs[i-1] = el"
                  class="w-12 h-12 text-center text-xl font-bold rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
              </div>
              <button @click="verifyPhoneOTP" class="btn-primary w-full">
                Verify Code
              </button>
              <button @click="phoneStep = 'enter'" class="text-sm text-gray-500 hover:text-primary-600 w-full">
                Change phone number
              </button>
            </div>
            
            <div v-else class="text-center space-y-4">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h4 class="text-lg font-semibold text-gray-900">Verified!</h4>
              <p class="text-sm text-gray-600">Phone authentication successful</p>
              <button @click="resetPhoneDemo" class="text-sm text-primary-600 hover:text-primary-700">
                Try again
              </button>
            </div>
          </div>
          
          <p class="text-xs text-gray-500 mt-4 text-center">
            Demo mode - no actual SMS is sent. Use code: 123456
          </p>
        </div>
        
        <!-- Email Auth Demo -->
        <div class="card">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Email Authentication</h3>
          </div>
          
          <div class="bg-gray-50 rounded-xl p-6">
            <div v-if="emailStep === 'enter'" class="space-y-4">
              <label class="block">
                <span class="text-sm font-medium text-gray-700">Email Address</span>
                <input 
                  type="email" 
                  v-model="email"
                  placeholder="you@example.com"
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-4 py-3"
                >
              </label>
              <button @click="sendMagicLink" class="btn-primary w-full">
                Send Magic Link
              </button>
            </div>
            
            <div v-else-if="emailStep === 'sent'" class="text-center space-y-4">
              <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <h4 class="text-lg font-semibold text-gray-900">Check your email!</h4>
              <p class="text-sm text-gray-600">We sent a magic link to {{ email }}</p>
              <button @click="simulateEmailClick" class="btn-secondary w-full">
                Simulate Click Link
              </button>
              <button @click="emailStep = 'enter'" class="text-sm text-gray-500 hover:text-primary-600 w-full">
                Use different email
              </button>
            </div>
            
            <div v-else class="text-center space-y-4">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h4 class="text-lg font-semibold text-gray-900">Authenticated!</h4>
              <p class="text-sm text-gray-600">Magic link authentication successful</p>
              <button @click="resetEmailDemo" class="text-sm text-primary-600 hover:text-primary-700">
                Try again
              </button>
            </div>
          </div>
          
          <p class="text-xs text-gray-500 mt-4 text-center">
            Demo mode - no actual email is sent
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

// Phone Auth Demo
const phoneNumber = ref('')
const phoneStep = ref('enter')
const phoneOTP = ref(['', '', '', '', '', ''])
const otpInputs = ref([])

const sendPhoneOTP = () => {
  if (phoneNumber.value) {
    phoneStep.value = 'verify'
  }
}

const handleOTPInput = (event, index) => {
  const value = event.target.value
  if (value && index < 6) {
    otpInputs.value[index]?.focus()
  }
}

const verifyPhoneOTP = () => {
  const otp = phoneOTP.value.join('')
  if (otp === '123456') {
    phoneStep.value = 'success'
  }
}

const resetPhoneDemo = () => {
  phoneNumber.value = ''
  phoneStep.value = 'enter'
  phoneOTP.value = ['', '', '', '', '', '']
}

// Email Auth Demo
const email = ref('')
const emailStep = ref('enter')

const sendMagicLink = () => {
  if (email.value) {
    emailStep.value = 'sent'
  }
}

const simulateEmailClick = () => {
  emailStep.value = 'success'
}

const resetEmailDemo = () => {
  email.value = ''
  emailStep.value = 'enter'
}
</script>
