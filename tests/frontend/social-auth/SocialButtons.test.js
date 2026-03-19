import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createTestMount } from '../utils/mount.js'

// Mock Social Auth Buttons Component
const SocialAuthButtonsComponent = {
    template: `
        <div class="social-auth" data-testid="social-auth">
            <p class="divider" data-testid="divider">Or continue with</p>
            <div class="social-buttons">
                <button
                    v-for="provider in providers"
                    :key="provider.id"
                    @click="handleSocialAuth(provider.id)"
                    :disabled="loading === provider.id"
                    :data-testid="'social-btn-' + provider.id"
                    class="social-btn"
                >
                    <component :is="provider.icon" v-if="provider.icon" />
                    <span>{{ loading === provider.id ? 'Connecting...' : provider.name }}</span>
                </button>
            </div>
            <div v-if="error" class="error" data-testid="error">{{ error }}</div>
        </div>
    `,
    props: {
        enabledProviders: {
            type: Array,
            default: () => ['google', 'github', 'facebook', 'twitter'],
        },
    },
    data() {
        return {
            loading: null,
            error: '',
        }
    },
    computed: {
        providers() {
            const allProviders = [
                { id: 'google', name: 'Google', icon: null },
                { id: 'github', name: 'GitHub', icon: null },
                { id: 'facebook', name: 'Facebook', icon: null },
                { id: 'twitter', name: 'Twitter', icon: null },
            ]
            return allProviders.filter(p => this.enabledProviders.includes(p.id))
        },
    },
    methods: {
        async handleSocialAuth(provider) {
            this.loading = provider
            this.error = ''

            try {
                const authUrl = await this.getAuthUrl(provider)
                window.location.href = authUrl
            } catch (e) {
                this.error = e.response?.data?.message || `Failed to connect with ${provider}`
                this.$emit('auth-error', { provider, error: e })
            } finally {
                this.loading = null
            }
        },
        getAuthUrl: vi.fn(),
    },
}

describe('Social Auth Buttons Component', () => {
    let mountComponent
    let originalLocation

    beforeEach(() => {
        mountComponent = createTestMount()
        // Mock window.location
        originalLocation = window.location
        delete window.location
        window.location = { href: '' }
    })

    afterEach(() => {
        window.location = originalLocation
    })

    describe('Rendering', () => {
        it('renders all enabled social providers', () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            
            expect(wrapper.find('[data-testid="social-btn-google"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="social-btn-github"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="social-btn-facebook"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="social-btn-twitter"]').exists()).toBe(true)
        })

        it('renders only specified providers', () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent, {
                props: { enabledProviders: ['google', 'github'] }
            })
            
            expect(wrapper.find('[data-testid="social-btn-google"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="social-btn-github"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="social-btn-facebook"]').exists()).toBe(false)
            expect(wrapper.find('[data-testid="social-btn-twitter"]').exists()).toBe(false)
        })

        it('renders divider text', () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            expect(wrapper.find('[data-testid="divider"]').text()).toBe('Or continue with')
        })
    })

    describe('Authentication Flow', () => {
        it('calls getAuthUrl when provider button clicked', async () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            const getAuthUrlFn = vi.fn().mockResolvedValue('https://accounts.google.com/oauth')
            wrapper.vm.getAuthUrl = getAuthUrlFn

            await wrapper.find('[data-testid="social-btn-google"]').trigger('click')
            
            expect(getAuthUrlFn).toHaveBeenCalledWith('google')
        })

        it('redirects to OAuth URL on success', async () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            const authUrl = 'https://accounts.google.com/oauth?client_id=xxx'
            wrapper.vm.getAuthUrl = vi.fn().mockResolvedValue(authUrl)

            await wrapper.find('[data-testid="social-btn-google"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(window.location.href).toBe(authUrl)
        })

        it('shows loading state while authenticating', async () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            wrapper.vm.getAuthUrl = vi.fn().mockImplementation(() => new Promise(() => {})) // Never resolves

            await wrapper.find('[data-testid="social-btn-google"]').trigger('click')
            
            expect(wrapper.find('[data-testid="social-btn-google"]').text()).toContain('Connecting...')
        })

        it('displays error on authentication failure', async () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            wrapper.vm.getAuthUrl = vi.fn().mockRejectedValue({
                response: { data: { message: 'OAuth service unavailable' } }
            })

            await wrapper.find('[data-testid="social-btn-google"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.find('[data-testid="error"]').text()).toBe('OAuth service unavailable')
        })

        it('emits auth-error event on failure', async () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            const error = new Error('Network error')
            wrapper.vm.getAuthUrl = vi.fn().mockRejectedValue(error)

            await wrapper.find('[data-testid="social-btn-github"]').trigger('click')
            await wrapper.vm.$nextTick()
            
            expect(wrapper.emitted('auth-error')).toBeTruthy()
            expect(wrapper.emitted('auth-error')[0][0].provider).toBe('github')
        })
    })

    describe('Multiple Providers', () => {
        it('only shows loading state for clicked provider', async () => {
            const wrapper = mountComponent(SocialAuthButtonsComponent)
            wrapper.vm.getAuthUrl = vi.fn().mockImplementation(() => new Promise(() => {}))

            await wrapper.find('[data-testid="social-btn-google"]').trigger('click')
            
            expect(wrapper.find('[data-testid="social-btn-google"]').attributes('disabled')).toBeDefined()
            expect(wrapper.find('[data-testid="social-btn-github"]').attributes('disabled')).toBeUndefined()
        })
    })
})
