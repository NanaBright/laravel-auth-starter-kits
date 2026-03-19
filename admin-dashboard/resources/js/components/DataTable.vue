<script setup>
import { computed } from 'vue';

const props = defineProps({
    columns: Array,
    data: Array,
    loading: Boolean,
    selectable: Boolean,
    selected: Array
});

const emit = defineEmits(['select', 'selectAll', 'rowClick']);

const allSelected = computed(() => {
    return props.data?.length > 0 && props.selected?.length === props.data.length;
});

const toggleAll = (event) => {
    emit('selectAll', event.target.checked);
};

const toggleRow = (id) => {
    emit('select', id);
};

const onRowClick = (row) => {
    emit('rowClick', row);
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 data-table">
            <thead class="bg-gray-50">
                <tr>
                    <th v-if="selectable" class="w-12 px-6 py-3">
                        <input
                            type="checkbox"
                            :checked="allSelected"
                            @change="toggleAll"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                    </th>
                    <th v-for="col in columns" :key="col.key">
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading">
                    <td :colspan="columns.length + (selectable ? 1 : 0)" class="px-6 py-8 text-center text-gray-500">
                        <svg class="animate-spin h-8 w-8 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2">Loading...</p>
                    </td>
                </tr>
                <tr v-else-if="!data || data.length === 0">
                    <td :colspan="columns.length + (selectable ? 1 : 0)" class="px-6 py-8 text-center text-gray-500">
                        No data available
                    </td>
                </tr>
                <tr
                    v-else
                    v-for="row in data"
                    :key="row.id"
                    @click="onRowClick(row)"
                    class="hover:bg-gray-50 cursor-pointer"
                >
                    <td v-if="selectable" class="w-12 px-6 py-4" @click.stop>
                        <input
                            type="checkbox"
                            :checked="selected?.includes(row.id)"
                            @change="toggleRow(row.id)"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                    </td>
                    <td v-for="col in columns" :key="col.key">
                        <slot :name="col.key" :row="row" :value="row[col.key]">
                            {{ row[col.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
