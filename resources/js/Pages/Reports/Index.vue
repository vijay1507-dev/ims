<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Reports & Analytics</h2>
                    <p class="text-gray-600 mt-1">Real-time analytical metrics mapped strictly from relational database layers</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200 mb-8 gap-8">
                <button
                    @click="activeTab = 'standard'"
                    :class="[
                        activeTab === 'standard'
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'pb-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer'
                    ]"
                >
                    <i class="fas fa-chart-line"></i> Standard Reports
                </button>
                <button
                    @click="activeTab = 'comparison'"
                    :class="[
                        activeTab === 'comparison'
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'pb-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer'
                    ]"
                >
                    <i class="fas fa-exchange-alt"></i> Comparison Analytics
                </button>
                <button
                    @click="activeTab = 'receivedOutstanding'"
                    :class="[
                        activeTab === 'receivedOutstanding'
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'pb-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer'
                    ]"
                >
                    <i class="fas fa-balance-scale"></i> Received vs Outstanding
                </button>
                <button
                    @click="activeTab = 'pnl'"
                    :class="[
                        activeTab === 'pnl'
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'pb-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer'
                    ]"
                >
                    <i class="fas fa-file-invoice-dollar"></i> Profit & Loss (P&L)
                </button>
            </div>

            <!-- TAB 1: STANDARD REPORTS -->
            <div v-if="activeTab === 'standard'" class="space-y-8">
                <!-- Primary Global Database Status KPI Metric Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Total Gross Revenue -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Gross Revenue Sum</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.total_revenue }}</p>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full mt-2 inline-block">Payments Ledger Query</span>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-lg shrink-0">
                            <i class="fas fa-coins text-emerald-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>

                    <!-- New Customers -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">New Customers</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.new_customers }}</p>
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full mt-2 inline-block">Total registered accounts</span>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-lg shrink-0">
                            <i class="fas fa-user-plus text-indigo-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>

                    <!-- Renewed Customers -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Renewed Customers</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.renewed_customers }}</p>
                            <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full mt-2 inline-block">Unique renewal payments</span>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-lg shrink-0">
                            <i class="fas fa-user-sync text-orange-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>

                    <!-- Total Customer Activity -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Customer Activity</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.total_customer_activity }}</p>
                            <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full mt-2 inline-block">Total registered + Renewals</span>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg shrink-0">
                            <i class="fas fa-users text-purple-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>
                </div>

                <!-- Tabular Reports Grid -->
                <div class="space-y-8 animate-fade-in">
                    <!-- Standard Sub Tabs -->
                    <div class="flex space-x-2 bg-gray-100 p-1.5 rounded-lg w-fit mb-4 flex-wrap gap-y-1">
                        <button
                            v-for="subTab in ['customers', 'sales', 'payments', 'subscriptions', 'top_paid']"
                            :key="subTab"
                            @click="activeStandardTab = subTab"
                            :class="[
                                activeStandardTab === subTab
                                    ? 'bg-white text-indigo-600 font-bold shadow-2xs'
                                    : 'text-gray-600 hover:text-gray-900',
                                'px-4 py-2 text-sm rounded-md capitalize transition-all duration-200 cursor-pointer font-semibold'
                            ]"
                        >
                            {{ subTab === 'customers' ? 'Customers Joined' : (subTab === 'sales' ? 'Total Sales' : (subTab === 'payments' ? 'Payments' : (subTab === 'subscriptions' ? 'Subscriptions' : 'Top 5 Paid'))) }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                         <!-- Graph (Left column) -->
                         <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                             <div class="flex items-center justify-between mb-6">
                                 <h3 class="font-bold text-gray-900 text-base">Trend Visualization</h3>
                                 <!-- If payments, show received vs pending toggle -->
                                 <div v-if="activeStandardTab === 'payments'" class="flex space-x-1 bg-gray-50 border border-gray-200 p-0.5 rounded-lg">
                                     <button 
                                         @click="activePaymentsSubTab = 'received'"
                                         :class="[
                                             activePaymentsSubTab === 'received' ? 'bg-white text-indigo-600 font-bold shadow-2xs' : 'text-gray-500',
                                             'px-2.5 py-1 text-xs rounded-md transition-all duration-200 cursor-pointer font-semibold'
                                         ]"
                                     >
                                         Received
                                     </button>
                                     <button 
                                         @click="activePaymentsSubTab = 'pending'"
                                         :class="[
                                             activePaymentsSubTab === 'pending' ? 'bg-white text-indigo-600 font-bold shadow-2xs' : 'text-gray-500',
                                             'px-2.5 py-1 text-xs rounded-md transition-all duration-200 cursor-pointer font-semibold'
                                         ]"
                                     >
                                         Pending
                                     </button>
                                 </div>
                             </div>
                             <BaseChart 
                                 :type="activeStandardTab === 'top_paid' ? 'bar' : 'line'"
                                 :data="standardChartData"
                                 :options="standardChartOptions"
                                 :height="320"
                             />
                         </div>

                         <!-- Table (Right 1 column) -->
                         <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                             <!-- CUSTOMERS TAB -->
                             <template v-if="activeStandardTab === 'customers'">
                                 <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
                                     <h3 class="font-bold text-gray-900 text-base">Customer Activity Ledger</h3>
                                     <select v-model="selectedYearCustomers" class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer">
                                         <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                     </select>
                                 </div>
                                 <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                                     <table class="min-w-full divide-y divide-gray-200 text-sm">
                                         <thead>
                                             <tr class="bg-gray-50">
                                                 <th class="px-4 py-2 text-left font-bold text-gray-600">Month</th>
                                                 <th class="px-4 py-2 text-right font-bold text-gray-600">New Customers</th>
                                                 <th class="px-4 py-2 text-right font-bold text-gray-600">Renewed Customers</th>
                                                 <th class="px-4 py-2 text-center font-bold text-gray-600">Actions</th>
                                             </tr>
                                         </thead>
                                         <tbody class="divide-y divide-gray-100">
                                             <tr v-for="row in filteredCustomers" :key="row.month" class="hover:bg-gray-55 transition-colors cursor-pointer" @click="goToDetail('customers', row.month)">
                                                 <td class="px-4 py-3 font-medium text-gray-900">{{ row.month }}</td>
                                                 <td class="px-4 py-3 text-right text-indigo-600 font-bold">{{ row.new_count }}</td>
                                                 <td class="px-4 py-3 text-right text-orange-600 font-bold">{{ row.renewed_count }}</td>
                                                 <td class="px-4 py-3 text-center">
                                                     <Link :href="route('reports.detail', { type: 'customers', month: row.month })" class="text-xs text-indigo-600 hover:text-indigo-900 font-semibold">View List <i class="fas fa-arrow-right ml-1"></i></Link>
                                                 </td>
                                             </tr>
                                             <tr v-if="!filteredCustomers || filteredCustomers.length === 0">
                                                 <td colspan="4" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>
                             </template>

                             <!-- SALES TAB -->
                             <template v-if="activeStandardTab === 'sales'">
                                 <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
                                     <h3 class="font-bold text-gray-900 text-base">Sales Ledger</h3>
                                     <select v-model="selectedYearTotalSales" class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer">
                                         <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                     </select>
                                 </div>
                                 <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                                     <table class="min-w-full divide-y divide-gray-200 text-sm">
                                         <thead>
                                             <tr class="bg-gray-50">
                                                 <th class="px-4 py-2 text-left font-bold text-gray-600">Month</th>
                                                 <th class="px-4 py-2 text-right font-bold text-gray-600">Volume / Value</th>
                                                 <th class="px-4 py-2 text-center font-bold text-gray-600">Actions</th>
                                             </tr>
                                         </thead>
                                         <tbody class="divide-y divide-gray-100">
                                             <tr v-for="row in filteredTotalSales" :key="row.month" class="hover:bg-gray-55 transition-colors cursor-pointer" @click="goToDetail('totalSales', row.month)">
                                                 <td class="px-4 py-3 font-medium text-gray-900">{{ row.month }}</td>
                                                 <td class="px-4 py-3 text-right text-emerald-600 font-bold">
                                                     {{ row.count }} sales ({{ formatCurrency(row.total) }})
                                                 </td>
                                                 <td class="px-4 py-3 text-center">
                                                     <Link :href="route('reports.detail', { type: 'totalSales', month: row.month })" class="text-xs text-emerald-600 hover:text-emerald-900 font-semibold">View List <i class="fas fa-arrow-right ml-1"></i></Link>
                                                 </td>
                                             </tr>
                                             <tr v-if="!filteredTotalSales || filteredTotalSales.length === 0">
                                                 <td colspan="3" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>
                             </template>

                             <!-- PAYMENTS TAB -->
                             <template v-if="activeStandardTab === 'payments'">
                                 <div v-if="activePaymentsSubTab === 'received'">
                                     <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
                                         <h3 class="font-bold text-gray-900 text-base">Collections</h3>
                                         <select v-model="selectedYearReceivedPayments" class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer">
                                             <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                         </select>
                                     </div>
                                     <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                                         <table class="min-w-full divide-y divide-gray-200 text-sm">
                                             <thead>
                                                 <tr class="bg-gray-50">
                                                     <th class="px-4 py-2 text-left font-bold text-gray-600">Month</th>
                                                     <th class="px-4 py-2 text-right font-bold text-gray-600">Volume / Value</th>
                                                     <th class="px-4 py-2 text-center font-bold text-gray-600">Actions</th>
                                                 </tr>
                                             </thead>
                                             <tbody class="divide-y divide-gray-100">
                                                 <tr v-for="row in filteredReceivedPayments" :key="row.month" class="hover:bg-gray-55 transition-colors cursor-pointer" @click="goToDetail('receivedPayments', row.month)">
                                                     <td class="px-4 py-3 font-medium text-gray-900">{{ row.month }}</td>
                                                     <td class="px-4 py-3 text-right text-blue-600 font-bold">
                                                         {{ row.count }} payments ({{ formatCurrency(row.total) }})
                                                     </td>
                                                     <td class="px-4 py-3 text-center">
                                                         <Link :href="route('reports.detail', { type: 'receivedPayments', month: row.month })" class="text-xs text-blue-600 hover:text-blue-900 font-semibold">View List <i class="fas fa-arrow-right ml-1"></i></Link>
                                                     </td>
                                                 </tr>
                                                 <tr v-if="!filteredReceivedPayments || filteredReceivedPayments.length === 0">
                                                     <td colspan="3" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                                                 </tr>
                                             </tbody>
                                         </table>
                                     </div>
                                 </div>
                                 <div v-else>
                                     <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
                                         <h3 class="font-bold text-gray-900 text-base">Receivables</h3>
                                         <select v-model="selectedYearPendingPayments" class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer">
                                             <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                         </select>
                                     </div>
                                     <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                                         <table class="min-w-full divide-y divide-gray-200 text-sm">
                                             <thead>
                                                 <tr class="bg-gray-50">
                                                     <th class="px-4 py-2 text-left font-bold text-gray-600">Month</th>
                                                     <th class="px-4 py-2 text-right font-bold text-gray-600">Outstanding Value</th>
                                                     <th class="px-4 py-2 text-center font-bold text-gray-600">Actions</th>
                                                 </tr>
                                             </thead>
                                             <tbody class="divide-y divide-gray-100">
                                                 <tr v-for="row in filteredPendingPayments" :key="row.month" class="hover:bg-gray-55 transition-colors cursor-pointer" @click="goToDetail('pendingPayments', row.month)">
                                                     <td class="px-4 py-3 font-medium text-gray-900">{{ row.month }}</td>
                                                     <td class="px-4 py-3 text-right text-amber-600 font-bold">
                                                         {{ row.count }} outstanding ({{ formatCurrency(row.total) }})
                                                     </td>
                                                     <td class="px-4 py-3 text-center">
                                                         <Link :href="route('reports.detail', { type: 'pendingPayments', month: row.month })" class="text-xs text-amber-600 hover:text-amber-900 font-semibold">View List <i class="fas fa-arrow-right ml-1"></i></Link>
                                                     </td>
                                                 </tr>
                                                 <tr v-if="!filteredPendingPayments || filteredPendingPayments.length === 0">
                                                     <td colspan="3" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                                                 </tr>
                                             </tbody>
                                         </table>
                                     </div>
                                 </div>
                             </template>

                             <!-- SUBSCRIPTIONS TAB -->
                             <template v-if="activeStandardTab === 'subscriptions'">
                                 <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
                                     <h3 class="font-bold text-gray-900 text-base">Active Plans</h3>
                                     <select v-model="selectedYearSubscriptions" class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer">
                                         <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                     </select>
                                 </div>
                                 <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                                     <table class="min-w-full divide-y divide-gray-200 text-sm">
                                         <thead>
                                             <tr class="bg-gray-50">
                                                 <th class="px-4 py-2 text-left font-bold text-gray-600">Month</th>
                                                 <th class="px-4 py-2 text-right font-bold text-gray-600">Total Subscriptions</th>
                                                 <th class="px-4 py-2 text-center font-bold text-gray-600">Actions</th>
                                             </tr>
                                         </thead>
                                         <tbody class="divide-y divide-gray-100">
                                             <tr v-for="row in filteredSubscriptions" :key="row.month" class="hover:bg-gray-55 transition-colors cursor-pointer" @click="goToDetail('subscriptions', row.month)">
                                                 <td class="px-4 py-3 font-medium text-gray-900">{{ row.month }}</td>
                                                 <td class="px-4 py-3 text-right text-purple-600 font-bold">{{ row.count }} active/trial</td>
                                                 <td class="px-4 py-3 text-center">
                                                     <Link :href="route('reports.detail', { type: 'subscriptions', month: row.month })" class="text-xs text-purple-600 hover:text-purple-900 font-semibold">View List <i class="fas fa-arrow-right ml-1"></i></Link>
                                                 </td>
                                             </tr>
                                             <tr v-if="!filteredSubscriptions || filteredSubscriptions.length === 0">
                                                 <td colspan="3" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>
                             </template>

                              <!-- RENEWALS TAB -->
                              <template v-if="activeStandardTab === 'renewals'">
                                  <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
                                      <h3 class="font-bold text-gray-900 text-base">Renewals Ledger</h3>
                                      <select v-model="selectedYearRenewals" class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer">
                                          <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                      </select>
                                  </div>
                                  <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                                      <table class="min-w-full divide-y divide-gray-200 text-sm">
                                          <thead>
                                              <tr class="bg-gray-50">
                                                  <th class="px-4 py-2 text-left font-bold text-gray-600">Month</th>
                                                  <th class="px-4 py-2 text-right font-bold text-gray-600">Total Renewals</th>
                                                  <th class="px-4 py-2 text-center font-bold text-gray-600">Actions</th>
                                              </tr>
                                          </thead>
                                          <tbody class="divide-y divide-gray-100">
                                              <tr v-for="row in filteredRenewals" :key="row.month" class="hover:bg-gray-55 transition-colors cursor-pointer" @click="goToDetail('renewals', row.month)">
                                                  <td class="px-4 py-3 font-medium text-gray-900">{{ row.month }}</td>
                                                  <td class="px-4 py-3 text-right text-orange-600 font-bold">{{ row.count }} pending renewals</td>
                                                  <td class="px-4 py-3 text-center">
                                                      <Link :href="route('reports.detail', { type: 'renewals', month: row.month })" class="text-xs text-orange-600 hover:text-orange-900 font-semibold">View List <i class="fas fa-arrow-right ml-1"></i></Link>
                                                  </td>
                                              </tr>
                                              <tr v-if="!filteredRenewals || filteredRenewals.length === 0">
                                                  <td colspan="3" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                                              </tr>
                                          </tbody>
                                      </table>
                                  </div>
                              </template>

                              <!-- TOP PAID CUSTOMERS TAB -->
                              <template v-if="activeStandardTab === 'top_paid'">
                                  <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
                                      <h3 class="font-bold text-gray-900 text-base">Top 5 Paid Customers</h3>
                                  </div>
                                  <div class="overflow-x-auto max-h-[320px] overflow-y-auto">
                                      <table class="min-w-full divide-y divide-gray-200 text-sm">
                                          <thead>
                                              <tr class="bg-gray-50">
                                                  <th class="px-4 py-2 text-center font-bold text-gray-600 w-12">Rank</th>
                                                  <th class="px-4 py-2 text-left font-bold text-gray-600">Customer Name</th>
                                                  <th class="px-4 py-2 text-center font-bold text-gray-600">Payments</th>
                                                  <th class="px-4 py-2 text-right font-bold text-gray-600">Total USD Spent</th>
                                              </tr>
                                          </thead>
                                          <tbody class="divide-y divide-gray-100">
                                              <tr v-for="(row, idx) in topPaidCustomers" :key="row.name" class="hover:bg-amber-50/50 transition-colors">
                                                  <td class="px-4 py-3 text-center font-bold text-gray-500">
                                                      <span :class="[
                                                          idx === 0 ? 'bg-amber-500 text-white' : (idx === 1 ? 'bg-gray-400 text-white' : (idx === 2 ? 'bg-amber-700 text-white' : 'bg-gray-200 text-gray-700')),
                                                          'w-6 h-6 rounded-full inline-flex items-center justify-center text-xs'
                                                      ]">
                                                          {{ idx + 1 }}
                                                      </span>
                                                  </td>
                                                  <td class="px-4 py-3 font-semibold text-gray-900">{{ row.name }}</td>
                                                  <td class="px-4 py-3 text-center text-gray-600">{{ row.payments_count }} payments</td>
                                                  <td class="px-4 py-3 text-right text-amber-600 font-bold">
                                                      {{ row.total_spent }}
                                                  </td>
                                              </tr>
                                              <tr v-if="!topPaidCustomers || topPaidCustomers.length === 0">
                                                  <td colspan="4" class="px-4 py-8 text-center text-gray-500">No records found.</td>
                                              </tr>
                                          </tbody>
                                      </table>
                                  </div>
                              </template>
                         </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: COMPARISON ANALYTICS -->
            <div v-else-if="activeTab === 'comparison'" class="space-y-8 animate-fade-in">
                <!-- Filters panel -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-500 uppercase mb-1.5">Comparison Mode</span>
                        <div class="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-50">
                            <button
                                @click="comparisonMode = 'yoy'"
                                :class="[
                                    comparisonMode === 'yoy'
                                        ? 'bg-indigo-600 text-white font-semibold shadow-xs'
                                        : 'text-gray-600 hover:text-gray-900',
                                    'px-4 py-1.5 text-sm rounded-md transition-all duration-200 cursor-pointer'
                                ]"
                            >
                                Year-over-Year (YoY)
                            </button>
                            <button
                                @click="comparisonMode = 'mom'"
                                :class="[
                                    comparisonMode === 'mom'
                                        ? 'bg-indigo-600 text-white font-semibold shadow-xs'
                                        : 'text-gray-600 hover:text-gray-900',
                                    'px-4 py-1.5 text-sm rounded-md transition-all duration-200 cursor-pointer'
                                ]"
                            >
                                Month-over-Month (MoM)
                            </button>
                            <button
                                @click="comparisonMode = 'qoq'"
                                :class="[
                                    comparisonMode === 'qoq'
                                        ? 'bg-indigo-600 text-white font-semibold shadow-xs'
                                        : 'text-gray-600 hover:text-gray-900',
                                    'px-4 py-1.5 text-sm rounded-md transition-all duration-200 cursor-pointer'
                                ]"
                            >
                                Quarter-over-Quarter (QoQ)
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-500 uppercase mb-1.5">Select Primary Year</span>
                        <select
                            v-model="selectedComparisonYear"
                            class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-3 py-1.5 cursor-pointer min-w-[120px]"
                        >
                            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>
                </div>

                <!-- Sub-tabs for metric selection -->
                <div class="flex space-x-2 bg-gray-100 p-1.5 rounded-lg w-fit">
                    <button
                        v-for="sub in ['sales', 'revenue', 'customers']"
                        :key="sub"
                        @click="activeSubMetric = sub"
                        :class="[
                            activeSubMetric === sub
                                ? 'bg-white text-indigo-600 font-bold shadow-2xs'
                                : 'text-gray-600 hover:text-gray-900',
                            'px-4 py-2 text-sm rounded-md capitalize transition-all duration-200 cursor-pointer'
                        ]"
                    >
                        <i :class="[
                            sub === 'sales' ? 'fas fa-shopping-cart mr-1' : '',
                            sub === 'revenue' ? 'fas fa-hand-holding-usd mr-1' : '',
                            sub === 'customers' ? 'fas fa-user-plus mr-1' : ''
                        ]"></i>
                        {{ sub === 'sales' ? 'Sales Invoices' : (sub === 'revenue' ? 'Received Revenue' : 'New Customers') }}
                    </button>
                </div>

                <!-- Dynamic Comparison Table -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base capitalize">
                                {{ activeSubMetric === 'sales' ? 'Sales Invoices' : (activeSubMetric === 'revenue' ? 'Received Revenue' : 'New Customers') }} Comparison Table
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Detailed comparison analysis list showing changes for each period</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="bg-gray-50" v-if="comparisonMode === 'qoq'">
                                    <th class="px-6 py-3 text-left font-bold text-gray-600">Quarter</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">{{ selectedComparisonYear }} (Primary)</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Previous Quarter</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Absolute Change</th>
                                    <th class="px-6 py-3 text-center font-bold text-gray-600">Change (%)</th>
                                </tr>
                                <tr class="bg-gray-50" v-else>
                                    <th class="px-6 py-3 text-left font-bold text-gray-600">Month</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">
                                        {{ selectedComparisonYear }} {{ activeSubMetric === 'sales' ? 'Sales' : (activeSubMetric === 'revenue' ? 'Revenue' : 'Customers') }}
                                    </th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">
                                        {{ comparisonMode === 'yoy' ? (parseInt(selectedComparisonYear) - 1) + ' ' + (activeSubMetric === 'sales' ? 'Sales' : (activeSubMetric === 'revenue' ? 'Revenue' : 'Customers')) : 'Previous Month' }}
                                    </th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Absolute Change</th>
                                    <th class="px-6 py-3 text-center font-bold text-gray-600">Change %</th>
                                </tr>
                            </thead>
                            
                            <!-- QoQ Body -->
                            <tbody class="divide-y divide-gray-100" v-if="comparisonMode === 'qoq'">
                                <tr v-for="row in calculatedQuarterlyComparisons" :key="row.quarterName" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ row.quarterName }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                        {{ activeSubMetric === 'customers' ? row[activeSubMetric].current : formatCurrency(row[activeSubMetric].current) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-500 font-medium">
                                        {{ activeSubMetric === 'customers' ? row[activeSubMetric].previous : formatCurrency(row[activeSubMetric].previous) }}
                                    </td>
                                    <td :class="[
                                        row[activeSubMetric].difference > 0 ? 'text-emerald-600 font-bold' : '',
                                        row[activeSubMetric].difference < 0 ? 'text-rose-600 font-bold' : '',
                                        row[activeSubMetric].difference === 0 ? 'text-gray-500' : '',
                                        'px-6 py-4 text-right'
                                    ]">
                                        {{ row[activeSubMetric].difference > 0 ? '+' : '' }}{{ activeSubMetric === 'customers' ? row[activeSubMetric].difference : formatCurrency(row[activeSubMetric].difference) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="row[activeSubMetric].difference > 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                            +{{ row[activeSubMetric].percentage.toFixed(1) }}%
                                        </span>
                                        <span v-else-if="row[activeSubMetric].difference < 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                                            {{ row[activeSubMetric].percentage.toFixed(1) }}%
                                        </span>
                                        <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-500">
                                            0.0%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- YoY / MoM Body -->
                            <tbody class="divide-y divide-gray-100" v-else>
                                <tr v-for="row in calculatedComparisons" :key="row.monthIndex" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        {{ comparisonMode === 'mom' ? row.monthName + ' ' + selectedComparisonYear : row.monthName }}
                                    </td>
                                    
                                    <!-- Primary Value -->
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                        {{ activeSubMetric === 'customers' ? row[activeSubMetric].current : formatCurrency(row[activeSubMetric].current) }}
                                    </td>
                                    
                                    <!-- Reference Value -->
                                    <td class="px-6 py-4 text-right text-gray-600 font-medium">
                                        {{ activeSubMetric === 'customers' ? row[activeSubMetric].previous : formatCurrency(row[activeSubMetric].previous) }}
                                    </td>
                                    
                                    <!-- Absolute Change -->
                                    <td :class="[
                                        row[activeSubMetric].difference > 0 ? 'text-emerald-600 font-semibold' : '',
                                        row[activeSubMetric].difference < 0 ? 'text-rose-600 font-semibold' : '',
                                        row[activeSubMetric].difference === 0 ? 'text-gray-500 font-medium' : '',
                                        'px-6 py-4 text-right'
                                    ]">
                                        {{ row[activeSubMetric].difference > 0 ? '+' : '' }}{{ activeSubMetric === 'customers' ? row[activeSubMetric].difference : formatCurrency(row[activeSubMetric].difference) }}
                                    </td>
                                    
                                    <!-- Percentage Change Badge -->
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="row[activeSubMetric].difference > 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                            <i class="fas fa-caret-up text-[10px]"></i>
                                            {{ row[activeSubMetric].percentage.toFixed(1) }}%
                                        </span>
                                        <span v-else-if="row[activeSubMetric].difference < 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                                            <i class="fas fa-caret-down text-[10px]"></i>
                                            {{ Math.abs(row[activeSubMetric].percentage).toFixed(1) }}%
                                        </span>
                                        <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-500">
                                            0.0%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: RECEIVED VS OUTSTANDING -->
            <div v-else-if="activeTab === 'receivedOutstanding'" class="space-y-8 animate-fade-in">
                <!-- Filters panel -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-500 uppercase mb-1.5">Select Report Year</span>
                            <select
                                v-model="selectedReceivedOutstandingYear"
                                class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-3 py-1.5 cursor-pointer min-w-[120px]"
                            >
                                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 max-w-xs self-start md:self-auto">
                        <span class="font-medium text-indigo-700 block">
                            Received Payments vs. Outstanding Invoices
                        </span>
                        <span>Analysis of collection rates and unpaid sales for {{ selectedReceivedOutstandingYear }}.</span>
                    </div>
                </div>

                <!-- Summary Cards for selected year -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Sales Invoiced</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(receivedOutstandingTotals.sales) }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider text-emerald-600">Total Collected</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ formatCurrency(receivedOutstandingTotals.received) }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider text-rose-600">Total Outstanding</p>
                        <p class="text-2xl font-bold text-rose-600 mt-1">{{ formatCurrency(receivedOutstandingTotals.outstanding) }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider text-indigo-600">Annual Collection Rate</p>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ receivedOutstandingTotals.rate.toFixed(1) }}%</p>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 text-base">Collections & Receivables Breakdown</h3>
                        <p class="text-xs text-gray-500 mt-1">Monthly collection progress and outstanding receivables comparison</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left font-bold text-gray-600">Month</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Total Sales</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Received Payments</th>
                                    <th class="px-6 py-3 text-right font-bold text-gray-600">Outstanding Payments</th>
                                    <th class="px-6 py-3 text-center font-bold text-gray-600">Collection Rate (%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="row in calculatedReceivedOutstanding" :key="row.monthIndex" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ row.monthName }}</td>
                                    <td class="px-6 py-4 text-right text-gray-900 font-medium">{{ formatCurrency(row.sales) }}</td>
                                    <td class="px-6 py-4 text-right text-emerald-600 font-medium">{{ formatCurrency(row.received) }}</td>
                                    <td class="px-6 py-4 text-right text-rose-600 font-medium">{{ formatCurrency(row.outstanding) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="[
                                            row.rate >= 80 ? 'bg-emerald-50 text-emerald-700' : '',
                                            row.rate >= 50 && row.rate < 80 ? 'bg-amber-50 text-amber-700' : '',
                                            row.rate < 50 ? 'bg-rose-50 text-rose-700' : '',
                                            'inline-flex px-2.5 py-1 rounded-full text-xs font-bold'
                                        ]">
                                            {{ row.rate.toFixed(1) }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: PROFIT & LOSS (PNL) -->
            <div v-else-if="activeTab === 'pnl'" class="space-y-8 animate-fade-in">
                <!-- Tab Header Info -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Profit & Loss Analysis</h3>
                        <p class="text-xs text-gray-500 mt-1">SaaS recurring revenues mapped against business operational expenses & purchases</p>
                    </div>
                </div>


                <!-- KPI Cards (showing left data as the primary high-level summary) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Gross Revenue</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(leftPnlData.revenue) }}</p>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full mt-2 inline-block">Selected Inflows</span>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-lg shrink-0">
                            <i class="fas fa-coins text-emerald-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Outflows</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(leftPnlData.totalOutflows) }}</p>
                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full mt-2 inline-block">Selected Outflows</span>
                        </div>
                        <div class="bg-rose-100 p-3 rounded-lg shrink-0">
                            <i class="fas fa-shopping-basket text-rose-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                                <span v-if="leftPnlData.netProfit >= 0">🟢 Net Profit</span>
                                <span v-else>🔴 Net Loss</span>
                            </p>
                            <p :class="[leftPnlData.netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600', 'text-2xl font-bold mt-1']">
                                <span v-if="leftPnlData.netProfit < 0">-</span>{{ formatCurrency(Math.abs(leftPnlData.netProfit)) }}
                            </p>
                            <span :class="[leftPnlData.netProfit >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50', 'text-[10px] font-bold px-2 py-0.5 rounded-full mt-2 inline-block']">
                                Selected Net Earnings
                            </span>
                        </div>
                        <div :class="[leftPnlData.netProfit >= 0 ? 'bg-emerald-100' : 'bg-rose-100', 'p-3 rounded-lg shrink-0']">
                            <i :class="[leftPnlData.netProfit >= 0 ? 'fas fa-arrow-trend-up text-emerald-600' : 'fas fa-arrow-trend-down text-rose-600', 'text-xl w-5 text-center']"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Net Profit Margin</p>
                            <p :class="[leftPnlData.margin >= 0 ? 'text-indigo-600' : 'text-rose-600', 'text-2xl font-bold mt-1']">
                                {{ leftPnlData.margin.toFixed(1) }}%
                            </p>
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full mt-2 inline-block">Selected margin</span>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-lg shrink-0">
                            <i class="fas fa-percent text-indigo-600 text-xl w-5 text-center"></i>
                        </div>
                    </div>
                </div>

                <!-- PNL Trend Chart -->
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-gray-900 text-base">Revenue vs. Expenses Trend</h3>
                        <span class="text-xs text-gray-500">Monthly breakdown for {{ leftPnlYear }}</span>
                    </div>
                    <div class="h-[340px]">
                        <BaseChart 
                            type="bar"
                            :data="pnlChartData"
                            :options="pnlChartOptions"
                            :height="340"
                        />
                    </div>
                </div>

                <!-- Financial Statement Tables Side-by-Side -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left: P&L Card 1 -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base">Income Statement (P&L) - Left</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-500 uppercase">Period:</span>
                                <select
                                    v-model="leftPnlMonth"
                                    class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer min-w-[100px]"
                                >
                                    <option value="all">Full Year</option>
                                    <option v-for="(name, idx) in monthNames" :key="idx" :value="(idx + 1).toString()">{{ name }}</option>
                                </select>
                                <select
                                    v-model="leftPnlYear"
                                    class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer min-w-[80px]"
                                >
                                    <option v-for="year in yearsToUse" :key="year" :value="year">{{ year }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left font-bold text-gray-700 min-w-[150px] w-3/5">Financial Line Item</th>
                                        <th class="px-6 py-3 text-right font-bold text-gray-900 w-2/5">
                                            {{ leftPnlData.label }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <!-- SECTION: REVENUE -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">REVENUE</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">SaaS Revenue</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.saasRevenue < 0}">{{ renderTableValue(leftPnlData.saasRevenue) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Payment Processing Revenue</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.processingRevenue < 0}">{{ renderTableValue(leftPnlData.processingRevenue) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Revenue</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.otherRevenue < 0}">{{ renderTableValue(leftPnlData.otherRevenue) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Total Revenue</td>
                                        <td class="px-6 py-2.5 text-right text-emerald-600">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.totalRevenue < 0}">{{ renderTableValue(leftPnlData.totalRevenue) }}</span>
                                        </td>
                                    </tr>

                                    <!-- SECTION: COGS -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">COST OF REVENUE (COGS)</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Inventory Purchases / Cost of Goods</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.inventoryPurchases < 0}">{{ renderTableValue(leftPnlData.inventoryPurchases) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Payment Processing Fees</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.processingFees < 0}">{{ renderTableValue(leftPnlData.processingFees) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Total COGS</td>
                                        <td class="px-6 py-2.5 text-right text-rose-600">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.totalCogs < 0}">{{ renderTableValue(leftPnlData.totalCogs) }}</span>
                                        </td>
                                    </tr>

                                    <!-- GROSS PROFIT -->
                                    <tr class="font-bold text-gray-900 bg-indigo-50/40">
                                        <td class="px-6 py-3 pl-6">Gross Profit</td>
                                        <td class="px-6 py-3 text-right">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.grossProfit < 0}">{{ renderTableValue(leftPnlData.grossProfit) }}</span>
                                        </td>
                                    </tr>

                                    <!-- SECTION: OPERATING EXPENSES -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">OPERATING EXPENSES</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Software & Server</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.softwareServer < 0}">{{ renderTableValue(leftPnlData.softwareServer) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Operating Expenses</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.otherExpenses < 0}">{{ renderTableValue(leftPnlData.otherExpenses) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Total Operating Expenses</td>
                                        <td class="px-6 py-2.5 text-right text-rose-600">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.totalOpEx < 0}">{{ renderTableValue(leftPnlData.totalOpEx) }}</span>
                                        </td>
                                    </tr>

                                    <!-- OPERATING PROFIT -->
                                    <tr class="font-bold text-gray-900 bg-indigo-50/20">
                                        <td class="px-6 py-3 pl-6">Operating Profit</td>
                                        <td class="px-6 py-3 text-right">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.operatingProfit < 0}">{{ renderTableValue(leftPnlData.operatingProfit) }}</span>
                                        </td>
                                    </tr>

                                    <!-- OTHER INCOME / EXPENSES -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">OTHER INCOME / EXPENSES</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Income</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.otherIncome < 0}">{{ renderTableValue(leftPnlData.otherIncome) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Expenses</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.otherExpensesLine < 0}">{{ renderTableValue(leftPnlData.otherExpensesLine) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Profit Before Tax</td>
                                        <td class="px-6 py-2.5 text-right font-semibold text-gray-900">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.profitBeforeTax < 0}">{{ renderTableValue(leftPnlData.profitBeforeTax) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Tax Expense</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.taxExpense < 0}">{{ renderTableValue(leftPnlData.taxExpense) }}</span>
                                        </td>
                                    </tr>

                                    <!-- NET PROFIT -->
                                    <tr :class="[leftPnlData.netProfit >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white', 'font-bold']">
                                        <td class="px-6 py-3 text-base pl-6">
                                            {{ leftPnlData.netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}
                                        </td>
                                        <td class="px-6 py-3 text-right text-base">
                                            {{ leftPnlData.netProfit < 0 ? '-' : '' }}{{ formatCurrency(Math.abs(leftPnlData.netProfit)) }}
                                        </td>
                                    </tr>
                                    
                                    <!-- NET PROFIT MARGIN -->
                                    <tr class="font-bold text-indigo-700 bg-indigo-50/70">
                                        <td class="px-6 py-2.5 pl-6">Net Profit Margin %</td>
                                        <td class="px-6 py-2.5 text-right text-emerald-600">
                                            <span :class="{'text-rose-600 font-bold': leftPnlData.margin < 0}">{{ leftPnlData.margin.toFixed(1) }}%</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Right: P&L Card 2 -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base">Income Statement (P&L) - Right</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-500 uppercase">Period:</span>
                                <select
                                    v-model="rightPnlMonth"
                                    class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer min-w-[100px]"
                                >
                                    <option value="all">Full Year</option>
                                    <option v-for="(name, idx) in monthNames" :key="idx" :value="(idx + 1).toString()">{{ name }}</option>
                                </select>
                                <select
                                    v-model="rightPnlYear"
                                    class="rounded-lg border-gray-300 text-xs font-medium text-gray-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white px-2.5 py-1 cursor-pointer min-w-[80px]"
                                >
                                    <option v-for="year in yearsToUse" :key="year" :value="year">{{ year }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left font-bold text-gray-700 min-w-[150px] w-3/5">Financial Line Item</th>
                                        <th class="px-6 py-3 text-right font-bold text-gray-900 w-2/5">
                                            {{ rightPnlData.label }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <!-- SECTION: REVENUE -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">REVENUE</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">SaaS Revenue</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.saasRevenue < 0}">{{ renderTableValue(rightPnlData.saasRevenue) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Payment Processing Revenue</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.processingRevenue < 0}">{{ renderTableValue(rightPnlData.processingRevenue) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Revenue</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.otherRevenue < 0}">{{ renderTableValue(rightPnlData.otherRevenue) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Total Revenue</td>
                                        <td class="px-6 py-2.5 text-right text-emerald-600">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.totalRevenue < 0}">{{ renderTableValue(rightPnlData.totalRevenue) }}</span>
                                        </td>
                                    </tr>

                                    <!-- SECTION: COGS -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">COST OF REVENUE (COGS)</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Inventory Purchases / Cost of Goods</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.inventoryPurchases < 0}">{{ renderTableValue(rightPnlData.inventoryPurchases) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Payment Processing Fees</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.processingFees < 0}">{{ renderTableValue(rightPnlData.processingFees) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Total COGS</td>
                                        <td class="px-6 py-2.5 text-right text-rose-600">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.totalCogs < 0}">{{ renderTableValue(rightPnlData.totalCogs) }}</span>
                                        </td>
                                    </tr>

                                    <!-- GROSS PROFIT -->
                                    <tr class="font-bold text-gray-900 bg-indigo-50/40">
                                        <td class="px-6 py-3 pl-6">Gross Profit</td>
                                        <td class="px-6 py-3 text-right">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.grossProfit < 0}">{{ renderTableValue(rightPnlData.grossProfit) }}</span>
                                        </td>
                                    </tr>

                                    <!-- SECTION: OPERATING EXPENSES -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">OPERATING EXPENSES</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Software & Server</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.softwareServer < 0}">{{ renderTableValue(rightPnlData.softwareServer) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Operating Expenses</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.otherExpenses < 0}">{{ renderTableValue(rightPnlData.otherExpenses) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Total Operating Expenses</td>
                                        <td class="px-6 py-2.5 text-right text-rose-600">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.totalOpEx < 0}">{{ renderTableValue(rightPnlData.totalOpEx) }}</span>
                                        </td>
                                    </tr>

                                    <!-- OPERATING PROFIT -->
                                    <tr class="font-bold text-gray-900 bg-indigo-50/20">
                                        <td class="px-6 py-3 pl-6">Operating Profit</td>
                                        <td class="px-6 py-3 text-right">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.operatingProfit < 0}">{{ renderTableValue(rightPnlData.operatingProfit) }}</span>
                                        </td>
                                    </tr>

                                    <!-- OTHER INCOME / EXPENSES -->
                                    <tr class="bg-gray-50/70 font-semibold text-gray-900">
                                        <td class="px-6 py-2.5">OTHER INCOME / EXPENSES</td>
                                        <td class="px-6 py-2.5 text-right"></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Income</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.otherIncome < 0}">{{ renderTableValue(rightPnlData.otherIncome) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Other Expenses</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.otherExpensesLine < 0}">{{ renderTableValue(rightPnlData.otherExpensesLine) }}</span>
                                        </td>
                                    </tr>
                                    <tr class="font-bold text-gray-900 bg-gray-50/30">
                                        <td class="px-6 py-2.5 pl-8">Profit Before Tax</td>
                                        <td class="px-6 py-2.5 text-right font-semibold text-gray-900">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.profitBeforeTax < 0}">{{ renderTableValue(rightPnlData.profitBeforeTax) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-2 pl-12 text-gray-600">Tax Expense</td>
                                        <td class="px-6 py-2 text-right text-gray-900 font-semibold">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.taxExpense < 0}">{{ renderTableValue(rightPnlData.taxExpense) }}</span>
                                        </td>
                                    </tr>

                                    <!-- NET PROFIT -->
                                    <tr :class="[rightPnlData.netProfit >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white', 'font-bold']">
                                        <td class="px-6 py-3 text-base pl-6">
                                            {{ rightPnlData.netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}
                                        </td>
                                        <td class="px-6 py-3 text-right text-base">
                                            {{ rightPnlData.netProfit < 0 ? '-' : '' }}{{ formatCurrency(Math.abs(rightPnlData.netProfit)) }}
                                        </td>
                                    </tr>
                                    
                                    <!-- NET PROFIT MARGIN -->
                                    <tr class="font-bold text-indigo-700 bg-indigo-50/70">
                                        <td class="px-6 py-2.5 pl-6">Net Profit Margin %</td>
                                        <td class="px-6 py-2.5 text-right text-emerald-600">
                                            <span :class="{'text-rose-600 font-bold': rightPnlData.margin < 0}">{{ rightPnlData.margin.toFixed(1) }}%</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import BaseChart from '../../Components/Charts/BaseChart.vue';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    reportsData: {
        type: Object,
        required: true,
    },
    comparisonData: {
        type: Object,
        required: true,
    },
    dynamicYears: {
        type: Array,
        required: true,
    },
    topPaidCustomers: {
        type: Array,
        default: () => [],
    },
    expenseCategories: {
        type: Array,
        default: () => [],
    }
});

