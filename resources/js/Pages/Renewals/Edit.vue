<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Breadcrumb Navigation Header -->
            <div class="mb-6">
                <Link :href="`/renewals?page=${returnPage}`" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to Renewals Dashboard
                </Link>
                <h2 class="text-2xl font-bold text-gray-900">Modify Renewal Contract</h2>
                <p class="text-gray-600 mt-1">Reassign valuation strings or shift expiration urgency categories for {{ renewal.customer_name }}.</p>
            </div>

            <!-- Main Form Card Container -->
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <form @submit.prevent="submitForm">
                    <div class="p-6 sm:p-8 space-y-6">


                        <!-- Renewals Automation Array -->
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-100 max-w-md">
                            <!-- Renewal reminders parameter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Renewal Reminders Sequence</label>
                                <select
                                    v-model="form.renewal_reminders"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer font-medium"
                                >
                                    <option value="7_days">7 days prior to expiry</option>
                                    <option value="3_days">3 days prior to expiry</option>
                                    <option value="1_day">24 hours prior to expiry</option>
                                    <option value="disabled">Mute Reminders</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Card Submission Action Bar -->
                    <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <Link
                            :href="`/renewals?page=${returnPage}`"
                            class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-medium transition-colors inline-flex items-center"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm disabled:opacity-50 transition-colors inline-flex items-center"
                        >
                            <span v-if="form.processing" class="inline-block animate-spin mr-2 border-2 border-white border-t-transparent h-4 w-4 rounded-full"></span>
                            Save Modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    renewal: {
        type: Object,
        required: true,
    },
    returnPage: {
        type: Number,
        default: 1,
    },
});

const form = useForm({
    customer_name: props.renewal.customer_name,
    type: props.renewal.type,
    plan_asset: props.renewal.plan_asset,
    current_value: props.renewal.current_value || '',
    renewal_date: props.renewal.renewal_date || '',
    days_left: props.renewal.days_left || '',
    priority: props.renewal.priority,
    renewal_reminders: props.renewal.renewal_reminders || '7_days',
});

const submitForm = () => {
    form.put(`/renewals/${props.renewal.id}?page=${props.returnPage}`);
};
</script>
