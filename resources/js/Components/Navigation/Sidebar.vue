<template>
    <aside
        id="sidebar"
        :class="[
            'sidebar fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-gray-200 z-30 overflow-y-auto',
            { 'open': isOpen }
        ]"
    >
        <nav class="p-4">
            <ul class="space-y-1">
                <li v-for="item in filteredNavigationItems" :key="item.name">
                    <!-- Expandable sub-menu -->
                    <div v-if="item.children && item.children.length > 0">
                        <button
                            @click="toggleSubMenu(item.name)"
                            :class="[
                                'sidebar-item flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer transition-colors duration-150',
                                isAnyChildActive(item) ? 'text-[#6366f1] font-semibold bg-[#eef2ff]/30' : ''
                            ]"
                        >
                            <span class="flex items-center">
                                <i :class="[item.icon, 'w-5 mr-3']"></i>
                                {{ item.name }}
                            </span>
                            <i :class="['fas text-xs transition-transform duration-250', activeSubMenus[item.name] ? 'fa-chevron-down rotate-180' : 'fa-chevron-right']"></i>
                        </button>
                        
                        <ul v-show="activeSubMenus[item.name]" class="mt-1 pl-8 space-y-1">
                            <li v-for="child in filterChildren(item.children)" :key="child.name">
                                <Link
                                    :href="child.href"
                                    :class="[
                                        'sidebar-item flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors duration-150',
                                        isActive(child.componentPrefix)
                                            ? 'active text-[#6366f1] bg-[#eef2ff] font-bold shadow-3xs'
                                            : 'text-gray-600 hover:bg-gray-100'
                                    ]"
                                    @click="closeSidebar"
                                >
                                    {{ child.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Regular single link -->
                    <Link
                        v-else
                        :href="item.href"
                        :class="[
                            'sidebar-item flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                            isActive(item.componentPrefix)
                                ? 'active text-[#6366f1] bg-[#eef2ff]'
                                : 'text-gray-700 hover:bg-gray-100'
                        ]"
                        @click="closeSidebar"
                    >
                        <i :class="[item.icon, 'w-5 mr-3']"></i>
                        {{ item.name }}
                    </Link>
                </li>
            </ul>
        </nav>
    </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { useSidebar } from '../../Composables/useSidebar';

const { isOpen, closeSidebar } = useSidebar();
const page = usePage();

const activeSubMenus = ref({});

const navigationItems = [
    { name: 'Dashboard', href: '/', icon: 'fas fa-home', componentPrefix: 'Dashboard', permission: 'dashboard.view' },
    { name: 'Clients', href: '/clients', icon: 'fas fa-building', componentPrefix: 'Clients', superadminOnly: true, centralOnly: true },
    { name: 'Customers', href: '/customers', icon: 'fas fa-users', componentPrefix: 'Customers', permission: 'customers.view', tenantOnly: true },
    { name: 'Subscriptions', href: '/subscriptions', icon: 'fas fa-credit-card', componentPrefix: 'Subscriptions', permission: 'subscriptions.view', tenantOnly: true },
    {
        name: 'Payments',
        icon: 'fas fa-dollar-sign',
        tenantOnly: true,
        children: [
            { name: 'Payment list', href: '/payments', componentPrefix: 'Payments', permission: 'payments.view' },
            { name: 'Payments channel', href: '/payment-channels', componentPrefix: 'PaymentChannels', permission: 'payment_channels.view' },
        ]
    },
    { name: 'Invoices', href: '/invoices', icon: 'fas fa-file-invoice', componentPrefix: 'Invoices', permission: 'invoices.view', tenantOnly: true },
    { name: 'Inventory', href: '/inventory', icon: 'fas fa-box', componentPrefix: 'Inventory', permission: 'inventory.view', tenantOnly: true },
    { name: 'Expenses', href: '/expenses', icon: 'fas fa-receipt', componentPrefix: 'Expenses', tenantOnly: true },
    { name: 'Purchases', href: '/purchases', icon: 'fas fa-shopping-cart', componentPrefix: 'Purchases', tenantOnly: true },
    { name: 'Renewals', href: '/renewals', icon: 'fas fa-sync', componentPrefix: 'Renewals', permission: 'renewals.view', tenantOnly: true },
    {
        name: 'Contracts',
        icon: 'fas fa-file-contract',
        tenantOnly: true,
        children: [
            { name: 'Contract', href: '/contracts', componentPrefix: 'Contracts', permission: 'contracts.view' },
            { name: 'Contract types', href: '/contract-types', componentPrefix: 'ContractTypes', permission: 'contract_types.view' },
        ]
    },
    { name: 'Reports', href: '/reports', icon: 'fas fa-chart-bar', componentPrefix: 'Reports', permission: 'reports.view', tenantOnly: true },
    {
        name: 'Commissions',
        icon: 'fas fa-hand-holding-usd',
        tenantOnly: true,
        children: [
            { name: 'Calculator', href: '/commissions', componentPrefix: 'Commissions', permission: 'payments.view' },
            { name: 'Slabs', href: '/commission-slabs', componentPrefix: 'CommissionSlabs', permission: 'settings.manage' },
        ]
    },
    /* { name: 'Email Templates', href: '/email-templates', icon: 'fas fa-envelope', componentPrefix: 'EmailTemplates', centralOnly: true },*/
    { name: 'Users and Roles', href: '/users', icon: 'fas fa-user-shield', componentPrefix: 'Administration', permission: 'users.manage' },
    { name: 'Billing Cycles', href: '/billing-cycles', icon: 'fas fa-history', componentPrefix: 'BillingCycles', permission: 'settings.manage', tenantOnly: true },
    { name: 'Settings', href: '/settings', icon: 'fas fa-cog', componentPrefix: 'Settings', permission: 'settings.manage' },
];

onMounted(() => {
    navigationItems.forEach(item => {
        if (item.children) {
            const hasActiveChild = item.children.some(child => isActive(child.componentPrefix));
            if (hasActiveChild) {
                activeSubMenus.value[item.name] = true;
            }
        }
    });
});

const toggleSubMenu = (name) => {
    activeSubMenus.value[name] = !activeSubMenus.value[name];
};

const isAnyChildActive = (item) => {
    return item.children && item.children.some(child => isActive(child.componentPrefix));
};

const checkIsSuperadmin = () => {
    const user = page.props.auth?.user;
    if (!user) return false;
    if (user.is_superadmin) return true;
    const userRole = user.role;
    const userRoles = user.roles || [];
    const userRoleIds = (user.role_ids || []).map(Number);
    return userRole === 'superadmin' || userRoles.includes('superadmin') || userRoles.includes('Superadmin') || userRoleIds.includes(1);
};

const filterChildren = (children) => {
    const isSuperadmin = checkIsSuperadmin();

    return children.filter(child => {
        if (isSuperadmin) return true;
        if (!child.permission) return true;
        const permissions = page.props.auth.user?.permissions || [];
        return permissions.includes(child.permission);
    });
};

const filteredNavigationItems = computed(() => {
    const isSuperadmin = checkIsSuperadmin();
    const isCentralDomain = page.props.isCentralDomain;

    return navigationItems.filter(item => {
        // Superadmin sees ALL modules
        if (isSuperadmin) {
            // On tenant domain, hide central-only items like Clients
            if (!isCentralDomain && item.centralOnly) {
                return false;
            }
            return true;
        }

        if (!isCentralDomain && item.centralOnly) {
            return false;
        }
        if (isCentralDomain && item.tenantOnly) {
            return false;
        }
        if (item.superadminOnly && !isSuperadmin) {
            return false;
        }
        if (item.children) {
            return item.children.some(child => {
                if (child.superadminOnly && !isSuperadmin) return false;
                if (!child.permission) return true;
                const permissions = page.props.auth.user?.permissions || [];
                return permissions.includes(child.permission);
            });
        }
        if (!item.permission) return true;
        const permissions = page.props.auth.user?.permissions || [];
        return permissions.includes(item.permission);
    });
});

const isActive = (prefix) => {
    return page.component && page.component.startsWith(prefix);
};
</script>

<style scoped>
.sidebar-item {
    transition: all 0.2s ease;
}
.sidebar-item:hover {
    background-color: #f3f4f6;
}
.sidebar-item.active {
    background-color: #eef2ff;
    color: #6366f1;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    .sidebar.open {
        transform: translateX(0);
    }
}
</style>
