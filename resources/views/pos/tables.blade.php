@extends('layouts.blank')

@section('content')
<div class="h-screen w-full bg-[#F6F8FC] dark:bg-[#0f172a] flex flex-col font-sans relative overflow-hidden" x-data="posTables()">
    
    {{-- 1. HEADER (រក្សានៅដដែល) --}}
    <div class="px-6 py-5 flex justify-between items-center bg-white/80 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 z-20 shrink-0">
        <div>
            <h1 class="text-3xl font-black text-gray-800 dark:text-white mb-1">{{ __('messages.select_table') }}</h1>
            <p class="text-sm font-medium text-gray-500">{{ __('messages.please_select_table_to_order') }}</p>
        </div>
        <div class="flex gap-3">
            <div class="flex items-center gap-2 bg-white dark:bg-gray-700 px-4 py-2 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase">{{ __('messages.available') }}</span>
            </div>
            <div class="flex items-center gap-2 bg-white dark:bg-gray-700 px-4 py-2 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase">{{ __('messages.busy') }}</span>
            </div>
        </div>
    </div>

    {{-- Loading (រក្សានៅដដែល) --}}
    <div x-show="isLoading && tables.length === 0" class="flex-1 flex flex-col items-center justify-center text-gray-400">
        <i class="ri-loader-4-line text-5xl animate-spin mb-4 text-primary"></i>
        <p>Loading...</p>
    </div>

    {{-- 2. TABLES GRID (រក្សានៅដដែល) --}}
    <div class="flex-1 overflow-y-auto p-6 custom-scrollbar" x-show="tables.length > 0" x-cloak>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            <template x-for="table in tables" :key="table.id">
                <div class="relative group">
                    <a :href="'/pos/menu/' + table.id" 
                       class="block aspect-square rounded-[32px] p-4 flex flex-col items-center justify-center transition-all duration-300 border-[3px] shadow-sm hover:shadow-xl hover:-translate-y-1 active:scale-95 relative overflow-hidden"
                       :class="table.status === 'available' ? 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 hover:border-emerald-400/50' : 'bg-rose-50 dark:bg-rose-900/10 border-rose-100 dark:border-rose-900/30 hover:border-rose-400/50'">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 transition-colors duration-300"
                             :class="table.status === 'available' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-100 text-rose-500'">
                             <i class="ri-restaurant-2-fill text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white mb-1" x-text="table.name"></h3>
                        <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-md"
                              :class="table.status === 'available' ? 'text-emerald-600 bg-emerald-100/50' : 'text-rose-600 bg-rose-100/50'"
                              x-text="table.status === 'available' ? '{{ __('messages.available') }}' : '{{ __('messages.busy') }}'">
                        </span>
                    </a>
                    <template x-if="table.status === 'busy'">
                        <button @click.prevent="openQuickCheckout(table)"
                                class="absolute top-2 right-2 w-11 h-11 bg-white dark:bg-gray-700 text-rose-500 rounded-full shadow-lg hover:bg-rose-500 hover:text-white hover:scale-110 transition-all z-10 flex items-center justify-center border border-rose-100 dark:border-rose-900 group/btn">
                            <i class="ri-money-dollar-circle-line text-2xl group-hover/btn:animate-wiggle"></i>
                        </button>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- MODAL: BILL DETAILS (កែសម្រួលថ្មី)           --}}
    {{-- =========================================== --}}
    <div x-show="isCheckoutModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="isCheckoutModalOpen = false"></div>

        <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-[32px] shadow-2xl overflow-hidden flex flex-col md:flex-row h-[85vh] md:h-[650px]"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            {{-- LEFT SIDE: BILL ITEMS --}}
            <div class="flex-1 bg-gray-50 dark:bg-gray-900/50 flex flex-col border-r border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="ri-file-list-3-line text-primary"></i> Bill Detail
                    </h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium font-mono" x-text="'Invoice: #' + (orderDetails.invoice_number || '...')"></p>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                    <div x-show="isLoadingOrder" class="flex justify-center py-10">
                        <i class="ri-loader-4-line animate-spin text-3xl text-gray-400"></i>
                    </div>

                    <div x-show="!isLoadingOrder" class="space-y-6">
                        <template x-for="item in orderDetails.items" :key="item.id">
                            <div class="border-b border-gray-100 dark:border-gray-800 pb-4 last:border-0">
                                {{-- Main Product Line --}}
                                <div class="flex justify-between items-start mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-800 dark:text-white text-base" x-text="item.product.name"></span>
                                        <span class="text-xs font-mono text-gray-500 bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded">x<span x-text="item.quantity"></span></span>
                                    </div>
                                    <p class="font-bold text-gray-900 dark:text-white text-base" 
                                       x-text="'$' + (item.price * item.quantity).toFixed(2)"></p>
                                </div>

                                {{-- ADDONS LIST (បង្ហាញ Qty ច្បាស់ៗ) --}}
                                <template x-if="item.addons && item.addons.length > 0">
                                    <div class="pl-0 space-y-1 mt-1">
                                        <template x-for="ad in item.addons">
                                            <div class="flex justify-between items-center text-sm text-gray-500">
                                                <div class="flex items-center gap-1.5">
                                                    <i class="ri-add-line text-[10px]"></i>
                                                    <span x-text="(ad.addon ? ad.addon.name : 'Unknown')"></span>
                                                    {{-- បង្ហាញ Qty Addon --}}
                                                    <span class="text-[10px] bg-blue-50 text-blue-600 px-1 rounded" 
                                                          x-text="'x' + (ad.quantity || 1)"></span>
                                                </div>
                                                <span class="font-medium text-xs" 
                                                      x-text="'+$' + (parseFloat(ad.price) * (ad.quantity || 1)).toFixed(2)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Note --}}
                                <template x-if="item.note">
                                    <p class="text-[10px] text-orange-500 italic mt-1" x-text="'Note: ' + item.note"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Total Footer --}}
                <div class="p-6 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-end">
                        <span class="text-lg font-bold text-gray-800 dark:text-white">Total Amount</span>
                        <span class="text-3xl font-black text-primary" x-text="'$' + parseFloat(orderDetails.total || 0).toFixed(2)"></span>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDE: PAYMENT METHOD (រក្សានៅដដែល) --}}
            <div class="w-full md:w-[380px] bg-white dark:bg-gray-800 p-8 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-extrabold text-gray-800 dark:text-white mb-6">Payment Method</h3>
                    <div class="space-y-4">
                        <div @click="paymentMethod = 'cash'" class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between group" :class="paymentMethod === 'cash' ? 'border-primary bg-primary/5' : 'border-gray-100 dark:border-gray-700 hover:border-gray-300'">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="ri-money-dollar-circle-fill text-xl"></i></div>
                                <span class="font-bold text-gray-700 dark:text-white text-lg">Cash</span>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'cash' ? 'border-primary' : 'border-gray-300'">
                                <div class="w-3 h-3 rounded-full bg-primary" x-show="paymentMethod === 'cash'"></div>
                            </div>
                        </div>
                        <div @click="paymentMethod = 'qr'" class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between group" :class="paymentMethod === 'qr' ? 'border-primary bg-primary/5' : 'border-gray-100 dark:border-gray-700 hover:border-gray-300'">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i class="ri-qr-code-line text-xl"></i></div>
                                <span class="font-bold text-gray-700 dark:text-white text-lg">KHQR Scan</span>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'qr' ? 'border-primary' : 'border-gray-300'">
                                <div class="w-3 h-3 rounded-full bg-primary" x-show="paymentMethod === 'qr'"></div>
                            </div>
                        </div>
                    </div>
                    <div x-show="paymentMethod === 'cash'" x-transition class="mt-8">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 block">RECEIVED AMOUNT ($)</label>
                        <input type="number" x-model="receivedAmount" class="w-full text-3xl font-black border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all" placeholder="0.00">
                        <div class="flex justify-between items-center mt-4 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-xl">
                            <span class="text-sm font-bold text-gray-500">Change:</span>
                            <span class="font-black text-xl text-green-600" x-text="'$' + Math.max(0, receivedAmount - orderDetails.total).toFixed(2)"></span>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 mt-6">
                    <button @click="processPayment()" :disabled="isProcessing || (paymentMethod === 'cash' && receivedAmount < orderDetails.total)" class="w-full bg-gray-900 dark:bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                        <i x-show="isProcessing" class="ri-loader-4-line animate-spin text-xl"></i>
                        <span>Print Invoice & Pay</span>
                    </button>
                    <button @click="isCheckoutModalOpen = false" class="w-full py-3 text-gray-500 hover:text-gray-800 dark:hover:text-white font-bold transition-colors">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- HIDDEN RECEIPT (For Printing - កែថ្មី)       --}}
    {{-- =========================================== --}}
    <div id="receipt-print-area" class="hidden">
        <div class="w-[78mm] mx-auto p-2 font-mono text-black text-[12px] leading-tight">
            {{-- Header --}}
            <div class="text-center mb-4">
                <template x-if="orderDetails.shop && orderDetails.shop.logo">
                    <img :src="'/storage/' + orderDetails.shop.logo" class="h-12 mx-auto mb-2 object-contain grayscale"> 
                </template>
                <h1 class="text-lg font-bold uppercase mb-1" x-text="orderDetails.shop ? orderDetails.shop.shop_en : 'POS SYSTEM'"></h1>
                <p x-text="orderDetails.shop ? orderDetails.shop.address_en : ''"></p>
                <p x-text="orderDetails.shop ? ('Tel: ' + orderDetails.shop.phone_number) : ''"></p>
            </div>

            {{-- Info --}}
            <div class="border-b border-dashed border-black pb-2 mb-2">
                <div class="flex justify-between"><span>Inv:</span> <span class="font-bold" x-text="orderDetails.invoice_number"></span></div>
                <div class="flex justify-between"><span>Date:</span> <span x-text="orderDetails.date"></span></div>
                <div class="flex justify-between"><span>Table:</span> <span x-text="selectedTable ? selectedTable.name : ''"></span></div>
                <div class="flex justify-between"><span>Cashier:</span> <span>{{ auth()->user()->name }}</span></div>
            </div>

            {{-- Items --}}
            <table class="w-full mb-2">
                <thead>
                    <tr class="border-b border-black">
                        <th class="text-left py-1 w-[45%]">Item</th>
                        <th class="text-center py-1 w-[15%]">Qty</th>
                        <th class="text-right py-1 w-[20%]">Price</th>
                        <th class="text-right py-1 w-[20%]">Total</th>
                    </tr>
                </thead>
                <tbody class="align-top">
                    <template x-for="item in orderDetails.items" :key="item.id">
                        <tr>
                            <td class="py-1 pr-1">
                                <div class="font-bold" x-text="item.product.name"></div>
                                {{-- Print Addons with Qty --}}
                                <template x-if="item.addons && item.addons.length > 0">
                                    <div class="text-[10px] italic mt-0.5">
                                        <template x-for="ad in item.addons">
                                            <div class="flex justify-between pr-2">
                                                <span>+ <span x-text="ad.addon ? ad.addon.name : 'Addon'"></span></span>
                                                <span>
                                                    <span x-text="'x' + (ad.quantity || 1)"></span>
                                                    <span x-text="'($' + (parseFloat(ad.price) * (ad.quantity || 1)).toFixed(2) + ')'"></span>
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </td>
                            <td class="text-center py-1" x-text="item.quantity"></td>
                            <td class="text-right py-1" x-text="parseFloat(item.price).toFixed(2)"></td>
                            <td class="text-right py-1 font-bold" x-text="(parseFloat(item.price) * item.quantity).toFixed(2)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            {{-- Total & Footer --}}
            <div class="border-t border-dashed border-black pt-2">
                <div class="flex justify-between text-base font-bold mb-1">
                    <span>TOTAL:</span>
                    <span x-text="'$' + parseFloat(orderDetails.total || 0).toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span>Paid By:</span>
                    <span class="uppercase font-bold" x-text="paymentMethod === 'qr' ? 'KHQR' : 'CASH'"></span>
                </div>
                <template x-if="paymentMethod === 'cash'">
                    <div>
                        <div class="flex justify-between text-xs"><span>Received:</span> <span x-text="'$' + parseFloat(receivedAmount || 0).toFixed(2)"></span></div>
                        <div class="flex justify-between text-xs font-bold mt-1"><span>Change:</span> <span x-text="'$' + Math.max(0, (receivedAmount || 0) - (orderDetails.total || 0)).toFixed(2)"></span></div>
                    </div>
                </template>
            </div>
            <div class="text-center mt-6 border-t border-black pt-2">
                <p class="font-bold">*** THANK YOU ***</p>
                <p class="text-[10px] mt-1" x-text="orderDetails.shop ? orderDetails.shop.description_en : 'Powered by IceCream POS'"></p>
            </div>
        </div>
    </div>

