import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestMount } from '../utils/mount.js'
import { mockRegisterSuccess } from '../utils/api-mocks.js'

// Mock Register Component
const RegisterComponent = {
    template: `
        <div>
            <h1>Create Account</h1>
            <div v-if="success" class="success" data-testid="success">{{ successMessage }}</div>
            <div v-if="error" class="error" data-testid="error">{{ error }}</div>
            <form @submit.prevent="handleSubmit" data-testid="register-form">
                <div>
                    <label for="name">Name</label>
                    <input 
                        id="name" 
                        v-model="form.name" 
                        type="text" 
                        data-testid="name-input"
                    />
                    <span v-if="errors.name" class="error-text" data-testid="name-error">{{ errors.name[0] }}</span>
                </div>
                <div>
                    <label for="email">Email Address</label>
                    <input 
                        id="email" 
                        v-model="form.email" 
                        type="email" 
                        data-testid="email-input"
                    />
                    <span v-if="errors.email" class="error-text" data-testid="email-error">{{ errors.email[0] }}</span>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input 
                        id="password" 
                        v-model="form.password" 
                        type="password" 
                        data-testid="password-input"
                    />
                    <span v-if="errors.password" class="error-text" data-testid="password-error">{{ errors.password[0] }}</span>
                </div>
                <div>
                    <label for="password_confirmation">Confirm Password</label>
                    <input 
                        id="password_confirmation" 
                        v-model="form.password_confirmation" 
                        type="password" 
                        data-testid="password-confirm-input"
                    />
                </div>
                <button type="submit" :disabled="loading" data-testid="submit-btn">
                    {{ loading ? 'Creating...' : 'Create Account' }}
                </button>
            </form>
        </div>
    `,
    data() {
        return {
            form: { name: '', email: '', password: '', password_confirmation: '' },
            loading: false,
            error: '',
            errors: {},
            success: false,
            successMessage: '',
        }
    },
    methods: {
        async handleSubmit() {
            this.loading = true
            this.error = ''
            this.errors = {}
            
            // Client-side validation
            if (this.form.password !== this.form.password_confirmation) {
                this.errors.password = ['Passwords do not match.']
                this.loading = false
                return
            }

            try {
                const response = await this.register(this.form)
                this.success = true
                this.successMessage = response.message || 'Account created successfully.'
                this.$emit('register-success', response)
            } catch (e) {
                if (e.response?.data?.errors) {
                    this.errors = e.response.data.errors
                }
                this.error = e.response?.data?.message || 'An error occurred'
                this.$emit('register-error', e)
            } finally {
                this.loading = false
            }
        },
        register: vi.fn(),
    },
}

describe('Register Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    describe('Rendering', () => {
        it('renders registration form with all fields', () => {
            const wrapper = mountComponent(RegisterComponent)
            
            expect(wrapper.find('[data-testid="register-form"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="name-input"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="email-input"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="password-input"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="password-confirm-input"]').exists()).toBe(true)
        })
    })

    describe('Form Validation', () => {
        it('validates password confirmation', async () => {
            const wrapper = mountComponent(RegisterComponent)
            
            await wrapper.find('[data-testid="name-input"]').setValue('Test User')
            await wrapper.find('[data-testid="email-input"]').setValue('test@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('password123')
            await wrapper.find('[data-testid="password-confirm-input"]').setValue('password456')
            await wrapper.find('[data-testid="register-form"]').trigger('submit')

            expect(wrapper.find('[data-testid="password-error"]').text()).toBe('Passwords do not match.')
        })
    })

    describe('Form Submission', () => {
        it('calls register method with form data', async () => {
            const wrapper = mountComponent(RegisterComponent)
            const registerFn = vi.fn().mockResolvedValue(mockRegisterSuccess)
            wrapper.vm.register = registerFn

            await wrapper.find('[data-testid="name-input"]').setValue('Test User')
            await wrapper.find('[data-testid="email-input"]').setValue('test@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('password123')
            await wrapper.find('[data-testid="password-confirm-input"]').setValue('password123')
            await wrapper.find('[data-testid="register-form"]').trigger('submit')

            expect(registerFn).toHaveBeenCalledWith({
                name: 'Test User',
                email: 'test@example.com',
                password: 'password123',
                password_confirmation: 'password123',
            })
        })

        it('displays success message on successful registration', async () => {
            const wrapper = mountComponent(RegisterComponent)
            wrapper.vm.register = vi.fn().mockResolvedValue({
                message: 'Please verify your email address.'
            })

            await wrapper.find('[data-testid="name-input"]').setValue('Test User')
            await wrapper.find('[data-testid="email-input"]').setValue('test@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('password123')
            await wrapper.find('[data-testid="password-confirm-input"]').setValue('password123')
            await wrapper.find('[data-testid="register-form"]').trigger('submit')
            await wrapper.vm.$nextTick()

            expect(wrapper.find('[data-testid="success"]').text()).toBe('Please verify your email address.')
        })

        it('displays email taken error', async () => {
            const wrapper = mountComponent(RegisterComponent)
            wrapper.vm.register = vi.fn().mockRejectedValue({
                response: {
                    data: {
                        message: 'Email already taken.',
                        errors: { email: ['The email has already been taken.'] }
                    }
                }
            })

            await wrapper.find('[data-testid="name-input"]').setValue('Test User')
            await wrapper.find('[data-testid="email-input"]').setValue('existing@example.com')
            await wrapper.find('[data-testid="password-input"]').setValue('password123')
            await wrapper.find('[data-testid="password-confirm-input"]').setValue('password123')
            await wrapper.find('[data-testid="register-form"]').trigger('submit')
            await wrapper.vm.$nextTick()

            expect(wrapper.find('[data-testid="email-error"]').text()).toBe('The email has already been taken.')
        })
    })
})
