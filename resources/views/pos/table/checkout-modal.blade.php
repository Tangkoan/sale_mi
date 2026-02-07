<div x-show="isCheckoutModalOpen" 
     class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-0 md:p-4" 
     style="display: none;" 
     x-cloak>
    
    {{-- BACKDROP (Background ខ្មៅស្រាលៗ) --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300" 
         x-show="isCheckoutModalOpen" @click="isCheckoutModalOpen = false"></div>

    {{-- ================================================= --}}
    {{-- MAIN CHECKOUT MODAL (ផ្ទាំងគិតលុយ) --}}
    {{-- ================================================= --}}
    <div class="relative w-full max-w-5xl bg-gray-100 dark:bg-gray-900 rounded-t-[20px] md:rounded-[24px] shadow-2xl overflow-hidden flex flex-col md:flex-row h-[95vh] md:h-[700px] transition-transform duration-300 transform"
         x-show="isCheckoutModalOpen"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="translate-y-full opacity-0" 
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="translate-y-0 opacity-100" 
         x-transition:leave-end="translate-y-full opacity-0">

        {{-- ... (LEFT SIDE: ITEMS LIST - រក្សាទុកដូចដើម) ... --}}
        <div class="flex flex-col h-[45%] md:h-full md:flex-[1.5] border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 relative">
            
            {{-- HEADER --}}
            <div class="p-4 md:p-5 border-b border-gray-100 dark:border-gray-700 shrink-0 bg-white dark:bg-gray-800 z-10">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="ri-file-list-3-fill"></i></span>
                        <span x-text="isSplitMode ? 'Select Items to Split' : 'Order Details'"></span>
                    </h3>
                    
                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        {{-- 🔥 MOVE BUTTON --}}
                        <button @click="openMoveModal()" x-show="!isSplitMode" 
                                class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 hover:scale-105 transition-all border border-indigo-100 flex items-center gap-1">
                            <i class="ri-share-forward-line"></i> Move
                        </button>

                        {{-- MERGE BUTTON --}}
                        <button @click="openMergeModal()" x-show="!isSplitMode" 
                                class="px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold hover:bg-purple-100 hover:scale-105 transition-all border border-purple-100 flex items-center gap-1">
                            <i class="ri-git-merge-line"></i> Merge
                        </button>
                        
                        {{-- SPLIT BUTTON --}}
                        <button @click="toggleSplitMode()" 
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border flex items-center gap-1"
                                :class="isSplitMode ? 'bg-red-50 text-red-600 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100 hover:bg-orange-100 hover:scale-105'">
                            <i class="ri-scissors-cut-line"></i> <span x-text="isSplitMode ? 'Cancel' : 'Split'"></span>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-400 pl-10" x-text="orderDetails.invoice_number"></p>
            </div>
            
            {{-- ITEMS LIST Container --}}
            <div class="flex-1 overflow-y-auto p-2 md:p-4 custom-scrollbar bg-gray-50 dark:bg-gray-900/50 relative">
                {{-- ... (Content ក្នុង List ម្ហូប រក្សាទុកដូចដើម) ... --}}
                <div class="space-y-3">
                    <template x-for="item in orderDetails.items" :key="'item-' + item.id">
                         {{-- ... (Item Card Code ដូចចាស់) ... --}}
                         <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col gap-2 transition-all"
                             :class="isSplitMode && isItemSplitted(item.id) ? 'ring-2 ring-primary border-primary bg-blue-50/50' : ''">
                            {{-- ... (ដាក់កូដ Item Card ចាស់នៅទីនេះ) ... --}}
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start gap-3">
                                        <div x-show="isSplitMode" class="pt-1">
                                            <input type="checkbox" @change="toggleSplitItem(item)" :checked="isItemSplitted(item.id)" class="w-5 h-5 rounded text-primary focus:ring-primary border-gray-300 cursor-pointer">
                                        </div>
                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-base">
                                            <span x-text="item.product ? item.product.name : 'Item'"></span>
                                        </div>
                                    </div>
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        <span x-text="'$' + (item.price * item.quantity).toFixed(2)"></span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between" :class="isSplitMode ? 'opacity-50 pointer-events-none' : ''">
                                    <button @click="updateItemQty(item.id, 'remove')" class="text-red-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg"><i class="ri-delete-bin-line text-lg"></i></button>
                                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5">
                                        <button @click="updateItemQty(item.id, 'decrease')" class="w-7 h-7 flex items-center justify-center rounded-md bg-white shadow-sm hover:text-red-500">-</button>
                                        <span class="w-8 text-center font-bold text-gray-800 text-sm" x-text="item.quantity"></span>
                                        <button @click="updateItemQty(item.id, 'increase')" class="w-7 h-7 flex items-center justify-center rounded-md bg-white shadow-sm hover:text-green-500">+</button>
                                    </div>
                                </div>
                            </div>
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

        {{-- ... (RIGHT SIDE: PAYMENT - រក្សាទុកដូចដើម) ... --}}
        <div class="flex flex-col h-[55%] md:h-full md:flex-1 bg-white dark:bg-gray-800 z-20 shadow-[-5px_0_15px_rgba(0,0,0,0.05)]">
            {{-- ... (Payment Content Code ដូចចាស់) ... --}}
             <div class="flex-1 overflow-y-auto p-5 md:p-8 custom-scrollbar">
                <div class="text-center mb-6 mt-1">
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-400">TOTAL PAYABLE</span>
                    <div class="flex flex-col items-center gap-1 mt-2">
                        <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight" x-text="'$' + currentTotalUSD.toFixed(2)"></h1>
                        <div class="px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 font-bold text-lg md:text-xl border border-blue-100">
                            <span x-text="totalRiel"></span> ៛
                        </div>
                    </div>
                </div>
                {{-- Payment Methods & Input --}}
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
                <div x-show="paymentMethod === 'cash'" class="space-y-4">
                     <div>
                        <label class="text-xs font-bold text-gray-400 uppercase ml-1 mb-1 block">Received Amount ($)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-lg">$</span>
                            <input type="number" x-model="receivedAmount" class="w-full text-2xl font-bold border border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:border-primary outline-none" placeholder="0.00">
                        </div>
                    </div>
                </div>
             </div>
             {{-- Footer Buttons --}}
             <div class="p-4 md:p-5 border-t border-gray-100 bg-white dark:bg-gray-800 shrink-0 pb-6 md:pb-5">
                <div class="flex gap-3">
                    <button @click="isCheckoutModalOpen = false" class="flex-1 py-3.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-50">Cancel</button>
                    <button @click="confirmPayment()" 
                            :disabled="isProcessing" 
                            class="flex-[2] py-3.5 rounded-xl text-white font-bold text-sm shadow-lg flex justify-center items-center gap-2 bg-primary hover:bg-primary/90">
                        <span x-text="isProcessing ? 'Processing...' : 'CONFIRM & PRINT'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- 🔥 NEW: MOVE TABLE MODAL (Overlay ដាច់ដោយឡែក)  --}}
    {{-- ================================================= --}}
    <div x-show="isMoveModalOpen" 
         style="display: none;"
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">

        <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all scale-100"
             @click.away="isMoveModalOpen = false">
            
            {{-- Header --}}
            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 border-b border-indigo-100 dark:border-indigo-800 text-center">
                <div class="w-16 h-16 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-indigo-50">
                    <i class="ri-share-forward-line text-3xl text-indigo-500"></i>
                </div>
                <h3 class="text-xl font-black text-gray-800 dark:text-white">ប្ដូរតុ (Move Table)</h3>
                <div class="flex items-center justify-center gap-2 mt-2 text-sm text-gray-500">
                    <span>បច្ចុប្បន្ន:</span> 
                    <span class="font-bold text-gray-800 bg-gray-200 px-2 py-0.5 rounded" x-text="selectedTable ? selectedTable.name : ''"></span>
                    <i class="ri-arrow-right-line text-gray-400"></i>
                    <span class="text-indigo-600 font-bold">ជ្រើសរើសតុថ្មី</span>
                </div>
            </div>

            {{-- Table List (Grid) --}}
            <div class="p-6 bg-white dark:bg-gray-800 max-h-[400px] overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    <template x-for="table in availableTables" :key="table.id">
                        <button @click="confirmMove(table.id)" 
                                class="group relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 border-dashed border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-200">
                            
                            {{-- Icon --}}
                            <div class="w-10 h-10 rounded-full bg-gray-50 group-hover:bg-white flex items-center justify-center mb-2 transition-colors">
                                <i class="ri-restaurant-line text-xl text-gray-400 group-hover:text-indigo-500"></i>
                            </div>
                            
                            {{-- Name --}}
                            <span class="font-bold text-gray-700 group-hover:text-indigo-700" x-text="table.name"></span>
                            
                            {{-- Available Tag --}}
                            <span class="absolute top-2 right-2 w-2 h-2 bg-emerald-400 rounded-full ring-2 ring-white"></span>
                        </button>
                    </template>
                </div>

                {{-- Empty State --}}
                <template x-if="availableTables.length === 0">
                    <div class="flex flex-col items-center justify-center py-8 text-center text-gray-400">
                        <i class="ri-store-3-line text-4xl mb-2 opacity-50"></i>
                        <p>មិនមានតុទំនេរសម្រាប់ប្ដូរទេ</p>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-center">
                <button @click="isMoveModalOpen = false" class="text-gray-500 hover:text-gray-800 font-bold text-sm px-6 py-2 rounded-lg hover:bg-gray-200/50 transition-colors">
                    បោះបង់ (Cancel)
                </button>
            </div>
        </div>
    </div>

</div>