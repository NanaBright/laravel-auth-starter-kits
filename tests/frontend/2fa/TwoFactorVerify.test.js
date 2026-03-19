import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createTestMount } from '../utils/mount.js'

// Mock 2FA Verification Component (for login)
const TwoFactorVerifyComponent = {
    template: `
        <div class="two-factor-verify" data-testid="2fa-verify">
            <h2>Two-Factor Authentication</h2>
            
            <!-- TOTP Code Input -->
            <div v-if="!useRecovery" data-testid="totp-section">
                <p>Enter the 6-digit code from your authenticator app</p>
                <form @submit.prevent="verifyTOTP" data-testid="totp-form">
                    <div class="code-inputs">
                        <input
                            v-for="(digit, index) in 6"
                            :key="index"
                            v-model="totpCode[index]"
                            type="text"
                            maxlength="1"
                            :data-testid="'totp-digit-' + index"
                            @input="handleTOTPInput(index, $event)"
                            @keydown="handleKeydown(index, $event)"
                        />
                    </div>
                    <div v-if="error && !useRecovery" class="error" data-testid="totp-error">{{ error }}</div>
                    <button type="submit" :disabled="loading || !isTOTPComplete" data-testid="verify-totp-btn">
                        {{ loading ? 'Verifying...' : 'Verify' }}
                    </button>
                </form>
                <button @click="useRecovery = true" data-testid="use-recovery-btn">
                    Use a recovery code instead
                </button>
            </div>

            <!-- Recovery Code Input -->
            <div v-else data-testid="recovery-section">
                <p>Enter one of your recovery codes</p>
                <form @submit.prevent="verifyRecovery" data-testid="recovery-form">
                    <input
                        v-model="recoveryCode"
                        type="text"
                        placeholder="XXXX-XXXX-XXXX"
                        data-testid="recovery-input"
                    />
                    <div v-if="error && useRecovery" class="error" data-testid="recovery-error">{{ error }}</div>
                    <button type="submit" :disabled="loading || !recoveryCode" data-testid="verify-recovery-btn">
                        {{ loading ? 'Verifying...' : 'Verify' }}
                    </button>
                </form>
                <button @click="useRecovery = false" data-testid="use-totp-btn">
                    Use authenticator code instead
                </button>
            </div>
        </div>
    `,
    data() {
        return {
            totpCode: Array(6).fill(''),
            recoveryCode: '',
            useRecovery: false,
            loading: false,
            error: '',
        }
    },
    computed: {
        isTOTPComplete() {
            return this.totpCode.every(d => d !== '')
        },
        fullTOTPCode() {
            return this.totpCode.join('')
        },
    },
    methods: {
        handleTOTPInput(index, event) {
            const value = event.target.value.replace(/\D/g, '')
            this.totpCode[index] = value
        },
        handleKeydown(index, event) {
            // Auto-focus handling would go here
        },
        async verifyTOTP() {
            this.loading = true
            this.error = ''
            try {
                const response = await this.verify2FA({ code: this.fullTOTPCode })
                this.$emit('verified', response)
            } catch (e) {
                this.error = e.response?.data?.message || 'Invalid code'
            } finally {
                this.loading = false
            }
        },
        async verifyRecovery() {
            this.loading = true
            this.error = ''
            try {
                const response = await this.verifyRecoveryCode({ code: this.recoveryCode })
                this.$emit('verified', response)
            } catch (e) {
                this.error = e.response?.data?.message || 'Invalid recovery code'
            } finally {
                this.loading = false
            }
        },
        verify2FA: vi.fn(),
        verifyRecoveryCode: vi.fn(),
    },
}

describe('Two-Factor Verify Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    describe('TOTP Verification', () => {
        it('renders TOTP input section by default', () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            
            expect(wrapper.find('[data-testid="totp-section"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="recovery-section"]').exists()).toBe(false)
        })

        it('renders 6 digit inputs', () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            
            for (let i = 0; i < 6; i++) {
                expect(wrapper.find(`[data-testid="totp-digit-${i}"]`).exists()).toBe(true)
            }
        })

        it('disables verify button when code incomplete', () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            
            expect(wrapper.find('[data-testid="verify-totp-btn"]').attributes('disabled')).toBeDefined()
        })

        it('enables verify button when code is complete', async () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            
            for (let i = 0; i < 6; i++) {
                wrapper.vm.totpCode[i] = String(i + 1)
            }
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="verify-totp-btn"]').attributes('disabled')).toBeUndefined()
        })

        it('verifies TOTP code successfully', async () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            wrapper.vm.verify2FA = vi.fn().mockResolvedValue({ token: 'jwt-token' })

            for (let i = 0; i < 6; i++) {
                wrapper.vm.totpCode[i] = String(i + 1)
            }
            await wrapper.vm.$nextTick()
            
            await wrapper.find('[data-testid="totp-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.verify2FA).toHaveBeenCalledWith({ code: '123456' })
            expect(wrapper.emitted('verified')).toBeTruthy()
        })

        it('displays error on invalid TOTP code', async () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            wrapper.vm.verify2FA = vi.fn().mockRejectedValue({
                response: { data: { message: 'Invalid authentication code.' } }
            })

            for (let i = 0; i < 6; i++) {
                wrapper.vm.totpCode[i] = '0'
            }
            await wrapper.vm.$nextTick()
            
            await wrapper.find('[data-testid="totp-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="totp-error"]').text()).toBe('Invalid authentication code.')
        })
    })

    describe('Recovery Code Verification', () => {
        it('switches to recovery code input', async () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            
            await wrapper.find('[data-testid="use-recovery-btn"]').trigger('click')
            
            expect(wrapper.find('[data-testid="recovery-section"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="totp-section"]').exists()).toBe(false)
        })

        it('switches back to TOTP input', async () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            wrapper.vm.useRecovery = true
            await wrapper.vm.$nextTick()
            
            await wrapper.find('[data-testid="use-totp-btn"]').trigger('click')
            
            expect(wrapper.find('[data-testid="totp-section"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="recovery-section"]').exists()).toBe(false)
        })

        it('verifies recovery code successfully', async () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            wrapper.vm.useRecovery = true
            wrapper.vm.verifyRecoveryCode = vi.fn().mockResolvedValue({ token: 'jwt-token' })
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="recovery-input"]').setValue('AAAA-BBBB-CCCC')
            await wrapper.find('[data-testid="recovery-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.verifyRecoveryCode).toHaveBeenCalledWith({ code: 'AAAA-BBBB-CCCC' })
            expect(wrapper.emitted('verified')).toBeTruthy()
        })

        it('displays error on invalid recovery code', async () => {
            const wrapper = mountComponent(TwoFactorVerifyComponent)
            wrapper.vm.useRecovery = true
            wrapper.vm.verifyRecoveryCode = vi.fn().mockRejectedValue({
                response: { data: { message: 'Invalid recovery code.' } }
            })
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="recovery-input"]').setValue('INVALID-CODE')
            await wrapper.find('[data-testid="recovery-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="recovery-error"]').text()).toBe('Invalid recovery code.')
        })
    })
})
