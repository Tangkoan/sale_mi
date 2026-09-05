<div x-show="isCheckoutModalOpen" 
     class="fixed inset-0 z-[60] flex items-end md:items-center justify-center sm:p-4" 
     style="display: none;" 
     x-cloak>
    
    {{-- BACKDROP --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300" 
         x-show="isCheckoutModalOpen" 
         x-transition.opacity 
         @click="isCheckoutModalOpen = false"></div>

    {{-- MAIN CHECKOUT MODAL (Compact Mobile Style) --}}
    <div class="relative w-full max-w-5xl bg-[#F4F6F8] dark:bg-gray-900 rounded-t-[20px] md:rounded-[20px] shadow-2xl flex flex-col md:flex-row overflow-hidden transition-transform duration-300 transform h-[90vh] md:h-[85vh]"
         x-show="isCheckoutModalOpen"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="translate-y-full sm:translate-y-4 opacity-0" 
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="translate-y-0 opacity-100" 
         x-transition:leave-end="translate-y-full sm:translate-y-4 opacity-0">

        {{-- ================================================== --}}
        {{-- LEFT SIDE: ITEMS LIST (Scrollable Area) --}}
        {{-- ================================================== --}}
        <div class="flex-1 flex flex-col overflow-hidden bg-[#F4F6F8] dark:bg-gray-900 z-10">
            
            {{-- Handle Bar (Mobile) --}}
            <div class="w-full flex justify-center pt-2 pb-1 md:hidden bg-white dark:bg-gray-800" @click="isCheckoutModalOpen = false">
                <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>

            {{-- HEADER SECTION (តូច និងបង្រួម) --}}
            <div class="px-3 py-2 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shrink-0 flex flex-col gap-2 shadow-sm">
                
                {{-- Title & Invoice --}}
                <div class="flex justify-between items-center">
                    <h2 class="text-[14px] font-bold text-gray-900 dark:text-white" x-text="isSplitMode ? 'ជ្រើសរើសម្ហូបបំបែក' : 'តុ ' + (selectedTable ? selectedTable.name : '')"></h2>
                    <span class="text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-1 rounded" x-text="'#' + orderDetails.invoice_number"></span>
                </div>

                {{-- Quick Actions (Move, Merge, Split) - ទំហំតូច --}}
                <div class="grid grid-cols-3 gap-2">
                    @can('pos-move-table')
                        <button @click="openMoveModal()" x-show="!isSplitMode" 
                                class="py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 flex flex-col items-center justify-center gap-0.5 active:scale-95 transition-all">
                            <i class="ri-share-forward-fill text-[14px] text-blue-500"></i>
                            <span class="text-[10px] font-medium text-gray-600 dark:text-gray-300">{{ __('messages.move') }}</span>
                        </button>
                    @endcan
                    
                    @can('pos-merge-table')
                        <button @click="openMergeModal()" x-show="!isSplitMode" 
                                class="py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 flex flex-col items-center justify-center gap-0.5 active:scale-95 transition-all">
                            <i class="ri-git-merge-fill text-[14px] text-purple-500"></i>
                            <span class="text-[10px] font-medium text-gray-600 dark:text-gray-300">{{ __('messages.merge') }}</span>
                        </button>
                    @endcan
                    
                    @can('pos-split-bill')
                        <button @click="toggleSplitMode()" 
                                class="py-1.5 rounded-lg border flex flex-col items-center justify-center gap-0.5 active:scale-95 transition-all"
                                :class="isSplitMode ? 'bg-red-50 border-red-200 text-red-600' : 'bg-gray-50 dark:bg-gray-700 border-gray-100 dark:border-gray-600 text-gray-600 dark:text-gray-300'">
                            <i class="ri-scissors-cut-fill text-[14px]" :class="isSplitMode ? 'text-red-500' : 'text-orange-500'"></i> 
                            <span class="text-[10px] font-medium" x-text="isSplitMode ? 'បោះបង់' : 'បំបែក'"></span>
                        </button>
                    @endcan
                </div>
            </div>
            
            {{-- ITEMS LIST Container (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-2 custom-scrollbar relative">
                <div class="space-y-2">
                    <template x-for="item in orderDetails.items" :key="'item-' + item.id">
                        
                        {{-- Clean Item Card (តូចល្មម) --}}
                        <div class="bg-white dark:bg-gray-800 p-2.5 rounded-[12px] shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col gap-1 transition-all relative overflow-hidden"
                             :class="isSplitMode && isItemSplitted(item.id) ? 'ring-1 ring-primary border-primary bg-primary/5' : ''">
                            
                            <div x-show="isSplitMode" @click="toggleSplitItem(item)" class="absolute inset-0 z-10 cursor-pointer"></div>

                            <div class="flex justify-between items-start gap-2 relative z-0">
                                <div class="flex items-start gap-2 flex-1 min-w-0">
                                    <div x-show="isSplitMode" class="pt-0.5 shrink-0">
                                        <div class="w-4 h-4 rounded-sm border flex items-center justify-center transition-colors"
                                             :class="isItemSplitted(item.id) ? 'bg-primary border-primary' : 'bg-white border-gray-300'">
                                            <i class="ri-check-line text-white text-[10px]" x-show="isItemSplitted(item.id)"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-[13px] text-gray-900 dark:text-white leading-tight" x-text="item.product ? item.product.name : 'មុខម្ហូប'"></div>
                                        
                                        {{-- Addons List --}}
                                        <template x-if="item.addons && item.addons.length > 0">
                                            <div class="mt-1 space-y-1">
                                                <template x-for="(ad, index) in item.addons" :key="index">
                                                    <div class="flex items-center justify-between text-[11px] text-gray-500 bg-gray-50 dark:bg-gray-700/30 p-1 rounded-md">
                                                        <div class="flex items-center gap-1 overflow-hidden">
                                                            <span class="truncate" x-text="ad.addon?.name || ad.name"></span>
                                                            <span x-show="parseFloat(ad.price) > 0" class="shrink-0" x-text="'(+' + formatRiel(ad.price) + '៛)'"></span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 shrink-0" :class="isSplitMode ? 'hidden' : ''">
                                                            <div class="flex items-center bg-white dark:bg-gray-600 rounded border border-gray-200 dark:border-gray-500 h-5">
                                                                <button @click="updateAddonQty(item.id, ad.id, 'decrease')" class="w-5 h-full hover:text-red-500 flex items-center justify-center">-</button>
                                                                <span class="w-4 text-center font-bold text-[10px] text-gray-800 dark:text-white" x-text="ad.quantity || 1"></span>
                                                                <button @click="updateAddonQty(item.id, ad.id, 'increase')" class="w-5 h-full hover:text-primary flex items-center justify-center">+</button>
                                                            </div>
                                                            <button @click="updateAddonQty(item.id, ad.id, 'remove')" class="text-red-400 hover:text-red-600"><i class="ri-close-line text-[14px]"></i></button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                
                                {{-- Line Item Price --}}
                                <div class="font-bold text-gray-900 dark:text-white text-[13px] shrink-0 text-right">
                                    <span x-text="formatRiel((parseFloat(item.price) + (item.addons ? item.addons.reduce((sum, ad) => sum + (parseFloat(ad.price) * (ad.quantity || 1)), 0) : 0)) * item.quantity) + ' ៛'"></span>
                                </div>
                            </div>
                            
                            {{-- Item Qty Control & Delete --}}
                            <div class="flex items-center justify-between mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700 relative z-0" :class="isSplitMode ? 'opacity-50 pointer-events-none' : ''">
                                <button @click="updateItemQty(item.id, 'remove')" class="flex items-center justify-center w-7 h-7 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                    <i class="ri-delete-bin-line text-[14px]"></i>
                                </button>
                                
                                <div class="flex items-center bg-gray-50 dark:bg-gray-700 rounded-full border border-gray-200 dark:border-gray-600 p-0.5">
                                    <button @click="updateItemQty(item.id, 'decrease')" class="w-6 h-6 flex items-center justify-center rounded-full bg-white dark:bg-gray-600 shadow-sm text-gray-600 dark:text-gray-300 active:scale-95 transition-all"><i class="ri-subtract-line text-[12px]"></i></button>
                                    <span class="w-7 text-center font-bold text-gray-900 dark:text-white text-[12px]" x-text="item.quantity"></span>
                                    <button @click="updateItemQty(item.id, 'increase')" class="w-6 h-6 flex items-center justify-center rounded-full bg-white dark:bg-gray-600 shadow-sm text-gray-600 dark:text-gray-300 active:scale-95 transition-all"><i class="ri-add-line text-[12px]"></i></button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ================================================== --}}
        {{-- RIGHT SIDE / BOTTOM: PAYMENT SUMMARY (តូច បង្រួម) --}}
        {{-- ================================================== --}}
        <div class="w-full md:w-[320px] shrink-0 bg-white dark:bg-gray-800 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-700 flex flex-col z-20">
            
            <div class="p-3 sm:p-4 flex-1 overflow-y-auto hide-scroll-bar">
    
            {{-- លាក់ Payment Method ដោយទុកឲ្យវាដើរជា 'cash' Auto តាម State របស់ Alpine --}}
            <input type="hidden" x-model="paymentMethod" value="cash">

            {{-- Total Payable Amount Block (1 Row Design) --}}
            <div class="bg-[#F8F9FA] dark:bg-gray-900 rounded-[12px] p-3.5 border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <span class="text-[13px] font-bold text-gray-600 dark:text-gray-400">ទឹកប្រាក់ត្រូវទូទាត់សរុប៖</span>
                <h1 class="text-[20px] font-black text-primary tracking-tight" x-text="formatRiel(currentTotal) + ' ៛'"></h1>
            </div>
            
        </div>

             {{-- Sticky Bottom Action Buttons (ប៊ូតុងខ្លីល្មម) --}}
             <div class="p-3 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0 pb-4 sm:pb-3">
                <div class="flex gap-2.5">
                    <button @click="isCheckoutModalOpen = false" 
                            class="w-[35%] h-11 rounded-[12px] border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold text-[13px] hover:bg-gray-50 dark:hover:bg-gray-700 active:scale-95 transition-all">
                        {{ __('messages.cancel') }}
                    </button>
                    <button @click="confirmPayment()" :disabled="isProcessing" 
                            class="flex-1 h-11 rounded-[12px] text-white font-bold text-[13px] shadow-md shadow-gray-900/10 flex justify-center items-center gap-2 active:scale-95 transition-all"
                            :class="isProcessing ? 'bg-gray-400 cursor-not-allowed' : 'bg-gray-900 dark:bg-gray-100 dark:text-gray-900'">
                        <i class="ri-printer-line text-[16px]" x-show="!isProcessing"></i>
                        <span x-text="isProcessing ? 'កំពុងដំណើរការ...' : 'បញ្ជាក់ និងបោះពុម្ព'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    
    
    {{-- MERGE TABLE MODAL --}}
    <div x-show="isMergeModalOpen" 
        style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm"
        x-cloak
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 scale-95" 
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 scale-100" 
        x-transition:leave-end="opacity-0 scale-95">

        <div class="bg-card-bg w-full max-w-md rounded-[24px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.away="isMergeModalOpen = false">
            <div class="bg-primary/5 p-6 text-center border-b border-primary/10">
                <h3 class="text-lg font-black text-gray-800 dark:text-white mb-4">{{ __('messages.merge') }}</h3>
                
                <div class="flex items-center justify-between gap-2 bg-card-bg p-3 rounded-xl shadow-sm border border-primary/10">
                    <div class="flex flex-col items-center w-1/3">
                        <span class="text-[10px] text-gray-400 font-bold uppercase mb-1">{{ __('messages.now') }}</span>
                        <div class="font-black text-gray-800 dark:text-white text-lg px-3 py-1 bg-input-bg rounded-lg w-full text-center border border-bor-color">
                            <span x-text="selectedTable ? selectedTable.name : '...'"></span>
                        </div>
                    </div>
                    
                    <div class="text-primary"><i class="ri-add-circle-fill text-2xl"></i></div>
                    
                    <div class="flex flex-col items-center w-1/3">
                        <span class="text-[10px] text-primary font-bold uppercase mb-1">{{ __('messages.merge_from') }}</span>
                        <div class="font-black text-lg px-2 py-1 rounded-lg w-full text-center transition-all border-2 border-dashed flex items-center justify-center min-h-[40px]"
                            :class="selectedMergeTables.length > 0 ? 'bg-primary text-white border-primary shadow-lg shadow-primary/30' : 'bg-card-bg text-gray-400 border-bor-color'">
                            <span class="text-sm leading-tight" 
                                  x-text="selectedMergeTables.length > 0 ? selectedMergeTables.map(t => t.name).join(', ') : '?'">
                            </span>
                        </div>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 mt-3" x-show="selectedMergeTables.length === 0">{{ __('messages.select_merge_source') }}</p>
                <p class="text-xs text-primary font-bold mt-3" x-show="selectedMergeTables.length > 0">
                    <span x-text="selectedMergeTables.length"></span> តុត្រូវបានជ្រើសរើស
                </p>
            </div>

            <div class="p-4 overflow-y-auto custom-scrollbar flex-1 bg-card-bg">
                <div class="grid grid-cols-3 gap-3">
                    <template x-for="table in busyTables" :key="table.id">
                        <button @click="toggleMergeTable(table)" 
                                class="relative flex flex-col items-center justify-center p-3 rounded-2xl border-2 transition-all duration-200 group"
                                :class="isTableSelectedForMerge(table.id) 
                                    ? 'border-primary bg-primary/5 ring-2 ring-primary/30' 
                                    : 'border-bor-color bg-card-bg hover:border-primary/50 hover:bg-primary/5'">
                            
                            <div x-show="isTableSelectedForMerge(table.id)" class="absolute top-2 right-2 text-primary bg-card-bg rounded-full shadow-sm">
                                <i class="ri-checkbox-circle-fill text-lg"></i>
                            </div>
                            
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-1 transition-colors" 
                                 :class="isTableSelectedForMerge(table.id) ? 'bg-card-bg text-primary' : 'bg-input-bg text-gray-400'">
                                 <i class="ri-restaurant-fill text-lg"></i>
                            </div>
                            
                            <span class="font-bold text-sm" 
                                  :class="isTableSelectedForMerge(table.id) ? 'text-primary' : 'text-gray-600 dark:text-gray-300'" 
                                  x-text="table.name"></span>
                        </button>
                    </template>
                </div>

                <template x-if="busyTables.length === 0">
                    <div class="flex flex-col items-center justify-center py-10 text-center text-gray-400">
                        <i class="ri-emotion-sad-line text-4xl mb-2 opacity-50"></i>
                        <p class="text-sm">{{ __('messages.no_busy_tables') }}</p>
                    </div>
                </template>
            </div>

            <div class="p-4 border-t border-bor-color bg-input-bg flex gap-3">
                <button @click="isMergeModalOpen = false" 
                        class="flex-1 py-3 rounded-xl border border-bor-color text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-card-bg transition">
                    {{ __('messages.cancel') }}
                </button>
                
                <button @click="submitMergeTable()" 
                        :disabled="selectedMergeTables.length === 0 || isProcessing" 
                        class="flex-[2] py-3 rounded-xl text-white font-bold text-sm shadow-lg flex justify-center items-center gap-2 transition-all" 
                        :class="selectedMergeTables.length === 0 || isProcessing ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary hover:bg-primary/90 active:scale-95'">
                    
                    <span x-show="!isProcessing">{{ __('messages.confirm') }} <span x-show="selectedMergeTables.length > 0" x-text="'(' + selectedMergeTables.length + ')'"></span></span>
                    <span x-show="isProcessing"><i class="ri-loader-4-line animate-spin"></i> កំពុងដំណើរការ...</span>
                </button>
            </div>
        </div>
    </div>

</div>