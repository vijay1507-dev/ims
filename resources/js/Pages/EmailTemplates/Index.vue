<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Email Templates</h2>
                </div>
                <Link 
                    href="/email-templates/create"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2"
                >
                    <i class="fas fa-plus"></i>
                    Add New Template
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 min-h-[500px]">
                <div class="space-y-8 animate-fade-in text-gray-800">
                    

                    <div class="grid grid-cols-1 gap-6 text-gray-800">
                        <div v-for="template in templates" :key="template.id" class="border border-gray-200 rounded-xl p-6 transition-all hover:bg-gray-50/50 bg-white shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="font-bold text-lg text-gray-900">{{ template.name }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">{{ template.description }}</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <Link 
                                        :href="`/email-templates/${template.id}/edit`"
                                        class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-colors cursor-pointer inline-flex items-center"
                                    >
                                        <i class="fas fa-edit mr-2"></i>Modify
                                    </Link>
                                    <button 
                                        @click="deleteTemplate(template)"
                                        type="button" 
                                        class="px-4 py-2 bg-rose-50 text-rose-600 rounded-lg text-xs font-bold hover:bg-rose-100 transition-colors cursor-pointer"
                                    >
                                        <i class="fas fa-trash mr-2"></i>Delete
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    templates: Array
});

const deleteTemplate = (template) => {
    if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
        router.delete(`/email-templates/${template.id}`);
    }
};
</script>

<style>
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
    animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>

<style>
.jodit-container {
    border-radius: 12px !important;
    border: 1px solid #e5e7eb !important;
    overflow: hidden;
}
.jodit-toolbar {
    background-color: #f9fafb !important;
    border-bottom: 1px solid #e5e7eb !important;
}
.jodit-status-bar {
    display: none !important;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
    animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
