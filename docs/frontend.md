# Frontend Guide

This guide covers the frontend architecture, components, and customization options for the Laravel Auth Starter Kits.

## 📋 Frontend Architecture

### Technology Stack

The Laravel Auth Starter Kits use a modern frontend stack:

- **Vue.js 3** - Progressive JavaScript framework
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Next-generation frontend tooling
- **Vue Router** - Official router for Vue.js
- **Pinia** - Vue.js store library for state management

### Directory Structure

```
resources/
├── css/
│   └── app.css           # Main CSS file with Tailwind imports
├── js/
│   ├── app.js            # Main JavaScript entry point
│   ├── bootstrap.js      # Bootstrap JavaScript dependencies
│   ├── components/       # Vue components
│   │   ├── common/       # Shared components
│   │   │   ├── Button.vue
│   │   │   ├── Input.vue
│   │   │   └── ...
│   │   └── auth/         # Authentication components
│   │       ├── PhoneForm.vue
│   │       ├── OtpInput.vue
│   │       └── ...
│   ├── layouts/          # Layout components
│   │   ├── AppLayout.vue
│   │   └── AuthLayout.vue
│   ├── pages/            # Page components
│   │   ├── auth/
│   │   │   ├── Login.vue
│   │   │   └── Verify.vue
│   │   ├── Dashboard.vue
│   │   └── ...
│   ├── router/           # Vue Router configuration
│   │   └── index.js
│   ├── stores/           # Pinia stores
│   │   ├── auth.js
│   │   └── ...
│   └── App.vue           # Root Vue component
└── views/
    ├── app.blade.php     # Main Blade template
    └── welcome.blade.php # Welcome page
```

## 🎨 UI Components

### Core Components

The UI kit includes the following core components:

#### 1. Form Inputs

```vue
<!-- Usage example -->
<Input
  v-model="form.phone"
  type="tel"
  label="Phone Number"
  placeholder="+1 (555) 123-4567"
  :error="errors.phone"
/>
```

#### 2. Buttons

```vue
<!-- Usage example -->
<Button
  type="submit"
  variant="primary"
  :loading="loading"
>
  Send Verification Code
</Button>
```

#### 3. OTP Input

```vue
<!-- Usage example -->
<OtpInput
  v-model="form.code"
  :length="6"
  :error="errors.code"
  @completed="verifyOtp"
/>
```

#### 4. Phone Input

```vue
<!-- Usage example -->
<PhoneInput
  v-model="form.phone"
  :countries="['US', 'CA', 'UK']"
  placeholder="Enter your phone number"
  :error="errors.phone"
/>
```

#### 5. Alerts

```vue
<!-- Usage example -->
<Alert
  type="success"
  :show="showAlert"
  dismissible
  @dismiss="showAlert = false"
>
  Verification code sent successfully!
</Alert>
```

### Authentication Components

#### Phone Verification Flow

1. **Phone Input Form**
   ```vue
   <PhoneForm @submit="sendOtp" />
   ```

2. **OTP Verification Form**
   ```vue
   <OtpVerificationForm
     :phone="form.phone"
     @verify="verifyOtp"
     @resend="resendOtp"
   />
   ```

3. **Authentication Success**
   ```vue
   <AuthSuccess :user="user" />
   ```

## 🔧 Customizing the UI

### Theme Customization

#### Tailwind Configuration

Customize the theme by editing `tailwind.config.js`:

```js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',  // Default primary color
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81',
        },
        secondary: {
          // Your secondary color palette
        },
        // Additional color definitions
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        // Additional font families
      },
      borderRadius: {
        'xl': '1rem',
        '2xl': '2rem',
      },
      // Additional theme customizations
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

#### Global CSS Variables

Define global CSS variables in `resources/css/app.css`:

```css
:root {
  --color-primary: #6366f1;
  --color-secondary: #8b5cf6;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-danger: #ef4444;
  --color-background: #ffffff;
  --font-family: 'Inter', sans-serif;
  --border-radius: 0.5rem;
}

/* Dark mode variables */
.dark {
  --color-primary: #818cf8;
  --color-secondary: #a78bfa;
  --color-background: #1f2937;
}
```

### Creating Custom Components

#### Component Template

Create a new component in `resources/js/components/custom/MyComponent.vue`:

```vue
<template>
  <div class="my-component">
    <slot></slot>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
  },
})

const classes = computed(() => ({
  'my-component--default': props.variant === 'default',
  'my-component--primary': props.variant === 'primary',
}))
</script>

<style scoped>
.my-component {
  padding: 1rem;
  border-radius: var(--border-radius);
}

.my-component--default {
  background-color: #f3f4f6;
}

.my-component--primary {
  background-color: var(--color-primary);
  color: white;
}
</style>
```

#### Register Global Components

Register custom components globally in `resources/js/app.js`:

```js
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'

// Import custom components
import MyComponent from './components/custom/MyComponent.vue'

const app = createApp(App)

// Register global components
app.component('MyComponent', MyComponent)

