<template>
    <AppLayout>
        <div class="p-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Subscription Management</h2>
                    <p class="text-gray-600 mt-1">Manage plans, pricing tiers, and subscriber lifecycle</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button 
                        v-if="$page.props.auth.user.permissions.includes('bulk-delete-subscriptions')"
                        @click="showBulkDeleteModal = true"
                        type="button" 
                        class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium transition-colors inline-flex items-center cursor-pointer"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>Bulk Delete
                    </button>
                    <button 
                        @click="handleExport"
                        type="button" 
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <!-- Dedicated separate multi-page link -->
                    <Link
                        href="/subscriptions/create"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-plus mr-2"></i>New Subscription
                    </Link>
                </div>
            </div>

            <!-- Top Metric Counters Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <KpiCard
                    title="Total Subscriptions"
                    :value="metrics.total_subscriptions"
                    icon="fas fa-layer-group"
                    icon-bg-class="bg-indigo-100"
                    icon-color-class="text-indigo-600"
                />
                <KpiCard
                    title="Active Subscriptions"
                    :value="metrics.active_subscriptions"
                    icon="fas fa-check-circle"
                    icon-bg-class="bg-green-100"
                    icon-color-class="text-green-600"
                />
                <KpiCard
                    title="Churned (Last 30d)"
                    :value="metrics.churned_count"
                    icon="fas fa-user-minus"
                    icon-bg-class="bg-red-100"
                    icon-color-class="text-red-600"
                />
                <KpiCard
                    title="Total MRR"
                    :value="metrics.total_mrr"
                    icon="fas fa-chart-line"
                    icon-bg-class="bg-emerald-100"
                    icon-color-class="text-emerald-600"
                />
            </div>

            <!-- Integrated Data Table with Live Filters -->
            <DataTable
                title="Active Subscriptions List"
                :columns="[
                    { label: 'Subscriber', key: 'subscriber' },
                    { label: 'Sub-Customer', key: 'sub_customer_name' },
                    { label: 'Plan Tier', key: 'plan' },
                    { label: 'Cycle', key: 'cycle' },
                    { label: 'Start Date', key: 'start_date' },
                    { label: 'End Date', key: 'next_billing' },
                    { label: 'Amount', key: 'amount' },
                    { label: 'Status', key: 'status' },
                    { label: 'Actions', key: 'actions', align: 'right' }
                ]"
                :has-rows="subscriptions.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="subscriptions.length"
            >
                <template #filters>
                    <!-- Real-time Text Search input -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search subscriber info..."
                            class="w-48 sm:w-64 pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                            @input="triggerFilter"
                        >
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400"></i>
                    </div>

                    <!-- Filter by Payment Status -->
                    <select
                        v-model="selectedStatus"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
                        @change="triggerFilter"
                    >
                        <option>All Status</option>
                        <option value="active">Active</option>
                        <option value="trial">Trial</option>
                        <option value="paused">Paused</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <!-- Filter by Assigned Plan -->
                    <select
                        v-model="selectedPlan"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
                        @change="triggerFilter"
                    >
                        <option>All Plans</option>
                        <option v-for="p in plans" :key="p.name" :value="p.name">{{ p.name }}</option>
                    </select>

                    <!-- Filter by Month -->
                    <select
                        v-model="selectedMonth"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
                        @change="triggerFilter"
                    >
                        <option v-for="m in availableMonths" :key="m" :value="m">{{ m }}</option>
                    </select>

                    <!-- Filter by Year -->
                    <select
                        v-model="selectedYear"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
                        @change="triggerFilter"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </template>

                <template #rows>
                    <tr
                        v-for="sub in paginatedSubscriptions"
                        :key="sub.id"
                        class="table-row hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ sub.customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ sub.customer_email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="sub.sub_customer_name" class="text-xs font-bold text-indigo-600">{{ sub.sub_customer_name }}</div>
                            <span v-else class="text-gray-400 text-xs">—</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                            {{ sub.plan_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-gray-100 text-gray-600 uppercase tracking-wider">
                                {{ formatBillingCycle(sub.billing_cycle) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ sub.start_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ sub.next_billing_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                            {{ sub.amount }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <StatusBadge :status="sub.status" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link
                                :href="`/subscriptions/${sub.id}/edit?page=${currentPage}`"
                                class="text-amber-600 hover:text-amber-900 mr-3 p-1.5 bg-amber-50 rounded-lg transition-colors inline-flex items-center justify-center"
                                title="Edit Subscription"
                            >
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button
                                type="button"
                                class="text-red-600 hover:text-red-900 p-1.5 bg-red-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Cancel Subscription"
                                @click="deleteSubscription(sub.id)"
                            >
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </DataTable>
            
            <BulkDeleteModal
                :show="showBulkDeleteModal"
                module="subscriptions"
                module-name="Subscriptions"
                @close="showBulkDeleteModal = false"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import KpiCard from '../../Components/Cards/KpiCard.vue';
import DataTable from '../../Components/Tables/DataTable.vue';
import StatusBadge from '../../Components/Shared/StatusBadge.vue';
import { exportToExcel } from '../../Utils/export';
import BulkDeleteModal from '../../Components/BulkDeleteModal.vue';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    plans: {
        type: Array,
        required: true,
    },
    subscriptions: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    availableMonths: {
        type: Array,
        required: true,
    },
    availableYears: {
        type: Array,
        required: true,
    },
});

// Live Reactive Filters bound from server parameters
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'All Status');
const selectedPlan = ref(props.filters.plan || 'All Plans');
const selectedMonth = ref(props.filters.filter_month || 'All Months');
const selectedYear = ref(props.filters.filter_year || 'All Years');

// Client Pagination States
const currentPage = ref(props.filters.page ? parseInt(props.filters.page) : 1);
const itemsPerPage = ref(10);
const showBulkDeleteModal = ref(false);

const paginatedSubscriptions = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.subscriptions.slice(start, end);
});

