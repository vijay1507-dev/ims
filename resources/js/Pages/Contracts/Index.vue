<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Contracts</h2>
                    <p class="text-sm text-gray-500 mt-1">Track and manage client agreements, service values, and durations</p>
                </div>
                <div v-if="$page.props.auth.user.permissions.includes('contracts.create')">
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors inline-flex items-center cursor-pointer"
                    >
                        <i class="fas fa-plus mr-2"></i>Create Contract
                    </button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 mb-6">
                <div class="flex flex-wrap items-end gap-4">
                    <!-- Search Input -->
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Search</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-sm"></i>
                            </span>
                            <input
                                v-model="filtersForm.search"
                                @input="handleSearch"
                                type="text"
                                placeholder="Number, subject, customer..."
                                class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-36">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                        <select
                            v-model="filtersForm.status"
                            @change="handleSearch"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                        >
                            <option value="All Status">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>

                    <!-- Contract Type Filter -->
                    <div class="w-52">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Contract Type</label>
                        <select
                            v-model="filtersForm.contract_type_id"
                            @change="handleSearch"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                        >
                            <option value="All Types">All Types</option>
                            <option v-for="type in allContractTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div class="w-40">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Start Date</label>
                        <input
                            v-model="filtersForm.start_date"
                            @change="handleSearch"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                        />
                    </div>

                    <!-- End Date -->
                    <div class="w-40">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">End Date</label>
                        <input
                            v-model="filtersForm.end_date"
                            @change="handleSearch"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                        />
                    </div>

                    <!-- Clear Filters Button -->
                    <div v-if="hasActiveFilters" class="shrink-0">
                        <label class="block text-xs font-bold text-transparent mb-2">Clear</label>
                        <button
                            @click="resetFilters"
                            class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 transition-colors cursor-pointer whitespace-nowrap"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>


            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left font-bold text-gray-600 w-16">#</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Contract Number</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Subject</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Assigned Customer</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Status</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600 min-w-48">Duration & Progress</th>
                                <th class="px-6 py-3 text-right font-bold text-gray-600">Contract Amount</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Contract Type</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(contract, index) in contracts.data" :key="contract.id" class="hover:bg-gray-55 transition-colors">
                                <td class="px-6 py-4 text-gray-500 font-medium">
                                    {{ (contracts.current_page - 1) * contracts.per_page + index + 1 }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-600">
                                    {{ contract.contract_number }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    {{ contract.subject }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    {{ contract.customer_name || 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="getStatusBadgeClass(contract.status)" class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border">
                                        {{ contract.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1.5 max-w-xs">
                                        <div class="flex items-center justify-between text-xs font-medium text-gray-500">
                                            <span>{{ getDurationString(contract.start_date, contract.end_date) }}</span>
                                            <span class="font-bold text-gray-700">
                                                {{ getElapsedPercentage(contract.start_date, contract.end_date, contract.status) }}%
                                            </span>
                                        </div>
                                        <!-- Progress Bar -->
                                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-200 shadow-3xs">
                                            <div
                                                :class="getProgressColor(contract.status)"
                                                class="h-full rounded-full transition-all duration-300"
                                                :style="{ width: `${getElapsedPercentage(contract.start_date, contract.end_date, contract.status)}%` }"
                                            ></div>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-medium">
                                            {{ formatDate(contract.start_date) }} to {{ formatDate(contract.end_date) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">
                                    {{ formatCurrency(contract.value) }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-semibold">
                                    {{ contract.contract_type ? contract.contract_type.name : 'Unknown Type' }}
                                </td>
                                <td class="px-6 py-4 text-center space-x-3 whitespace-nowrap">
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('contracts.view')"
                                        @click="openViewModal(contract)"
                                        title="View"
                                        class="text-gray-600 hover:text-gray-900 cursor-pointer text-sm"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('contracts.edit')"
                                        @click="openEditModal(contract)"
                                        title="Edit"
                                        class="text-indigo-600 hover:text-indigo-900 cursor-pointer text-sm"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('contracts.create')"
                                        @click="duplicateContract(contract)"
                                        title="Copy"
                                        class="text-teal-600 hover:text-teal-900 cursor-pointer text-sm"
                                    >
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('contracts.delete')"
                                        @click="deleteContract(contract)"
                                        title="Delete"
                                        class="text-rose-600 hover:text-rose-900 cursor-pointer text-sm"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!contracts.data || contracts.data.length === 0">
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">No contracts found matching parameters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="contracts.links && contracts.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between flex-wrap gap-4">
                    <div class="text-xs text-gray-500 font-medium font-bold">
                        Showing {{ contracts.from || 0 }} to {{ contracts.to || 0 }} of {{ contracts.total }} results
                    </div>
                    <div class="flex items-center space-x-1">
                        <Link
                            v-for="(link, i) in contracts.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-55 border border-gray-300',
                                'px-3 py-1.5 rounded-lg text-xs transition-all duration-150',
                                !link.url ? 'opacity-50 pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- Create / Edit Contract Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in overflow-y-auto">
                <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full overflow-hidden border border-gray-100 my-8">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-55 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-lg">
                            {{ isEditMode ? 'Modify Contract Agreement' : 'New Contract Agreement' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="submitForm" class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Subject -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Subject *</label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    placeholder="e.g. Annual Hosting & Support Services Contract"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                    required
                                />
                                <span v-if="form.errors.subject" class="text-xs text-rose-600 block mt-1">{{ form.errors.subject }}</span>
                            </div>

                            <!-- Value / Amount -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Contract Value ($) *</label>
                                <input
                                    v-model="form.value"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="e.g. 15000.00"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white font-bold"
                                    required
                                />
                                <span v-if="form.errors.value" class="text-xs text-rose-600 block mt-1">{{ form.errors.value }}</span>
                            </div>

                            <!-- Status Selection -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status *</label>
                                <select
                                    v-model="form.status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer font-semibold text-gray-800"
                                    required
                                >
                                    <option value="pending">Pending</option>
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                                <span v-if="form.errors.status" class="text-xs text-rose-600 block mt-1">{{ form.errors.status }}</span>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Start Date *</label>
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                    required
                                />
                                <span v-if="form.errors.start_date" class="text-xs text-rose-600 block mt-1">{{ form.errors.start_date }}</span>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">End Date *</label>
                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                    required
                                />
                                <span v-if="form.errors.end_date" class="text-xs text-rose-600 block mt-1">{{ form.errors.end_date }}</span>
                            </div>

                            <!-- Customer Name Text Field -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Customer Name (Optional)</label>
                                <input
                                    v-model="form.customer_name"
                                    type="text"
                                    placeholder="e.g. Acme Corporation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                />
                                <span v-if="form.errors.customer_name" class="text-xs text-rose-600 block mt-1">{{ form.errors.customer_name }}</span>
                            </div>

                            <!-- Contract Type Native Select -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Contract Type *</label>
                                <select
                                    v-model="form.contract_type_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                                    required
                                >
                                    <option value="" disabled>Select contract type...</option>
                                    <option
                                        v-for="type in allContractTypes"
                                        :key="type.id"
                                        :value="type.id"
                                        :disabled="type.status !== 'active'"
                                    >
                                        {{ type.name }}{{ type.status !== 'active' ? ' (Inactive)' : '' }}
                                    </option>
                                </select>
                                <p v-if="allContractTypes.length === 0" class="text-xs text-amber-600 mt-1">No contract types available. Please create one first.</p>
                                <span v-if="form.errors.contract_type_id" class="text-xs text-rose-600 block mt-1">{{ form.errors.contract_type_id }}</span>
                            </div>

                            <!-- Attachment Upload -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Attachment (PDF, Image, Doc)</label>
                                <input
                                    @change="handleFileChange"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                                />
                                <p v-if="isEditMode && existingAttachmentName" class="text-xs text-gray-500 mt-1">
                                    Current file: <span class="font-semibold text-gray-700">{{ existingAttachmentName }}</span> (uploading a new file will replace it)
                                </p>
                                <span v-if="form.errors.attachment" class="text-xs text-rose-600 block mt-1">{{ form.errors.attachment }}</span>
                            </div>
                        </div>

                        <!-- Modal footer inside form -->
                        <div class="flex items-center justify-end space-x-3 pt-5 border-t border-gray-100">
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
                                Save Contract
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- View Contract Modal -->
            <div v-if="isViewModalOpen" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in overflow-y-auto">
                <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full overflow-hidden border border-gray-100 my-8">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-55 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-lg">
                            Contract Details — {{ viewingContract?.contract_number }}
                        </h3>
                        <button @click="closeViewModal" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div v-if="viewingContract" class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Subject</label>
                                <p class="text-sm font-semibold text-gray-900">{{ viewingContract.subject }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Contract Number</label>
                                <p class="text-sm font-mono font-bold text-indigo-600">{{ viewingContract.contract_number }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                                <span :class="getStatusBadgeClass(viewingContract.status)" class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border">
                                    {{ viewingContract.status }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Contract Value</label>
                                <p class="text-sm font-bold text-gray-900">{{ formatCurrency(viewingContract.value) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Contract Type</label>
                                <p class="text-sm font-semibold text-gray-800">{{ viewingContract.contract_type ? viewingContract.contract_type.name : 'Unknown Type' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
                                <p class="text-sm text-gray-800">{{ formatDate(viewingContract.start_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">End Date</label>
                                <p class="text-sm text-gray-800">{{ formatDate(viewingContract.end_date) }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Assigned Customer</label>
                                <p class="text-sm text-gray-800">{{ viewingContract.customer_name || 'Unassigned' }}</p>
                            </div>

                            <!-- Attachment Preview -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Attachment</label>
                                <div v-if="viewingContract.attachment_url" class="border border-gray-200 rounded-lg p-3">
                                    <img
                                        v-if="isImageAttachment(viewingContract.attachment_name)"
                                        :src="viewingContract.attachment_url"
                                        :alt="viewingContract.attachment_name"
                                        class="max-h-64 rounded-lg border border-gray-100 mb-3"
                                    />
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600 font-medium truncate">
                                            <i class="fas fa-paperclip mr-1"></i>{{ viewingContract.attachment_name }}
                                        </span>
                                        <a
                                            :href="viewingContract.attachment_url"
                                            target="_blank"
                                            class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs cursor-pointer"
                                        >
                                            <i class="fas fa-download mr-1"></i>Open / Download
                                        </a>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-gray-400">No attachment uploaded for this contract.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-5 border-t border-gray-100">
                            <button
                                @click="closeViewModal"
                                class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    contracts: {
        type: Object,
        required: true,
    },
    activeContractTypes: {
        type: Array,
        required: true,
    },
    allContractTypes: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    }
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref(null);
const existingAttachmentName = ref('');

const isViewModalOpen = ref(false);
const viewingContract = ref(null);

const filtersForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'All Status',
    contract_type_id: props.filters.contract_type_id || 'All Types',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const form = useForm({
    subject: '',
    value: 0,
    start_date: '',
    end_date: '',
    status: 'pending',
    contract_type_id: '',
    customer_name: '',
    attachment: null,
});



const hasActiveFilters = computed(() => {
    return filtersForm.search ||
        filtersForm.status !== 'All Status' ||
        filtersForm.contract_type_id !== 'All Types' ||
        filtersForm.start_date ||
        filtersForm.end_date;
});

let debounceTimer = null;
const handleSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/contracts', {
            search: filtersForm.search,
            status: filtersForm.status,
            contract_type_id: filtersForm.contract_type_id,
            start_date: filtersForm.start_date,
            end_date: filtersForm.end_date,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
};

const resetFilters = () => {
    filtersForm.search = '';
    filtersForm.status = 'All Status';
    filtersForm.contract_type_id = 'All Types';
    filtersForm.start_date = '';
    filtersForm.end_date = '';
    handleSearch();
};

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    existingAttachmentName.value = '';
    isEditMode.value = false;
    editingId.value = null;
    isModalOpen.value = true;
};

const openEditModal = (contract) => {
    form.clearErrors();
    form.subject = contract.subject;
    form.value = contract.value;
    form.start_date = contract.start_date;
    form.end_date = contract.end_date;
    form.status = contract.status;
    form.contract_type_id = contract.contract_type_id;
    form.customer_name = contract.customer_name || '';
    form.attachment = null;
    existingAttachmentName.value = contract.attachment_name || '';

    isEditMode.value = true;
    editingId.value = contract.id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleFileChange = (event) => {
    form.attachment = event.target.files[0] || null;
};

const submitForm = () => {
    const pageNum = props.contracts.current_page;
    if (isEditMode.value) {
        form.transform((data) => ({
            ...data,
            page: pageNum,
            _method: 'put',
        })).post(`/contracts/${editingId.value}`, {
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/contracts', {
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    }
};

const openViewModal = (contract) => {
    viewingContract.value = contract;
    isViewModalOpen.value = true;
};

const closeViewModal = () => {
    isViewModalOpen.value = false;
    viewingContract.value = null;
};

const isImageAttachment = (filename) => {
    if (!filename) return false;
    return /\.(jpe?g|png|gif|webp)$/i.test(filename);
};

const duplicateContract = (contract) => {
    if (confirm(`Do you want to duplicate contract ${contract.contract_number}?`)) {
        router.post(`/contracts/${contract.id}/duplicate`, {}, {
            preserveScroll: true,
        });
    }
};

const deleteContract = (contract) => {
    if (confirm(`Are you sure you want to delete contract ${contract.contract_number}?`)) {
        router.delete(`/contracts/${contract.id}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatCurrency = (val) => {
    return '$' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getStatusBadgeClass = (status) => {
    const s = String(status).toLowerCase().trim();
    if (s === 'active') {
        return 'bg-emerald-50 text-emerald-700 border-emerald-100';
    }
    if (s === 'pending') {
        return 'bg-amber-50 text-amber-700 border-amber-100';
    }
    if (s === 'expired') {
        return 'bg-gray-100 text-gray-700 border-gray-200';
    }
    if (s === 'terminated') {
        return 'bg-rose-50 text-rose-700 border-rose-100';
    }
    return 'bg-gray-50 text-gray-700 border-gray-200';
};

const getProgressColor = (status) => {
    const s = String(status).toLowerCase().trim();
    if (s === 'active') return 'bg-indigo-600';
    if (s === 'expired') return 'bg-gray-500';
    if (s === 'terminated') return 'bg-rose-500';
    return 'bg-amber-500';
};

const getDurationString = (start, end) => {
    if (!start || !end) return '—';
    const startDate = new Date(start);
    const endDate = new Date(end);
    let years = endDate.getFullYear() - startDate.getFullYear();
    let months = endDate.getMonth() - startDate.getMonth();
    if (months < 0) {
        years--;
        months += 12;
    }
    const parts = [];
    if (years > 0) parts.push(`${years} ${years === 1 ? 'Year' : 'Years'}`);
    if (months > 0) parts.push(`${months} ${months === 1 ? 'Month' : 'Months'}`);
    if (parts.length === 0) {
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        parts.push(`${diffDays} ${diffDays === 1 ? 'Day' : 'Days'}`);
    }
    return parts.join(' ');
};

const getElapsedPercentage = (start, end, status) => {
    if (status === 'terminated' || status === 'expired') return 100;
    
    const now = new Date();
    const startDate = new Date(start);
    const endDate = new Date(end);
    
    if (now < startDate) return 0;
    if (now >= endDate) return 100;
    
    const totalDuration = endDate - startDate;
    if (totalDuration <= 0) return 100;
    
    const elapsed = now - startDate;
    const pct = Math.round((elapsed / totalDuration) * 100);
    return Math.min(100, Math.max(0, pct));
};
</script>
