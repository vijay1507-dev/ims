<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Create New Role</h2>
                    <p class="text-sm text-gray-500 mt-1">Define a new security role and its associated permissions</p>
                </div>
                <Link href="/users?tab=roles" class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Roles
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8 space-y-8">
                    <!-- Role Name Input -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Role Name</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium" 
                            placeholder="e.g. Content Manager"
                            required
                        >
                        <div v-if="form.errors.name" class="text-rose-600 text-xs mt-1 font-bold">{{ form.errors.name }}</div>
                    </div>

                    <div class="border-t border-gray-100 pt-8">
                        <h3 class="text-base font-bold text-gray-900 mb-4">Assign Permissions</h3>
                        
                        <!-- Permission Groups -->
                        <div class="space-y-10">
                            <div v-for="(group, groupName) in permissionGroups" :key="groupName" class="space-y-6">
                                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">
                                    {{ groupName }}
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div v-for="permission in group" :key="permission.id" class="flex items-center group cursor-pointer" @click="togglePermission(permission.name)">
                                        <div 
                                            class="w-5 h-5 rounded border flex items-center justify-center transition-all duration-200"
                                            :class="form.permissions.includes(permission.name) ? 'bg-indigo-600 border-indigo-600' : 'bg-white border-gray-300 group-hover:border-indigo-400'"
                                        >
                                            <i v-if="form.permissions.includes(permission.name)" class="fas fa-check text-[10px] text-white"></i>
                                        </div>
                                        <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors capitalize">
                                            {{ formatPermissionName(permission.name) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button 
                        type="submit" 
                        class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-[0.98] disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Creating Role...' : 'Create Role' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    permissions: Array,
});

const form = useForm({
    name: '',
    permissions: [],
});

// Group permissions by prefix (consistent with Permissions.vue)
const permissionGroups = {
    'DASHBOARD & ANALYTICS': [
        { name: 'dashboard.view' },
    ],
    'CUSTOMER MANAGEMENT': [
        { name: 'customers.view' },
        { name: 'customers.create' },
        { name: 'customers.edit' },
        { name: 'customers.delete' },
    ],
    'SUBSCRIPTION MANAGEMENT': [
        { name: 'subscriptions.view' },
        { name: 'subscriptions.create' },
        { name: 'subscriptions.edit' },
        { name: 'subscriptions.delete' },
    ],
    'PAYMENT MANAGEMENT': [
        { name: 'payments.view' },
        { name: 'payments.create' },
        { name: 'payments.edit' },
        { name: 'payments.delete' },
    ],
    'INVOICES MANAGEMENT': [
        { name: 'invoices.view' },
        { name: 'invoices.create' },
        { name: 'invoices.edit' },
        { name: 'invoices.delete' },
    ],
    'INVENTORY MANAGEMENT': [
        { name: 'inventory.view' },
        { name: 'inventory.create' },
        { name: 'inventory.edit' },
        { name: 'inventory.delete' },
    ],
    'RENEWAL MANAGEMENT': [
        { name: 'renewals.view' },
        { name: 'renewals.create' },
        { name: 'renewals.edit' },
        { name: 'renewals.delete' },
    ],
    'REPORTS': [
        { name: 'reports.view' },
    ],
    'SYSTEM ADMINISTRATION': [
        { name: 'users.manage' },
        { name: 'settings.manage' },
        { name: 'billing.manage' },
    ]
};

const togglePermission = (name) => {
    const index = form.permissions.indexOf(name);
    if (index === -1) {
        form.permissions.push(name);
    } else {
        form.permissions.splice(index, 1);
    }
};

const formatPermissionName = (name) => {
    return name.split('.').join(' ');
};

const submit = () => {
    form.post('/users/roles');
};
</script>
