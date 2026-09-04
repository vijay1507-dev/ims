<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Expenses</h2>
                    <p class="text-sm text-gray-500 mt-1">Monitor and categorize your direct and indirect business operational costs</p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        href="/expenses/create"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-plus mr-2"></i>Log Expense
                    </Link>
                </div>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Expenses -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Expenses</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">${{ metrics.total }}</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-receipt text-indigo-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Direct Expenses -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Direct Expenses</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">${{ metrics.direct }}</p>
                        <span class="text-[11px] text-gray-500 mt-1 block">{{ metrics.direct_percentage }}% of total</span>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-arrow-trend-down text-emerald-600 text-xl w-5 text-center"></i>
                    </div>
                </div>

                <!-- Indirect Expenses -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Indirect Expenses</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">${{ metrics.indirect }}</p>
                        <span class="text-[11px] text-gray-500 mt-1 block">{{ metrics.indirect_percentage }}% of total</span>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-lg shrink-0">
                        <i class="fas fa-arrow-trend-up text-amber-600 text-xl w-5 text-center"></i>
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
                        placeholder="Search by title, category, ref..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        @input="debounceSearch"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Type Filter -->
                    <select
                        v-model="filterForm.type"
                        class="border border-gray-300 rounded-lg text-sm px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                        @change="submitFilters"
                    >
                        <option value="All Types">All Types</option>
                        <option value="Direct">Direct</option>
                        <option value="Indirect">Indirect</option>
                    </select>

                    <!-- Month Filter -->
                    <select
                        v-model="filterForm.month"
                        class="border border-gray-300 rounded-lg text-sm px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                        @change="submitFilters"
                    >
                        <option value="All Months">All Months</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>

                    <!-- Year Filter -->
                    <select
                        v-model="filterForm.year"
                        class="border border-gray-300 rounded-lg text-sm px-3 py-2 bg-white focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                        @change="submitFilters"
                    >
                        <option value="All Years">All Years</option>
                        <option v-for="yr in available_years" :key="yr" :value="yr">{{ yr }}</option>
                    </select>
                    
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors border border-gray-300"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table of Expenses -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Expense Date</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Ref Number</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Amount</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150">
                            <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium whitespace-nowrap">{{ expense.expense_date }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div>
                                        <p class="font-semibold">{{ expense.title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ expense.description }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center',
                                            expense.type === 'Direct' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                                        ]"
                                    >
                                        {{ expense.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ expense.category }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ expense.reference_number || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right font-bold whitespace-nowrap">${{ expense.amount.toFixed(2) }}</td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <div class="inline-flex space-x-2">
                                        <button
                                            @click="generateExpensePDF(expense)"
                                            type="button"
                                            class="text-emerald-600 hover:text-emerald-900 p-1.5 bg-emerald-50 rounded-lg transition-colors inline-flex items-center justify-center font-bold cursor-pointer"
                                            title="Download PDF"
                                        >
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                        <Link
                                            :href="`/expenses/${expense.id}/edit`"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button
                                            @click="deleteExpense(expense.id)"
                                            class="p-1.5 text-gray-400 hover:text-rose-600 transition-colors cursor-pointer"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="expenses.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
                                    No expenses logged yet.
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
import { generateExpensePDF } from '../../Utils/pdf';

const props = defineProps({
    expenses: Array,
    metrics: Object,
    filters: Object,
    available_years: Array,
});

const filterForm = ref({
    search: props.filters.search,
    type: props.filters.type,
    month: props.filters.month,
    year: props.filters.year,
});

let debounceTimeout = null;

const debounceSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        submitFilters();
    }, 400);
};

const submitFilters = () => {
    router.get('/expenses', {
        search: filterForm.value.search,
        type: filterForm.value.type,
        month: filterForm.value.month,
        year: filterForm.value.year,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.value.search = '';
    filterForm.value.type = 'All Types';
    filterForm.value.month = 'All Months';
    filterForm.value.year = 'All Years';
    submitFilters();
};

const deleteExpense = (id) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/expenses/${id}`);
    }
};
</script>
