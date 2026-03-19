<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import StatsCard from '../components/StatsCard.vue';
import { Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const stats = ref(null);
const registrationTrends = ref([]);
const userStatus = ref([]);
const loading = ref(true);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
};

const lineChartData = ref({
    labels: [],
    datasets: [{
        label: 'New Users',
        data: [],
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        fill: true,
        tension: 0.4
    }]
});

const doughnutChartData = ref({
    labels: [],
    datasets: [{
        data: [],
        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
        borderWidth: 0
    }]
});

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/admin/stats');
        stats.value = response.data.stats;
        registrationTrends.value = response.data.registration_trends;
        userStatus.value = response.data.user_status;

        // Update charts
        lineChartData.value.labels = registrationTrends.value.map(t => {
            const date = new Date(t.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        lineChartData.value.datasets[0].data = registrationTrends.value.map(t => t.count);

        doughnutChartData.value.labels = userStatus.value.map(s => s.status);
        doughnutChartData.value.datasets[0].data = userStatus.value.map(s => s.count);
    } catch (error) {
        console.error('Failed to fetch stats:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});
</script>

<template>
    <div class="p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Overview of your application</p>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="flex items-center justify-center h-64">
            <svg class="animate-spin h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <template v-else>
            <!-- Stats cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <StatsCard
                    title="Total Users"
                    :value="stats?.total_users || 0"
                    icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                    color="indigo"
                />
                <StatsCard
                    title="Active Users"
                    :value="stats?.active_users || 0"
                    icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    color="green"
                />
                <StatsCard
                    title="New Today"
                    :value="stats?.new_users_today || 0"
                    icon="M12 6v6m0 0v6m0-6h6m-6 0H6"
                    color="blue"
                />
                <StatsCard
                    title="Active Today"
                    :value="stats?.active_today || 0"
                    icon="M13 10V3L4 14h7v7l9-11h-7z"
                    color="yellow"
                />
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Registration trends -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Trends (Last 14 Days)</h3>
                    <div class="h-64">
                        <Line :data="lineChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- User status distribution -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Status</h3>
                    <div class="h-64">
                        <Doughnut :data="doughnutChartData" :options="doughnutOptions" />
                    </div>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">This Week</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ stats?.new_users_this_week || 0 }}</p>
                    <p class="text-gray-500">new registrations</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">This Month</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ stats?.new_users_this_month || 0 }}</p>
                    <p class="text-gray-500">new registrations</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Verified Users</h3>
                    <p class="text-3xl font-bold text-green-600">{{ stats?.verified_users || 0 }}</p>
                    <p class="text-gray-500">email verified</p>
                </div>
            </div>
        </template>
    </div>
</template>
