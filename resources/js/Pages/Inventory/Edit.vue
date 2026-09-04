<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Breadcrumb Navigation Header -->
            <div class="mb-6">
                <Link :href="`/inventory?page=${returnPage}`" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to Inventory
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Modify Hardware Asset</h2>
                <p class="text-gray-600 mt-1">Reassign customer properties or update life span status strings for {{ inventory.asset_id }}.</p>
            </div>

            <!-- Main Form Card Container -->
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm">
                    <div class="p-6 sm:p-8 space-y-6">
                        <!-- Categorization Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Asset Specifications</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Hardware product class mapping and core identity descriptors.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Type</label>
                                    <select
                                        v-model="form.category"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option value="kiosk">Kiosks</option>
                                        <option value="tv">TVs</option>
                                        <option value="tablet">Tablets</option>
                                        <option value="printer">Printers</option>
                                        <option value="license">License Keys</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Model Name / Build String</label>
                                    <input
                                        v-model="form.name_model"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.name_model" class="text-xs text-red-500 mt-1 block">{{ form.errors.name_model }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Mapping Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Deployment & Tracking Assignment</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Assigned client targets and operational lifespan bindings.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Assigned Customer / Organization</label>
                                    <input
                                        v-model="form.customer_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.customer_name" class="text-xs text-red-500 mt-1 block">{{ form.errors.customer_name }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Serial Code / License Identifier</label>
                                    <input
                                        v-model="form.serial_license"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-mono"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Assigned Date</label>
                                    <input
                                        v-model="form.assigned_date"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Warranty Expiration</label>
                                    <input
                                        v-model="form.warranty_expiry"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Hardware Status</label>
                                    <select
                                        v-model="form.status"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option value="active">Active</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="retired">Retired</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Submission Action Bar -->
                    <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <Link
                            :href="`/inventory?page=${returnPage}`"
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
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    inventory: {
        type: Object,
        required: true,
    },
    returnPage: {
        type: Number,
        default: 1,
    },
});

const form = useForm({
    category: props.inventory.category,
    name_model: props.inventory.name_model,
    customer_name: props.inventory.customer_name,
    serial_license: props.inventory.serial_license || '',
    status: props.inventory.status,
    assigned_date: props.inventory.assigned_date || '',
    warranty_expiry: props.inventory.warranty_expiry || '',
});

const submitForm = () => {
    form.put(`/inventory/${props.inventory.id}?page=${props.returnPage}`);
};
</script>