const activeTab = ref('standard');
const activeSubMetric = ref('sales'); // sales, revenue, customers
const comparisonMode = ref('yoy'); // yoy, mom

const currentYear = new Date().getFullYear();
const availableYears = [];
for (let y = currentYear - 10; y <= currentYear + 10; y++) {
    availableYears.push(y.toString());
}
availableYears.sort((a, b) => b - a);

const yearsToUse = computed(() => {
    return availableYears;
});

const selectedYearCustomers = ref(currentYear.toString());
const selectedYearTotalSales = ref(currentYear.toString());
const selectedYearReceivedPayments = ref(currentYear.toString());
const selectedYearPendingPayments = ref(currentYear.toString());
const selectedYearSubscriptions = ref(currentYear.toString());

const selectedComparisonYear = ref(currentYear.toString());

const filteredCustomers = computed(() => {
    return props.reportsData.customers.filter(row => row.month && row.month.endsWith(selectedYearCustomers.value));
});

const filteredTotalSales = computed(() => {
    return props.reportsData.totalSales.filter(row => row.month && row.month.endsWith(selectedYearTotalSales.value));
});

const filteredReceivedPayments = computed(() => {
    return props.reportsData.receivedPayments.filter(row => row.month && row.month.endsWith(selectedYearReceivedPayments.value));
});

