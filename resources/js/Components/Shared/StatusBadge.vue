<template>
    <span :class="['status-badge inline-flex items-center', badgeClass]">
        <slot>{{ formattedStatus }}</slot>
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const formattedStatus = computed(() => {
    if (!props.status) return '';
    const s = props.status.toLowerCase();
    if (s === 'active') return 'Paid';
    return s.charAt(0).toUpperCase() + s.slice(1);
});

const badgeClass = computed(() => {
    const s = props.status.toLowerCase();
    switch (s) {
        case 'active':
        case 'paid':
        case 'success':
        case 'successful':
            return 'bg-[#dcfce7] text-[#166534]';
        case 'pending':
        case 'trial':
            return 'bg-[#fef3c7] text-[#92400e]';
        case 'expired':
        case 'inactive':
        case 'cancelled':
        case 'failed':
            return 'bg-[#fee2e2] text-[#991b1b]';
        case 'overdue':
            return 'bg-[#fecaca] text-[#b91c1c]';
        case 'paused':
        case 'refunded':
            return 'bg-[#e0e7ff] text-[#3730a3]';
        case 'draft':
            return 'bg-[#e5e7eb] text-[#374151]';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});
</script>

<style scoped>
.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-weight: 500;
}
</style>
