<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Back to Dashboard -->
            <div class="mb-4">
                <Link
                    href="/"
                    class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </Link>
            </div>

            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-gray-100 rounded-lg border border-gray-200/60">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h3l3-9 4 18 3-12h5"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Recent activities</h2>
                        <p class="text-gray-600 mt-1 text-sm">Chronological feed of subscription and payment events across the platform</p>
                    </div>
                </div>
            </div>

            <!-- Main Activities Container -->
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex flex-col justify-between">
                <!-- Filters and Search Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-gray-100 gap-4 mb-2">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by client or plan..."
                            class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                        />
                    </div>

                    <!-- Category Filters -->
                    <div class="flex items-center bg-gray-100 p-1 rounded-lg border border-gray-200/50 self-start sm:self-auto">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value"
                            :class="['px-3.5 py-1.5 text-xs font-semibold rounded-md transition-all duration-200', activeTab === tab.value ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-950']"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <!-- Activities List -->
                <div v-if="filteredActivities.length > 0" class="divide-y divide-gray-100">
                    <div
                        v-for="activity in filteredActivities"
                        :key="activity.id"
                        class="flex items-start justify-between py-4.5 hover:bg-gray-50/30 px-2 -mx-2 rounded-lg transition-colors duration-150"
                    >
                        <div class="flex items-start space-x-3.5 flex-1 min-w-0">
                            <!-- Icon Circle -->
                            <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0', activity.iconBg]">
                                <i :class="[activity.icon, 'text-sm', activity.iconColor]"></i>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0 pt-0.5">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-semibold text-gray-900 leading-none">
                                        {{ activity.title }}
                                    </span>
                                    <span
                                        v-if="activity.badge"
                                        :class="['text-[11px] font-semibold px-2 py-0.5 rounded-full leading-none', activity.badgeClass]"
                                    >
                                        {{ activity.badge }}
                                    </span>
                                </div>
                                <p class="text-[13px] text-gray-500 mt-1.5 font-medium leading-tight">
                                    {{ activity.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Time ago -->
                        <span class="text-xs text-gray-400 font-medium whitespace-nowrap pt-1 ml-4">
                            {{ activity.time_ago }}
                        </span>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-16 px-4">
                    <div class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100 mb-4 shadow-sm">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h4 class="text-base font-semibold text-gray-900 mb-1">No activities found</h4>
                    <p class="text-sm text-gray-500 text-center max-w-[280px]">No records found matching your filters or search query.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    activities: {
        type: Array,
        required: true,
    },
});

const searchQuery = ref('');
const activeTab = ref('all');

const tabs = [
    { label: 'All', value: 'all' },
    { label: 'Subscriptions', value: 'subscription' },
    { label: 'Payments', value: 'payment' },
    { label: 'Failed', value: 'failed' },
];

