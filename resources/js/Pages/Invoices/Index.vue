<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Invoice Management</h2>
                    <p class="text-gray-600 mt-1">Generate, track, and disburse official customer taxation documents</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button 
                        v-if="$page.props.auth.user.permissions.includes('bulk-delete-invoices')"
                        @click="showBulkDeleteModal = true"
                        type="button" 
                        class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm font-bold transition-colors inline-flex items-center shadow-2xs cursor-pointer"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>Bulk Delete
                    </button>
                    <button 
                        @click="handleExport"
                        type="button" 
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-bold transition-colors inline-flex items-center shadow-2xs cursor-pointer"
                    >
                        <i class="fas fa-file-excel mr-2 text-emerald-600"></i>Export Invoices
                    </button>
                    <!-- SPA Navigation route target -->
                    
                </div>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Invoices -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Invoices</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.total_invoices }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-file-invoice text-blue-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Paid Invoices -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Paid Invoices</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ metrics.paid_invoices }}</p>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-check-circle text-emerald-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Pending Invoices -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Settlement</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ metrics.pending_invoices }}</p>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-clock text-amber-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Overdue Invoices -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Overdue Clearings</p>
                        <p class="text-2xl font-bold text-rose-600 mt-1">{{ metrics.overdue_invoices }}</p>
                    </div>
                    <div class="bg-rose-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-exclamation-triangle text-rose-600 text-xl w-5 text-center"></i>
                    </div>
                </div>
            </div>

            <!-- Integrated Data Table with Live Filters -->
            <DataTable
                title="Tax / VAT Authorized Invoices"
                :columns="[
                    { label: 'Invoice #', key: 'invoice_number' },
                    { label: 'Issued Date', key: 'invoice_date' },
                    { label: 'Customer', key: 'customer_name' },
                    { label: 'Billing Address', key: 'billing_address' },
                    { label: 'Billing Cycle', key: 'billing_cycle' },
                    { label: 'Amount', key: 'usd_amount' },
                    { label: 'Subtotal', key: 'amount' },
                    { label: 'Tax/VAT Sum', key: 'tax' },
                    { label: 'Due Date', key: 'due_date' },
                    { label: 'Status Marker', key: 'status' },
                    { label: 'Closed By', key: 'closed_by' },
                    { label: 'Printable Formats & Edits', key: 'actions', align: 'right' }
                ]"
                :has-rows="invoices.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="invoices.length"
            >
                <template #filters>
                    <!-- Real-time Text Search input -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search invoice or client..."
                            class="w-48 sm:w-64 pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                            @input="triggerFilter"
                        >
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400"></i>
                    </div>

                    <!-- Filter by Status -->
                    <select
                        v-model="selectedStatus"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold animate-fade-in"
                        @change="triggerFilter"
                    >
                        <option>All Status</option>
                        <option value="paid" class="text-emerald-600">Paid</option>
                        <option value="pending" class="text-amber-600">Pending</option>
                        <option value="overdue" class="text-rose-600">Overdue</option>
                        <option value="draft" class="text-gray-600">Draft</option>
                    </select>

                    <!-- Filter by Month -->
                    <select
                        v-model="selectedMonth"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700 shadow-2xs"
                        @change="triggerFilter"
                    >
                        <option v-for="m in availableMonths" :key="m" :value="m">{{ m }}</option>
                    </select>

                    <!-- Filter by Year -->
                    <select
                        v-model="selectedYear"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700 shadow-2xs"
                        @change="triggerFilter"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>

                    <!-- Filter by Billing Cycle -->
                    <select
                        v-model="selectedCycle"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700 shadow-2xs"
                        @change="triggerFilter"
                    >
                        <option v-for="c in availableCycles" :key="c" :value="c">{{ c }}</option>
                    </select>
                </template>

                <template #rows>
                    <tr
                        v-for="inv in paginatedInvoices"
                        :key="inv.id"
                        class="table-row hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-indigo-600">
                            {{ inv.invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 font-medium">
                            {{ inv.invoice_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs font-bold text-gray-900">{{ inv.customer_name }}</div>
                        </td>
                        <!-- Persistent Billing Address display column -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 italic max-w-xs truncate" :title="inv.billing_address">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>{{ inv.billing_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-indigo-50 text-indigo-700 uppercase tracking-wider">
                                {{ formatBillingCycle(inv.billing_cycle) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-indigo-900">
                            ${{ parseFloat(inv.usd_amount).toFixed(2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-800">
                            {{ inv.amount }} <span class="text-[9px] text-gray-500 font-semibold uppercase">{{ inv.currency }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-amber-700 font-medium">
                            {{ inv.tax }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-rose-700 font-bold">
                            {{ inv.due_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                                :class="{
                                    'bg-emerald-100 text-emerald-800': inv.status === 'paid',
                                    'bg-amber-100 text-amber-800': inv.status === 'pending',
                                    'bg-rose-100 text-rose-800': inv.status === 'overdue',
                                    'bg-gray-100 text-gray-800': inv.status === 'draft'
                                }"
                            >
                                {{ inv.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700">
                            {{ inv.closed_by || '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                            <button
                                @click="generateInvoicePDF(inv)"
                                type="button"
                                class="text-emerald-600 hover:text-emerald-900 mr-2 p-1.5 bg-emerald-50 rounded-lg transition-colors inline-flex items-center justify-center font-bold cursor-pointer"
                                title="Download PDF"
                            >
                                <i class="fas fa-file-pdf"></i>
                            </button>

                            <Link
                                :href="`/invoices/${inv.id}/edit?page=${currentPage}`"
                                class="text-indigo-600 hover:text-indigo-900 mr-2 p-1.5 bg-indigo-50 rounded-lg transition-colors inline-flex items-center justify-center"
                                title="Edit Invoice"
                            >
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button
                                type="button"
                                class="text-red-600 hover:text-red-900 p-1.5 bg-red-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Revoke Invoice"
                                @click="deleteInvoice(inv.id)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </DataTable>
            
            <BulkDeleteModal
                :show="showBulkDeleteModal"
                module="invoices"
                module-name="Invoices"
                @close="showBulkDeleteModal = false"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import DataTable from '../../Components/Tables/DataTable.vue';
import { exportToExcel } from '../../Utils/export';
import { generateInvoicePDF } from '../../Utils/pdf';
import BulkDeleteModal from '../../Components/BulkDeleteModal.vue';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    invoices: {
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
    availableCycles: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'All Status');
const selectedMonth = ref(props.filters.filter_month || 'All Months');
const selectedYear = ref(props.filters.filter_year || 'All Years');
const selectedCycle = ref(props.filters.billing_cycle || 'All Cycles');

// Client Pagination States
const currentPage = ref(props.filters.page ? parseInt(props.filters.page) : 1);
const itemsPerPage = ref(10);
const showBulkDeleteModal = ref(false);

const paginatedInvoices = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.invoices.slice(start, end);
});

// Reset page on filter update
watch(() => [searchQuery.value, selectedStatus.value, selectedMonth.value, selectedYear.value, selectedCycle.value], () => {
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
        router.get('/invoices', {
            search: searchQuery.value,
            status: selectedStatus.value,
            filter_month: selectedMonth.value,
            filter_year: selectedYear.value,
            billing_cycle: selectedCycle.value,
        }, {
            preserveState: true, preserveScroll: true, replace: true
        });
    }, 250);
};

const deleteInvoice = (id) => {
    if (confirm('Permanently wipe and revoke this tax ledger record?')) {
        router.delete(`/invoices/${id}`, { preserveScroll: true });
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
        'invoice_number': 'Invoice #',
        'invoice_date': 'Issued Date',
        'customer_name': 'Customer',
        'billing_address': 'Billing Address',
        'billing_cycle': 'Billing Cycle',
        'amount': 'Subtotal',
        'tax': 'Tax Sum',
        'total': 'Aggregate Due',
        'due_date': 'Due Date',
        'status': 'Status'
    };
    exportToExcel(props.invoices, 'Invoices_Export', columnMap);
};
</script>
