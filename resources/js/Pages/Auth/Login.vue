<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
        <Head title="Log in" />

        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-xl overflow-hidden sm:rounded-2xl border border-gray-100">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Welcome Back</h1>
                <p class="mt-2 text-sm text-gray-500">Please enter your credentials to access your account</p>
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

                <div class="mt-5">
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <Link
                            href="/forgot-password"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 transition-colors"
                        >
                            Forgot password?
                        </Link>
                    </div>
                    <input
                        id="password"
                        type="password"
                        class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</div>
                </div>

                <div class="mt-8">
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform active:scale-[0.98]"
                        :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Signing in...</span>
                        <span v-else>Sign In</span>
                    </button>
                </div>
            </form>

           <!-- <div class="mt-8 text-center border-t border-gray-100 pt-6">
                <p class="text-sm text-gray-500">
                    Don't have an account? 
                    <Link href="/register" class="font-semibold text-indigo-600 hover:text-indigo-500">
                        Create one
                    </Link>
                </p>
            </div> -->
        </div>

        <div class="mt-8 text-gray-400 text-xs">
            &copy; 2026 SaaS Manager CRM. All rights reserved.
        </div>
    </div>
</template>
