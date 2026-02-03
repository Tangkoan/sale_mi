<div x-show="isCheckoutModalOpen" 
     class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-0 md:p-4" 
     style="display: none;" 
     x-cloak>
    
    {{-- BACKDROP --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300" 
         x-show="isCheckoutModalOpen" @click="isCheckoutModalOpen = false"></div>

    {{-- MODAL CONTAINER --}}
    <div class="relative w-full max-w-5xl bg-gray-100 dark:bg-gray-900 rounded-t-[20px] md:rounded-[24px] shadow-2xl overflow-hidden flex flex-col md:flex-row h-[95vh] md:h-[700px] transition-transform duration-300 transform"
         x-show="isCheckoutModalOpen"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0">

        {{-- =================================== --}}
        {{-- LEFT SIDE: ITEMS LIST               --}}
        {{-- =================================== --}}
        <div class="flex flex-col h-[45%] md:h-full md:flex-[1.5] border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 relative">
            
            {{-- HEADER: Title & Actions --}}
            <div class="p-4 md:p-5 border-b border-gray-100 dark:border-gray-700 shrink-0 bg-white dark:bg-gray-800 z-10">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="ri-file-list-3-fill"></i></span>
                        <span x-text="isSplitMode ? 'Select Items to Split' : 'Order Details'"></span>
                    </h3>
                    
                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        {{-- Merge Button --}}
                        <button @click="openMergeModal()" x-show="!isSplitMode" class="px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold hover:bg-purple-100 transition-colors border border-purple-100">
                            <i class="ri-git-merge-line mr-1"></i> Merge
                        </button>
                        
                        {{-- Split Button --}}
                        <button @click="toggleSplitMode()" 
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border"
                                :class="isSplitMode ? 'bg-red-50 text-red-600 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100 hover:bg-orange-100'">
                            <i class="ri-scissors-cut-line mr-1"></i> <span x-text="isSplitMode ? 'Cancel Split' : 'Split Bill'"></span>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-400 pl-10" x-text="orderDetails.invoice_number"></p>
            </div>
            
            {{-- ITEMS LIST --}}
            <div class="flex-1 overflow-y-auto p-2 md:p-4 custom-scrollbar bg-gray-50 dark:bg-gray-900/50 relative">
                
                {{-- Merge Selection Overlay --}}
                <div x-show="isMergeModalOpen" class="absolute inset-0 z-50 bg-white/95 dark:bg-gray-900/95 flex flex-col items-center justify-center p-6 backdrop-blur-sm" x-transition style="display: none;">
                    <h4 class="font-bold text-lg mb-4 text-gray-800 dark:text-white">ជ្រើសរើសតុដែលចង់បញ្ចូល (Merge)</h4>
                    <div class="grid grid-cols-3 gap-3 w-full max-w-md">
                        <template x-for="table in busyTables" :key="table.id">
                            <button @click="confirmMerge(table.id)" class="p-4 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 font-bold flex flex-col items-center gap-2 transition-all">
                                <i class="ri-table-alt-line text-2xl"></i>
                                <span x-text="table.name"></span>
                            </button>
                        </template>
                    </div>
                    <button @click="isMergeModalOpen = false" class="mt-6 text-gray-500 hover:text-gray-700 underline text-sm">Cancel</button>
                </div>

                <div class="space-y-3">
                    <template x-for="item in orderDetails.items" :key="'item-' + item.id">
                        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col gap-2 transition-all"
                             :class="isSplitMode && isItemSplitted(item.id) ? 'ring-2 ring-primary border-primary bg-blue-50/50' : ''">
                            
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start gap-3">
                                        {{-- CHECKBOX FOR SPLIT --}}
                                        <div x-show="isSplitMode" class="pt-1">
                                            <input type="checkbox" 
                                                   @change="toggleSplitItem(item)" 
                                                   :checked="isItemSplitted(item.id)"
                                                   class="w-5 h-5 rounded text-primary focus:ring-primary border-gray-300 cursor-pointer">
                                        </div>

                                        {{-- Item Name --}}
                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-base">
                                            <template x-if="isExtraItem(item) && item.addons.length > 0">
                                                <span x-text="item.addons[0].addon ? item.addons[0].addon.name : item.addons[0].name"></span>
                                            </template>
                                            <template x-if="isExtraItem(item) && item.addons.length == 0">
                                                <span>មុខម្ហូបបន្ថែម</span>
                                            </template>
                                            <template x-if="!isExtraItem(item)">
                                                <span x-text="item.product ? item.product.name : 'Unknown'"></span>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    {{-- Price --}}
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        <template x-if="isExtraItem(item) && item.addons.length > 0">
                                            <span x-text="'$' + (parseFloat(item.addons[0].price) * item.quantity).toFixed(2)"></span>
                                        </template>
                                        <template x-if="!isExtraItem(item)">
                                            <span x-text="'$' + (item.price * item.quantity).toFixed(2)"></span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Controls (Disable in Split Mode) --}}
                                <div class="flex items-center justify-between" :class="isSplitMode ? 'opacity-50 pointer-events-none' : ''">
                                    {{-- 🔥 LOCAL UPDATE ONLY --}}
                                    <button @click="updateItemQty(item.id, 'remove')" class="text-red-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg"><i class="ri-delete-bin-line text-lg"></i></button>
                                    
                                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5">
                                        <button @click="updateItemQty(item.id, 'decrease')" class="w-7 h-7 flex items-center justify-center rounded-md bg-white shadow-sm hover:text-red-500">-</button>
                                        <span class="w-8 text-center font-bold text-gray-800 text-sm" x-text="item.quantity"></span>
                                        <button @click="updateItemQty(item.id, 'increase')" class="w-7 h-7 flex items-center justify-center rounded-md bg-white shadow-sm hover:text-green-500">+</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Addons --}}
                            <template x-if="item.addons && item.addons.length > 0 && !isExtraItem(item)">
                                <div class="mt-1 space-y-2 border-t border-dashed border-gray-200 pt-2" :class="isSplitMode ? 'opacity-50 pointer-events-none' : ''">
                                    <template x-for="ad in item.addons" :key="'addon-' + ad.id">
                                        <div class="flex flex-col pl-4 border-l-2 border-blue-100">
                                            <div class="flex justify-between items-center text-sm mb-1">
                                                <span class="text-gray-500 font-medium flex items-center gap-1">
                                                    <i class="ri-add-circle-line text-blue-500"></i>
                                                    <span x-text="ad.addon ? ad.addon.name : 'Unknown'"></span>
                                                </span>
                                                <span class="text-gray-600 font-semibold" x-text="'+ $' + (ad.price * (ad.quantity || 1)).toFixed(2)"></span>
                                            </div>
                                            {{-- Addon Controls (LOCAL) --}}
                                            <div class="flex items-center justify-end gap-3">
                                                <button @click="updateAddonQty(ad.id, 'remove')" class="text-xs text-red-300 p-1 hover:text-red-500">Remove</button>
                                                <div class="flex items-center bg-gray-50 border rounded-md p-0.5 scale-90 origin-right">
                                                    <button @click="updateAddonQty(ad.id, 'decrease')" class="w-5 h-5 flex items-center justify-center rounded bg-white shadow-sm hover:text-red-500">-</button>
                                                    <span class="w-6 text-center text-xs font-bold" x-text="ad.quantity"></span>
                                                    <button @click="updateAddonQty(ad.id, 'increase')" class="w-5 h-5 flex items-center justify-center rounded bg-white shadow-sm hover:text-green-500">+</button>
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
                            <i class="ri-shopping-cart-line text-4xl mb-2 block"></i> No items in order
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 bg-white dark:bg-gray-800 shrink-0 shadow-sm z-10">
                <div class="flex justify-between items-center text-sm md:text-base">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-bold text-gray-800 dark:text-white" x-text="'$' + parseFloat(orderDetails.total).toFixed(2)"></span>
                </div>
            </div>
        </div>

        {{-- =================================== --}}
        {{-- RIGHT SIDE: PAYMENT                 --}}
        {{-- =================================== --}}
        <div class="flex flex-col h-[55%] md:h-full md:flex-1 bg-white dark:bg-gray-800 z-20 shadow-[-5px_0_15px_rgba(0,0,0,0.05)]">
            <div class="flex-1 overflow-y-auto p-5 md:p-8 custom-scrollbar">
                
                {{-- Total Display --}}
                <div class="text-center mb-6 mt-1">
                    <span class="text-xs font-bold uppercase tracking-widest" :class="isSplitMode ? 'text-orange-500' : 'text-gray-400'">
                        <span x-text="isSplitMode ? 'SPLIT PAYABLE' : 'TOTAL PAYABLE'"></span>
                    </span>
                    <div class="flex flex-col items-center gap-1 mt-2">
                        <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight" 
                            x-text="'$' + currentTotalUSD.toFixed(2)"></h1>
                        <div class="px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 font-bold text-lg md:text-xl border border-blue-100">
                            <span x-text="totalRiel"></span> ៛
                        </div>
                    </div>
                </div>

                {{-- Payment Methods --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div @click="paymentMethod = 'cash'" class="group flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer h-24" :class="paymentMethod === 'cash' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 hover:bg-gray-50'">
                        <i class="ri-money-dollar-circle-fill text-xl text-green-600 mb-2"></i>
                        <span class="font-semibold text-gray-700 text-sm">Cash</span>
                    </div>
                    <div @click="paymentMethod = 'qr'" class="group flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer h-24" :class="paymentMethod === 'qr' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-gray-200 hover:bg-gray-50'">
                        <i class="ri-qr-code-line text-xl text-blue-600 mb-2"></i>
                        <span class="font-semibold text-gray-700 text-sm">KHQR</span>
                    </div>
                </div>

                {{-- Cash Input --}}
                <div x-show="paymentMethod === 'cash'" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase ml-1 mb-1 block">Received Amount ($)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-lg">$</span>
                            <input type="number" x-model="receivedAmount" class="w-full text-2xl font-bold border border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:border-primary outline-none" placeholder="0.00">
                        </div>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-xl bg-gray-100 border border-gray-200">
                        <span class="text-sm font-medium text-gray-500">Change Due</span>
                        <div class="text-right">
                            <div class="text-xl font-bold" :class="(receivedAmount - currentTotalUSD) < 0 ? 'text-red-500' : 'text-emerald-600'" x-text="'$' + Math.max(0, receivedAmount - currentTotalUSD).toFixed(2)"></div>
                            <div class="text-xs text-gray-400" x-text="(Math.max(0, receivedAmount - currentTotalUSD) * exchangeRate).toLocaleString('km-KH') + ' ៛'"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="p-4 md:p-5 border-t border-gray-100 bg-white dark:bg-gray-800 shrink-0 pb-6 md:pb-5">
                <div class="flex gap-3">
                    <button @click="isCheckoutModalOpen = false" class="flex-1 py-3.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-50">Cancel</button>
                    
                    {{-- 🔥 Main Action Button --}}
                    <button @click="confirmPayment()" 
                            :disabled="isProcessing || (orderDetails.items.length === 0 && !confirmEmpty) || (paymentMethod === 'cash' && receivedAmount < currentTotalUSD)" 
                            class="flex-[2] py-3.5 rounded-xl text-white font-bold text-sm shadow-lg disabled:opacity-50 flex justify-center items-center gap-2"
                            :class="isSplitMode ? 'bg-orange-500 hover:bg-orange-600' : 'bg-primary hover:bg-primary/90'">
                        <i x-show="isProcessing" class="ri-loader-4-line animate-spin text-lg"></i>
                        <span x-text="isProcessing ? 'Processing...' : (isSplitMode ? 'PAY SPLIT BILL' : 'CONFIRM & PRINT')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>