<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
        <Head title="Forgot Password" />

        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-xl overflow-hidden sm:rounded-2xl border border-gray-100">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Forgot Password?</h1>
                <p class="mt-2 text-sm text-gray-500">No problem. Just let us know your email address and we will email you a password reset link.</p>
            </div>

            <div v-if="status" class="mb-6 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg border border-green-100">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@example.com"
                    />
                    <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</div>
                </div>

                <div class="mt-8 flex flex-col gap-4">
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform active:scale-[0.98]"
                        :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        Email Password Reset Link
                    </button>

                    <Link
                        href="/login"
                        class="text-center text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        Back to Login
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
