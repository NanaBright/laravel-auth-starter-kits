import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createTestMount } from '../utils/mount.js'

// Mock Account Linking Component
const AccountLinkingComponent = {
    template: `
        <div class="account-linking" data-testid="account-linking">
            <h2>Connected Accounts</h2>
            
            <div 
                v-for="provider in providers" 
                :key="provider.id"
                class="provider-item"
                :data-testid="'provider-' + provider.id"
            >
                <div class="provider-info">
                    <span class="provider-name">{{ provider.name }}</span>
                    <span 
                        v-if="isLinked(provider.id)" 
                        class="linked-email"
                        :data-testid="'linked-email-' + provider.id"
                    >
                        {{ getLinkedEmail(provider.id) }}
                    </span>
                </div>
                
                <button
                    v-if="isLinked(provider.id)"
                    @click="unlinkAccount(provider.id)"
                    :disabled="loading === provider.id"
                    :data-testid="'unlink-' + provider.id"
                    class="unlink-btn"
                >
                    {{ loading === provider.id ? 'Unlinking...' : 'Disconnect' }}
                </button>
                
                <button
                    v-else
                    @click="linkAccount(provider.id)"
                    :disabled="loading === provider.id"
                    :data-testid="'link-' + provider.id"
                    class="link-btn"
                >
                    {{ loading === provider.id ? 'Connecting...' : 'Connect' }}
                </button>
            </div>

            <div v-if="error" class="error" data-testid="error">{{ error }}</div>
            <div v-if="success" class="success" data-testid="success">{{ success }}</div>
        </div>
    `,
    props: {
        linkedAccounts: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            loading: null,
            error: '',
            success: '',
            providers: [
                { id: 'google', name: 'Google' },
                { id: 'github', name: 'GitHub' },
                { id: 'facebook', name: 'Facebook' },
                { id: 'twitter', name: 'Twitter' },
            ],
        }
    },
    methods: {
        isLinked(providerId) {
            return this.linkedAccounts.some(a => a.provider === providerId)
        },
        getLinkedEmail(providerId) {
            const account = this.linkedAccounts.find(a => a.provider === providerId)
            return account?.email || ''
        },
        async linkAccount(providerId) {
            this.loading = providerId
            this.error = ''
            this.success = ''

            try {
                const authUrl = await this.getOAuthUrl(providerId)
                window.location.href = authUrl
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to connect account'
            } finally {
                this.loading = null
            }
        },
        async unlinkAccount(providerId) {
            this.loading = providerId
            this.error = ''
            this.success = ''

            try {
                await this.unlink(providerId)
                this.success = `${providerId} account disconnected`
                this.$emit('unlinked', providerId)
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to disconnect account'
            } finally {
                this.loading = null
            }
        },
        getOAuthUrl: vi.fn(),
        unlink: vi.fn(),
    },
}

describe('Account Linking Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    describe('Rendering', () => {
        it('renders all providers', () => {
            const wrapper = mountComponent(AccountLinkingComponent)
            
            expect(wrapper.find('[data-testid="provider-google"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="provider-github"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="provider-facebook"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="provider-twitter"]').exists()).toBe(true)
        })

        it('shows connect button for unlinked providers', () => {
            const wrapper = mountComponent(AccountLinkingComponent, {
                props: { linkedAccounts: [] }
            })
            
            expect(wrapper.find('[data-testid="link-google"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="unlink-google"]').exists()).toBe(false)
        })

        it('shows disconnect button for linked providers', () => {
            const wrapper = mountComponent(AccountLinkingComponent, {
                props: {
                    linkedAccounts: [
                        { provider: 'google', email: 'user@gmail.com' }
                    ]
                }
            })
            
            expect(wrapper.find('[data-testid="unlink-google"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="link-google"]').exists()).toBe(false)
        })

        it('displays linked email', () => {
            const wrapper = mountComponent(AccountLinkingComponent, {
                props: {
                    linkedAccounts: [
                        { provider: 'google', email: 'user@gmail.com' }
                    ]
                }
            })
            
            expect(wrapper.find('[data-testid="linked-email-google"]').text()).toBe('user@gmail.com')
        })
    })

    describe('Linking', () => {
        let originalLocation

        beforeEach(() => {
            originalLocation = window.location
            delete window.location
            window.location = { href: '' }
        })

        afterEach(() => {
            window.location = originalLocation
        })

        it('redirects to OAuth URL when connecting', async () => {
            const wrapper = mountComponent(AccountLinkingComponent)
            wrapper.vm.getOAuthUrl = vi.fn().mockResolvedValue('https://accounts.google.com/oauth')

            await wrapper.find('[data-testid="link-google"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(window.location.href).toBe('https://accounts.google.com/oauth')
        })

        it('shows error on linking failure', async () => {
            const wrapper = mountComponent(AccountLinkingComponent)
            wrapper.vm.getOAuthUrl = vi.fn().mockRejectedValue({
                response: { data: { message: 'OAuth error' } }
            })

            await wrapper.find('[data-testid="link-github"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="error"]').text()).toBe('OAuth error')
        })
    })

    describe('Unlinking', () => {
        it('unlinks account successfully', async () => {
            const wrapper = mountComponent(AccountLinkingComponent, {
                props: {
                    linkedAccounts: [
                        { provider: 'google', email: 'user@gmail.com' }
                    ]
                }
            })
            wrapper.vm.unlink = vi.fn().mockResolvedValue({})

            await wrapper.find('[data-testid="unlink-google"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.vm.unlink).toHaveBeenCalledWith('google')
            expect(wrapper.emitted('unlinked')).toBeTruthy()
            expect(wrapper.emitted('unlinked')[0]).toEqual(['google'])
        })

        it('shows success message after unlinking', async () => {
            const wrapper = mountComponent(AccountLinkingComponent, {
                props: {
                    linkedAccounts: [
                        { provider: 'github', email: 'user@github.com' }
                    ]
                }
            })
            wrapper.vm.unlink = vi.fn().mockResolvedValue({})

            await wrapper.find('[data-testid="unlink-github"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="success"]').text()).toContain('github account disconnected')
        })

        it('shows error on unlinking failure', async () => {
            const wrapper = mountComponent(AccountLinkingComponent, {
                props: {
                    linkedAccounts: [
                        { provider: 'google', email: 'user@gmail.com' }
                    ]
                }
            })
            wrapper.vm.unlink = vi.fn().mockRejectedValue({
                response: { data: { message: 'Cannot disconnect primary login method' } }
            })

            await wrapper.find('[data-testid="unlink-google"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="error"]').text()).toBe('Cannot disconnect primary login method')
        })
    })
})
