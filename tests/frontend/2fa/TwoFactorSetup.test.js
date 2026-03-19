import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createTestMount } from '../utils/mount.js'
import { mock2FAEnabled } from '../utils/api-mocks.js'

// Mock 2FA Setup Component
const TwoFactorSetupComponent = {
    template: `
        <div class="two-factor-setup" data-testid="2fa-setup">
            <h2>Two-Factor Authentication</h2>
            
            <!-- Enable Section -->
            <div v-if="!enabled && !setupMode" data-testid="enable-section">
                <p>Add an extra layer of security to your account.</p>
                <button @click="startSetup" data-testid="enable-btn">Enable 2FA</button>
            </div>

            <!-- Setup Mode -->
            <div v-if="setupMode" data-testid="setup-section">
                <div v-if="qrCode" data-testid="qr-container">
                    <img :src="qrCode" alt="QR Code" data-testid="qr-code" />
                    <p>Scan this QR code with your authenticator app</p>
                    <p class="secret" data-testid="secret">Or enter manually: {{ secret }}</p>
                </div>

                <form @submit.prevent="confirmSetup" data-testid="confirm-form">
                    <label>Enter the 6-digit code from your app:</label>
                    <input 
                        v-model="confirmCode" 
                        type="text" 
                        maxlength="6"
                        data-testid="confirm-input"
                    />
                    <div v-if="error" class="error" data-testid="error">{{ error }}</div>
                    <button type="submit" :disabled="loading" data-testid="confirm-btn">
                        {{ loading ? 'Verifying...' : 'Confirm' }}
                    </button>
                    <button type="button" @click="cancelSetup" data-testid="cancel-btn">Cancel</button>
                </form>
            </div>

            <!-- Recovery Codes -->
            <div v-if="showRecoveryCodes" data-testid="recovery-codes-section">
                <h3>Save Your Recovery Codes</h3>
                <p>Store these codes safely. You can use them to access your account if you lose your authenticator.</p>
                <ul class="recovery-codes" data-testid="recovery-codes-list">
                    <li v-for="code in recoveryCodes" :key="code">{{ code }}</li>
                </ul>
                <button @click="downloadCodes" data-testid="download-btn">Download Codes</button>
                <button @click="finishSetup" data-testid="finish-btn">I've saved these codes</button>
            </div>

            <!-- Enabled State -->
            <div v-if="enabled && !setupMode && !showRecoveryCodes" data-testid="enabled-section">
                <p class="status">Two-factor authentication is enabled</p>
                <button @click="regenerateCodes" :disabled="regenerating" data-testid="regenerate-btn">
                    {{ regenerating ? 'Regenerating...' : 'Regenerate Recovery Codes' }}
                </button>
                <button @click="showDisableConfirm = true" data-testid="disable-btn">Disable 2FA</button>
            </div>

            <!-- Disable Confirmation -->
            <div v-if="showDisableConfirm" class="modal" data-testid="disable-modal">
                <p>Are you sure you want to disable two-factor authentication?</p>
                <input 
                    v-model="disablePassword" 
                    type="password" 
                    placeholder="Enter your password"
                    data-testid="disable-password"
                />
                <button @click="disable2FA" :disabled="disabling" data-testid="confirm-disable-btn">
                    {{ disabling ? 'Disabling...' : 'Yes, disable' }}
                </button>
                <button @click="showDisableConfirm = false" data-testid="cancel-disable-btn">Cancel</button>
            </div>
        </div>
    `,
    data() {
        return {
            enabled: false,
            setupMode: false,
            qrCode: '',
            secret: '',
            confirmCode: '',
            recoveryCodes: [],
            showRecoveryCodes: false,
            loading: false,
            error: '',
            showDisableConfirm: false,
            disablePassword: '',
            disabling: false,
            regenerating: false,
        }
    },
    methods: {
        async startSetup() {
            this.loading = true
            try {
                const response = await this.enable2FA()
                this.qrCode = response.qr_code
                this.secret = response.secret
                this.setupMode = true
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to start setup'
            } finally {
                this.loading = false
            }
        },
        async confirmSetup() {
            this.loading = true
            this.error = ''
            try {
                const response = await this.confirm2FA(this.confirmCode)
                this.recoveryCodes = response.recovery_codes
                this.showRecoveryCodes = true
                this.setupMode = false
            } catch (e) {
                this.error = e.response?.data?.message || 'Invalid code'
            } finally {
                this.loading = false
            }
        },
        cancelSetup() {
            this.setupMode = false
            this.qrCode = ''
            this.secret = ''
            this.confirmCode = ''
        },
        finishSetup() {
            this.showRecoveryCodes = false
            this.enabled = true
            this.$emit('enabled')
        },
        downloadCodes() {
            const content = this.recoveryCodes.join('\n')
            const blob = new Blob([content], { type: 'text/plain' })
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = 'recovery-codes.txt'
            a.click()
            URL.revokeObjectURL(url)
        },
        async regenerateCodes() {
            this.regenerating = true
            try {
                const response = await this.regenerateRecoveryCodes()
                this.recoveryCodes = response.recovery_codes
                this.showRecoveryCodes = true
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to regenerate codes'
            } finally {
                this.regenerating = false
            }
        },
        async disable2FA() {
            this.disabling = true
            try {
                await this.disable(this.disablePassword)
                this.enabled = false
                this.showDisableConfirm = false
                this.$emit('disabled')
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to disable 2FA'
            } finally {
                this.disabling = false
            }
        },
        enable2FA: vi.fn(),
        confirm2FA: vi.fn(),
        regenerateRecoveryCodes: vi.fn(),
        disable: vi.fn(),
    },
}

