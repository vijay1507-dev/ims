<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Contract Types</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage reusable categories for client and vendor agreements</p>
                </div>
                <div v-if="$page.props.auth.user.permissions.includes('contract_types.create')">
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors inline-flex items-center cursor-pointer"
                    >
                        <i class="fas fa-plus mr-2"></i>Create Contract Type
                    </button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-1 flex-col md:flex-row items-stretch md:items-center gap-4">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </span>
                        <input
                            v-model="filtersForm.search"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search by contract type name..."
                            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                        />
                    </div>

                    <div class="w-full md:w-48">
                        <select
                            v-model="filtersForm.status"
                            @change="handleSearch"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer"
                        >
                            <option value="All Status">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div v-if="filtersForm.search || filtersForm.status !== 'All Status'">
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 transition-colors cursor-pointer"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left font-bold text-gray-600 w-16">#</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Contract Type Name</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Description</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Status</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Created Date</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(type, index) in contractTypes.data" :key="type.id" class="hover:bg-gray-55 transition-colors">
                                <td class="px-6 py-4 text-gray-550 font-medium">{{ (contractTypes.current_page - 1) * contractTypes.per_page + index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ type.name }}</td>
                                <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ type.description || '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[
                                        type.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-600 border border-gray-200',
                                        'inline-flex px-2.5 py-1 rounded-full text-xs font-bold border'
                                    ]">
                                        {{ type.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 font-medium">{{ formatDate(type.created_at) }}</td>
                                <td class="px-6 py-4 text-center space-x-3">
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('contract_types.edit')"
                                        @click="openEditModal(type)"
                                        class="text-indigo-600 hover:text-indigo-900 font-semibold cursor-pointer text-xs"
                                    >
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('contract_types.edit')"
                                        @click="toggleStatus(type)"
                                        :class="[
                                            type.status === 'active' ? 'text-amber-600 hover:text-amber-900' : 'text-emerald-600 hover:text-emerald-900',
                                            'font-semibold cursor-pointer text-xs'
                                        ]"
                                    >
                                        <i :class="[type.status === 'active' ? 'fas fa-eye-slash' : 'fas fa-eye', 'mr-1']"></i>
                                        {{ type.status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('contract_types.delete')"
                                        @click="deleteContractType(type)"
                                        class="text-rose-600 hover:text-rose-900 font-semibold cursor-pointer text-xs"
                                    >
                                        <i class="fas fa-trash-alt mr-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!contractTypes.data || contractTypes.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No contract types found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Component -->
                <div v-if="contractTypes.links && contractTypes.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between flex-wrap gap-4">
                    <div class="text-xs text-gray-500 font-medium">
                        Showing {{ contractTypes.from || 0 }} to {{ contractTypes.to || 0 }} of {{ contractTypes.total }} results
                    </div>
                    <div class="flex items-center space-x-1">
                        <Link
                            v-for="(link, i) in contractTypes.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                                'px-3 py-1.5 rounded-lg text-xs transition-all duration-150',
                                !link.url ? 'opacity-50 pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- Create / Edit Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in">
                <div class="bg-white rounded-xl shadow-lg max-w-md w-full overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-lg">{{ isEditMode ? 'Edit Contract Type' : 'Create Contract Type' }}</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Contract Type Name *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Master Service Agreement (MSA)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                required
                            />
                            <span v-if="form.errors.name" class="text-xs text-rose-600 block mt-1">{{ form.errors.name }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Details about this contract type template"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                            ></textarea>
                            <span v-if="form.errors.description" class="text-xs text-rose-600 block mt-1">{{ form.errors.description }}</span>
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
                                Save Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    contractTypes: {
        type: Object,
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

const filtersForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'All Status',
});

const form = useForm({
    name: '',
    description: '',
    status: 'active',
});

let debounceTimer = null;
const handleSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/contract-types', {
            search: filtersForm.search,
            status: filtersForm.status,
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
    handleSearch();
};

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isEditMode.value = false;
    editingId.value = null;
    isModalOpen.value = true;
};

const openEditModal = (type) => {
    form.clearErrors();
    form.name = type.name;
    form.description = type.description;
    form.status = type.status;
    isEditMode.value = true;
    editingId.value = type.id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitForm = () => {
    const pageNum = props.contractTypes.current_page;
    if (isEditMode.value) {
        form.put(`/contract-types/${editingId.value}`, {
            data: {
                ...form.data(),
                page: pageNum,
            },
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/contract-types', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (type) => {
    router.post(`/contract-types/${type.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

const deleteContractType = (type) => {
    if (confirm(`Are you sure you want to delete the contract type "${type.name}"?`)) {
        router.delete(`/contract-types/${type.id}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>
