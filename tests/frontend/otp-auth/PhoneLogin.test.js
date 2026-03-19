import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createTestMount } from '../utils/mount.js'

// Mock Phone Login Component
const PhoneLoginComponent = {
    template: `
        <div class="phone-login" data-testid="phone-login">
            <h1>Login with Phone</h1>
            
            <!-- Step 1: Phone Number -->
            <div v-if="step === 1" data-testid="phone-step">
                <form @submit.prevent="sendOTP" data-testid="phone-form">
                    <label for="phone">Phone Number</label>
                    <div class="phone-input-group">
                        <select v-model="countryCode" data-testid="country-code">
                            <option value="+1">+1 (US)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+91">+91 (IN)</option>
                        </select>
                        <input
                            id="phone"
                            v-model="phoneNumber"
                            type="tel"
                            placeholder="555-123-4567"
                            data-testid="phone-input"
                        />
                    </div>
                    <div v-if="phoneError" class="error" data-testid="phone-error">{{ phoneError }}</div>
                    <button type="submit" :disabled="sending" data-testid="send-otp-btn">
                        {{ sending ? 'Sending...' : 'Send Code' }}
                    </button>
                </form>
            </div>

            <!-- Step 2: OTP Verification -->
            <div v-if="step === 2" data-testid="otp-step">
                <p data-testid="phone-display">Code sent to {{ fullPhone }}</p>
                <form @submit.prevent="verifyOTP" data-testid="otp-form">
                    <div class="otp-inputs">
                        <input
                            v-for="i in 6"
                            :key="i"
                            v-model="otpDigits[i - 1]"
                            type="text"
                            maxlength="1"
                            :data-testid="'otp-' + (i - 1)"
                        />
                    </div>
                    <div v-if="otpError" class="error" data-testid="otp-error">{{ otpError }}</div>
                    <button type="submit" :disabled="verifying || !isOTPComplete" data-testid="verify-btn">
                        {{ verifying ? 'Verifying...' : 'Verify' }}
                    </button>
                </form>
                <button @click="step = 1" data-testid="change-phone-btn">Change phone number</button>
            </div>
        </div>
    `,
    data() {
        return {
            step: 1,
            countryCode: '+1',
            phoneNumber: '',
            otpDigits: Array(6).fill(''),
            sending: false,
            verifying: false,
            phoneError: '',
            otpError: '',
        }
    },
    computed: {
        fullPhone() {
            return `${this.countryCode} ${this.phoneNumber}`
        },
        isOTPComplete() {
            return this.otpDigits.every(d => d !== '')
        },
        otpCode() {
            return this.otpDigits.join('')
        },
    },
    methods: {
        async sendOTP() {
            this.sending = true
            this.phoneError = ''

            // Validate phone
            if (!this.phoneNumber || this.phoneNumber.length < 10) {
                this.phoneError = 'Please enter a valid phone number'
                this.sending = false
                return
            }

            try {
                await this.requestOTP(this.fullPhone)
                this.step = 2
                this.$emit('otp-sent')
            } catch (e) {
                this.phoneError = e.response?.data?.message || 'Failed to send code'
            } finally {
                this.sending = false
            }
        },
        async verifyOTP() {
            this.verifying = true
            this.otpError = ''

            try {
                const response = await this.verify(this.fullPhone, this.otpCode)
                this.$emit('verified', response)
            } catch (e) {
                this.otpError = e.response?.data?.message || 'Invalid code'
            } finally {
                this.verifying = false
            }
        },
        requestOTP: vi.fn(),
        verify: vi.fn(),
    },
}

describe('Phone Login Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    describe('Step 1: Phone Number', () => {
        it('renders phone input form', () => {
            const wrapper = mountComponent(PhoneLoginComponent)
            
            expect(wrapper.find('[data-testid="phone-step"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="phone-input"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="country-code"]').exists()).toBe(true)
        })

        it('validates phone number', async () => {
            const wrapper = mountComponent(PhoneLoginComponent)
            
            await wrapper.find('[data-testid="phone-input"]').setValue('123')
            await wrapper.find('[data-testid="phone-form"]').trigger('submit')
            
            expect(wrapper.find('[data-testid="phone-error"]').text()).toBe('Please enter a valid phone number')
        })

        it('sends OTP for valid phone', async () => {
            const wrapper = mountComponent(PhoneLoginComponent)
            wrapper.vm.requestOTP = vi.fn().mockResolvedValue({})

            await wrapper.find('[data-testid="phone-input"]').setValue('5551234567')
            await wrapper.find('[data-testid="phone-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.requestOTP).toHaveBeenCalledWith('+1 5551234567')
        })

        it('moves to step 2 on success', async () => {
            const wrapper = mountComponent(PhoneLoginComponent)
            wrapper.vm.requestOTP = vi.fn().mockResolvedValue({})

            await wrapper.find('[data-testid="phone-input"]').setValue('5551234567')
            await wrapper.find('[data-testid="phone-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.step).toBe(2)
            expect(wrapper.find('[data-testid="otp-step"]').exists()).toBe(true)
        })
    })

    describe('Step 2: OTP Verification', () => {
        beforeEach(async () => {
            // Set up component in step 2
        })

        it('displays masked phone number', async () => {
            const wrapper = mountComponent(PhoneLoginComponent)
            wrapper.vm.step = 2
            wrapper.vm.countryCode = '+1'
            wrapper.vm.phoneNumber = '5551234567'
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="phone-display"]').text()).toContain('+1 5551234567')
        })

        it('verifies complete OTP code', async () => {
            const wrapper = mountComponent(PhoneLoginComponent)
            wrapper.vm.step = 2
            wrapper.vm.phoneNumber = '5551234567'
            wrapper.vm.verify = vi.fn().mockResolvedValue({ token: 'jwt' })
            await wrapper.vm.$nextTick()

            for (let i = 0; i < 6; i++) {
                wrapper.vm.otpDigits[i] = String(i + 1)
            }
            await wrapper.vm.$nextTick()
            
            await wrapper.find('[data-testid="otp-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.verify).toHaveBeenCalledWith('+1 5551234567', '123456')
            expect(wrapper.emitted('verified')).toBeTruthy()
        })

        it('allows changing phone number', async () => {
            const wrapper = mountComponent(PhoneLoginComponent)
            wrapper.vm.step = 2
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="change-phone-btn"]').trigger('click')
            
            expect(wrapper.vm.step).toBe(1)
        })
    })
})
