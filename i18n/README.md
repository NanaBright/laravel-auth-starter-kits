# Internationalization (i18n)

Multi-language support module for Laravel Auth Starter Kits.

## Supported Languages

| Code | Language | Direction |
|------|----------|-----------|
| en | English | LTR |
| es | Spanish | LTR |
| fr | French | LTR |
| de | German | LTR |
| pt | Portuguese | LTR |
| it | Italian | LTR |
| zh | Chinese | LTR |
| ja | Japanese | LTR |
| ko | Korean | LTR |
| ar | Arabic | RTL |

## Backend Setup (Laravel)

Add to `config/app.php`:

```php
'locale' => env('APP_LOCALE', 'en'),
'fallback_locale' => 'en',
'available_locales' => ['en', 'es', 'fr', 'de', 'pt', 'it', 'zh', 'ja', 'ko', 'ar'],
```

Copy language files from `lang/` to your project's `lang/` directory.

Add the locale middleware from `middleware/SetLocale.php`.

## Frontend Setup (Vue.js)

Install vue-i18n:

```bash
npm install vue-i18n
```

Copy the frontend translations from `frontend/` to your project.

See `frontend/README.md` for Vue.js integration details.

## Usage

Backend:
```php
__('auth.login')
```

Frontend:
```vue
{{ $t('auth.login') }}
```

## License

MIT

### Usage in Controllers

```php
// Get translation
__('auth.login');

// With parameters
__('auth.welcome', ['name' => $user->name]);

// Change locale
app()->setLocale('es');
```

## Frontend Setup (Vue.js)

### Installation

```bash
npm install vue-i18n@9
```

### Configuration

```javascript
// resources/js/i18n.js
import { createI18n } from 'vue-i18n';
import messages from './locales';

const i18n = createI18n({
    locale: localStorage.getItem('locale') || 'en',
    fallbackLocale: 'en',
    messages,
});

export default i18n;
```

### Usage in Components

```vue
<template>
    <h1>{{ $t('auth.login') }}</h1>
    <p>{{ $t('auth.welcome', { name: user.name }) }}</p>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
const { t, locale } = useI18n();

// Change language
const changeLanguage = (lang) => {
    locale.value = lang;
    localStorage.setItem('locale', lang);
};
</script>
```

## Adding New Languages

1. Create backend translation file:
```
lang/[code]/auth.php
lang/[code]/validation.php
```

2. Create frontend translation file:
```
resources/js/locales/[code].json
```

3. Add to available locales in config.

## RTL Support

For Arabic and other RTL languages, add this to your CSS:

```css
[dir="rtl"] {
    text-align: right;
}

[dir="rtl"] .ml-4 {
    margin-left: 0;
    margin-right: 1rem;
}
```

The `SetLocale` middleware automatically sets the `dir` attribute based on the selected language.
