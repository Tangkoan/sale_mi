<div x-show="isCheckoutModalOpen" class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-0 md:p-4" style="display: none;" x-cloak>
    
    {{-- 1. BACKDROP (ផ្ទៃខាងក្រោយខ្មៅ) --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300" 
         x-show="isCheckoutModalOpen"
         @click="isCheckoutModalOpen = false"></div>

    {{-- 2. MODAL CONTAINER --}}
    <div class="relative w-full max-w-5xl bg-gray-100 dark:bg-gray-900 rounded-t-[20px] md:rounded-[24px] shadow-2xl overflow-hidden flex flex-col md:flex-row h-[95vh] md:h-[700px] transition-transform duration-300 transform"
         x-show="isCheckoutModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0">

        {{-- Mobile Drag Handle (សម្រាប់ទាញចុះនៅទូរស័ព្ទ) --}}
        <div class="w-full flex justify-center pt-3 pb-1 md:hidden bg-white dark:bg-gray-800 shrink-0 cursor-grab" @click="isCheckoutModalOpen = false">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
        </div>

        {{-- ================================================= --}}
        {{-- LEFT SIDE: BILL DETAILS & EDITING (បញ្ជីមុខម្ហូប)   --}}
        {{-- ================================================= --}}
        <div class="flex flex-col h-[45%] md:h-full md:flex-[1.5] border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 relative">
            
            {{-- Header --}}
            <div class="p-4 md:p-5 border-b border-gray-100 dark:border-gray-700 shrink-0 flex justify-between items-center bg-white dark:bg-gray-800 z-10">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                            <i class="ri-file-list-3-fill"></i>
                        </span>
                        Order Details
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5 ml-10" x-text="orderDetails.invoice_number"></p>
                </div>
                {{-- Loading Indicator --}}
                <div x-show="isLoadingOrder" class="text-primary animate-spin">
                    <i class="ri-loader-4-line text-xl"></i>
                </div>
            </div>
            
            {{-- Item List (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-2 md:p-4 custom-scrollbar bg-gray-50 dark:bg-gray-900/50">
                <div class="space-y-3">
                    <template x-for="item in orderDetails.items" :key="'item-' + item.id">
                        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col gap-2">
                            
                            {{-- ======================== --}}
                            {{-- 1. MAIN ITEM (ម្ហូបគោល) --}}
                            {{-- ======================== --}}
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <div class="font-bold text-gray-800 dark:text-gray-200 text-base">
                                        <span x-text="item.product.name"></span>
                                    </div>
                                    <div class="font-bold text-gray-900 dark:text-white" x-text="'$' + (item.price * item.quantity).toFixed(2)"></div>
                                </div>

                                {{-- Controls របស់ម្ហូបគោល --}}
                                <div class="flex items-center justify-between">
                                    {{-- Trash Item --}}
                                    <button @click="updateItemQty(item.id, 'remove')" class="text-red-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </button>

                                    {{-- QTY Control Item --}}
                                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5">
                                        <button @click="updateItemQty(item.id, 'decrease')" 
                                                class="w-7 h-7 flex items-center justify-center rounded-md bg-white dark:bg-gray-600 text-gray-600 dark:text-white shadow-sm hover:text-red-500 active:scale-90 transition-all">
                                            <i class="ri-subtract-line font-bold"></i>
                                        </button>
                                        
                                        <span class="w-8 text-center font-bold text-gray-800 dark:text-white text-sm" x-text="item.quantity"></span>
                                        
                                        <button @click="updateItemQty(item.id, 'increase')" 
                                                class="w-7 h-7 flex items-center justify-center rounded-md bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm hover:bg-blue-50 active:scale-90 transition-all">
                                            <i class="ri-add-line font-bold"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- ======================== --}}
                            {{-- 2. ADDONS (ម្ហូបបន្ថែម)   --}}
                            {{-- ======================== --}}
                            <template x-if="item.addons && item.addons.length > 0">
                                <div class="mt-1 space-y-2 border-t border-dashed border-gray-200 dark:border-gray-700 pt-2">
                                    <template x-for="ad in item.addons" :key="'addon-' + ad.id">
                                        
                                        <div class="flex flex-col pl-4 border-l-2 border-blue-100 dark:border-blue-900/30">
                                            {{-- Addon Name & Price --}}
                                            <div class="flex justify-between items-center text-sm mb-1">
                                                <span class="text-gray-500 dark:text-gray-400 font-medium flex items-center gap-1">
                                                    <i class="ri-add-circle-line text-blue-500"></i>
                                                    <span x-text="ad.addon ? ad.addon.name : 'Unknown'"></span>
                                                </span>
                                                <span class="text-gray-600 dark:text-gray-300 font-semibold" 
                                                      x-text="'+ $' + (ad.price * ad.quantity).toFixed(2)"></span>
                                            </div>

                                            {{-- Addon Controls (Small) --}}
                                            <div class="flex items-center justify-end gap-3">
                                                {{-- Remove Addon --}}
                                                <button @click="updateAddonQty(ad.id, 'remove')" 
                                                        class="text-xs text-red-300 hover:text-red-500 p-1">
                                                    Remove
                                                </button>

                                                {{-- QTY Control Addon --}}
                                                <div class="flex items-center bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md p-0.5 scale-90 origin-right">
                                                    <button @click="updateAddonQty(ad.id, 'decrease')" 
                                                            class="w-5 h-5 flex items-center justify-center rounded bg-white dark:bg-gray-700 text-gray-500 shadow-sm hover:text-red-500">
                                                        <i class="ri-subtract-line text-xs"></i>
                                                    </button>
                                                    
                                                    <span class="w-6 text-center text-xs font-bold text-gray-700 dark:text-gray-300" x-text="ad.quantity"></span>
                                                    
                                                    <button @click="updateAddonQty(ad.id, 'increase')" 
                                                            class="w-5 h-5 flex items-center justify-center rounded bg-white dark:bg-gray-700 text-blue-500 shadow-sm hover:bg-blue-50">
                                                        <i class="ri-add-line text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </template>
                                </div>
                            </template>

                        </div>
                    </template>
                    
                    {{-- Empty State --}}
                    <template x-if="orderDetails.items.length === 0">
                        <div class="text-center py-10 text-gray-400">
                            <i class="ri-shopping-cart-line text-4xl mb-2 block"></i>
                            No items in order
                        </div>
                    </template>
                </div>
            </div>

            {{-- Subtotal Bar --}}
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-10">
                <div class="flex justify-between items-center text-sm md:text-base">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-bold text-gray-800 dark:text-white" x-text="'$' + parseFloat(orderDetails.total).toFixed(2)"></span>
                </div>
            </div>
        </div>

        {{-- ================================================= --}}
        {{-- RIGHT SIDE: PAYMENT (ផ្នែកគិតលុយ)                   --}}
        {{-- ================================================= --}}
        <div class="flex flex-col h-[55%] md:h-full md:flex-1 bg-white dark:bg-gray-800 z-20 shadow-[-5px_0_15px_rgba(0,0,0,0.05)]">
            
            {{-- SCROLLABLE CONTENT --}}
            <div class="flex-1 overflow-y-auto p-5 md:p-8 custom-scrollbar">
                
                {{-- 1. Total Display --}}
                <div class="text-center mb-6 mt-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Payable</span>
                    <div class="flex flex-col items-center gap-1 mt-2">
                        <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight" 
                            x-text="'$' + parseFloat(orderDetails.total || 0).toFixed(2)"></h1>
                        
                        {{-- Khmer Currency Display --}}
                        <div class="px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold text-lg md:text-xl border border-blue-100 dark:border-blue-800">
                            <span x-text="totalRiel"></span> ៛
                        </div>
                    </div>
                </div>

                {{-- 2. Payment Method --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    {{-- Cash --}}
                    <div @click="paymentMethod = 'cash'" 
                         class="group flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all duration-200 h-24"
                         :class="paymentMethod === 'cash' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50'">
                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-2">
                            <i class="ri-money-dollar-circle-fill text-xl"></i>
                        </div>
                        <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm">Cash</span>
                    </div>

                    {{-- KHQR --}}
                    <div @click="paymentMethod = 'qr'" 
                         class="group flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all duration-200 h-24"
                         :class="paymentMethod === 'qr' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50'">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-2">
                            <i class="ri-qr-code-line text-xl"></i>
                        </div>
                        <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm">KHQR</span>
                    </div>
                </div>

                {{-- 3. Received Amount --}}
                <div x-show="paymentMethod === 'cash'" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase ml-1 mb-1 block">Received Amount ($)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-lg">$</span>
                            <input type="number" x-model="receivedAmount" inputmode="decimal"
                                   class="w-full text-2xl font-bold border border-gray-200 dark:border-gray-600 rounded-xl pl-10 pr-4 py-3 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all bg-gray-50 dark:bg-gray-900/50 dark:text-white" 
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-4 rounded-xl bg-gray-100 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-500">Change Due</span>
                        <div class="text-right">
                            <div class="text-xl font-bold" 
                                 :class="(receivedAmount - orderDetails.total) < 0 ? 'text-red-500' : 'text-emerald-600'"
                                 x-text="'$' + Math.max(0, receivedAmount - orderDetails.total).toFixed(2)"></div>
                            <div class="text-xs text-gray-400"
                                 x-text="(Math.max(0, receivedAmount - orderDetails.total) * exchangeRate).toLocaleString('km-KH') + ' ៛'"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. FOOTER ACTIONS --}}
            <div class="p-4 md:p-5 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0 pb-6 md:pb-5">
                <div class="flex gap-3">
                    <button @click="isCheckoutModalOpen = false" 
                            class="flex-1 py-3.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white font-bold text-sm hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    
                    <button @click="processPayment()" 
                            :disabled="isProcessing || orderDetails.items.length === 0 || (paymentMethod === 'cash' && receivedAmount < orderDetails.total)"
                            class="flex-[2] py-3.5 rounded-xl bg-primary text-white font-bold text-sm shadow-lg shadow-primary/30 hover:bg-primary/90 active:scale-[0.98] transition-all disabled:opacity-50 disabled:shadow-none flex justify-center items-center gap-2">
                        <i x-show="isProcessing" class="ri-loader-4-line animate-spin text-lg"></i>
                        <span x-text="isProcessing ? 'Processing...' : 'CONFIRM & PRINT'"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>