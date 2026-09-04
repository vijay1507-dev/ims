<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Breadcrumb Navigation Header -->
            <div class="mb-6">
                <Link :href="`/payments?page=${returnPage}`" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to Payments Dashboard
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Modify Payment Transaction</h2>
                <p class="text-gray-600 mt-1">Update settlement status or reference mapping for {{ payment.transaction_id }}.</p>
            </div>

            <!-- Main Form Card Container -->
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm">
                    <div class="p-6 sm:p-8 space-y-6">
                        <!-- Identity & Channel Layout Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Payer Details</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Target customer account identification parameters.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Customer</label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            v-model="searchQuery"
                                            @focus="isOpen = true"
                                            @blur="handleBlur"
                                            required
                                            placeholder="Type customer name..."
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-gray-955"
                                        />
                                        <div v-if="isOpen && filteredCustomers.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                            <ul>
                                                <li
                                                    v-for="c in filteredCustomers"
                                                    :key="c.id"
                                                    @mousedown="selectCustomer(c)"
                                                    class="px-4 py-2.5 hover:bg-indigo-50 cursor-pointer text-sm font-medium text-gray-900 border-b border-gray-50 last:border-b-0"
                                                >
                                                    {{ c.name }} <span class="text-xs text-gray-400 font-normal">({{ c.country || 'Global Region' }})</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div v-if="searchQuery.trim() !== '' && filteredCustomers.length === 0" class="mt-2 flex items-center justify-between p-2.5 bg-amber-50 border border-amber-200 rounded-lg">
                                        <span class="text-xs text-amber-800 font-semibold">Customer "{{ searchQuery }}" does not exist.</span>
                                        <Link
                                            :href="`/customers/create?name=${encodeURIComponent(searchQuery)}`"
                                            class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-bold transition-colors shadow-sm inline-flex items-center"
                                        >
                                            <i class="fas fa-plus-circle mr-1.5"></i> Create Customer
                                        </Link>
                                    </div>
                                    <span v-if="form.errors.customer_name" class="text-xs text-red-500 mt-1 block">{{ form.errors.customer_name }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Sub-Customer / Location</label>
                                    <select
                                        v-model="form.sub_customer_id"
                                        :disabled="!selectedCustomerId || availableSubCustomers.length === 0"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium disabled:bg-gray-50 disabled:text-gray-400"
                                    >
                                        <option value="">{{ !selectedCustomerId ? 'Select a customer first' : (availableSubCustomers.length === 0 ? 'No sub-customers available' : 'Main Customer') }}</option>
                                        <option v-for="sc in availableSubCustomers" :key="sc.id" :value="sc.id">{{ sc.name }}</option>
                                    </select>
                                    <span v-if="form.errors.sub_customer_id" class="text-xs text-red-500 mt-1 block">{{ form.errors.sub_customer_id }}</span>
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

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
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
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Settlement Amount</label>
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
                        </div>

                        <!-- Gateway & Association Tiers Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Transaction Gateway Routing</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Assigned gateway channels and tokenized references.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Card Number</label>
                                    <input
                                        v-model="form.payment_method"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Channel Gateway Type</label>
                                    <select
                                        v-model="form.method_type"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option v-for="chan in paymentChannels" :key="chan.id" :value="chan.code">
                                            {{ chan.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Invoice Assignment Ref</label>
                                    <input
                                        v-model="form.invoice_ref"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Execution Date String</label>
                                    <input
                                        v-model="form.payment_date"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">End Date</label>
                                    <input
                                        v-model="form.end_date"
                                        type="text"
                                        placeholder="e.g. Jun 15, 2026"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-semibold text-gray-800"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Settlement Status</label>
                                    <select
                                        v-model="form.status"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option value="successful">Successful</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                        <option value="refunded">Refunded</option>
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
                            :href="`/payments?page=${returnPage}`"
                            class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-medium transition-colors inline-flex items-center"
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
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
    customers: {
        type: Array,
        default: () => [],
    },
    returnPage: {
        type: Number,
        default: 1,
    },
    paymentChannels: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    customer_name: props.payment.customer_name,
    amount: props.payment.amount,
    payment_method: props.payment.payment_method,
    method_type: props.payment.method_type,
    status: props.payment.status,
    invoice_ref: props.payment.invoice_ref || '',
    payment_date: props.payment.payment_date || '',
    billing_cycle: props.payment.billing_cycle || 'monthly',
    currency: props.payment.currency || 'USD',
    usd_amount: props.payment.usd_amount || 0.00,
    end_date: props.payment.end_date || '',
    sub_customer_id: props.payment.sub_customer_id || '',
    closed_by: props.payment.closed_by || '',
});

const initialCustomerMatch = props.customers.find(c => c.id === props.payment.customer_id) || props.customers.find(c => c.name === props.payment.customer_name);
const selectedCustomerId = ref(initialCustomerMatch ? initialCustomerMatch.id : '');

const searchQuery = ref(props.payment.customer_name || '');
const isOpen = ref(false);

const filteredCustomers = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();
    if (!q) return props.customers;
    return props.customers.filter(c => c.name.toLowerCase().includes(q));
});

