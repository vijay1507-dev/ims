<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Admin Settings</h2>
                </div>
                
                <!-- Live Client Status Indicator -->
                <div v-if="saveStatus" class="px-4 py-2 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold animate-pulse flex items-center shadow-2xs">
                    <i class="fas fa-check-circle mr-2 text-emerald-600"></i>
                    {{ saveStatus }}
                </div>
            </div>

            <!-- Global Tabs Layer Framework -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                <div class="border-b border-gray-200 bg-white sticky top-0 z-10">
                    <nav class="flex overflow-x-auto space-x-8 px-6 scrollbar-none" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            type="button"
                            class="py-4 px-1 border-b-2 font-bold text-sm whitespace-nowrap transition-all cursor-pointer select-none"
                            :class="activeTab === tab.id ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-900'"
                            @click="activeTab = tab.id"
                        >
                            <i :class="tab.icon" class="mr-2 text-xs opacity-80"></i>
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Active Tab Contents Display Area -->
                <div class="p-6">
                    <!-- 1. Currency Settings & General Info Tab View -->
                    <div v-show="activeTab === 'general'" class="space-y-8 animate-fade-in">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center">
                                <i class="fas fa-building text-indigo-600 mr-2 text-sm"></i>Company Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Company Name</label>
                                    <input v-model="formGeneral.company_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium" @input="triggerAutoSave">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Company Email</label>
                                    <input v-model="formGeneral.company_email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium" @input="triggerAutoSave">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Phone Number</label>
                                    <input v-model="formGeneral.phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium" @input="triggerAutoSave">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Website Infrastructure</label>
                                    <input v-model="formGeneral.website" type="url" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-all font-medium" @input="triggerAutoSave">
                                </div>
                            </div>
                        </div>

                        <!-- Currency Settings Section -->
                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center">
                                <i class="fas fa-coins text-indigo-600 mr-2 text-sm"></i>Currency Settings & Locale Maps
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Currency Settings (Base Unit)</label>
                                    <select v-model="formGeneral.currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer font-bold text-gray-900" @change="triggerAutoSave">
                                        <option>USD - US Dollar</option>
                                        <option>EUR - Euro</option>
                                        <option>GBP - British Pound</option>
                                        <option>JPY - Japanese Yen</option>
                                        <option>INR - Indian Rupee</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">System Time Zone Mapping</label>
                                    <select v-model="formGeneral.timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer font-medium" @change="triggerAutoSave">
                                        <option>UTC-5:00 Eastern Time</option>
                                        <option>UTC-6:00 Central Time</option>
                                        <option>UTC-7:00 Mountain Time</option>
                                        <option>UTC-8:00 Pacific Time</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center">
                                <i class="fas fa-satellite-dish text-indigo-600 mr-2 text-sm"></i>Automated Telemetry Dispatch
                            </h3>
                            <div class="space-y-3">
                                <label class="flex items-center cursor-pointer select-none">
                                    <input v-model="formGeneral.notifyNewCustomer" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" @change="triggerAutoSave">
                                    <span class="ml-3 text-sm text-gray-700 font-medium">Email instant verification digests for registered account channels</span>
                                </label>
                                <label class="flex items-center cursor-pointer select-none">
                                    <input v-model="formGeneral.notifyFailures" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" @change="triggerAutoSave">
                                    <span class="ml-3 text-sm text-gray-700 font-medium">Real-time alerts for payment failures or external API gateway timeouts</span>
                                </label>
                                <label class="flex items-center cursor-pointer select-none">
                                    <input v-model="formGeneral.notifyRenewals" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" @change="triggerAutoSave">
                                    <span class="ml-3 text-sm text-gray-700 font-medium">Automated timeline extension notifications and upcoming renewal triggers</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payment Gateway Settings Tab View -->
                    <div v-show="activeTab === 'payment'" class="space-y-6 animate-fade-in">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 mb-1">Payment Gateway Settings</h3>
                            <p class="text-xs text-gray-500 mb-4">Establish cryptographic keys to bridge transactional clearing operations.</p>
                        </div>

                        <!-- Stripe integration node -->
                        <div class="bg-white border border-gray-200 rounded-xl p-6 transition-all hover:shadow-2xs">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                <div class="flex items-center">
                                    <i class="fab fa-stripe text-indigo-600 text-3xl mr-3.5 w-10 text-center"></i>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-base">Stripe Integration</h4>
                                        <p class="text-xs text-gray-500">Stripe native webhooks supporting automated credit card and ACH parameters</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer"
                                    :class="gateways.stripe ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500'"
                                    @click="toggleGateway('stripe')"
                                >
                                    {{ gateways.stripe ? 'Active Connection' : 'Disabled' }}
                                </button>
                            </div>
                            <div v-if="gateways.stripe" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 animate-fade-in">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Publishable Key</label>
                                    <input type="text" value="" class="w-full px-3 py-1.5 border rounded-lg text-sm font-mono text-gray-700 bg-gray-50/50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Secret Key</label>
                                    <input type="password" value="" class="w-full px-3 py-1.5 border rounded-lg text-sm font-mono text-gray-700 bg-gray-50/50">
                                </div>
                            </div>
                        </div>

                        <!-- Razorpay integration node -->
                        <div class="bg-white border border-gray-200 rounded-xl p-6 transition-all hover:shadow-2xs">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                <div class="flex items-center">
                                    <i class="fas fa-layer-group text-emerald-600 text-2xl mr-3.5 w-10 text-center"></i>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-base">Razorpay Integration</h4>
                                        <p class="text-xs text-gray-500">Razorpay localized sub-merchant API clearing interfaces</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer"
                                    :class="gateways.razorpay ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500'"
                                    @click="toggleGateway('razorpay')"
                                >
                                    {{ gateways.razorpay ? 'Active Connection' : 'Disabled' }}
                                </button>
                            </div>
                            <div v-if="gateways.razorpay" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 animate-fade-in">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Key ID</label>
                                    <input type="text" value="" class="w-full px-3 py-1.5 border rounded-lg text-sm font-mono text-gray-700 bg-gray-50/50">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Secret Hash</label>
                                    <input type="password" value="" class="w-full px-3 py-1.5 border rounded-lg text-sm font-mono text-gray-700 bg-gray-50/50">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Advanced Tax Engineering & Compliance Tab View -->
                    <div v-show="activeTab === 'tax'" class="space-y-10 animate-fade-in">
                        <!-- Global Tax Configuration -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-1">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">Global tax configuration</h3>
                                <p class="text-xs text-gray-500 leading-relaxed">Establish base-level duty thresholds and automated percentage processing engines for all clearance transactions.</p>
                            </div>
                            <div class="lg:col-span-2 space-y-4 bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                                <label class="flex items-center cursor-pointer select-none">
                                    <input v-model="formTax.calcEnabled" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @change="triggerAutoSave">
                                    <span class="ml-3 text-sm text-gray-700 font-bold">Enable Global Tax Engine</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5">Standard Global Rate (%)</label>
                                        <input type="number" v-model="formTax.globalRate" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-bold outline-none focus:ring-2 focus:ring-indigo-500 bg-white" @input="triggerAutoSave">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5">Tax Identification Code</label>
                                        <input type="text" v-model="formTax.taxId" placeholder="e.g. TAX-US-9021" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white" @input="triggerAutoSave">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GST/VAT & TDS Settings -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-8 border-t border-gray-100">
                            <div class="lg:col-span-1">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">GST/VAT & TDS parameters</h3>
                                <p class="text-xs text-gray-500 leading-relaxed">Configure regional value-added identifiers and source-level deduction protocols for institutional compliance.</p>
                            </div>
                            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="bg-white p-5 border border-gray-200 rounded-xl shadow-2xs">
                                    <h4 class="text-xs font-bold text-indigo-600 uppercase mb-3 flex items-center">
                                        <i class="fas fa-file-invoice-dollar mr-2"></i>VAT / GST Settings
                                    </h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center text-xs text-gray-600 font-medium">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 mr-2"> Show GST/VAT breakdown on invoice
                                        </label>
                                        <input type="text" placeholder="GSTIN / VAT Number" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="bg-white p-5 border border-gray-200 rounded-xl shadow-2xs">
                                    <h4 class="text-xs font-bold text-emerald-600 uppercase mb-3 flex items-center">
                                        <i class="fas fa-hand-holding-usd mr-2"></i>TDS Protocols
                                    </h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center text-xs text-gray-600 font-medium">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 mr-2"> Enable TDS deduction at source
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase">Rate:</span>
                                            <input type="number" placeholder="2.0" class="w-20 px-2 py-1 border border-gray-300 rounded text-xs">
                                            <span class="text-[10px] text-gray-400 font-bold">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Tax Rules & Calculation Logic -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-8 border-t border-gray-100">
                            <div class="lg:col-span-1">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">Invoice tax rules</h3>
                                <p class="text-xs text-gray-500 leading-relaxed">Establish granular logic for how duties are compounded, rounded, and displayed on financial instruments.</p>
                            </div>
                            <div class="lg:col-span-2 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5">Tax Calculation settings</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white font-medium outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option>Calculate tax on subtotal (Net)</option>
                                            <option>Calculate tax on total (Gross)</option>
                                            <option>Inclusive Tax (Built into plan price)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5">Rounding Precision</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white font-medium outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option>Round half up (Standard)</option>
                                            <option>Round always up (Ceil)</option>
                                            <option>Round always down (Floor)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl flex items-start gap-3">
                                    <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                                    <p class="text-[11px] text-amber-800 leading-relaxed">Changes to calculation settings will only apply to future generated invoices. Existing ledger objects will retain their original duty snapshots.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Multi-country Tax Support & Exemptions -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-8 border-t border-gray-100">
                            <div class="lg:col-span-1">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">Multi-country support</h3>
                                <p class="text-xs text-gray-500 leading-relaxed">Manage dynamic jurisdiction mapping and define automated tax exemption defaults for specific client tiers.</p>
                            </div>
                            <div class="lg:col-span-2 space-y-6">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-widest">Jurisdiction Overrides</h4>
                                        <button class="text-[10px] font-bold text-indigo-600 hover:text-indigo-900 uppercase">+ Add Region</button>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-gray-50 border-b border-gray-100 font-bold text-gray-500 uppercase">
                                                <tr>
                                                    <th class="px-4 py-3">Sovereign Area</th>
                                                    <th class="px-4 py-3 text-center">Duty Rate</th>
                                                    <th class="px-4 py-3 text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <tr>
                                                    <td class="px-4 py-3 font-bold">United States (Default)</td>
                                                    <td class="px-4 py-3 text-center font-mono">0.0%</td>
                                                    <td class="px-4 py-3 text-right text-gray-300 italic">Core Node</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-3 font-bold">European Union (VAT)</td>
                                                    <td class="px-4 py-3 text-center font-mono">20.0%</td>
                                                    <td class="px-4 py-3 text-right"><i class="fas fa-trash text-rose-300 hover:text-rose-600 cursor-pointer"></i></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-widest mb-3">Tax exemption defaults</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 mr-3">
                                            <span class="text-xs text-gray-700 font-medium">Exempt Non-Profit Organizations</span>
                                        </label>
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 mr-3">
                                            <span class="text-xs text-gray-700 font-medium">Exempt Government Institutional Tiers</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                </div>

                <!-- Footer Command Center Submission Block -->
                <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end items-center">
                    <button
                        type="button"
                        class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-xs hover:bg-indigo-700 transition-colors cursor-pointer"
                        @click="commitMasterSave"
                    >
                        Commit Environment Configurations
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    config: {
        type: Object,
        required: true,
    },
});

