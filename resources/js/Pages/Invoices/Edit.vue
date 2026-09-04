<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Breadcrumb Navigation Header -->
            <div class="mb-6">
                <Link :href="`/invoices?page=${returnPage}`" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to Invoices Dashboard
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Modify Client Invoice</h2>
                <p class="text-gray-600 mt-1">Update parameters or operational life cycle states for {{ invoice.invoice_number }}.</p>
            </div>

            <!-- Main Form Card Container -->
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm">
                    <div class="p-6 sm:p-8 space-y-6">
                        <!-- Identity Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Billing Identity</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Target organization customer parameters.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Customer / Organization Name</label>
                                    <input
                                        v-model="form.customer_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                    >
                                    <span v-if="form.errors.customer_name" class="text-xs text-red-500 mt-1 block">{{ form.errors.customer_name }}</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Billing Cycle</label>
                                    <select
                                        v-model="form.billing_cycle"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium"
                                    >
                                        <option v-for="cycle in $page.props.activeBillingCycles" :key="cycle.code" :value="cycle.code">
                                            {{ cycle.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Amounts Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Charges & Taxation Strings</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Subtotal values and configured tax margins.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Currency</label>
                                    <select
                                        v-model="form.currency"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium"
                                    >
                                        <option value="SGD">SGD (S$)</option>
                                        <option value="USD">USD ($)</option>
                                        <option value="INR">INR (₹)</option>
                                        <option value="AED">AED (د.إ)</option>
                                        <option value="EUR">EUR (€)</option>
                                        <option value="GBP">GBP (£)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Base Subtotal</label>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium text-gray-900"
                                    >
                                    <span v-if="form.errors.amount" class="text-xs text-red-500 mt-1 block">{{ form.errors.amount }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">USD Amount ($)</label>
                                    <input
                                        v-model="form.usd_amount"
                                        type="number"
                                        step="0.01"
                                        readonly
                                        disabled
                                        class="w-full px-4 py-2.5 border border-gray-300 bg-gray-50 text-gray-500 rounded-lg text-sm outline-none font-bold"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Assigned Tax ($)</label>
                                    <input
                                        v-model="form.tax"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.tax" class="text-xs text-red-500 mt-1 block">{{ form.errors.tax }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Invoice Date</label>
                                    <input
                                        v-model="form.invoice_date"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Due Date</label>
                                    <input
                                        v-model="form.due_date"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tracking Status</label>
                                    <select
                                        v-model="form.status"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option value="paid">Paid</option>
                                        <option value="pending">Pending</option>
                                        <option value="overdue">Overdue</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Closed By</label>
                                    <input
                                        v-model="form.closed_by"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Submission Action Bar -->
                    <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <Link
                            :href="`/invoices?page=${returnPage}`"
                            class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-bold transition-colors inline-flex items-center"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm disabled:opacity-50 transition-colors inline-flex items-center"
                        >
                            <span v-if="form.processing" class="inline-block animate-spin mr-2 border-2 border-white border-t-transparent h-4 w-4 rounded-full"></span>
                            Save Modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
    returnPage: {
        type: Number,
        default: 1,
    },
});

const form = useForm({
    customer_name: props.invoice.customer_name,
    amount: props.invoice.amount,
    tax: props.invoice.tax,
    status: props.invoice.status,
    invoice_date: props.invoice.invoice_date || '',
    due_date: props.invoice.due_date || '',
    billing_cycle: props.invoice.billing_cycle || 'monthly',
    currency: props.invoice.currency || 'USD',
    usd_amount: props.invoice.usd_amount || 0.00,
    closed_by: props.invoice.closed_by || '',
});

const exchangeRates = {
    SGD: 0.74,
    USD: 1.00,
    INR: 0.012,
    AED: 0.27,
    EUR: 1.10,
    GBP: 1.30
};

watch(() => [form.amount, form.currency], () => {
    const rate = exchangeRates[form.currency] || 1.00;
    const amountVal = parseFloat(form.amount) || 0;
    form.usd_amount = parseFloat((amountVal * rate).toFixed(2));
}, { immediate: true });

// Dynamic Tax Calculation: Automatically update tax to 10% of subtotal
watch(() => form.amount, (newVal) => {
    form.tax = (parseFloat(newVal || 0) * 0.10).toFixed(2);
});

const submitForm = () => {
    form.put(`/invoices/${props.invoice.id}?page=${props.returnPage}`);
};
</script>