const filteredPendingPayments = computed(() => {
    return props.reportsData.pendingPayments.filter(row => row.month && row.month.endsWith(selectedYearPendingPayments.value));
});

const filteredSubscriptions = computed(() => {
    return props.reportsData.subscriptions.filter(row => row.month && row.month.endsWith(selectedYearSubscriptions.value));
});


const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const shortMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const getShortMonth = (m) => shortMonthNames[m - 1];

const calculatedComparisons = computed(() => {
    const year = parseInt(selectedComparisonYear.value);
    if (isNaN(year)) return [];

    const data = [];
    for (let m = 1; m <= 12; m++) {
        const currentYearData = props.comparisonData[year]?.[m] || { sales: 0, revenue: 0, customers: 0 };
        
        let prevYearData = { sales: 0, revenue: 0, customers: 0 };
        if (comparisonMode.value === 'yoy') {
            prevYearData = props.comparisonData[year - 1]?.[m] || { sales: 0, revenue: 0, customers: 0 };
        } else {
            // MoM comparison
            if (m > 1) {
                prevYearData = props.comparisonData[year]?.[m - 1] || { sales: 0, revenue: 0, customers: 0 };
            } else {
                prevYearData = props.comparisonData[year - 1]?.[12] || { sales: 0, revenue: 0, customers: 0 };
            }
        }

        const calcMetric = (curr, prev) => {
            const diff = curr - prev;
            const pct = prev > 0 ? (diff / prev) * 100 : (curr > 0 ? 100 : 0);
            return {
                current: curr,
                previous: prev,
                difference: diff,
                percentage: pct
            };
        };

        data.push({
            monthIndex: m,
            monthName: monthNames[m - 1],
            sales: calcMetric(currentYearData.sales, prevYearData.sales),
            revenue: calcMetric(currentYearData.revenue, prevYearData.revenue),
            customers: calcMetric(currentYearData.customers, prevYearData.customers)
        });
    }
    return data;
});