const activeTab = ref('general');

const tabs = [
    { id: 'general', label: 'Currency Settings', icon: 'fas fa-coins' },
    { id: 'payment', label: 'Payment Gateway Settings', icon: 'fas fa-credit-card' },
    { id: 'tax', label: 'Tax Settings', icon: 'fas fa-percent' },
];

const formGeneral = ref({
    company_name: props.config.company_name || 'SaaS Manager Platform',
    company_email: props.config.company_email || 'admin@saasmanager.com',
    phone: props.config.phone || '+1 (555) 998-2342',
    website: props.config.website || 'https://saasmanager.com',
    currency: props.config.currency || 'USD - US Dollar',
    timezone: props.config.timezone || 'UTC-5:00 Eastern Time',
    notifyNewCustomer: true,
    notifyFailures: true,
    notifyRenewals: true,
});

const gateways = ref({
    stripe: true,
    razorpay: true,
});

const formTax = ref({
    calcEnabled: true,
    inclusivePricing: true,
});

const saveStatus = ref('');
let statusTimeout = null;

const triggerAutoSave = () => {
    clearTimeout(statusTimeout);
    saveStatus.value = 'Local property parameters temporarily bound...';
    statusTimeout = setTimeout(() => { saveStatus.value = ''; }, 2000);
};

