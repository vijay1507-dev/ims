<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto animate-fade-in">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Payment Channels</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage transactional gateway methods and settlement options</p>
                </div>
                <div v-if="$page.props.auth.user.permissions.includes('payment_channels.create')">
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-xs transition-colors inline-flex items-center cursor-pointer border-0 outline-none"
                    >
                        <i class="fas fa-plus mr-2"></i>Create Payment Channel
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
                            placeholder="Search by name or code..."
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
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Channel Name</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Gateway Code</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Description</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Status</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-600">Created Date</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(channel, index) in paymentChannels.data" :key="channel.id" class="hover:bg-gray-55 transition-colors">
                                <td class="px-6 py-4 text-gray-550 font-medium">{{ (paymentChannels.current_page - 1) * paymentChannels.per_page + index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ channel.name }}</td>
                                <td class="px-6 py-4 font-mono text-gray-600 text-xs bg-gray-50 px-2 py-0.5 rounded border border-gray-100 inline-block mt-3.5">{{ channel.code }}</td>
                                <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ channel.description || '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[
                                        channel.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-600 border border-gray-200',
                                        'inline-flex px-2.5 py-1 rounded-full text-xs font-bold border'
                                    ]">
                                        {{ channel.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 font-medium">{{ formatDate(channel.created_at) }}</td>
                                <td class="px-6 py-4 text-center space-x-3">
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('payment_channels.edit')"
                                        @click="openEditModal(channel)"
                                        class="text-indigo-600 hover:text-indigo-900 font-semibold cursor-pointer text-xs bg-transparent border-0 outline-none"
                                    >
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('payment_channels.edit')"
                                        @click="toggleStatus(channel)"
                                        :class="[
                                            channel.status === 'active' ? 'text-amber-600 hover:text-amber-900' : 'text-emerald-600 hover:text-emerald-900',
                                            'font-semibold cursor-pointer text-xs bg-transparent border-0 outline-none'
                                        ]"
                                    >
                                        <i :class="[channel.status === 'active' ? 'fas fa-eye-slash' : 'fas fa-eye', 'mr-1']"></i>
                                        {{ channel.status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.permissions.includes('payment_channels.delete')"
                                        @click="deleteChannel(channel)"
                                        class="text-rose-600 hover:text-rose-900 font-semibold cursor-pointer text-xs bg-transparent border-0 outline-none"
                                    >
                                        <i class="fas fa-trash-alt mr-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!paymentChannels.data || paymentChannels.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No payment channels found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Component -->
                <div v-if="paymentChannels.links && paymentChannels.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between flex-wrap gap-4">
                    <div class="text-xs text-gray-500 font-medium">
                        Showing {{ paymentChannels.from || 0 }} to {{ paymentChannels.to || 0 }} of {{ paymentChannels.total }} results
                    </div>
                    <div class="flex items-center space-x-1">
                        <Link
                            v-for="(link, i) in paymentChannels.links"
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
                        <h3 class="font-bold text-gray-900 text-lg">{{ isEditMode ? 'Edit Payment Channel' : 'Create Payment Channel' }}</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 cursor-pointer bg-transparent border-0">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Channel Name *</label>
                            <input
                                v-model="form.name"
                                @input="handleNameInput"
                                type="text"
                                placeholder="e.g. Stripe Connect"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                required
                            />
                            <span v-if="form.errors.name" class="text-xs text-rose-600 block mt-1">{{ form.errors.name }}</span>
                            <span v-if="form.errors.code" class="text-xs text-rose-600 block mt-1">{{ form.errors.code }}</span>
                        </div>


                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Details about this gateway type"
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
                                class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-bold transition-colors cursor-pointer bg-white"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition-colors cursor-pointer inline-flex items-center border-0 outline-none"
                            >
                                <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                Save Channel
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
    paymentChannels: {
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
    code: '',
    description: '',
    status: 'active',
});

let debounceTimer = null;
const handleSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/payment-channels', {
            search: filtersForm.search,
            status: filtersForm.status,
        }, {
            preserveState: true,
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

const openEditModal = (channel) => {
    form.clearErrors();
    form.name = channel.name;
    form.code = channel.code;
    form.description = channel.description;
    form.status = channel.status;
    isEditMode.value = true;
    editingId.value = channel.id;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleNameInput = () => {
    if (!isEditMode.value) {
        form.code = form.name
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/(^_+|_+$)/g, '');
    }
};

const submitForm = () => {
    const pageNum = props.paymentChannels.current_page;
    if (isEditMode.value) {
        form.put(`/payment-channels/${editingId.value}`, {
            data: {
                ...form.data(),
                page: pageNum,
            },
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/payment-channels', {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (channel) => {
    router.post(`/payment-channels/${channel.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

const deleteChannel = (channel) => {
    if (confirm(`Are you sure you want to permanently delete the "${channel.name}" payment channel?`)) {
        router.delete(`/payment-channels/${channel.id}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>
