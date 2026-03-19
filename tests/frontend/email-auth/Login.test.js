import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestMount, fillForm, submitForm, findByTestId } from '../utils/mount.js'
import { mockApiResponse, mockApiError, mockLoginSuccess, validationErrors } from '../utils/api-mocks.js'

// Mock Login Component
const LoginComponent = {
    template: `
        <div>
            <h1>{{ $t('auth.login') }}</h1>
            <div v-if="error" class="error" data-testid="error">{{ error }}</div>
            <form @submit.prevent="handleSubmit" data-testid="login-form">
                <div>
                    <label for="email">{{ $t('auth.email') }}</label>
                    <input 
                        id="email" 
                        v-model="form.email" 
                        type="email" 
                        data-testid="email-input"
                        :class="{ 'is-invalid': errors.email }"
                    />
                    <span v-if="errors.email" class="error-text">{{ errors.email[0] }}</span>
                </div>
                <div>
                    <label for="password">{{ $t('auth.password') }}</label>
                    <input 
                        id="password" 
                        v-model="form.password" 
                        type="password" 
                        data-testid="password-input"
                    />
                </div>
                <div>
                    <input type="checkbox" v-model="form.remember" id="remember" data-testid="remember-checkbox" />
                    <label for="remember">{{ $t('auth.remember_me') }}</label>
                </div>
                <button type="submit" :disabled="loading" data-testid="submit-btn">
                    {{ loading ? 'Loading...' : $t('auth.sign_in') }}
                </button>
            </form>
            <a href="/forgot-password" data-testid="forgot-link">{{ $t('auth.forgot_password') }}</a>
        </div>
    `,
    data() {
        return {
            form: { email: '', password: '', remember: false },
            loading: false,
            error: '',
            errors: {},
        }
    },
    methods: {
        async handleSubmit() {
            this.loading = true
            this.error = ''
            this.errors = {}
            
            try {
                const response = await this.login(this.form)
                this.$emit('login-success', response)
            } catch (e) {
                if (e.response?.data?.errors) {
                    this.errors = e.response.data.errors
                }
                this.error = e.response?.data?.message || 'An error occurred'
                this.$emit('login-error', e)
            } finally {
                this.loading = false
            }
        },
        login: vi.fn(),
    },
}

describe('Login Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    describe('Rendering', () => {
        it('renders login form with all fields', () => {
            const wrapper = mountComponent(LoginComponent)
            
            expect(wrapper.find('[data-testid="login-form"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="email-input"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="password-input"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="remember-checkbox"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="submit-btn"]').exists()).toBe(true)
        })

        it('renders forgot password link', () => {
            const wrapper = mountComponent(LoginComponent)
            expect(wrapper.find('[data-testid="forgot-link"]').exists()).toBe(true)
        })

        it('displays translated labels', () => {
            const wrapper = mountComponent(LoginComponent)
            expect(wrapper.text()).toContain('Login')
            expect(wrapper.text()).toContain('Email Address')
            expect(wrapper.text()).toContain('Password')
        })
    })

    describe('Form Interaction', () => {
        it('updates form data on input', async () => {
            const wrapper = mountComponent(LoginComponent)
            
            await wrapper.find('[data-testid="email-input"]').setValue('test@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('password123')
            await wrapper.find('[data-testid="remember-checkbox"]').setValue(true)

            expect(wrapper.vm.form.email).toBe('test@example.com')
            expect(wrapper.vm.form.password).toBe('password123')
            expect(wrapper.vm.form.remember).toBe(true)
        })

        it('disables submit button while loading', async () => {
            const wrapper = mountComponent(LoginComponent)
            wrapper.vm.loading = true
            await wrapper.vm.$nextTick()

            expect(wrapper.find('[data-testid="submit-btn"]').attributes('disabled')).toBeDefined()
        })
    })

    describe('Form Submission', () => {
        it('calls login method on submit', async () => {
            const wrapper = mountComponent(LoginComponent)
            const loginFn = vi.fn().mockResolvedValue(mockLoginSuccess)
            wrapper.vm.login = loginFn

            await wrapper.find('[data-testid="email-input"]').setValue('test@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('password123')
            await wrapper.find('[data-testid="login-form"]').trigger('submit')

            expect(loginFn).toHaveBeenCalledWith({
                email: 'test@example.com',
                password: 'password123',
                remember: false,
            })
        })

        it('emits login-success event on successful login', async () => {
            const wrapper = mountComponent(LoginComponent)
            wrapper.vm.login = vi.fn().mockResolvedValue(mockLoginSuccess)

            await wrapper.find('[data-testid="email-input"]').setValue('test@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('password123')
            await wrapper.find('[data-testid="login-form"]').trigger('submit')
            await wrapper.vm.$nextTick()

            expect(wrapper.emitted('login-success')).toBeTruthy()
        })

        it('displays error message on failed login', async () => {
            const wrapper = mountComponent(LoginComponent)
            wrapper.vm.login = vi.fn().mockRejectedValue({
                response: { data: { message: 'Invalid credentials.' } }
            })

            await wrapper.find('[data-testid="email-input"]').setValue('test@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('wrong')
            await wrapper.find('[data-testid="login-form"]').trigger('submit')
            await wrapper.vm.$nextTick()

            expect(wrapper.find('[data-testid="error"]').text()).toBe('Invalid credentials.')
        })

        it('displays field validation errors', async () => {
            const wrapper = mountComponent(LoginComponent)
            wrapper.vm.login = vi.fn().mockRejectedValue({
                response: {
                    data: {
                        message: 'Validation failed.',
                        errors: { email: ['The email field is required.'] }
                    }
                }
            })

            await wrapper.find('[data-testid="login-form"]').trigger('submit')
            await wrapper.vm.$nextTick()

            expect(wrapper.text()).toContain('The email field is required.')
        })
    })
})
