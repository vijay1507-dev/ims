<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden',
    }[props.status];
});

const description = computed(() => {
    return {
        503: 'Sorry, we are doing some maintenance. Please check back soon.',
        500: 'Whoops, something went wrong on our servers.',
        404: 'Sorry, the page you are looking for could not be found.',
        403: 'Sorry, you are forbidden from accessing this page.',
    }[props.status];
});
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <Head :title="title" />

        <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-10 text-center">
            <div class="mb-6">
                <i :class="[
                    'fas fa-exclamation-triangle text-6xl',
                    status === 403 ? 'text-amber-500' : 'text-red-500'
                ]"></i>
            </div>
            
            <h1 class="text-4xl font-extrabold text-gray-900 mb-2">{{ status }}</h1>
            <h2 class="text-xl font-bold text-gray-700 mb-4">{{ title }}</h2>
            <p class="text-gray-500 mb-8">{{ description }}</p>

            <div class="flex flex-col gap-4">
                <Link
                    href="/"
                    class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-200"
                >
                    Return to Dashboard
                </Link>
                
                <button
                    @click="window.history.back()"
                    class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors"
                >
                    Go Back
                </button>
            </div>
        </div>
    </div>
</template>
