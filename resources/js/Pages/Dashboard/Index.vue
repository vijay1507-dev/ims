<template>
    <AppLayout>
        <div class="p-6">
            <!-- Page Header -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Dashboard Overview</h2>
                <p class="text-gray-600 mt-1">Monitor your subscription business metrics and performance</p>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <KpiCard
                    title="Monthly Revenue"
                    :value="kpis.monthly_revenue.value"
                    icon="fas fa-chart-line"
                    icon-bg-class="bg-indigo-100"
                    icon-color-class="text-indigo-600"
                    :trend-text="kpis.monthly_revenue.trend"
                    trend-icon="fas fa-arrow-up"
                    trend-color-class="text-green-600"
                />
                <KpiCard
                    title="Annual Revenue"
                    :value="kpis.annual_revenue.value"
                    icon="fas fa-dollar-sign"
                    icon-bg-class="bg-green-100"
                    icon-color-class="text-green-600"
                    :trend-text="kpis.annual_revenue.trend"
                    trend-icon="fas fa-arrow-up"
                    trend-color-class="text-green-600"
                />
                <KpiCard
                    title="Active Customers"
                    :value="kpis.active_customers.value"
                    icon="fas fa-users"
                    icon-bg-class="bg-blue-100"
                    icon-color-class="text-blue-600"
                    :trend-text="kpis.active_customers.trend"
                    trend-icon="fas fa-arrow-up"
                    trend-color-class="text-green-600"
                />
                <KpiCard
                    title="Pending Renewals"
                    :value="kpis.pending_renewals.value"
                    icon="fas fa-sync"
                    icon-bg-class="bg-yellow-100"
                    icon-color-class="text-yellow-600"
                    :trend-text="kpis.pending_renewals.trend"
                    trend-icon="fas fa-clock"
                    trend-color-class="text-yellow-600"
                />
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Overview</h3>
                    <BaseChart
                        type="line"
                        :data="charts.revenue_overview"
                        :options="{
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) { return '$' + value.toLocaleString(); }
                                    }
                                }
                            }
                        }"
                    />
                </div>

                <!-- Subscription Growth Chart -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Growth</h3>
                    <BaseChart
                        type="bar"
                        :data="charts.subscription_growth"
                        :options="{
                            plugins: { legend: { position: 'bottom' } },
                            scales: { y: { beginAtZero: true } }
                        }"
                    />
                </div>
            </div>

            <!-- Recent Activities & Quick Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activities -->
                <!-- Recent Activities -->
                <div class="lg:col-span-1 bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center space-x-2.5 pb-4 border-b border-gray-100">
                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h3l3-9 4 18 3-12h5"></path>
                            </svg>
                            <h3 class="text-[17px] font-bold text-gray-900 leading-none">Recent activities</h3>
                        </div>

                        <!-- Activities List -->
                        <div v-if="mappedActivities.length > 0" class="divide-y divide-gray-100">
                            <div
                                v-for="activity in mappedActivities"
                                :key="activity.id"
                                class="flex items-start justify-between py-4"
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
                        <div v-else class="flex flex-col items-center justify-center py-12 px-4 text-center">
                            <div class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100 mb-4 shadow-sm">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h4 class="text-[15px] font-semibold text-gray-900 mb-1">No recent activities</h4>
                            <p class="text-[13px] text-gray-500 max-w-[280px]">System notifications and logs will populate here as modifications occur.</p>
                        </div>
                    </div>

                    <!-- Bottom Link -->
                    <div class="pt-4 border-t border-gray-100 mt-2">
                        <Link href="/activities" class="text-[#2563eb] hover:text-[#1d4ed8] text-sm font-semibold flex items-center space-x-1.5 leading-none">
                            <span>→ View all activity</span>
                        </Link>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center space-x-2.5 pb-4 border-b border-gray-100 mb-5">
                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path>
                            </svg>
                            <h3 class="text-[17px] font-bold text-gray-900 leading-none">Quick stats</h3>
                        </div>

                        <!-- Stats Banners -->
                        <div class="space-y-3.5 mb-5">
                            <!-- Expired Subscriptions -->
                            <div class="flex items-center space-x-4 p-4 rounded-xl border border-[#c5221f]/30 bg-[#fce8e6]/10">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#fce8e6]/40 text-[#c5221f] flex-shrink-0">
                                    <i class="far fa-calendar-times text-lg"></i>
                                </div>
                                <div>
                                    <div class="text-[13px] font-semibold text-gray-500">Expired subscriptions</div>
                                    <div class="text-xl font-bold text-[#c5221f] mt-0.5 leading-none">{{ quickStats.expired_subscriptions }}</div>
                                </div>
                            </div>

                            <!-- Overdue Payments -->
                            <div class="flex items-center space-x-4 p-4 rounded-xl border border-[#b06000]/30 bg-[#fef7e0]/10">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#fef7e0]/40 text-[#b06000] flex-shrink-0">
                                    <i class="far fa-clock text-lg"></i>
                                </div>
                                <div>
                                    <div class="text-[13px] font-semibold text-gray-500">Overdue payments</div>
                                    <div class="text-xl font-bold text-[#b06000] mt-0.5 leading-none">{{ quickStats.overdue_payments }}</div>
                                </div>
                            </div>

                            <!-- Failed Payments -->
                            <div class="flex items-center space-x-4 p-4 rounded-xl border border-gray-200 bg-[#fafaf9]">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-gray-100 text-gray-600 flex-shrink-0">
                                    <i class="fas fa-credit-card text-base"></i>
                                </div>
                                <div>
                                    <div class="text-[13px] font-semibold text-gray-500">Failed payments</div>
                                    <div class="text-xl font-bold text-gray-900 mt-0.5 leading-none">{{ quickStats.failed_payments }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-b border-gray-100 my-4"></div>

                    <!-- MRR / ARR Grid -->
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <!-- MRR -->
                        <div class="bg-[#f0f0ed] p-4 rounded-xl flex flex-col justify-between">
                            <div class="flex items-center space-x-1.5 text-gray-500">
                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"></path>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wider">MRR</span>
                            </div>
                            <div class="text-xl font-bold text-[#137333] mt-2.5 leading-none">
                                {{ formatCurrency(quickStats.mrr) }}
                            </div>
                        </div>

                        <!-- ARR -->
                        <div class="bg-[#f0f0ed] p-4 rounded-xl flex flex-col justify-between">
                            <div class="flex items-center space-x-1.5 text-gray-500">
                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"></path>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wider">ARR</span>
                            </div>
                            <div class="text-xl font-bold text-[#137333] mt-2.5 leading-none">
                                {{ formatCurrency(quickStats.arr) }}
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-b border-gray-100 my-4"></div>

                    <!-- Churn Rate -->
                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center space-x-2 text-gray-700">
                            <i class="far fa-user text-base text-gray-400"></i>
                            <span class="text-sm font-semibold text-gray-700">Churn rate</span>
                        </div>
                        
                        <!-- Progress Track -->
                        <div class="flex-1 mx-4 flex items-center relative">
                            <div class="w-full h-1 bg-gray-100 rounded-full relative">
                                <!-- Green dot at start of track -->
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-[#137333]"></div>
                            </div>
                        </div>

                        <!-- Churn Value -->
                        <span class="text-sm font-bold text-gray-900 leading-none">
                            {{ quickStats.churn_rate }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import KpiCard from '../../Components/Cards/KpiCard.vue';
import BaseChart from '../../Components/Charts/BaseChart.vue';

const props = defineProps({
    kpis: {
        type: Object,
        required: true,
    },
    charts: {
        type: Object,
        required: true,
    },
    activities: {
        type: Array,
        required: true,
    },
    quickStats: {
        type: Object,
        required: true,
    },
});

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

const formatCurrency = (val) => {
    if (!val) return '';
    // Strip trailing decimals like .00 if present to match the design exactly
    return val.replace(/\.00$/, '');
};
</script>
