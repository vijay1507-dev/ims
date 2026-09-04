<template>
    <AppLayout>
        <div class="p-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Renewal Management</h2>
                    <p class="text-gray-600 mt-1">Track subscription renewals and warranty expirations</p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Integrated SPA link mapping creation endpoints -->
                    <Link
                        href="/renewals/create"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm transition-colors inline-flex items-center"
                    >
                        <i class="fas fa-calendar-plus mr-2"></i>Schedule Renewal
                    </Link>
                </div>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Upcoming Renewals -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Upcoming Renewals</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.upcoming_renewals }}</p>
                            <p class="text-xs text-yellow-600 mt-2 font-medium flex items-center">
                                <i class="fas fa-clock mr-1"></i> Next 30 days
                            </p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-lg shrink-0 self-start">
                            <i class="fas fa-sync text-yellow-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>
                </div>

                <!-- Urgent Renewals -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Urgent Renewals</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.urgent_renewals }}</p>
                            <p class="text-xs text-red-600 mt-2 font-medium flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Next 7 days
                            </p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg shrink-0 self-start">
                            <i class="fas fa-exclamation text-red-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>
                </div>

                <!-- Expired Renewals -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Expired Renewals</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.expired_renewals }}</p>
                            <p class="text-xs text-rose-600 mt-2 font-medium flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i> Overdue
                            </p>
                        </div>
                        <div class="bg-rose-100 p-3 rounded-lg shrink-0 self-start">
                            <i class="fas fa-times-circle text-rose-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>
                </div>

                <!-- Auto-Renewal Rate -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:-translate-y-0.5 transition-all shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Auto-Renewal Rate</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.auto_renewal_rate }}</p>
                            <p class="text-xs mt-2 font-medium flex items-center" :class="metrics.auto_renewal_trend.class">
                                <i class="mr-1" :class="metrics.auto_renewal_trend.icon"></i> {{ metrics.auto_renewal_trend.text }}
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg shrink-0 self-start">
                            <i class="fas fa-chart-line text-green-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fully Dynamic Reactive Calendar & Filtered Details Component Group -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Dedicated Dynamic Calendar Layer -->
                <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Renewal Calendar</h3>
                            <p v-if="selectedDay !== null" class="text-xs text-indigo-600 font-medium mt-0.5">
                                Showing events for {{ formattedMonthYear.split(' ')[0] }} {{ selectedDay }}, {{ viewedYear }}
                                <button type="button" class="underline ml-1 cursor-pointer font-bold" @click="selectedDay = null">Clear filter</button>
                            </p>
                            <p v-else class="text-xs text-gray-500 mt-0.5">Click any day to show scheduled contract renewals</p>
                        </div>
                        
                        <!-- Month-Year Navigation Bar -->
                        <div class="flex items-center space-x-2 bg-gray-50 p-1 rounded-lg border border-gray-200/80 shrink-0">
                            <button
                                type="button"
                                class="w-8 h-8 flex items-center justify-center text-sm bg-white border border-gray-200 rounded-md hover:bg-gray-100 transition-colors cursor-pointer shadow-2xs"
                                title="Previous Month"
                                @click="prevMonth"
                            >
                                <i class="fas fa-chevron-left text-xs text-gray-600"></i>
                            </button>
                            <span class="px-3 py-1 text-sm font-bold text-gray-800 tracking-wide min-w-[110px] text-center">
                                {{ formattedMonthYear }}
                            </span>
                            <button
                                type="button"
                                class="w-8 h-8 flex items-center justify-center text-sm bg-white border border-gray-200 rounded-md hover:bg-gray-100 transition-colors cursor-pointer shadow-2xs"
                                title="Next Month"
                                @click="nextMonth"
                            >
                                <i class="fas fa-chevron-right text-xs text-gray-600"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Matrix Render Area -->
                    <div class="grid grid-cols-7 gap-1 flex-1">
                        <!-- Standard Days Header -->
                        <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Sun</div>
                        <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Mon</div>
                        <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Tue</div>
                        <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Wed</div>
                        <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Thu</div>
                        <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Fri</div>
                        <div class="text-center text-xs font-bold text-gray-400 uppercase py-2">Sat</div>

                        <!-- Configured Days Array Cells -->
                        <div
                            v-for="(cell, index) in calendarDays"
                            :key="index"
                            class="min-h-[54px] p-1.5 rounded-lg transition-all relative flex flex-col justify-between select-none"
                            :class="{
                                'bg-white hover:bg-gray-50 cursor-pointer border border-transparent': cell.isCurrentMonth && selectedDay !== cell.dayNumber,
                                'bg-indigo-50/80 border border-indigo-300 ring-2 ring-indigo-500/30': selectedDay === cell.dayNumber,
                                'opacity-30 cursor-default bg-gray-50/40': !cell.isCurrentMonth
                            }"
                            @click="selectDay(cell)"
                        >
                            <div class="flex justify-center items-center">
                                <span
                                    class="text-xs font-bold inline-flex items-center justify-center w-5 h-5 rounded-full"
                                    :class="{
                                        'bg-indigo-600 text-white': cell.isToday && cell.isCurrentMonth,
                                        'text-gray-900': cell.isCurrentMonth && !cell.isToday,
                                        'text-gray-400': !cell.isCurrentMonth
                                    }"
                                >
                                    {{ cell.dayNumber }}
                                </span>
                            </div>

                            <!-- Indicator Event Point Dots -->
                            <div class="flex flex-wrap gap-1 justify-center mt-auto pt-1 h-3">
                                <span
                                    v-if="cell.hasEvents"
                                    class="w-1.5 h-1.5 rounded-full animate-pulse block"
                                    :class="{
                                        'bg-red-500': cell.events.some(e => e.priority === 'urgent'),
                                        'bg-yellow-500': cell.events.some(e => e.priority === 'soon') && !cell.events.some(e => e.priority === 'urgent'),
                                        'bg-green-500': !cell.events.some(e => e.priority === 'urgent' || e.priority === 'soon')
                                    }"
                                ></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Panel Item List mapped purely to reactive filtered scope -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <span v-if="selectedDay !== null">Selected Date Items</span>
                            <span v-else>{{ formattedMonthYear.split(' ')[0] }} Schedules</span>
                        </h3>
                        <span class="px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-600 rounded-full">
                            {{ activeSideItems.length }}
                        </span>
                    </div>
                    
                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[340px] pr-1">
                        <div
                            v-for="item in activeSideItems"
                            :key="item.id"
                            class="p-3 border rounded-lg transition-all hover:shadow-2xs"
                            :class="{
                                'border-red-200 bg-red-50/70': item.priority === 'urgent',
                                'border-yellow-200 bg-yellow-50/70': item.priority === 'soon',
                                'border-green-200 bg-green-50/70': item.priority === 'normal',
                                'border-gray-200 bg-gray-50/70': item.priority === 'renewed'
                            }"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-xs text-gray-900">{{ item.customer_name }}</p>
                                    <p class="text-xs text-gray-600 mt-0.5">{{ item.plan_asset }}</p>
                                    <p
                                        class="text-[11px] mt-1 font-medium flex items-center"
                                        :class="{
                                            'text-red-700': item.priority === 'urgent' || item.priority === 'expired',
                                            'text-yellow-700': item.priority === 'soon',
                                            'text-green-700': item.priority === 'normal',
                                            'text-gray-600': item.priority === 'renewed'
                                        }"
                                    >
                                        <i class="fas fa-calendar-day mr-1.5 text-[10px] opacity-70"></i>
                                        {{ item.renewal_date }} ({{ item.days_left }})
                                    </p>
                                </div>
                                <span
                                    class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider"
                                    :class="{
                                        'bg-red-100 text-red-800': item.priority === 'urgent',
                                        'bg-red-200 text-red-950 border border-red-300': item.priority === 'expired',
                                        'bg-yellow-100 text-yellow-800': item.priority === 'soon',
                                        'bg-green-100 text-green-800': item.priority === 'normal',
                                        'bg-gray-100 text-gray-800': item.priority === 'renewed'
                                    }"
                                >
                                    {{ item.priority }}
                                </span>
                            </div>
                        </div>

                        <div v-if="activeSideItems.length === 0" class="text-center py-12 text-xs text-gray-400 border border-dashed border-gray-200 rounded-lg">
                            <i class="fas fa-calendar-check text-2xl text-gray-300 block mb-2"></i>
                            No renewals listed for this parameter window.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integrated Data Table with Live Filters -->
            <DataTable
                title="Renewal Details"
                :columns="[
                    { label: 'Customer', key: 'customer_name' },
                    { label: 'Type', key: 'type' },
                    { label: 'Plan/Asset', key: 'plan_asset' },
                    { label: 'Current Value', key: 'current_value' },
                    { label: 'Renewal Date', key: 'renewal_date' },
                    { label: 'Days Left', key: 'days_left' },
                    { label: 'Priority', key: 'priority' },
                    { label: 'Actions', key: 'actions', align: 'right' }
                ]"
                :has-rows="renewals.length > 0"
                v-model:currentPage="currentPage"
                v-model:itemsPerPage="itemsPerPage"
                :totalItems="renewals.length"
            >
                <template #filters>
                    <!-- Real-time Text Search input -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search subscriptions..."
                            class="w-48 sm:w-64 pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium"
                            @input="triggerFilter"
                        >
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-xs text-gray-400"></i>
                    </div>

                    <!-- Filter by Type -->
                    <select
                        v-model="selectedType"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700"
                        @change="triggerFilter"
                    >
                        <option>All Types</option>
                        <option value="subscription">Subscription</option>
                        <option value="payment">Payment</option>
                        <option value="hardware">Hardware</option>
                    </select>

                    <!-- Filter by Priority -->
                    <select
                        v-model="selectedPriority"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700"
                        @change="triggerFilter"
                    >
                        <option>All Status</option>
                        <option value="expired" class="text-red-700">Expired</option>
                        <option value="urgent" class="text-rose-600">Urgent</option>
                        <option value="soon" class="text-amber-600">Soon</option>
                        <option value="normal" class="text-indigo-600">Normal</option>
                        <option value="renewed" class="text-gray-600">Renewed</option>
                    </select>

                    <!-- Filter by Month -->
                    <select
                        v-model="selectedMonth"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700"
                        @change="triggerFilter"
                    >
                        <option v-for="m in availableMonths" :key="m" :value="m">{{ m }}</option>
                    </select>

                    <!-- Filter by Year -->
                    <select
                        v-model="selectedYear"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer font-bold text-gray-700"
                        @change="triggerFilter"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </template>

                <template #rows>
                    <tr
                        v-for="ren in paginatedRenewals"
                        :key="ren.id"
                        class="table-row hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ ren.customer_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span 
                                class="px-2 py-0.5 text-[10px] font-bold rounded uppercase tracking-wider inline-block"
                                :class="{
                                    'bg-indigo-100 text-indigo-800': ren.type === 'subscription',
                                    'bg-emerald-100 text-emerald-800': ren.type === 'payment',
                                    'bg-amber-100 text-amber-800': ren.type !== 'subscription' && ren.type !== 'payment'
                                }"
                            >
                                {{ ren.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                            {{ ren.plan_asset }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-950">
                            {{ ren.current_value }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ ren.renewal_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" :class="ren.days_left <= 7 ? 'text-rose-600' : 'text-gray-500'">
                            {{ ren.days_left }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span 
                                class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                                :class="{
                                    'bg-rose-100 text-rose-800': ren.priority === 'urgent',
                                    'bg-red-200 text-red-950 border border-red-300': ren.priority === 'expired',
                                    'bg-amber-100 text-amber-800': ren.priority === 'medium' || ren.priority === 'soon',
                                    'bg-indigo-100 text-indigo-800': ren.priority === 'normal'
                                }"
                            >
                                {{ ren.priority }}
                            </span>
                        </td>
                        <!-- Actions: Renew + Delete -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="inline-flex items-center gap-3">
                                <!-- Renew button: primary style for expired, secondary for others -->
                                <button
                                    type="button"
                                    :class="ren.priority === 'expired'
                                        ? 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-all cursor-pointer'
                                        : 'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-all cursor-pointer'"
                                    @click="openRenewModal(ren)"
                                >
                                    <i class="fas fa-sync-alt text-[10px]"></i>
                                    Renew
                                </button>
                                <button
                                    type="button"
                                    class="text-red-500 hover:text-red-800 text-xs font-medium transition-colors cursor-pointer"
                                    @click="deleteRenewal(ren.id)"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </DataTable>
        </div>

        <!-- ============================================================ -->
        <!-- Renewal Confirmation Modal                                    -->
        <!-- ============================================================ -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div
                    v-if="showRenewModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="renew-modal-title"
                >
                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
                        @click="closeRenewModal"
                    ></div>

                    <!-- Modal Panel -->
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-sync-alt text-indigo-600"></i>
                                </div>
                                <div>
                                    <h2 id="renew-modal-title" class="text-base font-bold text-gray-900">Confirm Renewal</h2>
                                    <p class="text-xs text-gray-500 mt-0.5">Review current values and confirm the new renewal details</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer"
                                @click="closeRenewModal"
                            >
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5 space-y-5">

                            <!-- Current Values Summary -->
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Current Record</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Customer</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ renewModalData.customer_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Type</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-0.5 capitalize">{{ renewModalData.type }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Plan / Asset</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ renewModalData.plan_asset }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Current Value</p>
                                        <p class="text-sm font-bold text-indigo-700 mt-0.5">{{ renewModalData.current_value }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Expires / Renewal Date</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ renewModalData.renewal_date }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Priority</p>
                                        <span
                                            class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider"
                                            :class="{
                                                'bg-red-200 text-red-950 border border-red-300': renewModalData.priority === 'expired',
                                                'bg-rose-100 text-rose-800': renewModalData.priority === 'urgent',
                                                'bg-amber-100 text-amber-800': renewModalData.priority === 'soon',
                                                'bg-indigo-100 text-indigo-800': renewModalData.priority === 'normal',
                                            }"
                                        >{{ renewModalData.priority }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- New Renewal Values -->
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">New Renewal Details</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                    <!-- Amount -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="renew-amount">Renewal Amount ($)</label>
                                        <input
                                            id="renew-amount"
                                            v-model="renewForm.amount"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                                        />
                                    </div>

                                    <!-- Payment Date -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="renew-payment-date">Payment Date</label>
                                        <input
                                            id="renew-payment-date"
                                            v-model="renewForm.payment_date"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                                            @change="updateNewRenewalDate"
                                        />
                                    </div>

                                    <!-- Billing Cycle -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="renew-billing-cycle">Billing Cycle</label>
                                        <select
                                            id="renew-billing-cycle"
                                            v-model="renewForm.billing_cycle"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                            @change="updateNewRenewalDate"
                                        >
                                            <option v-for="bc in props.billingCycles" :key="bc.code" :value="bc.code">
                                                {{ bc.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="renew-status">Payment Status</label>
                                        <select
                                            id="renew-status"
                                            v-model="renewForm.status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                        >
                                            <option value="successful">Successful</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    </div>

                                    <!-- Payment Method -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="renew-payment-method">Payment Method</label>
                                        <input
                                            id="renew-payment-method"
                                            v-model="renewForm.payment_method"
                                            type="text"
                                            placeholder="e.g. ****4242"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                                        />
                                    </div>

                                    <!-- Method Type -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1.5" for="renew-method-type">Method Type</label>
                                        <select
                                            id="renew-method-type"
                                            v-model="renewForm.method_type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all"
                                        >
                                            <option value="visa">Visa</option>
                                            <option value="mastercard">Mastercard</option>
                                            <option value="stripe">Stripe</option>
                                            <option value="paypal">PayPal</option>
                                        </select>
                                    </div>

                                </div>

                                <!-- New Renewal Date Preview -->
                                <div class="mt-4 flex items-center gap-2 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-3">
                                    <i class="fas fa-calendar-check text-indigo-500 text-sm"></i>
                                    <div>
                                        <p class="text-[10px] text-indigo-500 font-semibold uppercase tracking-wider">New Renewal / End Date</p>
                                        <p class="text-sm font-bold text-indigo-800 mt-0.5">{{ computedNewRenewalDate || '—' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Duplicate / Error Message -->
                            <div
                                v-if="renewError"
                                class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3"
                            >
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>
                                <p class="text-sm text-red-700 font-medium">{{ renewError }}</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
                            <button
                                type="button"
                                class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                                :disabled="renewLoading"
                                @click="closeRenewModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                id="confirm-renewal-btn"
                                class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer inline-flex items-center gap-2"
                                :disabled="renewLoading"
                                @click="submitRenew"
                            >
                                <i v-if="renewLoading" class="fas fa-spinner fa-spin text-xs"></i>
                                <i v-else class="fas fa-sync-alt text-xs"></i>
                                {{ renewLoading ? 'Processing...' : 'Confirm Renewal' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Toast Notification -->
        <Teleport to="body">
            <Transition name="toast-slide">
                <div
                    v-if="toast.show"
                    class="fixed bottom-6 right-6 z-[60] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-2xl text-sm font-semibold max-w-sm"
                    :class="toast.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
                >
                    <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
                    {{ toast.message }}
                </div>
            </Transition>
        </Teleport>

    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import DataTable from '../../Components/Tables/DataTable.vue';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    renewals: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    availableMonths: {
        type: Array,
        required: true,
    },
    availableYears: {
        type: Array,
        required: true,
    },
    billingCycles: {
        type: Array,
        default: () => [
            { code: 'monthly',     name: 'Monthly',    duration_months: 1  },
            { code: 'quarterly',   name: 'Quarterly',  duration_months: 3  },
            { code: 'half_yearly', name: 'Half Yearly',duration_months: 6  },
            { code: 'annual',      name: 'Annual',     duration_months: 12 },
        ],
    },
});

// Live Reactive Filters
const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || 'All Types');
const selectedPriority = ref(props.filters.priority || 'All Status');
const selectedMonth = ref(props.filters.filter_month || 'All Months');
const selectedYear = ref(props.filters.filter_year || 'All Years');

// Client Pagination States
const currentPage = ref(props.filters.page ? parseInt(props.filters.page) : 1);
const itemsPerPage = ref(10);

const paginatedRenewals = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.renewals.slice(start, end);
});

// Reset page on filter update
watch(() => [searchQuery.value, selectedType.value, selectedPriority.value, selectedMonth.value, selectedYear.value], () => {
    currentPage.value = 1;
});

// Keep the ?page= query param in sync with client-side pagination
const syncPageInUrl = (pageNum) => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', pageNum);
    window.history.replaceState(window.history.state, '', url);
};

onMounted(() => {
    syncPageInUrl(currentPage.value);
});

watch(currentPage, (newPage) => {
    syncPageInUrl(newPage);
});

// ----------------------------------------------------
// Fully Reactive Real-time Calendar Engine Configuration
// ----------------------------------------------------
const currentDate = new Date();
const viewedYear = ref(currentDate.getFullYear());
const viewedMonth = ref(currentDate.getMonth()); // 0-indexed (0 = Jan, 11 = Dec)
const selectedDay = ref(null);

const monthNames = [
    "January", "February", "March", "April", "May", "June", 
    "July", "August", "September", "October", "November", "December"
];

const formattedMonthYear = computed(() => {
    return `${monthNames[viewedMonth.value]} ${viewedYear.value}`;
});

// Month Shift Controllers
const prevMonth = () => {
    if (viewedMonth.value === 0) {
        viewedMonth.value = 11;
        viewedYear.value--;
    } else {
        viewedMonth.value--;
    }
    selectedDay.value = null; // Auto-clear strict day filter on switch
};

const nextMonth = () => {
    if (viewedMonth.value === 11) {
        viewedMonth.value = 0;
        viewedYear.value++;
    } else {
        viewedMonth.value++;
    }
    selectedDay.value = null;
};

// Days grid structure map evaluation
const calendarDays = computed(() => {
    const year = viewedYear.value;
    const month = viewedMonth.value;
    
    // Day of the week for day 1 (0 = Sunday)
    const firstDayIndex = new Date(year, month, 1).getDay();
    // Count of absolute active days in active viewed month
    const totalDays = new Date(year, month + 1, 0).getDate();
    // Count of days in immediately preceding month
    const prevMonthDays = new Date(year, month, 0).getDate();
    
    const cells = [];
    
    // Inject preceding trailing filler blank cells
    for (let i = firstDayIndex - 1; i >= 0; i--) {
        cells.push({
            dayNumber: prevMonthDays - i,
            isCurrentMonth: false,
            hasEvents: false,
            events: []
        });
    }
    
    // Evaluate active month cells alongside literal timestamp validation
    for (let d = 1; d <= totalDays; d++) {
        // Map any record dates landing exactly on this day matrix
        const matches = props.renewals.filter(r => {
            if (!r.renewal_date) return false;
            const rDate = new Date(r.renewal_date);
            return !isNaN(rDate) && 
                   rDate.getDate() === d && 
                   rDate.getMonth() === month && 
                   rDate.getFullYear() === year;
        });
        
        cells.push({
            dayNumber: d,
            isCurrentMonth: true,
            hasEvents: matches.length > 0,
            events: matches,
            isToday: d === currentDate.getDate() && 
                     month === currentDate.getMonth() && 
                     year === currentDate.getFullYear()
        });
    }
    
    // Populate terminal filler cells to balance uniform grid layout matrices
    const remainingCells = 42 - cells.length;
    for (let d = 1; d <= remainingCells && cells.length < 35; d++) {
        cells.push({
            dayNumber: d,
            isCurrentMonth: false,
            hasEvents: false,
            events: []
        });
    }
    
    return cells;
});

// Single Day Selector binding
const selectDay = (cell) => {
    if (!cell.isCurrentMonth) return;
    if (selectedDay.value === cell.dayNumber) {
        selectedDay.value = null; // Unselect if clicked again
    } else {
        selectedDay.value = cell.dayNumber;
    }
};

// Computed list view filter feeding real-time panel items based strictly on active user selection state
const activeSideItems = computed(() => {
    // Option A: Specific date square filter active
    if (selectedDay.value !== null) {
        return props.renewals.filter(r => {
            if (!r.renewal_date) return false;
            const rDate = new Date(r.renewal_date);
            return !isNaN(rDate) && 
                   rDate.getDate() === selectedDay.value && 
                   rDate.getMonth() === viewedMonth.value && 
                   rDate.getFullYear() === viewedYear.value;
        });
    }
    
    // Option B: Active viewed month wide snapshot collection filter
    return props.renewals.filter(r => {
        if (!r.renewal_date) return false;
        const rDate = new Date(r.renewal_date);
        return !isNaN(rDate) && 
               rDate.getMonth() === viewedMonth.value && 
               rDate.getFullYear() === viewedYear.value;
    });
});

// ----------------------------------------------------
// Table Filters Triggers
// ----------------------------------------------------
let filterTimeout = null;

const triggerFilter = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get('/renewals', {
            search: searchQuery.value,
            type: selectedType.value,
            priority: selectedPriority.value,
            filter_month: selectedMonth.value,
            filter_year: selectedYear.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 250);
};

const getTypeIcon = (typeStr) => {
    switch (typeStr) {
        case 'warranty': return 'fas fa-shield-alt text-orange-600';
        case 'license': return 'fas fa-key text-yellow-600';
        default: return 'fas fa-credit-card text-blue-600';
    }
};

const getDaysLeftColor = (priority) => {
    switch (priority) {
        case 'urgent': return 'text-red-600';
        case 'soon': return 'text-yellow-600';
        default: return 'text-green-600';
    }
};

const deleteRenewal = (id) => {
    if (confirm('Are you sure you want to revoke and clear this recurring timeline monitor?')) {
        router.delete(`/renewals/${id}`, {
            preserveScroll: true,
        });
    }
};

// ----------------------------------------------------
// Renewal Modal State
// ----------------------------------------------------
const showRenewModal = ref(false);
const renewModalData = ref({});
const renewLoading = ref(false);
const renewError = ref('');
const computedNewRenewalDate = ref('');

const renewForm = ref({
    amount: '',
    payment_method: '****4242',
    method_type: 'visa',
    payment_date: '',
    billing_cycle: 'monthly',
    currency: 'USD',
    usd_amount: '',
    status: 'successful',
});

// Toast notification
const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

const showToast = (message, type = 'success') => {
    clearTimeout(toastTimer);
    toast.value = { show: true, message, type };
    toastTimer = setTimeout(() => {
        toast.value.show = false;
    }, 4000);
};

// Get duration_months for a billing cycle code from props
const getBillingCycleDuration = (code) => {
    const bc = (props.billingCycles || []).find(b => b.code === code);
    return bc ? bc.duration_months : 1;
};

// Add N months to a date string, return formatted display string
const addMonthsToDate = (dateStr, months) => {
    const d = new Date(dateStr);
    if (isNaN(d)) return '';
    d.setMonth(d.getMonth() + months);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
};

const updateNewRenewalDate = () => {
    if (!renewForm.value.payment_date || !renewForm.value.billing_cycle) {
        computedNewRenewalDate.value = '';
        return;
    }
    const months = getBillingCycleDuration(renewForm.value.billing_cycle);
    computedNewRenewalDate.value = addMonthsToDate(renewForm.value.payment_date, months);
};

// Strip leading '$' and commas from a value string like '$2,450.00'
const parseAmount = (valStr) => {
    if (!valStr) return '';
    return String(valStr).replace(/[^0-9.]/g, '');
};

// Detect billing cycle from plan_asset string e.g. "Payment Cycle (Monthly)"
const detectBillingCycle = (ren) => {
    if (ren.billing_cycle) return ren.billing_cycle;
    const pa = (ren.plan_asset || '').toLowerCase();
    if (pa.includes('annual')) return 'annual';
    if (pa.includes('quarterly')) return 'quarterly';
    if (pa.includes('half')) return 'half_yearly';
    if (pa.includes('monthly') || pa.includes('month')) return 'monthly';
    return 'monthly';
};

const openRenewModal = (ren) => {
    renewError.value = '';
    renewModalData.value = { ...ren };

    const todayIso = new Date().toISOString().split('T')[0];
    const detectedCycle = detectBillingCycle(ren);

    renewForm.value = {
        amount:         parseAmount(ren.current_value),
        payment_method: '****4242',
        method_type:    'visa',
        payment_date:   todayIso,
        billing_cycle:  detectedCycle,
        currency:       'USD',
        usd_amount:     parseAmount(ren.current_value),
        status:         'successful',
    };

    const months = getBillingCycleDuration(detectedCycle);
    computedNewRenewalDate.value = addMonthsToDate(todayIso, months);

    showRenewModal.value = true;
    document.body.style.overflow = 'hidden';
};

const closeRenewModal = () => {
    if (renewLoading.value) return;
    showRenewModal.value = false;
    renewError.value = '';
    document.body.style.overflow = '';
};

const submitRenew = async () => {
    renewError.value = '';

    if (!renewForm.value.amount || isNaN(parseFloat(renewForm.value.amount))) {
        renewError.value = 'Please enter a valid renewal amount.';
        return;
    }
    if (!renewForm.value.payment_date) {
        renewError.value = 'Please select a payment date.';
        return;
    }

    renewLoading.value = true;

    try {
        const renewalId = renewModalData.value.id;

        const dateObj = new Date(renewForm.value.payment_date);
        const formattedDate = dateObj.toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: '2-digit',
        });

        const payload = {
            amount:         parseFloat(renewForm.value.amount),
            payment_method: renewForm.value.payment_method,
            method_type:    renewForm.value.method_type,
            payment_date:   formattedDate,
            billing_cycle:  renewForm.value.billing_cycle,
            currency:       renewForm.value.currency,
            usd_amount:     parseFloat(renewForm.value.usd_amount) || parseFloat(renewForm.value.amount),
            status:         renewForm.value.status,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            || window.Laravel?.csrfToken 
            || '';

        const response = await fetch(`/renewals/${renewalId}/renew`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (response.status === 409 && data.already_renewed) {
            renewError.value = data.message;
            renewLoading.value = false;
            return;
        }

        if (!response.ok) {
            renewError.value = data.error || data.message || 'Renewal failed. Please try again.';
            renewLoading.value = false;
            return;
        }

        // Success — close modal, toast, refresh table preserving page
        closeRenewModal();
        showToast(data.message || 'Renewal successful!', 'success');

        const savedPage = currentPage.value;
        router.get('/renewals', {
            search:       searchQuery.value,
            type:         selectedType.value,
            priority:     selectedPriority.value,
            filter_month: selectedMonth.value,
            filter_year:  selectedYear.value,
            page:         savedPage,
        }, {
            preserveState:  false,
            preserveScroll: true,
            replace:        true,
            onSuccess: () => {
                currentPage.value = savedPage;
            },
        });

    } catch (err) {
        renewError.value = 'An unexpected error occurred. Please try again.';
    } finally {
        renewLoading.value = false;
    }
};
</script>

<style scoped>
/* Modal fade transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

/* Toast slide-up transition */
.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.toast-slide-enter-from,
.toast-slide-leave-to {
    transform: translateY(20px);
    opacity: 0;
}
</style>
