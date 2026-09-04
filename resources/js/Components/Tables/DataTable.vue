<template>
    <div class="bg-white rounded-xl border border-gray-200">
        <!-- Optional Table Header / Controls Area -->
        <div v-if="title || $slots.filters" class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h3 v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                <div class="mt-3 sm:mt-0 flex flex-wrap items-center gap-2">
                    <slot name="filters" />
                </div>
            </div>
        </div>
        
        <!-- Responsive Scrollable Data Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key || column.label"
                            :class="[
                                'px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider',
                                column.align === 'right' ? 'text-right' : column.align === 'center' ? 'text-center' : 'text-left',
                                column.headerClass || ''
                            ]"
                        >
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <slot name="rows">
                        <tr v-if="!hasRows" class="hover:bg-gray-50">
                            <td :colspan="columns.length" class="px-6 py-8 text-center text-sm text-gray-500">
                                {{ emptyMessage }}
                            </td>
                        </tr>
                    </slot>
                </tbody>
            </table>
        </div>

        <!-- Optional Pagination Footer -->
        <div v-if="$slots.pagination" class="px-6 py-4 border-t border-gray-200">
            <slot name="pagination" />
        </div>
        <div v-else-if="totalItems !== null" class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs font-semibold text-gray-500 bg-gray-50/50">
            <div class="flex items-center gap-2">
                <span>Show</span>
                <select
                    :value="itemsPerPage"
                    @change="$emit('update:itemsPerPage', parseInt($event.target.value))"
                    class="rounded-md border-gray-300 py-1 px-2 text-xs font-semibold text-gray-700 bg-white focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer shadow-2xs"
                >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                </select>
                <span>entries</span>
            </div>
            <div>
                <span>Showing {{ startOffset }} to {{ endOffset }} of {{ totalItems }} entries</span>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    type="button"
                    :disabled="currentPage <= 1"
                    @click="$emit('update:currentPage', currentPage - 1)"
                    class="px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors cursor-pointer shadow-2xs font-bold"
                >
                    Previous
                </button>
                <button
                    type="button"
                    :disabled="currentPage >= totalPages"
                    @click="$emit('update:currentPage', currentPage + 1)"
                    class="px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors cursor-pointer shadow-2xs font-bold"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    columns: {
        type: Array,
        required: true,
    },
    hasRows: {
        type: Boolean,
        default: true,
    },
    emptyMessage: {
        type: String,
        default: 'No records available.',
    },
    totalItems: {
        type: Number,
        default: null,
    },
    itemsPerPage: {
        type: Number,
        default: 10,
    },
    currentPage: {
        type: Number,
        default: 1,
    },
});

defineEmits(['update:itemsPerPage', 'update:currentPage']);

const totalPages = computed(() => Math.ceil((props.totalItems || 0) / props.itemsPerPage));
const startOffset = computed(() => props.totalItems === 0 ? 0 : (props.currentPage - 1) * props.itemsPerPage + 1);
const endOffset = computed(() => Math.min(props.currentPage * props.itemsPerPage, props.totalItems || 0));
</script>

<style scoped>
/* Inherit global CSS table hover rules cleanly */
:deep(.table-row:hover) {
    background-color: #f9fafb;
}
</style>
