<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans antialiased">
        <!-- Top Navigation -->
        <Navbar
            :model-value="modelValue"
            :show-search="showSearch"
            :search-placeholder="searchPlaceholder"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Side Navigation Drawer -->
        <Sidebar />

        <!-- Toast Notifications -->
        <ToastList />

        <!-- Dynamic Main Content Slot -->
        <main class="md:ml-64 pt-16">
            <slot />
        </main>
    </div>
</template>

<script setup>
import Navbar from '../Navigation/Navbar.vue';
import Sidebar from '../Navigation/Sidebar.vue';
import ToastList from '../ToastList.vue';

defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    showSearch: {
        type: Boolean,
        default: true,
    },
    searchPlaceholder: {
        type: String,
        default: 'Search customers, subscriptions...',
    },
});

defineEmits(['update:modelValue']);
</script>
