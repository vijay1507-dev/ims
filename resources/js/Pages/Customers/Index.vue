<template>
    <AppLayout>
        <div class="p-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Customer Management</h2>
                    <p class="text-gray-600 mt-1">Manage client accounts and status</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button 
                        v-if="$page.props.auth.user.permissions.includes('bulk-delete-customers')"
                        @click="showBulkDeleteModal = true"
                        type="button" 
                        class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium transition-colors inline-flex items-center cursor-pointer"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>Bulk Delete
                    </button>
                    <button 
                        @click="showImportModal = true"
                        type="button" 
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-upload mr-2 text-indigo-600"></i>Import Excel
                    </button>
                    <button 
                        @click="handleExport"
                        type="button" 
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <!-- Dedicated multi-page SPA link -->
                    <Link
                        href="/customers/create"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-plus mr-2"></i>Add Customer
                    </Link>
                </div>
            </div>

            <!-- Upper Metric Counters Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <KpiCard
                    title="Total Customers"
                    :value="metrics.total_customers"
                    icon="fas fa-users"
                    icon-bg-class="bg-indigo-100"
                    icon-color-class="text-indigo-600"
                />
                <KpiCard
                    title="Active Accounts"
                    :value="metrics.active_accounts"
                    icon="fas fa-check-circle"
                    icon-bg-class="bg-green-100"
                    icon-color-class="text-green-600"
                />
                <KpiCard
                    title="Trial Accounts"
                    :value="metrics.trial_accounts"
                    icon="fas fa-clock"
                    icon-bg-class="bg-yellow-100"
                    icon-color-class="text-yellow-600"
                />
                <KpiCard
                    title="Inactive Accounts"
                    :value="metrics.inactive_accounts"
                    icon="fas fa-times-circle"
                    icon-bg-class="bg-red-100"
                    icon-color-class="text-red-600"
                />
            </div>

            <!-- Integrated Data Table with Live Filters -->
            <DataTable
                title="Customer List"
                :columns="[
                    { label: 'Full Name', key: 'contact_name' },
                    { label: 'Company', key: 'name' },
                    { label: 'Email', key: 'email' },
                    { label: 'Phone', key: 'phone' },
                    { label: 'Role', key: 'role' },
                    { label: 'Sub-Customers', key: 'sub_customers_count' },
                    { label: 'Status', key: 'status' },
                    { label: 'Actions', key: 'actions', align: 'right' }
                ]"
                :has-rows="customers.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="customers.length"
            >
                <template #filters>
                    <!-- Real-time Text Search input -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search name or email..."
                            class="w-48 sm:w-64 pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                            @input="triggerFilter"
                        >
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400"></i>
                    </div>

                    <!-- Filter by Status -->
                    <select
                        v-model="selectedStatus"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
                        @change="triggerFilter"
                    >
                        <option>All Status</option>
                        <option value="active">Active</option>
                        <option value="trial">Trial</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <!-- Filter by Assigned Plan -->
                    <select
                        v-model="selectedPlan"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer"
                        @change="triggerFilter"
                    >
                        <option>All Plans</option>
                        <option v-for="plan in plans" :key="plan" :value="plan">{{ plan }}</option>
                    </select>
                </template>

                <template #rows>
                    <tr
                        v-for="customer in paginatedCustomers"
                        :key="customer.id"
                        class="table-row hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ customer.contact_name || 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ customer.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ customer.email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ customer.phone || 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ customer.role || 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <Link
                                v-if="customer.sub_customers_count > 0"
                                :href="`/customers/${customer.id}`"
                                class="inline-flex items-center justify-center min-w-6 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold"
                            >{{ customer.sub_customers_count }}</Link>
                            <span v-else class="text-gray-400 text-xs">—</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <StatusBadge :status="customer.status" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link
                                :href="`/customers/${customer.id}`"
                                class="text-indigo-600 hover:text-indigo-900 mr-3 p-1.5 bg-indigo-50 rounded-lg transition-colors inline-flex items-center justify-center"
                                title="View Profile"
                            >
                                <i class="fas fa-eye"></i>
                            </Link>
                            <Link
                                :href="`/customers/${customer.id}/edit?page=${currentPage}`"
                                class="text-amber-600 hover:text-amber-900 mr-3 p-1.5 bg-amber-50 rounded-lg transition-colors inline-flex items-center justify-center"
                                title="Edit"
                            >
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button
                                type="button"
                                class="text-red-600 hover:text-red-900 p-1.5 bg-red-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Delete"
                                @click="deleteCustomer(customer.id)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </DataTable>
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

                        <!-- Drag and Drop Area -->
                        <div 
                            @click="openFileSelector"
                            class="border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded-lg p-8 text-center cursor-pointer transition-colors bg-gray-50/50 hover:bg-indigo-50/10 flex flex-col items-center justify-center"
                        >
                            <i class="fas fa-file-excel text-4xl text-green-600 mb-3"></i>
                            <span class="text-sm font-medium text-gray-700">
                                {{ selectedFile ? selectedFile.name : 'Click to select Excel file' }}
                            </span>
                            <span class="text-xs text-gray-400 mt-1">
                                Supports .xlsx spreadsheet files
                            </span>
                            <input 
                                ref="fileInput" 
                                type="file" 
                                class="hidden" 
                                accept=".xlsx"
                                @change="handleFileChange"
                            />
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end space-x-3 bg-gray-50">
                        <button 
                            @click="showImportModal = false" 
                            type="button" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                            :disabled="isUploading"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="submitImport" 
                            type="button" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center"
                            :disabled="!selectedFile || isUploading"
                        >
                            <span v-if="isUploading">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Importing...
                            </span>
                            <span v-else>
                                Start Import
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            
            <BulkDeleteModal
                :show="showBulkDeleteModal"
                module="customers"
                module-name="Customers"
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
    customers: {
        type: Array,
        required: true,
    },
    plans: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

// Live Reactive Filters bound from server load states
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'All Status');
const selectedPlan = ref(props.filters.plan || 'All Plans');

// Client Pagination States
const currentPage = ref(props.filters.page ? parseInt(props.filters.page) : 1);
const itemsPerPage = ref(10);
const showBulkDeleteModal = ref(false);

const paginatedCustomers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.customers.slice(start, end);
});

// Reset page on filter update
watch(() => [searchQuery.value, selectedStatus.value, selectedPlan.value], () => {
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
        router.get('/customers', {
            search: searchQuery.value,
            status: selectedStatus.value,
            plan: selectedPlan.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 250); // Fluid debounce trigger layer
};

const deleteCustomer = (id) => {
    if (confirm('Are you sure you want to permanently delete this customer account?')) {
        router.delete(`/customers/${id}`, {
            preserveScroll: true,
        });
    }
};

const handleExport = () => {
    const columnMap = {
        'name': 'Customer Name',
        'email': 'Email Address',
        'phone': 'Contact Number',
        'plan': 'Active Plan',
        'status': 'Account Status',
        'mrr': 'MRR Yield',
        'joined_date': 'Onboarding Date'
    };
    exportToExcel(props.customers, 'Customers_List', columnMap);
};

// Excel Import States and Handlers
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
    formData.append('import_context', 'customers');

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
</script>
