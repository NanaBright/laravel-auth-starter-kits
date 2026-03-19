<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import DataTable from '../components/DataTable.vue';
import Pagination from '../components/Pagination.vue';

const router = useRouter();

const users = ref([]);
const loading = ref(true);
const selectedUsers = ref([]);

// Pagination
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const perPage = ref(15);

// Filters
const search = ref('');
const filterActive = ref('');
const filterAdmin = ref('');
const filterVerified = ref('');

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'status', label: 'Status' },
    { key: 'role', label: 'Role' },
    { key: 'created_at', label: 'Joined' },
    { key: 'actions', label: 'Actions' },
];

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
        };

        if (search.value) params.search = search.value;
        if (filterActive.value !== '') params.is_active = filterActive.value;
        if (filterAdmin.value !== '') params.is_admin = filterAdmin.value;
        if (filterVerified.value !== '') params.verified = filterVerified.value;

        const response = await axios.get('/admin/users', { params });
        users.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
        totalItems.value = response.data.total;
        perPage.value = response.data.per_page;
    } catch (error) {
        console.error('Failed to fetch users:', error);
    } finally {
        loading.value = false;
    }
};

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        fetchUsers();
    }, 300);
});

watch([filterActive, filterAdmin, filterVerified], () => {
    currentPage.value = 1;
    fetchUsers();
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const viewUser = (user) => {
    router.push(`/users/${user.id}`);
};

const toggleSelect = (id) => {
    const index = selectedUsers.value.indexOf(id);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(id);
    }
};

const selectAll = (checked) => {
    if (checked) {
        selectedUsers.value = users.value.map(u => u.id);
    } else {
        selectedUsers.value = [];
    }
};

const deleteSelected = async () => {
    if (!confirm(`Are you sure you want to delete ${selectedUsers.value.length} users?`)) {
        return;
    }

    try {
        await axios.post('/admin/users/bulk-delete', { ids: selectedUsers.value });
        selectedUsers.value = [];
        fetchUsers();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to delete users');
    }
};

const toggleUserStatus = async (user, event) => {
    event.stopPropagation();
    try {
        await axios.post(`/admin/users/${user.id}/toggle-status`);
        user.is_active = !user.is_active;
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to update user status');
    }
};

const deleteUser = async (user, event) => {
    event.stopPropagation();
    if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
        return;
    }

    try {
        await axios.delete(`/admin/users/${user.id}`);
        fetchUsers();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to delete user');
    }
};

const exportUsers = async () => {
    try {
        const params = {};
        if (search.value) params.search = search.value;
        if (filterActive.value !== '') params.is_active = filterActive.value;
        if (filterAdmin.value !== '') params.is_admin = filterAdmin.value;

        const response = await axios.get('/admin/users/export', {
            params,
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `users_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        alert('Failed to export users');
    }
};

const changePage = (page) => {
    currentPage.value = page;
    fetchUsers();
};

onMounted(() => {
    fetchUsers();
});
</script>

<template>
    <div class="p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Users</h1>
                <p class="text-gray-600">Manage user accounts</p>
            </div>
            <div class="flex space-x-3">
                <button @click="exportUsers" class="btn-secondary">
                    Export CSV
                </button>
                <router-link to="/users/create" class="btn-primary">
                    Add User
                </router-link>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Name or email..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="filterActive" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select v-model="filterAdmin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="1">Admin</option>
                        <option value="0">User</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Verified</label>
                    <select v-model="filterVerified" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="1">Verified</option>
                        <option value="0">Unverified</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bulk actions -->
        <div v-if="selectedUsers.length > 0" class="bg-indigo-50 rounded-lg p-4 mb-4 flex items-center justify-between">
            <span class="text-indigo-700">{{ selectedUsers.length }} users selected</span>
            <button @click="deleteSelected" class="btn-danger text-sm">
                Delete Selected
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <DataTable
                :columns="columns"
                :data="users"
                :loading="loading"
                :selectable="true"
                :selected="selectedUsers"
                @select="toggleSelect"
                @selectAll="selectAll"
                @rowClick="viewUser"
            >
                <template #name="{ row }">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-indigo-600 font-medium">{{ row.name.charAt(0) }}</span>
                        </div>
                        <span class="font-medium">{{ row.name }}</span>
                    </div>
                </template>
                <template #status="{ row }">
                    <span :class="['badge', row.is_active ? 'badge-success' : 'badge-danger']">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
                <template #role="{ row }">
                    <span :class="['badge', row.is_admin ? 'badge-info' : 'badge-warning']">
                        {{ row.is_admin ? 'Admin' : 'User' }}
                    </span>
                </template>
                <template #created_at="{ value }">
                    {{ formatDate(value) }}
                </template>
                <template #actions="{ row }">
                    <div class="flex space-x-2" @click.stop>
                        <button @click="toggleUserStatus(row, $event)" :class="['text-sm px-2 py-1 rounded', row.is_active ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50']">
                            {{ row.is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button @click="deleteUser(row, $event)" class="text-sm px-2 py-1 rounded text-red-600 hover:bg-red-50">
                            Delete
                        </button>
                    </div>
                </template>
            </DataTable>

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
