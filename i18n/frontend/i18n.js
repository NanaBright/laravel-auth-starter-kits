import { createI18n } from 'vue-i18n';

// Import locale files
import en from './locales/en.json';
import es from './locales/es.json';
import fr from './locales/fr.json';
import de from './locales/de.json';
import zh from './locales/zh.json';
import ar from './locales/ar.json';

// Supported locales
export const SUPPORTED_LOCALES = {
    en: { name: 'English', dir: 'ltr', flag: '🇺🇸' },
    es: { name: 'Español', dir: 'ltr', flag: '🇪🇸' },
    fr: { name: 'Français', dir: 'ltr', flag: '🇫🇷' },
    de: { name: 'Deutsch', dir: 'ltr', flag: '🇩🇪' },
    zh: { name: '中文', dir: 'ltr', flag: '🇨🇳' },
    ar: { name: 'العربية', dir: 'rtl', flag: '🇸🇦' },
};

// Get initial locale
function getInitialLocale() {
    // Check localStorage
    const stored = localStorage.getItem('locale');
    if (stored && stored in SUPPORTED_LOCALES) {
        return stored;
    }

    // Check browser language
    const browserLang = navigator.language.split('-')[0];
    if (browserLang in SUPPORTED_LOCALES) {
        return browserLang;
    }

    return 'en';
}

// Create i18n instance
const i18n = createI18n({
    legacy: false,
    locale: getInitialLocale(),
    fallbackLocale: 'en',
    messages: {
        en,
        es,
        fr,
        de,
        zh,
        ar,
    },
    // Enable HTML in messages
    warnHtmlMessage: false,
    // Number/date formatting
    numberFormats: {
        en: {
            currency: { style: 'currency', currency: 'USD' },
        },
        es: {
            currency: { style: 'currency', currency: 'EUR' },
        },
        de: {
            currency: { style: 'currency', currency: 'EUR' },
        },
        zh: {
            currency: { style: 'currency', currency: 'CNY' },
        },
    },
    datetimeFormats: {
        en: {
            short: { year: 'numeric', month: 'short', day: 'numeric' },
            long: { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric' },
        },
        es: {
            short: { year: 'numeric', month: 'short', day: 'numeric' },
            long: { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric' },
        },
    },
});

// Change locale and update document direction
export function setLocale(locale) {
    if (!(locale in SUPPORTED_LOCALES)) {
        console.warn(`Unsupported locale: ${locale}`);
        return;
    }

    i18n.global.locale.value = locale;
    localStorage.setItem('locale', locale);
    
    // Update document direction for RTL languages
    document.documentElement.dir = SUPPORTED_LOCALES[locale].dir;
    document.documentElement.lang = locale;
}

// Initialize document direction
document.documentElement.dir = SUPPORTED_LOCALES[getInitialLocale()].dir;
document.documentElement.lang = getInitialLocale();

export default i18n;
