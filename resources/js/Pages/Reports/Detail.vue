<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Navigation Back Button -->
            <div class="mb-6">
                <Link
                    :href="route('reports.index')"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-bold transition-colors inline-flex items-center cursor-pointer shadow-2xs text-gray-700"
                >
                    <i class="fas fa-arrow-left mr-2"></i>Back to Reports
                </Link>
            </div>

            <!-- Page Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ typeName }}</h2>
                    <p class="text-gray-600 mt-1">Detailed list of records for the month of <strong>{{ month }}</strong></p>
                </div>
                <!-- Dynamic Month and Year Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label for="monthFilter" class="text-sm font-bold text-gray-700">Month:</label>
                        <select
                            id="monthFilter"
                            :value="filterMonth"
                            @change="onMonthChange"
                            class="rounded-lg border-gray-300 text-sm font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-3 py-2 cursor-pointer"
                        >
                            <option v-for="m in availableMonths" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="yearFilter" class="text-sm font-bold text-gray-700">Year:</label>
                        <select
                            id="yearFilter"
                            :value="filterYear"
                            @change="onYearChange"
                            class="rounded-lg border-gray-300 text-sm font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-3 py-2 cursor-pointer"
                        >
                            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>
            </div>


            <!-- Customer Activity Filter Tabs (Only for Customer Activity List) -->
            <div v-if="type === 'customers'" class="flex space-x-2 bg-gray-100 p-1.5 rounded-lg w-fit mb-6">
                <button
                    v-for="filterOpt in ['all', 'new', 'renewal']"
                    :key="filterOpt"
                    @click="activityFilter = filterOpt"
                    :class="[
                        activityFilter === filterOpt
                            ? 'bg-white text-indigo-600 font-bold shadow-2xs'
                            : 'text-gray-600 hover:text-gray-900',
                        'px-4 py-2 text-sm rounded-md capitalize transition-all duration-200 cursor-pointer font-semibold'
                    ]"
                >
                    {{ filterOpt === 'all' ? 'All Customers' : (filterOpt === 'new' ? 'New Customers' : 'Renewed Customers') }}
                </button>
            </div>

            <!-- Detail List Card Table -->
            <div class="bg-white rounded-xl shadow-2xs border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead>
                            <tr class="bg-gray-50">
                                <template v-if="type === 'customers'">
                                    <th class="px-6 py-4 font-bold text-gray-600">Customer Name</th>
                                    <th class="px-6 py-4 font-bold text-gray-600">Payment Date</th>
                                    <th class="px-6 py-4 font-bold text-gray-600 text-center">Payment Type</th>
                                    <th class="px-6 py-4 font-bold text-gray-600">Subscription</th>
                                    <th class="px-6 py-4 font-bold text-gray-600 text-right">Payment Amount</th>
                                    <th class="px-6 py-4 font-bold text-gray-600 text-center">Payment Status</th>
                                </template>
                                <template v-else>
                                    <th class="px-6 py-4 font-bold text-gray-600">Entity / Reference</th>
                                    <th class="px-6 py-4 font-bold text-gray-600">Detail Info</th>
                                    <th class="px-6 py-4 font-bold text-gray-600">Date</th>
                                    <th class="px-6 py-4 font-bold text-gray-600 text-center">Status</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="(item, idx) in filteredItems" :key="idx">
                                <tr 
                                    @click="toggleRow(idx)" 
                                    :class="[
                                        type === 'customers' ? 'cursor-pointer hover:bg-indigo-50/20' : 'hover:bg-gray-50',
                                        'transition-colors'
                                    ]"
                                >
                                    <template v-if="type === 'customers'">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <i 
                                                    class="fas text-gray-400 text-xs shrink-0 transition-transform duration-200"
                                                    :class="expandedRows.includes(idx) ? 'fa-chevron-down rotate-0' : 'fa-chevron-right'"
                                                ></i>
                                                <div>
                                                    <div class="font-bold text-gray-900 flex flex-wrap items-center gap-1.5">
                                                        {{ item.name }}
                                                        <template v-if="item.sub_customer_name">
                                                            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-600 rounded">
                                                                {{ item.sub_customer_name }}
                                                            </span>
                                                        </template>
                                                        <template v-else-if="item.sub_customers && item.sub_customers.length > 0">
                                                            <span v-for="sub in item.sub_customers" :key="sub" class="px-1.5 py-0.5 text-[10px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-600 rounded">
                                                                {{ sub }}
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-0.5">{{ item.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 font-medium">{{ item.date || 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="item.payment_context === 'renewal' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200'" class="px-2.5 py-1 rounded-full text-xs font-bold inline-block capitalize">
                                                {{ item.payment_context }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 font-medium">
                                            <div v-if="item.subscriptions && item.subscriptions.length > 0">
                                                {{ item.subscriptions[0].plan_name }} ({{ item.subscriptions[0].billing_cycle }})
                                            </div>
                                            <div v-else class="text-gray-400 italic">No Active Plan</div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-900 font-bold">{{ formatCurrency(item.amount) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="getStatusBadgeClass(item.payment_status)" class="px-2.5 py-1 rounded-full text-xs font-bold inline-block">
                                                {{ item.payment_status }}
                                            </span>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="font-bold text-gray-900 flex flex-wrap items-center gap-1.5">
                                                    {{ item.name }}
                                                    <template v-if="item.sub_customer_name">
                                                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-600 rounded">
                                                            {{ item.sub_customer_name }}
                                                        </span>
                                                    </template>
                                                    <template v-else-if="item.sub_customers && item.sub_customers.length > 0">
                                                        <span v-for="sub in item.sub_customers" :key="sub" class="px-1.5 py-0.5 text-[10px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-600 rounded">
                                                            {{ sub }}
                                                        </span>
                                                    </template>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ item.email }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 font-medium">{{ item.details }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ item.date || 'N/A' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="getStatusBadgeClass(item.status)" class="px-2.5 py-1 rounded-full text-xs font-bold inline-block">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                    </template>
                                </tr>
                                <!-- Cascaded relational audit sub-drawer -->
                                <tr v-if="expandedRows.includes(idx) && type === 'customers'" class="bg-gray-50/50">
                                    <td colspan="6" class="px-6 py-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 p-5 bg-white rounded-xl border border-gray-200 shadow-xs">
                                            <!-- Subscriptions sub-ledger -->
                                            <div>
                                                <h4 class="font-bold text-gray-900 text-xs mb-3 uppercase tracking-wider flex items-center gap-1.5">
                                                    <i class="fas fa-file-invoice-dollar text-indigo-600"></i> Subscriptions
                                                </h4>
                                                <div v-if="item.subscriptions.length > 0" class="space-y-2">
                                                    <div v-for="(sub, sIdx) in item.subscriptions" :key="sIdx" class="text-xs p-2.5 bg-gray-50 border border-gray-150 rounded-lg flex items-center justify-between">
                                                        <div>
                                                             <div class="font-bold text-gray-800 flex items-center gap-1.5">
                                                                 {{ sub.plan_name }}
                                                                 <span v-if="sub.sub_customer_name" class="px-1.5 py-0.5 text-[9px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 rounded">
                                                                     {{ sub.sub_customer_name }}
                                                                 </span>
                                                             </div>
                                                             <div class="text-[10px] text-gray-500 font-medium">{{ formatCurrency(sub.amount) }} / {{ sub.billing_cycle }}</div>
                                                        </div>
                                                        <span :class="getStatusBadgeClass(sub.status)" class="px-2 py-0.5 rounded text-[9px] font-bold">
                                                            {{ sub.status }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-else class="text-xs text-gray-400 italic py-2">No subscriptions registered.</div>
                                            </div>

                                            <!-- Invoices sub-ledger -->
                                            <div>
                                                <h4 class="font-bold text-gray-900 text-xs mb-3 uppercase tracking-wider flex items-center gap-1.5">
                                                    <i class="fas fa-file-invoice text-emerald-600"></i> Invoices
                                                </h4>
                                                <div v-if="item.invoices.length > 0" class="space-y-2">
                                                    <div v-for="(inv, iIdx) in item.invoices" :key="iIdx" class="text-xs p-2.5 bg-gray-50 border border-gray-150 rounded-lg flex items-center justify-between">
                                                        <div>
                                                            <div class="font-bold text-gray-800">{{ inv.invoice_number }}</div>
                                                            <div class="text-[10px] text-gray-500 font-medium">Due: {{ inv.due_date }}</div>
                                                        </div>
                                                        <span :class="getStatusBadgeClass(inv.status)" class="px-2 py-0.5 rounded text-[9px] font-bold">
                                                            {{ inv.status }} ({{ formatCurrency(inv.total) }})
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-else class="text-xs text-gray-400 italic py-2">No invoices issued.</div>
                                            </div>

                                            <!-- Payments sub-ledger -->
                                            <div>
                                                <h4 class="font-bold text-gray-900 text-xs mb-3 uppercase tracking-wider flex items-center gap-1.5">
                                                    <i class="fas fa-credit-card text-blue-600"></i> Payments
                                                </h4>
                                                <div v-if="item.payments.length > 0" class="space-y-2">
                                                    <div v-for="(pay, pIdx) in item.payments" :key="pIdx" class="text-xs p-2.5 bg-gray-50 border border-gray-150 rounded-lg flex items-center justify-between">
                                                        <div>
                                                             <div class="font-mono font-bold text-gray-800 text-[10px] flex items-center gap-1.5">
                                                                 {{ pay.transaction_id }}
                                                                 <span v-if="pay.sub_customer_name" class="px-1.5 py-0.5 text-[9px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 rounded">
                                                                     {{ pay.sub_customer_name }}
                                                                 </span>
                                                             </div>
                                                             <div class="text-[10px] text-gray-500 font-medium font-semibold">Paid: {{ pay.payment_date }}</div>
                                                        </div>
                                                        <span :class="getStatusBadgeClass(pay.status)" class="px-2 py-0.5 rounded text-[9px] font-bold">
                                                            {{ pay.status }} ({{ formatCurrency(pay.amount) }})
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-else class="text-xs text-gray-400 italic py-2">No payments collected.</div>
                                            </div>

                                            <!-- Renewals sub-ledger -->
                                            <div>
                                                <h4 class="font-bold text-gray-900 text-xs mb-3 uppercase tracking-wider flex items-center gap-1.5">
                                                    <i class="fas fa-sync text-orange-600"></i> Renewals
                                                </h4>
                                                <div v-if="item.renewals && item.renewals.length > 0" class="space-y-2">
                                                    <div v-for="(ren, rIdx) in item.renewals" :key="rIdx" class="text-xs p-2.5 bg-gray-50 border border-gray-150 rounded-lg flex items-center justify-between">
                                                        <div>
                                                            <div class="font-bold text-gray-800 capitalize">{{ ren.type }} ({{ ren.plan_asset }})</div>
                                                            <div class="text-[10px] text-gray-500 font-medium">Date: {{ ren.renewal_date }}</div>
                                                        </div>
                                                        <span 
                                                            :class="[
                                                                ren.priority === 'high' || ren.priority === 'expired' ? 'bg-red-50 text-red-700 border border-red-200' : 
                                                                ren.priority === 'medium' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 
                                                                'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                                'px-2 py-0.5 rounded text-[9px] font-bold capitalize'
                                                            ]"
                                                        >
                                                            {{ ren.priority }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-else class="text-xs text-gray-400 italic py-2">No renewals scheduled.</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="filteredItems.length === 0">
                                <td :colspan="type === 'customers' ? 6 : 4" class="px-6 py-12 text-center text-gray-500">No records found for this timeframe.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    typeName: {
        type: String,
        required: true,
    },
    month: {
        type: String,
        required: true,
    },
    filterMonth: {
        type: String,
        required: true,
    },
    filterYear: {
        type: String,
        required: true,
    },
    items: {
        type: Array,
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

const activityFilter = ref('all');

const filteredItems = computed(() => {
    if (props.type !== 'customers') return props.items;

    const seen = new Set();
    const result = [];

    for (const item of props.items) {
        if (activityFilter.value === 'new' && item.payment_context !== 'new') {
            continue;
        }
        if (activityFilter.value === 'renewal' && item.payment_context !== 'renewal') {
            continue;
        }

        const ident = item.name;
        if (!seen.has(ident)) {
            seen.add(ident);
            result.push(item);
        }
    }

    return result;
});

const expandedRows = ref([]);

const toggleRow = (idx) => {
    if (props.type !== 'customers') return;
    const index = expandedRows.value.indexOf(idx);
    if (index > -1) {
        expandedRows.value.splice(index, 1);
    } else {
        expandedRows.value.push(idx);
    }
};

const formatCurrency = (val) => {
    return '$' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const onMonthChange = (e) => {
    router.visit(route('reports.detail', {
        type: props.type,
        filter_month: e.target.value,
        filter_year: props.filterYear
    }));
};

const onYearChange = (e) => {
    router.visit(route('reports.detail', {
        type: props.type,
        filter_month: props.filterMonth,
        filter_year: e.target.value
    }));
};

const getStatusBadgeClass = (status) => {
    const s = String(status).toLowerCase().trim();
    if (['active', 'successful', 'paid'].includes(s)) {
        return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
    }
    if (['pending', 'trial'].includes(s)) {
        return 'bg-amber-50 text-amber-700 border border-amber-200';
    }
    if (['inactive', 'failed', 'overdue', 'cancelled'].includes(s)) {
        return 'bg-red-50 text-red-700 border border-red-200';
    }
    return 'bg-gray-50 text-gray-700 border border-gray-200';
};
</script>
