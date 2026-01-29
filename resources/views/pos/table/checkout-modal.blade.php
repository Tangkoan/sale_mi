<div x-show="isCheckoutModalOpen" class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-0 md:p-4" style="display: none;" x-cloak>
    
    {{-- 1. BACKDROP --}}
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity duration-300" 
         x-show="isCheckoutModalOpen"
         x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="isCheckoutModalOpen = false"></div>

    {{-- 2. MODAL CONTAINER --}}
    <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-t-[28px] md:rounded-[24px] shadow-2xl overflow-hidden flex flex-col h-[90vh] md:h-[650px] transition-transform duration-300"
         x-show="isCheckoutModalOpen"
         x-transition:enter="transform cubic-bezier(0.16, 1, 0.3, 1)"
         x-transition:enter-start="translate-y-full scale-95 opacity-0"
         x-transition:enter-end="translate-y-0 scale-100 opacity-100"
         x-transition:leave="transform cubic-bezier(0.16, 1, 0.3, 1)"
         x-transition:leave-start="translate-y-0 scale-100 opacity-100"
         x-transition:leave-end="translate-y-full scale-95 opacity-0">

        {{-- Mobile Drag Handle --}}
        <div class="w-full flex justify-center pt-3 pb-1 md:hidden bg-white dark:bg-gray-800 shrink-0 cursor-grab" @click="isCheckoutModalOpen = false">
            <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
        </div>

        <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
            
            {{-- ================================================= --}}
            {{-- LEFT SIDE: BILL DETAILS (Hidden on small mobile) --}}
            {{-- ================================================= --}}
            <div class="hidden md:flex flex-1 flex-col border-r border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 shrink-0">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                            <i class="ri-file-list-3-fill"></i>
                        </span>
                        Order Details
                    </h3>
                    <p class="text-xs text-gray-400 mt-1 ml-10" x-text="orderDetails.invoice_number"></p>
                </div>
                
                <div class="flex-1 overflow-y-auto p-5 custom-scrollbar">
                    <div class="space-y-3">
                        <template x-for="item in orderDetails.items" :key="item.id">
                            <div class="flex justify-between items-start text-sm">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-200">
                                        <span class="text-gray-400 mr-1" x-text="item.quantity + 'x'"></span>
                                        <span x-text="item.product.name"></span>
                                    </div>
                                    {{-- Addons --}}
                                    <template x-if="item.addons && item.addons.length > 0">
                                        <div class="text-xs text-gray-400 pl-5 mt-0.5">
                                            <template x-for="ad in item.addons">
                                                <div x-text="'+ ' + (ad.addon ? ad.addon.name : '')"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="font-semibold text-gray-900 dark:text-white" x-text="'$' + (item.price * item.quantity).toFixed(2)"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="'$' + parseFloat(orderDetails.total).toFixed(2)"></span>
                    </div>
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- RIGHT SIDE: PAYMENT (Main View)                   --}}
            {{-- ================================================= --}}
            <div class="flex-1 flex flex-col w-full md:w-[400px] bg-white dark:bg-gray-800 relative z-10 h-full">
                
                {{-- SCROLLABLE CONTENT --}}
                <div class="flex-1 overflow-y-auto p-5 md:p-8 custom-scrollbar">
                    
                    {{-- 1. Total Display --}}
                    <div class="text-center mb-8 mt-2">
                        <span class="text-sm font-medium text-gray-400 uppercase tracking-wide">Total Amount</span>
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mt-1 tracking-tight" 
                            x-text="'$' + parseFloat(orderDetails.total || 0).toFixed(2)"></h1>
                    </div>

                    {{-- 2. Payment Method Selection --}}
                    <div class="space-y-3 mb-8">
                        <label class="text-xs font-bold text-gray-400 uppercase ml-1">Payment Method</label>
                        
                        {{-- Cash --}}
                        <div @click="paymentMethod = 'cash'" 
                             class="group flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition-all duration-200"
                             :class="paymentMethod === 'cash' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                    <i class="ri-money-dollar-circle-fill text-xl"></i>
                                </div>
                                <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm">Cash Payment</span>
                            </div>
                            <div class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center"
                                 :class="paymentMethod === 'cash' ? 'border-primary bg-primary' : ''">
                                <i class="ri-check-line text-white text-xs" x-show="paymentMethod === 'cash'"></i>
                            </div>
                        </div>

                        {{-- KHQR --}}
                        <div @click="paymentMethod = 'qr'" 
                             class="group flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition-all duration-200"
                             :class="paymentMethod === 'qr' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <i class="ri-qr-code-line text-xl"></i>
                                </div>
                                <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm">KHQR Scan</span>
                            </div>
                            <div class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center"
                                 :class="paymentMethod === 'qr' ? 'border-primary bg-primary' : ''">
                                <i class="ri-check-line text-white text-xs" x-show="paymentMethod === 'qr'"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Received Amount Input --}}
                    <div x-show="paymentMethod === 'cash'" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 -translate-y-2" 
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <label class="text-xs font-bold text-gray-400 uppercase ml-1 mb-2 block">Received Amount</label>
                        
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                            <input type="number" x-model="receivedAmount" inputmode="decimal"
                                   class="w-full text-2xl font-bold border border-gray-200 dark:border-gray-600 rounded-xl pl-8 pr-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white dark:bg-gray-800 dark:text-white shadow-sm" 
                                   placeholder="0.00">
                        </div>

                        {{-- Change Display --}}
                        <div class="flex justify-between items-center mt-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-500">Change Due</span>
                            <span class="text-xl font-bold text-gray-800 dark:text-white" 
                                  :class="(receivedAmount - orderDetails.total) < 0 ? 'text-red-500' : 'text-emerald-600'"
                                  x-text="'$' + Math.max(0, receivedAmount - orderDetails.total).toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                {{-- 4. FOOTER BUTTONS (FIXED BOTTOM) --}}
                <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0 z-20 pb-6 md:pb-5">
                    <div class="flex gap-3">
                        <button @click="isCheckoutModalOpen = false" 
                                class="flex-1 py-3.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        
                        <button @click="processPayment()" 
                                :disabled="isProcessing || (paymentMethod === 'cash' && receivedAmount < orderDetails.total)"
                                class="flex-[2] py-3.5 rounded-xl bg-primary text-white font-semibold text-sm shadow-lg shadow-primary/30 hover:bg-primary/90 active:scale-[0.98] transition-all disabled:opacity-50 disabled:shadow-none flex justify-center items-center gap-2">
                            <i x-show="isProcessing" class="ri-loader-4-line animate-spin"></i>
                            <span x-text="isProcessing ? 'Processing...' : 'Print & Pay'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>