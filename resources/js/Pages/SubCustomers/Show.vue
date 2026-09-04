<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto p-6">
            <div class="mb-6">
                <Link :href="`/customers/${customer.id}`" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to {{ customer.name }}
                </Link>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ subCustomer.name }}</h2>
                        <p class="text-gray-600 mt-1">Location under <span class="font-semibold">{{ customer.name }}</span></p>
                    </div>
                    <Link :href="`/sub-customers/${subCustomer.id}/edit`" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-medium">
                        <i class="fas fa-pen mr-1"></i> Edit
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Payments</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ metrics.payments_count }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">${{ Number(metrics.revenue).toLocaleString() }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Outstanding</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">${{ Number(metrics.outstanding).toLocaleString() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Payment History</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Transaction</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in payments" :key="p.id">
                            <td class="px-5 py-3 text-sm text-gray-900">{{ p.transaction_id }}</td>
                            <td class="px-5 py-3 text-sm text-gray-900">${{ Number(p.amount).toLocaleString() }}</td>
                            <td class="px-5 py-3 text-sm capitalize text-gray-600">{{ p.status }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ p.payment_date }}</td>
                        </tr>
                        <tr v-if="payments.length === 0">
                            <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-400">No payments recorded for this location yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

defineProps({
    customer: { type: Object, required: true },
    subCustomer: { type: Object, required: true },
    payments: { type: Array, default: () => [] },
    metrics: { type: Object, default: () => ({ payments_count: 0, revenue: 0, outstanding: 0 }) },
});
</script>