const calculatedQuarterlyComparisons = computed(() => {
    const year = parseInt(selectedComparisonYear.value);
    if (isNaN(year)) return [];

    const quarters = [
        { name: 'Q1 (Jan–Mar)', months: [1, 2, 3] },
        { name: 'Q2 (Apr–Jun)', months: [4, 5, 6] },
        { name: 'Q3 (Jul–Sep)', months: [7, 8, 9] },
        { name: 'Q4 (Oct–Dec)', months: [10, 11, 12] }
    ];

    const getQuarterSum = (y, months) => {
        let sales = 0, revenue = 0, customers = 0;
        months.forEach(m => {
            const mData = props.comparisonData[y]?.[m] || { sales: 0, revenue: 0, customers: 0 };
            sales += mData.sales;
            revenue += mData.revenue;
            customers += mData.customers;
        });
        return { sales, revenue, customers };
    };

    const data = [];
    quarters.forEach((q, idx) => {
        const currentData = getQuarterSum(year, q.months);
        
        let prevData = { sales: 0, revenue: 0, customers: 0 };
        if (idx > 0) {
            // Previous quarter in same year
            prevData = getQuarterSum(year, quarters[idx - 1].months);
        } else {
            // Q4 of previous year
            prevData = getQuarterSum(year - 1, [10, 11, 12]);
        }

        const calcMetric = (curr, prev) => {
            const diff = curr - prev;
            const pct = prev > 0 ? (diff / prev) * 100 : (curr > 0 ? 100 : 0);
            return {
                current: curr,
                previous: prev,
                difference: diff,
                percentage: pct
            };
        };

        data.push({
            quarterName: q.name,
            sales: calcMetric(currentData.sales, prevData.sales),
            revenue: calcMetric(currentData.revenue, prevData.revenue),
            customers: calcMetric(currentData.customers, prevData.customers)
        });
    });

    return data;
});

