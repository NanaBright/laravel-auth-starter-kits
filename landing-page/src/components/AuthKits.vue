<template>
  <section id="kits" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="section-title mb-4">Available Authentication Kits</h2>
        <p class="section-subtitle">
          Choose the authentication method that fits your application needs.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="kit in kits" :key="kit.name" 
             :class="['card relative overflow-hidden', kit.available ? 'border-2 border-primary-200' : 'opacity-75']">
          <div v-if="kit.status === 'coming-soon'" 
               class="absolute top-4 right-4 px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
            Coming Soon
          </div>
          <div v-if="kit.status === 'planned'" 
               class="absolute top-4 right-4 px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
            Planned
          </div>
          <div v-if="kit.status === 'available'" 
               class="absolute top-4 right-4 px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
            Available
          </div>
          
          <div class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center mb-6">
            <component :is="kit.icon" class="w-7 h-7 text-primary-600" />
          </div>
          
          <h3 class="text-xl font-bold text-gray-900 mb-2">{{ kit.name }}</h3>
          <p class="text-gray-600 mb-4">{{ kit.description }}</p>
          
          <ul class="space-y-2 mb-6">
            <li v-for="feature in kit.features" :key="feature" class="flex items-center text-sm text-gray-600">
              <svg class="w-4 h-4 text-primary-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              {{ feature }}
            </li>
          </ul>
          
          <a v-if="kit.available" 
             :href="kit.link" 
             class="btn-primary w-full justify-center">
            View Documentation
          </a>
          <button v-else 
                  disabled 
                  class="w-full px-6 py-3 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
            Coming Soon
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { h } from 'vue'

const IconPhone = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' })
    ])
  }
}

const IconEmail = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' })
    ])
  }
}

const IconOTP = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14' })
    ])
  }
}

const IconSocial = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9' })
    ])
  }
}

const Icon2FA = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' })
    ])
  }
}

const IconPasswordless = {
  render() {
    return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' })
    ])
  }
}

const kits = [
  {
    name: 'Phone Auth',
    description: 'Phone/SMS verification with OTP codes. Perfect for mobile-first applications.',
    icon: IconPhone,
    status: 'available',
    available: true,
    link: 'https://github.com/NanaBright/laravel-auth-starter-kits/tree/main/phone-auth',
    features: ['OTP verification', 'Multiple SMS gateways', 'Rate limiting', 'Fallback support']
  },
  {
    name: 'Email Auth',
    description: 'Email-based authentication with magic links. Passwordless and secure.',
    icon: IconEmail,
    status: 'available',
    available: true,
    link: 'https://github.com/NanaBright/laravel-auth-starter-kits/tree/main/email-auth',
    features: ['Magic links', 'Email verification', 'Custom templates', 'Multiple providers']
  },
  {
    name: 'OTP Auth',
    description: 'Multi-channel OTP with SMS and Email support. Maximum flexibility.',
    icon: IconOTP,
    status: 'coming-soon',
    available: false,
    features: ['SMS + Email OTP', 'Backup codes', 'Time-based codes', 'Recovery options']
  },
  {
    name: 'Social Auth',
    description: 'Social media login integration. Streamlined user onboarding.',
    icon: IconSocial,
    status: 'planned',
    available: false,
    features: ['Google login', 'GitHub login', 'Facebook login', 'Twitter login']
  },
  {
    name: 'Two-Factor Auth',
    description: 'Add an extra layer of security with 2FA support.',
    icon: Icon2FA,
    status: 'planned',
    available: false,
    features: ['TOTP support', 'SMS fallback', 'Recovery codes', 'Device trust']
  },
  {
    name: 'Passwordless Auth',
    description: 'Modern passwordless authentication methods.',
    icon: IconPasswordless,
    status: 'planned',
    available: false,
    features: ['WebAuthn/FIDO2', 'Biometric', 'Magic links', 'Security keys']
  }
]
</script>
