<div x-show="isProductModalOpen" class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;" x-cloak>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-[2px] transition-opacity" @click="closeProductModal()"></div>
    
    {{-- Modal Card --}}
    <div class="relative w-full sm:max-w-md bg-white dark:bg-gray-900 rounded-t-[32px] sm:rounded-[32px] shadow-2xl overflow-hidden flex flex-col max-h-[92vh] transition-all transform" 
            x-show="isProductModalOpen" 
            x-transition:enter="transition cubic-bezier(0.22, 1, 0.36, 1) duration-500" 
            x-transition:enter-start="translate-y-full opacity-0" 
            x-transition:enter-end="translate-y-0 opacity-100">
        
        {{-- 1. IMAGE HEADER (SMALLER HEIGHT) --}}
        <div class="relative h-40 shrink-0 bg-gray-100 dark:bg-gray-800 group">
            <template x-if="tempItem.image">
                <img :src="'/storage/' + tempItem.image" class="w-full h-full object-cover">
            </template>
            <template x-if="!tempItem.image">
                <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 bg-gray-50 dark:bg-gray-800">
                    <i class="ri-image-2-line text-3xl mb-1"></i>
                    <span class="text-[10px] uppercase tracking-widest">No Image</span>
                </div>
            </template>

            {{-- Close Button --}}
            <button @click="closeProductModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-black/20 hover:bg-black/40 text-white backdrop-blur-md rounded-full transition-colors z-10">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>

        {{-- 2. CONTENT BODY --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar bg-white dark:bg-gray-900 relative">
            
            {{-- Product Info Header (Compact Padding) --}}
            <div class="px-5 pt-4 pb-2">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-tight" x-text="tempItem.name"></h2>
                <p class="text-xs text-gray-400 mt-1 font-medium" x-text="tempItem.category_name || 'Item Detail'"></p>
            </div>

            {{-- Price & Qty Control --}}
            <div class="px-5 py-3 flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Price</span>
                    <span class="text-3xl font-black text-primary tracking-tight" x-text="'$' + parseFloat(tempItem.base_price).toFixed(2)"></span>
                </div>

                {{-- Qty Stepper --}}
                <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-full p-1 h-10 shadow-inner">
                    <button @click="if(tempItem.qty > 1) tempItem.qty--" class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-200 hover:text-primary shadow-sm flex items-center justify-center transition-transform active:scale-90">
                        <i class="ri-subtract-line font-bold text-sm"></i>
                    </button>
                    <span class="font-bold text-base w-8 text-center text-gray-800 dark:text-white select-none" x-text="tempItem.qty"></span>
                    <button @click="tempItem.qty++" class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-200 hover:text-primary shadow-sm flex items-center justify-center transition-transform active:scale-90">
                        <i class="ri-add-line font-bold text-sm"></i>
                    </button>
                </div>
            </div>

            {{-- Divider --}}
            <div class="h-1.5 bg-gray-50 dark:bg-gray-800/50 w-full mb-2"></div>

            <div class="px-5 space-y-5 pb-24"> 
                
                {{-- Addons List --}}
                <div x-show="availableAddons.length > 0">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-1 h-3 bg-primary rounded-full"></div>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wide">Add-ons</h3>
                    </div>
                    
                    {{-- Scrollable Addons if too many --}}
                    <div class="max-h-[220px] overflow-y-auto custom-scrollbar pr-1">
                        <div class="space-y-2">
                            <template x-for="addon in availableAddons" :key="addon.id">
                                <div @click="toggleAddon(addon)"
                                        class="group flex items-center justify-between p-2.5 rounded-xl border transition-all duration-200 cursor-pointer"
                                        :class="isAddonSelected(addon.id) ? 'border-primary/50 bg-primary/5 shadow-sm' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 hover:border-gray-200'">
                                    
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors shrink-0"
                                                :class="isAddonSelected(addon.id) ? 'bg-primary border-primary' : 'border-gray-300 dark:border-gray-600'">
                                            <i class="ri-check-line text-white text-[10px]" x-show="isAddonSelected(addon.id)"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" x-text="addon.name"></span>
                                            <span class="text-[10px] font-bold text-primary" x-text="'+$' + parseFloat(addon.price).toFixed(2)"></span>
                                        </div>
                                    </div>

                                    {{-- Mini Qty --}}
                                    <div x-show="isAddonSelected(addon.id)" @click.stop class="flex items-center bg-white dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600 h-6 shadow-sm">
                                        <button @click="updateAddonQty(addon.id, -1)" class="w-6 h-full text-gray-400 hover:text-red-500"><i class="ri-subtract-line text-[10px]"></i></button>
                                        <span class="text-xs font-bold w-4 text-center dark:text-white" x-text="getAddonQty(addon.id)"></span>
                                        <button @click="updateAddonQty(addon.id, 1)" class="w-6 h-full text-gray-400 hover:text-primary"><i class="ri-add-line text-[10px]"></i></button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Note Section --}}
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <i class="ri-file-edit-line text-gray-400 text-sm"></i>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wide">Note</h3>
                    </div>
                    <textarea x-model="tempItem.note" 
                                rows="2" 
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 border-none focus:ring-2 focus:ring-primary/20 focus:bg-white dark:focus:bg-gray-800 transition-all text-sm resize-none shadow-sm" 
                                placeholder="Type a note..."></textarea>
                </div>
            </div>
        </div>

        {{-- 3. FLOATING ACTION BUTTON (Compact & Clean) --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-white via-white/95 to-transparent dark:from-gray-900 dark:via-gray-900/95 z-30">
            <button @click="addToCart()" class="w-full bg-primary text-white h-12 rounded-full font-bold text-sm shadow-lg shadow-primary/30 hover:scale-[1.01] active:scale-[0.98] transition-all flex justify-between items-center px-1.5 overflow-hidden">
                <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm text-white">
                    <i class="ri-shopping-basket-2-fill"></i>
                </div>
                <span class="uppercase tracking-wide font-bold">Add to Order</span>
                <div class="bg-white text-primary px-3 py-1.5 rounded-full font-black text-sm min-w-[3.5rem] text-center shadow-sm">
                    <span x-text="'$' + calculateItemTotal().toFixed(2)"></span>
                </div>
            </button>
        </div>
    </div>
</div>