<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto p-6">
            <div class="mb-6">
                <Link :href="`/customers/${customer.id}`" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to {{ customer.name }}
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Add Sub-Customer</h2>
                <p class="text-gray-600 mt-1">New location under <span class="font-semibold">{{ customer.name }}</span>.</p>
            </div>

            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm">
                    <div class="p-6 sm:p-8 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Customer Type</label>
                                <select disabled class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-gray-50 text-gray-500">
                                    <option>Sub-Customer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Parent Customer</label>
                                <input type="text" disabled :value="customer.name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-gray-50 text-gray-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Sub-Customer Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="e.g. New York"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                >
                                <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 block">{{ form.errors.name }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="location@acme.com"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                >
                                <span v-if="form.errors.email" class="text-xs text-red-500 mt-1 block">{{ form.errors.email }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Phone</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="+1 (555) 000-0000"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status</label>
                                <select
                                    v-model="form.status"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Address</label>
                                <textarea
                                    v-model="form.address"
                                    rows="2"
                                    placeholder="Street, City, State"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all resize-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <Link
                            :href="`/customers/${customer.id}`"
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
                            Save Customer
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
    customer: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    status: 'active',
});

const submitForm = () => {
    form.post(`/customers/${props.customer.id}/sub-customers`);
};
</script>