describe('Two-Factor Setup Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    describe('Initial State', () => {
        it('renders enable section when 2FA is disabled', () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            
            expect(wrapper.find('[data-testid="enable-section"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="enable-btn"]').exists()).toBe(true)
        })

        it('renders enabled section when 2FA is enabled', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.enabled = true
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="enabled-section"]').exists()).toBe(true)
        })
    })

    describe('Setup Flow', () => {
        it('starts setup and displays QR code', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.enable2FA = vi.fn().mockResolvedValue(mock2FAEnabled)

            await wrapper.find('[data-testid="enable-btn"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="setup-section"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="qr-code"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="secret"]').text()).toContain(mock2FAEnabled.secret)
        })

        it('shows confirmation code input', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.enable2FA = vi.fn().mockResolvedValue(mock2FAEnabled)

            await wrapper.find('[data-testid="enable-btn"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="confirm-input"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="confirm-btn"]').exists()).toBe(true)
        })

        it('confirms setup with valid code', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.setupMode = true
            wrapper.vm.confirm2FA = vi.fn().mockResolvedValue({
                recovery_codes: mock2FAEnabled.recovery_codes
            })

            await wrapper.find('[data-testid="confirm-input"]').setValue('123456')
            await wrapper.find('[data-testid="confirm-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="recovery-codes-section"]').exists()).toBe(true)
        })

        it('displays error on invalid confirmation code', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.setupMode = true
            wrapper.vm.confirm2FA = vi.fn().mockRejectedValue({
                response: { data: { message: 'Invalid authentication code.' } }
            })

            await wrapper.find('[data-testid="confirm-input"]').setValue('000000')
            await wrapper.find('[data-testid="confirm-form"]').trigger('submit')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="error"]').text()).toBe('Invalid authentication code.')
        })

        it('cancels setup on cancel button click', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.setupMode = true
            wrapper.vm.qrCode = 'data:image/svg+xml;base64,...'
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="cancel-btn"]').trigger('click')
            
            expect(wrapper.vm.setupMode).toBe(false)
            expect(wrapper.vm.qrCode).toBe('')
        })
    })

    describe('Recovery Codes', () => {
        it('displays recovery codes after setup', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.showRecoveryCodes = true
            wrapper.vm.recoveryCodes = mock2FAEnabled.recovery_codes
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="recovery-codes-list"]').exists()).toBe(true)
            expect(wrapper.findAll('[data-testid="recovery-codes-list"] li').length).toBe(8)
        })

        it('finishes setup when user confirms codes saved', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.showRecoveryCodes = true
            wrapper.vm.recoveryCodes = mock2FAEnabled.recovery_codes
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="finish-btn"]').trigger('click')
            
            expect(wrapper.vm.enabled).toBe(true)
            expect(wrapper.vm.showRecoveryCodes).toBe(false)
            expect(wrapper.emitted('enabled')).toBeTruthy()
        })

        it('regenerates recovery codes', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.enabled = true
            wrapper.vm.regenerateRecoveryCodes = vi.fn().mockResolvedValue({
                recovery_codes: ['NEW1-CODE', 'NEW2-CODE']
            })
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="regenerate-btn"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.showRecoveryCodes).toBe(true)
            expect(wrapper.vm.recoveryCodes).toEqual(['NEW1-CODE', 'NEW2-CODE'])
        })
    })

    describe('Disable 2FA', () => {
        it('shows confirmation modal on disable click', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.enabled = true
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="disable-btn"]').trigger('click')
            
            expect(wrapper.find('[data-testid="disable-modal"]').exists()).toBe(true)
        })

        it('requires password to disable', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.enabled = true
            wrapper.vm.showDisableConfirm = true
            wrapper.vm.disable = vi.fn().mockResolvedValue({})
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="disable-password"]').setValue('password123')
            await wrapper.find('[data-testid="confirm-disable-btn"]').trigger('click')
            
            expect(wrapper.vm.disable).toHaveBeenCalledWith('password123')
        })

        it('disables 2FA successfully', async () => {
            const wrapper = mountComponent(TwoFactorSetupComponent)
            wrapper.vm.enabled = true
            wrapper.vm.showDisableConfirm = true
            wrapper.vm.disable = vi.fn().mockResolvedValue({})
            await wrapper.vm.$nextTick()

            await wrapper.find('[data-testid="disable-password"]').setValue('password123')
            await wrapper.find('[data-testid="confirm-disable-btn"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.enabled).toBe(false)
            expect(wrapper.emitted('disabled')).toBeTruthy()
        })
    })
})
