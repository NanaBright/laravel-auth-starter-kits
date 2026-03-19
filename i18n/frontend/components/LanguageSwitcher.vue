<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { SUPPORTED_LOCALES, setLocale } from '../i18n';

const { locale } = useI18n();

const isOpen = ref(false);

const currentLocale = computed(() => SUPPORTED_LOCALES[locale.value]);

const locales = computed(() => {
    return Object.entries(SUPPORTED_LOCALES).map(([code, config]) => ({
        code,
        ...config,
        active: code === locale.value,
    }));
});

const changeLocale = (code) => {
    setLocale(code);
    isOpen.value = false;
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    isOpen.value = false;
};
</script>

<template>
    <div class="relative" v-click-outside="closeDropdown">
        <!-- Trigger button -->
        <button
            @click="toggleDropdown"
            class="flex items-center space-x-2 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
        >
            <span class="text-lg">{{ currentLocale.flag }}</span>
            <span class="hidden sm:inline text-sm">{{ currentLocale.name }}</span>
            <svg
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown menu -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
            >
                <button
                    v-for="loc in locales"
                    :key="loc.code"
                    @click="changeLocale(loc.code)"
                    class="w-full flex items-center px-4 py-2 text-sm hover:bg-gray-100 transition-colors"
                    :class="{ 'bg-indigo-50 text-indigo-600': loc.active }"
                >
                    <span class="text-lg mr-3">{{ loc.flag }}</span>
                    <span>{{ loc.name }}</span>
                    <svg
                        v-if="loc.active"
                        class="w-4 h-4 ml-auto text-indigo-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </transition>
    </div>
</template>
