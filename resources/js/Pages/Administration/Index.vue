<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
                    <p class="text-gray-600 mt-1">Configure RBAC security levels and administrative access</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8 min-h-[500px]">
                    <div class="flex justify-between items-center mb-6 pb-2 border-b border-gray-100">
                        <div class="flex items-center space-x-6">
                            <button 
                                @click="activeTab = 'users'"
                                :class="['pb-2 text-sm font-bold transition-all relative', activeTab === 'users' ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600']"
                            >
                                Users
                                <div v-if="activeTab === 'users'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 rounded-full"></div>
                            </button>
                            <button 
                                @click="activeTab = 'roles'"
                                :class="['pb-2 text-sm font-bold transition-all relative', activeTab === 'roles' ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600']"
                            >
                                Roles
                                <div v-if="activeTab === 'roles'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 rounded-full"></div>
                            </button>
                        </div>
                        <button v-if="activeTab === 'users'" @click="openCreateModal" type="button" class="text-xs font-bold text-indigo-600 hover:text-indigo-900 cursor-pointer">
                                + Assign New User
                        </button>
                        <Link v-if="activeTab === 'roles'" href="/users/roles/create" class="text-xs font-bold text-indigo-600 hover:text-indigo-900 cursor-pointer">
                                + Create New Role
                        </Link>
                    </div>

                    <!-- Users Tab -->
                    <div v-if="activeTab === 'users'">
                        <DataTable
                            :columns="[
                                { label: 'Identity Node', key: 'name' },
                                { label: 'Assigned Privilege', key: 'role' },
                                { label: 'Operational State', key: 'status' },
                                { label: 'Modifiers', key: 'actions', align: 'right' }
                            ]"
                            :has-rows="users.length > 0"
                            v-model:currentPage="currentUsersPage"
                            v-model:itemsPerPage="usersPerPage"
                            :totalItems="users.length"
                        >
                            <template #rows>
                                <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img :src="user.avatar || 'https://ui-avatars.com/api/?name=' + user.name" class="w-8 h-8 rounded-full border border-gray-200 shrink-0" alt="">
                                            <div class="ml-3">
                                                <p class="font-bold text-gray-900 text-xs">{{ user.name }}</p>
                                                <p class="text-[11px] text-gray-500 mt-0.5">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-bold text-gray-800">{{ user.roles?.[0]?.name || 'No Role' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium space-x-2 text-gray-900">
                                        <button @click="editUser(user)" class="text-indigo-600 hover:text-indigo-900 p-2 bg-indigo-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer" title="Edit User">
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                        <button @click="deleteUser(user.id)" class="text-rose-600 hover:text-rose-900 p-2 bg-rose-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer" title="Delete User">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </DataTable>
                    </div>

                    <!-- Roles Tab -->
                    <div v-if="activeTab === 'roles'">
                        <DataTable
                            :columns="[
                                { label: 'Role Name', key: 'name' },
                                { label: 'Permissions', key: 'permissions' },
                                { label: 'Status', key: 'status' },
                                { label: 'Actions', key: 'actions', align: 'right' }
                            ]"
                            :has-rows="roles.length > 0"
                            v-model:currentPage="currentRolesPage"
                            v-model:itemsPerPage="rolesPerPage"
                            :totalItems="roles.length"
                        >
                            <template #rows>
                                <tr v-for="role in paginatedRoles" :key="role.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-bold text-gray-900 capitalize">{{ role.name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span 
                                                v-for="perm in role.permissions" 
                                                :key="perm.id" 
                                                class="px-2 py-0.5 text-[9px] font-semibold bg-indigo-50 text-indigo-700 rounded-md border border-indigo-100"
                                            >
                                                {{ perm.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                            Synchronized
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-medium space-x-2 text-gray-900">
                                        <Link 
                                            :href="`/users/roles/${role.id}/permissions`" 
                                            class="text-indigo-600 hover:text-indigo-900 p-2 bg-indigo-50 rounded-lg transition-colors inline-flex items-center justify-center"
                                            title="Manage Permissions"
                                        >
                                            <i class="fas fa-shield-alt"></i>
                                        </Link>
                                        <button 
                                            v-if="!['admin', 'superadmin'].includes(role.name.toLowerCase())"
                                            @click="deleteRole(role.id)" 
                                            class="text-rose-600 hover:text-rose-900 p-2 bg-rose-50 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer"
                                            title="Delete Role"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <div v-if="showCreateUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Assign New User</h3>
                    <button @click="showCreateUserModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="submitUser" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                        <input v-model="userForm.name" type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="John Doe" required>
                        <div v-if="userForm.errors.name" class="text-rose-600 text-[10px] mt-1 font-bold">{{ userForm.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input v-model="userForm.email" type="email" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="john@example.com" required>
                        <div v-if="userForm.errors.email" class="text-rose-600 text-[10px] mt-1 font-bold">{{ userForm.errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Password</label>
                        <input v-model="userForm.password" type="password" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="••••••••" required>
                        <div v-if="userForm.errors.password" class="text-rose-600 text-[10px] mt-1 font-bold">{{ userForm.errors.password }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Assign Role</label>
                        <select v-model="userForm.role" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                            <option value="" disabled>Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>
                        <div v-if="userForm.errors.role" class="text-rose-600 text-[10px] mt-1 font-bold">{{ userForm.errors.role }}</div>
                    </div>
                    <div class="pt-4 flex justify-end space-x-3">
                        <button @click="showCreateUserModal = false" type="button" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-700 transition-all active:scale-95" :disabled="userForm.processing">
                            {{ userForm.processing ? 'Saving...' : 'Assign User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div v-if="showEditUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Edit User</h3>
                    <button @click="showEditUserModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="updateUser" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                        <input v-model="userForm.name" type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                        <div v-if="userForm.errors.name" class="text-rose-600 text-[10px] mt-1 font-bold">{{ userForm.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input v-model="userForm.email" type="email" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                        <div v-if="userForm.errors.email" class="text-rose-600 text-[10px] mt-1 font-bold">{{ userForm.errors.email }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Password (Cannot be changed here)</label>
                        <input type="password" disabled class="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-400 cursor-not-allowed select-none opacity-75" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Assign Role</label>
                        <select v-model="userForm.role" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" required>
                            <option value="" disabled>Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>
                        <div v-if="userForm.errors.role" class="text-rose-600 text-[10px] mt-1 font-bold">{{ userForm.errors.role }}</div>
                    </div>
                    <div class="pt-4 flex justify-end space-x-3">
                        <button @click="showEditUserModal = false" type="button" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-700 transition-all active:scale-95" :disabled="userForm.processing">
                            {{ userForm.processing ? 'Saving...' : 'Update User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import DataTable from '../../Components/Tables/DataTable.vue';

const props = defineProps({
    users: Array,
    roles: Array,
});

const activeTab = ref('users');
const showCreateUserModal = ref(false);
const showEditUserModal = ref(false);
const editingUser = ref(null);

// Pagination States
const currentUsersPage = ref(1);
const usersPerPage = ref(10);
const currentRolesPage = ref(1);
const rolesPerPage = ref(10);

const paginatedUsers = computed(() => {
    const start = (currentUsersPage.value - 1) * usersPerPage.value;
    const end = start + usersPerPage.value;
    return (props.users || []).slice(start, end);
});

const paginatedRoles = computed(() => {
    const start = (currentRolesPage.value - 1) * rolesPerPage.value;
    const end = start + rolesPerPage.value;
    return (props.roles || []).slice(start, end);
});

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
});

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && ['users', 'roles'].includes(tab)) {
        activeTab.value = tab;
    }
});

const openCreateModal = () => {
    userForm.reset();
    userForm.clearErrors();
    showCreateUserModal.value = true;
};

const submitUser = () => {
    userForm.post('/users', {
        onSuccess: () => {
            showCreateUserModal.value = false;
            userForm.reset();
        },
    });
};

const editUser = (user) => {
    editingUser.value = user;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.password = '';
    userForm.role = user.roles?.[0]?.name || '';
    userForm.clearErrors();
    showEditUserModal.value = true;
};

const updateUser = () => {
    userForm.put(`/users/${editingUser.value.id}`, {
        onSuccess: () => {
            showEditUserModal.value = false;
            userForm.reset();
        },
    });
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`);
    }
};

const deleteRole = (id) => {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
        router.delete(`/users/roles/${id}`);
    }
};
</script>