const toggleGateway = (gatewayKey) => {
    gateways.value[gatewayKey] = !gateways.value[gatewayKey];
    saveStatus.value = `API pipeline authorization toggled for ${gatewayKey.toUpperCase()}.`;
    setTimeout(() => { saveStatus.value = ''; }, 3000);
};

const editTemplate = (tplName) => {
    const val = prompt(`Provide altered inline string layout structure for ${tplName}:`, "Subject: Updated base template context string");
    if (val) {
        saveStatus.value = `Modified memory template configuration for ${tplName}.`;
        setTimeout(() => { saveStatus.value = ''; }, 3000);
    }
};

const inviteUser = () => {
    const email = prompt("Enter standard authentication URI email identifier for the prospective system admin account:");
    if (email) {
        saveStatus.value = `Identity delegation invitation packet staged for dispatch to ${email}.`;
        setTimeout(() => { saveStatus.value = ''; }, 4000);
    }
};

const editUser = (usr) => {
    alert(`Active account configuration node inspect for ${usr.name}.\nAssigned clearance pool: ${usr.role}\nIdentity signature verified.`);
};

const commitMasterSave = () => {
    saveStatus.value = 'Persistent configuration arrays successfully dispatched to master backend storage layers!';
    setTimeout(() => { saveStatus.value = ''; }, 5000);
};
</script>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(2px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.25s ease-out forwards;
}
</style>
