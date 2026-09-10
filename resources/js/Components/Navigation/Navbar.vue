<template>
    <nav class="bg-white border-b border-gray-200 fixed top-0 left-0 right-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <button
                        type="button"
                        class="md:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100"
                        @click="toggleSidebar"
                    >
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="flex items-center ml-4 md:ml-0">
                        <i class="fas fa-chart-line text-indigo-600 text-2xl mr-3"></i>
                        <h1 class="text-xl font-semibold text-gray-900">SaaS Manager</h1>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    <div v-if="(!page.props.isCentralDomain || isSuperadmin) && showSearch" class="hidden md:block relative search-container">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="searchPlaceholder"
                                class="w-80 pl-10 pr-8 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all"
                                @input="handleSearch"
                                @focus="isOpen = true"
                            >
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                            
                            <!-- Loading / Clear Indicator -->
                            <span v-if="loading" class="absolute right-3 top-2.5">
                                <i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i>
                            </span>
                            <button v-else-if="searchQuery" @click="clearSearch" class="absolute right-3 top-2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-times-circle text-sm"></i>
                            </button>
                        </div>

                        <!-- Dropdown Search Results Overlay -->
                        <div v-if="isOpen && searchQuery.length >= 2" class="absolute left-0 mt-2 w-[420px] bg-white rounded-xl shadow-xl border border-gray-200 py-3 z-50 max-h-[480px] overflow-y-auto">
                            <!-- Loading State -->
                            <div v-if="loading" class="px-4 py-3 text-center text-sm text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Searching records...
                            </div>

                            <!-- Empty Results State -->
                            <div v-else-if="hasNoResults" class="px-4 py-6 text-center">
                                <i class="fas fa-search text-gray-300 text-2xl mb-2"></i>
                                <p class="text-sm font-semibold text-gray-900">No matches found</p>
                                <p class="text-xs text-gray-500 mt-0.5">Try searching with other terms</p>
                            </div>

                            <!-- Results Groups -->
                            <div v-else class="space-y-3">
                                <!-- Customers Group -->
                                <div v-if="results.customers && results.customers.length > 0">
                                    <div class="px-4 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                                        Customers ({{ results.customers.length }})
                                    </div>
                                    <div class="mt-1 divide-y divide-gray-50">
                                        <Link
                                            v-for="item in results.customers"
                                            :key="item.id"
                                            :href="`/customers/${item.id}`"
                                            @click="closeSearch"
                                            class="flex items-center justify-between px-4 py-2 hover:bg-indigo-50/40 transition-colors group"
                                        >
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-indigo-600">{{ item.name }}</p>
                                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ item.contact_name }} — {{ item.email }}</p>
                                            </div>
                                            <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-indigo-400 transition-colors ml-3"></i>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Subscriptions Group -->
                                <div v-if="results.subscriptions && results.subscriptions.length > 0">
                                    <div class="px-4 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                                        Subscriptions ({{ results.subscriptions.length }})
                                    </div>
                                    <div class="mt-1 divide-y divide-gray-50">
                                        <Link
                                            v-for="item in results.subscriptions"
                                            :key="item.id"
                                            :href="`/subscriptions/${item.id}/edit`"
                                            @click="closeSearch"
                                            class="flex items-center justify-between px-4 py-2 hover:bg-green-50/40 transition-colors group"
                                        >
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-green-600">{{ item.plan_name }}</p>
                                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ item.customer_name }} — ${{ parseFloat(item.amount).toLocaleString() }}</p>
                                            </div>
                                            <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-green-400 transition-colors ml-3"></i>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Invoices Group -->
                                <div v-if="results.invoices && results.invoices.length > 0">
                                    <div class="px-4 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                                        Invoices ({{ results.invoices.length }})
                                    </div>
                                    <div class="mt-1 divide-y divide-gray-50">
                                        <Link
                                            v-for="item in results.invoices"
                                            :key="item.id"
                                            :href="`/invoices/${item.id}/edit`"
                                            @click="closeSearch"
                                            class="flex items-center justify-between px-4 py-2 hover:bg-indigo-50/40 transition-colors group"
                                        >
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-indigo-600">{{ item.invoice_number }}</p>
                                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ item.customer_name }} — ₹{{ parseFloat(item.total).toLocaleString() }}</p>
                                            </div>
                                            <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-indigo-400 transition-colors ml-3"></i>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Payments Group -->
                                <div v-if="results.payments && results.payments.length > 0">
                                    <div class="px-4 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                                        Transactions ({{ results.payments.length }})
                                    </div>
                                    <div class="mt-1 divide-y divide-gray-50">
                                        <Link
                                            v-for="item in results.payments"
                                            :key="item.id"
                                            :href="`/payments/${item.id}/edit`"
                                            @click="closeSearch"
                                            class="flex items-center justify-between px-4 py-2 hover:bg-blue-50/40 transition-colors group"
                                        >
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600">{{ item.transaction_id }}</p>
                                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ item.customer_name }} — ${{ parseFloat(item.amount).toLocaleString() }}</p>
                                            </div>
                                            <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-blue-400 transition-colors ml-3"></i>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <!-- User Profile -->
                    <div class="relative group">
                        <button type="button" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg">
                            <img :src="`https://ui-avatars.com/api/?name=${user?.name}&background=6366f1&color=fff`" alt="User" class="w-8 h-8 rounded-full">
                            <div class="hidden md:flex flex-col items-start leading-tight">
                                <span class="text-sm font-semibold text-gray-900">{{ user?.name }}</span>
                                <span class="text-[10px] uppercase font-bold text-gray-400">{{ user?.roles[0] || 'User' }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 w-48 mt-2 py-2 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="px-4 py-2 border-b border-gray-50 mb-1">
                                <p class="text-xs text-gray-400 font-medium">Signed in as</p>
                                <p class="text-sm font-semibold truncate">{{ user?.email }}</p>
                            </div>
                            <div class="border-t border-gray-50 mt-1 pt-1">
                                <form @submit.prevent="logout">
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt w-5 mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useSidebar } from '../../Composables/useSidebar';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isSuperadmin = computed(() => {
    const role = user.value?.role;
    const roles = user.value?.roles || [];
    return role === 'superadmin' || roles.includes('superadmin') || roles.includes('Superadmin');
});

