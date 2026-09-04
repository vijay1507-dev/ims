<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Commission Slabs</h2>
                    <p class="text-sm text-gray-500 mt-1">Configure sales achievement levels, points, and employee commission rates.</p>
                </div>
                <div>
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors inline-flex items-center cursor-pointer"
                    >
                        <i class="fas fa-plus mr-2"></i>Create Slab
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-base">Active Achievement Milestones</h3>
                    <p class="text-xs text-gray-500 mt-1">Configured commission slabs evaluated during employee payouts calculations.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-55">
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Achievement Amount</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-600">Achievement %</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-600">Points Awarded</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-600">Commission Rate</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Status</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="slab in slabs" :key="slab.id" class="hover:bg-gray-55 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    ₹{{ formatNumber(slab.achievement_amount) }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">
                                    {{ parseFloat(slab.achievement_percentage) }}%
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">
                                    {{ slab.points }} pts
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-indigo-600">
                                    {{ parseFloat(slab.commission_percentage) }}% of Salary
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[
                                        slab.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600',
                                        'inline-flex px-2.5 py-1 rounded-full text-xs font-bold'
                                    ]">
                                        {{ slab.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center space-x-3">
                                    <button
                                        @click="openEditModal(slab)"
                                        class="text-indigo-600 hover:text-indigo-900 font-semibold cursor-pointer text-xs"
                                    >
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button
                                        @click="toggleStatus(slab)"
                                        :class="[
                                            slab.status === 'active' ? 'text-amber-600 hover:text-amber-900' : 'text-emerald-600 hover:text-emerald-900',
                                            'font-semibold cursor-pointer text-xs'
                                        ]"
                                    >
                                        <i :class="[slab.status === 'active' ? 'fas fa-eye-slash' : 'fas fa-eye', 'mr-1']"></i>
                                        {{ slab.status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        @click="deleteSlab(slab)"
                                        class="text-rose-600 hover:text-rose-900 font-semibold cursor-pointer text-xs"
                                    >
                                        <i class="fas fa-trash-alt mr-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!slabs || slabs.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No slabs configured.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create / Edit Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in">
                <div class="bg-white rounded-xl shadow-lg max-w-md w-full overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-lg">{{ isEditMode ? 'Edit Commission Slab' : 'Create Commission Slab' }}</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Achievement Amount (INR / ₹)</label>
                            <input
                                v-model="form.achievement_amount"
                                type="number"
                                step="0.01"
                                placeholder="e.g. 500000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <span v-if="form.errors.achievement_amount" class="text-xs text-rose-600 block mt-1">{{ form.errors.achievement_amount }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Achievement Percentage (%)</label>
                            <input
                                v-model="form.achievement_percentage"
                                type="number"
                                step="0.01"
                                placeholder="e.g. 100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <span v-if="form.errors.achievement_percentage" class="text-xs text-rose-600 block mt-1">{{ form.errors.achievement_percentage }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Points Awarded</label>
                            <input
                                v-model="form.points"
                                type="number"
                                placeholder="e.g. 100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <span v-if="form.errors.points" class="text-xs text-rose-600 block mt-1">{{ form.errors.points }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Commission Percentage (% of Salary)</label>
                            <input
                                v-model="form.commission_percentage"
                                type="number"
                                step="0.01"
                                placeholder="e.g. 25"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            />
                            <span v-if="form.errors.commission_percentage" class="text-xs text-rose-600 block mt-1">{{ form.errors.commission_percentage }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status</label>
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
                                class="px-4 py-2 border border-gray-300 hover:bg-gray-55 text-gray-700 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition-colors cursor-pointer inline-flex items-center"
                            >
                                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                Save Slab
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
    slabs: {
        type: Array,
        required: true,
    }
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref(null);

const form = useForm({
    achievement_amount: '',
    achievement_percentage: '',
    points: '',
    commission_percentage: '',
    status: 'active',
});

const formatNumber = (num) => {
    return parseFloat(num).toLocaleString('en-IN');
};

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isEditMode.value = false;
    editingId.value = null;
    isModalOpen.value = true;
};

const openEditModal = (slab) => {
    form.clearErrors();
    form.achievement_amount = slab.achievement_amount;
    form.achievement_percentage = slab.achievement_percentage;
    form.points = slab.points;
    form.commission_percentage = slab.commission_percentage;
    form.status = slab.status;
    isEditMode.value = true;
    editingId.value = slab.id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(`/commission-slabs/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/commission-slabs', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (slab) => {
    router.post(`/commission-slabs/${slab.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

const deleteSlab = (slab) => {
    if (confirm(`Are you sure you want to delete this commission slab of ₹${formatNumber(slab.achievement_amount)}?`)) {
        router.delete(`/commission-slabs/${slab.id}`, {
            preserveScroll: true,
        });
    }
};
</script>
