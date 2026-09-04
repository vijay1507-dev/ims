<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Back Button -->
            <Link 
                href="/customers"
                class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors inline-flex items-center gap-2 mb-6"
            >
                <i class="fas fa-arrow-left"></i>
                Back to Customers
            </Link>

            <!-- Customer Master Profile Shell Header -->
            <div class="bg-white rounded-xl border border-gray-200 mb-6 shadow-2xs overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <!-- Company logo/avatar -->
                            <img class="h-20 w-20 rounded-full border-2 border-indigo-50 shadow-xs shrink-0 object-cover" :src="customer.avatar" :alt="customer.name" />
                            <div>
                                <div class="flex items-center gap-3">
                                    <h2 class="text-2xl font-bold text-gray-900">{{ customer.name }}</h2>
                                    <!-- Subscription status token -->
                                    <span :class="[
                                        'text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide',
                                        customer.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 
                                        customer.status === 'trial' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'
                                    ]">{{ customer.status === 'active' ? 'PAID' : customer.status.toUpperCase() }}</span>
                                </div>

                                

                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3 shrink-0">
                            <Link
                                :href="`/customers/${customer.id}/edit`"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-bold text-gray-700 transition-colors inline-flex items-center shadow-2xs"
                            >
                                <i class="fas fa-edit mr-2 text-indigo-600"></i>Edit Profile
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Master Client Metrics KPI Strip -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Verified Active Packages -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Active Subscriptions</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ subscriptions.length }}</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-credit-card text-indigo-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Assigned inventory/devices -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned Hardware</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ devices.length }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-box text-purple-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Onboarding Milestone -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Customer Since</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ customer.joined_date }}</p>
                    </div>
                    <div class="bg-sky-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-calendar-alt text-sky-600 text-xl w-5 text-center"></i>
                    </div>
                </div>
            </div>

            <!-- Tabbed Configuration Interface Layer -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                <!-- Navigation Tabs Array -->
                <div class="border-b border-gray-200 bg-gray-50/50 px-6">
                    <nav class="flex space-x-6 overflow-x-auto" aria-label="Tabs">
                        <button
                            v-for="tab in availableTabs"
                            :key="tab.id"
                            type="button"
                            :class="[
                                'py-4 px-2 border-b-2 font-bold text-sm whitespace-nowrap transition-colors cursor-pointer inline-flex items-center gap-2',
                                activeTab === tab.id
                                    ? 'border-indigo-600 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300'
                            ]"
                            @click="activeTab = tab.id"
                        >
                            <i :class="tab.icon"></i>{{ tab.label }}
                            <span v-if="tab.badge !== undefined" class="ml-1 text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.2 rounded-full font-semibold">
                                {{ tab.badge }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- SPA View Contents Shell -->
                <div class="p-6">
                    <!-- Tab 1: Overview Panel -->
                    <div v-show="activeTab === 'overview'" class="animate-fade-in space-y-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Contact details block -->
                            <div class="bg-gray-50/50 rounded-xl p-5 border border-gray-100">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                    <i class="fas fa-address-card text-indigo-500"></i>Contact Parameters
                                </h3>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <dt class="text-gray-500 font-medium">Name</dt>
                                        <dd class="text-gray-900 font-bold">{{ customer.name }}</dd>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <dt class="text-gray-500 font-medium">Email</dt>
                                        <dd class="text-indigo-600 font-medium">{{ customer.email }}</dd>
                                    </div>
                                    <div class="flex justify-between py-1 border-gray-100">
                                        <dt class="text-gray-500 font-medium">Contact Number</dt>
                                        <dd class="text-gray-900 font-medium">{{ customer.phone || 'N/A' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Billing / Internal Operational Tag Blocks -->
                            <div class="bg-gray-50/50 rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <i class="fas fa-file-invoice-dollar text-emerald-500"></i>Billing Parameters
                                    </h3>
                                    <dl class="space-y-3 text-sm">
                                        <div v-if="billing_details" class="space-y-3">
                                            <div class="flex justify-between py-1 border-b border-gray-100">
                                                <dt class="text-gray-500 font-medium">Current Plan</dt>
                                                <dd class="text-gray-900 font-bold">{{ billing_details.plan_name }}</dd>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-gray-100">
                                                <dt class="text-gray-500 font-medium">Billing Cycle</dt>
                                                <dd class="text-gray-900 font-medium capitalize">{{ billing_details.billing_cycle }}</dd>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-gray-100">
                                                <dt class="text-gray-500 font-medium">Subscription Status</dt>
                                                <dd>
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-emerald-100 text-emerald-800">
                                                        {{ billing_details.status }}
                                                    </span>
                                                </dd>
                                            </div>
                                            <div class="flex justify-between py-1">
                                                <dt class="text-gray-500 font-medium">Next Billing Date</dt>
                                                <dd class="text-gray-900 font-medium">{{ billing_details.next_billing_date || 'N/A' }}</dd>
                                            </div>
                                            
                                        </div>
                                        <div v-else class="text-center py-6">
                                            <p class="text-gray-400 text-xs italic">No active subscription detected</p>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                    <!-- Revenue Realtime Tracking Chart Canvas -->
                        <div class="bg-white rounded-xl border border-gray-100 p-5">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <i class="fas fa-chart-area text-indigo-600"></i>Client MRR Revenue Trajectory
                            </h3>
                            <div class="relative h-[240px] w-full">
                                <canvas ref="refCanvasRev"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Sub-Customers / Locations -->
                    <div v-show="activeTab === 'sub_customers'" class="animate-fade-in space-y-4">
                        <div class="flex justify-end">
                            <Link
                                :href="`/customers/${customer.id}/sub-customers/create`"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-bold shadow-sm inline-flex items-center"
                            >
                                <i class="fas fa-plus mr-2"></i>Add Sub-Customer
                            </Link>
                        </div>

                        <div v-if="subCustomers.length === 0" class="text-center py-10 text-gray-400 text-sm italic border border-dashed border-gray-200 rounded-xl">
                            No sub-customers/locations yet. Add one to start tracking location-specific payments.
                        </div>

                        <div v-else class="border border-gray-200 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3.5">Sub-Customer</th>
                                        <th class="px-6 py-3.5">Payments</th>
                                        <th class="px-6 py-3.5">Revenue</th>
                                        <th class="px-6 py-3.5">Outstanding</th>
                                        <th class="px-6 py-3.5">Status</th>
                                        <th class="px-6 py-3.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="sc in subCustomers" :key="sc.id" class="hover:bg-gray-50/50">
                                        <td class="px-6 py-4 font-bold text-gray-900">{{ sc.name }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ sc.payments_count }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900">${{ number_format(sc.revenue, 2) }}</td>
                                        <td class="px-6 py-4 font-medium text-amber-600">${{ number_format(sc.outstanding, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="[
                                                'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase',
                                                sc.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'
                                            ]">{{ sc.status }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                            <Link :href="`/sub-customers/${sc.id}`" class="text-xs font-bold text-indigo-600 hover:text-indigo-900">View</Link>
                                            <Link :href="`/sub-customers/${sc.id}/edit`" class="text-xs font-bold text-gray-600 hover:text-gray-900">Edit</Link>
                                            <Link
                                                :href="`/sub-customers/${sc.id}`"
                                                method="delete"
                                                as="button"
                                                type="button"
                                                :disabled="sc.payments_count > 0"
                                                :class="['text-xs font-bold', sc.payments_count > 0 ? 'text-gray-300 cursor-not-allowed' : 'text-rose-600 hover:text-rose-900']"
                                                :title="sc.payments_count > 0 ? 'Cannot delete: has payment history' : 'Delete'"
                                                @click="!confirm('Delete this sub-customer?') && $event.preventDefault()"
                                            >Delete</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Active Plans / Subscriptions status -->
                    <div v-show="activeTab === 'subscriptions'" class="animate-fade-in space-y-4">
                        <div
                            v-for="sub in subscriptions"
                            :key="sub.id"
                            class="border border-gray-200 rounded-xl p-5 hover:border-indigo-200 transition-colors bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                        >
                            <div>
                                <div class="flex items-center gap-3">
                                    <h4 class="font-bold text-gray-900 text-base">{{ sub.plan_name }}</h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-emerald-100 text-emerald-800">
                                        {{ sub.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Recurrent processing mapped under active subscription bindings</p>
                                
                                <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-gray-600 font-medium">
                                    <span class="bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                        <i class="fas fa-sync text-indigo-500 mr-1.5"></i>Cycle: <strong class="text-gray-900 uppercase">{{ sub.billing_cycle }}</strong>
                                    </span>
                                    <span class="bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                        <i class="fas fa-dollar-sign text-emerald-600 mr-1"></i>Rate: <strong class="text-gray-900">${{ number_format(sub.amount, 2) }}</strong>
                                    </span>
                                    <span class="bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                        <i class="fas fa-calendar-check text-sky-500 mr-1.5"></i>Next Billing: <strong class="text-gray-900">{{ sub.next_billing_date }}</strong>
                                    </span>
                                </div>
                            </div>
                            
                            <Link
                                :href="`/subscriptions/${sub.id}/edit`"
                                class="px-3 py-1.5 bg-gray-50 hover:bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold border border-gray-200 transition-colors inline-flex items-center self-start sm:self-center"
                            >
                                Configure Plan
                            </Link>
                        </div>
                    </div>

                    <!-- Tab 3: Billing history -->
                    <div v-show="activeTab === 'payments'" class="animate-fade-in">
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3.5">Clearing Date</th>
                                        <th class="px-6 py-3.5">Transaction Hook</th>
                                        <th class="px-6 py-3.5">Amount</th>
                                        <th class="px-6 py-3.5">State</th>
                                        <th class="px-6 py-3.5 text-right">Invoice Document</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="pay in payments" :key="pay.id" class="hover:bg-gray-50/50">
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">
                                            {{ pay.payment_date }}
                                            <span v-if="pay.sub_customer_id" class="block text-[10px] font-bold text-indigo-500 uppercase mt-0.5">
                                                {{ subCustomers.find(sc => sc.id === pay.sub_customer_id)?.name || 'Location' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-xs">
                                            {{ pay.payment_method || 'Standard Electronic Transfer' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                            ${{ number_format(pay.amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-emerald-100 text-emerald-800">
                                                {{ pay.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-xs font-mono font-bold text-indigo-600 hover:text-indigo-900 cursor-pointer p-1 bg-indigo-50 rounded">
                                                {{ pay.invoice_ref || 'INV-GEN-902' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Assigned inventory/devices -->
                    <div v-show="activeTab === 'inventory'" class="animate-fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="dev in devices"
                                :key="dev.id"
                                class="border border-gray-200 rounded-xl p-4 bg-white hover:shadow-xs transition-shadow flex flex-col justify-between"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                            <i :class="[dev.icon || 'fas fa-server', 'text-xl', dev.color_class || 'text-indigo-600']"></i>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-emerald-100 text-emerald-800">
                                            {{ dev.status }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-sm">{{ dev.type_name || 'Provisioned Telemetry Server' }}</h4>
                                    <p class="text-xs font-mono text-gray-500 mt-1">Serial: {{ dev.serial_license }}</p>
                                </div>
                                
                                <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500 space-y-1">
                                    <div class="flex justify-between">
                                        <span>Assigned Key:</span>
                                        <strong class="text-gray-700">{{ dev.assigned_date }}</strong>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Warranty Scope:</span>
                                        <strong class="text-indigo-600">{{ dev.warranty_expiry }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: Activities traces -->
                    <div v-show="activeTab === 'activity'" class="animate-fade-in">
                        <div class="space-y-4 max-w-3xl">
                            <div
                                v-for="act in activities"
                                :key="act.id"
                                :class="['flex items-start space-x-3 p-4 border-l-4 rounded-r-xl', act.color]"
                            >
                                <div class="bg-white/80 p-2 rounded-lg shrink-0 shadow-2xs">
                                    <i :class="act.icon"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-900">{{ act.title }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1">{{ act.date }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    subscriptions: {
        type: Array,
        required: true,
    },
    payments: {
        type: Array,
        required: true,
    },
    devices: {
        type: Array,
        required: true,
    },
    activities: {
        type: Array,
        required: true,
    },
    revenueTrend: {
        type: Object,
        required: true,
    },
    billing_details: {
        type: Object,
        default: null,
    },
    subCustomers: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('overview');

const availableTabs = [
    { id: 'overview', label: 'Overview Profile', icon: 'fas fa-id-card' },
    { id: 'sub_customers', label: 'Sub-Customers', icon: 'fas fa-sitemap', badge: props.subCustomers.length },
    { id: 'subscriptions', label: 'Active Plans', icon: 'fas fa-layer-group', badge: props.subscriptions.length },
    { id: 'payments', label: 'Billing Ledgers', icon: 'fas fa-receipt', badge: props.payments.length },
    { id: 'inventory', label: 'Assigned Inventory', icon: 'fas fa-boxes', badge: props.devices.length },
    { id: 'activity', label: 'Audit Log Feed', icon: 'fas fa-history' },
];

const number_format = (val, decimals = 2) => {
    return Number(val).toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
};

const refCanvasRev = ref(null);

onMounted(() => {
    if (typeof window === 'undefined' || !window.Chart || !refCanvasRev.value) return;

    new window.Chart(refCanvasRev.value.getContext('2d'), {
        type: 'line',
        data: {
            labels: props.revenueTrend.labels,
            datasets: [{
                label: 'Compounded Yield',
                data: props.revenueTrend.data,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.08)',
                tension: 0.3,
                fill: true,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } } }
        }
    });
});
</script>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-2px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.25s ease-out forwards;
}
</style>
