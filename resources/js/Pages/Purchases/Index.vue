<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Purchases</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage vendor stock orders and track incoming assets</p>
                </div>
                <div>
                    <Link
                        href="/purchases/create"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-plus mr-2"></i>Create Purchase Order
                    </Link>
                </div>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Spent -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Purchases</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">${{ metrics.total_spent }}</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-dollar-sign text-indigo-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Received -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Received Purchases</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ metrics.received_count }}</p>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-box-open text-emerald-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Ordered -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ordered Purchases</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ metrics.ordered_count }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-paper-plane text-blue-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Pending -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Purchases</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ metrics.pending_count }}</p>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-clock text-amber-600 text-xl w-5 text-center"></i>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative max-w-md w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        v-model="filterForm.search"
                        type="text"
                        placeholder="Search PO number or supplier..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        @input="debounceSearch"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Status Filter -->
                    <select
                        v-model="filterForm.status"
                        class="border border-gray-300 rounded-lg text-sm px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                        @change="submitFilters"
                    >
                        <option value="All Statuses">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Ordered">Ordered</option>
                        <option value="Received">Received</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors border border-gray-300"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table of Purchases -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">PO Number</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Payment Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Total Amount</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150">
                            <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold whitespace-nowrap">{{ purchase.purchase_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">{{ purchase.supplier_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ purchase.purchase_date }}</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center border',
                                            purchase.status === 'Received' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                            purchase.status === 'Ordered' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                            purchase.status === 'Cancelled' ? 'bg-rose-50 text-rose-700 border-rose-200' :
                                            'bg-amber-50 text-amber-700 border-amber-200'
                                        ]"
                                    >
                                        {{ purchase.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center border',
                                            purchase.payment_status === 'Paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                            purchase.payment_status === 'Partially paid' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                                            'bg-rose-50 text-rose-700 border-rose-200'
                                        ]"
                                    >
                                        {{ purchase.payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right font-bold whitespace-nowrap">${{ purchase.total_amount.toFixed(2) }}</td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <div class="inline-flex space-x-2">
                                        <Link
                                            :href="`/purchases/${purchase.id}/edit`"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button
                                            @click="deletePurchase(purchase.id)"
                                            class="p-1.5 text-gray-400 hover:text-rose-600 transition-colors cursor-pointer"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="purchases.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3 block"></i>
                                    No purchase orders placed yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    purchases: Array,
    metrics: Object,
    filters: Object,
});

const filterForm = ref({
    search: props.filters.search,
    status: props.filters.status,
});

let debounceTimeout = null;

const debounceSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        submitFilters();
    }, 400);
};

const submitFilters = () => {
    router.get('/purchases', {
        search: filterForm.value.search,
        status: filterForm.value.status,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.value.search = '';
    filterForm.value.status = 'All Statuses';
    submitFilters();
};

const deletePurchase = (id) => {
    if (confirm('Are you sure you want to delete this purchase order?')) {
        router.delete(`/purchases/${id}`);
    }
};
</script>
