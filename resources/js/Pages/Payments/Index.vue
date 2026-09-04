<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Payment Management</h2>
                </div>
                <div class="flex items-center space-x-3">
                    <button 
                        v-if="$page.props.auth.user.permissions.includes('bulk-delete-payments')"
                        @click="showBulkDeleteModal = true"
                        type="button" 
                        class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm font-bold transition-colors inline-flex items-center shadow-2xs cursor-pointer"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>Bulk Delete
                    </button>
                    <!-- Filter by Billing Cycle -->
                    <select
                        v-model="selectedBillingCycle"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700 shadow-2xs"
                        @change="triggerFilter"
                    >
                        <option value="All Cycles">All Cycles</option>
                        <option v-for="cycle in $page.props.activeBillingCycles" :key="cycle.code" :value="cycle.code">
                            {{ cycle.name }}
                        </option>
                    </select>

                    <!-- Filter by Month -->
                    <select
                        v-model="selectedMonth"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700 shadow-2xs"
                        @change="triggerFilter"
                    >
                        <option v-for="m in availableMonths" :key="m" :value="m">{{ m }}</option>
                    </select>

                    <!-- Filter by Year -->
                    <select
                        v-model="selectedYear"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700 shadow-2xs"
                        @change="triggerFilter"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>

                    <button 
                        @click="handleExport"
                        type="button" 
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-bold transition-colors inline-flex items-center shadow-2xs cursor-pointer"
                    >
                        <i class="fas fa-file-export mr-2 text-indigo-600"></i>Export CSV
                    </button>
                    <button 
                        @click="showImportModal = true"
                        type="button" 
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-bold transition-colors inline-flex items-center shadow-2xs cursor-pointer"
                    >
                        <i class="fas fa-upload mr-2 text-indigo-600"></i>Import Excel
                    </button>
                    <!-- Dedicated multi-page SPA link -->
                    <Link
                        href="/payments/create"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-bold shadow-xs transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-plus mr-2"></i>Record Payment
                    </Link>
                </div>
            </div>

            <!-- Failed payment alerts Global Banner -->
            <div v-if="hasFailedPayments" class="mb-8 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-2xs flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-rose-100 rounded-lg text-rose-600">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-rose-900">Failed Payment Alerts Detected</p>
                        <p class="text-xs text-rose-700 mt-0.5">Automated settlement systems caught recent clearing failures. Intervene to secure recurring revenues.</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="px-3 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-bold hover:bg-rose-700 transition-colors shadow-2xs shrink-0 cursor-pointer"
                    @click="filterFailedOnly"
                >
                    View Failed Items
                </button>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Payments -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Payments</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.total_payments }}</p>
                        <p class="text-[11px] text-blue-600 mt-1.5 font-bold inline-flex items-center">
                            <i class="fas fa-list mr-1"></i> All transactions
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-credit-card text-blue-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Successful Payments -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Successful Clearings</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.successful_payments }}</p>
                        <p class="text-[11px] text-indigo-600 mt-1.5 font-bold inline-flex items-center">
                            <i class="fas fa-check mr-1"></i> Settle validation loop
                        </p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-check-circle text-indigo-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Total Received -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Received</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ metrics.received_payments }}</p>
                        <p class="text-[11px] text-emerald-600 mt-1.5 font-bold inline-flex items-center">
                            <i class="fas fa-check-double mr-1"></i> Cleared funds
                        </p>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-wallet text-emerald-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Payments</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ metrics.pending_payments }}</p>
                        <p class="text-[11px] text-amber-600 mt-1.5 font-bold inline-flex items-center">
                            <i class="fas fa-hourglass-half mr-1"></i> Awaiting clearing
                        </p>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-clock text-amber-600 text-xl w-5 text-center"></i>
                    </div>
                </div>
            </div>

            <!-- Payment Analytics Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Payment Trends -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-line text-indigo-600"></i>Payment Volume Trends
                    </h3>
                    <div class="h-64 relative">
                        <canvas ref="trendCanvas"></canvas>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fas fa-network-wired text-emerald-600"></i>Gateway Settlement Sources
                    </h3>
                    <div class="h-64 relative">
                        <canvas ref="methodCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Integrated Data Table with Live Filters -->
            <DataTable
                title="Stripe-Style Transaction Clearing Table"
                :columns="[
                    { label: 'Transaction Key', key: 'transaction_id' },
                    { label: 'Invoice Ref', key: 'invoice_ref' },
                    { label: 'Payment Date', key: 'payment_date' },
                    { label: 'End Date', key: 'end_date' },
                    { label: 'Client', key: 'customer_name' },
                    { label: 'Sub-Customer', key: 'sub_customer_name' },
                    { label: 'Authorized Sum', key: 'amount' },
                    { label: 'Amount', key: 'usd_amount' },
                    { label: 'Billing Cycle', key: 'billing_cycle' },
                    { label: 'Status Marker', key: 'status' },
                    { label: 'Closed By', key: 'closed_by' },
                    { label: 'Operations & Serials', key: 'actions', align: 'right' }
                ]"
                :has-rows="payments.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="payments.length"
            >
                <template #filters>
                    <!-- Real-time Text Search input -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search transaction..."
                            class="w-48 sm:w-64 pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-semibold"
                            @input="triggerFilter"
                        >
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400"></i>
                    </div>

                    <!-- Filter by status -->
                    <select
                        v-model="selectedStatus"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold"
                        @change="triggerFilter"
                    >
                        <option>All Status</option>
                        <option value="successful">Successful</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>

                    <!-- Filter by Customer -->
                    <select
                        v-model="selectedCustomer"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold"
                        @change="onCustomerFilterChange"
                    >
                        <option value="All Customers">All Customers</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <!-- Filter by Representative -->
                    <select
                        v-model="selectedRepresentative"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold"
                        @change="triggerFilter"
                    >
                        <option value="All Representatives">All Representatives</option>
                        <option v-for="rep in representatives" :key="rep" :value="rep">{{ rep }}</option>
                    </select>

                </template>

                <template #rows>
                    <tr
                        v-for="p in paginatedPayments"
                        :key="p.id"
                        class="table-row hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-indigo-600">
                            {{ p.transaction_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-mono font-bold text-gray-900">
                            {{ p.invoice_ref }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 font-medium">
                            {{ p.payment_date }}
                        </td>
                        <!-- End Date Column -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 font-medium">
                            {{ p.end_date || 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">{{ p.customer_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="p.sub_customer_name" class="text-xs font-bold text-indigo-600">{{ p.sub_customer_name }}</div>
                            <span v-else class="text-gray-400 text-xs">—</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-900">
                            {{ formatWithCurrency(p.raw_amount, p.currency) }} <span class="text-[10px] text-gray-500 font-semibold uppercase">{{ p.currency }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-indigo-900">
                            ${{ parseFloat(p.usd_amount).toFixed(2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center text-xs font-bold text-gray-700">
                                <i class="fas fa-sync-alt mr-1.5 text-indigo-500 text-xs"></i>
                                {{ formatBillingCycle(p.billing_cycle) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                                :class="{
                                    'bg-emerald-100 text-emerald-800': p.status === 'successful',
                                    'bg-rose-100 text-rose-800': p.status === 'failed',
                                    'bg-amber-100 text-amber-800': p.status === 'pending',
                                    'bg-indigo-100 text-indigo-800': p.status === 'refunded'
                                }"
                            >
                                {{ p.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700">
                            {{ p.closed_by || '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                            <button
                                @click="generatePaymentPDF(p)"
                                type="button"
                                class="text-emerald-600 hover:text-emerald-900 mr-2 p-1.5 bg-emerald-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Download PDF"
                            >
                                <i class="fas fa-file-pdf"></i>
                            </button>
                            
                            <button
                                v-if="p.status === 'successful'"
                                type="button"
                                class="text-amber-600 hover:text-amber-900 mr-2 p-1.5 bg-amber-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Issue Refund"
                                @click="dispatchRefund(p.id)"
                            >
                                <i class="fas fa-undo"></i>
                            </button>

                            <Link
                                :href="`/payments/${p.id}/edit?page=${currentPage}`"
                                class="text-indigo-600 hover:text-indigo-900 mr-2 p-1.5 bg-indigo-50 rounded-lg transition-colors inline-flex items-center justify-center"
                                title="Edit Payment"
                            >
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button
                                type="button"
                                class="text-rose-600 hover:text-rose-900 p-1.5 bg-rose-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Delete Payment"
                                @click="deletePayment(p.id)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>

            </DataTable>
            
            <BulkDeleteModal
                :show="showBulkDeleteModal"
                module="payments"
                module-name="Payments"
                @close="showBulkDeleteModal = false"
            />

            <!-- Import Modal -->
            <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Import Excel Sheet</h3>
                        <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer bg-transparent border-0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-6">
                            Upload your client subscription Excel sheet (`.xlsx`). The system will parse it and automatically create or update Customers, Subscriptions, Invoices, and Payments.
                        </p>
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 transition-colors" @click="openFileSelector">
                            <input 
                                type="file" 
                                ref="fileInput" 
                                class="hidden" 
                                accept=".xlsx"
                                @change="handleFileChange"
                            />
                            
                            <template v-if="!selectedFile">
                                <i class="fas fa-file-excel text-4xl text-gray-400 mb-3 animate-bounce"></i>
                                <span class="text-sm font-semibold text-gray-700">Click to upload spreadsheet</span>
                                <span class="text-xs text-gray-400 mt-1">Excel formats only (.xlsx)</span>
                            </template>
                            <template v-else>
                                <i class="fas fa-file-circle-check text-4xl text-green-500 mb-3"></i>
                                <span class="text-sm font-semibold text-gray-900 truncate max-w-xs">{{ selectedFile.name }}</span>
                                <span class="text-xs text-gray-400 mt-1">{{ (selectedFile.size / 1024).toFixed(1) }} KB</span>
                            </template>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                        <button 
                            @click="showImportModal = false" 
                            type="button" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-medium transition-colors cursor-pointer bg-white"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="submitImport" 
                            :disabled="!selectedFile || isUploading"
                            type="button" 
                            class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm transition-colors disabled:opacity-50 inline-flex items-center cursor-pointer"
                        >
                            <span v-if="isUploading" class="inline-block animate-spin mr-2 border-2 border-white border-t-transparent h-4 w-4 rounded-full"></span>
                            {{ isUploading ? 'Processing...' : 'Start Import' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import DataTable from '../../Components/Tables/DataTable.vue';
import { exportToExcel } from '../../Utils/export';
import { generatePaymentPDF } from '../../Utils/pdf';
import BulkDeleteModal from '../../Components/BulkDeleteModal.vue';

const page = usePage();

const currencySymbols = {
    USD: '$',
    SGD: 'S$',
    INR: '₹',
    AED: 'د.إ',
    EUR: '€',
    GBP: '£',
    AUD: 'A$',
    NZD: 'NZ$',
};

const formatWithCurrency = (amount, currency) => {
    const symbol = currencySymbols[currency] || '';
    return symbol + Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatBillingCycle = (code) => {
    if (!code) return 'N/A';
    const cycles = page.props.activeBillingCycles || [];
    const cycle = cycles.find(c => c.code === code);
    return cycle ? cycle.name : code.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    payments: {
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
    customers: {
        type: Array,
        default: () => [],
    },
    subCustomers: {
        type: Array,
        default: () => [],
    },
    representatives: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        required: true,
    },
});

// Canvas chart hooks
const trendCanvas = ref(null);
const methodCanvas = ref(null);

let trendChartInstance = null;
let methodChartInstance = null;

const hasFailedPayments = computed(() => {
    return props.payments.some(p => p.status === 'failed');
});

const initCharts = () => {
    if (typeof window !== 'undefined' && window.Chart) {
        if (trendChartInstance) {
            trendChartInstance.destroy();
            trendChartInstance = null;
        }
        if (methodChartInstance) {
            methodChartInstance.destroy();
            methodChartInstance = null;
        }

        const successfulSums = {};
        const failedSums = {};
        const allDatesSet = new Set();

        const getMonthKey = (dateStr) => {
            if (!dateStr || dateStr === 'Today') return 'Today';
            const date = new Date(dateStr);
            if (isNaN(date)) return 'Unknown';
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const m = months[date.getMonth()];
            const y = date.getFullYear();
            return selectedYear.value === 'All Years' ? `${m} ${y}` : m;
        };

        props.payments.forEach(p => {
            const key = getMonthKey(p.payment_date);
            if (key === 'Unknown') return;
            allDatesSet.add(key);
            const amt = parseFloat(p.usd_amount || 0);
            if (p.status === 'successful') {
                successfulSums[key] = (successfulSums[key] || 0) + amt;
            } else if (p.status === 'failed') {
                failedSums[key] = (failedSums[key] || 0) + amt;
            }
        });

        let labels = Array.from(allDatesSet).sort((a, b) => {
            if (a === 'Today') return 1;
            if (b === 'Today') return -1;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const parseKey = (key) => {
                if (selectedYear.value === 'All Years') {
                    const parts = key.split(' ');
                    const mIdx = months.indexOf(parts[0]);
                    const y = parseInt(parts[1]);
                    return new Date(y, mIdx, 1);
                } else {
                    const mIdx = months.indexOf(key);
                    return new Date(2026, mIdx, 1);
                }
            };
            return parseKey(a) - parseKey(b);
        });
        if (labels.length === 0) labels = ['No Activity'];

        const successfulData = labels.map(d => successfulSums[d] || 0);
        const failedData = labels.map(d => failedSums[d] || 0);

        if (trendCanvas.value) {
            trendChartInstance = new window.Chart(trendCanvas.value.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Successful Settlements',
                            data: successfulData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3
                        },
                        {
                            label: 'Failed Processing',
                            data: failedData,
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.08)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } } }
                }
            });
        }

        const methodCounts = { visa: 0, mastercard: 0, stripe: 0, paypal: 0 };
        props.payments.forEach(p => {
            const typeKey = p.method_type ? p.method_type.toLowerCase() : 'visa';
            if (methodCounts[typeKey] !== undefined) methodCounts[typeKey]++;
            else methodCounts.visa++;
        });

        const methodDataArray = [methodCounts.visa, methodCounts.mastercard, methodCounts.stripe, methodCounts.paypal];

        if (methodCanvas.value) {
            methodChartInstance = new window.Chart(methodCanvas.value.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Visa Gateway', 'MasterCard Provider', 'Stripe Connect', 'PayPal Express'],
                    datasets: [{
                        data: methodDataArray,
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#8b5cf6'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
                }
            });
        }
    }
};

const syncPageInUrl = (pageNum) => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', pageNum);
    window.history.replaceState(window.history.state, '', url);
};

onMounted(() => {
    initCharts();
    syncPageInUrl(currentPage.value);
});

watch(() => props.payments, () => {
    initCharts();
}, { deep: true });

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'All Status');
const selectedMonth = ref(props.filters.filter_month || 'All Months');
const selectedYear = ref(props.filters.filter_year || 'All Years');
const selectedBillingCycle = ref(props.filters.billing_cycle || 'All Cycles');
const selectedCustomer = ref(props.filters.customer_id || 'All Customers');
const selectedSubCustomer = ref(props.filters.sub_customer_id || 'All Locations');
const selectedRepresentative = ref(props.filters.closed_by || 'All Representatives');

const filterableSubCustomers = computed(() => {
    if (selectedCustomer.value === 'All Customers') return props.subCustomers;
    return props.subCustomers.filter(sc => String(sc.customer_id) === String(selectedCustomer.value));
});

const onCustomerFilterChange = () => {
    // Changing the customer filter resets the sub-customer/location filter.
    selectedSubCustomer.value = 'All Locations';
    triggerFilter();
};

// Client Pagination States
const currentPage = ref(props.filters.page ? parseInt(props.filters.page) : 1);
const itemsPerPage = ref(10);
const showBulkDeleteModal = ref(false);
const showImportModal = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);
const isUploading = ref(false);

const openFileSelector = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const handleFileChange = (e) => {
    if (e.target.files && e.target.files.length > 0) {
        selectedFile.value = e.target.files[0];
    }
};

const submitImport = () => {
    if (!selectedFile.value) return;

    isUploading.value = true;
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('import_context', 'payments');

    router.post('/import-excel', formData, {
        forceFormData: true,
        onSuccess: () => {
            showImportModal.value = false;
            selectedFile.value = null;
        },
        onFinish: () => {
            isUploading.value = false;
        }
    });
};

const paginatedPayments = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.payments.slice(start, end);
});

// Reset page on filter update
watch(() => [searchQuery.value, selectedStatus.value, selectedMonth.value, selectedYear.value, selectedBillingCycle.value, selectedCustomer.value, selectedSubCustomer.value, selectedRepresentative.value], () => {
    currentPage.value = 1;
});

// Keep the ?page= query param in sync with client-side pagination
watch(currentPage, (newPage) => {
    syncPageInUrl(newPage);
});

let filterTimeout = null;

const triggerFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get('/payments', {
            search: searchQuery.value,
            status: selectedStatus.value,
            filter_month: selectedMonth.value,
            filter_year: selectedYear.value,
            billing_cycle: selectedBillingCycle.value,
            customer_id: selectedCustomer.value,
            sub_customer_id: selectedSubCustomer.value,
            closed_by: selectedRepresentative.value,
        }, {
            preserveState: true, preserveScroll: true, replace: true
        });
    }, 250);
};

const filterFailedOnly = () => {
    selectedStatus.value = 'failed';
    triggerFilter();
};

const dispatchRefund = (id) => {
    if (confirm('Authorize direct gateway API instructions to reverse this deposit?')) {
        router.post(`/payments/${id}/refund`, {}, { preserveScroll: true });
    }
};

const getMethodIconClass = (type) => {
    switch (type) {
        case 'mastercard': return 'fab fa-cc-mastercard text-rose-600';
        case 'stripe': return 'fab fa-cc-stripe text-indigo-600';
        case 'paypal': return 'fab fa-paypal text-sky-500';
        default: return 'fab fa-cc-visa text-blue-600';
    }
};

const deletePayment = (id) => {
    if (confirm('Permanently wipe this ledger sequence entry?')) {
        router.delete(`/payments/${id}`, { preserveScroll: true });
    }
};

const handleExport = () => {
    const columnMap = {
        'transaction_id': 'Transaction Key',
        'invoice_ref': 'Invoice Ref',
        'payment_date': 'Payment Date',
        'end_date': 'End Date',
        'customer_name': 'Client',
        'sub_customer_name': 'Sub-Customer',
        'amount': 'Authorized Sum',
        'billing_cycle': 'Billing Cycle',
        'status': 'Status'
    };
    exportToExcel(props.payments, 'Payments_History', columnMap);
};
</script>
