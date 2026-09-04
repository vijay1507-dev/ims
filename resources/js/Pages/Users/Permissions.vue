<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Edit User Permissions</h2>
                    <p class="text-sm text-gray-500 mt-1">Configure granular access levels for {{ user.name }}</p>
                </div>
                <Link href="/users" class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to roles
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8 space-y-10">
                    <!-- Permission Groups -->
                    <div v-for="(group, groupName) in permissionGroups" :key="groupName" class="space-y-6">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">
                            {{ groupName }}
                        </h3>
                        
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

                <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-700 transition-all active:scale-[0.98]"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving Changes...' : 'Save Permissions' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    user: Object,
    permissions: Array,
    userPermissions: Array,
});

const form = useForm({
    permissions: [...props.userPermissions],
});

// Group permissions by prefix
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
    form.post(`/users/users/${props.user.id}/permissions`);
};
</script>