const mapDynamicActivity = (activity) => {
    const type = activity.type || '';
    const colorClass = activity.color_class || '';
    
    let title = activity.title;
    let badge = null;
    let badgeClass = '';
    let icon = activity.icon || 'fas fa-info-circle';
    let iconBg = 'bg-[#e8f0fe]';
    let iconColor = 'text-[#1a73e8]';

    // Map types to exact titles, icons, and pill badges matching the mockup design
    if (type.startsWith('subscription_')) {
        title = activity.title || 'Subscription updated';
        badge = 'Active';
        badgeClass = 'bg-[#e6f4ea] text-[#137333]';
        icon = 'fas fa-sync';
        iconBg = 'bg-[#e6f4ea]';
        iconColor = 'text-[#137333]';
        
        if (type.includes('create') || type === 'subscription_activated') {
            badge = 'Active';
            icon = 'fas fa-check';
        } else if (type.includes('delete')) {
            badge = 'Cancelled';
            badgeClass = 'bg-[#fce8e6] text-[#c5221f]';
            icon = 'fas fa-times';
            iconBg = 'bg-[#fce8e6]';
            iconColor = 'text-[#c5221f]';
        }
    } else if (type.startsWith('payment_')) {
        title = activity.title || 'Transaction verified';
        badge = 'Payment';
        badgeClass = 'bg-[#e8f0fe] text-[#1a73e8]';
        icon = 'fas fa-arrows-alt-v';
        iconBg = 'bg-[#e8f0fe]';
        iconColor = 'text-[#1a73e8]';
        
        if (type.includes('failed')) {
            title = activity.title || 'Payment failed';
            badge = 'Failed';
            badgeClass = 'bg-[#fce8e6] text-[#c5221f]';
            icon = 'fas fa-exclamation-triangle';
            iconBg = 'bg-[#fce8e6]';
            iconColor = 'text-[#c5221f]';
        }
    } else if (type.startsWith('invoice_')) {
        title = activity.title || 'Invoice processed';
        badge = 'Invoice';
        badgeClass = 'bg-[#eef2ff] text-[#4f46e5]';
        icon = 'fas fa-file-invoice';
        iconBg = 'bg-[#eef2ff]';
        iconColor = 'text-[#4f46e5]';
    } else if (type.startsWith('customer_')) {
        title = activity.title || 'Customer updated';
        badge = 'Customer';
        badgeClass = 'bg-[#f5f3ff] text-[#7c3aed]';
        icon = 'fas fa-user';
        iconBg = 'bg-[#f5f3ff]';
        iconColor = 'text-[#7c3aed]';
    } else if (type.startsWith('inventory_')) {
        title = activity.title || 'Inventory updated';
        badge = 'Inventory';
        badgeClass = 'bg-[#fffbeb] text-[#d97706]';
        icon = 'fas fa-boxes';
        iconBg = 'bg-[#fffbeb]';
        iconColor = 'text-[#d97706]';
    } else if (type.startsWith('emailtemplate_')) {
        title = activity.title || 'Template updated';
        badge = 'Template';
        badgeClass = 'bg-[#e0f2fe] text-[#0369a1]';
        icon = 'fas fa-envelope';
        iconBg = 'bg-[#e0f2fe]';
        iconColor = 'text-[#0369a1]';
    } else if (type.startsWith('renewal_')) {
        title = activity.title || 'Renewal updated';
        badge = 'Renewal';
        badgeClass = 'bg-[#ffe4e6] text-[#b91c1c]';
        icon = 'fas fa-hourglass-half';
        iconBg = 'bg-[#ffe4e6]';
        iconColor = 'text-[#b91c1c]';
    } else if (type.startsWith('subscriptionpackage_') || type.startsWith('subscriptionpricing_')) {
        title = activity.title || 'Package updated';
        badge = 'Package';
        badgeClass = 'bg-[#d1fae5] text-[#047857]';
        icon = 'fas fa-tags';
        iconBg = 'bg-[#d1fae5]';
        iconColor = 'text-[#047857]';
    } else if (type.startsWith('user_')) {
        title = activity.title || 'User updated';
        badge = 'User';
        badgeClass = 'bg-[#ccfbf1] text-[#0f766e]';
        icon = 'fas fa-user-cog';
        iconBg = 'bg-[#ccfbf1]';
        iconColor = 'text-[#0f766e]';
    } else if (type === 'subscription_activated') {
        title = 'Subscription activated';
        badge = 'Active';
        badgeClass = 'bg-[#e6f4ea] text-[#137333]';
        icon = 'fas fa-check';
        iconBg = 'bg-[#e6f4ea]';
        iconColor = 'text-[#137333]';
    } else if (type === 'payment_received') {
        title = 'Transaction verified';
        badge = 'Payment';
        badgeClass = 'bg-[#e8f0fe] text-[#1a73e8]';
        icon = 'fas fa-arrows-alt-v';
        iconBg = 'bg-[#e8f0fe]';
        iconColor = 'text-[#1a73e8]';
    } else if (type === 'payment_failed') {
        title = 'Payment failed';
        badge = 'Failed';
        badgeClass = 'bg-[#fce8e6] text-[#c5221f]';
        icon = 'fas fa-exclamation-triangle';
        iconBg = 'bg-[#fce8e6]';
        iconColor = 'text-[#c5221f]';
    } else if (type === 'subscription_renewed') {
        title = 'Renewal upcoming';
        badge = 'Pending';
        badgeClass = 'bg-[#fef7e0] text-[#b06000]';
        icon = 'fas fa-sync-alt';
        iconBg = 'bg-[#fef7e0]';
        iconColor = 'text-[#b06000]';
    } else {
        // Dynamic fallback mapping for general system events based on class names
        if (colorClass.includes('green') || colorClass.includes('emerald')) {
            iconBg = 'bg-[#e6f4ea]';
            iconColor = 'text-[#137333]';
        } else if (colorClass.includes('blue')) {
            iconBg = 'bg-[#e8f0fe]';
            iconColor = 'text-[#1a73e8]';
        } else if (colorClass.includes('yellow') || colorClass.includes('amber')) {
            iconBg = 'bg-[#fef7e0]';
            iconColor = 'text-[#b06000]';
        } else if (colorClass.includes('red') || colorClass.includes('rose')) {
            iconBg = 'bg-[#fce8e6]';
            iconColor = 'text-[#c5221f]';
        }
    }

    // Format separators elegantly with em-dashes
    let description = activity.description || '';
    description = description.replace(/\s+-\s+/, ' — ');

    return {
        ...activity,
        title,
        badge,
        badgeClass,
        description,
        icon,
        iconBg,
        iconColor
    };
};

const mappedActivities = computed(() => {
    return props.activities.map(activity => mapDynamicActivity(activity));
});

const filteredActivities = computed(() => {
    return mappedActivities.value.filter(activity => {
        // Filter by tab type using prefix checks
        if (activeTab.value === 'subscription') {
            if (!activity.type.includes('subscription')) return false;
        } else if (activeTab.value === 'payment') {
            if (!activity.type.includes('payment')) return false;
        } else if (activeTab.value === 'failed') {
            if (!activity.type.includes('fail') && activity.type !== 'payment_failed') return false;
        }

        // Filter by search query
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            const inTitle = activity.title.toLowerCase().includes(query);
            const inDesc = activity.description.toLowerCase().includes(query);
            const inBadge = activity.badge ? activity.badge.toLowerCase().includes(query) : false;
            return inTitle || inDesc || inBadge;
        }

        return true;
    });
});
</script>