</div>

<script>
    function posTables() {
        return {
            tables: [],
            isLoading: false,
            interval: null,
            isCheckoutModalOpen: false,
            isLoadingOrder: false,
            isProcessing: false,
            selectedTable: null,
            orderDetails: { items: [], total: 0 },
            paymentMethod: 'cash',
            receivedAmount: '',

            init() {
                this.fetchTables();
                this.interval = setInterval(() => { if (!this.isCheckoutModalOpen) this.fetchTables(true); }, 5000);
            },

            async fetchTables(silent = false) {
                if (!silent) this.isLoading = true;
                try {
                    const response = await fetch("{{ route('pos.tables.fetch') }}");
                    this.tables = await response.json();
                } catch (error) { console.error(error); } 
                finally { if (!silent) this.isLoading = false; }
            },

            async openQuickCheckout(table) {
                this.selectedTable = table;
                this.isCheckoutModalOpen = true;
                this.isLoadingOrder = true;
                this.paymentMethod = 'cash';
                this.receivedAmount = '';

                try {
                    const response = await fetch(`/pos/order-details/${table.id}`);
                    if (!response.ok) throw new Error("Order not found");
                    this.orderDetails = await response.json();
                    this.receivedAmount = this.orderDetails.total; 
                } catch (error) {
                    alert("Cannot load order details.");
                    this.isCheckoutModalOpen = false;
                } finally {
                    this.isLoadingOrder = false;
                }
            },

            async processPayment() {
                this.isProcessing = true;
                try {
                    const response = await fetch("{{ route('pos.order.checkout') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({
                            table_id: this.selectedTable.id,
                            received_amount: this.receivedAmount,
                            payment_method: this.paymentMethod
                        })
                    });
                    
                    if (response.ok) {
                        this.isCheckoutModalOpen = false;
                        setTimeout(() => window.print(), 300);
                        window.onafterprint = () => { this.fetchTables(); };
                        setTimeout(() => { this.fetchTables(); }, 3000);
                    } else {
                        alert("Payment Failed!");
                    }
                } catch (error) {
                    console.error(error);
                    alert("Error processing payment.");
                } finally {
                    this.isProcessing = false;
                }
            }
        }
    }
</script>

<style>
    @media print {
        body * { visibility: hidden; height: 0; overflow: hidden; }
        #receipt-print-area {
            display: block !important; visibility: visible !important;
            position: absolute; left: 0; top: 0; width: 80mm;
            margin: 0 auto; padding: 0; height: auto !important;
            background-color: white !important; color: black !important;
        }
        #receipt-print-area * { visibility: visible !important; height: auto !important; }
        @page { margin: 0; size: auto; }
    }
</style>
@endsection