// Reset page on filter update
watch(() => [searchQuery.value, selectedStatus.value, selectedPlan.value, selectedMonth.value, selectedYear.value], () => {
    currentPage.value = 1;
});

// Keep the ?page= query param in sync with client-side pagination
const syncPageInUrl = (pageNum) => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', pageNum);
    window.history.replaceState(window.history.state, '', url);
};

onMounted(() => {
    syncPageInUrl(currentPage.value);
});

watch(currentPage, (newPage) => {
    syncPageInUrl(newPage);
});

let filterTimeout = null;

const triggerFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get('/subscriptions', {
            search: searchQuery.value,
            status: selectedStatus.value,
            plan: selectedPlan.value,
            filter_month: selectedMonth.value,
            filter_year: selectedYear.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 250); // Fluid debounce mapping string queries
};

const deleteSubscription = (id) => {
    if (confirm('Are you sure you want to permanently cancel and remove this subscription assignment?')) {
        router.delete(`/subscriptions/${id}`, {
            preserveScroll: true,
        });
    }
};

const formatBillingCycle = (cycle) => {
    const map = {
        monthly: 'Monthly',
        annual: 'Yearly',
        two_months: '2 Months',
        half_yearly: 'Half Yearly',
        quarterly: 'Quarterly',
        evently: 'Evently'
    };
    return map[cycle] || cycle;
};

const handleExport = () => {
    const columnMap = {
        'customer_name': 'Subscriber Name',
        'customer_email': 'Subscriber Email',
        'plan_name': 'Plan Tier',
        'billing_cycle': 'Billing Cycle',
        'start_date': 'Start Date',
        'next_billing_date': 'End Date (Next Invoice)',
        'renewal_date': 'Renewal Date',
        'amount': 'Subscription Amount',
        'status': 'Status'
    };
    exportToExcel(props.subscriptions, 'Subscriptions_Export', columnMap);
};
</script>
