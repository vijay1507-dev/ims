<template>
    <div class="chart-container relative w-full" :style="{ height: height + 'px' }">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
    height: {
        type: Number,
        default: 300,
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const initChart = () => {
    if (!canvasRef.value) return;
    
    // Ensure Chart is available from loaded assets/CDN
    if (typeof window.Chart !== 'undefined') {
        if (chartInstance) {
            chartInstance.destroy();
        }
        
        chartInstance = new window.Chart(canvasRef.value.getContext('2d'), {
            type: props.type,
            data: props.data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                ...props.options,
            },
        });
    }
};

onMounted(() => {
    // If loaded asynchronously via CDN, retry briefly
    const checkChart = setInterval(() => {
        if (typeof window.Chart !== 'undefined') {
            clearInterval(checkChart);
            initChart();
        }
    }, 100);

    // Clear interval safety
    setTimeout(() => clearInterval(checkChart), 5000);
});

watch(
    () => props.data,
    (newData) => {
        if (chartInstance) {
            chartInstance.data = newData;
            chartInstance.update();
        }
    },
    { deep: true }
);

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
}
</style>
