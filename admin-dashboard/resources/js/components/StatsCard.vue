<script setup>
const props = defineProps({
    title: String,
    value: [String, Number],
    change: Number,
    icon: String,
    color: {
        type: String,
        default: 'indigo'
    }
});

const colorClasses = {
    indigo: 'bg-indigo-100 text-indigo-600',
    green: 'bg-green-100 text-green-600',
    blue: 'bg-blue-100 text-blue-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    red: 'bg-red-100 text-red-600',
};
</script>

<template>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">{{ title }}</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">{{ value }}</p>
                <p v-if="change !== undefined" class="mt-1 text-sm" :class="change >= 0 ? 'text-green-600' : 'text-red-600'">
                    <span>{{ change >= 0 ? '+' : '' }}{{ change }}</span>
                    <span class="text-gray-500"> from last period</span>
                </p>
            </div>
            <div :class="['w-12 h-12 rounded-lg flex items-center justify-center', colorClasses[color]]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
                </svg>
            </div>
        </div>
    </div>
</template>
