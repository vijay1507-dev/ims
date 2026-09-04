<template>
    <div v-if="meta && meta.total > 0" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ meta.from || 0 }}</span> to <span class="font-medium">{{ meta.to || 0 }}</span> of <span class="font-medium">{{ meta.total }}</span> results
        </div>
        <div class="flex flex-wrap items-center gap-1">
            <template v-for="(link, index) in meta.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    :class="[
                        'px-3 py-1 text-sm rounded-md transition-colors',
                        link.active
                            ? 'bg-indigo-600 text-white font-medium'
                            : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
                    ]"
                    v-html="cleanLabel(link.label)"
                />
                <span
                    v-else
                    :class="[
                        'px-3 py-1 text-sm rounded-md border border-gray-200 text-gray-400 cursor-not-allowed',
                    ]"
                    v-html="cleanLabel(link.label)"
                />
            </template>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    meta: {
        type: Object,
        required: true,
        // Expect standard Laravel paginator structure: { from, to, total, links: [{ url, label, active }] }
    },
});

const cleanLabel = (label) => {
    if (!label) return '';
    return label.replace('&laquo;', '').replace('&raquo;', '').trim();
};
</script>
