import { mount as vtuMount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import { createI18n } from 'vue-i18n'
import { createPinia } from 'pinia'

/**
 * Creates a custom mount function with common plugins
 */
export function createTestMount(options = {}) {
    const router = createRouter({
        history: createMemoryHistory(),
        routes: options.routes || [
            { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
            { path: '/login', name: 'login', component: { template: '<div>Login</div>' } },
            { path: '/register', name: 'register', component: { template: '<div>Register</div>' } },
            { path: '/dashboard', name: 'dashboard', component: { template: '<div>Dashboard</div>' } },
            { path: '/verify', name: 'verify', component: { template: '<div>Verify</div>' } },
        ],
    })

    const i18n = createI18n({
        legacy: false,
        locale: 'en',
        fallbackLocale: 'en',
        messages: options.messages || {
            en: {
                auth: {
                    login: 'Login',
                    logout: 'Logout',
                    register: 'Register',
                    email: 'Email Address',
                    password: 'Password',
                    error: 'An error occurred',
                },
            },
        },
    })

    const pinia = createPinia()

    return (component, mountOptions = {}) => {
        return vtuMount(component, {
            global: {
                plugins: [router, i18n, pinia, ...(mountOptions.global?.plugins || [])],
                stubs: {
                    RouterLink: true,
                    RouterView: true,
                    ...mountOptions.global?.stubs,
                },
                mocks: mountOptions.global?.mocks,
            },
            ...mountOptions,
        })
    }
}

/**
 * Wait for component to finish loading
 */
export async function waitForComponent(wrapper) {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))
}

/**
 * Find element by test id
 */
export function findByTestId(wrapper, testId) {
    return wrapper.find(`[data-testid="${testId}"]`)
}

/**
 * Fill form fields
 */
export async function fillForm(wrapper, fields) {
    for (const [selector, value] of Object.entries(fields)) {
        const input = wrapper.find(selector)
        if (input.exists()) {
            await input.setValue(value)
        }
    }
}

/**
 * Submit form
 */
export async function submitForm(wrapper, formSelector = 'form') {
    await wrapper.find(formSelector).trigger('submit')
    await waitForComponent(wrapper)
}
