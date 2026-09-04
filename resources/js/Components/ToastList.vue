<template>
    <div class="fixed top-6 right-6 z-[100] flex flex-col gap-3 w-full max-w-sm pointer-events-none">
        <TransitionGroup 
            name="toast-list"
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-for="toast in toasts" 
                :key="toast.id"
                class="pointer-events-auto w-full bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden"
                :class="[toast.type === 'success' ? 'ring-1 ring-emerald-500/10' : 'ring-1 ring-rose-500/10']"
            >
                <div class="p-4 flex items-start gap-4">
                    <div 
                        class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                        :class="[toast.type === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600']"
                    >
                        <i v-if="toast.type === 'success'" class="fas fa-check-circle text-lg"></i>
                        <i v-else class="fas fa-exclamation-circle text-lg"></i>
                    </div>
                    
                    <div class="flex-1 pt-0.5">
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            {{ toast.message }}
                        </p>
                    </div>

                    <button 
                        @click="remove(toast.id)"
                        class="shrink-0 text-gray-400 hover:text-gray-600 transition-colors p-1"
                    >
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div class="h-1 bg-gray-100 w-full overflow-hidden">
                    <div 
                        class="h-full transition-all duration-[5000ms] ease-linear"
                        :class="[toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500']"
                        :style="{ width: toast.progress + '%' }"
                    ></div>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const toasts = ref([]);
const page = usePage();

const add = (message, type = 'success') => {
    const id = Date.now();
    const toast = { id, message, type, progress: 100 };
    toasts.value.push(toast);

    // Fade out progress bar
    setTimeout(() => {
        toast.progress = 0;
    }, 10);

    // Remove after 5 seconds
    setTimeout(() => {
        remove(id);
    }, 5000);
};

const remove = (id) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index !== -1) {
        toasts.value.splice(index, 1);
    }
};

// Listen for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash.success) {
        add(flash.success, 'success');
    }
    if (flash.error) {
        add(flash.error, 'error');
    }
}, { deep: true });

// Initial check on mount
onMounted(() => {
    if (page.props.flash.success) {
        add(page.props.flash.success, 'success');
    }
    if (page.props.flash.error) {
        add(page.props.flash.error, 'error');
    }
});
</script>

<style scoped>
.toast-list-move {
    transition: all 0.3s ease;
}
</style>
