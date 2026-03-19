import { beforeAll, afterEach, vi } from 'vitest'
import { config } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'

// Create mock i18n instance
const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
        en: {
            auth: {
                login: 'Login',
                logout: 'Logout',
                register: 'Register',
                email: 'Email Address',
                password: 'Password',
                remember_me: 'Remember Me',
                forgot_password: 'Forgot Password?',
                confirm_password: 'Confirm Password',
                name: 'Name',
                sign_in: 'Sign In',
                sign_up: 'Sign Up',
                enter_otp: 'Enter Verification Code',
                resend_otp: 'Resend Code',
                two_factor: 'Two-Factor Authentication',
                continue_with: 'Continue with {provider}',
                error: 'An error occurred',
            },
        },
    },
})

// Global test configuration
config.global.plugins = [i18n]

// Mock window.matchMedia
beforeAll(() => {
    Object.defineProperty(window, 'matchMedia', {
        writable: true,
        value: vi.fn().mockImplementation(query => ({
            matches: false,
            media: query,
            onchange: null,
            addListener: vi.fn(),
            removeListener: vi.fn(),
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
            dispatchEvent: vi.fn(),
        })),
    })

    // Mock localStorage
    const localStorageMock = {
        getItem: vi.fn(),
        setItem: vi.fn(),
        removeItem: vi.fn(),
        clear: vi.fn(),
    }
    Object.defineProperty(window, 'localStorage', { value: localStorageMock })

    // Mock sessionStorage
    const sessionStorageMock = {
        getItem: vi.fn(),
        setItem: vi.fn(),
        removeItem: vi.fn(),
        clear: vi.fn(),
    }
    Object.defineProperty(window, 'sessionStorage', { value: sessionStorageMock })
})

// Reset mocks after each test
afterEach(() => {
    vi.clearAllMocks()
})
