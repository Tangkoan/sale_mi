<div x-show="isCheckoutModalOpen" 
     class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-0 md:p-4" 
     style="display: none;" 
     x-cloak>
    
    {{-- BACKDROP --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300" 
         x-show="isCheckoutModalOpen" @click="isCheckoutModalOpen = false"></div>

    {{-- MAIN CHECKOUT MODAL --}}
    <div class="relative w-full max-w-5xl bg-page-bg rounded-t-[20px] md:rounded-[24px] shadow-2xl overflow-hidden flex flex-col md:flex-row h-[95vh] md:h-[85vh] transition-transform duration-300 transform"
         x-show="isCheckoutModalOpen"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="translate-y-full opacity-0" 
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="translate-y-0 opacity-100" 
         x-transition:leave-end="translate-y-full opacity-0">

        {{-- LEFT SIDE: ITEMS LIST --}}
        <div class="flex flex-col h-[55%] md:h-full md:flex-[1.5] border-b md:border-b-0 md:border-r border-bor-color bg-card-bg relative">
            
            {{-- HEADER --}}
            <div class="p-4 border-b border-bor-color shrink-0 bg-card-bg z-10 flex flex-col gap-3">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm"><i class="ri-file-list-3-fill"></i></span>
                        <span x-text="isSplitMode ? 'Select Items to Split' : 'Order Details'"></span>
                    </h3>
                    <p class="text-xs text-gray-500 bg-input-bg px-2 py-1 rounded border border-bor-color" x-text="orderDetails.invoice_number"></p>
                </div>
                <div class="flex gap-2 w-full">
                    @can('pos-move-table')
                        <button @click="openMoveModal()" x-show="!isSplitMode" 
                                class="flex-1 py-2 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary/20 active:scale-95 transition-all border border-primary/10 flex items-center justify-center gap-1">
                            <i class="ri-share-forward-line"></i> {{ __('messages.move') }}
                        </button>
                    @endcan
                    @can('pos-merge-table')
                        <button @click="openMergeModal()" x-show="!isSplitMode" 
                                class="flex-1 py-2 bg-secondary/10 text-secondary rounded-lg text-xs font-bold hover:bg-secondary/20 active:scale-95 transition-all border border-secondary/10 flex items-center justify-center gap-1">
                            <i class="ri-git-merge-line"></i> {{ __('messages.merge') }}
                        </button>
                    @endcan
                    @can('pos-split-bill')
                        <button @click="toggleSplitMode()" 
                                class="flex-1 py-2 rounded-lg text-xs font-bold transition-all border flex items-center justify-center gap-1 active:scale-95"
                                :class="isSplitMode ? 'bg-red-50 text-red-600 border-red-100' : 'bg-input-bg text-gray-600 border-bor-color hover:bg-gray-200 dark:hover:bg-gray-700'">
                            <i class="ri-scissors-cut-line"></i> <span x-text="isSplitMode ? 'Cancel' : 'Split'"></span>
                        </button>
                    @endcan
                </div>
            </div>
            
            {{-- ITEMS LIST Container --}}
            <div class="flex-1 overflow-y-auto p-2 md:p-4 custom-scrollbar bg-page-bg relative">
                <div class="space-y-3">
                    <template x-for="item in orderDetails.items" :key="'item-' + item.id">
                        <div class="bg-card-bg p-3 rounded-xl border border-bor-color shadow-sm flex flex-col gap-2 transition-all"
                             :class="isSplitMode && isItemSplitted(item.id) ? 'ring-2 ring-primary border-primary bg-primary/5' : ''">
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start gap-3">
                                        <div x-show="isSplitMode" class="pt-1">
                                            <input type="checkbox" @change="toggleSplitItem(item)" :checked="isItemSplitted(item.id)" class="w-5 h-5 rounded text-primary focus:ring-primary border-gray-300 cursor-pointer">
                                        </div>
                                        <div class="text-gray-800 dark:text-gray-200 text-base flex-1">
                                            <div class="font-bold text-lg" x-text="item.product ? item.product.name : 'Item'"></div>
                                            <template x-if="item.addons && item.addons.length > 0">
                                                <div class="mt-2 space-y-1 border-t border-dashed border-bor-color pt-1">
                                                    <template x-for="(ad, index) in item.addons" :key="index">
                                                        <div class="flex items-center justify-between text-sm bg-input-bg p-1.5 rounded-lg group">
                                                            <div class="flex items-center gap-1">
                                                                <i class="ri-add-circle-fill text-primary text-xs"></i>
                                                                <span class="text-gray-600 dark:text-gray-300 font-medium" x-text="ad.addon?.name || ad.name"></span>
                                                                <span x-show="parseFloat(ad.price) > 0" class="text-xs text-gray-500" x-text="'($' + parseFloat(ad.price).toFixed(2) + ')'"></span>
                                                            </div>
                                                            <div class="flex items-center gap-2" :class="isSplitMode ? 'hidden' : ''">
                                                                <div class="flex items-center bg-card-bg rounded border border-bor-color h-6">
                                                                    <button @click="updateAddonQty(item.id, ad.id, 'decrease')" class="px-1.5 text-gray-500 hover:text-red-500 border-r border-bor-color h-full flex items-center">-</button>
                                                                    <span class="px-1.5 font-bold text-xs text-gray-800 dark:text-white" x-text="ad.quantity || 1"></span>
                                                                    <button @click="updateAddonQty(item.id, ad.id, 'increase')" class="px-1.5 text-gray-500 hover:text-primary border-l border-bor-color h-full flex items-center">+</button>
                                                                </div>
                                                                <button @click="updateAddonQty(item.id, ad.id, 'remove')" class="text-red-400 hover:text-red-600 p-0.5 rounded hover:bg-red-50 transition"><i class="ri-close-line"></i></button>
                                                            </div>
                                                            <div x-show="isSplitMode" class="text-xs font-bold text-gray-500">x<span x-text="ad.quantity || 1"></span></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="font-bold text-gray-900 dark:text-white text-lg">
                                        <span x-text="'$' + formatNumber((parseFloat(item.price) + (item.addons ? item.addons.reduce((sum, ad) => sum + (parseFloat(ad.price) * (ad.quantity || 1)), 0) : 0)) * item.quantity)"></span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between" :class="isSplitMode ? 'opacity-50 pointer-events-none' : ''">
                                    <button @click="updateItemQty(item.id, 'remove')" class="text-red-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg"><i class="ri-delete-bin-line text-lg"></i></button>
                                    <div class="flex items-center bg-input-bg rounded-lg p-0.5 border border-bor-color">
                                        <button @click="updateItemQty(item.id, 'decrease')" class="w-8 h-8 flex items-center justify-center rounded-md bg-card-bg shadow-sm hover:text-red-500 font-bold border border-bor-color">-</button>
                                        <span class="w-8 text-center font-bold text-gray-800 dark:text-white text-sm" x-text="item.quantity"></span>
                                        <button @click="updateItemQty(item.id, 'increase')" class="w-8 h-8 flex items-center justify-center rounded-md bg-card-bg shadow-sm hover:text-green-500 font-bold border border-bor-color">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- RIGHT SIDE: PAYMENT --}}
        <div class="flex flex-col h-[45%] md:h-full md:flex-1 bg-card-bg z-20 shadow-[-5px_0_15px_rgba(0,0,0,0.05)]">
             <div class="flex-1 overflow-y-auto p-5 md:p-8 custom-scrollbar">
                <div class="flex justify-between items-center mb-6 mt-1 p-4 bg-input-bg rounded-2xl border border-bor-color">
                    <div class="flex flex-col">
                        <span class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-gray-400">{{ __('messages.total_payble') }}</span>
                    </div>
                    <div class="flex items-center gap-2 md:gap-3">
                        <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tight" x-text="'$' + currentTotalUSD.toFixed(2)"></h1>
                        <span class="text-gray-300 text-2xl font-light hidden md:block">/</span>
                        <div class="px-3 py-1 rounded-lg bg-primary/10 text-primary border border-primary/20 font-bold text-sm md:text-base whitespace-nowrap flex items-center">
                            <span x-text="totalRiel"></span> <span class="ml-1 text-xs">៛</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-400 uppercase ml-1 mb-2 block">{{ __('messages.payment_method') }}</label>
                    <div class="bg-input-bg p-1 rounded-xl flex border border-bor-color">
                        <button @click="paymentMethod = 'cash'" 
                                class="flex-1 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center gap-2 transition-all duration-200"
                                :class="paymentMethod === 'cash' ? 'bg-card-bg text-primary shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700'">
                            <i class="ri-money-dollar-circle-fill text-lg"></i> {{ __('messages.cash') }}
                        </button>
                        <button @click="paymentMethod = 'qr'" 
                                class="flex-1 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center gap-2 transition-all duration-200"
                                :class="paymentMethod === 'qr' ? 'bg-card-bg text-primary shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700'">
                            <i class="ri-qr-code-line text-lg"></i> {{ __('messages.khqr') }}
                        </button>
                    </div>
                </div>
             </div>

             <div class="p-4 md:p-5 border-t border-bor-color bg-card-bg shrink-0 pb-6 md:pb-5">
                <div class="flex gap-3">
                    <button @click="isCheckoutModalOpen = false" class="flex-1 py-3.5 rounded-xl border border-bor-color text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-input-bg">{{ __('messages.cancel') }}</button>
                    <button @click="confirmPayment()" :disabled="isProcessing" class="flex-[2] py-3.5 rounded-xl text-white font-bold text-sm shadow-lg flex justify-center items-center gap-2 bg-primary hover:bg-primary/90">
                        <span x-text="isProcessing ? 'Processing...' : '{{ __('messages.confirm_print') }}'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MOVE TABLE MODAL --}}
    <div x-show="isMoveModalOpen" 
        style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm"
        x-cloak
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 scale-95" 
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 scale-100" 
        x-transition:leave-end="opacity-0 scale-95">

        <div class="bg-card-bg w-full max-w-md rounded-[24px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.away="isMoveModalOpen = false">
            <div class="bg-primary/5 p-6 text-center border-b border-primary/10">
                <h3 class="text-lg font-black text-gray-800 dark:text-white mb-4">{{ __('messages.move_table') }}</h3>
                <div class="flex items-center justify-between gap-2 bg-card-bg p-3 rounded-xl shadow-sm border border-primary/10">
                    <div class="flex flex-col items-center w-1/3">
                        <span class="text-[10px] text-gray-400 font-bold uppercase mb-1">{{ __('messages.now') }}</span>
                        <div class="font-black text-gray-800 dark:text-white text-lg px-3 py-1 bg-input-bg rounded-lg w-full text-center">
                            <span x-text="selectedTable ? selectedTable.name : '...'"></span>
                        </div>
                    </div>
                    <div class="text-primary"><i class="ri-arrow-right-line text-xl bg-primary/10 p-1.5 rounded-full"></i></div>
                    <div class="flex flex-col items-center w-1/3">
                        <span class="text-[10px] text-primary font-bold uppercase mb-1">{{ __('messages.new_table') }}</span> {{-- 🔥 ប្រើ Message --}}
                        <div class="font-black text-lg px-3 py-1 rounded-lg w-full text-center transition-all border-2 border-dashed"
                            :class="selectedTargetTable ? 'bg-primary text-white border-primary shadow-lg shadow-primary/30' : 'bg-card-bg text-gray-400 border-bor-color'">
                            <span x-text="selectedTargetTable ? selectedTargetTable.name : '?'"></span>
                        </div>
                    </div>
                </div>
                {{-- 🔥 ប្រើ Message --}}
                <p class="text-xs text-gray-500 mt-3" x-show="!selectedTargetTable">{{ __('messages.select_move_target') }}</p>
            </div>
            <div class="p-4 overflow-y-auto custom-scrollbar flex-1 bg-card-bg">
                <div class="grid grid-cols-3 gap-3">
                    <template x-for="table in availableTables" :key="table.id">
                        <button @click="selectedTargetTable = table" 
                                class="relative flex flex-col items-center justify-center p-3 rounded-2xl border-2 transition-all duration-200 group"
                                :class="selectedTargetTable && selectedTargetTable.id === table.id ? 'border-primary bg-primary/5 ring-2 ring-primary/30' : 'border-bor-color hover:border-primary/50 hover:bg-primary/5'">
                            <div x-show="selectedTargetTable && selectedTargetTable.id === table.id" class="absolute top-2 right-2 text-primary bg-card-bg rounded-full shadow-sm"><i class="ri-checkbox-circle-fill text-lg"></i></div>
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-1 transition-colors" :class="selectedTargetTable && selectedTargetTable.id === table.id ? 'bg-card-bg text-primary' : 'bg-input-bg text-gray-400'"><i class="ri-restaurant-line text-lg"></i></div>
                            <span class="font-bold text-sm" :class="selectedTargetTable && selectedTargetTable.id === table.id ? 'text-primary' : 'text-gray-600 dark:text-gray-300'" x-text="table.name"></span>
                        </button>
                    </template>
                </div>
                <template x-if="availableTables.length === 0">
                    <div class="flex flex-col items-center justify-center py-10 text-center text-gray-400">
                        <i class="ri-store-3-line text-4xl mb-2 opacity-50"></i>
                        <p class="text-sm">{{ __('messages.not_have_table_free') }}</p>
                    </div>
                </template>
            </div>
            <div class="p-4 border-t border-bor-color bg-input-bg flex gap-3">
                <button @click="isMoveModalOpen = false" class="flex-1 py-3 rounded-xl border border-bor-color text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-card-bg transition">{{ __('messages.cancel') }}</button>
                <button @click="submitMoveTable()" :disabled="!selectedTargetTable || isProcessing" class="flex-[2] py-3 rounded-xl text-white font-bold text-sm shadow-lg flex justify-center items-center gap-2 transition-all" :class="!selectedTargetTable || isProcessing ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary hover:bg-primary/90 active:scale-95'">
                    <span x-show="!isProcessing">{{ __('messages.confirm') }}</span>
                    <span x-show="isProcessing"><i class="ri-loader-4-line animate-spin"></i> Processing...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- 🔥 MERGE TABLE MODAL (MULTIPLE SELECTION - PRIMARY COLOR FIX) 🔥 --}}
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
            
            {{-- HEADER: ប្រើ bg-primary/5 ជំនួស secondary --}}
            <div class="bg-primary/5 p-6 text-center border-b border-primary/10">
                <h3 class="text-lg font-black text-gray-800 dark:text-white mb-4">{{ __('messages.merge') }}</h3>
                
                {{-- Merge Info Box --}}
                <div class="flex items-center justify-between gap-2 bg-card-bg p-3 rounded-xl shadow-sm border border-primary/10">
                    
                    {{-- Current Table --}}
                    <div class="flex flex-col items-center w-1/3">
                        <span class="text-[10px] text-gray-400 font-bold uppercase mb-1">{{ __('messages.now') }}</span>
                        <div class="font-black text-gray-800 dark:text-white text-lg px-3 py-1 bg-input-bg rounded-lg w-full text-center border border-bor-color">
                            <span x-text="selectedTable ? selectedTable.name : '...'"></span>
                        </div>
                    </div>
                    
                    {{-- Icon (Add) --}}
                    <div class="text-primary"><i class="ri-add-circle-fill text-2xl"></i></div>
                    
                    {{-- Selected Tables List --}}
                    <div class="flex flex-col items-center w-1/3">
                        <span class="text-[10px] text-primary font-bold uppercase mb-1">{{ __('messages.merge_from') }}</span>
                        
                        {{-- Active Selection Box (Primary Color) --}}
                        <div class="font-black text-lg px-2 py-1 rounded-lg w-full text-center transition-all border-2 border-dashed flex items-center justify-center min-h-[40px]"
                            :class="selectedMergeTables.length > 0 ? 'bg-primary text-white border-primary shadow-lg shadow-primary/30' : 'bg-card-bg text-gray-400 border-bor-color'">
                            
                            <span class="text-sm leading-tight" 
                                  x-text="selectedMergeTables.length > 0 ? selectedMergeTables.map(t => t.name).join(', ') : '?'">
                            </span>
                        </div>
                    </div>
                </div>
                
                {{-- Helper Text --}}
                <p class="text-xs text-gray-500 mt-3" x-show="selectedMergeTables.length === 0">{{ __('messages.select_merge_source') }}</p>
                <p class="text-xs text-primary font-bold mt-3" x-show="selectedMergeTables.length > 0">
                    <span x-text="selectedMergeTables.length"></span> តុត្រូវបានជ្រើសរើស
                </p>
            </div>

            {{-- BODY: List of Tables --}}
            <div class="p-4 overflow-y-auto custom-scrollbar flex-1 bg-card-bg">
                <div class="grid grid-cols-3 gap-3">
                    <template x-for="table in busyTables" :key="table.id">
                        <button @click="toggleMergeTable(table)" 
                                class="relative flex flex-col items-center justify-center p-3 rounded-2xl border-2 transition-all duration-200 group"
                                :class="isTableSelectedForMerge(table.id) 
                                    ? 'border-primary bg-primary/5 ring-2 ring-primary/30' 
                                    : 'border-bor-color bg-card-bg hover:border-primary/50 hover:bg-primary/5'">
                            
                            {{-- Checkbox Icon --}}
                            <div x-show="isTableSelectedForMerge(table.id)" class="absolute top-2 right-2 text-primary bg-card-bg rounded-full shadow-sm">
                                <i class="ri-checkbox-circle-fill text-lg"></i>
                            </div>
                            
                            {{-- Table Icon --}}
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-1 transition-colors" 
                                 :class="isTableSelectedForMerge(table.id) ? 'bg-card-bg text-primary' : 'bg-input-bg text-gray-400'">
                                 <i class="ri-restaurant-fill text-lg"></i>
                            </div>
                            
                            {{-- Table Name --}}
                            <span class="font-bold text-sm" 
                                  :class="isTableSelectedForMerge(table.id) ? 'text-primary' : 'text-gray-600 dark:text-gray-300'" 
                                  x-text="table.name"></span>
                        </button>
                    </template>
                </div>

                {{-- Empty State --}}
                <template x-if="busyTables.length === 0">
                    <div class="flex flex-col items-center justify-center py-10 text-center text-gray-400">
                        <i class="ri-emotion-sad-line text-4xl mb-2 opacity-50"></i>
                        <p class="text-sm">{{ __('messages.no_busy_tables') }}</p>
                    </div>
                </template>
            </div>

            {{-- FOOTER --}}
            <div class="p-4 border-t border-bor-color bg-input-bg flex gap-3">
                <button @click="isMergeModalOpen = false" 
                        class="flex-1 py-3 rounded-xl border border-bor-color text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-card-bg transition">
                    {{ __('messages.cancel') }}
                </button>
                
                {{-- Confirm Button (Primary) --}}
                <button @click="submitMergeTable()" 
                        :disabled="selectedMergeTables.length === 0 || isProcessing" 
                        class="flex-[2] py-3 rounded-xl text-white font-bold text-sm shadow-lg flex justify-center items-center gap-2 transition-all" 
                        :class="selectedMergeTables.length === 0 || isProcessing ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary hover:bg-primary/90 active:scale-95'">
                    
                    <span x-show="!isProcessing">{{ __('messages.confirm') }} <span x-show="selectedMergeTables.length > 0" x-text="'(' + selectedMergeTables.length + ')'"></span></span>
                    <span x-show="isProcessing"><i class="ri-loader-4-line animate-spin"></i> Processing...</span>
                </button>
            </div>
        </div>
    </div>

</div>