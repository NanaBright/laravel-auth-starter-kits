import { vi } from 'vitest'

/**
 * Mock API response helper
 */
export function mockApiResponse(data, status = 200) {
    return Promise.resolve({
        status,
        data,
        headers: {},
    })
}

/**
 * Mock API error helper
 */
export function mockApiError(message, status = 400, errors = {}) {
    return Promise.reject({
        response: {
            status,
            data: {
                message,
                errors,
            },
        },
    })
}

/**
 * Create axios mock
 */
export function createAxiosMock() {
    return {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        defaults: {
            headers: {
                common: {},
            },
        },
        interceptors: {
            request: { use: vi.fn(), eject: vi.fn() },
            response: { use: vi.fn(), eject: vi.fn() },
        },
    }
}

/**
 * Mock successful login response
 */
export const mockLoginSuccess = {
    user: {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        email_verified_at: '2024-01-01T00:00:00.000000Z',
    },
    token: 'mock-jwt-token',
}

/**
 * Mock registration response
 */
export const mockRegisterSuccess = {
    user: {
        id: 1,
        name: 'New User',
        email: 'new@example.com',
        email_verified_at: null,
    },
    message: 'Please verify your email address.',
}

/**
 * Mock OTP sent response
 */
export const mockOTPSent = {
    message: 'Verification code sent.',
    expires_in: 300,
}

/**
 * Mock OTP verification response
 */
export const mockOTPVerified = {
    user: {
        id: 1,
        name: 'Test User',
        phone: '+1234567890',
    },
    token: 'mock-jwt-token',
}

/**
 * Mock 2FA enable response
 */
export const mock2FAEnabled = {
    secret: 'JBSWY3DPEHPK3PXP',
    qr_code: 'data:image/svg+xml;base64,...',
    recovery_codes: [
        'AAAA-BBBB-CCCC',
        'DDDD-EEEE-FFFF',
        'GGGG-HHHH-IIII',
        'JJJJ-KKKK-LLLL',
        'MMMM-NNNN-OOOO',
        'PPPP-QQQQ-RRRR',
        'SSSS-TTTT-UUUU',
        'VVVV-WWWW-XXXX',
    ],
}

/**
 * Mock social auth URL response
 */
export const mockSocialAuthUrl = (provider) => ({
    url: `https://oauth.${provider}.com/authorize?client_id=xxx&redirect_uri=xxx`,
})

/**
 * Validation error responses
 */
export const validationErrors = {
    email: {
        email: ['The email field is required.'],
    },
    password: {
        password: ['The password field is required.'],
    },
    invalidCredentials: {
        message: 'Invalid credentials.',
        errors: {
            email: ['These credentials do not match our records.'],
        },
    },
    invalidOTP: {
        message: 'Invalid verification code.',
        errors: {
            code: ['The verification code is invalid or expired.'],
        },
    },
    invalid2FA: {
        message: 'Invalid authentication code.',
        errors: {
            code: ['The authentication code is invalid.'],
        },
    },
}
