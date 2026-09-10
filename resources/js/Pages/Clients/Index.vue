<template>
    <AppLayout>
        <div class="p-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Client Management</h2>
                    <p class="text-gray-600 mt-1">Manage client tenants, subdomains, and administrator access</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button
                        @click="showAddModal = true"
                        type="button"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors inline-flex items-center cursor-pointer"
                    >
                        <i class="fas fa-plus mr-2"></i>Add Client
                    </button>
                </div>
            </div>

            <!-- Upper Metric Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <KpiCard
                    title="Total Clients"
                    :value="metrics.total_clients"
                    icon="fas fa-building"
                    icon-bg-class="bg-indigo-100"
                    icon-color-class="text-indigo-600"
                />
                <KpiCard
                    title="Active Domains"
                    :value="metrics.active_domains"
                    icon="fas fa-globe"
                    icon-bg-class="bg-green-100"
                    icon-color-class="text-green-600"
                />
                <KpiCard
                    title="Tenant Users"
                    :value="metrics.total_users"
                    icon="fas fa-user-shield"
                    icon-bg-class="bg-purple-100"
                    icon-color-class="text-purple-600"
                />
                <KpiCard
                    title="Total Customers"
                    :value="metrics.total_customers"
                    icon="fas fa-users"
                    icon-bg-class="bg-amber-100"
                    icon-color-class="text-amber-600"
                />
            </div>

            <!-- Integrated Data Table with Live Filter -->
            <DataTable
                title="Clients List"
                :columns="[
                    { label: 'ID', key: 'id' },
                    { label: 'Client / Tenant Name', key: 'name' },
                    { label: 'Domain / Subdomain', key: 'domain' },
                    { label: 'Admin Contact', key: 'admin_email' },
                    { label: 'Users', key: 'users_count' },
                    { label: 'Customers', key: 'customers_count' },
                    { label: 'Onboarded Date', key: 'created_at' },
                    { label: 'Actions', key: 'actions', align: 'right' }
                ]"
                :has-rows="clients.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="clients.length"
            >
                <template #filters>
                    <!-- Search Filter -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search client name or domain..."
                            class="w-48 sm:w-64 pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all"
                            @input="triggerFilter"
                        >
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400"></i>
                    </div>
                </template>

                <template #rows>
                    <tr
                        v-for="client in paginatedClients"
                        :key="client.id"
                        class="table-row hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                            #{{ client.id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ client.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-medium">
                            <div class="flex flex-col items-start space-y-1">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
                                    <i class="fas fa-link mr-1.5 text-xs"></i>{{ client.domain }}
                                </span>
                                <div>
                                    <span v-if="client.is_active" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Active
                                    </span>
                                    <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1"></span> Disabled
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <div>
                                <div class="font-medium text-gray-800">{{ client.admin_name }}</div>
                                <div class="text-xs text-gray-400">{{ client.admin_email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 text-xs font-bold">
                                {{ client.users_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">
                                {{ client.customers_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ client.created_at }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <!-- Toggle Enable/Disable Domain Button -->
                            <button
                                type="button"
                                :class="[
                                    'px-3 py-1.5 text-xs font-bold rounded-lg transition-colors inline-flex items-center cursor-pointer border shadow-xs',
                                    client.is_active
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                        : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100'
                                ]"
                                :title="client.is_active ? 'Disable Domain' : 'Enable Domain'"
                                @click="toggleDomain(client)"
                            >
                                <i :class="['fas mr-1.5', client.is_active ? 'fa-check-circle text-emerald-600' : 'fa-ban text-rose-600']"></i>
                                {{ client.is_active ? 'Enabled' : 'Disabled' }}
                            </button>

                            <!-- Delete Button -->
                            <button
                                type="button"
                                class="text-red-600 hover:text-red-900 p-1.5 bg-red-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer border border-red-100"
                                title="Delete Client Tenant"
                                @click="deleteClient(client.id, client.name)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </DataTable>

            <!-- Add Client Modal -->
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Add New Client Tenant</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 cursor-pointer bg-transparent border-0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form @submit.prevent="submitCreate">
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company / Organization Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Acme Corp"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                >
                                <div v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sub-domain Name</label>
                                <div class="flex rounded-lg shadow-xs">
                                    <input
                                        v-model="form.subdomain"
                                        @input="handleSubdomainInput"
                                        type="text"
                                        required
                                        placeholder="acme"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-l-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                    >
                                    <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 text-gray-500 text-xs font-medium">
                                        .{{ baseHost }}
                                    </span>
                                </div>
                                <p v-if="formattedSubdomain" class="mt-1 text-xs text-indigo-600 font-medium">
                                    Domain: <span class="font-bold underline">{{ formattedSubdomain }}</span>
                                </p>
                                <div v-if="form.errors.subdomain" class="mt-1 text-xs text-red-600">{{ form.errors.subdomain }}</div>
                            </div>

                            <hr class="border-gray-100 my-2" />

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Primary Admin Name</label>
                                <input
                                    v-model="form.admin_name"
                                    type="text"
                                    required
                                    placeholder="John Doe"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                >
                                <div v-if="form.errors.admin_name" class="mt-1 text-xs text-red-600">{{ form.errors.admin_name }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Primary Admin Email</label>
                                <input
                                    v-model="form.admin_email"
                                    type="email"
                                    required
                                    placeholder="admin@acme.com"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                >
                                <div v-if="form.errors.admin_email" class="mt-1 text-xs text-red-600">{{ form.errors.admin_email }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Password</label>
                                <input
                                    v-model="form.admin_password"
                                    type="password"
                                    required
                                    placeholder="••••••••"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                >
                                <div v-if="form.errors.admin_password" class="mt-1 text-xs text-red-600">{{ form.errors.admin_password }}</div>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-100 flex justify-end space-x-3 bg-gray-50">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                                :disabled="form.processing"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Creating Client...
                                </span>
                                <span v-else>
                                    Create Client Tenant
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import KpiCard from '../../Components/Cards/KpiCard.vue';
import DataTable from '../../Components/Tables/DataTable.vue';

const props = defineProps({
    clients: {
        type: Array,
        required: true,
    },
    metrics: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref(props.filters.search || '');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const showAddModal = ref(false);

const form = useForm({
    name: '',
    subdomain: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
});

const baseHost = computed(() => {
    if (typeof window !== 'undefined') {
        const host = window.location.hostname;
        return host.startsWith('www.') ? host.slice(4) : host;
    }
    return 'localhost';
});

const formattedSubdomain = computed(() => {
    if (!form.subdomain) return '';
    const cleanSubdomain = form.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '');
    return cleanSubdomain ? `${cleanSubdomain}.${baseHost.value}` : '';
});

const handleSubdomainInput = (e) => {
    form.subdomain = e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
};

const paginatedClients = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.clients.slice(start, end);
});

let filterTimeout = null;
const triggerFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get('/clients', {
            search: searchQuery.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 250);
};

const closeModal = () => {
    showAddModal.value = false;
    form.reset();
    form.clearErrors();
};

const submitCreate = () => {
    form.post('/clients', {
        onSuccess: () => {
            closeModal();
        },
    });
};

const toggleDomain = (client) => {
    const actionText = client.is_active ? 'disable' : 'enable';
    if (confirm(`Are you sure you want to ${actionText} the domain '${client.domain}'? Disabled domains will be blocked from accessing the application.`)) {
        router.post(`/clients/${client.id}/toggle-domain`, {}, {
            preserveScroll: true,
        });
    }
};

const deleteClient = (id, name) => {
    if (confirm(`Are you sure you want to delete client '${name}' and all associated domains?`)) {
        router.delete(`/clients/${id}`, {
            preserveScroll: true,
        });
    }
};
</script>
