<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto p-6">
            <!-- Breadcrumb Navigation Header -->
            <!-- Back Button -->
            <Link 
                :href="`/customers?page=${returnPage}`"
                class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors inline-flex items-center gap-2 mb-6"
            >
                <i class="fas fa-arrow-left"></i>
                Back to Customers
            </Link>

            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Modify Enterprise Client Record</h2>
                        <p class="text-gray-600 mt-1">Adjust operational telemetry flags, hardware lifecycles, and taxation invoice flows for <strong class="text-indigo-900">{{ customer.name }}</strong>.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form Card Container (2 Columns wide) -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                        <form @submit.prevent="submitForm">
                            <div class="p-6 sm:p-8 space-y-6">
                                <!-- Profile Layout Section -->
                                <div class="border-b border-gray-100 pb-6">
                                    <h3 class="text-base font-bold text-gray-900 flex items-center">
                                        <i class="fas fa-building text-indigo-600 mr-2 text-sm"></i>Organization Parameters
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Primary identifier and communication addresses.</p>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6 mt-4">
                                    
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Full Name (Contact Person)</label>
                                            <input
                                                v-model="form.contact_name"
                                                type="text"
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                            >
                                            <span v-if="form.errors.contact_name" class="text-xs text-rose-500 mt-1 block">{{ form.errors.contact_name }}</span>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Company Name</label>
                                            <input
                                                v-model="form.name"
                                                type="text"
                                                required
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                            >
                                            <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1 block">{{ form.errors.name }}</span>
                                        </div>


                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Primary Email</label>
                                            <input
                                                v-model="form.email"
                                                type="email"
                                                required
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                            >
                                            <span v-if="form.errors.email" class="text-xs text-rose-500 mt-1 block">{{ form.errors.email }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Contact Number</label>
                                            <input
                                                v-model="form.phone"
                                                type="text"
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                            >
                                            <span v-if="form.errors.phone" class="text-xs text-rose-500 mt-1 block">{{ form.errors.phone }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Country</label>
                                            <select
                                                v-model="form.country"
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium"
                                            >
                                                <option value="">Select country</option>
                                                <option v-for="item in countries" :key="item" :value="item">{{ item }}</option>
                                            </select>
                                            <span v-if="form.errors.country" class="text-xs text-rose-500 mt-1 block">{{ form.errors.country }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Industry Segment</label>
                                            <select
                                                v-model="form.industry"
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all font-medium"
                                            >
                                                <option value="">Select industry</option>
                                                <option v-for="item in industries" :key="item" :value="item">{{ item }}</option>
                                            </select>
                                            <span v-if="form.errors.industry" class="text-xs text-rose-500 mt-1 block">{{ form.errors.industry }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Role / Position</label>
                                            <input
                                                v-model="form.role"
                                                type="text"
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                            >
                                            <span v-if="form.errors.role" class="text-xs text-rose-500 mt-1 block">{{ form.errors.role }}</span>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Date Created</label>
                                            <input
                                                v-model="form.joined_date"
                                                type="date"
                                                required
                                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                                            >
                                            <span v-if="form.errors.joined_date" class="text-xs text-rose-500 mt-1 block">{{ form.errors.joined_date }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-6">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Internal Note / Message</label>
                                        <textarea
                                            v-model="form.message"
                                            rows="3"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium resize-none"
                                        ></textarea>
                                        <span v-if="form.errors.message" class="text-xs text-rose-500 mt-1 block">{{ form.errors.message }}</span>
                                    </div>
                                </div>


                            </div>

                            <!-- Card Submission Action Bar -->
                            <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                                <Link
                                    :href="`/customers?page=${returnPage}`"
                                    class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-bold transition-colors inline-flex items-center"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-bold shadow-xs disabled:opacity-50 transition-colors inline-flex items-center cursor-pointer"
                                >
                                    <span v-if="form.processing" class="inline-block animate-spin mr-2 border-2 border-white border-t-transparent h-4 w-4 rounded-full"></span>
                                    Save Base Modifications
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Sub-Customers / Locations -->
                    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Sub-Customers / Locations</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Locations billed independently under this main account.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 rounded-full text-xs font-bold">{{ subCustomers ? subCustomers.length : 0 }} Locations</span>
                                <Link
                                    :href="`/customers/${customer.id}/sub-customers/create`"
                                    class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-colors inline-flex items-center"
                                >
                                    <i class="fas fa-plus mr-1.5"></i>Add Sub-Customer
                                </Link>
                            </div>
                        </div>
                        <div class="p-6">
                            <div v-if="!subCustomers || subCustomers.length === 0" class="text-center py-6 text-gray-400 text-xs italic border-2 border-dashed border-gray-100 rounded-lg">
                                No sub-customers/locations yet. Add one to start tracking location-specific payments.
                            </div>
                            <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-2.5">Name</th>
                                            <th class="px-4 py-2.5">Email</th>
                                            <th class="px-4 py-2.5">Payments</th>
                                            <th class="px-4 py-2.5">Status</th>
                                            <th class="px-4 py-2.5 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-xs">
                                        <tr v-for="sc in subCustomers" :key="sc.id" class="hover:bg-gray-50/50">
                                            <td class="px-4 py-3 font-bold text-gray-900">{{ sc.name }}</td>
                                            <td class="px-4 py-3 text-gray-500">{{ sc.email || 'N/A' }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ sc.payments_count }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase" :class="sc.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'">
                                                    {{ sc.status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                                <Link :href="`/sub-customers/${sc.id}`" class="text-indigo-600 hover:text-indigo-900 font-bold">View</Link>
                                                <Link :href="`/sub-customers/${sc.id}/edit`" class="text-gray-600 hover:text-gray-900 font-bold">Edit</Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Grid Rendering Attached Inventory Assets -->
                    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Assigned Inventory Assets</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Physical hardware tracking nodes matching client identity allocations.</p>
                            </div>
                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 rounded-full text-xs font-bold">{{ devices ? devices.length : 0 }} Units</span>
                        </div>
                        <div class="p-6">
                            <div v-if="!devices || devices.length === 0" class="text-center py-6 text-gray-400 text-xs italic border-2 border-dashed border-gray-100 rounded-lg">
                                No dedicated inventory assets currently allocated. Provision items via the command center shortcuts.
                            </div>
                            <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-2.5">Asset ID</th>
                                            <th class="px-4 py-2.5">Category</th>
                                            <th class="px-4 py-2.5">Model Identifier</th>
                                            <th class="px-4 py-2.5">Serial Vector</th>
                                            <th class="px-4 py-2.5">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-xs">
                                        <tr v-for="ast in devices" :key="ast.id" class="hover:bg-gray-50/50">
                                            <td class="px-4 py-3 font-mono font-bold text-indigo-600">{{ ast.asset_id }}</td>
                                            <td class="px-4 py-3 font-bold text-gray-800">{{ ast.type_name }}</td>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ ast.name_model }}</td>
                                            <td class="px-4 py-3 font-mono text-gray-500">{{ ast.serial_license || 'N/A' }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase" :class="ast.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                                                    {{ ast.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Grid Rendering Attached Invoices -->
                    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Taxation Invoice Streams</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Recurring billing strings clearing directly through central accounting registers.</p>
                            </div>
                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">{{ invoices ? invoices.length : 0 }} Ledgers</span>
                        </div>
                        <div class="p-6">
                            <div v-if="!invoices || invoices.length === 0" class="text-center py-6 text-gray-400 text-xs italic border-2 border-dashed border-gray-100 rounded-lg">
                                No dedicated taxation invoices triggered. Initiate item serial arrays using the shortcut suite.
                            </div>
                            <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-2.5">Invoice #</th>
                                            <th class="px-4 py-2.5">Gross Total</th>
                                            <th class="px-4 py-2.5">Tax Component</th>
                                            <th class="px-4 py-2.5">Clearing Date</th>
                                            <th class="px-4 py-2.5">State Marker</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-xs">
                                        <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-50/50">
                                            <td class="px-4 py-3 font-mono font-bold text-indigo-600">{{ inv.invoice_number }}</td>
                                            <td class="px-4 py-3 font-bold text-indigo-950">{{ inv.total }}</td>
                                            <td class="px-4 py-3 font-medium text-gray-500">{{ inv.tax }}</td>
                                            <td class="px-4 py-3 text-rose-700 font-bold">{{ inv.due_date }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase" :class="inv.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                                                    {{ inv.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Command Center Shortcuts Sidebar (1 Column wide) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-xs">
                        <div class="flex items-center mb-4 pb-2 border-b border-gray-100">
                            <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600 mr-3">
                                <i class="fas fa-bolt text-base w-4 text-center"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900">Provisioning Actions</h4>
                                <p class="text-[11px] text-gray-500">Staged immediate module links</p>
                            </div>
                        </div>


                        <div class="space-y-3">
                            <!-- Shortcut Link: Create Inventory Page -->
                            <Link
                                :href="`/inventory/create?customer_name=${encodeURIComponent(customer.contact_name || customer.name)}&redirect_customer_id=${customer.id}`"
                                class="w-full py-3 px-4 bg-indigo-600 text-white rounded-lg text-xs font-bold shadow-xs hover:bg-indigo-700 transition-all flex items-center justify-between group cursor-pointer"
                            >
                                <span class="flex items-center">
                                    <i class="fas fa-boxes mr-2 text-indigo-200 group-hover:scale-110 transition-transform"></i>
                                    Add Inventory
                                </span>
                                <i class="fas fa-arrow-right text-[10px] opacity-80 group-hover:translate-x-0.5 transition-transform"></i>
                            </Link>

                            <!-- Shortcut Link: Record Payment -->
                            <Link
                                :href="`/payments/create?customer=${encodeURIComponent(customer.contact_name || customer.name)}&redirect_customer_id=${customer.id}`"
                                class="w-full py-3 px-4 bg-amber-600 text-white rounded-lg text-xs font-bold shadow-xs hover:bg-amber-700 transition-all flex items-center justify-between group cursor-pointer"
                            >
                                <span class="flex items-center">
                                    <i class="fas fa-receipt mr-2 text-amber-200 group-hover:scale-110 transition-transform"></i>
                                    Record Payment
                                </span>
                                <i class="fas fa-arrow-right text-[10px] opacity-80 group-hover:translate-x-0.5 transition-transform"></i>
                            </Link>

                            <!-- Shortcut Link: Add Sub-Customer -->
                            <Link
                                :href="`/customers/${customer.id}/sub-customers/create`"
                                class="w-full py-3 px-4 bg-emerald-600 text-white rounded-lg text-xs font-bold shadow-xs hover:bg-emerald-700 transition-all flex items-center justify-between group cursor-pointer"
                            >
                                <span class="flex items-center">
                                    <i class="fas fa-sitemap mr-2 text-emerald-200 group-hover:scale-110 transition-transform"></i>
                                    Add Sub-Customer
                                </span>
                                <i class="fas fa-arrow-right text-[10px] opacity-80 group-hover:translate-x-0.5 transition-transform"></i>
                            </Link>

                            <!-- Shortcut Link: Add Subscription -->
                            <Link
                                :href="`/subscriptions/create?customer_name=${encodeURIComponent(customer.contact_name || customer.name)}&customer_email=${encodeURIComponent(customer.email)}&redirect_customer_id=${customer.id}`"
                                class="w-full py-3 px-4 bg-sky-600 text-white rounded-lg text-xs font-bold shadow-xs hover:bg-sky-700 transition-all flex items-center justify-between group cursor-pointer"
                            >
                                <span class="flex items-center">
                                    <i class="fas fa-plus-circle mr-2 text-sky-200 group-hover:scale-110 transition-transform"></i>
                                    Add Subscription
                                </span>
                                <i class="fas fa-arrow-right text-[10px] opacity-80 group-hover:translate-x-0.5 transition-transform"></i>
                            </Link>
                        </div>
                    </div>
                </div>
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
    devices: {
        type: Array,
        default: () => [],
    },
    invoices: {
        type: Array,
        default: () => [],
    },
    subCustomers: {
        type: Array,
        default: () => [],
    },
    countries: {
        type: Array,
        default: () => [],
    },
    industries: {
        type: Array,
        default: () => [],
    },
    returnPage: {
        type: Number,
        default: 1,
    },
});

const form = useForm({
    name: props.customer.name,
    contact_name: props.customer.contact_name || '',
    email: props.customer.email,
    phone: props.customer.phone || '',
    address: props.customer.address || '',
    role: props.customer.role || '',
    industry: props.customer.industry || '',
    country: props.customer.country || '',
    message: props.customer.message || '',
    joined_date: props.customer.joined_date || '',
});

const submitForm = () => {
    form.put(`/customers/${props.customer.id}?page=${props.returnPage}`);
};
</script>
