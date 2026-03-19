import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { createTestMount } from '../utils/mount.js'

// Mock Language Switcher Component
const LanguageSwitcherComponent = {
    template: `
        <div class="language-switcher" data-testid="language-switcher">
            <button 
                @click="toggleDropdown" 
                class="trigger"
                data-testid="trigger-btn"
            >
                <span data-testid="current-flag">{{ currentLocale.flag }}</span>
                <span data-testid="current-name">{{ currentLocale.name }}</span>
            </button>
            
            <div v-if="isOpen" class="dropdown" data-testid="dropdown">
                <button
                    v-for="locale in locales"
                    :key="locale.code"
                    @click="selectLocale(locale.code)"
                    :class="{ active: locale.code === current }"
                    :data-testid="'locale-' + locale.code"
                >
                    <span>{{ locale.flag }}</span>
                    <span>{{ locale.name }}</span>
                </button>
            </div>
        </div>
    `,
    data() {
        return {
            current: 'en',
            isOpen: false,
            locales: [
                { code: 'en', name: 'English', flag: '🇺🇸' },
                { code: 'es', name: 'Español', flag: '🇪🇸' },
                { code: 'fr', name: 'Français', flag: '🇫🇷' },
                { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
                { code: 'zh', name: '中文', flag: '🇨🇳' },
                { code: 'ar', name: 'العربية', flag: '🇸🇦' },
            ],
        }
    },
    computed: {
        currentLocale() {
            return this.locales.find(l => l.code === this.current) || this.locales[0]
        },
    },
    methods: {
        toggleDropdown() {
            this.isOpen = !this.isOpen
        },
        selectLocale(code) {
            this.current = code
            this.isOpen = false
            localStorage.setItem('locale', code)
            document.documentElement.lang = code
            document.documentElement.dir = code === 'ar' ? 'rtl' : 'ltr'
            this.$emit('change', code)
        },
    },
}

describe('Language Switcher Component', () => {
    let mountComponent

    beforeEach(() => {
        mountComponent = createTestMount()
    })

    afterEach(() => {
        vi.clearAllMocks()
    })

    describe('Rendering', () => {
        it('renders trigger button with current locale', () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            expect(wrapper.find('[data-testid="trigger-btn"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="current-name"]').text()).toBe('English')
        })

        it('dropdown is hidden by default', () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            expect(wrapper.find('[data-testid="dropdown"]').exists()).toBe(false)
        })

        it('shows dropdown when trigger clicked', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            
            expect(wrapper.find('[data-testid="dropdown"]').exists()).toBe(true)
        })

        it('renders all available locales in dropdown', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            
            expect(wrapper.find('[data-testid="locale-en"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="locale-es"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="locale-fr"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="locale-de"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="locale-zh"]').exists()).toBe(true)
            expect(wrapper.find('[data-testid="locale-ar"]').exists()).toBe(true)
        })
    })

    describe('Locale Selection', () => {
        it('changes locale when option clicked', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            await wrapper.find('[data-testid="locale-es"]').trigger('click')
            
            expect(wrapper.vm.current).toBe('es')
            expect(wrapper.find('[data-testid="current-name"]').text()).toBe('Español')
        })

        it('closes dropdown after selection', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            await wrapper.find('[data-testid="locale-fr"]').trigger('click')
            
            expect(wrapper.find('[data-testid="dropdown"]').exists()).toBe(false)
        })

        it('emits change event with locale code', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            await wrapper.find('[data-testid="locale-de"]').trigger('click')
            
            expect(wrapper.emitted('change')).toBeTruthy()
            expect(wrapper.emitted('change')[0]).toEqual(['de'])
        })

        it('saves locale to localStorage', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            await wrapper.find('[data-testid="locale-zh"]').trigger('click')
            
            expect(localStorage.setItem).toHaveBeenCalledWith('locale', 'zh')
        })

        it('updates document lang attribute', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            await wrapper.find('[data-testid="locale-fr"]').trigger('click')
            
            expect(document.documentElement.lang).toBe('fr')
        })
    })

    describe('RTL Support', () => {
        it('sets RTL direction for Arabic', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            await wrapper.find('[data-testid="locale-ar"]').trigger('click')
            
            expect(document.documentElement.dir).toBe('rtl')
        })

        it('sets LTR direction for other languages', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            wrapper.vm.current = 'ar'
            document.documentElement.dir = 'rtl'
            
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            await wrapper.find('[data-testid="locale-en"]').trigger('click')
            
            expect(document.documentElement.dir).toBe('ltr')
        })
    })

    describe('Toggle Behavior', () => {
        it('toggles dropdown open and closed', async () => {
            const wrapper = mountComponent(LanguageSwitcherComponent)
            
            // Open
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            expect(wrapper.vm.isOpen).toBe(true)
            
            // Close
            await wrapper.find('[data-testid="trigger-btn"]').trigger('click')
            expect(wrapper.vm.isOpen).toBe(false)
        })
    })
})