app.use(createPinia())
app.use(router)
app.mount('#app')
```

### Creating Custom Layouts

Create a new layout in `resources/js/layouts/CustomLayout.vue`:

```vue
<template>
  <div class="custom-layout">
    <header class="custom-layout__header">
      <slot name="header">
        <h1 class="text-2xl font-bold">{{ title }}</h1>
      </slot>
    </header>
    
    <main class="custom-layout__content">
      <slot></slot>
    </main>
    
    <footer class="custom-layout__footer">
      <slot name="footer">
        <p class="text-center text-gray-500">&copy; {{ new Date().getFullYear() }} Your Company</p>
      </slot>
    </footer>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Laravel Auth',
  },
})
</script>

<style scoped>
.custom-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.custom-layout__header {
  padding: 1rem;
  background-color: var(--color-background);
  border-bottom: 1px solid #e5e7eb;
}

.custom-layout__content {
  flex: 1;
  padding: 1rem;
}

.custom-layout__footer {
  padding: 1rem;
  background-color: var(--color-background);
  border-top: 1px solid #e5e7eb;
}
</style>
```

Use the custom layout in your pages:

```vue
<template>
  <CustomLayout title="Dashboard">
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <Button variant="secondary" @click="logout">Log out</Button>
      </div>
    </template>
    
    <div class="dashboard-content">
      <!-- Page content -->
    </div>
    
    <template #footer>
      <p class="text-center">Custom footer content</p>
    </template>
  </CustomLayout>
</template>

<script setup>
import CustomLayout from '@/layouts/CustomLayout.vue'
import Button from '@/components/common/Button.vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const logout = async () => {
  await authStore.logout()
}
</script>
```

## 🔄 State Management

### Pinia Store Setup

The authentication state is managed using Pinia stores:

```js
// resources/js/stores/auth.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    loading: false,
    error: null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.user && !!state.token,
    isPhoneVerified: (state) => state.user?.phone_verified_at !== null,
  },
  
  actions: {
    async sendOtp(phone) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/api/auth/phone/send-otp', { phone })
        return response.data
      } catch (error) {
        this.error = error.response?.data?.error?.message || 'Failed to send verification code'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async verifyOtp(phone, code) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/api/auth/phone/verify', { phone, code })
        this.token = response.data.data.token
        this.user = response.data.data.user
        
        localStorage.setItem('token', this.token)
        
        // Set axios default headers for subsequent requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.error?.message || 'Failed to verify code'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async logout() {
      this.loading = true
      
      try {
        if (this.token) {
          await axios.post('/api/auth/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('token')
        delete axios.defaults.headers.common['Authorization']
        this.loading = false
      }
    },
    
    async fetchUser() {
      if (!this.token) return
      
      this.loading = true
      
      try {
        const response = await axios.get('/api/user')
        this.user = response.data.data
        return this.user
      } catch (error) {
        this.error = error.response?.data?.error?.message || 'Failed to fetch user'
        // If unauthorized, clear state
        if (error.response?.status === 401) {
          this.logout()
        }
        throw error
      } finally {
        this.loading = false
      }
    },
  },
})
```

### Using the Store in Components

```vue
<template>
  <div>
    <PhoneInput
      v-model="phone"
      :error="authStore.error"
    />
    
    <Button
      type="submit"
      :loading="authStore.loading"
      @click="sendOtp"
    >
      Send Code
    </Button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import PhoneInput from '@/components/common/PhoneInput.vue'
import Button from '@/components/common/Button.vue'

const phone = ref('')
const router = useRouter()
const authStore = useAuthStore()

const sendOtp = async () => {
  try {
    await authStore.sendOtp(phone.value)
    router.push({ name: 'verify', params: { phone: phone.value } })
  } catch (error) {
    console.error(error)
  }
}
</script>
```

## 📱 Responsive Design

### Mobile-First Approach

All components are designed with a mobile-first approach using Tailwind CSS breakpoints:

```vue
<template>
  <div class="auth-container p-4 md:p-8 lg:p-12">
    <div class="auth-card w-full max-w-md mx-auto rounded-lg shadow-md bg-white p-6 md:p-8">
      <!-- Content -->
    </div>
  </div>
</template>
```

### Custom Breakpoints

Add custom breakpoints in `tailwind.config.js`:

```js
module.exports = {
  theme: {
    screens: {
      'xs': '475px',
      'sm': '640px',
      'md': '768px',
      'lg': '1024px',
      'xl': '1280px',
      '2xl': '1536px',
    },
  },
}
```

## 🌐 Internationalization

### Setting Up Vue I18n

1. Install Vue I18n:

```bash
npm install vue-i18n@next
```

2. Create translation files:

```js
// resources/js/i18n/en.js
export default {
  auth: {
    phoneVerification: 'Phone Verification',
    enterPhone: 'Enter your phone number',
    sendCode: 'Send Code',
    enterCode: 'Enter verification code',
    verify: 'Verify',
    resend: 'Resend Code',
    success: 'Successfully verified!',
    errors: {
      invalidPhone: 'Please enter a valid phone number',
      invalidCode: 'Please enter a valid verification code',
    },
  },
}
```

3. Set up Vue I18n:

```js
// resources/js/i18n/index.js
import { createI18n } from 'vue-i18n'
import en from './en'
import es from './es'

