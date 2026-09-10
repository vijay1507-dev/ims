<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    name: '',
    company_name: '',
    subdomain: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const baseHost = computed(() => {
    if (typeof window !== 'undefined') {
        const host = window.location.hostname;
        return host.startsWith('www.') ? host.slice(4) : host;
    }
    return 'localhost';
});

const formattedSubdomain = computed(() => {
    if (!form.subdomain) return '';
    const cleanSubdomain = form.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '');
    return cleanSubdomain ? `${cleanSubdomain}.${baseHost.value}` : '';
});

const handleSubdomainInput = (e) => {
    form.subdomain = e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
};

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 bg-gray-50">
        <Head title="Register Tenant" />

        <div class="w-full sm:max-w-md px-8 py-10 bg-white shadow-xl overflow-hidden sm:rounded-2xl border border-gray-100">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Create Tenant</h1>
                <p class="mt-2 text-sm text-gray-500">Setup your organization and subdomain</p>
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input
                        id="name"
                        type="text"
                        class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="John Doe"
                    />
                    <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>

                <div class="mt-5">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company / Organization Name</label>
                    <input
                        id="company_name"
                        type="text"
                        class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                        v-model="form.company_name"
                        placeholder="Acme Corporation"
                    />
                    <div v-if="form.errors.company_name" class="mt-2 text-sm text-red-600">{{ form.errors.company_name }}</div>
                </div>

                <div class="mt-5">
                    <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1">Sub-domain Name</label>
                    <div class="flex rounded-lg shadow-xs">
                        <input
                            id="subdomain"
                            type="text"
                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-l-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                            v-model="form.subdomain"
                            @input="handleSubdomainInput"
                            required
                            placeholder="acme"
                        />
                        <span class="inline-flex items-center px-4 rounded-r-lg border border-l-0 border-gray-200 bg-gray-100 text-gray-500 text-sm font-medium">
                            .{{ baseHost }}
                        </span>
                    </div>
                    <p v-if="formattedSubdomain" class="mt-1.5 text-xs text-indigo-600 font-medium">
                        Your workspace URL: <span class="font-bold underline">{{ formattedSubdomain }}</span>
                    </p>
                    <div v-if="form.errors.subdomain" class="mt-2 text-sm text-red-600">{{ form.errors.subdomain }}</div>
                </div>

                <div class="mt-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="john@example.com"
                    />
                    <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</div>
                </div>

                <div class="mt-5">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        id="password"
                        type="password"
                        class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</div>
                </div>

                <div class="mt-5">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <div v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
                </div>

                <div class="mt-8">
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform active:scale-[0.98]"
                        :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        Register Tenant & Account
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <Link href="/login" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                        Already have an account? Sign in
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