const selectedReceivedOutstandingYear = ref(currentYear.toString());

const calculatedReceivedOutstanding = computed(() => {
    const year = parseInt(selectedReceivedOutstandingYear.value);
    if (isNaN(year)) return [];

    const data = [];
    for (let m = 1; m <= 12; m++) {
        const periodData = props.comparisonData[year]?.[m] || { sales: 0, revenue: 0, customers: 0, outstanding: 0 };
        const sales = periodData.sales;
        const received = periodData.revenue;
        const outstanding = periodData.outstanding;
        const rate = sales > 0 ? (received / sales) * 100 : 0;

        data.push({
            monthIndex: m,
            monthName: monthNames[m - 1],
            sales,
            received,
            outstanding,
            rate
        });
    }
    return data;
});

const receivedOutstandingTotals = computed(() => {
    let sales = 0, received = 0, outstanding = 0;
    calculatedReceivedOutstanding.value.forEach(row => {
        sales += row.sales;
        received += row.received;
        outstanding += row.outstanding;
    });
    const rate = sales > 0 ? (received / sales) * 100 : 0;
    return { sales, received, outstanding, rate };
});

const leftPnlYear = ref(currentYear.toString());
const leftPnlMonth = ref((new Date().getMonth() + 1).toString());

const rightPnlYear = ref((currentYear - 1).toString());
const rightPnlMonth = ref((new Date().getMonth() + 1).toString());

