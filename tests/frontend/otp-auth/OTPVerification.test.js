import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createTestMount } from '../utils/mount.js'
import { mockOTPSent, mockOTPVerified } from '../utils/api-mocks.js'

// Mock OTP Verification Component
const OTPVerificationComponent = {
    template: `
        <div>
            <h1>Verify Your Phone</h1>
            <p v-if="destination" data-testid="destination-info">
                Enter the code sent to {{ destination }}
            </p>
            <div v-if="error" class="error" data-testid="error">{{ error }}</div>
            <form @submit.prevent="handleVerify" data-testid="otp-form">
                <div class="otp-inputs" data-testid="otp-inputs">
                    <input
                        v-for="(digit, index) in digits"
                        :key="index"
                        v-model="code[index]"
                        type="text"
                        maxlength="1"
                        :data-testid="'otp-digit-' + index"
                        @input="handleInput(index, $event)"
                        @keydown="handleKeydown(index, $event)"
                        ref="inputs"
                    />
                </div>
                <button type="submit" :disabled="loading || !isComplete" data-testid="verify-btn">
                    {{ loading ? 'Verifying...' : 'Verify' }}
                </button>
            </form>
            <div class="resend-section">
                <button 
                    @click="handleResend" 
                    :disabled="resendDisabled || resending"
                    data-testid="resend-btn"
                >
                    {{ resending ? 'Sending...' : resendText }}
                </button>
            </div>
            <p v-if="countdown > 0" data-testid="countdown">
                Resend available in {{ countdown }}s
            </p>
        </div>
    `,
    props: {
        destination: { type: String, default: '+1 *** *** 7890' },
        digits: { type: Number, default: 6 },
    },
    data() {
        return {
            code: Array(this.digits).fill(''),
            loading: false,
            resending: false,
            error: '',
            countdown: 0,
        }
    },
    computed: {
        isComplete() {
            return this.code.every(d => d !== '')
        },
        resendDisabled() {
            return this.countdown > 0
        },
        resendText() {
            return this.countdown > 0 ? `Resend in ${this.countdown}s` : 'Resend Code'
        },
        fullCode() {
            return this.code.join('')
        },
    },
    methods: {
        handleInput(index, event) {
            const value = event.target.value.replace(/\D/g, '')
            this.code[index] = value

            // Auto-focus next input
            if (value && index < this.digits - 1) {
                this.$refs.inputs[index + 1]?.focus()
            }
        },
        handleKeydown(index, event) {
            if (event.key === 'Backspace' && !this.code[index] && index > 0) {
                this.$refs.inputs[index - 1]?.focus()
            }
        },
        async handleVerify() {
            this.loading = true
            this.error = ''

            try {
                const response = await this.verifyOTP(this.fullCode)
                this.$emit('verified', response)
            } catch (e) {
                this.error = e.response?.data?.message || 'Invalid code'
                this.$emit('error', e)
            } finally {
                this.loading = false
            }
        },
        async handleResend() {
            this.resending = true
            this.error = ''

            try {
                await this.resendOTP()
                this.startCountdown(60)
                this.$emit('resent')
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to resend code'
            } finally {
                this.resending = false
            }
        },
        startCountdown(seconds) {
            this.countdown = seconds
            const interval = setInterval(() => {
                this.countdown--
                if (this.countdown <= 0) {
                    clearInterval(interval)
                }
            }, 1000)
        },
        verifyOTP: vi.fn(),
        resendOTP: vi.fn(),
    },
}

describe('OTP Verification Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    describe('Rendering', () => {
        it('renders OTP input fields', () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            
            expect(wrapper.find('[data-testid="otp-form"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="otp-inputs"]').exists()).toBe(true)
            // Should have 6 digit inputs by default
            for (let i = 0; i < 6; i++) {
                expect(wrapper.find(`[data-testid="otp-digit-${i}"]`).exists()).toBe(true)
            }
        })

        it('displays destination info', () => {
            const wrapper = mountComponent(OTPVerificationComponent, {
                props: { destination: '+1 555 123 4567' }
            })
            
            expect(wrapper.find('[data-testid="destination-info"]').text()).toContain('+1 555 123 4567')
        })

        it('renders verify and resend buttons', () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            
            expect(wrapper.find('[data-testid="verify-btn"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="resend-btn"]').exists()).toBe(true)
        })
    })

    describe('OTP Input', () => {
        it('accepts only numeric input', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            const input = wrapper.find('[data-testid="otp-digit-0"]')
            
            await input.setValue('a')
            expect(wrapper.vm.code[0]).toBe('')

            await input.setValue('1')
            expect(wrapper.vm.code[0]).toBe('1')
        })

        it('computes full code correctly', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            
            for (let i = 0; i < 6; i++) {
                await wrapper.find(`[data-testid="otp-digit-${i}"]`).setValue(String(i + 1))
            }
            
            expect(wrapper.vm.fullCode).toBe('123456')
        })

        it('disables verify button when code incomplete', () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            
            expect(wrapper.find('[data-testid="verify-btn"]').attributes('disabled')).toBeDefined()
        })

        it('enables verify button when code is complete', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            
            for (let i = 0; i < 6; i++) {
                wrapper.vm.code[i] = String(i + 1)
            }
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="verify-btn"]').attributes('disabled')).toBeUndefined()
        })
    })

    describe('Verification', () => {
        it('calls verifyOTP with correct code', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            const verifyFn = vi.fn().mockResolvedValue(mockOTPVerified)
            wrapper.vm.verifyOTP = verifyFn

            for (let i = 0; i < 6; i++) {
                wrapper.vm.code[i] = String(i + 1)
            }
            await wrapper.vm.$nextTick()
            
            await wrapper.find('[data-testid="otp-form"]').trigger('submit')
            
            expect(verifyFn).toHaveBeenCalledWith('123456')
        })

        it('emits verified event on success', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            wrapper.vm.verifyOTP = vi.fn().mockResolvedValue(mockOTPVerified)

            for (let i = 0; i < 6; i++) {
                wrapper.vm.code[i] = '1'
            }
            await wrapper.vm.$nextTick()
            
            await wrapper.find('[data-testid="otp-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.emitted('verified')).toBeTruthy()
        })

        it('displays error on invalid code', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            wrapper.vm.verifyOTP = vi.fn().mockRejectedValue({
                response: { data: { message: 'Invalid verification code.' } }
            })

            for (let i = 0; i < 6; i++) {
                wrapper.vm.code[i] = '0'
            }
            await wrapper.vm.$nextTick()
            
            await wrapper.find('[data-testid="otp-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="error"]').text()).toBe('Invalid verification code.')
        })
    })

    describe('Resend', () => {
        it('calls resendOTP on button click', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            const resendFn = vi.fn().mockResolvedValue(mockOTPSent)
            wrapper.vm.resendOTP = resendFn

            await wrapper.find('[data-testid="resend-btn"]').trigger('click')
            
            expect(resendFn).toHaveBeenCalled()
        })

        it('starts countdown after resend', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            wrapper.vm.resendOTP = vi.fn().mockResolvedValue(mockOTPSent)

            await wrapper.find('[data-testid="resend-btn"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.countdown).toBe(60)
        })

        it('disables resend button during countdown', async () => {
            const wrapper = mountComponent(OTPVerificationComponent)
            wrapper.vm.countdown = 30
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="resend-btn"]').attributes('disabled')).toBeDefined()
        })
    })
})
