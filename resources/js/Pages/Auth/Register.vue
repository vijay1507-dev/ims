<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const isPasswordFocused = ref(false);

const form = useForm({
    name: '',
    company_name: '',
    subdomain: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const passwordCriteria = computed(() => {
    const pwd = form.password || '';
    return {
        length: pwd.length >= 8,
        uppercase: /[A-Z]/.test(pwd),
        lowercase: /[a-z]/.test(pwd),
        numbers: /[0-9]/.test(pwd),
        symbols: /[^A-Za-z0-9]/.test(pwd),
    };
});

const isAllPasswordCriteriaMet = computed(() => {
    return !!(
        passwordCriteria.value.length &&
        passwordCriteria.value.uppercase &&
        passwordCriteria.value.lowercase &&
        passwordCriteria.value.numbers &&
        passwordCriteria.value.symbols
    );
});

const isFormValid = computed(() => {
    return !!(
        form.name && form.name.trim() !== '' &&
        form.subdomain && form.subdomain.trim() !== '' &&
        form.email && form.email.trim() !== '' &&
        form.password &&
        form.password_confirmation &&
        form.password === form.password_confirmation &&
        isAllPasswordCriteriaMet.value
    );
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
    form.clearErrors('subdomain');
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

            <form @submit.prevent="submit" novalidate>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        type="text"
                        class="block w-full px-4 py-3 bg-gray-50 border rounded-lg outline-none transition-colors duration-200"
                        :class="form.errors.name ? 'border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'"
                        v-model="form.name"
                        autofocus
                        autocomplete="name"
                        placeholder="John Doe"
                        @input="form.clearErrors('name')"
                    />
                    <div v-if="form.errors.name" class="mt-1.5 text-xs text-red-600">{{ form.errors.name }}</div>
                </div>

                <div class="mt-5">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company / Organization Name</label>
                    <input
                        id="company_name"
                        type="text"
                        class="block w-full px-4 py-3 bg-gray-50 border rounded-lg outline-none transition-colors duration-200"
                        :class="form.errors.company_name ? 'border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'"
                        v-model="form.company_name"
                        placeholder="Acme Corporation"
                        @input="form.clearErrors('company_name')"
                    />
                    <div v-if="form.errors.company_name" class="mt-1.5 text-xs text-red-600">{{ form.errors.company_name }}</div>
                </div>

                <div class="mt-5">
                    <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1">
                        Domain <span class="text-red-500">*</span>
                    </label>
                    <div class="flex rounded-lg shadow-xs">
                        <input
                            id="subdomain"
                            type="text"
                            class="block w-full px-4 py-3 bg-gray-50 border rounded-l-lg outline-none transition-colors duration-200"
                            :class="form.errors.subdomain ? 'border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'"
                            v-model="form.subdomain"
                            @input="handleSubdomainInput"
                            placeholder="acme"
                        />
                        <span
                            class="inline-flex items-center px-4 rounded-r-lg border border-l-0 text-sm font-medium"
                            :class="form.errors.subdomain ? 'border-red-500 bg-red-50 text-red-500' : 'border-gray-200 bg-gray-100 text-gray-500'"
                        >
                            .{{ baseHost }}
                        </span>
                    </div>
                    <p v-if="formattedSubdomain && !form.errors.subdomain" class="mt-1.5 text-xs text-indigo-600 font-medium">
                        Your workspace URL: <span class="font-bold underline">{{ formattedSubdomain }}</span>
                    </p>
                    <div v-if="form.errors.subdomain" class="mt-1.5 text-xs text-red-600">{{ form.errors.subdomain }}</div>
                </div>

                <div class="mt-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full px-4 py-3 bg-gray-50 border rounded-lg outline-none transition-colors duration-200"
                        :class="form.errors.email ? 'border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'"
                        v-model="form.email"
                        autocomplete="username"
                        placeholder="john@example.com"
                        @input="form.clearErrors('email')"
                    />
                    <div v-if="form.errors.email" class="mt-1.5 text-xs text-red-600">{{ form.errors.email }}</div>
                </div>

                <div class="mt-5">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full px-4 py-3 pr-11 bg-gray-50 border rounded-lg outline-none transition-colors duration-200"
                            :class="form.errors.password ? 'border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'"
                            v-model="form.password"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            @focus="isPasswordFocused = true"
                            @blur="isPasswordFocused = false"
                            @input="form.clearErrors('password')"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            @mousedown.prevent
                            class="absolute right-3.5 top-3.5 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                            tabindex="-1"
                        >
                            <i :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="mt-1.5 text-xs text-red-600">{{ form.errors.password }}</div>

                    <!-- Password Validation Criteria Checklist (Only open when password field is clicked / focused) -->
                    <div v-show="isPasswordFocused" class="mt-2.5 p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl text-xs space-y-2 transition-all duration-200">
                        <div class="flex items-center space-x-2 transition-colors duration-150" :class="passwordCriteria.length ? 'text-emerald-600 font-medium' : 'text-slate-500'">
                            <span class="inline-block w-1.5 h-1.5 rounded-full" :class="passwordCriteria.length ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                            <span>Minimum 8 characters</span>
                        </div>
                        <div class="flex items-center space-x-2 transition-colors duration-150" :class="passwordCriteria.uppercase ? 'text-emerald-600 font-medium' : 'text-slate-500'">
                            <span class="inline-block w-1.5 h-1.5 rounded-full" :class="passwordCriteria.uppercase ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                            <span>At least one uppercase letter (A-Z)</span>
                        </div>
                        <div class="flex items-center space-x-2 transition-colors duration-150" :class="passwordCriteria.lowercase ? 'text-emerald-600 font-medium' : 'text-slate-500'">
                            <span class="inline-block w-1.5 h-1.5 rounded-full" :class="passwordCriteria.lowercase ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                            <span>At least one lowercase letter (a-z)</span>
                        </div>
                        <div class="flex items-center space-x-2 transition-colors duration-150" :class="passwordCriteria.numbers ? 'text-emerald-600 font-medium' : 'text-slate-500'">
                            <span class="inline-block w-1.5 h-1.5 rounded-full" :class="passwordCriteria.numbers ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                            <span>At least one number (0-9)</span>
                        </div>
                        <div class="flex items-center space-x-2 transition-colors duration-150" :class="passwordCriteria.symbols ? 'text-emerald-600 font-medium' : 'text-slate-500'">
                            <span class="inline-block w-1.5 h-1.5 rounded-full" :class="passwordCriteria.symbols ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                            <span>At least one symbol (e.g. @, #, $, !)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="block w-full px-4 py-3 pr-11 bg-gray-50 border rounded-lg outline-none transition-colors duration-200"
                            :class="form.errors.password_confirmation ? 'border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'"
                            v-model="form.password_confirmation"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            @input="form.clearErrors('password_confirmation')"
                        />
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            @mousedown.prevent
                            class="absolute right-3.5 top-3.5 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                            tabindex="-1"
                        >
                            <i :class="['fas', showConfirmPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
                        </button>
                    </div>
                    <div v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-600">{{ form.errors.password_confirmation }}</div>
                    <div v-else-if="form.password_confirmation && form.password !== form.password_confirmation" class="mt-1.5 text-xs text-red-600">
                        Password does not match.
                    </div>
                </div>

                <div class="mt-8">
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 rounded-lg shadow-sm text-sm font-bold text-white transition-all duration-200 transform active:scale-[0.98]"
                        :class="(!isFormValid || form.processing) ? 'bg-indigo-400 opacity-60 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer'"
                        :disabled="!isFormValid || form.processing"
                    >
                        <span v-if="form.processing">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Registering Tenant...
                        </span>
                        <span v-else>
                            Register Account
                        </span>
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