const getPnlDataFor = (yearVal, monthVal) => {
    const year = parseInt(yearVal);
    const month = monthVal === 'all' ? 'all' : parseInt(monthVal);
    
    if (isNaN(year)) {
        return {
            label: 'No Data',
            revenue: 0, totalOutflows: 0, saasRevenue: 0, processingRevenue: 0, otherRevenue: 0,
            totalRevenue: 0, inventoryPurchases: 0, processingFees: 0, totalCogs: 0, grossProfit: 0,
            softwareServer: 0, otherExpenses: 0, totalOpEx: 0, operatingProfit: 0, otherIncome: 0,
            otherExpensesLine: 0, profitBeforeTax: 0, taxExpense: 0, netProfit: 0, margin: 0
        };
    }
    
    if (month === 'all') {
        let revenue = 0, purchases = 0, expenses = 0, netProfit = 0;
        const categoryTotals = {};
        
        const yearMonthsData = props.comparisonData[year] || {};
        for (let m = 1; m <= 12; m++) {
            const periodData = yearMonthsData[m] || { revenue: 0, purchases: 0, expenses: 0, expense_categories: {} };
            revenue += periodData.revenue || 0;
            purchases += periodData.purchases || 0;
            expenses += periodData.expenses || 0;
            
            if (periodData.expense_categories) {
                Object.entries(periodData.expense_categories).forEach(([cat, amt]) => {
                    categoryTotals[cat] = (categoryTotals[cat] || 0) + amt;
                });
            }
        }
        netProfit = revenue - (purchases + expenses);
        const margin = revenue > 0 ? (netProfit / revenue) * 100 : 0;
        const breakdown = getExpensesBreakdown(categoryTotals);
        
        return {
            label: `Full Year ${year}`,
            revenue,
            totalOutflows: purchases + expenses,
            saasRevenue: revenue * 0.959596,
            processingRevenue: revenue * 0.040404,
            otherRevenue: 0,
            totalRevenue: revenue,
            inventoryPurchases: purchases,
            processingFees: 0,
            totalCogs: purchases,
            grossProfit: revenue - purchases,
            softwareServer: breakdown.softwareServer,
            otherExpenses: breakdown.otherExpenses,
            totalOpEx: expenses,
            operatingProfit: (revenue - purchases) - expenses,
            otherIncome: 0,
            otherExpensesLine: 0,
            profitBeforeTax: (revenue - purchases) - expenses,
            taxExpense: 0,
            netProfit,
            margin
        };
    } else {
        const periodData = props.comparisonData[year]?.[month] || { revenue: 0, purchases: 0, expenses: 0, expense_categories: {} };
        const revenue = periodData.revenue || 0;
        const purchases = periodData.purchases || 0;
        const expenses = periodData.expenses || 0;
        const netProfit = revenue - (purchases + expenses);
        const margin = revenue > 0 ? (netProfit / revenue) * 100 : 0;
        const breakdown = getExpensesBreakdown(periodData.expense_categories || {});
        
        return {
            label: `${monthNames[month - 1]} ${year}`,
            revenue,
            totalOutflows: purchases + expenses,
            saasRevenue: revenue * 0.959596,
            processingRevenue: revenue * 0.040404,
            otherRevenue: 0,
            totalRevenue: revenue,
            inventoryPurchases: purchases,
            processingFees: 0,
            totalCogs: purchases,
            grossProfit: revenue - purchases,
            softwareServer: breakdown.softwareServer,
            otherExpenses: breakdown.otherExpenses,
            totalOpEx: expenses,
            operatingProfit: (revenue - purchases) - expenses,
            otherIncome: 0,
            otherExpensesLine: 0,
            profitBeforeTax: (revenue - purchases) - expenses,
            taxExpense: 0,
            netProfit,
            margin
        };
    }
};

