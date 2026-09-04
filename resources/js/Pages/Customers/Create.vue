<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Breadcrumb Navigation Header -->
            <div class="mb-6">
                <Link href="/customers" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to Customers List
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Provision New Enterprise Account</h2>
                <p class="text-gray-600 mt-1">Fill out the organization profile parameters to deploy live telemetry monitoring.</p>
            </div>

            <!-- Main Form Card Container -->
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm">
                    <div class="p-6 sm:p-8 space-y-6">
                        <!-- Hierarchy Section -->
                        <div class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Customer Type</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Main customers can later have multiple sub-customers/locations.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Customer Type</label>
                                    <select
                                        v-model="customerType"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option value="main">Main Customer</option>
                                        <option value="sub">Sub-Customer</option>
                                    </select>
                                </div>

                                <div v-if="customerType === 'sub'">
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Parent Customer</label>
                                    <select
                                        v-model="subForm.customer_id"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option value="">Select Customer</option>
                                        <option v-for="c in mainCustomers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                    <span v-if="subForm.errors.customer_id" class="text-xs text-red-500 mt-1 block">{{ subForm.errors.customer_id }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Customer Fields -->
                        <div v-if="customerType === 'sub'" class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Sub-Customer Details</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Location-specific contact information.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Sub-Customer Name</label>
                                    <input
                                        v-model="subForm.name"
                                        type="text"
                                        required
                                        placeholder="e.g. New York"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="subForm.errors.name" class="text-xs text-red-500 mt-1 block">{{ subForm.errors.name }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Email</label>
                                    <input
                                        v-model="subForm.email"
                                        type="email"
                                        placeholder="location@acme.com"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="subForm.errors.email" class="text-xs text-red-500 mt-1 block">{{ subForm.errors.email }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Phone</label>
                                    <input
                                        v-model="subForm.phone"
                                        type="text"
                                        placeholder="+1 (555) 000-0000"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status</label>
                                    <select
                                        v-model="subForm.status"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Address</label>
                                    <textarea
                                        v-model="subForm.address"
                                        rows="2"
                                        placeholder="Street, City, State"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all resize-none"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Layout Section -->
                        <div v-if="customerType === 'main'" class="border-b border-gray-100 pb-6">
                            <h3 class="text-base font-semibold text-gray-900">Organization Parameters</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Primary identifier and communication addresses.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6 mt-4">

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Full Name (Contact Person)</label>
                                    <input
                                        v-model="form.contact_name"
                                        type="text"
                                        placeholder="e.g. John Doe"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.contact_name" class="text-xs text-red-500 mt-1 block">{{ form.errors.contact_name }}</span>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Company Name</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        placeholder="e.g. Acme Corporation"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 block">{{ form.errors.name }}</span>
                                </div>


                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Primary Email</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        placeholder="billing@acme.com"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.email" class="text-xs text-red-500 mt-1 block">{{ form.errors.email }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Contact Number</label>
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        placeholder="+1 (555) 000-0000"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.phone" class="text-xs text-red-500 mt-1 block">{{ form.errors.phone }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Country</label>
                                    <select
                                        v-model="form.country"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all cursor-pointer"
                                    >
                                        <option value="">Select your country</option>
                                        <option v-for="item in countries" :key="item" :value="item">{{ item }}</option>
                                    </select>
                                    <span v-if="form.errors.country" class="text-xs text-red-500 mt-1 block">{{ form.errors.country }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Industry Segment</label>
                                    <select
                                        v-model="form.industry"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all cursor-pointer"
                                    >
                                        <option value="">Select your industry</option>
                                        <option v-for="item in industries" :key="item" :value="item">{{ item }}</option>
                                    </select>
                                    <span v-if="form.errors.industry" class="text-xs text-red-500 mt-1 block">{{ form.errors.industry }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Role / Position</label>
                                    <input
                                        v-model="form.role"
                                        type="text"
                                        placeholder="e.g. IT Manager"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.role" class="text-xs text-red-500 mt-1 block">{{ form.errors.role }}</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Date Created</label>
                                    <input
                                        v-model="form.joined_date"
                                        type="date"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                                    >
                                    <span v-if="form.errors.joined_date" class="text-xs text-red-500 mt-1 block">{{ form.errors.joined_date }}</span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Internal Note / Message</label>
                                <textarea
                                    v-model="form.message"
                                    rows="3"
                                    placeholder="Add any additional context or client notes here..."
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all resize-none"
                                ></textarea>
                                <span v-if="form.errors.message" class="text-xs text-red-500 mt-1 block">{{ form.errors.message }}</span>
                            </div>
                        </div>


                    </div>

                    <!-- Card Submission Action Bar -->
                    <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <Link
                            href="/customers"
                            class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-medium transition-colors inline-flex items-center"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="customerType === 'main' ? form.processing : subForm.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm disabled:opacity-50 transition-colors inline-flex items-center"
                        >
                            <span v-if="customerType === 'main' ? form.processing : subForm.processing" class="inline-block animate-spin mr-2 border-2 border-white border-t-transparent h-4 w-4 rounded-full"></span>
                            {{ customerType === 'main' ? 'Create' : 'Save Customer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    countries: {
        type: Array,
        default: () => [],
    },
    industries: {
        type: Array,
        default: () => [],
    },
    mainCustomers: {
        type: Array,
        default: () => [],
    },
});

const customerType = ref('main');

const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
const prefillName = urlParams ? (urlParams.get('name') || '') : '';

const form = useForm({
    name: prefillName,
    contact_name: '',
    email: '',
    phone: '',
    address: '',
    role: '',
    industry: '',
    country: '',
    message: '',
    joined_date: new Date().toISOString().split('T')[0],
});

const subForm = useForm({
    customer_id: '',
    name: '',
    email: '',
    phone: '',
    address: '',
    status: 'active',
});

const submitForm = () => {
    if (customerType.value === 'sub') {
        if (!subForm.customer_id) {
            subForm.errors.customer_id = 'Parent customer is required.';
            return;
        }
        subForm.post(`/customers/${subForm.customer_id}/sub-customers`);
        return;
    }
    form.post('/customers');
};
</script>
