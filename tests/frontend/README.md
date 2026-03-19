# Frontend Tests

Comprehensive test suite for all authentication kits using Vitest and Vue Test Utils.

## Overview

This directory contains frontend tests for:
- **Email Magic Link Auth** (`email-auth/`)
- **Phone/OTP Auth** (`otp-auth/`)
- **Social OAuth** (`social-auth/`)
- **Two-Factor Authentication** (`2fa/`)
- **Common Components** (`components/`)

## Setup

### Install Dependencies

```bash
npm install -D vitest @vue/test-utils @testing-library/vue @testing-library/jest-dom happy-dom
```

### Configure Vitest

Add to `vite.config.js`:

```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'happy-dom',
    setupFiles: ['./tests/setup.js'],
    include: ['tests/**/*.test.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
    },
  },
})
```

### Add Test Scripts

Add to `package.json`:

```json
{
  "scripts": {
    "test": "vitest",
    "test:run": "vitest run",
    "test:coverage": "vitest run --coverage",
    "test:ui": "vitest --ui"
  }
}
```

## Running Tests

```bash
# Run all tests in watch mode
npm test

# Run tests once
npm run test:run

# Run with coverage
npm run test:coverage

# Run specific test file
npm test -- tests/email-auth/Login.test.js

# Run tests matching pattern
npm test -- --grep "Login"
```

## Test Structure

```
tests/
├── README.md
├── setup.js                    # Global test setup
├── utils/
│   ├── mount.js               # Custom mount helpers
│   └── api-mocks.js           # API mock utilities
├── email-auth/
│   ├── Login.test.js
│   ├── Register.test.js
│   └── MagicLink.test.js
├── otp-auth/
│   ├── PhoneLogin.test.js
│   ├── OTPVerification.test.js
│   └── ResendOTP.test.js
├── social-auth/
│   ├── SocialButtons.test.js
│   ├── OAuth.test.js
│   └── AccountLinking.test.js
├── 2fa/
│   ├── TwoFactorSetup.test.js
│   ├── TwoFactorVerify.test.js
│   └── RecoveryCodes.test.js
└── components/
    ├── LanguageSwitcher.test.js
    └── AuthForm.test.js
```

## Writing Tests

### Component Test Example

```javascript
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import Login from '@/pages/Login.vue'

describe('Login', () => {
  it('renders login form', () => {
    const wrapper = mount(Login)
    expect(wrapper.find('form').exists()).toBe(true)
    expect(wrapper.find('input[type="email"]').exists()).toBe(true)
  })

  it('validates email field', async () => {
    const wrapper = mount(Login)
    await wrapper.find('input[type="email"]').setValue('invalid')
    await wrapper.find('form').trigger('submit')
    expect(wrapper.text()).toContain('valid email')
  })

  it('submits form with valid data', async () => {
    const wrapper = mount(Login)
    await wrapper.find('input[type="email"]').setValue('test@example.com')
    await wrapper.find('input[type="password"]').setValue('password123')
    await wrapper.find('form').trigger('submit')
    // Assert API call or state change
  })
})
```

### API Mocking

```javascript
import { vi } from 'vitest'
import axios from 'axios'

vi.mock('axios')

describe('with mocked API', () => {
  it('handles successful login', async () => {
    axios.post.mockResolvedValueOnce({
      data: { user: { id: 1 }, token: 'abc123' }
    })

    // Test component behavior
  })

  it('handles API error', async () => {
    axios.post.mockRejectedValueOnce({
      response: { status: 401, data: { message: 'Invalid credentials' } }
    })

    // Test error handling
  })
})
```

## Coverage Goals

| Category | Target |
|----------|--------|
| Statements | 80% |
| Branches | 75% |
| Functions | 80% |
| Lines | 80% |

## Best Practices

1. **Isolate tests** - Each test should be independent
2. **Mock external dependencies** - API calls, localStorage, etc.
3. **Test user behavior** - Focus on what users see and do
4. **Use data-testid** - For stable element selection
5. **Avoid implementation details** - Test outputs, not internals
