<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import DataTable from '../../Components/Tables/DataTable.vue';

const props = defineProps({
    users: Array,
});

// Client Pagination States
const currentPage = ref(1);
const itemsPerPage = ref(10);

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return (props.users || []).slice(start, end);
});
</script>

<template>
    <AppLayout>
        <Head title="User Management" />

        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                    <p class="text-sm text-gray-500">Manage your enterprise users and their roles.</p>
                </div>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i> Add User
                </button>
            </div>

            <DataTable
                :columns="[
                    { label: 'Name', key: 'name' },
                    { label: 'Email', key: 'email' },
                    { label: 'Roles', key: 'roles' },
                    { label: 'Joined', key: 'created_at' },
                    { label: 'Actions', key: 'actions', align: 'right' }
                ]"
                :has-rows="users.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="users.length"
            >
                <template #rows>
                    <tr v-for="user in paginatedUsers" :key="user.id" class="table-row hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" :src="`https://ui-avatars.com/api/?name=${user.name}&background=6366f1&color=fff`" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ user.email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span v-for="role in user.roles" :key="role.id" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 mr-1">
                                {{ role.name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ new Date(user.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            <button class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