const form = useForm({});
const logout = () => {
    form.post('/logout');
};

defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    showSearch: {
        type: Boolean,
        default: true,
    },
    searchPlaceholder: {
        type: String,
        default: 'Search customers, subscriptions...',
    },
});

defineEmits(['update:modelValue']);

const searchQuery = ref('');
const results = ref({
    customers: [],
    subscriptions: [],
    payments: [],
    invoices: [],
});
const loading = ref(false);
const isOpen = ref(false);

const hasNoResults = computed(() => {
    return !results.value.customers?.length &&
           !results.value.subscriptions?.length &&
           !results.value.payments?.length &&
           !results.value.invoices?.length;
});

let debounceTimeout = null;

const handleSearch = () => {
    clearTimeout(debounceTimeout);
    
    if (searchQuery.value.length < 2) {
        results.value = { customers: [], subscriptions: [], payments: [], invoices: [] };
        loading.value = false;
        return;
    }

    loading.value = true;
    isOpen.value = true;

    debounceTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/global-search?q=${encodeURIComponent(searchQuery.value)}`);
            const data = await response.json();
            results.value = data;
        } catch (error) {
            console.error('Global search error:', error);
        } finally {
            loading.value = false;
        }
    }, 250);
};

const clearSearch = () => {
    searchQuery.value = '';
    results.value = { customers: [], subscriptions: [], payments: [], invoices: [] };
    isOpen.value = false;
};

const closeSearch = () => {
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (!event.target.closest('.search-container')) {
        closeSearch();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const { toggleSidebar } = useSidebar();
</script>
