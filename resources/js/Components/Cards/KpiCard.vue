<template>
    <div class="kpi-card bg-white rounded-xl p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">{{ title }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ value }}</p>
                <p v-if="trendText" :class="['text-sm mt-2', trendColorClass]">
                    <i v-if="trendIcon" :class="trendIcon"></i> {{ trendText }}
                </p>
            </div>
            <div :class="['p-3 rounded-lg', iconBgClass]">
                <i :class="[icon, 'text-xl', iconColorClass]"></i>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        required: true,
    },
    iconBgClass: {
        type: String,
        default: 'bg-indigo-100',
    },
    iconColorClass: {
        type: String,
        default: 'text-indigo-600',
    },
    trendText: {
        type: String,
        default: '',
    },
    trendIcon: {
        type: String,
        default: '',
    },
    trendColorClass: {
        type: String,
        default: 'text-green-600',
    },
});
</script>

<style scoped>
.kpi-card {
    transition: all 0.3s ease;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
</style>
