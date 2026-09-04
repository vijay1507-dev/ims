<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto animate-fade-in">
            <!-- Page Title Header -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Commissions Dashboard</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage, calculate, and log monthly sales commission payouts for your representatives.</p>
                </div>
                <!-- Navigation Tabs -->
                <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200 shadow-2xs">
                    <button
                        @click="activeTab = 'history'"
                        :class="[
                            'px-4 py-2 text-sm font-bold rounded-lg cursor-pointer transition-all duration-200',
                            activeTab === 'history'
                                ? 'bg-white text-indigo-600 shadow-xs'
                                : 'text-gray-600 hover:text-gray-900'
                        ]"
                    >
                        <i class="fas fa-history mr-2"></i>Payouts History
                    </button>
                    <button
                        @click="activeTab = 'calculator'"
                        :class="[
                            'px-4 py-2 text-sm font-bold rounded-lg cursor-pointer transition-all duration-200',
                            activeTab === 'calculator'
                                ? 'bg-white text-indigo-600 shadow-xs'
                                : 'text-gray-600 hover:text-gray-900'
                        ]"
                    >
                        <i class="fas fa-calculator mr-2"></i>Commission Calculator
                    </button>
                </div>
            </div>

            <!-- Tab 1: Payouts History -->
            <div v-if="activeTab === 'history'" class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-55 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Saved Commission Logs</h3>
                            <p class="text-xs text-gray-500 mt-1">Audit log of all calculated, drafted, and finalized commission payments.</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left font-bold text-gray-600">Employee</th>
                                    <th class="px-6 py-3 text-left font-bold text-gray-600">Period</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Base Salary</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Closed Sales</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Slab Target</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Commission Rate</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Commission Payout</th>
                                    <th class="px-6 py-3 text-center font-bold text-gray-600">Status</th>
                                    <th class="px-6 py-3 text-center font-bold text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="log in commissions" :key="log.id" class="hover:bg-gray-55 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ log.employee_name }}</td>
                                    <td class="px-6 py-4 text-gray-600 font-medium">{{ log.period }}</td>
                                    <td class="px-6 py-4 text-right text-gray-900">{{ log.salary }}</td>
                                    <td class="px-6 py-4 text-right text-gray-900">{{ log.total_payments_closed }}</td>
                                    <td class="px-6 py-4 text-right text-gray-500">{{ log.slab_name }}</td>
                                    <td class="px-6 py-4 text-right text-indigo-600 font-medium">{{ log.commission_percentage }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-indigo-600">{{ log.commission_amount }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <select
                                            v-model="log.status"
                                            @change="updateStatus(log)"
                                            :disabled="log.status === 'paid'"
                                            :class="[
                                                log.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                log.status === 'approved' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                                                log.status === 'calculated' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                                                'bg-gray-100 text-gray-700 border-gray-200',
                                                'text-xs font-bold rounded-lg border px-2.5 py-1 text-center bg-white cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-indigo-500 disabled:opacity-80'
                                            ]"
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="calculated">Calculated</option>
                                            <option value="approved">Approved</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            @click="deleteRecord(log)"
                                            class="text-rose-600 hover:text-rose-900 font-semibold cursor-pointer text-xs"
                                            :disabled="log.status === 'paid'"
                                        >
                                            <i class="fas fa-trash-alt mr-1"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!commissions || commissions.length === 0">
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-receipt text-3xl text-gray-300 mb-3 block"></i>
                                        No commission records logged.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Commission Calculator -->
            <div v-else-if="activeTab === 'calculator'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Form Parameters -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-6 space-y-6">
                        <div>
                            <h3 class="font-extrabold text-gray-900 text-base">Calculation Parameters</h3>
                            <p class="text-xs text-gray-500 mt-1">Specify employee, salary details, and period.</p>
                        </div>
                        
                        <form @submit.prevent="runCalculation" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Employee</label>
                                <select
                                    v-model="calcForm.employee_name"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required
                                >
                                    <option value="">Select Employee</option>
                                    <option v-for="emp in employees" :key="emp" :value="emp">{{ emp }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Monthly Base Salary (₹)</label>
                                <input
                                    v-model="calcForm.salary"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="e.g. 50000"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Month</label>
                                    <select
                                        v-model="calcForm.month"
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                                        required
                                    >
                                        <option v-for="m in availableMonths" :key="m.value" :value="m.value">{{ m.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Year</label>
                                    <select
                                        v-model="calcForm.year"
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                                        required
                                    >
                                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-4">
                                <button
                                    type="submit"
                                    :disabled="calculating"
                                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center"
                                >
                                    <i v-if="calculating" class="fas fa-spinner fa-spin mr-2"></i>
                                    Calculate Commission
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Calculation Summary / Breakdown -->
                <div class="lg:col-span-2 space-y-6">
                    <div v-if="result" class="space-y-6">
                        <!-- Premium Summary Card -->
                        <div class="bg-indigo-900 rounded-2xl p-6 text-white shadow-md relative overflow-hidden border border-indigo-950">
                            <!-- Background Accent Glow -->
                            <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-indigo-500/20 blur-xl"></div>
                            
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs uppercase font-extrabold tracking-widest text-indigo-200 bg-indigo-850 px-2.5 py-1 rounded-full border border-indigo-800">
                                        Payout Summary
                                    </span>
                                    <h3 class="text-2xl font-extrabold mt-3">{{ result.employee_name }}</h3>
                                    <p class="text-sm text-indigo-200 mt-1">Salary: ₹{{ formatNumber(result.salary) }} | Period: {{ result.period }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-indigo-300 font-bold uppercase tracking-wider">Commission Payout</p>
                                    <p class="text-3xl font-extrabold text-white mt-1">₹{{ formatNumber(result.commission_amount) }}</p>
                                </div>
                            </div>

                            <!-- Dashboard KPI Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-indigo-800">
                                <div>
                                    <span class="text-xs text-indigo-300 block">Total Payments Closed</span>
                                    <span class="text-base font-bold">₹{{ formatNumber(result.total_payments_closed) }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-indigo-300 block">Achievement Rate</span>
                                    <span class="text-base font-bold">{{ result.achievement_percentage }}%</span>
                                </div>
                                <div>
                                    <span class="text-xs text-indigo-300 block">Slab Points</span>
                                    <span class="text-base font-bold">{{ result.points }} pts</span>
                                </div>
                                <div>
                                    <span class="text-xs text-indigo-300 block">Commission Rate</span>
                                    <span class="text-base font-bold text-amber-300">{{ result.commission_percentage }}% of Salary</span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2 text-xs text-indigo-200">
                                <span>Achieved Slab Target:</span>
                                <span class="font-extrabold text-white">{{ result.slab_name }}</span>
                            </div>
                        </div>

                        <!-- Payments Included Breakdown table -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                                <h4 class="font-bold text-gray-900 text-sm">Included Payments Breakdown</h4>
                                <span class="text-xs text-gray-500 font-medium">Eligible transactions closed during period</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead>
                                        <tr class="bg-gray-55">
                                            <th class="px-4 py-2.5 text-left font-bold text-gray-600">Transaction ID</th>
                                            <th class="px-4 py-2.5 text-left font-bold text-gray-600">Customer</th>
                                            <th class="px-4 py-2.5 text-left font-bold text-gray-600">Date</th>
                                            <th class="px-4 py-2.5 text-right font-bold text-gray-600">Original Amount</th>
                                            <th class="px-4 py-2.5 text-right font-bold text-gray-600">INR Amount</th>
                                            <th class="px-4 py-2.5 text-center font-bold text-gray-600">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="p in result.payments" :key="p.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-semibold text-gray-900">{{ p.transaction_id }}</td>
                                            <td class="px-4 py-3 text-gray-700 font-medium">{{ p.customer_name }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ p.payment_date }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                                {{ p.amount }} <span class="text-xs text-gray-400 font-bold uppercase">{{ p.currency }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right font-extrabold text-indigo-600">{{ p.inr_amount }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-md font-bold uppercase text-[10px] tracking-wider">
                                                    {{ p.status }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="!result.payments || result.payments.length === 0">
                                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                                No eligible closed payments found for this user in the selected period.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-900">Total Eligible Payments:</span>
                                <span class="font-extrabold text-indigo-600">₹{{ formatNumber(result.total_payments_closed) }}</span>
                            </div>
                        </div>

                        <!-- Save Actions Bar -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm">Log Payout Record</h4>
                                <p class="text-xs text-gray-500">Save the calculated payout to the CRM history.</p>
                            </div>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <button
                                    @click="saveCommission('draft')"
                                    :disabled="saving"
                                    class="flex-1 sm:flex-initial px-4 py-2 border border-gray-300 hover:bg-gray-55 rounded-lg text-xs font-bold transition-colors cursor-pointer text-gray-700"
                                >
                                    Save as Draft
                                </button>
                                <button
                                    @click="saveCommission('approved')"
                                    :disabled="saving"
                                    class="flex-1 sm:flex-initial px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center"
                                >
                                    <i v-if="saving" class="fas fa-spinner fa-spin mr-1"></i>
                                    Save and Approve
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calculator Placeholder / Instructions -->
                    <div v-else class="bg-white rounded-xl border border-gray-200 border-dashed p-16 text-center shadow-2xs flex flex-col items-center justify-center min-h-[300px]">
                        <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
                            <i class="fas fa-calculator text-2xl text-indigo-600"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900">Calculation Results</h4>
                        <p class="text-sm text-gray-500 max-w-sm mt-2">
                            Select an employee, enter their salary, select the payout month, and click Calculate Commission to see dynamic telemetry.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    employees: {
        type: Array,
        required: true,
    },
    commissions: {
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
});

const activeTab = ref('history');
const calculating = ref(false);
const saving = ref(false);
const result = ref(null);

const calcForm = ref({
    employee_name: '',
    salary: '',
    month: new Date().getMonth() + 1, // defaults to current month
    year: new Date().getFullYear(), // defaults to current year
});

const formatNumber = (num) => {
    return parseFloat(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const runCalculation = async () => {
    calculating.value = true;
    result.value = null;
    try {
        const response = await axios.post('/commissions/calculate', calcForm.value);
        if (response.data.success) {
            result.value = response.data.data;
        } else {
            alert(response.data.message || 'Error occurred during calculation.');
        }
    } catch (error) {
        console.error(error);
        const errorMsg = error.response?.data?.message || 'Failed to calculate commission. Ensure inputs are correct.';
        alert(errorMsg);
    } finally {
        calculating.value = false;
    }
};

const saveCommission = (status) => {
    if (!result.value) return;
    
    saving.value = true;
    const saveForm = useForm({
        employee_name: result.value.employee_name,
        salary: result.value.salary,
        month: result.value.month,
        year: result.value.year,
        status: status,
    });

    saveForm.post('/commissions', {
        onSuccess: () => {
            activeTab.value = 'history';
            result.value = null;
            saving.value = false;
        },
        onError: (err) => {
            console.error(err);
            alert('Failed to log payout record.');
            saving.value = false;
        }
    });
};

const updateStatus = (log) => {
    router.put(`/commissions/${log.id}`, {
        status: log.status
    }, {
        preserveScroll: true,
        onSuccess: () => {},
        onError: (err) => {
            alert('Failed to update commission record status.');
        }
    });
};

const deleteRecord = (log) => {
    if (confirm(`Are you sure you want to delete the commission record for "${log.employee_name}" for period "${log.period}"?`)) {
        router.delete(`/commissions/${log.id}`, {
            preserveScroll: true,
        });
    }
};
</script>