const selectCustomer = (c) => {
    selectedCustomerId.value = c.id;
    searchQuery.value = c.name;
    isOpen.value = false;
};

const handleBlur = () => {
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};

const availableSubCustomers = computed(() => {
    const customer = props.customers.find(c => c.id === selectedCustomerId.value);
    return customer?.sub_customers ?? [];
});

watch(searchQuery, (newVal) => {
    form.customer_name = newVal;
    const match = props.customers.find(c => c.name.toLowerCase() === newVal.toLowerCase().trim());
    if (match) {
        selectedCustomerId.value = match.id;
    } else {
        selectedCustomerId.value = '';
    }
});

let isInitialCustomerSync = true;
watch(selectedCustomerId, (newId) => {
    const customer = props.customers.find(c => c.id === newId);
    if (customer) {
        form.customer_name = customer.name;
        searchQuery.value = customer.name;
    }
    if (isInitialCustomerSync) {
        // Preserve the payment's existing sub-customer on first load; only
        // reset it when the user actually changes the customer afterwards.
        isInitialCustomerSync = false;
        return;
    }
    form.sub_customer_id = '';
}, { immediate: true });

const exchangeRatesFallback = {
    SGD: 0.74,
    USD: 1.00,
    INR: 0.012,
    AED: 0.27,
    EUR: 1.10,
    GBP: 1.30
};

const page = usePage();

watch(() => [form.payment_date, form.billing_cycle], () => {
    if (!form.payment_date) {
        form.end_date = '';
        return;
    }
    const date = new Date(form.payment_date);
    if (!isNaN(date.getTime()) && form.billing_cycle) {
        const cycles = page.props.activeBillingCycles || [];
        const cycle = cycles.find(c => c.code === form.billing_cycle);
        const durationMonths = cycle ? cycle.duration_months : 1;
        
        // Add duration in months
        const endDate = new Date(date.setMonth(date.getMonth() + durationMonths));
        form.end_date = endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
}, { immediate: true });

let currentRequestId = 0;

watch(() => [form.amount, form.currency, form.payment_date], async () => {
    const amountVal = parseFloat(form.amount) || 0;
    if (form.currency === 'USD') {
        form.usd_amount = amountVal;
        return;
    }
    const requestId = ++currentRequestId;
    try {
        const dateParam = form.payment_date ? `&date=${encodeURIComponent(form.payment_date)}` : '';
        const response = await fetch(`/exchange-rate?from=${form.currency}&to=USD${dateParam}`);
        const data = await response.json();
        if (requestId !== currentRequestId) return;
        const rate = data.rates.USD || 1.00;
        form.usd_amount = parseFloat((amountVal * rate).toFixed(2));
    } catch (error) {
        if (requestId !== currentRequestId) return;
        const rate = exchangeRatesFallback[form.currency] || 1.00;
        form.usd_amount = parseFloat((amountVal * rate).toFixed(2));
    }
}, { immediate: true });
const submitForm = () => {
    form.transform((data) => ({
        ...data,
        sub_customer_id: data.sub_customer_id || null,
    })).put(`/payments/${props.payment.id}?page=${props.returnPage}`);
};
</script>
