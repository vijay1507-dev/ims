<template>
    <div v-if="show" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-trash-alt text-rose-500"></i>
                    Bulk Delete {{ moduleName }}
                </h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <!-- Segmented Control for Period Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Delete By</label>
                    <div class="flex rounded-lg bg-gray-100 p-1">
                        <button
                            type="button"
                            @click="changePeriodType('month')"
                            :class="[
                                periodType === 'month'
                                    ? 'bg-white text-indigo-700 shadow-xs'
                                    : 'text-gray-500 hover:text-gray-900',
                                'flex-1 py-2 text-xs font-bold rounded-md transition-all cursor-pointer text-center'
                            ]"
                        >
                            Month
                        </button>
                        <button
                            type="button"
                            @click="changePeriodType('year')"
                            :class="[
                                periodType === 'year'
                                    ? 'bg-white text-indigo-700 shadow-xs'
                                    : 'text-gray-500 hover:text-gray-900',
                                'flex-1 py-2 text-xs font-bold rounded-md transition-all cursor-pointer text-center'
                            ]"
                        >
                            Year
                        </button>
                    </div>
                </div>

                <!-- Period Selection Dropdowns -->
                <div class="space-y-4">
                    <div v-if="periodType === 'month'" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Month</label>
                            <select
                                v-model="subMonth"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer font-medium text-gray-800"
                            >
                                <option v-for="m in months" :key="m.value" :value="m.value">
                                    {{ m.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Year</label>
                            <select
                                v-model="subYear"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer font-medium text-gray-800"
                            >
                                <option v-for="y in years" :key="y" :value="y">
                                    {{ y }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div v-else>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Year</label>
                        <select
                            v-model="subYear"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer font-medium text-gray-800"
                        >
                            <option v-for="y in years" :key="y" :value="y">
                                {{ y }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Record Count Indicator -->
                <div class="text-sm font-medium">
                    <div v-if="loading" class="text-gray-500 flex items-center gap-2">
                        <span class="inline-block animate-spin border-2 border-indigo-600 border-t-transparent h-4 w-4 rounded-full"></span>
                        Calculating matching records...
                    </div>
                    <div v-else-if="count > 0" class="text-rose-600 font-bold">
                        {{ count }} {{ moduleName.toLowerCase() }} records found
                    </div>
                    <div v-else class="text-gray-500 italic">
                        No {{ moduleName.toLowerCase() }} records found for {{ selectedPeriodLabel }}.
                    </div>
                </div>

                <!-- Warning Banner -->
                <div v-if="!loading && count > 0" class="bg-rose-50 border border-rose-100 rounded-lg p-4 text-xs text-rose-800 space-y-2">
                    <p class="font-bold flex items-center gap-1.5">
                        <i class="fas fa-exclamation-triangle text-rose-500"></i>
                        Attention Required
                    </p>
                    <p>
                        You are about to delete {{ count }} {{ moduleName.toLowerCase() }} records from {{ selectedPeriodLabel }}. 
                        These records will be deleted and  can be restored later.
                    </p>
                    <p v-if="module.toLowerCase() === 'customers'" class="font-semibold text-rose-700">
                        * Note: This will also cascade and Delete all related Subscriptions, Invoices, Payments, and Inventory items for these customers.
                    </p>
                    <p v-else-if="module.toLowerCase() === 'subscriptions'" class="font-semibold text-rose-700">
                        * Note: This will also cascade and Delete all Payments and associated Invoices for these subscriptions' customers.
                    </p>
                    <p v-else-if="module.toLowerCase() === 'invoices'" class="font-semibold text-rose-700">
                        * Note: This will also cascade and Delete all Payments associated with these invoices.
                    </p>
                    <p v-else-if="module.toLowerCase() === 'payments'" class="font-semibold text-rose-700">
                        * Note: This will also cascade and Delete all Invoices associated with these payments.
                    </p>
                </div>
            </div>

            <!-- Modal Actions Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-xs font-bold transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    :disabled="loading || count === 0 || deleting"
                    @click="executeDelete"
                    class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 disabled:opacity-50 text-xs font-bold transition-colors cursor-pointer flex items-center gap-2"
                >
                    <span v-if="deleting" class="inline-block animate-spin border-2 border-white border-t-transparent h-3 w-3 rounded-full"></span>
                    {{ deleting ? 'Archiving...' : `Delete ${count} Records` }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    module: {
        type: String,
        required: true, // 'customers', 'subscriptions', 'payments', 'invoices', 'inventory'
    },
    moduleName: {
        type: String,
        required: true,
    }
});

const emit = defineEmits(['close', 'success']);

const periodType = ref('month');
const subMonth = ref('');
const subYear = ref('');
const count = ref(0);
const loading = ref(false);
const deleting = ref(false);

const months = [
    { label: 'January', value: '01' },
    { label: 'February', value: '02' },
    { label: 'March', value: '03' },
    { label: 'April', value: '04' },
    { label: 'May', value: '05' },
    { label: 'June', value: '06' },
    { label: 'July', value: '07' },
    { label: 'August', value: '08' },
    { label: 'September', value: '09' },
    { label: 'October', value: '10' },
    { label: 'November', value: '11' },
    { label: 'December', value: '12' },
];

const years = computed(() => {
    const list = [];
    const currentYear = new Date().getFullYear();
    // Previous 10 years and next 10 years (total 21 years)
    for (let y = currentYear + 10; y >= currentYear - 10; y--) {
        list.push(String(y));
    }
    return list;
});

const selectedPeriod = computed(() => {
    if (periodType.value === 'month') {
        return `${subYear.value}-${subMonth.value}`;
    }
    return subYear.value;
});

const selectedPeriodLabel = computed(() => {
    if (periodType.value === 'month') {
        const mOpt = months.find(m => m.value === subMonth.value);
        const mLabel = mOpt ? mOpt.label : '';
        return `${mLabel} ${subYear.value}`;
    }
    return subYear.value;
});

// Setup default periods
const resetPeriodDefault = () => {
    const today = new Date();
    subMonth.value = String(today.getMonth() + 1).padStart(2, '0');
    subYear.value = String(today.getFullYear());
};

const changePeriodType = (type) => {
    periodType.value = type;
    resetPeriodDefault();
};

const fetchCount = async () => {
    if (!props.show || !selectedPeriod.value) return;
    loading.value = true;
    try {
        const response = await axios.post('/bulk-delete/count', {
            module: props.module,
            type: periodType.value,
            period: selectedPeriod.value
        });
        count.value = response.data.count;
    } catch (err) {
        console.error('Failed to fetch count', err);
        count.value = 0;
    } finally {
        loading.value = false;
    }
};

const executeDelete = () => {
    if (count.value === 0 || deleting.value) return;
    deleting.value = true;
    router.post('/bulk-delete/execute', {
        module: props.module,
        type: periodType.value,
        period: selectedPeriod.value
    }, {
        onSuccess: () => {
            emit('close');
            emit('success');
        },
        onError: (errors) => {
            console.error('Deletion failed', errors);
            alert('An error occurred while deleting.');
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

const closeModal = () => {
    emit('close');
};

// Listeners
watch(() => props.show, (newVal) => {
    if (newVal) {
        resetPeriodDefault();
        fetchCount();
    }
});

watch(selectedPeriod, () => {
    fetchCount();
});
</script>