export default createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  messages: {
    en,
    es,
  },
})
```

4. Register with Vue app:

```js
// resources/js/app.js
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import i18n from './i18n'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)
app.mount('#app')
```

5. Use in components:

```vue
<template>
  <h1>{{ $t('auth.phoneVerification') }}</h1>
  <p>{{ $t('auth.enterPhone') }}</p>
</template>
```

### Language Switcher Component

```vue
<template>
  <div class="language-switcher">
    <select v-model="locale" class="form-select">
      <option v-for="(lang, code) in availableLocales" :key="code" :value="code">
        {{ lang.name }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()

const availableLocales = {
  en: { name: 'English' },
  es: { name: 'Español' },
  fr: { name: 'Français' },
}

watch(locale, (newLocale) => {
  localStorage.setItem('locale', newLocale)
})
</script>
```

## 🧪 Testing UI Components

### Vue Test Utils Setup

1. Install testing dependencies:

```bash
npm install --save-dev @vue/test-utils vitest @testing-library/vue
```

2. Create a test file:

```js
// tests/components/PhoneForm.test.js
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import PhoneForm from '@/components/auth/PhoneForm.vue'

describe('PhoneForm', () => {
  it('emits submit event with phone number when form is submitted', async () => {
    const wrapper = mount(PhoneForm)
    
    // Set phone number input value
    await wrapper.find('input[type="tel"]').setValue('+12125550123')
    
    // Submit the form
    await wrapper.find('form').trigger('submit.prevent')
    
    // Check that the submit event was emitted with correct value
    expect(wrapper.emitted('submit')).toBeTruthy()
    expect(wrapper.emitted('submit')[0]).toEqual(['+12125550123'])
  })
  
  it('displays error message when phone number is invalid', async () => {
    const wrapper = mount(PhoneForm, {
      props: {
        errors: {
          phone: 'Invalid phone number'
        }
      }
    })
    
    // Check that error message is displayed
    expect(wrapper.find('.error-message').text()).toBe('Invalid phone number')
  })
})
```

### Running Tests

Add test script to `package.json`:

```json
"scripts": {
  "test": "vitest",
  "test:coverage": "vitest run --coverage"
}
```

Run tests:

```bash
npm test
```

## 📱 Progressive Web App (PWA)

### Setting Up PWA

1. Install Vite PWA plugin:

```bash
npm install --save-dev vite-plugin-pwa
```

2. Configure in `vite.config.js`:

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'masked-icon.svg'],
      manifest: {
        name: 'Laravel Auth',
        short_name: 'LaravelAuth',
        description: 'Laravel Authentication Starter Kit',
        theme_color: '#6366f1',
        icons: [
          {
            src: 'pwa-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          },
          {
            src: 'pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any maskable'
          }
        ]
      },
    }),
  ],
});
```

3. Create the PWA icons in the `public` directory.

## 📊 Analytics Integration

### Event Tracking

```js
// resources/js/utils/analytics.js
export const trackEvent = (category, action, label = null, value = null) => {
  if (window.gtag) {
    window.gtag('event', action, {
      event_category: category,
      event_label: label,
      value: value,
    })
  }
}
```

Usage in components:

```vue
<script setup>
import { trackEvent } from '@/utils/analytics'

const sendOtp = async () => {
  try {
    await authStore.sendOtp(phone.value)
    trackEvent('Authentication', 'OTP Sent', phone.value)
    router.push({ name: 'verify', params: { phone: phone.value } })
  } catch (error) {
    trackEvent('Authentication', 'OTP Send Error', error.message)
    console.error(error)
  }
}
</script>
```

## 🚀 Performance Optimization

### Code Splitting

Configure Vue Router for code splitting:

```js
// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/Home.vue')
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/Login.vue')
  },
  {
    path: '/verify/:phone',
    name: 'verify',
    component: () => import('@/pages/auth/Verify.vue'),
    props: true
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/pages/Dashboard.vue'),
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else {
    next()
  }
})

export default router
```

### Asset Optimization

Configure Vite for asset optimization in `vite.config.js`:

```js
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vue: ['vue', 'vue-router', 'pinia'],
          vendor: ['axios', 'lodash'],
        }
      }
    },
    chunkSizeWarningLimit: 1000
  }
})
```

## 🚀 Next Steps

- Check out the [API Documentation](api.md) for integrating with the frontend
- Learn about [SMS Gateway Integration](sms-gateways.md) for phone authentication
- Review the [Security Guide](security.md) for securing your application
- Explore [Deployment Options](deployment.md) for deploying your application

---

**Need help with the frontend?** [Open a GitHub issue](https://github.com/yourusername/laravel-auth-starter-kits/issues/new) or reach out on [Discord](https://discord.gg/your-discord).
