<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Breadcrumb Navigation Header -->
            <div class="mb-6">
                <Link 
                    :href="form.redirect_customer_id ? `/customers/${form.redirect_customer_id}/edit` : '/subscriptions'" 
                    class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2 font-bold"
                >
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> 
                    Back to {{ form.redirect_customer_id ? 'Customer Profile' : 'Subscriptions Dashboard' }}
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Provision Subscription Assignment</h2>
                <p class="text-gray-600 mt-1">Configure subscriber identity bindings, tier upgrades, and automated renewal cycles.</p>
            </div>

            <!-- Main Form Card Container -->
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm">
                    <div class="p-6 sm:p-8 space-y-8">
                        <!-- Profile Layout Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-user-tag text-indigo-500"></i>Subscriber Identity
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Target entity and notification delivery paths.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Subscriber Account</label>
                                    <select
                                        v-model="selectedCustomerId"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium"
                                    >
                                        <option value="">Select customer</option>
                                        <option v-for="c in customers" :key="c.id" :value="c.id">
                                            {{ c.name }}
                                        </option>
                                    </select>
                                    <span v-if="form.errors.customer_name" class="text-xs text-red-500 mt-1 block">{{ form.errors.customer_name }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Sub-Customer / Location</label>
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

                            </div>
                        </div>

                        <!-- Billing & Contract Tiers Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-layer-group text-indigo-500"></i>Pricing Tier & Cycle Structure
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Assigned recurrence frequency, base interval sums, and status flags.</p>
                                </div>
                                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                    Supports Upgrade / Downgrade Pro-Rata
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Subscription Tier</label>
                                    <select
                                        v-model="form.plan_name"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium"
                                        @change="updateAmount"
                                    >
                                        <option v-for="p in plans" :key="p.name" :value="p.name">
                                            {{ p.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Billing Interval</label>
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

                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mt-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Interval Charge ($)</label>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        placeholder="89.00"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-bold text-gray-900"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Start Date</label>
                                    <input
                                        v-model="form.start_date"
                                        type="text"
                                        placeholder="e.g. Aug 11, 2026"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">End Date</label>
                                    <input
                                        v-model="form.next_billing_date"
                                        type="text"
                                        placeholder="e.g. Jun 15, 2026"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Operational State</label>
                                    <select
                                        v-model="form.status"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium"
                                    >
                                        <option value="active">Active Plan</option>
                                        <option value="trial">Trial Subscription</option>
                                        <option value="paused">Paused</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- Card Submission Action Bar -->
                    <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <Link
                            :href="form.redirect_customer_id ? `/customers/${form.redirect_customer_id}/edit` : '/subscriptions'"
                            class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-bold transition-colors inline-flex items-center cursor-pointer"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-bold shadow-sm disabled:opacity-50 transition-colors inline-flex items-center cursor-pointer"
                        >
                            <span v-if="form.processing" class="inline-block animate-spin mr-2 border-2 border-white border-t-transparent h-4 w-4 rounded-full"></span>
                            Deploy Allocation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
    initial_name: {
        type: String,
        default: '',
    },
    initial_email: {
        type: String,
        default: '',
    },
    redirect_customer_id: {
        type: [String, Number],
        default: '',
    },
    customers: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (date) => {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const d = new Date(date);
    const day = d.getDate();
    const month = months[d.getMonth()];
    const year = d.getFullYear();
    return `${month} ${day < 10 ? '0' + day : day}, ${year}`;
};

const calculateEndDate = (startDateStr, billingCycleCode) => {
    if (!startDateStr) return '';
    const d = new Date(startDateStr);
    if (isNaN(d.getTime())) return '';
    
    const page = usePage();
    const cycle = (page.props.activeBillingCycles || []).find(c => c.code === billingCycleCode);
    const monthsToAdd = cycle ? parseInt(cycle.duration_months) : 1;
    
    d.setMonth(d.getMonth() + monthsToAdd);
    
    return formatDate(d);
};

const todayFormatted = formatDate(new Date());

const form = useForm({
    customer_name: props.initial_name,
    customer_email: props.initial_email,
    plan_name: props.plans.length > 0 ? props.plans[0].name : 'Starter Plan',
    billing_cycle: usePage().props.activeBillingCycles && usePage().props.activeBillingCycles.length > 0 ? usePage().props.activeBillingCycles[0].code : 'monthly',
    start_date: todayFormatted,
    next_billing_date: calculateEndDate(new Date(), usePage().props.activeBillingCycles && usePage().props.activeBillingCycles.length > 0 ? usePage().props.activeBillingCycles[0].code : 'monthly'),
    amount: props.plans.length > 0 ? props.plans[0].default_amount : 49.00,
    status: 'active',
    sub_customer_id: '',
    // Custom lifecycle module keys
    addons: [],
    auto_renew: true,
    renewal_reminders: '7_days',
    redirect_customer_id: props.redirect_customer_id,
});

const initialCustomerMatch = props.customers.find(c => c.name === props.initial_name || c.email === props.initial_email);
const selectedCustomerId = ref(initialCustomerMatch ? initialCustomerMatch.id : '');

const availableSubCustomers = computed(() => {
    const customer = props.customers.find(c => c.id === selectedCustomerId.value);
    return customer?.sub_customers ?? [];
});

watch(selectedCustomerId, (newId) => {
    const customer = props.customers.find(c => c.id === newId);
    form.customer_name = customer ? customer.name : '';
    form.customer_email = customer ? customer.email : '';
    form.sub_customer_id = '';
}, { immediate: true });

watch(() => [form.start_date, form.billing_cycle], ([newStartDate, newCycle]) => {
    form.next_billing_date = calculateEndDate(newStartDate, newCycle);
});

const updateAmount = () => {
    const selected = props.plans.find(p => p.name === form.plan_name);
    if (selected) {
        form.amount = selected.default_amount;
    }
};

const submitForm = () => {
    form.post('/subscriptions');
};
</script>
