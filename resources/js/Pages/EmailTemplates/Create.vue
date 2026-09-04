<template>
    <AppLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <Link 
                    href="/email-templates"
                    class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors flex items-center gap-2 mb-4"
                >
                    <i class="fas fa-arrow-left"></i>
                    Back to Templates
                </Link>
                <h2 class="text-3xl font-bold text-gray-900">Create New Template</h2>
                <p class="text-gray-600 mt-1">Initialize a new automated communication trigger point</p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden animate-slide-up">
                <form @submit.prevent="submit" class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Template Name</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                placeholder="e.g. Welcome Sequencer"
                                class="w-full px-5 py-4 rounded-2xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-medium text-gray-900"
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Description</label>
                            <input 
                                v-model="form.description"
                                type="text" 
                                placeholder="Short internal note about this template"
                                class="w-full px-5 py-4 rounded-2xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-medium text-gray-900"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Email Subject</label>
                        <input 
                            v-model="form.subject"
                            type="text" 
                            placeholder="Email subject line (supports #{{tags}})"
                            class="w-full px-5 py-4 rounded-2xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-medium text-gray-900"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Message Body</label>
                        <textarea 
                            id="jodit-editor"
                            v-model="form.body"
                            class="w-full px-5 py-4 rounded-2xl border border-gray-200 min-h-[400px]"
                        ></textarea>
                    </div>

                    <div class="pt-6 flex justify-end">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 disabled:opacity-50 transition-all flex items-center gap-2"
                        >
                            <i v-if="form.processing" class="fas fa-spinner animate-spin"></i>
                            Create Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    description: '',
    subject: '',
    body: '',
});

let editor = null;

onMounted(() => {
    initJodit();
});

onBeforeUnmount(() => {
    if (editor) {
        editor.destruct();
    }
});

const initJodit = () => {
    if (typeof window.Jodit === 'undefined') {
        if (!document.getElementById('jodit-script')) {
            const script = document.createElement('script');
            script.id = 'jodit-script';
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.js';
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.css';
            document.head.appendChild(link);
            document.head.appendChild(script);

            script.onload = () => {
                createEditor();
            };
        } else {
            const checkJodit = setInterval(() => {
                if (typeof window.Jodit !== 'undefined') {
                    clearInterval(checkJodit);
                    createEditor();
                }
            }, 100);
        }
    } else {
        createEditor();
    }
};

const createEditor = () => {
    if (editor) return;

    editor = new window.Jodit('#jodit-editor', {
        height: 500,
        buttons: 'bold,italic,underline,strikethrough,|,ul,ol,|,font,fontsize,brush,paragraph,|,image,table,link,|,align,undo,redo',
        theme: 'default',
        uploader: {
            insertImageAsBase64URI: true
        }
    });
    editor.value = form.body;
    editor.events.on('change', (newValue) => {
        form.body = newValue;
    });
};

const submit = () => {
    form.post('/email-templates');
};
</script>

<style>
.jodit-container {
    border-radius: 16px !important;
    border: 1px solid #e5e7eb !important;
    overflow: hidden;
    box-shadow: none !important;
}
.jodit-toolbar {
    background-color: #f9fafb !important;
    border-bottom: 1px solid #e5e7eb !important;
    padding: 6px !important;
}
.jodit-status-bar {
    display: none !important;
}
.jodit-workplace {
    background-color: white !important;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
    animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