const leftPnlData = computed(() => getPnlDataFor(leftPnlYear.value, leftPnlMonth.value));
const rightPnlData = computed(() => getPnlDataFor(rightPnlYear.value, rightPnlMonth.value));

const calculatedPnl = computed(() => {
    const year = parseInt(leftPnlYear.value);
    if (isNaN(year)) return [];

    const data = [];
    for (let m = 1; m <= 12; m++) {
        const periodData = props.comparisonData[year]?.[m] || { revenue: 0, purchases: 0, expenses: 0, expense_categories: {} };
        const revenue = periodData.revenue || 0;
        const purchases = periodData.purchases || 0;
        const expenses = periodData.expenses || 0;
        const totalOutflows = purchases + expenses;
        const netProfit = revenue - totalOutflows;
        const margin = revenue > 0 ? (netProfit / revenue) * 100 : 0;

        data.push({
            monthIndex: m,
            monthName: monthNames[m - 1],
            revenue,
            purchases,
            expenses,
            totalOutflows,
            netProfit,
            margin,
            expense_categories: periodData.expense_categories || {}
        });
    }
    return data;
});

const pnlChartData = computed(() => {
    const labels = calculatedPnl.value.map(row => shortMonthNames[row.monthIndex - 1]);
    const revenues = calculatedPnl.value.map(row => row.revenue);
    const expenses = calculatedPnl.value.map(row => row.totalOutflows);
    const netProfits = calculatedPnl.value.map(row => row.netProfit);

    return {
        labels,
        datasets: [
            {
                type: 'bar',
                label: 'Revenue',
                data: revenues,
                backgroundColor: 'rgba(16, 185, 129, 0.75)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1,
                borderRadius: 4,
                order: 2
            },
            {
                type: 'bar',
                label: 'Expenses & Purchases',
                data: expenses,
                backgroundColor: 'rgba(239, 68, 68, 0.75)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1,
                borderRadius: 4,
                order: 3
            },
            {
                type: 'line',
                label: 'Net Profit',
                data: netProfits,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                tension: 0.35,
                fill: false,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointHoverRadius: 6,
                order: 1
            }
        ]
    };
});

