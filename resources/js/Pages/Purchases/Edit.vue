<template>
    <AppLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <!-- Breadcrumbs / Back button -->
            <div class="mb-6">
                <Link
                    href="/purchases"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors inline-flex items-center"
                >
                    <i class="fas fa-arrow-left mr-2"></i>Back to Purchases
                </Link>
            </div>

            <!-- Page Title -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Edit Purchase Order: {{ purchase.purchase_number }}</h2>
                <p class="text-sm text-gray-500 mt-1">Modify supplier, basic order details, status, or item ledger.</p>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-xl shadow-2xs p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Supplier Selection -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Supplier / Vendor Name *</label>
                            <input
                                v-model="form.supplier_name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-rose-500': form.errors.supplier_name }"
                            />
                            <p v-if="form.errors.supplier_name" class="text-xs text-rose-600 mt-1">{{ form.errors.supplier_name }}</p>
                        </div>

                        <!-- Purchase Date -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Purchase Date *</label>
                            <input
                                v-model="form.purchase_date"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-rose-500': form.errors.purchase_date }"
                            />
                            <p v-if="form.errors.purchase_date" class="text-xs text-rose-600 mt-1">{{ form.errors.purchase_date }}</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Order Status *</label>
                            <select
                                v-model="form.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                                :class="{ 'border-rose-500': form.errors.status }"
                            >
                                <option value="pending">Pending</option>
                                <option value="ordered">Ordered</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <p v-if="form.errors.status" class="text-xs text-rose-600 mt-1">{{ form.errors.status }}</p>
                        </div>

                        <!-- Payment Status -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Payment Status *</label>
                            <select
                                v-model="form.payment_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer"
                                :class="{ 'border-rose-500': form.errors.payment_status }"
                            >
                                <option value="unpaid">Unpaid</option>
                                <option value="partially_paid">Partially Paid</option>
                                <option value="paid">Paid</option>
                            </select>
                            <p v-if="form.errors.payment_status" class="text-xs text-rose-600 mt-1">{{ form.errors.payment_status }}</p>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Notes / Memo</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Instructions or internal details regarding this order..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Line Items Section -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-2xs p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Line Items</h3>
                        <button
                            type="button"
                            @click="addItem"
                            class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-colors inline-flex items-center cursor-pointer"
                        >
                            <i class="fas fa-plus mr-1.5"></i>Add Item
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="flex flex-col md:flex-row items-start md:items-center gap-4 p-4 border border-gray-150 rounded-lg bg-gray-50/50"
                        >
                            <!-- Item name -->
                            <div class="flex-1 w-full">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Item Name *</label>
                                <input
                                    v-model="item.item_name"
                                    type="text"
                                    placeholder="e.g. Smart TV Model X"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <!-- Category selection/input -->
                            <div class="w-full md:w-48">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Category *</label>
                                <select
                                    v-model="item.category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                                    required
                                >
                                    <option value="" disabled>Select Category</option>
                                    <optgroup label="Inventory Tracked Assets">
                                        <option value="kiosk">Kiosks</option>
                                        <option value="tv">Smart TVs</option>
                                        <option value="tablet">Tablets</option>
                                        <option value="printer">Printers</option>
                                        <option value="license">Licenses</option>
                                        <option value="desktop">Desktops</option>
                                        <option value="display">Displays</option>
                                        <option value="mobile">Mobiles</option>
                                    </optgroup>
                                    <optgroup label="General Purchases">
                                        <option value="software_subscription">Software / SaaS</option>
                                        <option value="office_supplies">Office Supplies</option>
                                        <option value="marketing">Marketing & Promo</option>
                                        <option value="professional_services">Professional Services</option>
                                        <option value="utilities">Utilities & Hosting</option>
                                        <option value="travel">Travel & Meals</option>
                                        <option value="other">Other Operations</option>
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="w-full md:w-28">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Qty *</label>
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <!-- Unit Cost -->
                            <div class="w-full md:w-32">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cost ($) *</label>
                                <input
                                    v-model.number="item.unit_cost"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <!-- Subtotal -->
                            <div class="w-full md:w-28 text-right pr-2">
                                <span class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Subtotal</span>
                                <span class="text-sm font-bold text-gray-900">${{ (item.quantity * item.unit_cost).toFixed(2) }}</span>
                            </div>

                            <!-- Remove button -->
                            <div class="w-full md:w-auto text-right md:pt-4">
                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                    title="Delete line item"
                                    :disabled="form.items.length <= 1"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Overall Purchase total summary -->
                    <div class="flex justify-end items-center mt-6 pt-6 border-t border-gray-150">
                        <span class="text-gray-500 mr-4 font-semibold">Total Order Amount:</span>
                        <span class="text-2xl font-black text-gray-900">${{ totalAmount.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Form actions buttons -->
                <div class="flex justify-end space-x-3">
                    <Link
                        href="/purchases"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold transition-colors shadow-xs disabled:opacity-50 cursor-pointer"
                    >
                        Update Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';

const props = defineProps({
    purchase: Object,
});

// Format date string from database to YYYY-MM-DD
const formattedDate = new Date(props.purchase.purchase_date).toISOString().split('T')[0];

const form = useForm({
    supplier_name: props.purchase.supplier_name,
    purchase_date: formattedDate,
    status: props.purchase.status,
    payment_status: props.purchase.payment_status,
    notes: props.purchase.notes,
    items: props.purchase.items.map(item => ({
        item_name: item.item_name,
        category: item.category,
        quantity: item.quantity,
        unit_cost: parseFloat(item.unit_cost) || 0,
    })),
});

const addItem = () => {
    form.items.push({ item_name: '', category: '', quantity: 1, unit_cost: 0 });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const totalAmount = computed(() => {
    return form.items.reduce((sum, item) => {
        const qty = parseFloat(item.quantity) || 0;
        const cost = parseFloat(item.unit_cost) || 0;
        return sum + (qty * cost);
    }, 0);
});

const submit = () => {
    form.put(`/purchases/${props.purchase.id}`);
};
</script>
