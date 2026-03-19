<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Pagination from '../components/Pagination.vue';

const logs = ref([]);
const loading = ref(true);
const actions = ref([]);

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const perPage = ref(25);

// Filters
const search = ref('');
const filterAction = ref('');

const fetchLogs = async () => {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
        };

        if (search.value) params.search = search.value;
        if (filterAction.value) params.action = filterAction.value;

        const response = await axios.get('/admin/logs', { params });
        logs.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        totalItems.value = response.data.total;
    } catch (error) {
        console.error('Failed to fetch logs:', error);
    } finally {
        loading.value = false;
    }
};

const fetchActions = async () => {
    try {
        const response = await axios.get('/admin/logs/actions');
        actions.value = response.data;
    } catch (error) {
        console.error('Failed to fetch actions:', error);
    }
};

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        fetchLogs();
    }, 300);
});

watch(filterAction, () => {
    currentPage.value = 1;
    fetchLogs();
});

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};

const getActionColor = (action) => {
    const colors = {
        'login': 'badge-success',
        'logout': 'badge-warning',
        'user_created': 'badge-info',
        'user_updated': 'badge-info',
        'user_deleted': 'badge-danger',
        'bulk_delete': 'badge-danger',
        'user_activated': 'badge-success',
        'user_deactivated': 'badge-warning',
    };
    return colors[action] || 'badge-info';
};

const changePage = (page) => {
    currentPage.value = page;
    fetchLogs();
};

onMounted(() => {
    fetchLogs();
    fetchActions();
});
</script>

<template>
    <div class="p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-600">Track admin actions and user events</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search description..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                    <select v-model="filterAction" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Actions</option>
                        <option v-for="action in actions" :key="action" :value="action">
                            {{ action }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Logs table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="loading">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <svg class="animate-spin h-8 w-8 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-2">Loading...</p>
                        </td>
                    </tr>
                    <tr v-else-if="logs.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No activity logs found
                        </td>
                    </tr>
                    <tr v-else v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(log.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="['badge', getActionColor(log.action)]">
                                {{ log.action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ log.description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ log.admin?.name || 'System' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            {{ log.ip_address }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <Pagination
                :currentPage="currentPage"
                :totalPages="totalPages"
                :totalItems="totalItems"
                :perPage="perPage"
                @pageChange="changePage"
            />
        </div>
    </div>
</template>