const pnlChartOptions = computed(() => {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    boxWidth: 12,
                    font: {
                        size: 11,
                        weight: 'bold'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += formatCurrency(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(243, 244, 246, 1)',
                },
                ticks: {
                    callback: function(value) {
                        return formatCurrency(value);
                    },
                    font: {
                        size: 11,
                    }
                }
            },
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    font: {
                        size: 11,
                    }
                }
            }
        }
    };
});

const goToDetail = (type, month) => {
    router.visit(route('reports.detail', { type, month }));
};

const formatCurrency = (val) => {
    return '$' + parseFloat(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getExpensesBreakdown = (categories) => {
    let softwareServer = 0;
    let otherExpenses = 0;
    Object.entries(categories).forEach(([cat, amt]) => {
        if (/software|server|hosting|cloud|it/i.test(cat)) {
            softwareServer += amt;
        } else {
            otherExpenses += amt;
        }
    });
    return { softwareServer, otherExpenses };
};

const renderTableValue = (val) => {
    if (val < 0) {
        return `-$${Math.abs(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
    return `$${val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const activeStandardTab = ref('customers');
const activePaymentsSubTab = ref('received');

const standardChartData = computed(() => {
    let rawData = [];
    let label = '';
    let borderColor = 'rgba(99, 102, 241, 1)';
    let backgroundColor = 'rgba(99, 102, 241, 0.1)';
    let isCount = false;
    
    if (activeStandardTab.value === 'customers') {
        rawData = filteredCustomers.value;
        label = 'New Customers Joined';
        borderColor = 'rgb(99, 102, 241)';
        backgroundColor = 'rgba(99, 102, 241, 0.1)';
        isCount = true;
    } else if (activeStandardTab.value === 'sales') {
        rawData = filteredTotalSales.value;
        label = 'Total Sales Invoiced';
        borderColor = 'rgb(16, 185, 129)';
        backgroundColor = 'rgba(16, 185, 129, 0.1)';
    } else if (activeStandardTab.value === 'payments') {
        if (activePaymentsSubTab.value === 'received') {
            rawData = filteredReceivedPayments.value;
            label = 'Received Payments (Revenue)';
            borderColor = 'rgb(59, 130, 246)';
            backgroundColor = 'rgba(59, 130, 246, 0.1)';
        } else {
            rawData = filteredPendingPayments.value;
            label = 'Pending Payments (Outstanding)';
            borderColor = 'rgb(245, 158, 11)';
            backgroundColor = 'rgba(245, 158, 11, 0.1)';
        }
    } else if (activeStandardTab.value === 'subscriptions') {
        rawData = filteredSubscriptions.value;
        label = 'New Subscriptions';
        borderColor = 'rgb(168, 85, 247)';
        backgroundColor = 'rgba(168, 85, 247, 0.1)';

    } else if (activeStandardTab.value === 'top_paid') {
        return {
            labels: props.topPaidCustomers.map(c => c.name),
            datasets: [
                {
                    label: 'Total USD Spent ($)',
                    data: props.topPaidCustomers.map(c => c.raw_total_spent),
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    borderWidth: 2,
                    borderRadius: 6,
                }
            ]
        };
    }

    const monthOrder = {
        'January': 1, 'February': 2, 'March': 3, 'April': 4, 'May': 5, 'June': 6,
        'July': 7, 'August': 8, 'September': 9, 'October': 10, 'November': 11, 'December': 12
    };

    const sortedData = [...rawData].sort((a, b) => {
        const mNameA = a.month.split(' ')[0];
        const mNameB = b.month.split(' ')[0];
        return (monthOrder[mNameA] || 0) - (monthOrder[mNameB] || 0);
    });

    const labels = sortedData.map(row => row.month);
    const dataPoints = sortedData.map(row => isCount ? (row.count || 0) : (row.total || 0));

    return {
        labels,
        datasets: [
            {
                label: label,
                data: dataPoints,
                borderColor: borderColor,
                backgroundColor: backgroundColor,
                tension: 0.3,
                fill: true,
                borderWidth: 2,
                pointBackgroundColor: borderColor,
                pointHoverRadius: 6,
            }
        ]
    };
});

const standardChartOptions = computed(() => {
    const isCount = activeStandardTab.value === 'customers' || activeStandardTab.value === 'subscriptions';
    return {
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += isCount ? context.parsed.y + ' units' : formatCurrency(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(243, 244, 246, 1)',
                },
                ticks: {
                    callback: function(value) {
                        return isCount ? value : formatCurrency(value);
                    },
                    font: {
                        size: 12,
                    }
                }
            },
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    font: {
                        size: 12,
                    }
                }
            }
        }
    };
});
</script>

