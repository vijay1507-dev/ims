<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Billing Cycles</h2>
                    <p class="text-sm text-gray-500 mt-1">Configure subscription intervals and invoice durations</p>
                </div>
                <div>
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors inline-flex items-center cursor-pointer"
                    >
                        <i class="fas fa-plus mr-2"></i>Create Cycle
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-base">Active & Inactive Intervals</h3>
                    <p class="text-xs text-gray-500 mt-1">Standard and custom durations used across payments, subscriptions, and invoices</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Name</th>
                                <!-- <th class="px-6 py-3 text-left font-bold text-gray-600">Code</th> -->
                                <th class="px-6 py-3 text-right font-bold text-gray-600">Duration (Months)</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Status</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="cycle in billingCycles" :key="cycle.id" class="hover:bg-gray-55 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ cycle.name }}</td>
                                <!-- <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ cycle.code }}</td> -->
                                <td class="px-6 py-4 text-right font-medium text-gray-900">{{ cycle.duration_months }} months</td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[
                                        cycle.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600',
                                        'inline-flex px-2.5 py-1 rounded-full text-xs font-bold'
                                    ]">
                                        {{ cycle.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-3">
                                    <button
                                        @click="openEditModal(cycle)"
                                        class="text-indigo-600 hover:text-indigo-900 font-semibold cursor-pointer text-xs"
                                    >
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button
                                        @click="toggleStatus(cycle)"
                                        :class="[
                                            cycle.status === 'active' ? 'text-amber-600 hover:text-amber-900' : 'text-emerald-600 hover:text-emerald-900',
                                            'font-semibold cursor-pointer text-xs'
                                        ]"
                                    >
                                        <i :class="[cycle.status === 'active' ? 'fas fa-eye-slash' : 'fas fa-eye', 'mr-1']"></i>
                                        {{ cycle.status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        @click="deleteCycle(cycle)"
                                        class="text-rose-600 hover:text-rose-900 font-semibold cursor-pointer text-xs"
                                    >
                                        <i class="fas fa-trash-alt mr-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!billingCycles || billingCycles.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create / Edit Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in">
                <div class="bg-white rounded-xl shadow-lg max-w-md w-full overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-lg">{{ isEditMode ? 'Edit Billing Cycle' : 'Create Billing Cycle' }}</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Display Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Quarterly"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <span v-if="form.errors.name" class="text-xs text-rose-600 block mt-1">{{ form.errors.name }}</span>
                        </div>



                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Duration (Months)</label>
                            <input
                                v-model="form.duration_months"
                                type="number"
                                min="1"
                                placeholder="e.g. 3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <span v-if="form.errors.duration_months" class="text-xs text-rose-600 block mt-1">{{ form.errors.duration_months }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Initial Status</label>
                            <select
                                v-model="form.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <span v-if="form.errors.status" class="text-xs text-rose-600 block mt-1">{{ form.errors.status }}</span>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition-colors cursor-pointer inline-flex items-center"
                            >
                                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                Save Cycle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    billingCycles: {
        type: Array,
        required: true,
    }
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    code: '',
    duration_months: 1,
    status: 'active',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isEditMode.value = false;
    editingId.value = null;
    isModalOpen.value = true;
};

const openEditModal = (cycle) => {
    form.clearErrors();
    form.name = cycle.name;
    form.code = cycle.code;
    form.duration_months = cycle.duration_months;
    form.status = cycle.status;
    isEditMode.value = true;
    editingId.value = cycle.id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitForm = () => {
    if (!isEditMode.value) {
        form.code = form.name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
    }
    
    if (isEditMode.value) {
        form.put(`/billing-cycles/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/billing-cycles', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (cycle) => {
    router.post(`/billing-cycles/${cycle.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

const deleteCycle = (cycle) => {
    if (confirm(`Are you sure you want to delete the billing cycle "${cycle.name}"?`)) {
        router.delete(`/billing-cycles/${cycle.id}`, {
            preserveScroll: true,
        });
    }
};
</script>
