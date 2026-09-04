<template>
    <AppLayout>
        <div class="p-6 max-w-3xl mx-auto">
            <!-- Breadcrumbs / Back button -->
            <div class="mb-6">
                <Link
                    href="/expenses"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors inline-flex items-center"
                >
                    <i class="fas fa-arrow-left mr-2"></i>Back to Expenses
                </Link>
            </div>

            <!-- Page Title -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Log New Expense</h2>
                <p class="text-sm text-gray-500 mt-1">Classify and enter the details of the business expense</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-2xs p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Title -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Expense Title *</label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="e.g., AWS Hosting Fees, Office Rent"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-rose-500': form.errors.title }"
                            />
                            <p v-if="form.errors.title" class="text-xs text-rose-600 mt-1">{{ form.errors.title }}</p>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Expense Type *</label>
                            <select
                                v-model="form.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                                :class="{ 'border-rose-500': form.errors.type }"
                            >
                                <option value="" disabled>Select Type</option>
                                <option value="direct">Direct Expense</option>
                                <option value="indirect">Indirect Expense</option>
                            </select>
                            <p v-if="form.errors.type" class="text-xs text-rose-600 mt-1">{{ form.errors.type }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Category *</label>
                            <input
                                v-model="form.category"
                                type="text"
                                placeholder="e.g., Software Subscriptions, Utilities"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-rose-500': form.errors.category }"
                            />
                            <p v-if="form.errors.category" class="text-xs text-rose-600 mt-1">{{ form.errors.category }}</p>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Amount ($) *</label>
                            <input
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-rose-500': form.errors.amount }"
                            />
                            <p v-if="form.errors.amount" class="text-xs text-rose-600 mt-1">{{ form.errors.amount }}</p>
                        </div>

                        <!-- Expense Date -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Expense Date *</label>
                            <input
                                v-model="form.expense_date"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-rose-500': form.errors.expense_date }"
                            />
                            <p v-if="form.errors.expense_date" class="text-xs text-rose-600 mt-1">{{ form.errors.expense_date }}</p>
                        </div>

                        <!-- Reference Number -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Reference / Invoice Number</label>
                            <input
                                v-model="form.reference_number"
                                type="text"
                                placeholder="e.g., INV-998822"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                placeholder="Additional details or notes about this expense..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <Link
                            href="/expenses"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold transition-colors shadow-xs disabled:opacity-50 cursor-pointer"
                        >
                            Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

// Initialize today's date formatted for the date picker input (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0];

const form = useForm({
    title: '',
    type: '',
    category: '',
    amount: '',
    expense_date: today,
    reference_number: '',
    description: '',
});

const submit = () => {
    form.post('/expenses');
};
</script>
