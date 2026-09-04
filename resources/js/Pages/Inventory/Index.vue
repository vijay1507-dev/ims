<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Inventory & Asset Tracking</h2>
                </div>
                <div class="flex items-center space-x-3">
                    <button 
                        v-if="$page.props.auth.user.permissions.includes('bulk-delete-inventory')"
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
                        <i class="fas fa-file-excel mr-2 text-indigo-600"></i>Export Inventory
                    </button>
                    <Link
                        href="/inventory/create"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-bold shadow-xs transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-plus mr-2"></i>Add Inventory
                    </Link>
                </div>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Assets -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Inventory</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.total_assets }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-cubes text-blue-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Active Devices -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Active Devices</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ metrics.active_devices }}</p>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-check-circle text-emerald-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Under Maintenance -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">In Maintenance</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ metrics.under_maintenance }}</p>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-tools text-amber-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Expiring Soon -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Warranty Expiring</p>
                        <p class="text-2xl font-bold text-rose-600 mt-1">{{ metrics.expiring_soon }}</p>
                    </div>
                    <div class="bg-rose-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-exclamation-triangle text-rose-600 text-xl w-5 text-center"></i>
                    </div>
                </div>
            </div>

            <!-- Asset Categories Sub-grid (Matching Checklist Vectors Exactly) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <!-- Kiosks -->
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-2xs cursor-pointer hover:border-indigo-300 transition-all" @click="filterByCategory('Kiosk Terminals')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="bg-indigo-100 p-2.5 rounded-lg shrink-0">
                            <i class="fas fa-cash-register text-indigo-600 text-lg w-5 text-center"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ categories.kiosks }}</span>
                    </div>
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Kiosks</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Assigned touch kiosks</p>
                </div>

                <!-- TVs -->
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-2xs cursor-pointer hover:border-purple-300 transition-all" @click="filterByCategory('Smart TVs')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="bg-purple-100 p-2.5 rounded-lg shrink-0">
                            <i class="fas fa-tv text-purple-600 text-lg w-5 text-center"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ categories.tvs }}</span>
                    </div>
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">TVs</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Smart display matrices</p>
                </div>

                <!-- Tablets -->
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-2xs cursor-pointer hover:border-emerald-300 transition-all" @click="filterByCategory('Tablet Devices')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="bg-emerald-100 p-2.5 rounded-lg shrink-0">
                            <i class="fas fa-tablet-alt text-emerald-600 text-lg w-5 text-center"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ categories.tablets }}</span>
                    </div>
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Tablets</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Mobile input units</p>
                </div>

                <!-- Printers -->
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-2xs cursor-pointer hover:border-rose-300 transition-all" @click="filterByCategory('Network Printers')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="bg-rose-100 p-2.5 rounded-lg shrink-0">
                            <i class="fas fa-print text-rose-600 text-lg w-5 text-center"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ categories.printers }}</span>
                    </div>
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Printers</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Network receipt drums</p>
                </div>

                <!-- License Keys -->
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-2xs cursor-pointer hover:border-amber-300 transition-all" @click="filterByCategory('License Keys')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="bg-amber-100 p-2.5 rounded-lg shrink-0">
                            <i class="fas fa-key text-amber-600 text-lg w-5 text-center"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">{{ categories.licenses }}</span>
                    </div>
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">License Keys</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Software digital serials</p>
                </div>
            </div>

            <!-- Integrated Data Table with Live Filters -->
            <DataTable
                :columns="[
                    { label: 'Asset ID', key: 'asset_id' },
                    { label: 'Hardware Type', key: 'type_name' },
                    { label: 'Name / Model', key: 'name_model' },
                    { label: 'Assigned Entity', key: 'customer_name' },
                    { label: 'Serial / License Key', key: 'serial_license' },
                    { label: 'Warranty Expiry', key: 'warranty_expiry' },
                    { label: 'Device Status', key: 'status' },
                    { label: 'Serials Operations', key: 'actions', align: 'right' }
                ]"
                :has-rows="inventories.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="inventories.length"
            >
                <template #filters>
                    <!-- Real-time Text Search input -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search asset, key or client..."
                            class="w-48 sm:w-64 pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                            @input="triggerFilter"
                        >
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400"></i>
                    </div>

                    <!-- Filter by Category -->
                    <select
                        v-model="selectedCategory"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700"
                        @change="triggerFilter"
                    >
                        <option>All Categories</option>
                        <option>Kiosk Terminals</option>
                        <option>Smart TVs</option>
                        <option>Tablet Devices</option>
                        <option>Network Printers</option>
                        <option>License Keys</option>
                    </select>

                    <!-- Filter by Status -->
                    <select
                        v-model="selectedStatus"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold"
                        @change="triggerFilter"
                    >
                        <option>All Status</option>
                        <option value="active" class="text-emerald-600">Active</option>
                        <option value="maintenance" class="text-amber-600">Maintenance</option>
                        <option value="inactive" class="text-rose-600">Inactive</option>
                        <option value="retired" class="text-gray-600">Retired</option>
                    </select>
                </template>

                <template #rows>
                    <tr
                        v-for="ast in paginatedInventories"
                        :key="ast.id"
                        class="table-row hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-indigo-600">
                            {{ ast.asset_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center text-xs font-bold text-gray-800">
                                <i :class="getTypeIconClass(ast.category)" class="mr-2.5 text-sm"></i>
                                {{ ast.type_name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-900">
                            {{ ast.name_model }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs font-bold text-indigo-950">{{ ast.customer_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-amber-800 font-bold bg-amber-50/40 rounded px-1.5 py-0.5 inline-block mt-2.5">
                            {{ ast.serial_license || 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-rose-700">
                            {{ ast.warranty_expiry }}
                        </td>


                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                                :class="{
                                    'bg-emerald-100 text-emerald-800': ast.status === 'active',
                                    'bg-amber-100 text-amber-800': ast.status === 'maintenance',
                                    'bg-rose-100 text-rose-800': ast.status === 'inactive',
                                    'bg-gray-100 text-gray-800': ast.status === 'retired'
                                }"
                            >
                                {{ ast.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                            <button
                                type="button"
                                class="text-indigo-600 hover:text-indigo-900 mr-2 p-1.5 bg-indigo-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Schedule Maintenance"
                                @click="openMaintenanceModal(ast)"
                            >
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                            <button
                                type="button"
                                class="text-blue-600 hover:text-blue-900 mr-2 p-1.5 bg-blue-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Replacement History"
                                @click="openHistoryModal(ast)"
                            >
                                <i class="fas fa-history"></i>
                            </button>
                            <Link
                                :href="`/inventory/${ast.id}/edit?page=${currentPage}`"
                                class="text-amber-600 hover:text-amber-900 mr-2 p-1.5 bg-amber-50 rounded-lg transition-colors inline-flex items-center justify-center"
                                title="Edit Asset"
                            >
                                <i class="fas fa-edit"></i>
                            </Link>
                            <button
                                type="button"
                                class="text-rose-600 hover:text-rose-900 p-1.5 bg-rose-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                title="Delete Asset"
                                @click="deleteAsset(ast.id)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </DataTable>

            <!-- Maintenance Scheduling Modal -->
            <div v-if="showMaintenanceModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <!-- Backdrop overlay -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showMaintenanceModal = false"></div>
                
                <!-- Modal Box -->
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-200 animate-fade-in">
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i class="fas fa-calendar-plus text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Schedule Maintenance</h3>
                                <p class="text-xs text-gray-500">Asset: <span class="font-mono font-bold text-indigo-600">{{ selectedAsset?.asset_id }}</span></p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1.5">Preferred Date</label>
                                <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1.5">Service Priority</label>
                                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer transition-all">
                                    <option>Standard Maintenance</option>
                                    <option>Critical Repair</option>
                                    <option>Hardware Upgrade</option>
                                    <option>Firmware Patch</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1.5">Operational Notes</label>
                                <textarea rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white resize-none transition-all" placeholder="Enter service requirements or specific hardware concerns..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition-colors" @click="showMaintenanceModal = false">
                            Cancel
                        </button>
                        <button type="button" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-sm transition-all" @click="showMaintenanceModal = false">
                            Confirm Schedule
                        </button>
                    </div>
                </div>
            </div>

            <!-- Replacement History Modal -->
            <div v-if="showHistoryModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showHistoryModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-200 animate-fade-in">
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fas fa-history text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Replacement History</h3>
                                <p class="text-xs text-gray-500">Lifecycle Audit for <span class="font-mono font-bold text-indigo-600">{{ selectedAsset?.asset_id }}</span></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 h-2 w-2 rounded-full bg-blue-500 shrink-0"></div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Initial Deployment</p>
                                        <p class="text-[11px] text-gray-600 mt-0.5">Original hardware baseline configured and assigned to client site.</p>
                                        <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-wider">{{ selectedAsset?.assigned_date }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="selectedAsset?.replacement_history" class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 h-2 w-2 rounded-full bg-blue-600 shrink-0"></div>
                                    <div>
                                        <p class="text-xs font-bold text-blue-900">Service Record</p>
                                        <p class="text-[11px] text-blue-800 mt-0.5">{{ selectedAsset.replacement_history }}</p>
                                        <p class="text-[10px] font-bold text-blue-400 mt-1 uppercase tracking-wider text-right italic">Logged Event</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-6">
                                <p class="text-xs text-gray-400 italic">No replacement events recorded for this asset identifier.</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                        <button type="button" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 shadow-sm transition-all" @click="showHistoryModal = false">
                            Close Audit
                        </button>
                    </div>
                </div>
            </div>

            <BulkDeleteModal
                :show="showBulkDeleteModal"
                module="inventory"
                module-name="Inventory"
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
import BulkDeleteModal from '../../Components/BulkDeleteModal.vue';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    categories: {
        type: Object,
        required: true,
    },
    inventories: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || 'All Categories');
const selectedStatus = ref(props.filters.status || 'All Status');
const showMaintenanceModal = ref(false);
const showHistoryModal = ref(false);
const selectedAsset = ref(null);

// Client Pagination States
const currentPage = ref(props.filters.page ? parseInt(props.filters.page) : 1);
const itemsPerPage = ref(10);
const showBulkDeleteModal = ref(false);

const paginatedInventories = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.inventories.slice(start, end);
});

// Reset page on filter update
watch(() => [searchQuery.value, selectedCategory.value, selectedStatus.value], () => {
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

const openMaintenanceModal = (ast) => {
    selectedAsset.value = ast;
    showMaintenanceModal.value = true;
};

const openHistoryModal = (ast) => {
    selectedAsset.value = ast;
    showHistoryModal.value = true;
};

let filterTimeout = null;

const triggerFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get('/inventory', {
            search: searchQuery.value,
            category: selectedCategory.value,
            status: selectedStatus.value,
        }, {
            preserveState: true, preserveScroll: true, replace: true
        });
    }, 250);
};

const filterByCategory = (catName) => {
    selectedCategory.value = catName;
    triggerFilter();
};

const getTypeIconClass = (cat) => {
    switch (cat) {
        case 'tv': return 'fas fa-tv text-purple-600';
        case 'tablet': return 'fas fa-tablet-alt text-emerald-600';
        case 'license': return 'fas fa-key text-amber-600';
        case 'printer': return 'fas fa-print text-rose-600';
        case 'kiosk': return 'fas fa-cash-register text-indigo-600';
        default: return 'fas fa-microchip text-blue-600';
    }
};

const deleteAsset = (id) => {
    if (confirm('Authorize permanent de-provisioning of this serialized physical hardware record?')) {
        router.delete(`/inventory/${id}`, { preserveScroll: true });
    }
};

const handleExport = () => {
    const columnMap = {
        'asset_id': 'Asset ID',
        'type_name': 'Hardware Type',
        'name_model': 'Name / Model',
        'customer_name': 'Assigned Entity',
        'serial_license': 'Serial / License Key',
        'warranty_expiry': 'Warranty Expiry',
        'maintenance_schedule': 'Maintenance Schedule',
        'replacement_history': 'Replacement History',
        'status': 'Status'
    };
    exportToExcel(props.inventories, 'Inventory_Assets', columnMap);
};
</script>
